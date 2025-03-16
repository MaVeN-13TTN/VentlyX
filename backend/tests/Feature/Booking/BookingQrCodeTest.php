<?php

namespace Tests\Feature\Booking;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Role;
use App\Models\TicketType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Contracts\Auth\Authenticatable;

class BookingQrCodeTest extends TestCase
{
    use RefreshDatabase;

    private Authenticatable $user;
    private Event $event;
    private TicketType $ticketType;
    private Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $userRole = Role::factory()->create(['name' => 'user']);

        $this->user = User::factory()->create();
        $this->user->roles()->attach($userRole->id);

        $this->event = Event::factory()->create([
            'start_time' => Carbon::now()->addDays(5),
            'end_time' => Carbon::now()->addDays(5)->addHours(3),
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
    }

    public function test_can_generate_qr_code_for_confirmed_booking()
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/bookings/{$this->booking->id}/qr-code");

        $response->assertOk()
            ->assertJsonStructure([
                'qr_code_url'
            ]);

        $qrCodePath = $response->json('qr_code_url');
        $this->assertTrue(Storage::disk('public')->exists($qrCodePath));
    }

    public function test_cannot_generate_qr_code_for_unconfirmed_booking()
    {
        $this->booking->update(['status' => 'pending']);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/bookings/{$this->booking->id}/qr-code");

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'QR code can only be generated for confirmed bookings'
            ]);
    }

    public function test_cannot_generate_qr_code_for_cancelled_booking()
    {
        $this->booking->update(['status' => 'cancelled']);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/bookings/{$this->booking->id}/qr-code");

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'QR code can only be generated for confirmed bookings'
            ]);
    }

    public function test_qr_code_contains_valid_booking_data()
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/bookings/{$this->booking->id}/qr-code");

        $response->assertOk();

        // Verify QR code content through validation endpoint
        $validationResponse = $this->actingAs($this->user)
            ->postJson('/api/v1/bookings/validate-qr', [
                'qr_content' => base64_encode(json_encode([
                    'booking_id' => $this->booking->id,
                    'event_id' => $this->event->id,
                    'user_id' => $this->user->id
                ]))
            ]);

        $validationResponse->assertOk()
            ->assertJsonFragment([
                'is_valid' => true
            ]);
    }

    public function test_other_users_cannot_generate_qr_code()
    {
        $otherUser = User::factory()->create();
        $otherUser->roles()->attach(Role::where('name', 'user')->first()->id);

        $response = $this->actingAs($otherUser)
            ->getJson("/api/v1/bookings/{$this->booking->id}/qr-code");

        $response->assertForbidden();
    }

    public function test_qr_code_is_regenerated_on_each_request()
    {
        // First request
        $response1 = $this->actingAs($this->user)
            ->getJson("/api/v1/bookings/{$this->booking->id}/qr-code");

        $qrCodePath1 = $response1->json('qr_code_url');

        // Add a small delay to ensure different timestamps
        sleep(1);

        // Second request
        $response2 = $this->actingAs($this->user)
            ->getJson("/api/v1/bookings/{$this->booking->id}/qr-code");

        $qrCodePath2 = $response2->json('qr_code_url');

        $this->assertNotEquals($qrCodePath1, $qrCodePath2);
        $this->assertFalse(Storage::disk('public')->exists($qrCodePath1));
        $this->assertTrue(Storage::disk('public')->exists($qrCodePath2));
    }
}
