<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PageController;
use App\Http\Resources\KueResource;
use App\Models\KomposisiKue;
use App\Models\Kue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class KueController extends Controller
{
    public function index()
    {
        $cari = request('q');

        $data =
            $cari == null
                ? Kue::orderBy('id', 'desc')
                : Kue::where('nama_kue', 'like', '%' . $cari . '%')
                    ->whereIn('user_id', [auth()->user()->id])
                    ->orderBy('id', 'desc');

        if ($data->count() != 0) {
            $kue = PageController::pagination($data, 'kue', 10);
            return response()->json([
                'current_page' => $kue->current_page,
                'data' => KueResource::collection(
                    $kue->data->loadMissing(['komposisiKues.bahanBaku'])
                ),
                'total_data' => $kue->total_data,
                'links' => $kue->links,
                'first_page_url' => $kue->first_page_url,
                'last_page_url' => $kue->last_page_url,
                'prev_page_url' => $kue->prev_page_url,
                'next_page_url' => $kue->next_page_url,
                'to' => $kue->to,
                'last_page' => $kue->last_page,
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
        foreach ($request->komposisiKues as $key => $value) {
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
                'nama_kue' => 'required|max:30',
                'keuntungan_kue' => 'required|numeric|max:99999999999|gt:0',
                'komposisiKues.*.bahan_baku_id' => 'required',
                'komposisiKues.*.jumlah_bb' =>
                    'required|numeric|max:99999999999|gt:0',
            ],
            [
                'nama_kue.required' => 'Input harus diisi',
                'nama_kue.max' => 'Input maximal 20 caracter',
                'keuntungan_kue.required' => 'Input harus diisi',
                'keuntungan_kue.numeric' => 'Input harus berisi nilai',
                'keuntungan_kue.max' => 'Input maximal 99.999.999.999',
                'keuntungan_kue.gt' => 'Input harus lebih dari 0',
                'komposisiKues.*.bahan_baku_id.required' => 'Input harus diisi',
                'komposisiKues.*.jumlah_bb.required' => 'Input harus diisi',
                'komposisiKues.*.jumlah_bb.numeric' =>
                    'Input harus berisi nilai',
                'komposisiKues.*.jumlah_bb.max' =>
                    'Input maximal 99.999.999.999',
                'komposisiKues.*.jumlah_bb.gt' => 'Input harus lebih dari 0',
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
            $kue = new Kue();
            $kue->user_id = auth()->user()->id;
            $kue->nama_kue = $request->nama_kue;
            $kue->keuntungan_kue = $request->keuntungan_kue;
            $kue->save();

            foreach ($request['komposisiKues'] as $key => $value) {
                KomposisiKue::create([
                    'user_id' => auth()->user()->id,
                    'bahan_baku_id' =>
                        $request['komposisiKues'][$key]['bahan_baku_id'],
                    'kue_id' => $kue->id,
                    'jumlah_bb' => $request['komposisiKues'][$key]['jumlah_bb'],
                ]);
            }

            DB::commit();
            return new KueResource(
                $kue->loadMissing(['komposisiKues.bahanBaku'])
            );
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $kue = Kue::findOrFail($id);
        return new KueResource($kue->loadMissing(['komposisiKues.bahanBaku']));
    }

    public function update(Request $request, $id)
    {
        $request->validate(
            [
                'nama_kue' => 'required|max:30',
                'keuntungan_kue' => 'required|numeric|max:99999999999|gt:0',
                'komposisiKues.*.bahan_baku_id' => 'required',
                'komposisiKues.*.jumlah_bb' =>
                    'required|numeric|max:99999999999|gt:0',
            ],
            [
                'nama_kue.required' => 'Input harus diisi',
                'nama_kue.max' => 'Input maximal 20 caracter',
                'keuntungan_kue.required' => 'Input harus diisi',
                'keuntungan_kue.numeric' => 'Input harus berisi nilai',
                'keuntungan_kue.max' => 'Input maximal 99.999.999.999',
                'keuntungan_kue.gt' => 'Input harus lebih dari 0',
                'komposisiKues.*.bahan_baku_id.required' => 'Input harus diisi',
                'komposisiKues.*.jumlah_bb.required' => 'Input harus diisi',
                'komposisiKues.*.jumlah_bb.numeric' =>
                    'Input harus berisi nilai',
                'komposisiKues.*.jumlah_bb.max' =>
                    'Input maximal 99.999.999.999',
                'komposisiKues.*.jumlah_bb.gt' => 'Input harus lebih dari 0',
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
            Kue::findOrFail($id)->update([
                'nama_kue' => $request->nama_kue,
                'keuntungan_kue' => $request->keuntungan_kue,
            ]);

            if ($request['destroyKomposisiKues']) {
                foreach ($request['destroyKomposisiKues'] as $key => $value) {
                    KomposisiKue::findOrFail($value['id'])->delete();
                }
            }

            foreach ($request['komposisiKues'] as $key => $value) {
                if ($value['id'] != null) {
                    KomposisiKue::findOrFail($value['id'])->update([
                        'bahan_baku_id' => $value['bahan_baku_id'],
                        'jumlah_bb' => $value['jumlah_bb'],
                    ]);
                } else {
                    KomposisiKue::create([
                        'user_id' => auth()->user()->id,
                        'bahan_baku_id' => $value['bahan_baku_id'],
                        'kue_id' => $id,
                        'jumlah_bb' => $value['jumlah_bb'],
                    ]);
                }
            }

            DB::commit();
            return response()->json(
                ['message' => 'Data berhasil di update'],
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $kue = Kue::with('komposisiKues')->findOrFail($id);
        $kue->delete();
        return new KueResource($kue->loadMissing(['komposisiKues.bahanBaku']));
    }
}
