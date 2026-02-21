<?php

namespace App\Filament\Customer\Pages;

use App\Models\Rating;
use App\Models\Seller;
use App\Models\SellerReview;
use App\Models\SellerSocialMedia;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Livewire\Attributes\Url;
use Mokhosh\FilamentRating\Entries\RatingEntry;

class SellerInfo extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.customer.pages.seller-info';

   protected static bool $shouldRegisterNavigation = false;
    #[Url]
    public $sellerId;
    public $seller;

    public function mount()
    {
        $this->seller = Seller::with(['ratings', 'socialMedia'])->find($this->sellerId);
    }

    public function infolist(): Infolist
    {
        return Infolist::make()
            ->record($this->seller)
            ->schema([
                Section::make('Seller Information')
                    ->description('Detailed information about the seller')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                ImageEntry::make('avatar_url')
                                    ->label('Avatar')
                                    ->disk('public')
                                    ->defaultImageUrl(url('/images/default-avatar.png'))
                                    ->circular()
                                    ->size(100),

                                TextEntry::make('name')
                                    ->label('Name')
                                    ->state(fn ($record) => $record->name)
                                    ->icon('heroicon-o-user')
                                    ->weight('bold'),

                                TextEntry::make('email')
                                    ->label('Email')
                                    ->state(fn ($record) => $record->email)
                                    ->copyable()
                                    ->tooltip('Click to copy email'),

                                TextEntry::make('company_name')
                                    ->label('Company Name')
                                    ->state(fn ($record) => $record->company_name)
                                    ->icon('heroicon-o-briefcase'),

                                TextEntry::make('company_description')
                                    ->label('Company Description')
                                    ->state(fn ($record) => $record->company_description)
                                    ->markdown()
                                    ->columnSpan(2),

                                TextEntry::make('years_in_business')
                                    ->label('Years in Business')
                                    ->state(fn ($record) => $record->years_in_business)
                                    ->badge()
                                    ->color('primary'),

                                TextEntry::make('location')
                                    ->label('Location')
                                    ->state(fn ($record) => $record->location)
                                    ->icon('heroicon-o-location-marker'),

                                TextEntry::make('website')
                                    ->label('Website')
                                    ->state(fn ($record) => $record->website)
                                    ->url(fn ($record) => $record->website)
                                    ->openUrlInNewTab()
                                    ->icon('heroicon-o-globe-alt'),

                                TextEntry::make('company_size.name')
                                    ->label('Company Size')
                                    ->state(fn ($record) => optional($record->companySize)->name)
                                    ->icon('heroicon-o-office-building')
                            ]),
                    ]),

                Section::make('Social Media')
                    ->description('Social media links of the seller')
                    ->schema([
                        Grid::make(2)
                            ->schema(array_map(function ($socialMedia) {
                                return TextEntry::make("socialMedia.{$socialMedia->platform}")
                                    ->label(ucfirst($socialMedia->platform))
                                    ->state(fn () => $socialMedia->link)
                                    ->url(fn () => $socialMedia->link)
                                    ->openUrlInNewTab()
                                    ->icon('heroicon-o-globe-alt') ;
                            }, $this->seller->socialMedia->all()))
                    ]),

                Section::make('Services')
                    ->description('Services of the seller')
                    ->schema([
                        Grid::make()
                            ->schema(array_map(function ($sellerProfileServices) {
                                return TextEntry::make("sellerProfileServices.{$sellerProfileServices->service_title}")
                                    ->label($sellerProfileServices->service_title)
                                    ->state(fn () => $sellerProfileServices->service_description)
                                    ->icon('heroicon-o-wrench-screwdriver');
                            }, $this->seller->sellerProfileServices->all()))
                    ]),

                Section::make('Reviews')
                    ->description('Customer reviews and ratings')
                    ->schema(
                        $this->seller->ratings->map(function (Rating $review) {
                            return Section::make('')
                                ->schema([
                                    RatingEntry::make('rating')
                                        ->state(fn () => $review->rating)
                                    ,
                                    TextEntry::make('review')
                                        ->label('Review')
                                        ->state(fn () => $review->review)
                                        ->markdown(),
                                ])
                                ->columns(1);
                        })->toArray()
                    )
            ]);
    }
}
