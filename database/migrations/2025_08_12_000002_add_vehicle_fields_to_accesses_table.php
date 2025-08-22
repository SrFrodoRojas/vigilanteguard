// database/migrations/2025_08_12_000002_add_vehicle_fields_to_accesses_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('accesses', function (Blueprint $table) {
            $table->string('vehicle_make', 60)->nullable()->after('plate');   // marca
            $table->string('vehicle_color', 40)->nullable()->after('vehicle_make');
            $table->string('vehicle_type', 40)->nullable()->after('vehicle_color'); // auto, moto, bici, etc.
        });
    }
    public function down(): void {
        Schema::table('accesses', function (Blueprint $table) {
            $table->dropColumn(['vehicle_make','vehicle_color','vehicle_type']);
        });
    }
};
