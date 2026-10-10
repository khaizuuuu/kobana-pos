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
        Schema::create('fp_analyses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('date_from');
            $table->date('date_to');
            $table->decimal('min_support', 8, 4);
            $table->decimal('min_confidence', 8, 4);
            $table->unsignedInteger('total_transactions')->default(0);
            $table->enum('status', ['pending', 'running', 'done', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('fp_frequent_itemsets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analysis_id')->constrained('fp_analyses')->cascadeOnDelete();
            $table->json('items');
            $table->string('items_label');
            $table->unsignedTinyInteger('itemset_size');
            $table->unsignedInteger('support_count');
            $table->decimal('support', 8, 4);

            $table->index('analysis_id');
            $table->index(['analysis_id', 'itemset_size']);
        });

        Schema::create('fp_association_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analysis_id')->constrained('fp_analyses')->cascadeOnDelete();
            $table->json('antecedent');
            $table->json('consequent');
            $table->string('antecedent_label');
            $table->string('consequent_label');
            $table->decimal('support', 8, 4);
            $table->decimal('confidence', 8, 4);
            $table->decimal('lift', 8, 4);

            $table->index('analysis_id');
            $table->index(['analysis_id', 'confidence']);
            $table->index(['analysis_id', 'lift']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fp_association_rules');
        Schema::dropIfExists('fp_frequent_itemsets');
        Schema::dropIfExists('fp_analyses');
    }
};
