<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUnitSelected
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
{
    if (
        auth()->check() &&
        auth()->user()->isAdminUser() &&
        !session()->has('selected_bimba_unit')
    ) {
        return redirect()->route('select.unit');
    }

    return $next($request);
}
}
