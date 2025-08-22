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
        Schema::create('accesses', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['vehicle', 'pedestrian'])->default('vehicle');
            $table->string('plate')->nullable();                            // Solo vehicle
            $table->unsignedTinyInteger('people_count')->nullable();        // Opcional
            $table->string('full_name');                                    // Conductor o peatón
            $table->string('document');                                     // CI / DNI (obligatorio)
            $table->timestamp('entry_at')->useCurrent();                    // Hora entrada auto
            $table->timestamp('exit_at')->nullable();                       // Hora salida
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // quién registró
            $table->timestamps();

            $table->index(['plate']);
            $table->index(['document']);
            $table->index(['exit_at']);
            $table->index(['type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accesses');
    }
};
