<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faults', function (Blueprint $table) {
            $table->id();

            // Customer (who reported)
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');

            $table->string('type');
            $table->text('description');
            $table->string('location');

            $table->enum('status', ['Pending','In Progress','Resolved'])
                  ->default('Pending');

            // Technician (assigned)
            $table->foreignId('technician_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faults');
    }
};