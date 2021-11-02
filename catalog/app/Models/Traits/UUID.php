<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\App;

trait UUID
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if($this->casts && count($this->casts) > 0) {
            $this->casts['id'] = 'string';
        } else {
            $this->casts = ['id' => 'string'];
        }
        $this->incrementing = false;
        $this->keyType = 'string';
    }
}
