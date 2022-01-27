<?php
namespace Tests\Stubs\Models;

use App\Models\Traits\UploadFiles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UploadFilesStub extends Model
{
    use UploadFiles;

    protected function uploadDir(): string
    {
        return "1";
    }


//    protected $table = 'category_stubs';
//    protected $fillable = ['name', 'description'];
//
//    public static function createTable()
//    {
//        Schema::create('category_stubs', function (Blueprint $table) {
//            $table->id();
//            $table->string('name');
//            $table->text('description')->nullable();
//            $table->timestamps();
//        });
//    }
//
//    public static function dropTable()
//    {
//        Schema::dropIfExists('category_stubs');
//    }
}
