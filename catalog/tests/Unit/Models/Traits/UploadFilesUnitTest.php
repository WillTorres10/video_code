<?php

namespace Tests\Unit\Models;

use App\Models\CastMember;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Stubs\Models\UploadFilesStub;
use Tests\TestCase;

class UploadFilesUnitTest extends TestCase
{
    protected UploadFilesStub $obj;

    protected function setUp():void
    {
        parent::setUp();
        $this->obj = new UploadFilesStub();
        Storage::fake();
    }

    public function testUploadFile()
    {
        $file = $this->factoryVideo(1);
        $this->obj->uploadFile($file);
        $this->assertFilesOnStorage([$file]);
    }

    public function testUploadFiles()
    {
        $files = $this->factoryVideosArray(2);
        $this->obj->uploadFiles($files);
        $this->assertFilesOnStorage($files);
    }

    public function testDeleteFile()
    {
        // Deleting by hash name
        $file = $this->factoryVideo(1);
        $this->obj->uploadFile($file);
        $this->obj->deleteFile($file->hashName());
        $this->asserFilesMissingOnStorage([$file]);
        // Deleting by UploadedFile object
        $file = $this->factoryVideo(1);
        $this->obj->uploadFile($file);
        $this->obj->deleteFile($file);
        $this->asserFilesMissingOnStorage([$file]);
    }

    public function testDeleteFiles()
    {
        // Deleting by hash name
        $files = $this->factoryVideosArray(2);
        $this->obj->uploadFiles($files);
        $filenames = $this->getHashNameFiles($files);
        $this->obj->deleteFiles($filenames);
        $this->asserFilesMissingOnStorage($files);
        // Deleting by UploadedFile object
        $files = $this->factoryVideosArray(2);
        $this->obj->uploadFiles($files);
        $this->obj->deleteFiles($files);
        $this->asserFilesMissingOnStorage($files);
    }

    private function factoryVideosArray(int $total): array
    {
        $retorno = [];
        for ($i = 0; $i < $total; $i++){
            $retorno[] = $this->factoryVideo($i);
        }
        return $retorno;
    }

    private function factoryVideo(int $i): UploadedFile
    {
        return UploadedFile::fake()->create("video{$i}.mp4");
    }

    private function assertFilesOnStorage(array $files): void
    {
        foreach ($files as  $file) {
            Storage::assertExists("1/{$file->hashName()}");
        }
    }

    /**
     * @param UploadedFile[] $files
     * @return string[]
     */
    private function getHashNameFiles(array $files): array
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
    private function asserFilesMissingOnStorage(array $files): void
    {
        foreach ($files as $file) {
            Storage::assertMissing("1/{$file->hashName()}");
        }
    }

}
