<?php

namespace App\Http\Middleware;

use App\Models\BahanBaku;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BahanBakuAccess
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
        // return $next($request);
        try {
            $currenUser = auth()->user()->id;
            $bahanBaku = BahanBaku::findOrFail($request->id);

            if ($bahanBaku->user_id != $currenUser) {
                return response()->json(
                    ['message' => 'Bahan baku ini bukan hak anda'],
                    Response::HTTP_FORBIDDEN
                );
            }
            return $next($request);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'message' => 'Bahan baku tidak ada',
                    'errors' => $th->getMessage(),
                ],
                Response::HTTP_NOT_FOUND
            );
        }
    }
}