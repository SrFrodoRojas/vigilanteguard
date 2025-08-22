<?php
namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\Request;

class PeopleController extends Controller
{
    public function lookup(Request $request)
    {
        $doc = trim((string) $request->query('document', ''));
        if ($doc === '') {
            return response()->json(['found' => false], 200);
        }

                                                // ⬇️ Normalización sugerida (no rompe nada)
        $doc = preg_replace('/\s+/', '', $doc); // quitar espacios intermedios
        $doc = mb_strtoupper($doc, 'UTF-8');    // unificar a MAYÚSCULAS

        $p = Person::where('document', $doc)->first();
        if (! $p) {
            return response()->json(['found' => false], 200);
        }

        return response()->json([
            'found'     => true,
            'full_name' => $p->full_name,
            'gender'    => $p->gender,
            'document'  => $p->document,
        ], 200);
    }

}
