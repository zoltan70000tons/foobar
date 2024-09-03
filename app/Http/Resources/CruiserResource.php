<?php

namespace App\Http\Resources;

use App\Models\Cruise;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CruiserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'value' => (string) $this->id,
            'label' => $this->name,
        ];
    }
}
