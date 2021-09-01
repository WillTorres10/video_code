<?php

namespace App\Observers;

use App\Models\Gender;
use Illuminate\Support\Str;

class GenderObserver
{
    public function creating(Gender $gender)
    {
        $gender->id = Str::uuid();
   }
}
