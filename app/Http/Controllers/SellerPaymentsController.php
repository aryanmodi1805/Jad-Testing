<?php

namespace App\Http\Controllers;

use App\Http\Resources\SellerMyServicesResource;
use App\Http\Resources\SellerProfileResource;
use App\Http\Resources\SellerReviewsResource;
use App\Models\Seller;
use App\Settings\AppSettings;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Lang;

class SellerPaymentsController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth:sanctum','web']);
    }




    public function chargeCredit(Request $request, AppSettings $settings)
    {
        Filament::getPanel("seller")->auth()->login($request->user());
        $locale = $request->header('Accept-Language', 'ar');
        LanguageSwitch::make()->userPreferredLocale($locale);
        return redirect()->
        to('/seller/charge-credit-page?locale='.$locale);

    }
    public function subscribe(Request $request, AppSettings $settings)
    {
        Filament::getPanel("seller")->auth()->login($request->user());
        $locale = $request->header('Accept-Language', 'ar');
        LanguageSwitch::make()->userPreferredLocale($locale);
        return redirect()->
        to('/seller/app-subscribe?locale='.$locale);

    }


}
