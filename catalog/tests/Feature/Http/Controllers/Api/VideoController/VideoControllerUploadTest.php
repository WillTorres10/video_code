<?php

namespace Tests\Feature\Http\Controllers\Api\VideoController;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class VideoControllerUploadTest extends BaseVideoControllerTestCase
{
    public function testInvalidationFiles()
    {
        // Check if is file
        $this->assertInvalidationInStoreAction(['video_file' => 'test'], 'file');
        // Check max size of file
        $file = UploadedFile::fake()->create('video.mp4', 21000);
        $this->assertInvalidationInStoreAction(['video_file' => $file], 'max.file');
        // Check mimetype of file
        $file = UploadedFile::fake()->create('video.txt', 10000);
        $this->assertInvalidationInStoreAction(['video_file' => $file], 'mimetypes');
    }

    public function testUploadFileStore()
    {
        Storage::fake();
        $generated = $this->generateGenresWithCategories();
        $file = UploadedFile::fake()->create('file.mp4', 10000);
        $data = $this->sendData + $this->selectGenresAndCategories($generated) + [
                'video_file' => $file
            ];
        $response = $this->json('POST', $this->routeStore(), $data);
        $id = $response->json('id');
        $file_name = $response->json('video_file');
        Storage::assertExists("$id/$file_name");
    }
}
