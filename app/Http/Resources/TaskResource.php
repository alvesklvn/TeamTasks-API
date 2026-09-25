<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'identificador' => $this->id,
            'titulo' => $this->name,
            'description' => $this->description,
            'status' => $this->status->name,
            'prazo' => $this->deadline,
            'colaborador' => $this->user->name
        ];
    }
}
