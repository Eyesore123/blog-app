<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('posts', function (Blueprint $table) {
        $table->json('translations')->nullable()->after('content');
    });
}

    public function down()
    {
        if (Schema::hasColumn('posts', 'translations')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropColumn('translations');
                });
            };
    }

};
