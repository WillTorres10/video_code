<?php

namespace Tests\Prod\Models\Traits;

use App\Exceptions\ConfigurationNotSettedException;
use App\Models\Traits\UploadFiles;
use Illuminate\Support\Facades\{Config, Storage};
use Tests\Stubs\Models\UploadFilesStub;
use Tests\TestCase;
use Tests\Traits\{TestProd, TestUploadHooks, TestStorages};

class UploadFilesProdTest extends TestCase
{
    use  TestProd, TestUploadHooks, TestStorages;

    protected UploadFilesStub $obj;

    protected function setUp():void
    {
        parent::setUp();
        $this->skipTestIfNotProd();
        $this->obj = new UploadFilesStub();
        Config::set('filesystems.default', 's3');
        $this->deleteAllFiles();
    }

    public function getModel()
    {
        return UploadFilesStub::class;
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
        $this->assertFilesMissingOnStorage([$file]);
        // Deleting by UploadedFile object
        $file = $this->factoryVideo(1);
        $this->obj->uploadFile($file);
        $this->obj->deleteFile($file);
        $this->assertFilesMissingOnStorage([$file]);
    }

    public function testDeleteFiles()
    {
        // Deleting by hash name
        $files = $this->factoryVideosArray(2);
        $this->obj->uploadFiles($files);
        $filenames = $this->getHashNameFiles($files);
        $this->obj->deleteFiles($filenames);
        $this->assertFilesMissingOnStorage($files);
        // Deleting by UploadedFile object
        $files = $this->factoryVideosArray(2);
        $this->obj->uploadFiles($files);
        $this->obj->deleteFiles($files);
        $this->assertFilesMissingOnStorage($files);
    }

    public function testDeleteOldFiles()
    {
        $files = $this->factoryVideosArray(2);
        $this->obj->uploadFiles($files);
        $this->obj->deleteOldFiles();
        $this->assertCount(2, Storage::allFiles());

        $this->obj->oldFiles = [$files[0]->hashName()];
        $this->obj->deleteOldFiles();
        $this->assertFilesMissingOnStorage([$files[0]]);
        $this->assertFilesOnStorage([$files[1]]);
    }

    public function testThrowErrorVariableFileFieldsNotSetted()
    {
        try {
            $files = [];
            UploadFiles::extractFiles($files);
        } catch (ConfigurationNotSettedException $e) {
            $this->assertTrue(true);
        }
    }
}
