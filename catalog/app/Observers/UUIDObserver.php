<?php

namespace App\Observers;

use Illuminate\Support\Str;

class UUIDObserver
{
    public function creating($model)
    {
        $model->id = (string) Str::uuid();
    }
}
