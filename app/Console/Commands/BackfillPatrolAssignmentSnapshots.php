<?php

namespace App\Console\Commands;

use App\Models\Checkpoint;
use App\Models\PatrolAssignment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillPatrolAssignmentSnapshots extends Command
{
    protected $signature = 'patrol:snapshot-backfill {--strategy=auto : auto|scanned|route}';
    protected $description = 'Congela snapshots de checkpoints por asignación (backfill idempotente)';

    public function handle(): int
    {
        $strategy = $this->option('strategy'); // auto|scanned|route

        DB::transaction(function () use ($strategy) {
            PatrolAssignment::with(['route','scans'])
                ->orderBy('id')
                ->chunkById(100, function ($chunk) use ($strategy) {
                    foreach ($chunk as $a) {
                        $exists = DB::table('patrol_assignment_checkpoints')
                            ->where('patrol_assignment_id', $a->id)
                            ->exists();
                        if ($exists) continue;

                        $checkpointIds = [];

                        if ($strategy === 'scanned') {
                            $checkpointIds = $a->scans()->pluck('checkpoint_id')->unique()->values()->all();
                        } elseif ($strategy === 'route') {
                            $checkpointIds = Checkpoint::where('patrol_route_id', $a->patrol_route_id)->pluck('id')->all();
                        } else { // auto
                            if ($a->status === 'completed' && $a->scans()->exists()) {
                                $checkpointIds = $a->scans()->pluck('checkpoint_id')->unique()->values()->all();
                            }
                            if (empty($checkpointIds)) {
                                $checkpointIds = Checkpoint::where('patrol_route_id', $a->patrol_route_id)->pluck('id')->all();
                            }
                        }

                        if (!empty($checkpointIds)) {
                            DB::table('patrol_assignment_checkpoints')->insert(
                                array_map(fn ($cid) => [
                                    'patrol_assignment_id' => $a->id,
                                    'checkpoint_id'        => $cid,
                                    'created_at'           => now(),
                                    'updated_at'           => now(),
                                ], $checkpointIds)
                            );
                        }
                    }
                });
        });

        $this->info('Backfill OK.');
        return self::SUCCESS;
    }
}
