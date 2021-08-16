<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SanctumAbilitiesCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$abilities)
    {

        foreach ($abilities as $ability) {
            if (!$request->user()->tokenCan($ability)) {
                abort(response()->json([
                    'message' => 'Unauthorized'
                ], 401));
            }
        }

        return $next($request);
    }
}
