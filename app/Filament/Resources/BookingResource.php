<?php

namespace App\Filament\Resources;

use App\Enums\BookingStatus;
use App\Filament\Resources\BookingResource\Pages;
use App\Jobs\SlotRecoveryBroadcastJob;
use App\Models\Booking;
use App\Services\CapacityEngine;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Reservasi';
    protected static ?string $modelLabel = 'Reservasi';
    protected static ?string $pluralModelLabel = 'Semua Reservasi';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Pelanggan')
                ->schema([
                    Forms\Components\Select::make('customer_id')
                        ->relationship('customer', 'name')
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('barber_id')
                        ->relationship('barber', 'nickname')
                        ->required(),
                    Forms\Components\Select::make('service_id')
                        ->relationship('service', 'name')
                        ->required(),
                ])->columns(3),

            Forms\Components\Section::make('Jadwal & Status')
                ->schema([
                    Forms\Components\DateTimePicker::make('scheduled_at')
                        ->required()
                        ->timezone('Asia/Jakarta'),
                    Forms\Components\Select::make('status')
                        ->options(collect(BookingStatus::cases())->mapWithKeys(
                            fn ($case) => [$case->value => $case->label()]
                        ))
                        ->required(),
                    Forms\Components\TextInput::make('dp_amount')
                        ->numeric()
                        ->prefix('Rp')
                        ->default(config('sisir.dp_amount')),
                ])->columns(3),

            Forms\Components\Section::make('Catatan')
                ->schema([
                    Forms\Components\Textarea::make('notes')->rows(2),
                    Forms\Components\Textarea::make('cancellation_reason')->rows(2),
                ])->columns(2)->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('# ID')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('barber.nickname')
                    ->label('Kapster')
                    ->sortable(),

                Tables\Columns\TextColumn::make('service.name')
                    ->label('Layanan')
                    ->sortable(),

                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Jadwal')
                    ->dateTime('D, d M Y H:i')
                    ->timezone('Asia/Jakarta')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => BookingStatus::from($state)->label())
                    ->color(fn ($state) => BookingStatus::from($state)->color()),

                Tables\Columns\TextColumn::make('dp_amount')
                    ->label('DP')
                    ->money('IDR', locale: 'id'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('scheduled_at', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(BookingStatus::cases())->mapWithKeys(
                        fn ($case) => [$case->value => $case->label()]
                    ))
                    ->multiple(),

                Tables\Filters\Filter::make('today')
                    ->label('Hari Ini')
                    ->query(fn ($query) => $query->whereDate('scheduled_at', today())),
            ])
            ->actions([
                // Mark In Service
                Tables\Actions\Action::make('in_service')
                    ->label('Mulai Layani')
                    ->icon('heroicon-o-scissors')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Booking $record) => $record->status === BookingStatus::CONFIRMED)
                    ->action(function (Booking $record) {
                        $record->transitionTo(BookingStatus::IN_SERVICE);
                        Notification::make()->title('Status diperbarui ke Sedang Dilayani')->success()->send();
                    }),

                // Mark Completed
                Tables\Actions\Action::make('complete')
                    ->label('Selesai')
                    ->icon('heroicon-o-check-circle')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->visible(fn (Booking $record) => $record->status === BookingStatus::IN_SERVICE)
                    ->action(function (Booking $record) {
                        $record->transitionTo(BookingStatus::COMPLETED);
                        Notification::make()->title('Booking selesai ✅')->success()->send();
                    }),

                // Mark No Show
                Tables\Actions\Action::make('no_show')
                    ->label('Tidak Hadir')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Booking $record) => in_array($record->status, [
                        BookingStatus::CONFIRMED, BookingStatus::IN_SERVICE,
                    ]))
                    ->action(function (Booking $record, CapacityEngine $capacity) {
                        $record->transitionTo(BookingStatus::NO_SHOW);
                        $capacity->releaseSlot($record->id, $record->barber_id, $record->scheduled_at);

                        SlotRecoveryBroadcastJob::dispatch(
                            $record->scheduled_at->toIso8601String(),
                            20,
                            $record->service_id
                        )->onQueue('broadcasts');

                        Notification::make()->title('Ditandai Tidak Hadir. Slot Recovery dikirim.')->warning()->send();
                    }),

                // 1-Click Walk-In
                Tables\Actions\Action::make('walk_in')
                    ->label('Walk-In')
                    ->icon('heroicon-o-user-plus')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('barber_id')
                            ->label('Kapster')
                            ->relationship('barber', 'nickname')
                            ->required(),
                        Forms\Components\Select::make('service_id')
                            ->label('Layanan')
                            ->relationship('service', 'name')
                            ->required(),
                        Forms\Components\DateTimePicker::make('scheduled_at')
                            ->label('Waktu')
                            ->default(now()->timezone('Asia/Jakarta'))
                            ->required(),
                        Forms\Components\TextInput::make('customer_name')
                            ->label('Nama Pelanggan')
                            ->default('Walk-in Customer')
                            ->required(),
                    ])
                    ->action(function (array $data, CapacityEngine $capacity) {
                        $result = $capacity->registerWalkIn(
                            $data['barber_id'],
                            \Illuminate\Support\Carbon::parse($data['scheduled_at']),
                            $data['service_id'],
                            $data['customer_name']
                        );

                        if ($result === false) {
                            Notification::make()->title('Slot sudah terisi!')->danger()->send();
                            return;
                        }

                        Notification::make()->title("Walk-in #{$result->id} berhasil dicatat.")->success()->send();
                    }),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->poll('5s'); // Real-time refresh every 5 seconds
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit'   => Pages\EditBooking::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::whereDate('scheduled_at', today())
            ->whereNotIn('status', ['COMPLETED', 'CANCELLED_BY_SYSTEM', 'NO_SHOW'])
            ->count();
    }
}
