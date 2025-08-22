<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('checkpoint_scans', function (Blueprint $table) {
            $table->integer('speed_mps')->nullable();     // velocidad vs. último scan del guardia
            $table->integer('jump_m')->nullable();        // salto de distancia vs. último scan
            $table->boolean('suspect')->default(false);   // bandera sospechosa
            $table->string('suspect_reason')->nullable(); // motivo (texto corto)
        });
    }
    public function down(): void {
        Schema::table('checkpoint_scans', function (Blueprint $table) {
            $table->dropColumn(['speed_mps','jump_m','suspect','suspect_reason']);
        });
    }
};
