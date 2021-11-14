<?php

namespace App\Models;

use App\Models\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class Category extends Model
{
    use HasFactory, SoftDeletes, UUID;

    protected $fillable = ['name', 'description', 'is_active'];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function videos()
    {
        return $this->belongsToMany(Video::class);
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class);
    }
}
