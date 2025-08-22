<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('branches', function (Blueprint $table) {
            if (!Schema::hasColumn('branches', 'color')) {
                $table->string('color', 7)->nullable()->after('location'); // #RRGGBB
            }
        });
    }
    public function down(): void {
        Schema::table('branches', function (Blueprint $table) {
            if (Schema::hasColumn('branches', 'color')) {
                $table->dropColumn('color');
            }
        });
    }
};
