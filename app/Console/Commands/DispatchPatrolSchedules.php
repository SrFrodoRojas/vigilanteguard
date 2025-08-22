<?php
namespace App\Console\Commands;

use App\Models\PatrolAssignment;
use App\Models\PatrolSchedule;
use Illuminate\Console\Command;

class DispatchPatrolSchedules extends Command
{
    protected $signature   = 'patrol:dispatch-schedules';
    protected $description = 'Genera asignaciones desde las programaciones activas y envía avisos';

    public function handle(): int
    {
        $now = now();

        PatrolSchedule::where('active', true)->chunkById(100, function ($schedules) use ($now) {
            foreach ($schedules as $s) {
                if (! $s->guardUser || ! $s->route || ! $s->route->active) {
                    continue;
                }

                // ¿Ya hay una asignación próxima en la próxima frecuencia?
                $windowEnd = $now->copy()->addMinutes($s->frequency_minutes);
                $exists    = PatrolAssignment::where('guard_id', $s->guard_id)
                    ->where('patrol_route_id', $s->patrol_route_id)
                    ->where('scheduled_start', '>=', $now->subMinutes(5)) // pequeña tolerancia
                    ->where('scheduled_start', '<=', $windowEnd)
                    ->exists();

                if ($exists) {
                    continue;
                }

                                                      // Crear nueva asignación para el próximo slot
                $start = $now->copy()->ceilMinute(5); // redondeo leve
                $end   = $start->copy()->addMinutes($s->route->expected_duration_min);

                PatrolAssignment::create([
                    'guard_id'        => $s->guard_id,
                    'patrol_route_id' => $s->patrol_route_id,
                    'scheduled_start' => $start,
                    'scheduled_end'   => $end,
                    'status'          => 'scheduled',
                ]);

                // TODO: enviar notificación (push/email/in-app). Si usás PWA WebPush, integrá aquí.
                $this->info("Asignación creada para guardia {$s->guard_id} ruta {$s->patrol_route_id} {$start}.");
            }
        });

        return self::SUCCESS;
    }
}
