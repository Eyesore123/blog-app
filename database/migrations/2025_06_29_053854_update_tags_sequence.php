<?php

use Illuminate\Database\Migrations\Migration;
use Illuinate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class UpdateTagsSequence extends Migration
{
    public function up()
    {
        DB::statement("ALTER SEQUENCE tags_id_seq RESTART WITH 76");
    }

    public function down()
    {
        // No need to roll back this change
    }
}