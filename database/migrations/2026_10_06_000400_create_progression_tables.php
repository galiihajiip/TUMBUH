<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->string('key', 40)->unique();
            $table->string('name');
            $table->text('description');
            $table->string('type', 30); // tier_upgrade | credit | learning | mentoring | consultation | insight
            $table->string('criteria'); // human-readable unlock rule
            $table->string('icon', 40);
            $table->timestamps();
        });

        Schema::create('business_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reward_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('unlocked'); // unlocked | claimed
            $table->string('reason');
            $table->timestamp('unlocked_at');
            $table->timestamp('claimed_at')->nullable();
            $table->timestamps();
            $table->unique(['business_id', 'reward_id']);
        });

        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('key', 40);
            $table->string('title');
            $table->string('description');
            $table->string('icon', 40)->default('workspace_premium');
            $table->timestamp('earned_at');
            $table->timestamps();
            $table->unique(['business_id', 'key']);
        });

        // Peer-cohort leaderboard inputs for businesses that are not fully
        // simulated (seeded peers). Real/demo businesses are computed live.
        Schema::create('leaderboard_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->date('period');
            $table->decimal('target_achievement', 5, 2);
            $table->decimal('mission_completion', 5, 2);
            $table->decimal('kpi_improvement', 5, 2);
            $table->decimal('data_discipline', 5, 2);
            $table->decimal('score', 5, 2);
            $table->timestamps();
            $table->unique(['business_id', 'period']);
        });

        Schema::create('expansion_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 20); // readiness | sitesense | msight
            $table->json('inputs');
            $table->unsignedSmallInteger('score')->nullable();
            $table->json('result');
            $table->string('source', 20)->default('simulated');
            $table->timestamps();
        });

        Schema::create('dce_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('stage', 20)->default('eligibility'); // eligibility | application | profile_submitted | selection | mentoring
            $table->unsignedSmallInteger('eligibility_score');
            $table->json('profile')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['dce_applications', 'expansion_assessments', 'leaderboard_snapshots', 'achievements', 'business_rewards', 'rewards'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
