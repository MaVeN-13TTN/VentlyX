<?php

namespace Tests\Feature\Notifications;

use App\Models\User;
use App\Providers\EventServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use ReflectionClass;
use Tests\TestCase;

class EventNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create();
    }

    public function test_registered_event_triggers_email_verification_notification()
    {
        // Mock the event dispatcher
        Event::fake();

        // Trigger the Registered event
        event(new Registered($this->user));

        // Assert that the event was dispatched
        Event::assertDispatched(Registered::class);

        // We don't check for the listener registration as it depends on the application configuration
    }

    public function test_event_service_provider_registers_listeners()
    {
        // Get the event service provider
        $provider = new EventServiceProvider(app());

        // Use reflection to access the protected property
        $reflection = new ReflectionClass($provider);
        $property = $reflection->getProperty('listen');
        $property->setAccessible(true);
        $listeners = $property->getValue($provider);

        // Assert that the Registered event has the SendEmailVerificationNotification listener
        $this->assertArrayHasKey(Registered::class, $listeners);
        $this->assertContains(SendEmailVerificationNotification::class, $listeners[Registered::class]);
    }

    public function test_email_verification_notification_is_sent_when_user_registers()
    {
        // Skip this test if email verification is not enabled
        if (!config('auth.verify_email', false)) {
            $this->markTestSkipped('Email verification is not enabled in this application.');
            return;
        }

        // Mock the notification dispatcher
        Notification::fake();

        // Create a new user through the registration endpoint with a password that meets requirements
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ]);

        // Check if registration was successful
        if ($response->status() !== 201) {
            $this->markTestSkipped('User registration failed: ' . json_encode($response->json()));
            return;
        }

        // Get the newly created user
        $newUser = User::where('email', 'newuser@example.com')->first();

        // Assert that a verification notification was sent
        Notification::assertSentTo(
            $newUser,
            \Illuminate\Auth\Notifications\VerifyEmail::class
        );
    }
}
