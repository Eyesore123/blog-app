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
        $table->string('slug')->after('title'); // Add the column without UNIQUE!!! Otherise slug won't migrate
    });

    // Add the UNIQUE constraint separately
    Schema::table('posts', function (Blueprint $table) {
        $table->unique('slug');
    });
}

public function down()
{

}
};
