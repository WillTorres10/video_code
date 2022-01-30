<?php

namespace Tests\Unit\Models;

use App\Exceptions\ConfigurationNotSettedException;
use App\Models\Traits\UploadFiles;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
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

    public function testThrowErrorVariableFileFieldsNotSetted()
    {
        try {
            $files = [];
            UploadFiles::extractFiles($files);
        } catch (ConfigurationNotSettedException $e) {
            $this->assertTrue(true);
        }
    }

    public function testExtractFiles()
    {
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
