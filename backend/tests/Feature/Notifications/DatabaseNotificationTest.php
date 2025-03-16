<?php

namespace Tests\Feature\Notifications;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class DatabaseNotificationTest extends TestCase
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
    }

    public function test_user_can_receive_database_notification()
    {
        // Create a database notification for the user
        $this->user->notifications()->create([
            'id' => Str::uuid()->toString(),
            'type' => 'App\\Notifications\\TestNotification',
            'data' => ['message' => 'Test notification'],
            'read_at' => null,
        ]);

        // Check that the notification was created
        $this->assertCount(1, $this->user->notifications);
        $this->assertEquals('Test notification', $this->user->notifications->first()->data['message']);
    }

    public function test_user_can_mark_notification_as_read()
    {
        // Create an unread notification
        $notification = $this->user->notifications()->create([
            'id' => Str::uuid()->toString(),
            'type' => 'App\\Notifications\\TestNotification',
            'data' => ['message' => 'Test notification'],
            'read_at' => null,
        ]);

        // Mark as read
        $this->user->notifications()->find($notification->id)->markAsRead();

        // Check that it's marked as read
        $this->assertNotNull($this->user->notifications->first()->read_at);
        $this->assertTrue($this->user->notifications->first()->read());
    }

    public function test_user_can_retrieve_unread_notifications()
    {
        // Create one read and one unread notification
        $this->user->notifications()->create([
            'id' => Str::uuid()->toString(),
            'type' => 'App\\Notifications\\TestNotification',
            'data' => ['message' => 'Read notification'],
            'read_at' => now(),
        ]);

        $this->user->notifications()->create([
            'id' => Str::uuid()->toString(),
            'type' => 'App\\Notifications\\TestNotification',
            'data' => ['message' => 'Unread notification'],
            'read_at' => null,
        ]);

        // Check unread notifications
        $this->assertCount(1, $this->user->unreadNotifications);
        $this->assertEquals('Unread notification', $this->user->unreadNotifications->first()->data['message']);
    }

    public function test_user_can_delete_notification()
    {
        // Create a notification
        $notification = $this->user->notifications()->create([
            'id' => Str::uuid()->toString(),
            'type' => 'App\\Notifications\\TestNotification',
            'data' => ['message' => 'Test notification'],
            'read_at' => null,
        ]);

        // Delete the notification
        $this->user->notifications()->find($notification->id)->delete();

        // Check that it's deleted
        $this->assertCount(0, $this->user->notifications);
    }
}
