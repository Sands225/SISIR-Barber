<?php

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TodayBookingsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Reservasi Hari Ini';

    // Live-poll every 5 seconds for real-time updates
    protected static ?string $pollingInterval = '5s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Booking::with(['customer', 'barber', 'service'])
                    ->whereDate('scheduled_at', today())
                    ->orderBy('scheduled_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Jam')
                    ->time('H:i')
                    ->timezone('Asia/Jakarta'),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Pelanggan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('barber.nickname')
                    ->label('Kapster'),

                Tables\Columns\TextColumn::make('service.name')
                    ->label('Layanan'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => BookingStatus::from($state)->label())
                    ->color(fn ($state) => BookingStatus::from($state)->color()),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Booking $record) => route('filament.admin.resources.bookings.edit', $record)),
            ])
            ->emptyStateHeading('Tidak ada reservasi hari ini')
            ->emptyStateDescription('Walk-in atau booking online akan muncul di sini.')
            ->emptyStateIcon('heroicon-o-calendar');
    }
}
