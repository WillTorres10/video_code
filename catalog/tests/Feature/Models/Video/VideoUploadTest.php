<?php

namespace Tests\Feature\Models\Video;

use App\Models\Video;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Support\Facades\{Event, Storage};
use Tests\Exceptions\TestException;
use Tests\Traits\TestUploadHooks;
use Illuminate\Support\Facades\Config;

class VideoUploadTest extends BaseVideoTestCase
{
    use TestUploadHooks;

    protected function getModel()
    {
        return Video::class;
    }

    public function testCreateWithFiles()
    {
        Storage::fake();
        $video = Video::create($this->data + $this->generateArrayFilesUploadForModel());
        foreach (Video::$fileFields as $field) {
            Storage::assertExists("{$video->id}/{$video->{$field}}");
        }
    }

    public function testCreateIfRollbackFiles()
    {
        Storage::fake();
        Event::listen(TransactionCommitted::class, static fn() => throw new TestException());
        $hasError = false;

        try {
            Video::create($this->data + $this->generateArrayFilesUploadForModel());
        } catch (TestException $e) {
            $this->assertCount(0, Storage::allFiles());
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    /**
     * @throws \Exception
     */
    public function testUpdateWithFiles()
    {
        Storage::fake();
        $video = Video::factory()->create();
        $files = $this->generateArrayFilesUploadForModel();
        $video->update($this->data + $files);
        $fileFieldsData = [];
        foreach (Video::$fileFields as $field) {
            $fileFieldsData[$field] = $video->{$field};
            Storage::assertExists("{$video->id}/{$fileFieldsData[$field]}");
        }
        $video->refresh();
        $this->assertDatabaseHas('videos', $this->data + $fileFieldsData + ['id' => $video->id, 'opened' => false]);

        $files2 = $this->generateArrayFilesUploadForModel();
        $video->update($this->data + ['opened' => true] + $files2);
        $this->assertTrue($video->opened);
        $fileFieldsData2 = [];
        foreach (Video::$fileFields as $field) {
            $fileFieldsData2[$field] = $files2[$field]->hashName();
            Storage::assertMissing("{$video->id}/{$files[$field]->hashName()}");
            Storage::assertExists("{$video->id}/{$files2[$field]->hashName()}");
        }
        $this->assertDatabaseHas('videos', $this->data + $fileFieldsData2 + ['id' => $video->id, 'opened' => true]);
    }

    public function testUpdateIfRollbackFiles()
    {
        Storage::fake();
        $video = Video::factory()->create();
        Event::listen(TransactionCommitted::class, fn() => throw new TestException());
        $hasError = false;
        try {
            $video->update($this->data + $this->generateArrayFilesUploadForModel());
        } catch (TestException $e) {
            $this->assertCount(0, Storage::allFiles());
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    public function testFileUrlsWithLocalDriver()
    {
        $fileFields = [];
        foreach (Video::$fileFields as $field) {
            $fileFields[$field] = "$field.test";
        }
        $video = Video::factory()->create($fileFields);
        $baseUrl = config('filesystems.disks.public')['url'];
        foreach ($fileFields as $field => $value) {
            $fileUrl = $video->{"{$field}_url"};
            $this->assertEquals("{$baseUrl}/$video->id/$value", $fileUrl);
        }
    }

    public function testFileUrlsWithS3Driver()
    {
        $fileFields = [];
        foreach (Video::$fileFields as $field) {
            $fileFields[$field] = "$field.test";
        }
        $video = Video::factory()->create($fileFields);
        $baseUrl = config('filesystems.disks.s3.url');
        Config::set('filesystems.default', 's3');
        foreach ($fileFields as $field => $value) {
            $fileUrl = $video->{"{$field}_url"};
            $this->assertEquals("{$baseUrl}/$video->id/$value", $fileUrl);
        }
    }

    public function testFileUrlsIfNullWhenFieldsAreNull()
    {
        $video = Video::factory()->create();
        foreach (Video::$fileFields as $field) {
            $fileUrl = $video->{"{$field}_url"};
            $this->assertNull($fileUrl);
        }
    }
}
