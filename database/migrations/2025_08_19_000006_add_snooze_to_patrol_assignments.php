<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('patrol_assignments', function (Blueprint $table) {
            $table->unsignedTinyInteger('snooze_count')->default(0);
        });
    }
    public function down(): void {
        Schema::table('patrol_assignments', function (Blueprint $table) {
            $table->dropColumn('snooze_count');
        });
    }
};
