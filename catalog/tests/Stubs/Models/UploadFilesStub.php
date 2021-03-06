<?php
namespace Tests\Stubs\Models;

use App\Models\DTOs\FileRulesValidation;
use App\Models\Traits\UploadFiles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UploadFilesStub extends Model
{
    use UploadFiles;

    protected $table = "upload_file_stubs";

    protected $fillable = ['name', 'file1', 'file2'];

    public static array $fileFields = ['file1', 'file2'];

    public static function makeTable()
    {
        Schema::create('upload_file_stubs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('file1')->nullable();
            $table->string('file2')->nullable();
            $table->timestamps();
        });
    }

    public static function getFilesFieldsRules(): array
    {
        return [
            new FileRulesValidation(field: 'file1', required: false, typeValidation: "image", maxKilobytes: 10000),
            new FileRulesValidation(field: 'file2', required: true, typeValidation: "mimetypes:video/mp4", maxKilobytes: 10000),
        ];
    }

    public static function dropTable()
    {
        Schema::dropIfExists('upload_file_stubs');
    }

    public static function prepareToUse()
    {
        self::dropTable();
        self::makeTable();
    }

    protected function uploadDir(): string
    {
        return "1";
    }

}
