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
  *{ font-family: DejaVu Sans, sans-serif; }
  body { font-size: 12px; }
  .header { width:100%; border-collapse: collapse; margin-bottom:8px; }
  .header td { padding:4px; font-size:11px; }
  .titulo { text-align:center; font-size:16px; font-weight:bold; margin:8px 0; }
  .subtitulo { text-align:center; font-size:13px; margin-bottom:8px; }
  .table { width:100%; border-collapse: collapse; font-size:12px; }
  .table2 { width:100%; border-collapse: collapse; font-size:8px; }
  .table td, .table th { border:1px solid #444; padding:5px; }
  .label { background:#f0f0f0; font-weight:bold; }
  .right { text-align:right; }
  .center { text-align:center; }
  .firma { text-align:center; font-size:11px; padding-top:25px; }
  .firma .linea { border-top:1px dotted #444; width:80%; margin:0 auto 4px; }
  .nota { font-size:10px; margin-top:5px; }
  .dark { background:#5f6c78; color:#fff; font-weight:bold; text-align:center; }
  .no-border td { border:none !important; }
  </style>

  <table class="header">
    <tr>
      <td rowspan="2" style="width:150px;"><img src="../../logoOld.png" width="150"></td>
      <td colspan="3" style="text-align:center; font-weight:bold; font-size:22px;">EMPRESA BOLIVIANA DE ALIMENTOS Y DERIVADOS - EBA</td>
      <td style="border:1px solid #000; font-size:10px;" rowspan="2">
        <b>Fecha de emisión:</b>01/01/2025<br><br>
        <b>Usuario: _________</b><br><br>
        <b>Código: 9999999</b>
      </td>
    </tr>
    <tr>
      <td colspan="3" style="text-align:center; font-size:12px;">NOMBRE DEL CIP ........</td>
    </tr>
    <tr><td colspan="5" style="text-align:center; font-size:16px; font-weight:bold;">ACTA DE CONTROL DE CALIDAD Y CONFORMIDAD DE MATERIA PRIMA</td></tr>
  </table>


  <table class="table">
    <tr><td class="label">Recibí de:</td><td colspan="2">EMPRESA BOLIVIANA DE ALIMENTOS Y DERIVADOS - EBA</td></tr>
    <tr><td class="label">La suma de:</td><td>Líquido Pagable al productor (Bs.): <td>${monto}</b></td></tr>
    <tr><td class="label">Son:</td><td colspan="2">(${montoLiteral} Bolivianos)</td></tr>
    <tr><td class="label">Por la compra de:</td><td>${cantidadKg} </td><td>Kilogramos de polen</td></tr>
  </table>
  <br>
  <table class="table">
    <tr>
      <td class="label">El proveedor tiene régimen agrícola unificado - R.A.U.</td><td style="text-align:center; font-weight:bold; font-size:14px;">NO</td>
      <td class="label">Precio con/sin R.A.U.</td><td>${precioUnitario} Bs/kg</td>
    </tr>
  </table>
  <br>
  <div class="dark">DETALLE DE LIQUIDACIÓN</div>
  <table class="table">
    <tr><td style="font-weight:bold; width:20px">A</td><td class="label" style="text-align:right;">Costo de la compra de miel</td><td class="right">${costoCompra} Bs</td></tr>
    <tr><td style="font-weight:bold; width:20px">B</td><td class="label" style="text-align:right;">Impuesto IT (3%) Pago de Impuesto</td><td class="right">${impuestoIT} Bs</td></tr>
    <tr><td style="font-weight:bold; width:20px">C</td><td class="label" style="text-align:right;">Impuesto IUE (6%) Pago de Impuesto</td><td class="right">${impuestoIUE} Bs</td></tr>
    <tr><td style="font-weight:bold; width:20px">D</td><td class="label" style="text-align:right;">Total pago de impuestos (B+C)</td><td class="right">${totalImpuestos} Bs</td></tr>
    <tr><td style="font-weight:bold; width:20px">E</td><td class="label" style="text-align:right;"><b>LIQUIDO PAGABLE AL PRODUCTOR POR LA COMPRA DEL BIEN (A-D)</b></td><td class="right"><b>${liquidoPagable} Bs</b></td></tr>
  </table>

  <br>
  <table class="table">
    <tr><td class="label">Proveedor:</td><td>${productor}</td><td class="label">Teléfono:</td><td>${telefono}</td></tr>
    <tr><td class="label">Organización:</td><td>${organizacion}</td><td class="label">Procedencia de la Materia Prima:</td><td>${materia}</td></tr>
    <tr><td class="label">Departamento:</td><td>${departamento}</td><td class="label">Municipio:</td><td>${municipio}</td></tr>
  </table>
<br>
  <p class="nota">
  Nota: El proveedor hace entrega del producto en conformidad a los parámetros de calidad que la Empresa EBA exige, siendo miel natural sin ningún tipo de adulteración.
  </p>
<br>
<br>
<br>
  <table class="table no-border" style="margin-top:25px;">
    <tr>
      <td class="firma"><div class="linea"></div>Entregué Conforme</td>
      <td class="firma"><div class="linea"></div>Recibí Conforme</td>
      <td class="firma"><div class="linea"></div>Huella Dactilar / Pulgar Derecho</td>
    </tr>
  </table>
  `;
}
