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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('folder_id')->nullable()->constrained('folders')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['folder_id', 'name']);
            $table->index('name');
        });

        DB::statement(
            'CREATE UNIQUE INDEX files_root_name_unique
            ON files(name)
            WHERE folder_id IS NULL'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
