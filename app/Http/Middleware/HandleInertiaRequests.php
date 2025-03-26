<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Closure;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
            ],
            'routeName' => optional($request->route())->getName(),
        ]);
    }

    public function handle($request, Closure $next)
    {
        $response = parent::handle($request, $next);
        
        // Add headers to prevent caching when Cloudflare challenge might occur
        if ($request->ajax() || $request->wantsJson()) {
            $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, proxy-revalidate');
            $response->header('Pragma', 'no-cache');
            $response->header('Expires', '0');
        }
        
        return $response;
    }
}
