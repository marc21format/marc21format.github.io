<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOrExecutive
{
    /**
     * Handle an incoming request.
     * Only allow users who are admin or executive.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user || (! method_exists($user, 'isAdmin') || ! method_exists($user, 'isExecutive'))) {
            abort(403);
        }
        if (! ($user->isAdmin() || $user->isExecutive())) {
            abort(403);
        }
        return $next($request);
    }
}
