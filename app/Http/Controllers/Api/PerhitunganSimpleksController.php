<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SimpleksController;
use App\Http\Resources\HitungResource;
use App\Models\Hitung;
use App\Models\Kue;
use App\Models\PersediaanBahanBaku;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PerhitunganSimpleksController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $pbb = PersediaanBahanBaku::with(
            'stokBahanBakus.bahanBaku'
        )->findOrFail($request->persediaan_bahan_baku_id);
        $kues = Kue::with('komposisiKues.bahanBaku')
            ->whereIn('id', $request->dataHitungs)
            ->get();

        $data = ['persediaanBahanBaku' => $pbb, 'kues' => $kues];

        $simpleks = new SimpleksController($data);

        return response()->json(
            ['data' => $simpleks->prosesSimpleks()],
            Response::HTTP_OK
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $kue = [];
        foreach ($request->dataHitungs as $key => $value) {
            $kue[] = $request->dataHitungs[$key]['kue_id'];
        }

        $pbb = PersediaanBahanBaku::with(
            'stokBahanBakus.bahanBaku'
        )->findOrFail($request->persediaan_bahan_baku_id);
        $kues = Kue::with('komposisiKues.bahanBaku')
            ->whereIn('id', $kue)
            ->get();

        $data = ['persediaanBahanBaku' => $pbb, 'kues' => $kues];

        $simpleks = new SimpleksController($data);

        return response()->json(
            ['data' => $simpleks->prosesSimpleks()],
            Response::HTTP_OK
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $hitung = Hitung::findOrFail($id);

        // dd(
        //     new HitungResource(
        //         $hitung->loadMissing([
        //             'dataHitungs.kue.komposisiKues.bahanBaku',
        //             'dataHitung.persediaanBahanBaku.stokBahanBakus.bahanBaku',
        //         ])
        //     )
        // );

        // return new HitungResource(
        //     $hitung->loadMissing([
        //         'dataHitungs.kue.komposisiKues.bahanBaku',
        //         'dataHitung.persediaanBahanBaku.stokBahanBakus.bahanBaku',
        //     ])
        // );

        $data = new HitungResource(
            $hitung->loadMissing([
                'dataHitungs.kue.komposisiKues.bahanBaku',
                'dataHitung.persediaanBahanBaku.stokBahanBakus.bahanBaku',
            ])
        );

        // $data = HitungResource::collection(
        //     $hitung->loadMissing([
        //         'dataHitungs.kue.komposisiKues.bahanBaku',
        //         'dataHitung.persediaanBahanBaku.stokBahanBakus.bahanBaku',
        //     ])
        // );

        $isi = [];

        foreach ($data->dataHitungs as $key => $value) {
            # code...
            $isi[] = $value;
        }

        dd($isi);
        // dd($data->dataHitungs);

        $simpleks = new SimpleksController($data->dataHitungs);

        // dd($simpleks->prosesSimpleks());

        return response()->json(
            ['data' => $simpleks->prosesSimpleks()],
            Response::HTTP_OK
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}