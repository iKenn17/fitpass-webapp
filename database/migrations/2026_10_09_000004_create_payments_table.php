<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            // null for walk-in payers who are not members
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->string('payer_name');
            // monthly | walk_in
            $table->string('kind')->default('monthly');
            $table->decimal('amount', 10, 2);
            // gcash | cash
            $table->string('method')->default('cash');
            $table->dateTime('paid_at');
            $table->timestamps();

            $table->index('paid_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
