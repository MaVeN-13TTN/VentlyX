<?php

namespace Tests\Feature\Events;

use Tests\TestCase;
use App\Models\User;
use App\Models\Event;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class EventImageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var \App\Models\User
     */
    protected $organizer;

    /**
     * @var \App\Models\User
     */
    protected $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // Create roles
        $adminRole = Role::create(['name' => 'Admin']);
        $organizerRole = Role::create(['name' => 'Organizer']);
        $userRole = Role::create(['name' => 'User']);

        // Create users
        $this->organizer = User::factory()->create();
        $this->organizer->roles()->attach($organizerRole);

        $this->regularUser = User::factory()->create();
        $this->regularUser->roles()->attach($userRole);
    }

    public function test_can_upload_event_image()
    {
        $event = Event::factory()->create([
            'organizer_id' => $this->organizer->id
        ]);

        $image = UploadedFile::fake()->image('event.jpg', 800, 600);

        $response = $this->actingAs($this->organizer)
            ->postJson("/api/v1/events/{$event->id}/image", [
                'image' => $image
            ]);

        $response->assertOk();

        $event->refresh();
        // Check that image_url field was updated
        $this->assertNotNull($event->image_url);

        // Extract path from URL for storage check
        $path = str_replace('/storage/', '', $event->image_url);
        // Check the file exists in the storage
        $this->assertTrue(Storage::disk('public')->exists($path));
    }

    public function test_rejects_invalid_image_format()
    {
        $event = Event::factory()->create([
            'organizer_id' => $this->organizer->id
        ]);

        $invalidFile = UploadedFile::fake()->create('document.pdf', 500);

        $response = $this->actingAs($this->organizer)
            ->postJson("/api/v1/events/{$event->id}/image", [
                'image' => $invalidFile
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_rejects_oversized_image()
    {
        $event = Event::factory()->create([
            'organizer_id' => $this->organizer->id
        ]);

        $oversizedImage = UploadedFile::fake()->image('large.jpg')->size(3072); // 3MB

        $response = $this->actingAs($this->organizer)
            ->postJson("/api/v1/events/{$event->id}/image", [
                'image' => $oversizedImage
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_updates_replace_existing_image()
    {
        $event = Event::factory()->create([
            'organizer_id' => $this->organizer->id
        ]);

        // Upload initial image
        $oldImage = UploadedFile::fake()->image('old-image.jpg');
        $initialResponse = $this->actingAs($this->organizer)
            ->postJson("/api/v1/events/{$event->id}/image", [
                'image' => $oldImage
            ]);

        $event->refresh();
        $oldImagePath = str_replace('/storage/', '', $event->image_url);

        // Upload new image
        $newImage = UploadedFile::fake()->image('new-image.jpg');
        $response = $this->actingAs($this->organizer)
            ->postJson("/api/v1/events/{$event->id}/image", [
                'image' => $newImage
            ]);

        $response->assertOk();
        $event->refresh();

        $newImagePath = str_replace('/storage/', '', $event->image_url);

        // Check the new image exists
        $this->assertTrue(Storage::disk('public')->exists($newImagePath));

        // Check the old image was deleted (if paths are different)
        if ($oldImagePath != $newImagePath) {
            $this->assertFalse(Storage::disk('public')->exists($oldImagePath));
        }
    }

    public function test_deletes_image_when_event_deleted()
    {
        $event = Event::factory()->create([
            'organizer_id' => $this->organizer->id
        ]);

        $image = UploadedFile::fake()->image('event-image.jpg');
        $this->actingAs($this->organizer)
            ->postJson("/api/v1/events/{$event->id}/image", [
                'image' => $image
            ]);

        $event->refresh();
        $imagePath = str_replace('/storage/', '', $event->image_url);

        // Delete the event
        $this->actingAs($this->organizer)
            ->deleteJson("/api/v1/events/{$event->id}");

        $this->assertFalse(Storage::disk('public')->exists($imagePath));
    }

    public function test_unauthorized_user_cannot_upload_image()
    {
        $event = Event::factory()->create([
            'organizer_id' => $this->organizer->id
        ]);

        $image = UploadedFile::fake()->image('event.jpg');

        $response = $this->actingAs($this->regularUser)
            ->postJson("/api/v1/events/{$event->id}/image", [
                'image' => $image
            ]);

        $response->assertForbidden();
    }
}
