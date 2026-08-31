<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('generation_id')->nullable()->index();
            $table->unsignedInteger('version');
            $table->string('source', 30)->default('generation');
            $table->json('spec')->nullable();
            $table->longText('code')->nullable();
            $table->string('build_path')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'version']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->foreign('current_version_id')
                ->references('id')
                ->on('project_versions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['current_version_id']);
        });
        Schema::dropIfExists('project_versions');
    }
};
