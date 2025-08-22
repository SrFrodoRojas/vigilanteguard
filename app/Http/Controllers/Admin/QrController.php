<?php
// app/Http/Controllers/Patrol/Admin/QrController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Checkpoint;
use RuntimeException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrController extends Controller
{
    public function png(Checkpoint $checkpoint)
    {
        $url = url('/patrol/scan?c=' . $checkpoint->qr_token);

        try {
            // Intentar PNG (requiere imagick)
            $png = QrCode::format('png')->size(512)->margin(1)->generate($url);
            return response($png, 200)->header('Content-Type', 'image/png');
        } catch (RuntimeException $e) {
            // Fallback a SVG si no hay imagick
            $svg = QrCode::format('svg')->size(512)->margin(1)->generate($url);
            return response($svg, 200)->header('Content-Type', 'image/svg+xml');
        }
    }

}
