{{-- resources/views/pdf/organizaciones_activos.blade.php --}}
@php
    use Carbon\Carbon;
    $now     = Carbon::now();
    $logo    = public_path('images/logo.png'); // cambia si usas otra ruta
    $userRef = $user->username ?? ($user->name ?? $user->email ?? '—');
    $codigo  = str_pad($user->id ?? 0, 6, '0', STR_PAD_LEFT);

    // helper de texto corto
    function short($v, $n=60){ $v = trim((string)$v); return mb_strlen($v)>$n ? mb_substr($v,0,$n-1).'…' : $v; }
@endphp
    <!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Organizaciones — Estado: {{ strtoupper($estado) }}</title>
    <style>
        @page{ margin: 45px 35px 45px 35px; }
        *{ font-family: Arial, sans-serif; }
        body{ font-size:12px; color:#111; }

        /* Cabecera */
        .hdr{ width:100%; border-collapse:collapse; }
        .hdr td{ vertical-align:top; }
        .hdr .logo{ width:22%; }
        .hdr .logo img{ height:78px; } /* ← MÁS GRANDE */
        .hdr .title{ width:56%; text-align:center; }
        .hdr .boxcell{ width:22%; }

        .orgname{ font-size:16px; font-weight:bold; letter-spacing:.3px; margin:2px 0 4px 0; }
        .orgsub{ font-size:11px; color:#555; margin:0; }

        .metabox{ border:1px solid #999; border-radius:6px; overflow: hidden; }
        .metabox table{ width:100%; border-collapse:collapse; font-size:11px; }
        .metabox td{ padding:4px 6px; border-bottom:1px solid #e6e6e6; }
        .metabox tr:last-child td{ border-bottom:none; }
        .mk{ color:#fff; background:#333; font-weight:bold; width:55%; }
        .mv{ text-align:right; font-weight:bold; }

        .sep{ height:1px; background:#e5e5e5; border:none; margin:8px 0 12px 0; }

        /* Tabla de organizaciones */
        table.list{ width:100%; border-collapse:collapse; table-layout: fixed; }
        table.list th, table.list td{ border:1px solid #dcdcdc; padding:6px 6px; }
        table.list th{
            background:#f5f7fb; color:#333; font-size:11px; text-transform:uppercase; letter-spacing:.3px;
        }
        /* Ajustes de ancho por columna (puedes retocar % a gusto) */
        .c-num   { width:4%;  text-align:center; }
        .c-org { width:22%; }
        .c-pre { width:16%; }
        .c-apc { width:8%;  text-align:center; }
        .c-col { width:10%; text-align:center; }
        .c-mun { width:12%; }
        .c-pro { width:12%; }
        .c-dep { width:12%; }
        .c-est { width:8%;  text-align:center; white-space:nowrap; }

        .muted{ color:#666; }
    </style>
</head>
<body>

<!-- CABECERA -->
<table class="hdr">
    <tr>
        <td class="logo">
            @if(is_file($logo))
                <img src="{{ $logo }}" alt="EBA">
            @else
                <strong>EBA</strong>
            @endif
        </td>

        <td class="title">
            <div class="orgname">EMPRESA BOLIVIANA DE ALIMENTOS Y DERIVADOS</div>
            <div class="orgsub">LISTADO DE ORGANIZACIONES — ESTADO: {{ strtoupper($estado) }}</div>
        </td>

        <td class="boxcell">
            <div class="metabox">
                <table>
                    <tr>
                        <td class="mk">Fecha de Emisión</td>
                        <td class="mv">{{ $now->format('Y-m-d') }}</td>
                    </tr>
                    <tr>
                        <td class="mk">Usuario</td>
                        <td class="mv">{{ $userRef }}</td>
                    </tr>
                    <tr>
                        <td class="mk">Código</td>
                        <td class="mv">{{ $codigo }}</td>
                    </tr>
                </table>
            </div>
        </td>
    </tr>
</table>

<hr class="sep">

<!-- TABLA DE ORGANIZACIONES -->
<table class="list">
    <thead>
    <tr>
        <th class="c-num">#</th>
        <th class="c-org">Organización</th>
        <th class="c-pre">Presidente</th>
        <th class="c-apc">Apicult.</th>
        <th class="c-col">Colmenas</th>
        <th class="c-mun">Municipio</th>
        <th class="c-pro">Provincia</th>
        <th class="c-dep">Departamento</th>
        <th class="c-est">Estado</th>
    </tr>
    </thead>
    <tbody>
    @forelse($organizaciones as $i => $org)
        @php
            $mun = optional($org->municipio)->nombre_municipio;
            $pro = optional($org->provincia)->nombre_provincia;
            $dep = optional($org->departamento)->nombre_departamento;
        @endphp
        <tr>
            <td class="c-num">{{ $i+1 }}</td>
            <td class="c-org">{{ short($org->nombre_organiza) }}</td>
            <td class="c-pre">{{ short($org->nombre_presidente, 40) }}</td>
            <td class="c-apc">{{ $org->num_apicultor ?? 0 }}</td>
            <td class="c-col">{{ $org->num_colmena ?? 0 }}</td>
            <td class="c-mun">{{ short($mun, 24) }}</td>
            <td class="c-pro">{{ short($pro, 24) }}</td>
            <td class="c-dep">{{ short($dep, 24) }}</td>
            <td class="c-est">{{ strtoupper($org->estado ?? '—') }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="9" class="muted" style="text-align:center;">No se encontraron organizaciones con el estado seleccionado.</td>
        </tr>
    @endforelse
    </tbody>
    @if($organizaciones->count())
        <tfoot>
        <tr>
            <th colspan="3" style="text-align:right;">Totales:</th>
            <th class="c-apc">
                {{ $organizaciones->sum(fn($o) => (int)($o->num_apicultor ?? 0)) }}
            </th>
            <th class="c-col">
                {{ $organizaciones->sum(fn($o) => (int)($o->num_colmena ?? 0)) }}
            </th>
            <th colspan="4" style="text-align:right;">
                Registros: {{ $organizaciones->count() }}
            </th>
        </tr>
        </tfoot>
    @endif
</table>

</body>
</html>
