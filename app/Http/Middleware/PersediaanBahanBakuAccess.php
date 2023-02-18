<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\PersediaanBahanBaku;
use Symfony\Component\HttpFoundation\Response;

class PersediaanBahanBakuAccess
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
            $pbb = PersediaanBahanBaku::findOrFail($request->id);

            if ($pbb->user_id != $currenUser) {
                return response()->json(
                    ['message' => 'Persediaan bahan baku ini bukan hak anda'],
                    Response::HTTP_FORBIDDEN
                );
            }
            return $next($request);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'message' => 'Persediaan bahan baku tidak ada',
                    'errors' => $th->getMessage(),
                ],
                Response::HTTP_NOT_FOUND
            );
        }
    }
}