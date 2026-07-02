<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ban_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ban_log_id')->constrained('ban_logs')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('note');
            $table->boolean('is_internal')->default(true);
            $table->timestamps();
            
            $table->index('ban_log_id');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ban_notes');
    }
};