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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
              $table->foreignId('order_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->uuid('transaction_uuid')->unique();

        $table->string('transaction_code')->nullable();

        $table->decimal('amount',10,2);

        $table->string('method')->default('esewa');

        $table->enum('status',[
            'pending',
            'paid',
            'failed'
        ])->default('pending');

        // store complete callback from esewa
        $table->json('callback_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
