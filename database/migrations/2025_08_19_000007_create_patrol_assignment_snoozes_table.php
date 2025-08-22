<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('patrol_assignment_snoozes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patrol_assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users'); // quién pospuso (guardia)
            $table->unsignedSmallInteger('minutes')->default(10);
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('patrol_assignment_snoozes');
    }
};
