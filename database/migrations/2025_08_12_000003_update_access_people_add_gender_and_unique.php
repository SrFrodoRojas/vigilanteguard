// database/migrations/2025_08_12_000003_update_access_people_add_gender_and_unique.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('access_people', function (Blueprint $table) {
            $table->string('gender', 20)->nullable()->after('is_driver');
            $table->unique(['access_id','document']); // evita que chofer y acompañante tengan el mismo documento
        });
    }
    public function down(): void {
        Schema::table('access_people', function (Blueprint $table) {
            $table->dropUnique(['access_id','document']);
            $table->dropColumn('gender');
        });
    }
};
