<?php
namespace App\Filament\Actions;



use App\Traits\HasSellerProfileAction;
use Filament\Infolists\Components\Actions\Action;
use Filament\Forms\Components\ViewField;
use Illuminate\Database\Eloquent\Model;




class SellerProfileAction extends Action
{

    use HasSellerProfileAction;

}
