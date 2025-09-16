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
        Schema::create('transaction_assign_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('action_by');      // who performed the reassignment
            $table->unsignedBigInteger('from_member_id'); // member that was reassigned
            $table->unsignedBigInteger('from_user_id')->nullable();   // old marketing user
            $table->unsignedBigInteger('to_user_id');     // new marketing user
            $table->integer('moved_count');               // how many transactions were moved
            $table->timestamps();

            $table->index('action_by');
            $table->index('from_member_id');
            $table->index('from_user_id');
            $table->index('to_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_assign_logs');
    }
};
