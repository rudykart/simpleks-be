<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Hitung;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HitungAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $currenUser = auth()->user()->id;
            $hitung = Hitung::findOrFail($request->id);

            if ($hitung->user_id != $currenUser) {
                return response()->json(
                    ['message' => 'Perhitungan ini bukan hak anda'],
                    Response::HTTP_FORBIDDEN
                );
            }
            return $next($request);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'message' => 'Perhitungan tidak ada',
                    'errors' => $th->getMessage(),
                ],
                Response::HTTP_NOT_FOUND
            );
        }
    }
}