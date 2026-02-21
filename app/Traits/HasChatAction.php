<?php

namespace App\Traits;

use App\Enums\ResponseStatus;
use App\Filament\Actions\FormEstimateAction;
use App\Models\Customer;
use App\Models\EstimateBase;
use App\Models\Response;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

trait HasChatAction
{
    public static function getDefaultName(): ?string
    {
        return 'chat-action';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('labels.go_to_chat'))
            ->modalHeading(function ($record) {
                // Check if the record exists and has necessary relationships
                if (!$record || !$record->exists) {
                    return __('labels.chat');
                }

                // Ensure the record is loaded with necessary relationships
                if ($record instanceof Response && !$record->relationLoaded('request')) {
                    $record->load(['request', 'request.customer']);
                }

                return new HtmlString(
                    Blade::render('components.chat.chat-header', [
                        'selectedConversation' => $record,
                        'currentUser' => Filament::auth()->user(),
                    ])
                );
            })
            ->icon('heroicon-o-chat-bubble-left-right')
            ->modalSubmitAction(false)
            ->modalCancelAction(fn($action, $record) => $action->label(__('labels.close')))
            ->color('success')
            ->slideOver()
            ->extraModalWindowAttributes([
                'class' => 'modal-without-padding',
            ])
            ->badge(function($record) {
                if (!$record) return null;

                $count = $record?->messages?->whereNull('read_at')
                    ?->where('sender_id', '!=', Filament::auth()->id())?->count() ?? 0;
                return $count > 0 ? $count : null;
            })
            ->badgeColor('warning')
            ->hidden(fn($record) => !$record || $record->status == ResponseStatus::Invited)
            ->modalContent(function (Model $record) {
                return ViewField::make('box')->view('forms.components.list-box')->viewData([
                    'record' => $record,
                ]);
            });
    }
}
