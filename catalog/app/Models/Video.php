<?php

namespace App\Models;

use App\Models\DTOs\FileRulesValidation;
use App\Models\Traits\{UploadFiles, UUID};
use Exception;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Video extends Model
{
    use HasFactory, SoftDeletes, UUID, UploadFiles;

    const NO_RATING = 'L';
    const RATING_LIST = [self::NO_RATING, '10', '12', '14', '16', '18'];

    const FILE_TYPE_VALIDATION_VIDEO = "mimetypes:video/mp4";
    const FILE_TYPE_VALIDATION_IMAGE = "image";

    public static array $fileFields = ['thumb_file', 'banner_file', 'trailer_file', 'video_file'];
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

    /**
     * @throws \Spatie\DataTransferObject\Exceptions\UnknownProperties
     */
    public static function getFilesFieldsRules(): array
    {
        return [
            new FileRulesValidation(field: 'thumb_file', required: false, typeValidation: self::FILE_TYPE_VALIDATION_IMAGE, maxKilobytes: 5120),
            new FileRulesValidation(field: 'banner_file', required: false, typeValidation: self::FILE_TYPE_VALIDATION_IMAGE, maxKilobytes: 10240),
            new FileRulesValidation(field: 'trailer_file', required: false, typeValidation: self::FILE_TYPE_VALIDATION_VIDEO, maxKilobytes: 1048576),
            new FileRulesValidation(field: 'video_file', required: false, typeValidation: self::FILE_TYPE_VALIDATION_VIDEO, maxKilobytes: 52428800),
        ];
    }

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
            if (isset($obj)) {
                $obj?->deleteFiles($files);
            }
            throw $exception;
        }
    }

    public static function handleRelations(Video $video, array $attributes)
    {
        if (isset($attributes['categories_id'])) {
            $video->categories()->sync($attributes['categories_id']);
        }
        if (isset($attributes['genres_id'])) {
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

    public function update(array $attributes = [], array $options = [])
    {
        $files = self::extractFiles($attributes);
        try {
            DB::beginTransaction();
            $saved = parent::update($attributes, $options);
            static::handleRelations($this, $attributes);
            if ($saved) {
                $this->uploadFiles($files);
            }
            DB::commit();
            if ($saved && count($files)) {
                $this->deleteOldFiles($files);
            }
            return $saved;
        } catch (Exception $exception) {
            DB::rollBack();
            $this->deleteFiles($files);
            throw $exception;
        }
    }

    protected function uploadDir(): string
    {
        return (string)$this->id;
    }

    public function getThumbFileUrlAttribute()
    {
        return $this->thumb_file ? $this->getFileUrl($this->thumb_file) : null;
    }

    public function getBannerFileUrlAttribute()
    {
        return $this->banner_file ? $this->getFileUrl($this->banner_file) : null;
    }

    public function getTrailerFileUrlAttribute()
    {
        return $this->trailer_file ? $this->getFileUrl($this->trailer_file) : null;
    }

    public function getVideoFileUrlAttribute()
    {
        return $this->video_file ? $this->getFileUrl($this->video_file) : null;
    }
}
