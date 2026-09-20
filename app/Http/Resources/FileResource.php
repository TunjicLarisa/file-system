<?php

namespace App\Http\Resources;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin File
 */
class FileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'folder_id' => $this->folder_id,

            'folder_path' => $this->when(
                $request->boolean('all'),
                fn () => $this->folder
                    ? $this->folder->getPath()
                    : 'Home'
            ),
        ];
    }
}
