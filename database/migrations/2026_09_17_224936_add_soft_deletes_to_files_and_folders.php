<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->dropUnique(['parent_id', 'name']);
            $table->softDeletes();
        });

        Schema::table('files', function (Blueprint $table) {
            $table->dropUnique(['folder_id', 'name']);
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->unique(['parent_id', 'name']);
        });

        Schema::table('files', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->unique(['folder_id', 'name']);
        });
    }
};