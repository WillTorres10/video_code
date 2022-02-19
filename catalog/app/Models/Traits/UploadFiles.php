<?php

namespace App\Models\Traits;

use App\Exceptions\ConfigurationNotSettedException;
use App\Models\DTOs\FileRulesValidation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

trait UploadFiles
{
    public $oldFiles = [];

    abstract protected function uploadDir(): string;

    /**
     * @return FileRulesValidation[]
     */
    abstract public static function getFilesFieldsRules(): array;

    public static function bootUploadFiles()
    {
        static::updating(function (Model $model){
            $fieldsUpdated = array_keys($model->getDirty());
            $filesUpdated = array_intersect($fieldsUpdated, self::$fileFields);
            $fileFiltered = Arr::where($filesUpdated, static fn ($fileField) => $model->getOriginal($fileField));
            $model->oldFiles = array_map(function ($fileField) use ($model) {
                return $model->getOriginal($fileField);
            }, $fileFiltered);
        });
    }

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

    public function deleteOldFiles()
    {
        $this->deleteFiles($this->oldFiles);
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

    /**
     * @throws \Spatie\DataTransferObject\Exceptions\UnknownProperties
     * @return array
     */
    public static function validationRulesFiles(): array
    {
        $rules = [];
        foreach (self::getFilesFieldsRules() as $fieldsRule) {
            $rules[$fieldsRule->field] = $fieldsRule->getArrayValidationRules();
        }
        return $rules;
    }

    public function relativeFilePath($value)
    {
        return "{$this->uploadDir()}/{$value}";
    }

    protected function getFileUrl($filename)
    {
        return Storage::url($this->relativeFilePath($filename));
    }
}
