<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manager_report_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->unique(['manager_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manager_report_comments');
    }
};
