<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Exceptions\HttpResponseException;

class AuthenticateRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $roleName)
    {
        if(auth()->user()->roles()->where('name', $roleName)->first())
            return $next($request);
        else
            throw new HttpResponseException(response()->json(['success'=> 0, 'data' => ['message' => 'Unauthorized']], 401));
    }
}
