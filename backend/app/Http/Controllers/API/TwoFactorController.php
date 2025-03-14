<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorController extends Controller
{
    protected $google2fa;

    public function __construct(Google2FA $google2fa)
    {
        $this->google2fa = $google2fa;
    }

    public function enable(Request $request)
    {
        $user = $request->user();

        if ($user->two_factor_enabled) {
            return response()->json([
                'message' => '2FA is already enabled'
            ], 400);
        }

        // Generate the secret key
        $secretKey = $this->google2fa->generateSecretKey();

        // Create QR code
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secretKey
        );

        // Generate recovery codes
        $recoveryCodes = Collection::times(8, function () {
            return $this->generateRecoveryCode();
        })->all();

        // Store the data temporarily (will be confirmed later)
        $user->update([
            'two_factor_secret' => $secretKey,
            'two_factor_recovery_codes' => $recoveryCodes
        ]);

        // Generate QR code image
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new ImagickImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeImage = base64_encode($writer->writeString($qrCodeUrl));

        return response()->json([
            'message' => '2FA setup initiated',
            'secret' => $secretKey,
            'qr_code' => $qrCodeImage,
            'recovery_codes' => $recoveryCodes
        ]);
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6']
        ]);

        $user = $request->user();

        if ($user->two_factor_enabled) {
            return response()->json([
                'message' => '2FA is already enabled'
            ], 400);
        }

        $valid = $this->google2fa->verifyKey(
            $user->two_factor_secret,
            $request->code
        );

        if (!$valid) {
            return response()->json([
                'message' => 'Invalid verification code'
            ], 422);
        }

        $user->update([
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now()
        ]);

        return response()->json([
            'message' => '2FA enabled successfully'
        ]);
    }

    public function disable(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6']
        ]);

        $user = $request->user();

        if (!$user->two_factor_enabled) {
            return response()->json([
                'message' => '2FA is not enabled'
            ], 400);
        }

        $valid = $this->google2fa->verifyKey(
            $user->two_factor_secret,
            $request->code
        );

        if (!$valid) {
            return response()->json([
                'message' => 'Invalid verification code'
            ], 422);
        }

        $user->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null
        ]);

        return response()->json([
            'message' => '2FA disabled successfully'
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string']
        ]);

        $user = $request->user();

        // Check if it's a recovery code
        if (strlen($request->code) > 6) {
            return $this->verifyRecoveryCode($request, $user);
        }

        if (!$user->two_factor_enabled) {
            return response()->json([
                'message' => '2FA is not enabled'
            ], 400);
        }

        $valid = $this->google2fa->verifyKey(
            $user->two_factor_secret,
            $request->code
        );

        if (!$valid) {
            return response()->json([
                'message' => 'Invalid verification code'
            ], 422);
        }

        session(['2fa_verified' => true]);

        return response()->json([
            'message' => '2FA verified successfully'
        ]);
    }

    protected function verifyRecoveryCode(Request $request, $user)
    {
        $recoveryCodes = $user->two_factor_recovery_codes;

        if (($key = array_search($request->code, $recoveryCodes)) !== false) {
            // Remove used recovery code
            unset($recoveryCodes[$key]);
            $user->update([
                'two_factor_recovery_codes' => array_values($recoveryCodes)
            ]);

            session(['2fa_verified' => true]);

            return response()->json([
                'message' => 'Recovery code accepted',
                'remaining_codes' => count($recoveryCodes)
            ]);
        }

        return response()->json([
            'message' => 'Invalid recovery code'
        ], 422);
    }

    protected function generateRecoveryCode()
    {
        return sprintf(
            '%s-%s-%s-%s',
            bin2hex(random_bytes(4)),
            bin2hex(random_bytes(4)),
            bin2hex(random_bytes(4)),
            bin2hex(random_bytes(4))
        );
    }
}
