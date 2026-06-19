<?php

namespace Tests\Feature;

use App\Models\Barber;
use App\Models\BarberSchedule;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test Settings index page is accessible when authenticated.
     */
    public function test_settings_page_is_accessible(): void
    {
        $response = $this->actingAs($this->user)->get(route('sisir.settings'));
        $response->assertStatus(200);
        $response->assertViewIs('sisir.settings');
    }

    /**
     * Test updating operational settings with valid data.
     */
    public function test_update_operational_settings_success(): void
    {
        $data = [
            'shop_name'       => 'Barber Baru',
            'shop_address'    => 'Jl. Baru No. 10',
            'whatsapp_number' => '62899999999',
            'chairs_count'    => 5,
            'slot_duration'   => 40, // 40 minutes is kelipatan 10 & >= 30
            'opening_time'    => '10:00',
            'closing_time'    => '20:00',
            'dp_amount'       => 20,
        ];

        $response = $this->actingAs($this->user)
            ->post(route('sisir.settings.operational'), $data);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertEquals('Barber Baru', Setting::get('shop_name'));
        $this->assertEquals(5, Setting::get('chairs_count'));
        $this->assertEquals(40, Setting::get('slot_duration'));
    }

    /**
     * Test updating operational settings fails if slot duration is not divisible by 10.
     */
    public function test_update_operational_settings_fails_invalid_duration(): void
    {
        $data = [
            'shop_name'       => 'Barber Baru',
            'shop_address'    => 'Jl. Baru No. 10',
            'whatsapp_number' => '62899999999',
            'chairs_count'    => 5,
            'slot_duration'   => 35, // Not divisible by 10
            'opening_time'    => '10:00',
            'closing_time'    => '20:00',
            'dp_amount'       => 20,
        ];

        $response = $this->actingAs($this->user)
            ->post(route('sisir.settings.operational'), $data);

        $response->assertSessionHasErrors(['slot_duration']);
    }

    /**
     * Test adding a new barber.
     */
    public function test_create_barber_success(): void
    {
        $data = [
            'nickname' => 'Ali',
            'bio'      => 'Ahli cukur premium',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('sisir.settings.barbers'), $data);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $barber = Barber::where('nickname', 'Ali')->first();
        $this->assertNotNull($barber);
        $this->assertEquals('Ahli cukur premium', $barber->bio);
        $this->assertTrue($barber->is_active);

        // Verify 7 daily schedules were seeded
        $schedulesCount = BarberSchedule::where('barber_id', $barber->id)->count();
        $this->assertEquals(7, $schedulesCount);
    }

    /**
     * Test adding a new service with valid data.
     */
    public function test_create_service_success(): void
    {
        $data = [
            'name'             => 'Pijat Kepala',
            'description'      => 'Pijat kepala rileks',
            'duration_minutes' => 30, // divisible by 10 & >= 30
            'price'            => 25000,
        ];

        $response = $this->actingAs($this->user)
            ->post(route('sisir.settings.services'), $data);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $service = Service::where('name', 'Pijat Kepala')->first();
        $this->assertNotNull($service);
        $this->assertEquals(30, $service->duration_minutes);
        $this->assertEquals(25000, $service->price);
    }

    /**
     * Test adding a new service fails if duration is not divisible by 10.
     */
    public function test_create_service_fails_invalid_duration(): void
    {
        $data = [
            'name'             => 'Pijat Kepala',
            'description'      => 'Pijat kepala rileks',
            'duration_minutes' => 35, // invalid
            'price'            => 25000,
        ];

        $response = $this->actingAs($this->user)
            ->post(route('sisir.settings.services'), $data);

        $response->assertSessionHasErrors(['duration_minutes']);
    }
}
