<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group')->default('general');
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->string('type')->default('text');
            $table->timestamps();
        });

        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('source');
            $table->string('event')->nullable();
            $table->json('payload')->nullable();
            $table->json('headers')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->integer('response_code')->nullable();
            $table->text('response_body')->nullable();
            $table->boolean('is_valid')->default(true);
            $table->timestamps();
        });

        Schema::create('api_request_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('method', 10);
            $table->string('endpoint');
            $table->json('request_body')->nullable();
            $table->integer('response_code');
            $table->json('response_body')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->float('response_time')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_request_logs');
        Schema::dropIfExists('webhook_logs');
        Schema::dropIfExists('settings');
    }
};
