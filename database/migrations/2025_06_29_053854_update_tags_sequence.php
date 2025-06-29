<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateTagsSequence extends Migration
{
    public function up()
    {
        DB::statement("SELECT setval('tags_id_seq', (SELECT MAX(id) + 1 FROM tags))");
    }

    public function down()
    {
        // No need to roll back this change
    }
}