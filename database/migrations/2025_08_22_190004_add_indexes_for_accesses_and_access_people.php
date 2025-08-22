<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Usamos un helper que consulta information_schema (sin Doctrine)
        $self = $this;

        Schema::table('accesses', function (Blueprint $t) use ($self) {
            if (! $self->indexExists('accesses', 'idx_accesses_plate')) {
                $t->index('plate', 'idx_accesses_plate');
            }
            if (! $self->indexExists('accesses', 'idx_accesses_entry_at')) {
                $t->index('entry_at', 'idx_accesses_entry_at');
            }
            if (! $self->indexExists('accesses', 'idx_accesses_exit_at')) {
                $t->index('exit_at', 'idx_accesses_exit_at');
            }
            if (! $self->indexExists('accesses', 'idx_accesses_plate_exit_at')) {
                $t->index(['plate', 'exit_at'], 'idx_accesses_plate_exit_at');
            }
        });

        Schema::table('access_people', function (Blueprint $t) use ($self) {
            if (! $self->indexExists('access_people', 'idx_access_people_document')) {
                $t->index('document', 'idx_access_people_document');
            }
            if (! $self->indexExists('access_people', 'idx_access_people_exit_at')) {
                $t->index('exit_at', 'idx_access_people_exit_at');
            }
            if (! $self->indexExists('access_people', 'idx_access_people_document_exit_at')) {
                $t->index(['document', 'exit_at'], 'idx_access_people_document_exit_at');
            }
        });
    }

    public function down(): void
    {
        $self = $this;

        Schema::table('access_people', function (Blueprint $t) use ($self) {
            if ($self->indexExists('access_people', 'idx_access_people_document')) {
                $t->dropIndex('idx_access_people_document');
            }
            if ($self->indexExists('access_people', 'idx_access_people_exit_at')) {
                $t->dropIndex('idx_access_people_exit_at');
            }
            if ($self->indexExists('access_people', 'idx_access_people_document_exit_at')) {
                $t->dropIndex('idx_access_people_document_exit_at');
            }
        });

        Schema::table('accesses', function (Blueprint $t) use ($self) {
            if ($self->indexExists('accesses', 'idx_accesses_plate')) {
                $t->dropIndex('idx_accesses_plate');
            }
            if ($self->indexExists('accesses', 'idx_accesses_entry_at')) {
                $t->dropIndex('idx_accesses_entry_at');
            }
            if ($self->indexExists('accesses', 'idx_accesses_exit_at')) {
                $t->dropIndex('idx_accesses_exit_at');
            }
            if ($self->indexExists('accesses', 'idx_accesses_plate_exit_at')) {
                $t->dropIndex('idx_accesses_plate_exit_at');
            }
        });
    }

    /** Comprueba si existe un índice usando information_schema (sin Doctrine) */
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
