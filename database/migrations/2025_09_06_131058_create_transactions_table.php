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
        Schema::create('transactions', function (Blueprint $table) {
            // $table->id();
            $table->uuid('id')->primary();

            $table->foreignId('member_id')
                ->constrained('members')
                ->cascadeOnDelete();

             $table->foreignId('user_id')
                ->constrained('users');

            $table->decimal('amount', 15, 2);
            $table->date('transaction_date');
            $table->string("type");
            $table->string("username")->nullable();
            $table->string('phone')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
