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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_number', 20)->unique();
            $table->string('sender_name');
            $table->string('sender_phone', 18);
            $table->string('receiver_name');
            $table->string('receiver_phone', 18);
            $table->string('receiver_address');
            $table->float('delivery_fee', 20, 2)->default(0);
            $table->string('sprinter')->nullable();
            $table->string('note')->nullable();
            $table->enum('status', ['pending', 'process', 'delivering', 'done', 'cancel'])->default('pending');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
