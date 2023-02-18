<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\DataHitung;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DataHitungAccess
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
            $dataHitung = DataHitung::findOrFail($request->id);

            if ($dataHitung->user_id != $currenUser) {
                return response()->json(
                    ['message' => 'Data hitung ini bukan hak anda'],
                    Response::HTTP_FORBIDDEN
                );
            }
            return $next($request);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'message' => 'Data hitung tidak ada',
                    'errors' => $th->getMessage(),
                ],
                Response::HTTP_NOT_FOUND
            );
        }
    }
}