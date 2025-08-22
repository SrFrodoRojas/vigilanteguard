// database/migrations/2025_08_12_000001_create_people_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 120);
            $table->string('document', 50)->unique();
            $table->string('gender', 20)->nullable(); // texto libre: M, F, No binario, etc.
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('people');
    }
};
