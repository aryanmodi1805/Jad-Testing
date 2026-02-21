<?php

namespace App\Http\Controllers;

use App\Http\Resources\BalanceStatesResource;
use App\Http\Resources\WalletResource;
use App\Models\Seller;
use App\Settings\AppSettings;
use Illuminate\Http\Request;
use Lang;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }


    public function getSellerBalance(Request $request, AppSettings $settings)
    {

        $seller = Seller::where('id', $request->user()->id)->first();
        try {
            if (!$seller) {
                return response()->json(['error' => Lang::get('api.record_not_found')], 404);
            }

            $balance = $seller->balance();


            return $this->ApiResponseFormatted(200, $balance, Lang::get('api.success'), $settings, $request);

        } catch (\Exception $e) {
            return $this->ApiResponseFormatted(500, null, $e->getMessage(), $settings, $request);
        }

    }

    public function getSellerTransactions(Request $request, AppSettings $settings)
    {
        $seller = $request->user();
        try {

            $transactions = $seller->balanceStates()->orderBy('created_at', 'desc')->get();

            return $this->ApiResponseFormatted(200, BalanceStatesResource::collection($transactions), Lang::get('api.success'), $settings, $request);

        } catch (\Exception $e) {
            Log::error($e);
            return $this->ApiResponseFormatted(500, null, $e->getMessage(), $settings, $request);
        }
    }
}
