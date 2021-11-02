<?php

namespace App\Models;

use App\Models\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class CastMember extends Model
{
    use HasFactory, SoftDeletes, UUID;

    const TYPE_DIRECTOR = 1;
    const TYPE_ACTOR = 2;

    public static $types = [
        CastMember::TYPE_DIRECTOR,
        CastMember::TYPE_ACTOR,
    ];

    protected $fillable = ['name', 'type'];
    protected $dates = ['deleted_at'];

    protected $casts = [
        'type' => 'integer'
    ];
}
