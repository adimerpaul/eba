export function actaconformidadPDF(data = {}) {
  const {
    lugar = 'SAMAUZABETY',
    dia = '07',
    mes = '07',
    anio = '2022',
    nroRecibo = '0001',
    fechaRecibo = '07/07/2022',
    productor = 'Nombre del Productor',
    monto = '0.00',
    montoLiteral = 'CERO 00/100',
    cantidadKg = '0.00',
    precioUnitario = '0.00',
    costoCompra = '0.00',
    impuestoIT = '0.00',
    impuestoIUE = '0.00',
    totalImpuestos = '0.00',
    liquidoPagable = '0.00',
    telefono = '',
    organizacion = '',
    municipio = '',
    materia = '',
    departamento = '',
  } = data;

  return `
<style>
/* --- ESTRUCTURA GENERAL --- */
.acta {
  width: 100%;
  max-width: 850px;
  margin: 0 auto;
  font-family: "Arial", sans-serif;
  font-size: 12px;
  border: 1px solid #000;
  padding: 12px;
  box-sizing: border-box;
}

.acta h3, .acta h4 {
  margin: 4px 0;
  text-align: center;
}

.acta h4.seccion {
  background: #d9d9d9;
  padding: 4px;
  text-transform: uppercase;
  border: 1px solid #000;
  margin-top: 10px;
  margin-bottom: 5px;
}

/* --- TABLAS --- */
.acta table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 8px;
}

.acta td, .acta th {
  border: 1px solid #000;
  padding: 4px 6px;
  vertical-align: top;
}

.acta th {
  text-align: center;
  background: #f2f2f2;
}

.acta td strong {
  font-weight: bold;
}

/* --- CABECERA --- */
.acta .cabecera {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 8px;
}

.acta .cabecera .izquierda {
  width: 65%;
}

.acta .cabecera .derecha {
  width: 30%;
  border: 1px solid #000;
  padding: 4px;
  font-size: 11px;
}

/* --- CHECKBOX --- */
.acta input[type="checkbox"] {
  margin-left: 6px;
  margin-right: 2px;
  transform: scale(1.1);
}

/* --- FIRMAS --- */
.acta .firmas th {
  background: #e6e6e6;
  font-weight: bold;
}

.acta .firmas td {
  height: 80px;
  vertical-align: top;
  padding: 6px;
}

/* --- PIE --- */
.acta .pie {
  text-align: center;
  font-size: 11px;
  border-top: 1px solid #000;
  padding-top: 6px;
}
</style>
<div class="acta">
  <div class="cabecera">
    <div class="izquierda">
      <h3>EMPRESA BOLIVIANA DE ALIMENTOS Y DERIVADOS</h3>
      <h4>ACTA DE CONFORMIDAD</h4>
      <h4>RECEPCIÓN DE MATERIA PRIMA</h4>
      <p><strong>Nombre del CIP:</strong> {{ $cip_nombre ?? '' }}</p>
    </div>
    <div class="derecha">
      <strong>Fecha de emisión:</strong> {{ $fecha_emision ?? '' }}<br>
      <strong>Usuario:</strong> {{ $usuario ?? '' }}<br>
      <strong>Código:</strong> {{ $codigo ?? '' }}
    </div>
  </div>

  <h4 class="seccion">a) Acopio</h4>
  <table>
    <tr><td><strong>Fecha de acopio:</strong> {{ $fecha_acopio }}</td><td><strong>CIP/Planta:</strong> {{ $cip_planta }}</td></tr>
    <tr><td><strong>Materia Prima:</strong> {{ $materia_prima }}</td><td><strong>Peso (Kg):</strong> {{ $peso }}</td></tr>
    <tr><td><strong>Precio Unitario Bs/Kg:</strong> {{ $precio_unitario }}</td><td><strong>Proveedor:</strong> {{ $proveedor }}</td></tr>
    <tr><td><strong>Productor:</strong> {{ $productor }}</td><td><strong>N° colmenas en producción:</strong> {{ $n_colmenas }}</td></tr>
    <tr><td colspan="2"><strong>Producción anual promedio (Kg):</strong> {{ $produccion_promedio }}</td></tr>
  </table>

  <h4 class="seccion">b) Datos de georreferenciación</h4>
  <table>
    <tr>
      <td><strong>Tiene georreferenciación:</strong> 
        <input type="checkbox" {{ ($geo=='SI')?'checked':'' }}> SI 
        <input type="checkbox" {{ ($geo=='NO')?'checked':'' }}> NO
      </td>
    </tr>
    <tr><td><strong>Latitud:</strong> {{ $latitud }} &nbsp;&nbsp; <strong>Longitud:</strong> {{ $longitud }}</td></tr>
    <tr>
      <td><strong>Dentro de capacidad productiva:</strong> 
        <input type="checkbox" {{ ($capacidad=='SI')?'checked':'' }}> SI 
        <input type="checkbox" {{ ($capacidad=='NO')?'checked':'' }}> NO
      </td>
    </tr>
    <tr><td><strong>Observaciones:</strong> {{ $observaciones }}</td></tr>
  </table>

  <h4 class="seccion">c) Control de calidad</h4>
  <table>
    <tr><td><strong>% Brix:</strong> {{ $brix }}</td><td><strong>Miel filtrada:</strong> {{ $miel_filtrada }}</td><td><strong>Humedad:</strong> {{ $humedad }}</td></tr>
    <tr><td><strong>Color:</strong> {{ $color }}</td><td><strong>Olor:</strong> {{ $olor }}</td><td><strong>Sabor:</strong> {{ $sabor }}</td></tr>
    <tr><td><strong>Consistencia:</strong> {{ $consistencia }}</td><td colspan="2"><strong>Aspecto:</strong> {{ $aspecto }}</td></tr>
  </table>

  <p>Distribuido al CIP/Planta: <strong>{{ $cip_planta }}</strong>. Monto total a cancelar: <strong>Bs. {{ $monto_total }}</strong>.</p>

  <table class="firmas">
    <tr><th>ENTREGUE CONFORME</th><th>RECIBIMOS CONFORME</th></tr>
    <tr>
      <td>
        <strong>Firma:</strong> ___________________________<br>
        <strong>Nombre completo:</strong> {{ $entrega_nombre }}<br>
        <strong>CI:</strong> {{ $entrega_ci }}<br>
        <em>Huella dactilar (pulgar derecho)</em>
      </td>
      <td>
        <strong>Acopio - EBA:</strong> ___________________<br>
        <strong>Georreferenciación - EBA:</strong> _______<br>
        <strong>Control de calidad - EBA:</strong> _______
      </td>
    </tr>
  </table>

  <div class="pie">
    EL DOCUMENTO DEBE SER LEGIBLE PARA SU VALIDEZ Y NO DEBE TENER BORRADURAS NI TACHADURAS.
  </div>
</div>
    `;
}