<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gender extends Model
{
    use HasFactory, SoftDeletes;

    protected $keyType = 'string';

    protected $fillable = ['name', 'is_active'];

    public $incrementing = false;

    protected $dates = ['deleted_at'];

    protected $casts = [
        'id' => 'string',
        'is_active' => 'boolean'
    ];
}
