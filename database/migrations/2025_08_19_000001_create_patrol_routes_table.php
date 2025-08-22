<?php
    // database/migrations/2025_08_19_000001_create_patrol_routes_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('patrol_routes')) {
            Schema::create('patrol_routes', function (Blueprint $t) {
                $t->id();
                $t->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
                $t->string('name');
                $t->unsignedSmallInteger('expected_duration_min')->default(30);
                $t->boolean('active')->default(true);
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        // OJO: si la tabla existía antes, no la borres en down().
        if (Schema::hasTable('patrol_routes')) {
            Schema::dropIfExists('patrol_routes');
        }
    }
};
