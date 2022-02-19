<?php

namespace Tests\Traits;

use Illuminate\Support\Facades\Storage;

trait TestStorages
{
    protected function deleteAllFiles()
    {
        foreach (Storage::directories() as $dir) {
            Storage::delete(Storage::files($dir));
            Storage::deleteDirectory($dir);
        }
    }
}
