<?php
// database/migrations/2025_08_19_000003_create_patrol_assignments_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('patrol_assignments')) {
            Schema::create('patrol_assignments', function (Blueprint $t) {
                $t->id();
                $t->foreignId('guard_id')->constrained('users')->cascadeOnDelete();
                $t->foreignId('patrol_route_id')->constrained('patrol_routes')->cascadeOnDelete();
                $t->timestamp('scheduled_start');
                $t->timestamp('scheduled_end');
                $t->enum('status', ['scheduled','in_progress','completed','missed','cancelled'])->default('scheduled');
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('patrol_assignments');
    }
};


