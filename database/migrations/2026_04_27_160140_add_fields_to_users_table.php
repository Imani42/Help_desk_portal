<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->string('phone')->nullable();
            $table->string('region')->nullable();
            $table->string('district')->nullable();
            $table->string('ward')->nullable();
            $table->string('street')->nullable();
            $table->string('tech_base')->nullable();
            $table->string('role')->default('customer');

        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropColumn([
                'phone',
                'region',
                'district',
                'ward',
                'street',
                'tech_base',
                'role'
            ]);

        });
    }
};