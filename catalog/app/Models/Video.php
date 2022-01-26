<?php

namespace App\Models;

use App\Models\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Support\Facades\DB;
use Exception;

class Video extends Model
{
    use HasFactory, SoftDeletes, UUID;

    const NO_RATING = 'L';
    const RATING_LIST = [self::NO_RATING, '10', '12', '14', '16', '18'];

    protected $fillable = [ 'title', 'description', 'year_launched', 'opened', 'rating', 'duration' ];
    protected $dates = ['deleted_at'];
    protected $casts = [
        'opened' => 'boolean',
        'year_launched' => 'integer',
        'duration' => 'integer'
    ];

    public static function create(array $attributes = [])
    {
        try {
            DB::beginTransaction();
            /* @var $obj Video */
            $obj = static::query()->create($attributes);
            self::handleRelations($obj, $attributes);
            // Realizar o upload dos arquivos
            DB::commit();
            return $obj;
        } catch (Exception $exception) {
            DB::rollBack();
            if(isset($obj)) {
                // Excluir os arquivos de upload
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
}
