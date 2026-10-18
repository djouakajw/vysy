<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regional_pricing', function (Blueprint $table) {
            $table->id();
            $table->string('plan_id'); // entry, business, prime, prime-plus
            $table->string('region_code'); // CM, CI, SN, FR, US, etc.
            $table->string('region_name');
            $table->decimal('price_monthly', 12, 2);
            $table->decimal('price_yearly', 12, 2)->nullable();
            $table->string('currency', 3)->default('XOF');
            $table->decimal('original_price_monthly', 12, 2)->nullable();
            $table->decimal('original_price_yearly', 12, 2)->nullable();
            $table->integer('discount_percent')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('plan_id');
            $table->index('region_code');
            $table->unique(['plan_id', 'region_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regional_pricing');
    }
};
