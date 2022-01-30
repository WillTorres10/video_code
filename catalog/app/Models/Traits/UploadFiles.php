<?php

namespace App\Models\Traits;

use App\Exceptions\ConfigurationNotSettedException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait UploadFiles
{
    abstract protected function uploadDir(): string;

    /**
     * @param UploadedFile[] $files
     * @return void
     */
    public function uploadFiles(array $files)
    {
        foreach ($files as $file) {
            $this->uploadFile($file);
        }
    }

    /**
     * @param UploadedFile $file
     * @return void
     */
    public function uploadFile(UploadedFile $file)
    {
        $file->store($this->uploadDir());
    }

    /**
     * @param array $files
     * @return void
     */
    public function deleteFiles(array $files)
    {
        foreach ($files as $file) {
            $this->deleteFile($file);
        }
    }

    /**
     * @param string|UploadedFile $file
     * @return void
     */
    public function deleteFile($file)
    {
        $filename = $file instanceof UploadedFile ? $file->hashName() : $file;
        Storage::delete("{$this->uploadDir()}/{$filename}");
    }

    /**
     * @param array $attributes
     * @return UploadedFile[]
     * @throws ConfigurationNotSettedException
     */
    public static function extractFiles(array &$attributes = []): array
    {
        if (!isset(self::$fileFields)) {
            throw new ConfigurationNotSettedException(field: '$fileFields');
        }
        $files = [];
        foreach (self::$fileFields as $field) {
            if (isset($attributes[$field]) && $attributes[$field] instanceof UploadedFile) {
                $files[] = $attributes[$field];
                $attributes[$field] = $attributes[$field]->hashName();
            }
        }
        return $files;
    }
}
