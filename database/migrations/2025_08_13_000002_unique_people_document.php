<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $exists = DB::selectOne("
            SELECT 1
            FROM information_schema.statistics
            WHERE table_schema = DATABASE()
              AND table_name = 'people'
              AND index_name = 'people_document_unique'
            LIMIT 1
        ");

        if (! $exists) {
            Schema::table('people', function (Blueprint $table) {
                $table->unique('document', 'people_document_unique');
            });
        }
    }

    public function down(): void
    {
        $exists = DB::selectOne("
            SELECT 1
            FROM information_schema.statistics
            WHERE table_schema = DATABASE()
              AND table_name = 'people'
              AND index_name = 'people_document_unique'
            LIMIT 1
        ");

        if ($exists) {
            Schema::table('people', function (Blueprint $table) {
                $table->dropUnique('people_document_unique');
            });
        }
    }
};
