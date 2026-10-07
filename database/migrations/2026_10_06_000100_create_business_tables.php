<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->nullable()->index();
            // Identity
            $table->string('category')->nullable();
            $table->string('sub_category')->nullable();
            $table->text('description')->nullable();
            $table->string('logo_path')->nullable();
            $table->unsignedSmallInteger('start_year')->nullable();
            // Location
            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('address')->nullable();
            $table->boolean('has_physical_store')->default(false);
            $table->unsignedSmallInteger('outlets')->default(0);
            // Scale
            $table->unsignedInteger('employees')->nullable();
            $table->string('revenue_range', 20)->nullable();
            $table->unsignedInteger('avg_monthly_transactions')->nullable();
            $table->unsignedBigInteger('avg_order_value')->nullable();
            $table->unsignedInteger('monthly_customers')->nullable();
            // Channels & tools
            $table->json('sales_channels')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('marketplace_url')->nullable();
            $table->json('digital_tools')->nullable();
            $table->json('telkomsel_products')->nullable();
            // Program state
            $table->string('tier', 10)->default('bronze');
            $table->timestamp('tier_expires_at')->nullable();
            $table->string('tier_note')->nullable();
            $table->string('passport_level', 20)->default('digital_ready');
            $table->unsignedTinyInteger('onboarding_step')->default(1);
            $table->timestamp('onboarded_at')->nullable();
            $table->timestamp('goals_set_at')->nullable();
            $table->unsignedSmallInteger('growth_cycle')->default(1);
            $table->string('demo_key', 30)->nullable()->index();
            $table->boolean('is_peer')->default(false); // leaderboard peer (no login)
            $table->timestamps();
        });

        Schema::create('business_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('goal_key', 40);
            $table->string('metric', 30)->nullable();
            $table->decimal('baseline_value', 15, 2)->nullable();
            $table->decimal('target_value', 15, 2)->nullable();
            $table->unsignedSmallInteger('target_period_days')->nullable();
            $table->date('target_date')->nullable();
            $table->timestamps();
            $table->unique(['business_id', 'goal_key']);
        });

        Schema::create('data_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('consent_type', 20);
            $table->boolean('granted')->default(false);
            $table->timestamp('granted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'consent_type']);
        });

        Schema::create('growth_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('action_label')->nullable();
            $table->string('action_url')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growth_notifications');
        Schema::dropIfExists('data_consents');
        Schema::dropIfExists('business_goals');
        Schema::dropIfExists('businesses');
    }
};
