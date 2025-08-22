<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('patrol_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patrol_route_id')->constrained('patrol_routes')->cascadeOnDelete();
            $table->foreignId('guard_id')->nullable()->constrained('users'); // null = aplica a múltiples guardias (si querés)
            $table->unsignedSmallInteger('frequency_minutes')->default(180); // cada 3 hs
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('patrol_schedules');
    }
};
