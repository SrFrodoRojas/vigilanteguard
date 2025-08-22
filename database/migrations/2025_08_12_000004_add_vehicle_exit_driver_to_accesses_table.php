// database/migrations/2025_08_12_000004_add_vehicle_exit_driver_to_accesses_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('accesses', function (Blueprint $table) {
            // persona que condujo el vehículo al salir (si corresponde)
            $table->foreignId('vehicle_exit_driver_id')
                  ->nullable()
                  ->constrained('access_people')
                  ->nullOnDelete();
        });
    }
    public function down(): void {
        Schema::table('accesses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vehicle_exit_driver_id');
        });
    }
};
