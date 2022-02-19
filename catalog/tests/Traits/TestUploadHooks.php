<?php

namespace Tests\Traits;

use App\Models\DTOs\FileRulesValidation;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait TestUploadHooks
{
    use TestGetModel;

    /**
     * @param int $total
     * @return UploadedFile[]
     */
    protected function factoryVideosArray(int $total): array
    {
        $retorno = [];
        for ($i = 0; $i < $total; $i++){
            $retorno[] = $this->factoryVideo($i);
        }
        return $retorno;
    }

    /**
     * @param int $i
     * @param int $size
     * @return UploadedFile
     */
    protected function factoryVideo(int $i, int $size = 10): UploadedFile
    {
        return UploadedFile::fake()->create("video{$i}.mp4", $size);
    }

    /**
     * @param int $total
     * @return UploadedFile[]
     */
    protected function factoryImagesArray(int $total): array
    {
        $retorno = [];
        for ($i = 0; $i < $total; $i++){
            $retorno[] = $this->factoryVideo($i);
        }
        return $retorno;
    }

    /**
     * @param int $i
     * @param int $size
     * @return UploadedFile
     */
    protected function factoryImage(int $i, int $size = 0): UploadedFile
    {
        return UploadedFile::fake()->create("image{$i}.jpg", $size);
    }

    /**
     * @param UploadedFile[] $files
     * @return void
     */
    protected function assertFilesOnStorage(array $files): void
    {
        foreach ($files as  $file) {
            Storage::assertExists("1/{$file->hashName()}");
        }
    }

    /**
     * @param UploadedFile[] $files
     * @return string[]
     */
    protected function getHashNameFiles(array $files): array
    {
        $retorno = [];
        foreach ($files as $file) {
            $retorno[] = $file->hashName();
        }
        return $retorno;
    }

    /**
     * @param UploadedFile[] $files
     * @return void
     */
    protected function assertFilesMissingOnStorage(array $files): void
    {
        foreach ($files as $file) {
            Storage::assertMissing("1/{$file->hashName()}");
        }
    }

    /**
     * @param int $addToSize
     * @param bool $reverse
     * @return UploadedFile[]
     * @throws Exception
     */
    protected function generateArrayFilesUploadForModel(int $addToSize = 0, bool $reverse = false): array
    {
        $class = $this->getModel();
        $files = [];
        foreach ($class::getFilesFieldsRules() as $fieldRules){
            /* @var $fieldRules FileRulesValidation */
            $size = $fieldRules->maxKilobytes + $addToSize;
            $files[$fieldRules->field] = $this->getFileByTypeValidation($fieldRules->typeValidation, $size, $reverse);
        }
        return $files;
    }

    /**
     * @param string $type
     * @param int $size
     * @param bool $reverse
     * @return UploadedFile
     * @throws Exception
     */
    private function getFileByTypeValidation(string $type, int $size, bool $reverse): UploadedFile
    {
        $i = random_int(1, 20);
        $typeToGenerate = ($type === "image" && !$reverse) || ($type !== "image" && $reverse)
            ? "image" : "mimetypes:video/mp4";
        return match ($typeToGenerate) {
            "image" => $this->factoryImage($i, $size),
            "mimetypes:video/mp4" => $this->factoryVideo($i, $size)
        };
    }
}
