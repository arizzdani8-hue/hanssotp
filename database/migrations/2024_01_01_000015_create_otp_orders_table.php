<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_id', 30)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('country_id')->constrained();
            $table->foreignId('service_id')->constrained('otp_services');
            $table->foreignId('operator_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('provider_id')->constrained('otp_providers');
            $table->foreignId('pricing_id')->nullable()->constrained('otp_pricing')->nullOnDelete();
            $table->string('provider_order_id')->nullable();
            $table->string('phone_number', 30)->nullable();
            $table->string('otp_code', 20)->nullable();
            $table->enum('status', ['pending', 'waiting', 'received', 'cancelled', 'expired', 'refunded'])->default('pending');
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('cost', 12, 2)->default(0);
            $table->decimal('profit', 12, 2)->default(0);
            $table->boolean('is_refunded')->default(false);
            $table->text('full_sms')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('provider_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_orders');
    }
};
