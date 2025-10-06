@php
    $p   = $cert->productor;
    $fullName = trim(($p->nombre ?? '').' '.($p->apellidos ?? ''));
    $ubi = trim(($p->municipio->nombre_municipio ?? '').' — '.($p->municipio->provincia->nombre_provincia ?? '').' — '.($p->municipio->departamento->nombre_departamento ?? ''));
    $estadoColor = [
        'VIGENTE' => '#2e7d32',
        'VENCIDO' => '#c62828',
        'SUSPENDIDO' => '#ef6c00'
    ][$cert->estado] ?? '#607d8b';

    $venceEnDias = null;
    if ($cert->fecha_vencimiento) {
        try { $venceEnDias = \Carbon\Carbon::parse($cert->fecha_vencimiento)->diffInDays(now(), false); } catch(\Throwable $e) {}
    }
@endphp
    <!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Certificación #{{ $cert->id }}</title>
    <style>
        @page { margin: 22mm 16mm; }
        * { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; }
        .header { display:flex; align-items:center; gap:16px; }
        .logo { height:54px; }
        .brand {
            color:#0d47a1; font-weight:700; font-size:16px; line-height:1.2;
        }
        .subtitle { color:#37474f; font-size:11px; }
        .title {
            margin:12px 0 8px; padding:8px 12px; background:#e3f2fd; color:#0d47a1;
            border-left:4px solid #0d47a1; font-weight:700; font-size:15px;
        }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 16px; }
        .card {
            border:1px solid #e0e0e0; border-radius:6px; padding:10px 12px;
        }
        .row { display:flex; justify-content:space-between; margin:2px 0; }
        .label { color:#607d8b; font-size:11px; }
        .value { font-size:12px; font-weight:600; color:#263238; }
        .pill {
            display:inline-block; padding:3px 8px; border-radius:999px; color:#fff; font-weight:700; font-size:10px;
        }
        table { width:100%; border-collapse: collapse; }
        th, td { border:1px solid #e0e0e0; padding:6px 8px; font-size:12px; }
        th { background:#fafafa; text-align:left; color:#37474f; }
        .footer {
            position: fixed; bottom: 14mm; left: 16mm; right: 16mm;
            font-size:10px; color:#607d8b; display:flex; justify-content:space-between;
            border-top:1px dashed #b0bec5; padding-top:6px;
        }
        .muted { color:#78909c; font-size:10px; }
    </style>
</head>
<body>

<!-- Header -->
<div class="header">
    @if($logo)
        <img class="logo" src="{{ $logo }}" alt="EBA">
    @endif
    <div>
        <div class="brand">EMPRESA BOLIVIANA DE ALIMENTOS (EBA)</div>
        <div class="subtitle">Registro de Certificaciones</div>
    </div>
</div>

<!-- Título -->
<div class="title">Ficha de Certificación N° {{ $cert->id }}</div>

<!-- Bloques principales -->
<div class="grid">
    <!-- Bloque Productor -->
    <div class="card">
        <div class="row"><span class="label">Productor</span><span class="value">{{ $fullName }}</span></div>
        <div class="row"><span class="label">CI</span><span class="value">{{ $p->numcarnet ?? '-' }}</span></div>
        <div class="row"><span class="label">RUNSA</span><span class="value">{{ $p->runsa ?? '-' }}</span></div>
        <div class="row"><span class="label">Organización</span><span class="value">{{ $p->organizacion->nombre_organiza ?? '-' }}</span></div>
        <div class="row"><span class="label">Celular</span><span class="value">{{ $p->num_celular ?? '-' }}</span></div>
        <div class="row"><span class="label">Comunidad</span><span class="value">{{ $p->comunidad ?? '-' }}</span></div>
        <div class="row"><span class="label">Ubicación</span><span class="value">{{ $ubi }}</span></div>
    </div>

    <!-- Bloque Certificación -->
    <div class="card">
        <div class="row"><span class="label">Tipo</span><span class="value">{{ $cert->tipo_certificacion ?? '-' }}</span></div>
        <div class="row"><span class="label">Organismo</span><span class="value">{{ $cert->organismo_certificador ?? '-' }}</span></div>
        <div class="row"><span class="label">Fecha de emisión</span><span class="value">{{ $cert->fecha_emision ?? '-' }}</span></div>
        <div class="row"><span class="label">Fecha de vencimiento</span><span class="value">{{ $cert->fecha_vencimiento ?? '-' }}</span></div>
        <div class="row">
            <span class="label">Estado</span>
            <span class="value">
          <span class="pill" style="background: {{ $estadoColor }}">{{ $cert->estado }}</span>
        </span>
        </div>
        <div class="row"><span class="label">URL</span><span class="value">{{ $cert->certificado_url ?? '-' }}</span></div>
        @if(!is_null($venceEnDias))
            <div class="row">
                <span class="label">Vigencia</span>
                <span class="value">
            @if($venceEnDias > 0)
                        Vencido hace {{ $venceEnDias }} día(s)
                    @elseif($venceEnDias === 0)
                        Vence hoy
                    @else
                        Vence en {{ abs($venceEnDias) }} día(s)
                    @endif
          </span>
            </div>
        @endif
    </div>
</div>

<!-- Detalle (tabla simple, por si luego agregas campos u observaciones) -->
<div style="margin-top:12px;">
    <table>
        <thead>
        <tr>
            <th style="width:35%;">Campo</th>
            <th>Detalle</th>
        </tr>
        </thead>
        <tbody>
        <tr><td>Observaciones</td><td class="muted">—</td></tr>
        </tbody>
    </table>
</div>

<!-- Footer -->
<div class="footer">
    <div>Generado: {{ $hoy }}</div>
    <div>Sistema: {{ $appName }}</div>
</div>

</body>
</html>
