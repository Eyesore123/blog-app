<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostTagTable extends Migration
{
    public function up()
    {
        Schema::create('post_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade'); // Foreign key to posts table
            $table->foreignId('tag_id')->constrained()->onDelete('cascade'); // Foreign key to tags table
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('post_tag');
    }
}