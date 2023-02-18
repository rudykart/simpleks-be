<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HitungResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'keterangan_hitung' => $this->keterangan_hitung,
            'created_at' => $this->created_at->diffForHumans(),
            'updated_at' => $this->updated_at->diffForHumans(),
            'user_id' => $this->user_id,
            'persediaanBahanBaku' => new PersediaanBahanBakuResource(
                $this->dataHitung->persediaanBahanBaku
            ),

            'dataHitungs' => $this->whenLoaded(
                'dataHitungs',
                DataHitungResource::collection($this->dataHitungs)
            ),
        ];
    }
}
