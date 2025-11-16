export function limpiezaDesinfeccionPDF(data = {}) {
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
      <td>${registro.equipo_herramienta_material || ''}</td>
      <td>${registro.material_recubrimiento || ''}</td>
      <td>${registro.metodo_limpieza_utilizado || ''}</td>
      <td>${registro.producto_quimico_desinfeccion || ''}</td>
      <td>${registro.fecha_aplicacion || ''}</td>
    </tr>
  `).join('');

  return `
<style>
/* --- ESTRUCTURA GENERAL --- */
.formulario-limpieza {
  width: 100%;
  max-width: 900px;
  margin: 0 auto;
  font-family: "Arial", sans-serif;
  font-size: 11px;
  border: 1px solid #000;
  padding: 12px;
  box-sizing: border-box;
}

.formulario-limpieza h3, .formulario-limpieza h4 {
  margin: 4px 0;
  text-align: center;
}

.formulario-limpieza h3 {
  font-size: 14px;
  text-transform: uppercase;
  margin-bottom: 8px;
}

.formulario-limpieza h4 {
  font-size: 12px;
  background: #e0e0e0;
  padding: 4px;
  border: 1px solid #000;
  margin-top: 8px;
  margin-bottom: 4px;
}

/* --- TABLAS --- */
.formulario-limpieza table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 8px;
}

.formulario-limpieza td, .formulario-limpieza th {
  border: 1px solid #000;
  padding: 4px;
  text-align: left;
  vertical-align: top;
}

.formulario-limpieza th {
  background: #f0f0f0;
  font-weight: bold;
  text-align: center;
}

.formulario-limpieza .info-label {
  font-weight: bold;
  width: 30%;
  background: #f9f9f9;
}

.formulario-limpieza .info-valor {
  width: 70%;
}
</style>

<div class="formulario-limpieza">
  <h3>REGISTRO DE LIMPIEZA Y DESINFECCIÓN</h3>
  
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

  <h4>Registros de Limpieza y Desinfección</h4>
  <table>
    <thead>
      <tr>
        <th style="width: 5%;">N°</th>
        <th style="width: 22%;">Equipo/Herramienta/Material</th>
        <th style="width: 20%;">Material de Recubrimiento</th>
        <th style="width: 20%;">Método de Limpieza Utilizado</th>
        <th style="width: 20%;">Producto Químico de Desinfección</th>
        <th style="width: 13%;">Fecha de Aplicación</th>
      </tr>
    </thead>
    <tbody>
      ${filasTabla || '<tr><td colspan="6" style="text-align: center;">No hay registros</td></tr>'}
    </tbody>
  </table>
</div>
`;
}
