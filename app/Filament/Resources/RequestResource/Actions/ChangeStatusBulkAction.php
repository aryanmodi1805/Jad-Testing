<?php

namespace App\Filament\Resources\RequestResource\Actions;

use App\Enums\RequestStatus;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Filament\Forms\Components\Select;

class ChangeStatusBulkAction extends BulkAction
{
    protected function setUp(): void
    {
        parent::setUp();


        $this->name('changeStatus');
           $this->label(__('requests.Change Status'));
           $this->form([
                Select::make('status')
                    ->label('Status')
                    ->options(
                        RequestStatus::class
                    )
                    ->required(),
            ]);

        $this->successNotificationTitle(__('requests.Status Changed'));

        $this->color('warning');

        $this->icon('heroicon-m-rectangle-group')
            ->action(function (Collection $records, array $data): void {
                foreach ($records as $record) {
                    $record->update(['status' => $data['status']]);
                }
                $this->success();

            });
        $this->deselectRecordsAfterCompletion();
            $this->requiresConfirmation();
    }
}
