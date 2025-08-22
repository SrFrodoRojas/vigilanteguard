<?php
// app/Console/Commands/ExportCheckpointQrs.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Checkpoint;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class ExportCheckpointQrs extends Command
{
    protected $signature = 'patrol:export-qrs {route_id}';
    protected $description = 'Genera PNG de QRs para todos los checkpoints de una ruta';

    public function handle(): int
    {
        $routeId = (int) $this->argument('route_id');
        $checkpoints = Checkpoint::where('patrol_route_id', $routeId)->get();

        if ($checkpoints->isEmpty()) {
            $this->error('No hay checkpoints.'); return self::FAILURE;
        }

        foreach ($checkpoints as $cp) {
            $url = url('/patrol/scan?c=' . $cp->qr_token);
            $png = QrCode::format('png')->size(512)->margin(1)->generate($url);
            $path = "qrs/route_{$routeId}/cp_{$cp->id}.png";
            Storage::disk('public')->put($path, $png);
            $this->info("OK -> storage/app/public/{$path}");
        }
        return self::SUCCESS;
    }
}
