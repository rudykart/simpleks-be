<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\BahanBaku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PageController;
use App\Http\Resources\BahanBakuResource;

class BahanBakuController extends Controller
{
    public function index()
    {
        $cari = request('q');
        $data =
            $cari == null
                ? BahanBaku::where('user_id', auth()->user()->id)->orderBy(
                    'id',
                    'desc'
                )
                : BahanBaku::where('nama_bb', 'like', '%' . $cari . '%')
                    ->whereIn('user_id', [auth()->user()->id])
                    ->orderBy('id', 'desc');

        if ($data->count() != 0) {
            $bahanBaku = PageController::pagination($data, 'bahanbaku', 10);
            return response()->json([
                'current_page' => $bahanBaku->current_page,
                'data' => BahanBakuResource::collection($bahanBaku->data),
                'total_data' => $bahanBaku->total_data,
                'links' => $bahanBaku->links,
                'first_page_url' => $bahanBaku->first_page_url,
                'last_page_url' => $bahanBaku->last_page_url,
                'prev_page_url' => $bahanBaku->prev_page_url,
                'next_page_url' => $bahanBaku->next_page_url,
                'to' => $bahanBaku->to,
                'last_page' => $bahanBaku->last_page,
            ]);
        } elseif ($data->count() == 0) {
            return response()->json([
                'current_page' => 1,
                'data' => null,
                'total_data' => 0,
                'links' => ['url' => null, 'label' => 1, 'active' => true],
                'first_page_url' => null,
                'last_page_url' => null,
                'prev_page_url' => null,
                'next_page_url' => null,
                'to' => 1,
                'last_page' => 1,
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate(
            [
                'nama_bb' => 'required|max:30',
            ],
            [
                'nama_bb.required' => 'Nama bahan baku tidak boleh kosong',
                'nama_bb.max' => 'Nama bahan baku maximal 30',
            ]
        );

        // $request['user_id'] = auth()->user()->id;
        $request['user_id'] = 1;

        DB::beginTransaction();
        try {
            $bahanBaku = BahanBaku::create($request->all());
            DB::commit();
            return new BahanBakuResource($bahanBaku);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $bahanBaku = BahanBaku::findOrFail($id);
        return new BahanBakuResource($bahanBaku);
    }

    public function update(Request $request, $id)
    {
        $bahanBaku = BahanBaku::findOrFail($id);

        $request->validate(
            [
                'nama_bb' => 'required|max:30',
            ],
            [
                'nama_bb.required' => 'Nama bahan baku tidak boleh kosong',
                'nama_bb.max' => 'Nama bahan baku maximal 30',
            ]
        );

        DB::beginTransaction();
        try {
            $bahanBaku->update($request->all());
            DB::commit();
            return new BahanBakuResource($bahanBaku);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $bahanBaku = BahanBaku::findOrFail($id);

        $bahanBaku->delete();
        return new BahanBakuResource($bahanBaku);
    }
}
