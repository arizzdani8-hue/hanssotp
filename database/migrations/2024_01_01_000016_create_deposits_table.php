<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('gateway');
            $table->string('channel_code')->nullable();
            $table->string('icon')->nullable();
            $table->decimal('fee_flat', 12, 2)->default(0);
            $table->decimal('fee_percent', 5, 2)->default(0);
            $table->decimal('min_amount', 12, 2)->default(10000);
            $table->decimal('max_amount', 12, 2)->default(10000000);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_id', 30)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();
            $table->string('gateway');
            $table->decimal('amount', 15, 2);
            $table->decimal('fee', 12, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->enum('status', ['pending', 'paid', 'expired', 'failed', 'cancelled'])->default('pending');
            $table->string('gateway_reference')->nullable();
            $table->string('qr_url')->nullable();
            $table->string('checkout_url')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('gateway_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposits');
        Schema::dropIfExists('payment_methods');
    }
};
