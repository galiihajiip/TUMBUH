<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Configurable question bank.
        Schema::create('diagnostic_questions', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique();
            $table->string('dimension', 30);
            $table->string('section', 40);
            $table->text('prompt');
            $table->text('help')->nullable();
            $table->string('type', 20); // single | multi | number | percent | currency
            $table->json('options')->nullable(); // [{value,label,score}]
            $table->json('scoring')->nullable(); // numeric benchmark: {good, max}
            $table->decimal('weight', 4, 2)->default(1);
            $table->unsignedSmallInteger('position');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('diagnostics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('cycle')->default(1);
            $table->string('status', 20)->default('in_progress'); // in_progress | answered | completed
            $table->unsignedSmallInteger('current_index')->default(0);
            $table->unsignedBigInteger('kpi_snapshot_id')->nullable(); // KPI anchor for this cycle
            $table->timestamp('answered_at')->nullable();
            $table->timestamp('baseline_confirmed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('diagnostic_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diagnostic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('diagnostic_question_id')->constrained()->cascadeOnDelete();
            $table->json('value');
            $table->decimal('normalized_score', 5, 2)->nullable();
            $table->timestamps();
            $table->unique(['diagnostic_id', 'diagnostic_question_id']);
        });

        // Growth Ledger: one row per business per month.
        Schema::create('kpi_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->date('period'); // first day of month
            $table->boolean('is_baseline')->default(false);
            $table->unsignedBigInteger('revenue')->default(0);
            $table->unsignedInteger('new_customers')->default(0);
            $table->unsignedInteger('total_customers')->default(0);
            $table->decimal('conversion_rate', 5, 2)->default(0); // %
            $table->decimal('repeat_rate', 5, 2)->default(0); // %
            $table->unsignedBigInteger('marketing_spend')->default(0);
            $table->unsignedInteger('campaign_reach')->default(0);
            $table->unsignedInteger('leads')->default(0);
            $table->decimal('gross_margin', 5, 2)->nullable(); // %
            $table->decimal('digital_txn_share', 5, 2)->default(0); // % of sales recorded digitally (productivity proxy)
            $table->unsignedSmallInteger('growth_score')->nullable();
            $table->string('source', 20)->default('self_reported');
            $table->json('contributions')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
            $table->unique(['business_id', 'period']);
        });

        Schema::create('growth_score_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('diagnostic_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('overall');
            $table->json('dimensions');
            $table->string('strongest', 30);
            $table->string('bottleneck', 30);
            $table->string('secondary', 30)->nullable();
            $table->string('priority', 10);
            $table->json('changes')->nullable(); // per-dimension delta vs previous snapshot
            $table->string('trigger', 30)->default('diagnostic');
            $table->text('explanation')->nullable();
            $table->timestamp('calculated_at');
            $table->timestamps();
        });

        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('growth_score_snapshot_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kind', 20)->default('assistant'); // assistant | review
            $table->string('bottleneck', 30);
            $table->string('headline');
            $table->text('whats_wrong');
            $table->text('why_it_matters');
            $table->text('what_to_do');
            $table->text('success_metric');
            $table->json('evidence'); // [{label,value,tone}]
            $table->json('solution_keys');
            $table->json('review')->nullable(); // {worked, didnt, next}
            $table->boolean('is_current')->default(true);
            $table->timestamps();
        });

        Schema::create('action_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recommendation_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('cycle')->default(1);
            $table->string('bottleneck', 30);
            $table->string('title');
            $table->string('goal');
            $table->string('kpi_metric', 30);
            $table->decimal('kpi_baseline', 15, 2)->nullable();
            $table->decimal('kpi_target', 15, 2)->nullable();
            $table->date('starts_on');
            $table->string('status', 20)->default('active'); // active | completed | superseded
            $table->timestamps();
        });

        Schema::create('action_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('action_plan_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('week');
            $table->string('phase', 20); // Understand | Activate | Optimize | Prove
            $table->string('title');
            $table->string('goal');
            $table->json('actions');
            $table->string('solution_key', 30)->nullable();
            $table->string('kpi_target');
            $table->string('status', 20)->default('pending'); // pending | active | done
            $table->timestamps();
        });

        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('action_plan_item_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('week');
            $table->string('title');
            $table->text('description');
            $table->string('kpi_metric', 30)->nullable();
            $table->string('kpi_label')->nullable();
            $table->decimal('kpi_target', 15, 2)->nullable();
            $table->string('solution_key', 30)->nullable();
            $table->date('due_on')->nullable();
            $table->string('status', 20)->default('upcoming'); // upcoming | active | completed | partial | missed
            $table->unsignedSmallInteger('points')->default(10);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('mission_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained()->cascadeOnDelete();
            $table->string('completion', 10); // yes | partial | no
            $table->decimal('kpi_value', 15, 2)->nullable();
            $table->string('evidence_path')->nullable();
            $table->text('problem')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['mission_submissions', 'missions', 'action_plan_items', 'action_plans', 'recommendations', 'growth_score_snapshots', 'kpi_snapshots', 'diagnostic_responses', 'diagnostics', 'diagnostic_questions'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
