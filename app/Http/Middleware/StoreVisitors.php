<?php

namespace App\Http\Middleware;

use App\Models\Visitor;
use Closure;

class StoreVisitors
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
        // $ip = trim(shell_exec("dig +short myip.opendns.com @resolver1.opendns.com"));
        // if(empty($ip))
        $ip = $request->ip();
        $visitor = Visitor::firstOrCreate(['ip' => $ip]);
        $visitor->last_visit = now();
        $visitor->visits_count = $visitor->visits_count + 1;
        $visitor->save();
        return $next($request);
    }
}
