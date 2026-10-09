<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // owner | admin | front_desk | trainer
            $table->string('role')->default('front_desk');
            $table->time('shift_start')->nullable();
            // on_shift | off_duty | on_leave
            $table->string('shift_status')->default('off_duty');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'shift_start', 'shift_status', 'is_active', 'last_login_at']);
        });
    }
};
