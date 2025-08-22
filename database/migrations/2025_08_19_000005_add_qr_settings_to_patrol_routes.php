<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('patrol_routes', function (Blueprint $table) {
            $table->boolean('qr_required')->default(true);
            $table->unsignedSmallInteger('min_radius_m')->default(20);
        });
    }
    public function down(): void {
        Schema::table('patrol_routes', function (Blueprint $table) {
            $table->dropColumn(['qr_required','min_radius_m']);
        });
    }
};
