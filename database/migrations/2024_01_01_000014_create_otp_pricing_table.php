<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_pricing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('otp_services')->cascadeOnDelete();
            $table->foreignId('operator_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('provider_id')->constrained('otp_providers')->cascadeOnDelete();
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('sell_price', 12, 2)->default(0);
            $table->decimal('sell_price_gold', 12, 2)->default(0);
            $table->decimal('sell_price_platinum', 12, 2)->default(0);
            $table->decimal('markup_percent', 5, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('provider_service_code')->nullable();
            $table->string('provider_country_code')->nullable();
            $table->timestamps();

            $table->index(['country_id', 'service_id', 'provider_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_pricing');
    }
};
