export function controlPlagasPDF(data = {}) {
  const {
    cosecha = {},
    registros = []
  } = data;

  const {
    fecha_cosecha = '',
    apiario = {},
    producto = {},
    cantidad_kg = '0.00',
    num_acta = ''
  } = cosecha;

  const productor = apiario?.productor?.nombres || 'Sin productor';
  const nombreApiario = apiario?.nombre || 'Sin apiario';
  const nombreProducto = producto?.nombre || 'Sin producto';

  const filasTabla = registros.map((registro, index) => `
    <tr>
      <td style="text-align: center;">${index + 1}</td>
      <td>${registro.fecha || ''}</td>
      <td style="text-align: center;">${registro.numero_colmenas_apiario || ''}</td>
      <td>${registro.nombre_plaga || ''}</td>
      <td style="text-align: center;">${registro.plaga_presente || ''}</td>
      <td>${registro.daño_visible_apiario || ''}</td>
      <td>${registro.medidas_control_celdilla || ''}</td>
      <td>${registro.observaciones || ''}</td>
    </tr>
  `).join('');

  return `
<style>
/* --- ESTRUCTURA GENERAL --- */
.formulario-plagas {
  width: 100%;
  max-width: 900px;
  margin: 0 auto;
  font-family: "Arial", sans-serif;
  font-size: 11px;
  border: 1px solid #000;
  padding: 12px;
  box-sizing: border-box;
}

.formulario-plagas h3, .formulario-plagas h4 {
  margin: 4px 0;
  text-align: center;
}

.formulario-plagas h3 {
  font-size: 14px;
  text-transform: uppercase;
  margin-bottom: 8px;
}

.formulario-plagas h4 {
  font-size: 12px;
  background: #e0e0e0;
  padding: 4px;
  border: 1px solid #000;
  margin-top: 8px;
  margin-bottom: 4px;
}

/* --- TABLAS --- */
.formulario-plagas table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 8px;
}

.formulario-plagas td, .formulario-plagas th {
  border: 1px solid #000;
  padding: 4px;
  text-align: left;
  vertical-align: top;
}

.formulario-plagas th {
  background: #f0f0f0;
  font-weight: bold;
  text-align: center;
}

.formulario-plagas .info-label {
  font-weight: bold;
  width: 30%;
  background: #f9f9f9;
}

.formulario-plagas .info-valor {
  width: 70%;
}
</style>

<div class="formulario-plagas">
  <h3>REGISTRO DE CONTROL DE PLAGAS EN COLMENAS</h3>
  
  <h4>Información de la Cosecha</h4>
  <table>
    <tr>
      <td class="info-label">Productor:</td>
      <td class="info-valor">${productor}</td>
      <td class="info-label">Fecha Cosecha:</td>
      <td class="info-valor">${fecha_cosecha}</td>
    </tr>
    <tr>
      <td class="info-label">Apiario:</td>
      <td class="info-valor">${nombreApiario}</td>
      <td class="info-label">Producto:</td>
      <td class="info-valor">${nombreProducto}</td>
    </tr>
    <tr>
      <td class="info-label">Cantidad (Kg):</td>
      <td class="info-valor">${cantidad_kg}</td>
      <td class="info-label">N° Acta:</td>
      <td class="info-valor">${num_acta}</td>
    </tr>
  </table>

  <h4>Registros de Control de Plagas</h4>
  <table>
    <thead>
      <tr>
        <th style="width: 4%;">N°</th>
        <th style="width: 10%;">Fecha</th>
        <th style="width: 8%;">N° Colmenas</th>
        <th style="width: 15%;">Nombre de la Plaga</th>
        <th style="width: 8%;">Plaga Presente</th>
        <th style="width: 18%;">Daño Visible en Apiario</th>
        <th style="width: 20%;">Medidas de Control/Celdilla</th>
        <th style="width: 17%;">Observaciones</th>
      </tr>
    </thead>
    <tbody>
      ${filasTabla || '<tr><td colspan="8" style="text-align: center;">No hay registros</td></tr>'}
    </tbody>
  </table>
</div>
`;
}
