<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('accesses', function (Blueprint $table) {
            $table->timestamp('vehicle_exit_at')->nullable()->after('entry_at');
        });
    }
    public function down(): void {
        Schema::table('accesses', function (Blueprint $table) {
            $table->dropColumn('vehicle_exit_at');
        });
    }
};
