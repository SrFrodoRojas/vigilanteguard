<?php
// database/migrations/2025_08_19_000002_create_checkpoints_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('checkpoints')) {
            Schema::create('checkpoints', function (Blueprint $t) {
                $t->id();
                $t->foreignId('patrol_route_id')->constrained('patrol_routes')->cascadeOnDelete();
                $t->string('name');
                $t->decimal('latitude', 10, 7);
                $t->decimal('longitude', 10, 7);
                $t->unsignedSmallInteger('radius_m')->default(25);
                $t->uuid('qr_token')->unique();
                $t->string('short_code', 8)->unique();
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('checkpoints');
    }
};

