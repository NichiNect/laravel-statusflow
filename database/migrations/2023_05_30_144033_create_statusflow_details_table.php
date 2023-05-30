<?php

use App\Models\Statusflow;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('statusflow_details', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Statusflow::class)->onDelete('cascade');
            $table->string('current_status')->nullable();
            $table->string('next_status');
            $table->integer('level')->default(1);
            $table->string('description')->nullable();
            $table->tinyInteger('row_order')->nullable()->default(1);
            $table->tinyInteger('row_active')->default(1);
            $table->timestamps();

            $table->unique(['current_status', 'next_status', 'level'], 'status_matcher_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statusflow_details');
    }
};
