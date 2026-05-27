<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_inspection_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('vehicle_inspection_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_inspection_template_id')->constrained('vehicle_inspection_templates')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('applies_to_fuels')->nullable();
            $table->timestamps();

            $table->unique(['vehicle_inspection_template_id', 'slug']);
        });

        Schema::create('vehicle_inspection_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_inspection_category_id')->constrained('vehicle_inspection_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('applies_to_fuels')->nullable();
            $table->boolean('is_critical')->default(false);
            $table->timestamps();

            $table->unique(['vehicle_inspection_category_id', 'slug']);
        });

        Schema::create('vehicle_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_inspection_template_id')->nullable()->constrained('vehicle_inspection_templates')->nullOnDelete();
            $table->foreignId('v3_vehicle_id')->nullable()->constrained('v3_vehicles')->nullOnDelete();
            $table->string('status')->default('draft');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('sub_model')->nullable();
            $table->string('version')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->unsignedInteger('kilometers')->nullable();
            $table->string('vin', 32)->nullable();
            $table->string('registration', 20)->nullable();
            $table->string('color')->nullable();
            $table->string('fuel')->nullable();
            $table->unsignedSmallInteger('power')->nullable();
            $table->string('transmission')->nullable();
            $table->string('traction')->nullable();
            $table->longText('notes')->nullable();
            $table->longText('recommendations')->nullable();
            $table->string('inspection_result')->nullable();
            $table->decimal('total_points', 8, 2)->default(0);
            $table->decimal('max_points', 8, 2)->default(0);
            $table->unsignedInteger('verified_items')->default(0);
            $table->unsignedInteger('ok_items')->default(0);
            $table->unsignedInteger('attention_items')->default(0);
            $table->unsignedInteger('problem_items')->default(0);
            $table->unsignedInteger('unverified_items')->default(0);
            $table->unsignedInteger('critical_issues')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('vehicle_inspection_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_inspection_id')->constrained('vehicle_inspections')->cascadeOnDelete();
            $table->foreignId('vehicle_inspection_item_id')->constrained('vehicle_inspection_items')->cascadeOnDelete();
            $table->string('status')->default('nao_verificado');
            $table->string('priority')->default('baixa');
            $table->longText('notes')->nullable();
            $table->timestamps();

            $table->unique(['vehicle_inspection_id', 'vehicle_inspection_item_id'], 'inspection_item_unique');
        });

        Schema::create('vehicle_inspection_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_inspection_entry_id')->constrained('vehicle_inspection_entries')->cascadeOnDelete();
            $table->string('type');
            $table->string('path');
            $table->string('original_name');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_inspection_media');
        Schema::dropIfExists('vehicle_inspection_entries');
        Schema::dropIfExists('vehicle_inspections');
        Schema::dropIfExists('vehicle_inspection_items');
        Schema::dropIfExists('vehicle_inspection_categories');
        Schema::dropIfExists('vehicle_inspection_templates');
    }
};
