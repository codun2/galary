<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('make', 60);
            $table->string('model', 80);
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('mileage')->default(0);
            $table->enum('fuel', ['petrol', 'diesel', 'hybrid', 'electric'])->default('petrol');
            $table->enum('transmission', ['automatic', 'manual'])->default('automatic');
            $table->string('color', 40)->nullable();
            $table->string('drivetrain', 30)->nullable();
            $table->decimal('price', 12, 2);
            $table->text('description')->nullable();
            $table->json('features')->nullable();            // مميزات: فتحة سقف، جلد...
            $table->enum('status', ['available', 'reserved', 'sold', 'hidden'])->default('available');
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamps();

            $table->index(['status', 'is_featured']);
            $table->index(['make', 'year']);
        });

        Schema::create('vehicle_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->string('file_path');
            $table->boolean('is_cover')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 30);
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->string('subject')->nullable();          // السيارة المهتم بها (نص حر)
            $table->text('message')->nullable();
            $table->enum('status', ['new', 'replied', 'closed'])->default('new');
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('vehicle_media');
        Schema::dropIfExists('vehicles');
    }
};
