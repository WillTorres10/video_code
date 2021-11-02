<?php

namespace App\Observers;

use Illuminate\Support\Str;

class UUID
{
    public function creating($model)
    {
        $model->id = (string) Str::uuid();
    }
}
