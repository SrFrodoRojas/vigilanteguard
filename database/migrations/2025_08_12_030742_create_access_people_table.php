<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('access_people', function (Blueprint $table) {
            $table->id();
            $table->foreignId('access_id')->constrained('accesses')->cascadeOnDelete();
            $table->string('full_name', 120);
            $table->string('document', 50)->index();
            $table->enum('role', ['driver','passenger','pedestrian'])->default('pedestrian');
            $table->boolean('is_driver')->default(false);
            $table->timestamp('entry_at')->useCurrent();
            $table->timestamp('exit_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('access_people');
    }
};

