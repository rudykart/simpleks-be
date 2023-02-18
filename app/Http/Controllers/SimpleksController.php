<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SimpleksController extends Controller
{
    public $kue = [];
    public $pbb = [];

    public $fZ = [];
    public $fK = [];
    public $nK = [];

    public $z = []; //fungsi tujuan
    public $k = []; //fungsi kendala
    public $s = []; //slack var

    public $index = [];

    public $simpAwal = [];
    public $iterasi = [];

    public $namaKolom = [];
    public $namaBaris = [];

    public $hKue = [];
    public $hBahanBaku = [];

    public $vd = [];

    public function __construct($dataHitungs)
    {
        $this->kue = $dataHitungs['kues'];
        $this->pbb = $dataHitungs['persediaanBahanBaku'];
        // $this->setVariable();
    }

    public function cek()
    {
        return $this->kue;
    }

    public function setVariable()
    {
        // dd($this->kue);

        $kS = []; //slack nama kolom
        $sZ = [];
        $fkSemetara = [];

        // Menyusun variable kendala dan nilai kunci
        foreach ($this->pbb['stokBahanBakus'] as $k1 => $dPbb) {
            $n = (int) $k1 + 1;
            $kS[$k1] = "S$n";
            $this->nK[$k1] = $dPbb['stok_bb'];

            $this->hBahanBaku[$k1] = [
                'kodeSimpBB' => "S$n",
                'namaBB' => $dPbb['bahanBaku']['nama_bb'],
                'sisaBB' => 0,
            ];

            foreach ($this->pbb['stokBahanBakus'] as $k2 => $pbb2) {
                $k1 == $k2
                    ? ($this->s[$k1][$k2] = 1)
                    : ($this->s[$k1][$k2] = 0);
                foreach ($this->kue as $k3 => $dKue) {
                    $this->fK[$k1][$k3] = 0;
                    foreach ($dKue['komposisiKues'] as $kompKue) {
                        if (
                            $dPbb['bahan_baku_id'] == $kompKue['bahan_baku_id']
                        ) {
                            $this->fK[$k1][$k3] = $kompKue['jumlah_bb'];
                        }
                    }
                }
            }
            $sZ[$k1] = 0;
            //menyusun kolom table simplek
            $fkSemetara[$k1] = array_merge([0], $this->fK[$k1], $this->s[$k1], [
                $this->nK[$k1],
            ]);
        }

        $kX = []; //x1,x2,x2,... nama kolom
        //meyusun variable tujuan atau Z
        foreach ($this->kue as $key => $data) {
            $z = $data['keuntungan_kue'];
            $this->fZ[$key] = $z * 0 - $z;
            $n = $key + 1;
            $kX[$key] = "X$n";

            $this->hKue[$key] = [
                'kodeSimpKue' => "X$n",
                'namaKue' => $data['nama_kue'],
                'keuntunganKue' => $data['keuntungan_kue'],
                'komposisiKue' => $data['komposisiKues'],
                'jumlahKue' => 0,
            ];
        }

        // dd($this->kue);

        $this->namaKolom = array_merge(['Z'], $kX, $kS, ['NK']);
        $this->namaBaris = array_merge(['Z'], $kS);

        //meyusun ke table simplek
        // $this->tSimp = array_merge([$this->z], $fkSemetara);
        $this->z = array_merge([1], $this->fZ, $sZ, [0]);
        $this->k = array_merge($fkSemetara);

        return [
            'z' => $this->z,
            'k' => $this->k,
            'namaKolom' => $this->namaKolom,
            'namaBaris' => $this->namaBaris,
            'hKue' => $this->hKue,
            'hBahanBaku' => $this->hBahanBaku,
        ];
    }

    public function cekData()
    {
        dd($this->hKue);
    }

    public function prosesSimpleks()
    {
        $loop = 0;

        $var = $this->setVariable();

        $setIterasi = array_merge([$var['z']], $var['k']);
        $this->simpAwal = $setIterasi;

        $kolom = $var['namaKolom'];
        $baris = $var['namaBaris'];

        $kue = $var['hKue'];
        $bahanBaku = $var['hBahanBaku'];

        $no = 0;
        do {
            //menentukan kolom kunci
            $kk = $this->getKolomKunci($setIterasi[0]);

            //menentukan baris kunci
            $bk = $this->getBarisKunci($setIterasi, $kk['keyKK']);

            //menentukan nilai baru baris kunci
            $nbbk = $this->getNBBK($setIterasi, $bk['keyBK'], $kk['keyKK']);

            // mengganti nilai baris baru selain baris kunci
            $nbb = $this->getNBB(
                $setIterasi,
                $bk['keyBK'],
                $kk['keyKK'],
                $nbbk
            );

            // penyesuaian variable dasar
            $varDasar = $this->getVD(
                $kk['keyKK'],
                $kolom,
                $bk['keyBK'],
                $baris
            );

            $baris = $varDasar;
            $setIterasi = $nbb;

            $vd[$no] = $baris;
            $iterasi[$no] = $setIterasi;
            $no++;

            //mengecek nilai f tujuan
            $cekKK = $this->getKolomKunci($nbb[0]);
            $cekKK['nilaiKK'] >= 0 ? ($loop = 1) : '';

            // $hasil[$no] = $this->setHasil(end($vd), end($iterasi));
            $hasil[] = $this->setHasil(end($vd), end($iterasi));

            // break;
        } while ($loop != 1);

        // dd($hasil);
        // dd($iterasi);

        return [
            'simpleks_awal' => $this->setVariable(),
            'vd' => $vd,
            'iterasi' => $iterasi,
            'hasil' => $hasil,
        ];
    }

    public function setHasil($vdBaris, $itAkhir)
    {
        $var = $this->setVariable();

        $kue = $var['hKue'];
        $bahanBaku = $var['hBahanBaku'];

        foreach ($vdBaris as $k => $baris) {
            for ($i = 0; $i < count($kue); $i++) {
                if ($vdBaris[$k] == $kue[$i]['kodeSimpKue']) {
                    $kue[$i]['jumlahKue'] = end($itAkhir[$k]);
                }
            }

            for ($i = 0; $i < count($bahanBaku); $i++) {
                if ($vdBaris[$k] == $bahanBaku[$i]['kodeSimpBB']) {
                    $bahanBaku[$i]['sisaBB'] = end($itAkhir[$k]);
                }
            }
        }

        return [
            'kue' => $kue,
            'bahanBaku' => $bahanBaku,
        ];
    }

    public function getKolomKunci($z)
    {
        $hasil = [];
        $nk = ['nilai' => min($z)];
        foreach ($z as $key => $value) {
            if ($value == $nk['nilai']) {
                $hasil = ['nilaiKK' => $nk['nilai'], 'keyKK' => $key];
                break;
            }
        }
        return $hasil;
    }

    public function getBarisKunci($fZfK, $keyKK)
    {
        $hasil = [];
        $keyBK = 0;
        //nentukan nilai index
        foreach ($fZfK as $key => $val) {
            if ($val[$keyKK] == 0) {
                $index[$key] = '∞';
            } else {
                $index[$key] = floatval(end($fZfK[$key]) / $val[$keyKK]);

                // $result = end($fZfK[$key]) / $val[$keyKK];
                // if (is_float($result)) {
                //     $index[$key] = floatval(end($fZfK[$key]) / $val[$keyKK]);
                // } else {
                //     $index[$key] = $result;
                // }
            }
        }

        //menentukan baris kunci
        for ($i = 0; $i < count($index); $i++) {
            $index[$i] < 0 ? ($index[$i] = 0) : '';
        }
        $kecuali = [0]; //kecuali
        $bkIndex = min(array_values(array_diff($index, $kecuali)));

        //menentukan key baris kunci
        foreach ($index as $key => $value) {
            if ($value == $bkIndex) {
                $keyBK = $key;
                break;
            }
        }

        $hasil = array_merge([
            'bkIndex' => $bkIndex,
            'keyBK' => $keyBK,
            'index' => $index,
        ]);
        return $hasil;
    }

    public function getNBBK($fZfK, $keyBK, $keyKK)
    {
        $data = [];
        foreach ($fZfK[$keyBK] as $value) {
            if ($fZfK[$keyBK][$keyKK] == 0) {
                $data[] = 0;
            } else {
                $data[] = floatval($value / $fZfK[$keyBK][$keyKK]);

                // $result = $value / $fZfK[$keyBK][$keyKK];
                // if (is_float($result)) {
                //     $data[] = floatval($value / $fZfK[$keyBK][$keyKK]);
                // } else {
                //     $data[] = $result;
                // }
            }
        }
        return $data;
    }

    public function getNBB($fZfK, $keyBK, $keyKK, $nbbk)
    {
        $data = [];
        for ($i = 0; $i < count($fZfK); $i++) {
            for ($j = 0; $j < count($fZfK[$i]); $j++) {
                if ($i === $keyBK) {
                    $data[$i][$j] = $nbbk[$j];
                } else {
                    $data[$i][$j] =
                        $fZfK[$i][$j] - $fZfK[$i][$keyKK] * $nbbk[$j];
                }
            }
        }
        return $data;
    }

    public function getVD($keyKK, $kolom, $keyBK, $baris)
    {
        // $barisBaru = 0;
        // $key = 0;
        for ($i = 0; $i < count($kolom); $i++) {
            if ($i == $keyKK) {
                $barisBaru = $kolom[$i];
            }
        }

        for ($j = 0; $j < count($baris); $j++) {
            $j == $keyBK ? ($baris[$j] = $barisBaru) : '';
        }
        return $baris;
    }
}
