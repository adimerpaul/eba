export function actaRecepcionConformidadPDF(data = {}) {
  const {
    lugar = 'SAMAUZABETY',
    dia = '07',
    mes = '07',
    anio = '2022',
    almacen = '0001',
    fechaRecibo = '07/07/2022',
    literal = '',
    monto = '0.00',
    consistencia = '',
    precioUnitario = '0.00',
    sabor = '_____',
    aroma = '_____',
    gb = '_____',
    humedad = '_____',
    region = '_____',
    proveedor = '_____',
    peso = '_____',
    nombre = '_____',
    materia = '_____',
    color = '_____',
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
  p{font-size:14px;}
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

  <p>
  De acuerdo a la designacion como, <b>ENCARGADA DE ACOPIO</b> Y PERSONAL DE CALIDAD, mi persona ${nombre}, ha procedido con la RECEPCION DE MIEL, mediante COMPRA DIRECTA, 
  lote que fue entregado en fecha ${fechaRecibo}, de acuerdo al siguiente detalle:
  </p>
  <p>${peso} Kg. de ${materia}, con un precio por kg. de ${precioUnitario} Bs/kg</p>
  <p>La entrega fue realizada por el proveedor: ${proveedor}</p>
  <p>Productor de la region de : ${region}</p>
  <p>En conformidad con los parametros de control de calidad estipulados por la Empresa de Alimentos y Derivados EBA: <br> <b>MIEL ${materia}</b></p>
  <p>Luego de un Analisis sensorial, la miel presento las siguientes caracteristicas, una humedad promedio de: <br> 
   ${humedad} %, GB ${gb}, Aroma: ${aroma}, Sabor: ${sabor}, Consistencia: ${consistencia}, Color: ${color}
  </p>
  <p>Con presencia minima de impurezas.</p>
  <p>Del lote de miel recepcionado ${ peso } Kg. se distribuyo al ${almacen}</p>
  <p>El monto total CANCELADO ES: <b>${monto}</b> Bs.</p>
  <p>${literal} 00/100 Bolivianos</p>
  <p>Es cuanto se informa para fines consiguientes:</p>
  <br>

  <table class="table no-border" style="margin-top:25px;">
    <tr class="dark" style="font-size:8px;"><td>ENTREGUE CONFORME</td><td></td><td>RECIBI CONFORME</td><td></td></tr>
    <tr style="height:100px"><td></td><td></td><td></td><td></td></tr
    <tr>
      <td class="firma"><div class="linea"></div>PRODUCTOR O PRODUCTORA</td>
      <td class="firma"><div class="linea"></div>HUELLA DACTILAR PULGAR DERECHO</td>
      <td class="firma"><div class="linea"></div>AUXILIAR DE ACOPIO</td>
      <td class="firma"><div class="linea"></div>ENCARGADO DE CALIDAD DE PLANTA</td>
    </tr>
  </table>
  `;
}
