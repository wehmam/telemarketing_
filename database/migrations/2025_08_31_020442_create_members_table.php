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
            $table->string('phone')->nullable();

            // langsung relasi ke users.id
            $table->foreignId('marketing_id')
                ->nullable()
                ->constrained('users');

            $table->foreignId('team_id')
                ->nullable()
                ->constrained('teams');

            $table->string('batch_code')->nullable();
            $table->timestamp('import_at')->nullable();


            $table->timestamps();
            $table->softDeletes();

            $table->index('phone');
            $table->index('marketing_id');
            $table->index('team_id');
            $table->index('username');

            $table->index(['marketing_id', 'team_id']);
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
