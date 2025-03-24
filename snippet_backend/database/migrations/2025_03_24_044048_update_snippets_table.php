<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('snippets', function (Blueprint $table) {
            $table->renameColumn('is_favorite', 'is_favourite');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('snippets', function (Blueprint $table) {
            $table->renameColumn('is_favourite', 'is_favorite');
        });
    }
};
