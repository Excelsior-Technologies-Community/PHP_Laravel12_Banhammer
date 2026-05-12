<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ban_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');

            $table->string('user_name')->nullable();
            $table->string('email')->nullable();

            $table->string('ip')->nullable();

            $table->string('reason')->nullable();

            $table->timestamp('expired_at')->nullable();

            $table->string('status')->default('banned');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ban_logs');
    }
};