<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleLivewireJsonRedirects
{
    /**
     * Handle an incoming request.
     *
     * This middleware intercepts Livewire JSON responses that contain redirect effects
     * and converts them to proper HTTP redirects. This fixes the issue where login
     * forms return JSON snapshots instead of redirecting after a few hours.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Check if this looks like a Livewire JSON response with a redirect
        $content = $response->getContent();
        if (empty($content) || $content[0] !== '{') {
            return $response;
        }

        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $response;
        }

        // Skip if this is a legitimate Livewire AJAX request (JS will handle the JSON)
        if ($request->hasHeader('X-Livewire')) {
            return $response;
        }

        // Check if this is a Livewire component response with a redirect effect
        if (isset($data['components']) && is_array($data['components'])) {
            foreach ($data['components'] as $component) {
                if (isset($component['effects']['redirect'])) {
                    return redirect($component['effects']['redirect']);
                }
            }
        }

        // Also check specifically for the structure seen in the error log
        if (isset($data['effects']['redirect'])) {
             return redirect($data['effects']['redirect']);
        }
        
        return $response;
    }
}
