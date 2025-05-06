<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('posts', function (Blueprint $table) {
        $table->boolean('published')->default(true); // Adds the 'published' column as a boolean
    });
}

public function down()
{
    Schema::table('posts', function (Blueprint $table) {
        $table->dropColumn('published'); // Drops the 'published' column if we rollback
    });
}

};
