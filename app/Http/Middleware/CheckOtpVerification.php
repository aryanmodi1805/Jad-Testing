<?php

namespace App\Http\Middleware;

use App\Filament\Company\Pages\AcceptInvite as AcceptInviteCompany;
use App\Filament\Customer\Pages\OtpPage;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOtpVerification
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $panel = filament()->getCurrentPanel()->getPath();
        $guard = filament()->getCurrentPanel()->getAuthGuard();

        if (in_array($request->route(), [OtpPage::getRouteName(), route('filament.' . $panel . '.auth.logout'), $panel . '/logout',])
            || $request->url() == OtpPage::getUrl(tenant: getTenant())
            || $request->url() == url('/' . $panel . '/logout'))
            return $next($request);

        if (auth($guard)->check() && !auth($guard)->user()->phone_verified_at) {
            return redirect()->route(OtpPage::getRouteName($panel), [
                "tenant" => getTenant(),
            ]);
        }
        return $next($request);
    }
}
