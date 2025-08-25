<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Crea el índice ÚNICO si no existe (sin Doctrine)
        if (! $this->indexExists('patrol_assignment_checkpoints', 'pac_assignment_checkpoint_unique')) {
            Schema::table('patrol_assignment_checkpoints', function (Blueprint $table) {
                $table->unique(
                    ['patrol_assignment_id', 'checkpoint_id'],
                    'pac_assignment_checkpoint_unique'
                );
            });
        }
    }

    public function down(): void
    {
        // Elimina el índice ÚNICO si existe (sin Doctrine)
        if ($this->indexExists('patrol_assignment_checkpoints', 'pac_assignment_checkpoint_unique')) {
            Schema::table('patrol_assignment_checkpoints', function (Blueprint $table) {
                $table->dropUnique('pac_assignment_checkpoint_unique');
            });
        }
    }

    /**
     * Verifica existencia de índice usando information_schema (compatible MySQL/MariaDB)
     */
    private function indexExists(string $table, string $index): bool
    {
        $database = DB::getDatabaseName();

        return DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }
};
