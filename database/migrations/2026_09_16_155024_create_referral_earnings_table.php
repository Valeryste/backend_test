<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_earnings', function (Blueprint $table) {
            $table->id();

            // Кто получает вознаграждение (реферер).
            $table->foreignId('referrer_master_id')
                ->constrained('masters')
                ->cascadeOnDelete();

            // За кого начислено (приведённый мастер).
            $table->foreignId('referred_master_id')
                ->constrained('masters')
                ->cascadeOnDelete();

            // Связь с записью реферала.
            $table->foreignId('referral_id')
                ->constrained('referrals')
                ->cascadeOnDelete();


            $table->foreignId('payment_id')
                ->constrained('payments')
                ->cascadeOnDelete();

            // Сумма платежа в копейках.
            $table->unsignedBigInteger('payment_amount');

            // Сумма вознаграждения в копейках.
            $table->unsignedBigInteger('amount');

            // Процент вознаграждения.
            $table->unsignedTinyInteger('percent');

            $table->string('status', 16)
                ->default('pending');

            $table->timestamps();

            $table->unique('payment_id');

            $table->index(['referrer_master_id', 'status']);
            $table->index(['referred_master_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_earnings');
    }
};