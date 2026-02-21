<?php

namespace App\Livewire;

use App\Enums\ResponseStatus;
use App\Events\RefreshRequestEvent;
use App\Filament\Actions\PageSellerProfileAction;
use App\Filament\Actions\TableSellerProfileAction;
use App\Models\Rating;
use App\Models\Request;
use App\Models\SellerService;
use App\Services\RequestServiceHandler;
use App\Settings\GeneralSettings;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Seller;
use Filament\Notifications\Notification;

#[On('RefreshMatchingSellers')]
class ShowMatchingSellers extends Component implements HasForms, HasActions , HasTable
{
    use InteractsWithTable;
    use InteractsWithActions;
    use InteractsWithForms;

    public ?Request $request;
    public $sortByRating = null; // 'asc' for least to greatest, 'desc' for greatest to least
    public $sortByDistance = null; // 'asc' for nearest to farthest, 'desc' for farthest to nearest

    public function mount(Request | Model | null $record)
    {
        $this->request = $record;


    }

    public function getTableQuery() : Builder
    {
        return Seller::getMatchingSeller($this->request);
    }

    public function table(Table $table): Table
    {
        $query = $this->getTableQuery();
        return $table
            ->query($this->getTableQuery())
            ->pluralModelLabel(__('string.matching_sellers'))
            ->columns([
                Stack::make([
                       ViewColumn::make('name')->view('livewire.matching-sellers.seller-component')
                    ->viewData(['fast_response_badge' => app(GeneralSettings::class)->fast_response_badge]),
                ])->extraAttributes([
                    'class' => 'sellerContainer'
                ])->space(2)
            ])
            ->contentGrid([
                'md' => 1,
                'lg' => 2,
                'xl' => 3,
            ])
            ->actions([
                TableSellerProfileAction::make()->button()->extraAttributes([
                    'style' => 'width:50%; height:3rem;  border-start-start-radius: 0;
                                border-start-end-radius: 0;
                                border-end-end-radius: 0;',
                ]),
                \Filament\Tables\Actions\Action::make('Invite')
                    ->label(__('string.invite'))
                    ->button()->extraAttributes([
                        'style' => 'width:50%; height:3rem; border-start-start-radius: 0;
                                border-start-end-radius: 0;
                                border-end-start-radius: 0;',
                    ])->requiresConfirmation()
                    ->modalIcon('icon-invite')
                    ->color('success')
                    ->modalDescription(fn()=> new HtmlString('<span class="text-lg">'. __('string.seller_invitation') .'</span>'))
                    ->successNotificationTitle(__('string.invitation_sent'))
                    ->action(function($record,$action){
                        if($this->request->responses()->where('seller_id', $record->id)->count() == 0){
                            $this->request->responses()->create([
                                'seller_id' => $record->id,
                                'status' => ResponseStatus::Invited,
                                'service_id' => $this->request->service_id
                            ]);
                            broadcast(new RefreshRequestEvent([$record->id]));
                            $action->success();
                        };
                    })->after(fn($livewire) => $livewire->dispatch('refreshViewRequest'))
            ]);
    }

    public function render()
    {
        return view('livewire.matching-sellers.index');
    }
}
