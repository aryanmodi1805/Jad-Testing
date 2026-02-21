<?php

namespace App\Http\Middleware;

use App\Services\RequestService;
use Closure;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectPendingRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(auth('customer')->check() &&\Session::has('pending_request') && auth('customer')->user()->is_phone_verified){
            try {
                /** @var RequestService $pendingRequest */
                $pendingRequest = \Session::get('pending_request');
                $request = null;
                try{
                    return $pendingRequest->createRequest(auth('customer')->user());
                } catch (\Exception $e) {
                    Notification::make()
                        ->title(__('requests.unknown_error'))
                        ->danger()
                        ->send();
                }

            }catch (\Exception $e) {
                Notification::make()
                    ->title(__('requests.unknown_error'))
                    ->danger()
                    ->send();
            }



        }
        return $next($request);
    }
}
