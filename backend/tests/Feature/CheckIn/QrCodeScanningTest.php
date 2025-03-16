<?php

namespace Tests\Feature\CheckIn;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Role;
use App\Models\TicketType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QrCodeScanningTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $organizer;
    private Event $event;
    private TicketType $ticketType;
    private Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        // Create roles
        $organizerRole = Role::factory()->create(['name' => 'Organizer']);
        $userRole = Role::factory()->create(['name' => 'User']);

        // Create users with proper role assignments
        $this->organizer = User::factory()->create();
        $this->organizer->roles()->attach($organizerRole->id);

        $this->user = User::factory()->create();
        $this->user->roles()->attach($userRole->id);

        // Create an event that is currently happening
        $this->event = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'start_time' => Carbon::now()->subHour(),
            'end_time' => Carbon::now()->addHours(2),
            'status' => 'published'
        ]);

        $this->ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'status' => 'active'
        ]);

        $this->booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'event_id' => $this->event->id,
            'ticket_type_id' => $this->ticketType->id,
            'status' => 'confirmed',
            'payment_status' => 'paid'
        ]);

        // Generate QR code for the booking
        $this->booking->generateQrCode();
    }

    public function test_qr_code_is_generated_with_correct_data()
    {
        $this->assertNotNull($this->booking->qr_code_url);
        $this->assertTrue(Storage::disk('public')->exists($this->booking->qr_code_url));
    }

    public function test_scan_valid_qr_code()
    {
        // Simulate scanning by extracting QR code data
        $qrData = json_encode([
            'booking_id' => $this->booking->id,
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'timestamp' => now()->timestamp
        ]);

        $response = $this->actingAs($this->organizer)
            ->postJson("/api/v1/check-in/verify", [
                'qr_data' => $qrData,
                'event_id' => $this->event->id
            ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'valid',
                'booking',
                'checked_in'
            ])
            ->assertJson([
                'valid' => true,
                'checked_in' => false
            ]);
    }

    public function test_scan_qr_code_for_wrong_event()
    {
        // Create another event
        $otherEvent = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'status' => 'published'
        ]);

        // Simulate scanning by extracting QR code data
        $qrData = json_encode([
            'booking_id' => $this->booking->id,
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'timestamp' => now()->timestamp
        ]);

        $response = $this->actingAs($this->organizer)
            ->postJson("/api/v1/check-in/verify", [
                'qr_data' => $qrData,
                'event_id' => $otherEvent->id
            ]);

        $response->assertStatus(404)
            ->assertJsonFragment([
                'valid' => false,
                'message' => 'Invalid booking or wrong event'
            ]);
    }

    public function test_scan_qr_code_for_cancelled_booking()
    {
        // Cancel the booking
        $this->booking->update(['status' => 'cancelled']);

        // Simulate scanning by extracting QR code data
        $qrData = json_encode([
            'booking_id' => $this->booking->id,
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'timestamp' => now()->timestamp
        ]);

        $response = $this->actingAs($this->organizer)
            ->postJson("/api/v1/check-in/verify", [
                'qr_data' => $qrData,
                'event_id' => $this->event->id
            ]);

        $response->assertStatus(400)
            ->assertJsonFragment([
                'valid' => false,
                'message' => 'Booking is not confirmed'
            ]);
    }

    public function test_scan_qr_code_and_check_in()
    {
        // Simulate scanning by extracting QR code data
        $qrData = json_encode([
            'booking_id' => $this->booking->id,
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'timestamp' => now()->timestamp
        ]);

        // First verify the QR code
        $verifyResponse = $this->actingAs($this->organizer)
            ->postJson("/api/v1/check-in/verify", [
                'qr_data' => $qrData,
                'event_id' => $this->event->id
            ]);

        $verifyResponse->assertOk()
            ->assertJson([
                'valid' => true,
                'checked_in' => false
            ]);

        // Then check in the booking
        $checkInResponse = $this->actingAs($this->organizer)
            ->postJson("/api/v1/check-in/bookings/{$this->booking->id}");

        $checkInResponse->assertOk()
            ->assertJsonStructure([
                'message',
                'booking' => [
                    'id',
                    'checked_in_at'
                ]
            ]);

        // Verify the booking was checked in
        $this->assertDatabaseHas('bookings', [
            'id' => $this->booking->id,
            'checked_in_by' => $this->organizer->id
        ]);
        $this->assertNotNull(Booking::find($this->booking->id)->checked_in_at);

        // Verify the QR code again - should show as already checked in
        $verifyAgainResponse = $this->actingAs($this->organizer)
            ->postJson("/api/v1/check-in/verify", [
                'qr_data' => $qrData,
                'event_id' => $this->event->id
            ]);

        $verifyAgainResponse->assertOk()
            ->assertJson([
                'valid' => true,
                'checked_in' => true
            ]);
    }

    public function test_scan_invalid_qr_code_format()
    {
        // Invalid QR data (missing booking_id)
        $qrData = json_encode([
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'timestamp' => now()->timestamp
        ]);

        $response = $this->actingAs($this->organizer)
            ->postJson("/api/v1/check-in/verify", [
                'qr_data' => $qrData,
                'event_id' => $this->event->id
            ]);

        $response->assertStatus(400)
            ->assertJsonFragment([
                'valid' => false,
                'message' => 'Invalid QR code data'
            ]);
    }

    public function test_scan_tampered_qr_code()
    {
        // Tampered QR data (non-existent booking ID)
        $qrData = json_encode([
            'booking_id' => 99999, // Non-existent ID
            'event_id' => $this->event->id,
            'user_id' => $this->user->id,
            'timestamp' => now()->timestamp
        ]);

        $response = $this->actingAs($this->organizer)
            ->postJson("/api/v1/check-in/verify", [
                'qr_data' => $qrData,
                'event_id' => $this->event->id
            ]);

        $response->assertStatus(404)
            ->assertJsonFragment([
                'valid' => false
            ]);
    }

    public function test_qr_code_regeneration_invalidates_old_code()
    {
        // Get the original QR code URL
        $originalQrUrl = $this->booking->qr_code_url;

        // Regenerate QR code
        $this->booking->generateQrCode();

        // Verify a new QR code is generated
        $this->assertNotEquals($originalQrUrl, $this->booking->qr_code_url);
        $this->assertTrue(Storage::disk('public')->exists($this->booking->qr_code_url));
    }
}
