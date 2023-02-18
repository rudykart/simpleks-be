<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class KomposisiKueResource extends JsonResource
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
            'jumlah_bb' => $this->jumlah_bb,
            'created_at' => $this->created_at->diffForHumans(),
            'updated_at' => $this->updated_at->diffForHumans(),
            'user_id' => $this->user_id,
            'kue_id' => $this->kue_id,
            'bahan_baku_id' => $this->bahan_baku_id,
        ];
    }
}