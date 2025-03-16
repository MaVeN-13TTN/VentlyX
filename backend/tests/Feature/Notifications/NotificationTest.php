<?php

namespace Tests\Feature\Notifications;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);
    }

    public function test_reset_password_notification_is_sent()
    {
        Notification::fake();

        // Trigger password reset
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => $this->user->email,
        ]);

        $response->assertStatus(200);

        // Assert a notification was sent to the user
        Notification::assertSentTo(
            $this->user,
            ResetPasswordNotification::class
        );
    }

    public function test_reset_password_notification_contains_valid_token()
    {
        Notification::fake();

        // Trigger password reset
        $this->postJson('/api/v1/auth/forgot-password', [
            'email' => $this->user->email,
        ]);

        // Check that the notification contains a token
        Notification::assertSentTo($this->user, ResetPasswordNotification::class, function ($notification) {
            return !empty($notification->token);
        });
    }

    public function test_reset_password_notification_uses_mail_channel()
    {
        $notification = new ResetPasswordNotification('test-token');

        // Check that the notification uses the mail channel
        $this->assertEquals(['mail'], $notification->via($this->user));
    }

    public function test_reset_password_notification_email_has_correct_content()
    {
        $token = 'test-token';
        $notification = new ResetPasswordNotification($token);

        // Get the mail representation
        $mail = $notification->toMail($this->user);

        // Check mail content
        $this->assertEquals('Reset Password Notification', $mail->subject);
        $this->assertStringContainsString('You are receiving this email because we received a password reset request', $mail->introLines[0]);

        // Check that the action URL contains the token and email
        $this->assertStringContainsString($token, $mail->actionUrl);
        $this->assertStringContainsString(urlencode($this->user->email), $mail->actionUrl);
    }

    public function test_reset_password_notification_implements_should_queue()
    {
        // Test that the notification implements ShouldQueue
        $this->assertContains(
            \Illuminate\Contracts\Queue\ShouldQueue::class,
            class_implements(ResetPasswordNotification::class)
        );
    }

    public function test_notification_not_sent_for_nonexistent_email()
    {
        Notification::fake();

        // Try to reset password for non-existent email
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        // The application returns 422 for nonexistent emails
        $response->assertStatus(422);

        // No notification should be sent
        Notification::assertNothingSent();
    }

    public function test_user_model_has_notifiable_trait()
    {
        // Test that the User model uses the Notifiable trait
        $this->assertContains(
            \Illuminate\Notifications\Notifiable::class,
            class_uses_recursive(User::class)
        );
    }
}
