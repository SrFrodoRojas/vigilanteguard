<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ACCESSES
        Schema::table('accesses', function (Blueprint $table) {
            // Búsquedas y orden por fechas
            $table->index('entry_at', 'accesses_entry_at_idx');
            $table->index('exit_at', 'accesses_exit_at_idx');

            // Filtros por tipo + fecha
            $table->index(['type', 'entry_at'], 'accesses_type_entry_idx');

            // Verificación de "vehículo adentro": type=vehicle, plate=X, vehicle_exit_at IS NULL
            $table->index(['type', 'plate', 'vehicle_exit_at'], 'accesses_type_plate_vexit_idx');

            // (útil para with('user'))
            $table->index('user_id', 'accesses_user_id_idx');
        });

        // ACCESS_PEOPLE
        Schema::table('access_people', function (Blueprint $table) {
            $table->index('access_id', 'access_people_access_id_idx');
            $table->index('exit_at', 'access_people_exit_at_idx');
            $table->index(['access_id', 'exit_at'], 'access_people_access_exit_idx');

            // Búsquedas por documento (lookup y validaciones)
            $table->index('document', 'access_people_document_idx');
        });

        // PEOPLE
        Schema::table('people', function (Blueprint $table) {
            // Índice simple para acelerar lookup (dejamos UNIQUE para una segunda etapa)
            $table->index('document', 'people_document_idx');
        });
    }

    public function down(): void
    {
        Schema::table('accesses', function (Blueprint $table) {
            $table->dropIndex('accesses_entry_at_idx');
            $table->dropIndex('accesses_exit_at_idx');
            $table->dropIndex('accesses_type_entry_idx');
            $table->dropIndex('accesses_type_plate_vexit_idx');
            $table->dropIndex('accesses_user_id_idx');
        });

        Schema::table('access_people', function (Blueprint $table) {
            $table->dropIndex('access_people_access_id_idx');
            $table->dropIndex('access_people_exit_at_idx');
            $table->dropIndex('access_people_access_exit_idx');
            $table->dropIndex('access_people_document_idx');
        });

        Schema::table('people', function (Blueprint $table) {
            $table->dropIndex('people_document_idx');
        });
    }
};
