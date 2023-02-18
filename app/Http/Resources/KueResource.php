<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class KueResource extends JsonResource
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
            'nama_kue' => $this->nama_kue,
            'keuntungan_kue' => $this->keuntungan_kue,
            'created_at' => $this->created_at->diffForHumans(),
            'updated_at' => $this->updated_at->diffForHumans(),
            'user_id' => $this->user_id,

            'komposisiKues' => $this->whenLoaded(
                'komposisiKues',
                KomposisiKueResource::collection($this->komposisiKues)
            ),
        ];
    }
}