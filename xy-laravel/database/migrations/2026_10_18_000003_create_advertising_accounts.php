<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advertising_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('platform'); // meta_ads, google_ads, tiktok_ads, meta_pixel
            $table->string('account_id');
            $table->string('account_name');
            $table->string('pixel_id')->nullable();
            $table->string('conversion_id')->nullable();
            $table->text('access_token')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('config')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index('platform');
            $table->unique(['tenant_id', 'platform', 'account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertising_accounts');
    }
};
