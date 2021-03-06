<?php

namespace Tests\Unit\Models\Traits;

use App\Exceptions\ConfigurationNotSettedException;
use App\Models\Traits\UploadFiles;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Stubs\Models\UploadFilesStub;
use Tests\TestCase;
use Tests\Traits\{TestUploadHooks, TestFileFieldsAndValidations};

class UploadFilesUnitTest extends TestCase
{
    use TestUploadHooks, TestFileFieldsAndValidations;

    protected UploadFilesStub $obj;

    protected function setUp():void
    {
        parent::setUp();
        $this->obj = new UploadFilesStub();
        Storage::fake();
    }

    protected function getModel()
    {
        return UploadFilesStub::class;
    }

    public function testUploadFile()
    {
        Storage::fake();
        $file = $this->factoryVideo(1);
        $this->obj->uploadFile($file);
        $this->assertFilesOnStorage([$file]);
    }

    public function testUploadFiles()
    {
        Storage::fake();
        $files = $this->factoryVideosArray(2);
        $this->obj->uploadFiles($files);
        $this->assertFilesOnStorage($files);
    }

    public function testDeleteFile()
    {
        Storage::fake();
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
        Storage::fake();
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
        Storage::fake();
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
        Storage::fake();
        try {
            $files = [];
            UploadFiles::extractFiles($files);
        } catch (ConfigurationNotSettedException $e) {
            $this->assertTrue(true);
        }
    }

    public function testExtractFiles()
    {
        Storage::fake();
        $attributes = [];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(0, $attributes);
        $this->assertCount(0, $files);

        $attributes = ['file1' => 'test'];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(1, $attributes);
        $this->assertEquals(['file1' => 'test'], $attributes);
        $this->assertCount(0, $files);

        $attributes = ['file1' => 'test', 'file2' => 'test'];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(2, $attributes);
        $this->assertEquals(['file1' => 'test', 'file2' => 'test'], $attributes);
        $this->assertCount(0, $files);

        $file1 = $this->factoryVideo(1);
        $attributes = ['file1' => $file1, 'other' => 'test'];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(2, $attributes);
        $this->assertEquals(['file1' => $file1->hashName(), 'other' => 'test'], $attributes);
        $this->assertCount(1, $files);
        $this->assertEquals([$file1], $files);

        $file2 = $this->factoryVideo(2);
        $attributes = ['file1' => $file1, 'file2' => $file2, 'other' => 'test'];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(3, $attributes);
        $this->assertEquals(['file1' => $file1->hashName(), 'file2' => $file2->hashName(),  'other' => 'test'], $attributes);
        $this->assertCount(2, $files);
        $this->assertEquals([$file1, $file2], $files);
    }

    public function testValidationRules()
    {
        $expected = [
            'file1' => ['nullable', 'file', "image", "max:10000"],
            'file2' => ['required', 'file', "mimetypes:video/mp4", "max:10000"],
        ];
        $this->assertEquals($expected, UploadFilesStub::validationRulesFiles());
    }

}
