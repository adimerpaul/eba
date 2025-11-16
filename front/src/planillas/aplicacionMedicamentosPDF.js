export function aplicacionMedicamentosPDF(data = {}) {
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
      <td>${registro.nombre_producto || ''}</td>
      <td>${registro.principio_activo || ''}</td>
      <td>${registro.dosis_recomendada || ''}</td>
      <td>${registro.dosis_aplicada || ''}</td>
      <td>${registro.plagas_controladas || ''}</td>
      <td>${registro.periodo_espera_cosecha || ''}</td>
      <td>${registro.nombre_encargado || ''}</td>
      <td style="text-align: center;">${registro.firma || ''}</td>
    </tr>
  `).join('');

  return `
<style>
/* --- ESTRUCTURA GENERAL --- */
.formulario-medicamentos {
  width: 100%;
  max-width: 900px;
  margin: 0 auto;
  font-family: "Arial", sans-serif;
  font-size: 10px;
  border: 1px solid #000;
  padding: 12px;
  box-sizing: border-box;
}

.formulario-medicamentos h3, .formulario-medicamentos h4 {
  margin: 4px 0;
  text-align: center;
}

.formulario-medicamentos h3 {
  font-size: 14px;
  text-transform: uppercase;
  margin-bottom: 8px;
}

.formulario-medicamentos h4 {
  font-size: 12px;
  background: #e0e0e0;
  padding: 4px;
  border: 1px solid #000;
  margin-top: 8px;
  margin-bottom: 4px;
}

/* --- TABLAS --- */
.formulario-medicamentos table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 8px;
}

.formulario-medicamentos td, .formulario-medicamentos th {
  border: 1px solid #000;
  padding: 4px;
  text-align: left;
  vertical-align: top;
}

.formulario-medicamentos th {
  background: #f0f0f0;
  font-weight: bold;
  text-align: center;
}

.formulario-medicamentos .info-label {
  font-weight: bold;
  width: 30%;
  background: #f9f9f9;
}

.formulario-medicamentos .info-valor {
  width: 70%;
}
</style>

<div class="formulario-medicamentos">
  <h3>REGISTRO DE APLICACIÓN DE MEDICAMENTOS</h3>
  
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

  <h4>Registros de Aplicación de Medicamentos</h4>
  <table>
    <thead>
      <tr>
        <th style="width: 4%;">N°</th>
        <th style="width: 9%;">Fecha</th>
        <th style="width: 12%;">Nombre del Producto</th>
        <th style="width: 12%;">Principio Activo</th>
        <th style="width: 10%;">Dosis Recomendada</th>
        <th style="width: 10%;">Dosis Aplicada</th>
        <th style="width: 12%;">Plagas Controladas</th>
        <th style="width: 10%;">Período Espera Cosecha</th>
        <th style="width: 12%;">Nombre del Encargado</th>
        <th style="width: 9%;">Firma</th>
      </tr>
    </thead>
    <tbody>
      ${filasTabla || '<tr><td colspan="10" style="text-align: center;">No hay registros</td></tr>'}
    </tbody>
  </table>
</div>
`;
}
