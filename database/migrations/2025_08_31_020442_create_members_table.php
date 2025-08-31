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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('name');                      // nama member
            $table->string('nama_rekening')->nullable(); // nama rekening bank
            $table->string('username')->unique();
            $table->string('phone');

            // langsung relasi ke users.id
            $table->foreignId('marketing_id')
                ->constrained('users');

            $table->foreignId('team_id')
                ->constrained('teams');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
