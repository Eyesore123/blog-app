<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->after('user_id');
            $table->foreign('parent_id')->references('id')->on('comments')->onDelete('cascade');
        });
    }

    public function down()
    {
        // Drop the foreign key constraint only if it exists (PostgreSQL safe)
        DB::statement("
            DO $$
            BEGIN
                IF EXISTS (
                    SELECT 1 FROM information_schema.table_constraints
                    WHERE constraint_name = 'comments_parent_id_foreign'
                    AND table_name = 'comments'
                ) THEN
                    ALTER TABLE comments DROP CONSTRAINT comments_parent_id_foreign;
                END IF;
            END
            $$;
        ");

        // Then drop the column if it exists
        if (Schema::hasColumn('comments', 'parent_id')) {
            Schema::table('comments', function (Blueprint $table) {
                $table->dropColumn('parent_id');
            });
        }
    }
};