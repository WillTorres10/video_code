<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'year_launched' => $this->year_launched,
            'opened' => $this->opened,
            'rating' => $this->rating,
            'duration' => $this->duration,
            'genres' => $this->when($this->genres, GenreSimpleResource::collection($this->genres), null),
            'categories' =>  $this->when($this->categories, CategorySimpleResource::collection($this->categories), null),
            'thumb_file_url' => $this->thumb_file_url,
            'banner_file_url' => $this->banner_file_url,
            'trailer_file_url' => $this->trailer_file_url,
            'video_file_url' => $this->video_file_url,
            'thumb_file' => $this->thumb_file,
            'banner_file' => $this->banner_file,
            'trailer_file' => $this->trailer_file,
            'video_file' => $this->video_file,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
