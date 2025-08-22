<?php

// database/migrations/2025_08_19_000004_create_checkpoint_scans_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('checkpoint_scans')) {
            Schema::create('checkpoint_scans', function (Blueprint $t) {
                $t->id();
                $t->foreignId('patrol_assignment_id')->constrained('patrol_assignments')->cascadeOnDelete();
                $t->foreignId('checkpoint_id')->constrained('checkpoints')->cascadeOnDelete();
                $t->timestamp('scanned_at');
                $t->decimal('lat', 10, 7)->nullable();
                $t->decimal('lng', 10, 7)->nullable();
                $t->unsignedSmallInteger('distance_m')->nullable();
                $t->unsignedSmallInteger('accuracy_m')->nullable();
                $t->string('device_info')->nullable();
                $t->boolean('verified')->default(false);
                $t->string('source_ip', 45)->nullable();
                $t->timestamps();

                $t->unique(['patrol_assignment_id','checkpoint_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('checkpoint_scans');
    }
};

