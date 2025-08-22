<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('access_people', function (Blueprint $table) {
            // si existiera un FK previo con otro nombre, habría que dropearlo antes
            $table->foreign('access_id', 'access_people_access_fk')
                  ->references('id')->on('accesses')
                  ->onDelete('cascade'); // elimina occupants si se borra el access
        });
    }

    public function down(): void
    {
        Schema::table('access_people', function (Blueprint $table) {
            $table->dropForeign('access_people_access_fk');
        });
    }
};
