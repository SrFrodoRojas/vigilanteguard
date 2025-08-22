<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('accesses', function (Blueprint $table) {
            $table->enum('tipo_vehiculo', ['auto', 'moto', 'bicicleta', 'camion'])->nullable()->change();
        });

        Schema::table('access_people', function (Blueprint $table) {
            $table->enum('gender', ['femenino', 'masculino'])->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('accesos', function (Blueprint $table) {
            if (Schema::hasColumn('accesos', 'sexo')) {
                $table->dropColumn('sexo');
            }

            if (Schema::hasColumn('accesos', 'tipo_vehiculo')) {
                $table->dropColumn('tipo_vehiculo');
            }

            if (Schema::hasColumn('accesos', 'marca_vehiculo')) {
                $table->dropColumn('marca_vehiculo');
            }

            if (Schema::hasColumn('accesos', 'color_vehiculo')) {
                $table->dropColumn('color_vehiculo');
            }

        });
    }

};
