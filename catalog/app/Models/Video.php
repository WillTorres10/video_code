<?php

namespace App\Models;

use App\Models\Traits\{UUID, UploadFiles};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Support\Facades\DB;
use Exception;

class Video extends Model
{
    use HasFactory, SoftDeletes, UUID, UploadFiles;

    const NO_RATING = 'L';
    const RATING_LIST = [self::NO_RATING, '10', '12', '14', '16', '18'];

    protected $fillable = [
        'title', 'description', 'year_launched', 'opened', 'rating', 'duration',
        'thumb_file', 'banner_file', 'trailer_file', 'video_file'
    ];
    protected $dates = ['deleted_at'];
    protected $casts = [
        'opened' => 'boolean',
        'year_launched' => 'integer',
        'duration' => 'integer'
    ];

    protected static array $fileFields = ['thumb_file', 'banner_file', 'trailer_file', 'video_file'];

    public static function create(array $attributes = [])
    {
        $files = self::extractFiles($attributes);
        try {
            DB::beginTransaction();
            /* @var $obj Video */
            $obj = static::query()->create($attributes);
            self::handleRelations($obj, $attributes);
            $obj->uploadFiles($files);
            // Realizar o upload dos arquivos
            DB::commit();
            return $obj;
        } catch (Exception $exception) {
            DB::rollBack();
            if(isset($obj)) {
                // Excluir os arquivos de upload
                $obj->deleteFiles($files);
            }
            throw $exception;
        }
    }

    public function update(array $attributes = [], array $options = [])
    {
        try {
            DB::beginTransaction();
            $saved = parent::update($attributes, $options);
            if ($saved) {
                static::handleRelations($this, $attributes);
                // Realizar o upload dos arquivos
                // Excluir os antigos
            }
            DB::commit();
            return $saved;
        } catch (Exception $exception) {
            DB::rollBack();
                // Excluir os arquivos de upload
            throw $exception;
        }
    }

    public static function handleRelations(Video $video, array $attributes)
    {
        if(isset($attributes['categories_id'])) {
            $video->categories()->sync($attributes['categories_id']);
        }
        if(isset($attributes['genres_id'])) {
            $video->genres()->sync($attributes['genres_id']);
        }
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class);
    }

    protected function uploadDir(): string
    {
        return (string) $this->id;
    }
}
