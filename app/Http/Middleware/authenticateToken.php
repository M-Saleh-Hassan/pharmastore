<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use Illuminate\Http\Exceptions\HttpResponseException;

class authenticateToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(auth()->user())
            return $next($request);
        else
            throw new HttpResponseException(response()->json(['success'=> 0, 'data' => ['message' => 'Unauthorized']], 401));
    }
}
