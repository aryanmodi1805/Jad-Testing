<?php
namespace App\Filament\Actions;

use App\Forms\Components\ProfileContainer;
use App\Models\Seller;
use App\Traits\HasSellerProfileAction;
use Filament\Tables\Actions\Action;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;

class TableSellerProfileAction extends Action
{
    use HasSellerProfileAction;
}
