<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sketches', function (Blueprint $table) {
            $table->string('topic')->nullable();
            $table->boolean('published')->default(true);
            $table->string('image')->nullable();
            $table->text('tags')->nullable();
        });
    }

    public function down()
    {
        Schema::table('sketches', function (Blueprint $table) {
            $table->dropColumn(['topic', 'published', 'image', 'tags']);
        });
    }
};