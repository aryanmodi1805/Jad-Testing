<?php

namespace App\Filament\Seller\Resources;

use App\Enums\RequestStatus;
use App\Enums\ResponseStatus;
use App\Filament\Seller\Resources\BlockReportResource\Pages;
use App\Filament\Customer\Resources\BlockReportResource\RelationManagers;
use App\Models\BlockReport;
use App\Models\Request;
use App\Models\Response;
use App\Models\Seller;
use App\Services\RequestService;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class SellerBlockReportResource extends Resource
{
    protected static ?string $model = BlockReport::class;

    protected static bool $shouldRegisterNavigation = false;
    protected static bool $shouldSkipAuthorization = true;

    public static function getPluralLabel(): ?string
    {
        return __('labels.blocked_users');
    }

    public static function getModelLabel(): string
    {
        return __('string.block_reports.singular') ;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading(__('string.block_reports.empty'))
            ->columns([
                Tables\Columns\TextColumn::make('blocked.name')
                    ->label(__('labels.name'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('blockReason.name')
                    ->label(__('labels.block_reason'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('labels.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([

            ])
            ->actions([
                Tables\Actions\DeleteAction::make()->label(__('string.unblock'))
                    ->before(function(?BlockReport $record) {

                        $reference = $record->reference;

                        if($reference instanceof Response){
                            /*  @var  $reference Response*/
                            /*  @var  $request Request*/
                            $request = $reference->request;

                            if($request->status == RequestStatus::Open){
                                $reference->update([
                                    'status' => ResponseStatus::Pending
                                ]);
                            }
                        }

                        return null;
                    }),

            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSellerBlockReports::route('/'),
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('blocker_type',Seller::class)->where('blocker_id',Filament::auth()->id())->latest() ;
    }
}


