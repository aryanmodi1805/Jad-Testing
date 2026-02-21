<?php
namespace App\Filament\Actions;

use Closure;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;

class BlockAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'BlockAction';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(fn (Model $record) => $record->blocked ? __('columns.unblock') : __('columns.block'))
            ->icon(fn (Model $record) => $record->blocked ? 'heroicon-o-lock-open' : 'heroicon-o-lock-closed')
            ->color(fn (Model $record) => $record->blocked ? 'success' : 'danger')
            ->action(function (Model $record): void {
                $record->update(['blocked' => ! $record->blocked]);
            });
    }
}
