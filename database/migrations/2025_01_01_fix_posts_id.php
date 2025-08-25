<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        // Step 1: Ensure sequence exists
        DB::statement("CREATE SEQUENCE IF NOT EXISTS posts_id_seq");

        // Step 2: Attach sequence to posts.id column
        DB::statement("ALTER TABLE posts ALTER COLUMN id SET DEFAULT nextval('posts_id_seq')");

        // Step 3: Fix duplicate IDs if any exist
        $duplicates = DB::select("
            SELECT id, COUNT(*) as cnt
            FROM posts
            GROUP BY id
            HAVING COUNT(*) > 1
        ");

        foreach ($duplicates as $dup) {
            $rows = DB::select("SELECT ctid, id FROM posts WHERE id = ?", [$dup->id]);
            $skip = true;
            foreach ($rows as $row) {
                if ($skip) { $skip = false; continue; }
                // Assign next value from sequence
                $newId = DB::selectOne("SELECT nextval('posts_id_seq') as new_id")->new_id;
                DB::update("UPDATE posts SET id = ? WHERE ctid = ?", [$newId, $row->ctid]);
            }
        }

        // Step 4: Add primary key if not exists
        try {
            DB::statement("ALTER TABLE posts ADD CONSTRAINT posts_pkey PRIMARY KEY (id)");
        } catch (\Exception $e) {
            // ignore error if PK already exists
        }

        // Step 5: Reseed sequence to MAX(id)+1
        DB::statement("SELECT setval('posts_id_seq', (SELECT MAX(id) FROM posts)+1)");
    }

    public function down() {
        // Rollback
        DB::statement("ALTER TABLE posts ALTER COLUMN id DROP DEFAULT");
        DB::statement("ALTER TABLE posts DROP CONSTRAINT IF EXISTS posts_pkey");
        DB::statement("DROP SEQUENCE IF EXISTS posts_id_seq");
    }
};
