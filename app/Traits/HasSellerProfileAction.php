<?php
namespace App\Traits;

use App\Forms\Components\ProfileContainer;
use App\Models\Seller;
use Filament\Forms\Components\ViewField;
use Illuminate\Database\Eloquent\Model;

trait HasSellerProfileAction
{
    public static function getDefaultName(): ?string
    {
        return 'seller-profile-action';
    }

    protected function setUp() :void
    {
        parent::setUp();

        $this->label('Seller Profile')
            ->modalHeading(false)
            ->icon('heroicon-o-user')
            ->label(__('seller.seller-profile'))
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->slideOver()
            ->extraModalWindowAttributes([
                'class' => 'modal-without-padding',
            ])
            ->modalContent(function ( $record) {
                $seller = $record instanceof Seller ? $record : $record->seller;
                return ProfileContainer::make('box')
                    ->seller($seller);
            });
    }



}
