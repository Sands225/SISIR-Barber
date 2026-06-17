<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Models\Barber;
use App\Models\BarberSchedule;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use App\Models\Waitlist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin User ────────────────────────────────────────────────────────
        User::firstOrCreate(
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
                'nickname'          => 'Bang Budi',
                'bio'               => 'Kapster senior 10 tahun pengalaman. Spesialis fade & pompadour.',
                'capacity_per_slot' => 1,
                'is_active'         => true,
            ]
        );

        $barber2 = Barber::firstOrCreate(
            ['user_id' => $barberUser2->id],
            [
                'nickname'          => 'Kang Andi',
                'bio'               => 'Spesialis undercut modern dan desain rambut kreatif.',
                'capacity_per_slot' => 1,
                'is_active'         => true,
            ]
        );

        // ── Barber Schedules (Mon–Sat, 09:00–20:00) ──────────────────────────
        foreach ([$barber1, $barber2] as $barber) {
            foreach (range(1, 6) as $day) {   // 1 = Monday … 6 = Saturday
                BarberSchedule::firstOrCreate(
                    ['barber_id' => $barber->id, 'day_of_week' => $day],
                    ['open_time' => '09:00:00', 'close_time' => '21:00:00', 'is_active' => true]
                );
            }
        }

        // ── Services ──────────────────────────────────────────────────────────
        $servicesData = [
            ['name' => 'Basic Haircut',                                  'duration_minutes' => 30, 'price' => 25000],
            ['name' => 'Haircut & Wash',                                 'duration_minutes' => 45, 'price' => 40000],
            ['name' => 'Full Treatment',                                 'duration_minutes' => 90, 'price' => 100000],
            ['name' => 'Pompadour Styling',                              'duration_minutes' => 60, 'price' => 60000],
            ['name' => 'Creambath & Treatment',                          'duration_minutes' => 60, 'price' => 75000],
            ['name' => 'Cukur Jenggot',                                  'duration_minutes' => 20, 'price' => 20000],
        ];

        $serviceModels = [];
        foreach ($servicesData as $svc) {
            $serviceModels[] = Service::firstOrCreate(
                ['name' => $svc['name']],
                array_merge($svc, ['is_active' => true])
            );
        }

        // ── App Settings ──────────────────────────────────────────────────────
        $settings = [
            ['key' => 'shop_name',        'value' => 'SISIR Barber',           'label' => 'Nama Toko',               'group' => 'general'],
            ['key' => 'shop_address',     'value' => 'Jl. Contoh No.1, Depok', 'label' => 'Alamat',                  'group' => 'general'],
            ['key' => 'dp_amount',        'value' => '20000',                  'label' => 'Jumlah DP (IDR)',          'group' => 'payment'],
            ['key' => 'slot_duration',    'value' => '30',                     'label' => 'Durasi Slot (menit)',      'group' => 'booking'],
            ['key' => 'discount_recovery','value' => '15',                     'label' => 'Diskon Slot Recovery (%)', 'group' => 'booking'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }

        // ── Dummy Customers ───────────────────────────────────────────────────
        $customerData = [
            ['name' => 'Reza Firmansyah', 'phone' => '6281234567001', 'wa_id' => '6281234567001'],
            ['name' => 'Dito Saputra',    'phone' => '6281234567002', 'wa_id' => '6281234567002'],
            ['name' => 'Yusuf Hakim',     'phone' => '6281234567003', 'wa_id' => '6281234567003'],
            ['name' => 'Fajar Nugroho',   'phone' => '6281234567004', 'wa_id' => '6281234567004'],
            ['name' => 'Rizky Pratama',   'phone' => '6281234567005', 'wa_id' => '6281234567005'],
            ['name' => 'Bagas Wicaksono', 'phone' => '6281234567006', 'wa_id' => '6281234567006'],
            ['name' => 'Galih Adrianto',  'phone' => '6281234567007', 'wa_id' => '6281234567007'],
            ['name' => 'Hendra Kusuma',   'phone' => '6281234567008', 'wa_id' => '6281234567008'],
        ];

        $customers = [];
        foreach ($customerData as $cd) {
            $customers[] = Customer::firstOrCreate(
                ['phone' => $cd['phone']],
                array_merge($cd, ['conversation_state' => 'idle'])
            );
        }

        // ── Dummy Bookings ────────────────────────────────────────────────────
        // [customer_index, barber_index, service_index, day_offset, hour, BookingStatus]
        $barbers = [$barber1, $barber2];
        $today   = Carbon::today('Asia/Jakarta');
        $dp      = 20000;

        $scenarios = [
            // ── Today ───────────────────────────────────────────────────
            [0, 0, 0,  0,  9, BookingStatus::BOOKED],           // Reza   – Potong Biasa   – 09:00 – BOOKED
            [1, 0, 1,  0, 10, BookingStatus::CONFIRMED],        // Dito   – Fade & Taper   – 10:00 – CONFIRMED
            [2, 1, 2,  0,  9, BookingStatus::IN_SERVICE],       // Yusuf  – Pompadour      – 09:00 – IN_SERVICE
            [3, 1, 4,  0, 11, BookingStatus::TEMP_LOCKED],      // Fajar  – Cukur Jenggot  – 11:00 – TEMP_LOCKED
            [4, 0, 5,  0, 14, BookingStatus::BOOKED],           // Rizky  – Paket Lengkap  – 14:00 – BOOKED
            [5, 1, 3,  0, 15, BookingStatus::BOOKED],           // Bagas  – Creambath      – 15:00 – BOOKED
            // ── Yesterday ───────────────────────────────────────────────
            [6, 0, 0, -1, 10, BookingStatus::COMPLETED],        // Galih  – Potong Biasa   – COMPLETED
            [7, 1, 1, -1, 11, BookingStatus::COMPLETED],        // Hendra – Fade & Taper   – COMPLETED
            [0, 0, 3, -1, 13, BookingStatus::COMPLETED],        // Reza   – Creambath      – COMPLETED
            [1, 1, 4, -1, 16, BookingStatus::NO_SHOW],          // Dito   – Jenggot        – NO_SHOW
            // ── 2 days ago ──────────────────────────────────────────────
            [2, 0, 1, -2,  9, BookingStatus::COMPLETED],        // Yusuf  – Fade & Taper   – COMPLETED
            [3, 0, 5, -2, 14, BookingStatus::CANCELLED_BY_SYSTEM], // Fajar – Paket        – CANCELLED
            // ── 3 days ago ──────────────────────────────────────────────
            [4, 1, 2, -3, 10, BookingStatus::COMPLETED],        // Rizky  – Pompadour      – COMPLETED
            [5, 0, 0, -3, 12, BookingStatus::COMPLETED],        // Bagas  – Potong Biasa   – COMPLETED
            [6, 1, 3, -3, 15, BookingStatus::COMPLETED],        // Galih  – Creambath      – COMPLETED
            // ── 5 days ago ──────────────────────────────────────────────
            [7, 0, 4, -5, 10, BookingStatus::COMPLETED],        // Hendra – Jenggot        – COMPLETED
            [0, 1, 1, -5, 13, BookingStatus::COMPLETED],        // Reza   – Fade & Taper   – COMPLETED
        ];

        $bookingsCreated = 0;
        foreach ($scenarios as [$custIdx, $barberIdx, $svcIdx, $dayOffset, $hour, $status]) {
            $customer    = $customers[$custIdx];
            $barber      = $barbers[$barberIdx];
            $service     = $serviceModels[$svcIdx];
            $scheduledAt = $today->copy()->addDays($dayOffset)->setTime($hour, 0, 0);

            // Idempotent: skip if this slot already exists
            if (Booking::where('customer_id', $customer->id)
                ->where('barber_id', $barber->id)
                ->where('scheduled_at', $scheduledAt)
                ->exists()) {
                continue;
            }

            Booking::create([
                'customer_id'    => $customer->id,
                'barber_id'      => $barber->id,
                'service_id'     => $service->id,
                'scheduled_at'   => $scheduledAt,
                'status'         => $status->value,
                'dp_amount'      => $dp,
                'lock_expires_at'=> $status === BookingStatus::TEMP_LOCKED
                    ? now()->addMinutes(10)
                    : null,
            ]);

        }
        
        // Seed conflict bookings for today at 19:00, 19:30, and 20:00 for both barbers to trigger renegotiation scenario
        $conflictTimes = ['19:00:00', '19:30:00', '20:00:00'];
        foreach ($barbers as $bIdx => $barber) {
            foreach ($conflictTimes as $tIdx => $timeStr) {
                $custIdx = ($bIdx * 3 + $tIdx) % count($customers);
                $customer = $customers[$custIdx];
                $service = $serviceModels[0]; // Basic Haircut
                $scheduledAt = Carbon::today('Asia/Jakarta')->setTimeFromTimeString($timeStr);

                Booking::firstOrCreate(
                    [
                        'barber_id' => $barber->id,
                        'scheduled_at' => $scheduledAt,
                    ],
                    [
                        'customer_id' => $customer->id,
                        'service_id' => $service->id,
                        'status' => BookingStatus::BOOKED->value,
                        'dp_amount' => $dp,
                    ]
                );

                $bookingsCreated++;
            }
        }

        // ── Dummy Waitlist entries ────────────────────────────────────────────
        Waitlist::firstOrCreate(
            ['customer_id' => $customers[3]->id, 'is_active' => true],
            ['preferred_date' => $today->toDateString()]
        );
        Waitlist::firstOrCreate(
            ['customer_id' => $customers[5]->id, 'is_active' => true],
            ['preferred_date' => $today->copy()->addDay()->toDateString()]
        );

        $this->command->info('✅ SISIR Seeder complete!');
        $this->command->info('   Admin  : admin@sisir.barber / password');
        $this->command->info('   Barbers: Bang Budi, Kang Andi');
        $this->command->info('   Services  : ' . count($servicesData)  . ' layanan seeded');
        $this->command->info('   Customers : ' . count($customers)     . ' pelanggan dummy seeded');
        $this->command->info('   Bookings  : ' . $bookingsCreated      . ' booking dummy seeded');
    }
}
