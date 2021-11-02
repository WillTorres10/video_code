<?php

namespace App\Models;

use App\Models\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class Gender extends Model
{
    use HasFactory, SoftDeletes, UUID;

    protected $fillable = ['name', 'is_active'];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}
