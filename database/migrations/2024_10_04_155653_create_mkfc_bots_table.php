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
        Schema::create('mkfc_bots', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('telegram_id')->unique();   // User's Telegram ID
            $table->string('telegram_username')->nullable(); // User's Telegram Username
            $table->string('earns')->default(0);     // MKFC earnings
            $table->string('my_referral_code')->unique();   // User's unique referral code
            $table->bigInteger('referred_by')->nullable();  // User who referred them (if any)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mkfc_bots');
    }
};
