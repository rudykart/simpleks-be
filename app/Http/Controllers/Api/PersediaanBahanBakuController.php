<?php

namespace App\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\PageController;
use App\Http\Resources\BahanBakuResource;
use App\Http\Resources\PersediaanBahanBakuResource;
use App\Models\PersediaanBahanBaku;
use App\Models\StokBahanBaku;
use Illuminate\Http\Response;

class PersediaanBahanBakuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $cari = request('q');

        $data =
            $cari == null
                ? PersediaanBahanBaku::orderBy('id', 'desc')
                : PersediaanBahanBaku::where(
                    'keterangan_pbb',
                    'like',
                    '%' . $cari . '%'
                )
                    ->whereIn('user_id', [auth()->user()->id])
                    ->orderBy('id', 'desc');

        if ($data->count() != 0) {
            $pbb = PageController::pagination($data, 'pbb', 10);
            return response()->json([
                'current_page' => $pbb->current_page,
                'data' => PersediaanBahanBakuResource::collection(
                    $pbb->data->loadMissing(['stokBahanBakus.bahanBaku'])
                ),
                'total_data' => $pbb->total_data,
                'links' => $pbb->links,
                'first_page_url' => $pbb->first_page_url,
                'last_page_url' => $pbb->last_page_url,
                'prev_page_url' => $pbb->prev_page_url,
                'next_page_url' => $pbb->next_page_url,
                'to' => $pbb->to,
                'last_page' => $pbb->last_page,
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

    public function cekData($request)
    {
        $bb = [];
        foreach ($request->stokBahanBakus as $key => $value) {
            $bb[] = $value['bahan_baku_id'];
        }

        foreach ($bb as $key => $value) {
            $cek = array_count_values($bb);
            if ($cek[$value] > 1) {
                return false;
            }
        }
        return true;
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'keterangan_pbb' => 'required|max:30',
                'stokBahanBakus.*.bahan_baku_id' => 'required',
                'stokBahanBakus.*.stok_bb' =>
                    'required|numeric|max:99999999999|gt:0',
            ],
            [
                'keterangan_pbb.required' => 'Input harus diisi',
                'keterangan_pbb.max' => 'Input maximal 20 caracter',
                'stokBahanBakus.*.bahan_baku_id.required' =>
                    'Input harus diisi',
                'stokBahanBakus.*.stok_bb.required' => 'Input harus diisi',
                'stokBahanBakus.*.stok_bb.numeric' =>
                    'Input harus berisi nilai',
                'stokBahanBakus.*.stok_bb.max' =>
                    'Input maximal 99.999.999.999',
                'stokBahanBakus.*.stok_bb.gt' => 'Input harus lebih dari 0',
            ]
        );

        if ($this->cekData($request) == false) {
            return response()->json(
                ['data_ganda' => 'Data tidak boleh ganda'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        DB::beginTransaction();
        try {
            $pbb = new PersediaanBahanBaku();
            // $pbb->user_id = $request->user_id;
            $pbb->user_id = auth()->user()->id;
            $pbb->keterangan_pbb = $request->keterangan_pbb;
            $pbb->save();

            foreach ($request['stokBahanBakus'] as $key => $value) {
                StokBahanBaku::create([
                    'user_id' => auth()->user()->id,
                    // 'user_id' => $request['stokBahanBakus'][$key]['user_id'],
                    'bahan_baku_id' =>
                        $request['stokBahanBakus'][$key]['bahan_baku_id'],
                    'persediaan_bahan_baku_id' => $pbb->id,
                    'stok_bb' => $request['stokBahanBakus'][$key]['stok_bb'],
                ]);
            }

            DB::commit();
            return new PersediaanBahanBakuResource(
                $pbb->loadMissing(['stokBahanBakus.bahanBaku'])
            );
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
        $pbb = PersediaanBahanBaku::findOrFail($id);
        return new PersediaanBahanBakuResource(
            $pbb->loadMissing(['stokBahanBakus.bahanBaku'])
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate(
            [
                'keterangan_pbb' => 'required|max:30',
                'stokBahanBakus.*.bahan_baku_id' => 'required',
                'stokBahanBakus.*.stok_bb' =>
                    'required|numeric|max:99999999999|gt:0',
            ],
            [
                'keterangan_pbb.required' => 'Input harus diisi',
                'keterangan_pbb.max' => 'Input maximal 20 caracter',
                'stokBahanBakus.*.bahan_baku_id.required' =>
                    'Input harus diisi',
                'stokBahanBakus.*.stok_bb.required' => 'Input harus diisi',
                'stokBahanBakus.*.stok_bb.numeric' =>
                    'Input harus berisi nilai',
                'stokBahanBakus.*.stok_bb.max' =>
                    'Input maximal 99.999.999.999',
                'stokBahanBakus.*.stok_bb.gt' => 'Input harus lebih dari 0',
            ]
        );

        if ($this->cekData($request) == false) {
            return response()->json(
                ['data_ganda' => 'Data tidak boleh ganda'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        DB::beginTransaction();
        try {
            PersediaanBahanBaku::findOrFail($id)->update([
                'keterangan_pbb' => $request->keterangan_pbb,
            ]);

            if ($request['destroyStokBahanBakus']) {
                foreach ($request['destroyStokBahanBakus'] as $key => $value) {
                    StokBahanBaku::findOrFail($value['id'])->delete();
                }
            }

            foreach ($request['stokBahanBakus'] as $key => $value) {
                if ($value['id'] != null) {
                    StokBahanBaku::findOrFail($value['id'])->update([
                        'bahan_baku_id' => $value['bahan_baku_id'],
                        'stok_bb' => $value['stok_bb'],
                    ]);
                } else {
                    StokBahanBaku::create([
                        'user_id' => auth()->user()->id,
                        'bahan_baku_id' => $value['bahan_baku_id'],
                        'persediaan_bahan_baku_id' => $id,
                        'stok_bb' => $value['stok_bb'],
                    ]);
                }
            }

            DB::commit();
            // return new PersediaanBahanBakuResource(
            //     $pbb->loadMissing(['stokBahanBakus.bahanBaku'])
            // );
            return response()->json(
                ['message' => 'Data berhasil di update'],
                Response::HTTP_OK
            );
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
        $pbb = PersediaanBahanBaku::with('stokBahanBakus')->findOrFail($id);

        $pbb->delete();

        return new PersediaanBahanBakuResource(
            $pbb->loadMissing(['stokBahanBakus.bahanBaku'])
        );
    }
}
