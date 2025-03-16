<?php

namespace Tests\Feature\Notifications;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create();

        // Create the notifications table if it doesn't exist
        if (!Schema::hasTable('notifications')) {
            $this->artisan('notifications:table');
            $this->artisan('migrate');
        }

        // Create some test notifications
        $this->createTestNotifications();
    }

    /**
     * Create test notifications for the user.
     */
    protected function createTestNotifications(): void
    {
        // Create read notifications
        for ($i = 0; $i < 3; $i++) {
            $this->user->notifications()->create([
                'id' => Str::uuid()->toString(),
                'type' => 'App\\Notifications\\TestNotification',
                'data' => ['message' => "Read notification {$i}", 'type' => 'test'],
                'read_at' => now(),
            ]);
        }

        // Create unread notifications
        for ($i = 0; $i < 2; $i++) {
            $this->user->notifications()->create([
                'id' => Str::uuid()->toString(),
                'type' => 'App\\Notifications\\TestNotification',
                'data' => ['message' => "Unread notification {$i}", 'type' => 'test'],
                'read_at' => null,
            ]);
        }
    }

    public function test_user_can_get_all_notifications()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/notifications');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'notifications',
                'unread_count',
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total'
                ]
            ]);

        $this->assertEquals(5, $response->json('meta.total'));
        $this->assertEquals(2, $response->json('unread_count'));
    }

    public function test_user_can_get_unread_notifications()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/notifications/unread');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'notifications',
                'unread_count',
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total'
                ]
            ]);

        $this->assertEquals(2, $response->json('meta.total'));
        $this->assertEquals(2, $response->json('unread_count'));
    }

    public function test_user_can_mark_notification_as_read()
    {
        // Get an unread notification
        $notification = $this->user->unreadNotifications()->first();

        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/notifications/{$notification->id}/read");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Notification marked as read'
            ]);

        // Check that the notification is now marked as read
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_can_mark_all_notifications_as_read()
    {
        $response = $this->actingAs($this->user)
            ->patchJson('/api/v1/notifications/read-all');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'All notifications marked as read'
            ]);

        // Check that all notifications are now marked as read
        $this->assertEquals(0, $this->user->unreadNotifications()->count());
    }

    public function test_user_can_delete_notification()
    {
        // Get a notification
        $notification = $this->user->notifications()->first();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/notifications/{$notification->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Notification deleted'
            ]);

        // Check that the notification is deleted
        $this->assertNull($notification->fresh());
    }

    public function test_user_can_delete_all_notifications()
    {
        $response = $this->actingAs($this->user)
            ->deleteJson('/api/v1/notifications/all');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'All notifications deleted'
            ]);

        // Check that all notifications are deleted
        $this->assertEquals(0, $this->user->notifications()->count());
    }

    public function test_user_can_get_notification_preferences()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/notifications/preferences');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'preferences' => [
                    'email',
                    'database',
                    'sms'
                ]
            ]);
    }

    public function test_user_can_update_notification_preferences()
    {
        $response = $this->actingAs($this->user)
            ->putJson('/api/v1/notifications/preferences', [
                'preferences' => [
                    'email' => [
                        'booking_confirmation' => false,
                        'event_reminder' => true
                    ],
                    'sms' => [
                        'event_cancellation' => false
                    ]
                ]
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Notification preferences updated'
            ]);
    }
}
