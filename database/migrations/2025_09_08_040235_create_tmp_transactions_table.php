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
        Schema::create('tmp_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('username')->nullable();
            $table->string('phone', 20)->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->dateTime('transaction_date')->nullable();

            $table->unsignedBigInteger('entry_by')->nullable(); // who imported
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tmp_transactions');
    }
};
