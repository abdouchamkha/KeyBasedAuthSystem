<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Number;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDownload extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'name'=>decrypt($this->labels['original_name']),
            'size'=>$this->getHumanReadableSize($this->labels['size']),
            'file_extension'=>$this->file_extension,
            'uploaded_at'=>$this->created_at?->format('Y-m-d H:i'),
            'uploaded_by'=>$this->whenLoaded('uploadedBy'),
            'created_by'=>$this->whenLoaded('createdBy'),
            'deleted_by'=>$this->whenLoaded('deletedBy'),
            'deleted_at'=>$this->deleted_at?->format('Y-m-d H:i'),
            'products'=>Product::collection($this->whenLoaded('products')),
    ];
    }
    /**
     * Convert the file size into a human-readable format (B, KB, MB).
     *
     * @param int $size
     * @return string
     */
    protected function getHumanReadableSize(int $size): string
    {
        if ($size < 1024) {
            return Number::format($size) . ' B';
        } elseif ($size < 1048576) { // 1024 * 1024 = 1048576
            return Number::format($size / 1024, 2) . ' KB';
        } else {
            return Number::format($size / 1048576, 2) . ' MB';
        }
    }
}
