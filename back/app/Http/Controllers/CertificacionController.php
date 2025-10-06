<?php

namespace App\Http\Controllers;

use App\Models\Certificacion;
use App\Models\Productor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use PDF; // barryvdh/laravel-dompdf

class CertificacionController extends Controller
{
    public function index(Request $request)
    {
        $q = Certificacion::with([
            'productor:id,nombre,apellidos,numcarnet'
        ]);

        if ($pid = $request->get('productor_id')) {
            $q->where('productor_id', $pid);
        }

        $q->orderBy('id', 'desc');

        if ($request->boolean('paginate', true)) {
            $per = max(10, min((int) $request->get('per_page', 50), 200));
            return $q->paginate($per)->appends($request->query());
        }

        return $q->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'productor_id'          => ['required','integer','exists:productores,id'],
            'tipo_certificacion'    => ['nullable','string','in:ORGANICO,COMERCIO_JUSTO,DENOMINACION_ORIGEN,BUENAS_PRACTICAS'],
            'organismo_certificador'=> ['nullable','string','max:200'],
            'fecha_emision'         => ['nullable','date'],
            'fecha_vencimiento'     => ['nullable','date','after_or_equal:fecha_emision'],
            'certificado_url'       => ['nullable','string'],
            'estado'                => ['nullable','string','in:VIGENTE,VENCIDO,SUSPENDIDO'],
        ]);

        $cert = Certificacion::create($data);
        return response()->json($cert->load('productor:id,nombre,apellidos,numcarnet'), 201);
    }

    public function show(Certificacion $certificacion)
    {
        return $certificacion->load('productor:id,nombre,apellidos,numcarnet');
    }

    public function update(Request $request, Certificacion $certificacion)
    {
        $data = $request->validate([
            'productor_id'          => ['required','integer','exists:productores,id'],
            'tipo_certificacion'    => ['nullable','string','in:ORGANICO,COMERCIO_JUSTO,DENOMINACION_ORIGEN,BUENAS_PRACTICAS'],
            'organismo_certificador'=> ['nullable','string','max:200'],
            'fecha_emision'         => ['nullable','date'],
            'fecha_vencimiento'     => ['nullable','date','after_or_equal:fecha_emision'],
            'certificado_url'       => ['nullable','string'],
            'estado'                => ['nullable','string','in:VIGENTE,VENCIDO,SUSPENDIDO'],
        ]);

        $certificacion->update($data);
        return $certificacion->load('productor:id,nombre,apellidos,numcarnet');
    }

    public function destroy(Certificacion $certificacion)
    {
        $certificacion->delete();
        return response()->json(['message' => 'Eliminado'], 200);
    }

    // ------- PDF (dompdf) -------
//    public function printByProductor(Productor $productor)
//    {
//        $productor->load([
//            'municipio:id,nombre_municipio,provincia_id,departamento_id',
//            'organizacion:id,nombre_organiza',
//            'certificaciones'
//        ]);
//
//        $data = [
//            'productor' => $productor,
//            'hoy' => now()->format('d/m/Y H:i'),
//            'appName' => config('app.name', 'Trazabilidad Miel'),
//            // Coloca tu logo en public/images/logo.png
//            'logo' => public_path('images/logo.png'),
//        ];
//
//        $pdf = Pdf::loadView('pdf.productor_certificaciones', $data)->setPaper('A4', 'portrait');
//
//        $safeName = Str::slug(($productor->nombre ?? '').' '.($productor->apellidos ?? ''), '_');
//        $filename = "certificaciones_{$productor->id}_{$safeName}_".now()->format('Ymd_His').".pdf";
//
//        // Forzar descarga (attachment)
//        return $pdf->download($filename);
//        // Si quisieras inline: return $pdf->stream($filename);
//    }
    public function print(\App\Models\Certificacion $certificacion)
    {
        // Trae datos del productor para mostrar en el PDF
        $certificacion->load([
            'productor:id,nombre,apellidos,numcarnet,runsa,comunidad,num_celular,organizacion_id,municipio_id',
            'productor.organizacion:id,nombre_organiza',
            'productor.municipio:id,nombre_municipio,provincia_id,departamento_id',
            'productor.municipio.provincia:id,nombre_provincia',
            'productor.municipio.departamento:id,nombre_departamento',
        ]);

        // Logo -> base64 (seguro para Dompdf)
        $logoBase64 = null;
        $logoPath = public_path('logo.png');
        if (is_file($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $data = [
            'cert'     => $certificacion,
            'hoy'      => now()->format('d/m/Y H:i'),
            'appName'  => config('app.name', 'Trazabilidad Miel'),
            'logo'     => $logoBase64,
        ];

        $pdf = PDF::loadView('pdf.certificacion', $data)->setPaper('A4', 'portrait');

        $tipo   = Str::slug($certificacion->tipo_certificacion ?? 'certificacion', '_');
        $nombre = Str::slug(optional($certificacion->productor)->nombre . ' ' . optional($certificacion->productor)->apellidos, '_');
        $filename = "certificacion_{$certificacion->id}_{$tipo}_{$nombre}.pdf";

        return $pdf->download($filename); // o ->stream($filename)
    }
}
