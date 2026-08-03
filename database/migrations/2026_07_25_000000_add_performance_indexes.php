<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('faults', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'faults_user_created_index');
            $table->index(['user_id', 'status'], 'faults_user_status_index');
            $table->index(['technician_id', 'created_at'], 'faults_technician_created_index');
            $table->index(['technician_id', 'status'], 'faults_technician_status_index');
            $table->index('created_at', 'faults_created_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index(['role', 'created_at'], 'users_role_created_index');
            $table->index(['role', 'region', 'is_approved'], 'users_role_region_approved_index');
        });
    }

    public function down(): void
    {
        Schema::table('faults', function (Blueprint $table) {
            $table->dropIndex('faults_user_created_index');
            $table->dropIndex('faults_user_status_index');
            $table->dropIndex('faults_technician_created_index');
            $table->dropIndex('faults_technician_status_index');
            $table->dropIndex('faults_created_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_created_index');
            $table->dropIndex('users_role_region_approved_index');
        });
    }
};
