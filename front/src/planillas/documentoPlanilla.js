// === Plantilla Dompdf-friendly (100% tablas, CSS mínimo) ===
export function actaRecepcionConformidadPDF(cosecha = {}) {

  return `
  <style>
  *{ font-family: DejaVu Sans, sans-serif; }
  .titulo { text-align:center; font-size:20px; margin-bottom:10px; }
  .box { width:100%; border-collapse:collapse; margin-bottom:8px; }
  .box td { border:1px solid #8a96a3; padding:6px; font-size:12px; }
  .box .label { background:#5f6c78; color:#fff; font-weight:600; width:220px; }
  .tabla { width:100%; border-collapse:collapse; }
  .tabla th, .tabla td { border:1px solid #8a96a3; padding:6px; font-size:12px; }
  .tabla th { background:#5f6c78; color:#fff; font-weight:700; }
  .tabla tfoot td { background:#5f6c78; color:#fff; font-weight:700; }
  .right { text-align:right; }
  .center { text-align:center; }
  .firmas { width:100%; text-align:center; margin-top:36px; font-size:12px; }
  .firmas .linea { border-top:1px dotted #444; margin:20px auto 6px; width:80%; }
</style>

<h2 style="text-align:center;">IRUPANA</h2>
<h3 class="titulo">ACTA DE RECEPCIÓN Y CONFORMIDAD DE MATERIA PRIMA</h3>

<table class="box">
  <tr><td class="label">Responsable de Almacén:</td><td>ORLANDO URUÑA KAPA</td></tr>
  <tr><td class="label">Dependencia:</td><td>GERENCIA DE LÍNEA APÍCOLA, LIOFILIZADOS, STEVIA Y GRANOS</td></tr>
  <tr><td class="label">Tipo de Materia Prima:</td><td>MAP-17 MIEL</td></tr>
  <tr><td class="label">Proveedor:</td><td>TINTA MAMANI ELIZABETH</td></tr>
</table>

<table class="tabla">
  <thead>
    <tr>
      <th>Nro.</th>
      <th>Código</th>
      <th>Partida</th>
      <th>Descripción</th>
      <th>Unidad de Medida</th>
      <th>Fecha de Vencimiento</th>
      <th>Lote</th>
      <th>Cantidad</th>
      <th>Costo Unitario</th>
      <th>Costo Total</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td class="center">1</td>
      <td><b>MAP-17</b></td>
      <td><b>31300</b></td>
      <td><b>MIEL</b></td>
      <td><b>KILO</b></td>
      <td><b>2027-12-31</b></td>
      <td><b>S/L</b></td>
      <td class="right">400.00</td>
      <td class="right">32.00</td>
      <td class="right">12,800.00</td>
    </tr>
  </tbody>
  <tfoot>
    <tr>
      <td colspan="9" class="right">Total:</td>
      <td class="right">12,800.00</td>
    </tr>
  </tfoot>
</table>

<table class="firmas">
  <tr>
    <td><div class="linea"></div>Encargado de Almacén</td>
    <td><div class="linea"></div>Área/Unidad administrativa</td>
    <td><div class="linea"></div>Jefe de Planta</td>
  </tr>
</table>

  `;
}
