<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('info_banners', function (Blueprint $table) {
            $table->id();
            $table->text('message')->nullable();
            $table->boolean('is_visible')->default(false); // hide by default
            $table->timestamps();
        });

        // Optional: seed default banner
        DB::table('info_banners')->insert([
        'message' => '',
        'is_visible' => false, // hidden by default
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    }

    public function down(): void
    {
        Schema::dropIfExists('info_banners');
    }
};
