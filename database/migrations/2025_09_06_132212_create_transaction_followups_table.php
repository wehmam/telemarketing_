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
        Schema::create('transaction_followups', function (Blueprint $table) {
            $table->id();

            $table->uuid('transaction_id');
            $table->foreign('transaction_id')
                ->references('id')->on('transactions')
                ->cascadeOnDelete();

            // User (marketing/staff) yang follow up
            $table->foreignId('user_id')
                ->constrained('users');

            $table->text('note')->nullable();
            $table->timestamp('followed_up_at')->useCurrent(); 

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_followups');
    }
};
