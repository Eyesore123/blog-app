<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('comments', function (Blueprint $table) {
            if (!Schema::hasColumn('comments', 'user_name')) {
                $table->string('user_name')->nullable();
            }
            if (!Schema::hasColumn('comments', 'guest_name')) {
                $table->string('guest_name')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('comments', function (Blueprint $table) {
            if (Schema::hasColumn('comments', 'user_name')) {
                $table->dropColumn('user_name');
            }
            if (Schema::hasColumn('comments', 'guest_name')) {
                $table->dropColumn('guest_name');
            }
        });
    }
};
