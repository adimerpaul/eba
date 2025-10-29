<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nota de Venta #{{ $venta->id }}</title>
    <style>
        @page { margin: 18mm 14mm; }
        * { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
        .h1 { font-size: 18px; font-weight: bold; }
        .h2 { font-size: 14px; font-weight: bold; }
        .muted { color: #666; }
        .right { text-align: right; }
        .center { text-align: center; }
        .mt-4 { margin-top: 12px; }
        .mt-6 { margin-top: 18px; }
        .mb-2 { margin-bottom: 6px; }
        .mb-4 { margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 6px; vertical-align: top; }
        thead th { border-bottom: 1px solid #444; }
        tbody td { border-bottom: 1px dotted #bbb; }
        .tot { border-top: 1px solid #444; font-weight: bold; }
        .small { font-size: 10px; }
        .header { display: table; width: 100%; }
        .cell { display: table-cell; vertical-align: middle; }
        .logo { width: 70px; height: auto; }
        .box { border: 1px solid #ddd; padding: 8px; border-radius: 6px; }
    </style>
</head>
<body>
<div class="header mb-4">
    <div class="cell" style="width: 80px;">
        @if(file_exists($empresa['logo']))
            <img class="logo" src="{{ $empresa['logo'] }}" alt="logo">
        @endif
    </div>
    <div class="cell">
        <div class="h1">NOTA DE VENTA</div>
        <div class="muted">{{ $empresa['nombre'] }}</div>
        <div class="small muted">{{ $empresa['dir'] }} · {{ $empresa['tel'] }} · {{ $empresa['ciudad'] }}</div>
    </div>
    <div class="cell right">
        <div class="h2"># {{ $venta->id }}</div>
        <div class="small muted">Fecha emisión: {{ $hoy->format('d/m/Y H:i') }}</div>
    </div>
</div>

<table>
    <tr>
        <td class="box" style="width: 60%">
            <div class="h2 mb-2">Cliente</div>
            <div><b>{{ $venta->cliente->nombre_cliente ?? '-' }}</b></div>
            <div class="small muted">NIT: {{ $venta->cliente->nit ?? '-' }}</div>
            <div class="small muted">{{ $venta->cliente->direccion ?? '' }}</div>
            <div class="small muted">{{ $venta->cliente->telefono ?? '' }} · {{ $venta->cliente->email ?? '' }}</div>
        </td>
        <td></td>
        <td class="box" style="width: 35%">
            <div class="h2 mb-2">Venta</div>
            <div><b>Fecha:</b> {{ \Carbon\Carbon::parse($venta->fecha_venta)->format('d/m/Y H:i') }}</div>
            <div><b>Factura:</b> {{ $venta->num_factura ?? '-' }}</div>
            <div><b>Guía remisión:</b> {{ $venta->guia_remision ?? '-' }}</div>
            <div><b>Destino:</b> {{ $venta->destino_final ?? '-' }}</div>
            @if($venta->transporte)
                <div class="small muted mt-4"><b>Transporte:</b> {{ $venta->transporte->empresa ?? $venta->transporte->responsable ?? $venta->transporte->placa }}</div>
            @endif
        </td>
    </tr>
</table>

<div class="mt-6">
    <table>
        <thead>
        <tr>
            <th style="width: 22%">Lote</th>
            <th>Producto</th>
            <th class="right" style="width: 12%">Kg</th>
            <th class="right" style="width: 16%">Precio</th>
            <th class="right" style="width: 18%">Subtotal</th>
        </tr>
        </thead>
        <tbody>
        @foreach($venta->detalles as $d)
            <tr>
                <td>{{ $d->lote->codigo_lote ?? $d->lote_id }}</td>
                <td>{{ $d->producto->nombre_producto ?? $d->producto_id }}</td>
                <td class="right">{{ number_format($d->cantidad_salida, 2, '.', ',') }}</td>
                <td class="right">{{ number_format($d->precio_venta, 2, '.', ',') }}</td>
                <td class="right">{{ number_format($d->cantidad_salida * $d->precio_venta, 2, '.', ',') }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="4" class="right tot">TOTAL (Bs)</td>
            <td class="right tot">{{ number_format($venta->precio_total, 2, '.', ',') }}</td>
        </tr>
        </tbody>
    </table>
</div>

<div class="mt-6 small center muted">
    Gracias por su preferencia. — EBA
</div>
</body>
</html>
