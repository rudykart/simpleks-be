<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PageController;
use App\Http\Resources\HitungResource;
use App\Http\Resources\KueResource;
use App\Models\DataHitung;
use App\Models\Hitung;
use App\Models\KomposisiKue;
use App\Models\PersediaanBahanBaku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class HitungController extends Controller
{
    public function index()
    {
        $cari = request('q');

        $data =
            $cari == null
                ? Hitung::orderBy('id', 'desc')
                : Hitung::where('keterangan_hitung', 'like', '%' . $cari . '%')
                    ->whereIn('user_id', [auth()->user()->id])
                    ->orderBy('id', 'desc');

        if ($data->count() != 0) {
            $hitung = PageController::pagination($data, 'hitung', 10);
            return response()->json([
                'current_page' => $hitung->current_page,
                'data' => HitungResource::collection(
                    $hitung->data->loadMissing([
                        'dataHitungs.kue.komposisiKues.bahanBaku',
                        'dataHitung.persediaanBahanBaku.stokBahanBakus.bahanBaku',
                    ])
                ),
                'total_data' => $hitung->total_data,
                'links' => $hitung->links,
                'first_page_url' => $hitung->first_page_url,
                'last_page_url' => $hitung->last_page_url,
                'prev_page_url' => $hitung->prev_page_url,
                'next_page_url' => $hitung->next_page_url,
                'to' => $hitung->to,
                'last_page' => $hitung->last_page,
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

    public function cekDataKue($request)
    {
        $bb = [];
        foreach ($request->dataHitungs as $key => $value) {
            $bb[] = $value['kue_id'];
        }

        foreach ($bb as $key => $value) {
            $cek = array_count_values($bb);
            if ($cek[$value] > 1) {
                return false;
            }
        }
        return true;
    }

    public function cekKuePbb($request)
    {
        //cek komposisi kue cocok
        $pbb = PersediaanBahanBaku::findOrFails(
            $request->persediaan_bahan_baku_id
        );

        foreach ($pbb->stokBahanBakus as $key1 => $stokPbb) {
            $stok[] = $stokPbb->bahan_baku_id;
        }

        foreach ($request['dataHitungs'] as $key1 => $kue) {
            foreach (
                $kue[$key1]['kue']['komposisi_kues']
                as $key2 => $kompKue
            ) {
                if (!in_array($kompKue['bahan_baku_id'], $stok)) {
                    // dd('gagal, Komposisi Kue gavalid');
                    // return $this->err = 1;
                    return false;
                }
            }
        }
        // dd('Berhasil, Komposisi Kue valid');
        return true;
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'keterangan_hitung' => 'required|max:30',
                'persediaan_bahan_baku_id' => 'required',
                'dataHitungs.*.kue_id' => 'required',
            ],
            [
                'keterangan_hitung.required' => 'Input harus diisi',
                'persediaan_bahan_baku_id.required' => 'Input harus diisi',
                'keterangan_hitung.max' => 'Input maximal 20 caracter',
                'dataHitungs.*.kue_id.required' => 'Input harus diisi',
            ]
        );

        if ($this->cekDataKue($request) == false) {
            return response()->json(
                ['data_ganda' => 'Data tidak boleh ganda'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        DB::beginTransaction();
        try {
            $hitung = new Hitung();
            $hitung->user_id = auth()->user()->id;
            $hitung->keterangan_hitung = $request->keterangan_hitung;
            $hitung->save();

            foreach ($request['dataHitungs'] as $key => $value) {
                DataHitung::create([
                    'user_id' => auth()->user()->id,
                    'persediaan_bahan_baku_id' =>
                        $request->persediaan_bahan_baku_id,
                    'kue_id' => $request['dataHitungs'][$key]['kue_id'],
                    'hitung_id' => $hitung->id,
                ]);
            }

            DB::commit();

            return new HitungResource(
                $hitung->loadMissing([
                    'dataHitungs.kue.komposisiKues.bahanBaku',
                    'dataHitung.persediaanBahanBaku.stokBahanBakus.bahanBaku',
                ])
            );
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $hitung = Hitung::findOrFail($id);
        return new HitungResource(
            $hitung->loadMissing([
                'dataHitungs.kue.komposisiKues.bahanBaku',
                'dataHitung.persediaanBahanBaku.stokBahanBakus.bahanBaku',
            ])
        );
    }

    public function update(Request $request, $id)
    {
        $request->validate(
            [
                'keterangan_hitung' => 'required|max:30',
                'persediaan_bahan_baku_id' => 'required',
                'dataHitungs.*.kue_id' => 'required',
            ],
            [
                'keterangan_hitung.required' => 'Input harus diisi',
                'persediaan_bahan_baku_id.required' => 'Input harus diisi',
                'keterangan_hitung.max' => 'Input maximal 20 caracter',
                'dataHitungs.*.kue_id.required' => 'Input harus diisi',
            ]
        );

        if ($this->cekDataKue($request) == false) {
            return response()->json(
                ['data_ganda' => 'Data tidak boleh ganda'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        DB::beginTransaction();
        try {
            Hitung::findOrFail($id)->update([
                'keterangan_hitung' => $request->keterangan_hitung,
            ]);

            if ($request['destroyDataHitungs']) {
                foreach ($request['destroyDataHitungs'] as $key => $value) {
                    DataHitung::findOrFail($value['id'])->delete();
                }
            }

            foreach ($request['dataHitungs'] as $key => $value) {
                if ($value['id'] != null) {
                    DataHitung::findOrFail($value['id'])->update([
                        'persediaan_bahan_baku_id' =>
                            $request->persediaan_bahan_baku_id,
                        'kue_id' => $request['dataHitungs'][$key]['kue_id'],
                    ]);
                } else {
                    DataHitung::create([
                        'user_id' => auth()->user()->id,
                        'persediaan_bahan_baku_id' =>
                            $request->persediaan_bahan_baku_id,
                        'kue_id' => $request['dataHitungs'][$key]['kue_id'],
                        'hitung_id' => $id,
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
        DB::beginTransaction();
        try {
            $hitung = Hitung::findOrFail($id);
            $hitung->delete();
            DB::commit();
            return response()->json(['message' => 'data berhasil dihapus']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()]);
        }
    }
}