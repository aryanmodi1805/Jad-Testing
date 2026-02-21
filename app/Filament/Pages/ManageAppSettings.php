<?php

namespace App\Filament\Pages;

use App\Settings\AppSettings;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Illuminate\Contracts\Support\Htmlable;

class ManageAppSettings extends SettingsPage
{
    use HasPageShield;


    public function deletePayment($id)
    {
        $payment = \App\Models\PendingPayment::find($id);
        
        if ($payment) {
            $payment->delete();
            
            \Filament\Notifications\Notification::make()
                ->title('Payment deleted successfully')
                ->success()
                ->send();
        }
    }

    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';

    protected static string $settings = AppSettings::class;

    public function getTitle(): string|Htmlable
    {
        return __('settings.app_settings.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('settings.app_settings.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('nav.settings');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('settings.app_settings.ios_settings'))
                    ->schema([
//                        Forms\Components\Toggle::make('ios_app_active')
//                            ->label(__('settings.app_settings.ios_app_active'))
//                            ->onColor('success')
//                            ->offColor('danger')
//                            ->inline(false)
//                            ->onIcon('heroicon-o-check')
//                            ->offIcon('heroicon-o-x-circle')
//                            ->helperText(__('settings.app_settings.ios_app_active_help')),

                        Forms\Components\TextInput::make('ios_min_app_version')
                            ->label(__('settings.app_settings.ios_min_app_version'))
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->helperText(__('settings.app_settings.min_version_help')),
                    ]),

                Forms\Components\Section::make(__('settings.app_settings.android_settings'))
                    ->schema([
//                        Forms\Components\Toggle::make('android_app_active')
//                            ->label(__('settings.app_settings.android_app_active'))
//                            ->onColor('success')
//                            ->offColor('danger')
//                            ->inline(false)
//                            ->onIcon('heroicon-o-check')
//                            ->offIcon('heroicon-o-x-circle')
//                            ->helperText(__('settings.app_settings.android_app_active_help')),

                        Forms\Components\TextInput::make('android_min_app_version')
                            ->label(__('settings.app_settings.android_min_app_version'))
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->helperText(__('settings.app_settings.min_version_help')),
                    ]),

                Forms\Components\Section::make(__('settings.app_settings.seller_settings'))
                    ->schema([
                        Forms\Components\TextInput::make('minimum_seller_wallet_balance')
                            ->label(__('settings.app_settings.minimum_seller_wallet_balance'))
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->default(0)
                            ->helperText(__('settings.app_settings.minimum_seller_wallet_balance_help')),

                        Forms\Components\TextInput::make('maximum_requests_per_day')
                            ->label('Maximum Requests Per Day')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(5)
                            ->helperText('Maximum number of requests a seller can connect to in 24 hours.'),

                        Forms\Components\TextInput::make('max_open_pending_requests')
                            ->label('Max Open/Pending Requests')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(2)
                            ->helperText('Maximum number of open or pending requests a seller can have before being blocked from connecting to new customers.'),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('view_target_payments')
                ->label('View Target Payments')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->modalHeading('Payments Eligible for Fetching')
                ->modalWidth('7xl')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->modalContent(function () {
                    $payments = \App\Models\PendingPayment::whereIn('status', ['pending', 'failed', 'expired', 'verifying'])
                        ->where('expires_at', '>', now()->subDays(30))
                        ->with('user')
                        ->latest()
                        ->get();

                    return view('filament.pages.manage-app-settings.components.target-payments-list', [
                        'payments' => $payments,
                    ]);
                }),
            \Filament\Actions\Action::make('fetch_failed_payments')
                ->label('Fetch Failed Payments')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Fetch Failed Payments from Tap')
                ->modalDescription('This will check all pending and failed payments with Tap and update their status. This may take a few moments.')
                ->modalSubmitActionLabel('Fetch Now')
                ->action(function () {
                    $pendingPayments = \App\Models\PendingPayment::whereIn('status', ['pending', 'failed', 'expired', 'verifying'])
                        ->where('expires_at', '>', now()->subDays(30)) // Increased look-back to 30 days
                        ->get();

                    $processed = 0;

                    foreach ($pendingPayments as $payment) {
                        // Revive payments that are expired, failed, or stuck in verifying for too long
                        $isStuckVerifying = $payment->status === 'verifying' && $payment->last_verified_at && $payment->last_verified_at->diffInMinutes() > 10;
                        
                        if ($payment->status === 'expired' || $payment->status === 'failed' || $isStuckVerifying) {
                            $payment->update([
                                'status' => 'pending',
                                'expires_at' => now()->addMinutes(10),
                                'verification_attempts' => 0 
                            ]);
                        }

                        // Dispatch verification job immediately
                        \App\Jobs\VerifyPendingPayment::dispatch($payment->charge_id);
                        $processed++;
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('Payment Verification Triggered')
                        ->body("Dispatched verification for {$processed} payments (including pending, failed, expired, and stuck records).")
                        ->success()
                        ->send();
                }),
        ];
    }
}
