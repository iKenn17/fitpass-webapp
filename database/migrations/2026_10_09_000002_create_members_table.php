<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('member_code')->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            // Regular | Student | Senior
            $table->string('type')->default('Regular');
            // Monthly Unlimited | Quarterly
            $table->string('plan')->default('Monthly Unlimited');
            $table->date('joined_at');
            $table->date('expires_at');
            $table->timestamps();

            $table->index('type');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
