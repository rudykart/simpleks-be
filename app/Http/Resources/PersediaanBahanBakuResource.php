<?php

namespace App\Http\Resources;

use App\Models\StokBahanBaku;
use Illuminate\Http\Resources\Json\JsonResource;

class PersediaanBahanBakuResource extends JsonResource
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
            'keterangan_pbb' => $this->keterangan_pbb,
            'created_at' => $this->created_at->diffForHumans(),
            'updated_at' => $this->updated_at->diffForHumans(),
            'user_id' => $this->user_id,

            'stokBahanBakus' => $this->whenLoaded(
                'stokBahanBakus',
                StokBahanBakuResource::collection($this->stokBahanBakus)
            ),
        ];
    }
}
