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
            $table->text('reason')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->string('status')->default('banned');
            
            $table->enum('type', ['user', 'ip'])->default('user');
            $table->string('duration')->nullable();
            $table->timestamp('unbanned_at')->nullable();
            $table->unsignedBigInteger('banned_by')->nullable();
            $table->string('ip_address', 45)->nullable();
            
            $table->integer('warning_level')->default(0);
            $table->boolean('auto_ban')->default(false);
            $table->enum('appeal_status', ['none', 'pending', 'approved', 'rejected'])->default('none');
            
            $table->timestamps();
            
            $table->foreign('banned_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index('type');
            $table->index('status');
            $table->index('user_id');
            $table->index('ip');
            $table->index('created_at');
            $table->index('appeal_status');
            $table->index('warning_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ban_logs');
    }
};