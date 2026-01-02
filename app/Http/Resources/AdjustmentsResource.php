<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdjustmentsResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'type' => $this->type,
            'operation' => $this->operation,
            'value' => $this->value,

            'restrictions' => $this->when(is_array($this->restrictions), [
                'logic' => $this->restrictions['logic'] ?? null,
                'conditions' => collect($this->restrictions['conditions'] ?? [])
                    ->map(
                        fn($condition) => [
                            'property' => $condition['property'] ?? null,
                            'operator' => $condition['operator'] ?? null,
                            'value' => $condition['value'] ?? null,
                        ],
                    )
                    ->values()
                    ->toArray(),
                'behavior' => $this->restrictions['behavior'] ?? null,
            ]),

            'system' => (bool) $this->system,
        ];
    }
}
