<?php

namespace Database\Seeders;

use App\Models\Barber;
use App\Models\BarberSchedule;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin User ────────────────────────────────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@sisir.barber'],
            [
                'name'     => 'Admin SISIR',
                'password' => Hash::make('password'),
            ]
        );

        // ── Barber Users & Profiles ───────────────────────────────────────────
        $barberUser1 = User::firstOrCreate(
            ['email' => 'budi@sisir.barber'],
            ['name' => 'Budi Santoso', 'password' => Hash::make('password')]
        );

        $barberUser2 = User::firstOrCreate(
            ['email' => 'andi@sisir.barber'],
            ['name' => 'Andi Kurniawan', 'password' => Hash::make('password')]
        );

        $barber1 = Barber::firstOrCreate(
            ['user_id' => $barberUser1->id],
            [
                'nickname'         => 'Bang Budi',
                'bio'              => 'Kapster senior 10 tahun pengalaman. Spesialis fade & pompadour.',
                'capacity_per_slot' => 1,
                'is_active'        => true,
            ]
        );

        $barber2 = Barber::firstOrCreate(
            ['user_id' => $barberUser2->id],
            [
                'nickname'         => 'Kang Andi',
                'bio'              => 'Spesialis undercut modern dan desain rambut kreatif.',
                'capacity_per_slot' => 1,
                'is_active'        => true,
            ]
        );

        // ── Barber Schedules (Mon-Sat, 09:00-20:00) ───────────────────────────
        foreach ([$barber1, $barber2] as $barber) {
            foreach (range(1, 6) as $day) { // 1=Mon to 6=Sat
                BarberSchedule::firstOrCreate(
                    ['barber_id' => $barber->id, 'day_of_week' => $day],
                    ['open_time' => '09:00:00', 'close_time' => '20:00:00', 'is_active' => true]
                );
            }
        }

        // ── Services ──────────────────────────────────────────────────────────
        $services = [
            ['name' => 'Potong Rambut Biasa',    'duration_minutes' => 30, 'price' => 25000],
            ['name' => 'Fade & Taper',           'duration_minutes' => 45, 'price' => 40000],
            ['name' => 'Pompadour Styling',      'duration_minutes' => 60, 'price' => 60000],
            ['name' => 'Creambath & Treatment',  'duration_minutes' => 60, 'price' => 75000],
            ['name' => 'Cukur Jenggot',          'duration_minutes' => 20, 'price' => 20000],
            ['name' => 'Paket Lengkap (Potong + Jenggot + Creambath)', 'duration_minutes' => 90, 'price' => 100000],
        ];

        foreach ($services as $svc) {
            Service::firstOrCreate(
                ['name' => $svc['name']],
                array_merge($svc, ['is_active' => true])
            );
        }

        // ── App Settings ──────────────────────────────────────────────────────
        $settings = [
            ['key' => 'shop_name',   'value' => 'SISIR Barber',  'label' => 'Nama Toko',     'group' => 'general'],
            ['key' => 'shop_address','value' => 'Jl. Contoh No.1, Depok', 'label' => 'Alamat', 'group' => 'general'],
            ['key' => 'dp_amount',   'value' => '20000',          'label' => 'Jumlah DP (IDR)', 'group' => 'payment'],
            ['key' => 'slot_duration','value' => '30',            'label' => 'Durasi Slot (menit)', 'group' => 'booking'],
            ['key' => 'discount_recovery', 'value' => '15',       'label' => 'Diskon Slot Recovery (%)', 'group' => 'booking'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }

        $this->command->info('✅ SISIR Seeder complete!');
        $this->command->info('   Admin: admin@sisir.barber / password');
        $this->command->info('   Barbers: Bang Budi, Kang Andi');
        $this->command->info('   Services: ' . count($services) . ' layanan seeded');
    }
}
