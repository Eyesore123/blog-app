<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Only drop the constraint if it exists (PostgreSQL)
        DB::statement("
            DO $$
            BEGIN
                IF EXISTS (
                    SELECT 1 FROM information_schema.table_constraints
                    WHERE constraint_name = 'comments_user_id_foreign'
                    AND table_name = 'comments'
                ) THEN
                    ALTER TABLE comments DROP CONSTRAINT comments_user_id_foreign;
                END IF;
            END
            $$;
        ");
    }

    public function down(): void
    {
        Schema::table('comments', function ($table) {
            $table->foreignId('user_id')->change();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};