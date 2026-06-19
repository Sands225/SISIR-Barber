<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Slot Kosong</title>
    <!-- Outfit Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #0f172a;
            --card-bg: #1e293b;
            --green-primary: #10b981;
            --green-soft: rgba(16, 185, 129, 0.15);
            --red-primary: #f43f5e;
            --red-soft: rgba(244, 63, 94, 0.15);
            --gray-text: #94a3b8;
            --white-text: #f8fafc;
            --border-color: #334155;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-dark);
            color: var(--white-text);
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 20px;
            width: 480px;
            margin: 0 auto;
        }

        .schedule-card {
            background-color: var(--card-bg);
            border-radius: 24px;
            width: 100%;
            padding: 24px;
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .header {
            text-align: center;
            margin-bottom: 24px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 16px;
        }

        .shop-badge {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            font-weight: 800;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 6px 14px;
            border-radius: var(--radius-full, 50px);
            display: inline-block;
            margin-bottom: 8px;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
        }

        .title {
            font-size: 22px;
            font-weight: 800;
            color: var(--white-text);
            margin-bottom: 4px;
        }

        .date-subtitle {
            font-size: 13px;
            color: var(--gray-text);
            font-weight: 500;
        }

        .grid-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        th {
            color: var(--gray-text);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 8px 12px;
            text-align: center;
        }

        th.time-col {
            text-align: left;
            width: 80px;
        }

        .slot-row td {
            padding: 10px 8px;
            vertical-align: middle;
        }

        .time-box {
            font-size: 14px;
            font-weight: 700;
            color: var(--white-text);
            background: rgba(255, 255, 255, 0.05);
            padding: 8px 10px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            display: inline-block;
        }

        .status-pill {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 700;
            padding: 8px 12px;
            border-radius: 12px;
            text-align: center;
            transition: all 0.2s ease;
        }

        .status-available {
            background-color: var(--green-soft);
            color: var(--green-primary);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .status-booked {
            background-color: var(--red-soft);
            color: var(--red-primary);
            border: 1px solid rgba(244, 63, 94, 0.3);
        }

        .status-icon {
            width: 12px;
            height: 12px;
            fill: currentColor;
        }

        .footer {
            margin-top: 24px;
            text-align: center;
            font-size: 11px;
            color: var(--gray-text);
            border-top: 1px solid var(--border-color);
            padding-top: 16px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="schedule-card">
        <div class="header">
            <span class="shop-badge">{{ $shopName }}</span>
            <h1 class="title">Jadwal Slot Barber</h1>
            <p class="date-subtitle">{{ $formattedDate }}</p>
        </div>

        <table class="grid-table">
            <thead>
                <tr>
                    <th class="time-col">Waktu</th>
                    @foreach(array_keys($scheduleData) as $barberName)
                        <th>{{ explode(' ', $barberName)[0] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($timeKeys as $time)
                    <tr class="slot-row">
                        <td>
                            <div class="time-box">{{ $time }}</div>
                        </td>
                        @foreach($scheduleData as $barberName => $slots)
                            @php
                                $slot = collect($slots)->firstWhere('time', $time);
                                $isAvailable = $slot && ($slot['available'] ?? false);
                            @endphp
                            <td>
                                @if($isAvailable)
                                    <div class="status-pill status-available">
                                        <svg class="status-icon" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        Tersedia
                                    </div>
                                @else
                                    <div class="status-pill status-booked">
                                        <svg class="status-icon" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                        Penuh
                                    </div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            <p>Silakan pilih hari & jam lalu konfirmasi pesanan Anda via WhatsApp bot. ✂️</p>
        </div>
    </div>
</body>
</html>
