<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StokBahanBakuResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'nama_bb' => $this->whenLoaded(
                'bahanBaku',
                $this->bahanBaku->nama_bb
            ),
            'stok_bb' => $this->stok_bb,
            'created_at' => $this->created_at->diffForHumans(),
            'updated_at' => $this->updated_at->diffForHumans(),
            'user_id' => $this->user_id,
            'persediaan_bahan_baku_id' => $this->persediaan_bahan_baku_id,
            'bahan_baku_id' => $this->bahan_baku_id,
        ];
    }
}
