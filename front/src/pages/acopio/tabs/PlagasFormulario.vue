<template>
  <q-card flat bordered>
    <q-card-section class="row items-center justify-between">
      <div class="text-subtitle1">Control de Plagas</div>
      <!-- MODIFICACION 2025-11-17: Boton Nuevo solo visible en modo edicion -->
      <!-- MODIFICACION 2025-11-17: Boton Imprimir siempre visible (tanto en edicion como en solo lectura) -->
      <div class="q-gutter-sm">
        <q-btn v-if="!readOnly" color="primary" icon="add" label="Nuevo" no-caps @click="onNuevo" />
        <q-btn color="green" icon="print" label="Imprimir" no-caps @click="onImprimir" />
      </div>
    </q-card-section>

    <q-separator />

    <!-- MODIFICACION 2025-11-18: Encabezado del formulario con datos del productor y ubicacion -->
    <!-- Replica el encabezado del formulario fisico de Senasag -->
    <q-card-section v-if="datosEncabezado" class="bg-grey-2">
      <div class="text-h6 text-center q-mb-md text-primary">
        Registro de Control de Plagas en Colmenas
      </div>
      <div class="row q-col-gutter-sm">
        <div class="col-12 col-md-6">
          <div class="row q-col-gutter-xs">
            <div class="col-6">
              <q-field dense borderless label="REGISTRO SANITARIO:" stack-label>
                <template v-slot:control>
                  <div class="self-center full-width no-outline text-weight-medium">{{ datosEncabezado.registro_sanitario || 'N/A' }}</div>
                </template>
              </q-field>
            </div>
            <div class="col-6">
              <q-field dense borderless label="DEPARTAMENTO:" stack-label>
                <template v-slot:control>
                  <div class="self-center full-width no-outline text-weight-medium">{{ datosEncabezado.departamento || 'N/A' }}</div>
                </template>
              </q-field>
            </div>
            <div class="col-12">
              <q-field dense borderless label="NOMBRE DEL APICULTOR O RESPONSABLE:" stack-label>
                <template v-slot:control>
                  <div class="self-center full-width no-outline text-weight-medium">{{ datosEncabezado.nombre_apicultor || 'N/A' }}</div>
                </template>
              </q-field>
            </div>
            <div class="col-12">
              <q-field dense borderless label="NOMBRE DEL APIARIO:" stack-label>
                <template v-slot:control>
                  <div class="self-center full-width no-outline text-weight-medium">{{ datosEncabezado.nombre_apiario || 'N/A' }}</div>
                </template>
              </q-field>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6">
          <div class="row q-col-gutter-xs">
            <div class="col-12">
              <q-field dense borderless label="PROVINCIA:" stack-label>
                <template v-slot:control>
                  <div class="self-center full-width no-outline text-weight-medium">{{ datosEncabezado.provincia || 'N/A' }}</div>
                </template>
              </q-field>
            </div>
            <div class="col-12">
              <q-field dense borderless label="MUNICIPIO:" stack-label>
                <template v-slot:control>
                  <div class="self-center full-width no-outline text-weight-medium">{{ datosEncabezado.municipio || 'N/A' }}</div>
                </template>
              </q-field>
            </div>
            <div class="col-12">
              <q-field dense borderless label="GEOREFERENCIACIÓN:" stack-label>
                <template v-slot:control>
                  <div class="self-center full-width no-outline text-weight-medium">{{ datosEncabezado.georeferenciacion || 'N/A' }}</div>
                </template>
              </q-field>
            </div>
            <div class="col-12">
              <q-field dense borderless label="LOCALIDAD:" stack-label>
                <template v-slot:control>
                  <div class="self-center full-width no-outline text-weight-medium">{{ datosEncabezado.localidad || 'N/A' }}</div>
                </template>
              </q-field>
            </div>
          </div>
        </div>
      </div>
    </q-card-section>

    <q-separator />
    <q-card-section>
      <q-markup-table dense wrap-cells bordered flat>
        <thead>
          <tr class="bg-primary text-white">
            <th>Acciones</th>
            <th>Fecha</th>
            <th>Nombre o Código del Apiario</th>
            <th>Plaga Presente</th>
            <th>Daño Visible</th>
            <th>Método de Control</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in rows" :key="row.id">
            <td class="text-right">
              <!-- MODIFICACION 2025-11-17: Dropdown de acciones solo visible en modo edicion -->
              <q-btn-dropdown v-if="!readOnly" dense no-caps label="Acciones" color="primary" size="10px">
                <q-list>
                  <q-item clickable v-ripple @click="onEditar(row)" v-close-popup>
                    <q-item-section avatar><q-icon name="edit" /></q-item-section>
                    <q-item-section>Editar</q-item-section>
                  </q-item>
                  <q-item clickable v-ripple @click="onEliminar(row)" v-close-popup>
                    <q-item-section avatar><q-icon name="delete" color="negative" /></q-item-section>
                    <q-item-section>Eliminar</q-item-section>
                  </q-item>
                </q-list>
              </q-btn-dropdown>
              <!-- MODIFICACION 2025-11-17: En modo solo lectura, mostrar guion -->
              <span v-else>—</span>
            </td>
            <td>{{ row.fecha }}</td>
            <td>{{ row.nombre_plaga }}</td>
            <td>{{ row.plaga_presente }}</td>
            <td>{{ row.daño_visible_apiario }}</td>
            <td>{{ row.medidas_control_celdilla }}</td>
          </tr>
        </tbody>
      </q-markup-table>
    </q-card-section>

    <!-- Diálogo Crear/Editar -->
    <q-dialog v-model="dlg.open" persistent>
      <q-card style="max-width: 90vw;">
        <q-card-section class="row items-center justify-between">
          <div class="text-subtitle1">{{ dlg.modo === 'crear' ? 'Nuevo registro' : 'Editar registro' }}</div>
          <q-btn flat round dense icon="close" v-close-popup />
        </q-card-section>
        <q-separator />
        <q-card-section class="">
          <div class="row q-col-gutter-md">
            <div class="col-12 col-md-4">
              <q-input v-model="form.fecha" type="date" label="Fecha" dense outlined />
            </div>
            <!-- MODIFICACION 2025-11-18: Campos ocultos que se llenan automaticamente -->
            <!-- nombre_plaga almacena: Nombre o Código del Apiario (automatico) -->
            <!-- numero_colmenas_apiario almacena: Numero de Colmenas (automatico) -->
            <div class="col-12 col-md-6">
              <q-input v-model="form.plaga_presente" label="Plaga Presente en el Apiario" dense outlined />
            </div>
            <div class="col-12 col-md-6">
              <q-input v-model="form.daño_visible_apiario" label="Daño Visible en el Apiario" dense outlined />
            </div>
            <div class="col-12">
              <q-input v-model="form.medidas_control_celdilla" label="Método de Control Utilizado" dense outlined />
            </div>
            <div class="col-12">
              <q-input v-model="form.observaciones" type="textarea" autogrow label="Observaciones" dense outlined />
            </div>
          </div>
        </q-card-section>
        <q-separator />
        <q-card-actions align="right">
          <q-btn flat label="Cancelar" v-close-popup />
          <q-btn color="primary" :label="dlg.modo === 'crear' ? 'Guardar' : 'Actualizar'" @click="onGuardar" :loading="saving" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-card>
</template>

<script>
export default {
  name: 'PlagasFormulario',
  props: {
    cosecha: { type: Object, required: true },
    // MODIFICACION 2025-11-17: Prop para activar modo solo lectura
    // Cuando readOnly=true: oculta botones Nuevo/Editar/Eliminar y muestra boton Imprimir
    // Usado en CosechaShow.vue para consulta, ProductorAcopios.vue para edicion
    readOnly: { type: Boolean, default: false }
  },
  data () {
    return {
      loading: false,
      saving: false,
      rows: [],
      dlg: { open: false, modo: 'crear', id: null },
      form: {
        acopio_cosecha_id: null,
        fecha: null,
        numero_colmenas_apiario: '',
        nombre_plaga: '',
        plaga_presente: '',
        daño_visible_apiario: '',
        medidas_control_celdilla: '',
        observaciones: ''
      }
    }
  },
  // MODIFICACION 2025-11-18: Computed property para extraer datos del encabezado del formulario
  // Obtiene datos de la cadena de relaciones: cosecha -> apiario -> productor -> municipio -> provincia/departamento
  computed: {
    datosEncabezado () {
      if (!this.cosecha?.apiario?.productor) return null
      
      const productor = this.cosecha.apiario.productor
      const municipio = productor.municipio || {}
      const provincia = municipio.provincia || {}
      const departamento = municipio.departamento || {}
      const apiario = this.cosecha.apiario || {}
      
      return {
        registro_sanitario: productor.runsa || productor.codigo_runsa || 'N/A',
        departamento: departamento.nombre_departamento || 'N/A',
        nombre_apicultor: `${productor.nombre || ''} ${productor.apellidos || ''}`.trim() || productor.nombre_apellido || 'N/A',
        nombre_apiario: apiario.nombre_apiario || apiario.codigo_apiario || 'N/A',
        provincia: provincia.nombre_provincia || 'N/A',
        municipio: municipio.nombre_municipio || 'N/A',
        georeferenciacion: `${apiario.latitud || ''}, ${apiario.longitud || ''}`.trim().replace(/^,\s*|,\s*$/g, '') || 'N/A',
        localidad: productor.direccion || 'N/A'
      }
    }
  },
  mounted () {
    this.fetchRows()
  },
  methods: {
    resetForm () {
      // MODIFICACION 2025-11-18: fecha se pre-llena con fecha actual en formato YYYY-MM-DD
      // pero permite modificacion manual para registros retroactivos
      const hoy = new Date().toISOString().split('T')[0]
      
      // MODIFICACION 2025-11-18: Pre-llenar campos automaticos con datos del apiario
      // nombre_plaga almacena el nombre/codigo del apiario (sin modificar estructura de BD)
      // numero_colmenas_apiario almacena el numero de colmenas del apiario
      const apiario = this.cosecha?.apiario || {}
      const nombreApiario = apiario.nombre_apiario || apiario.codigo_apiario || ''
      const numeroColmenas = apiario.numero_colmenas || apiario.cant_colmenas || ''
      
      this.form = {
        acopio_cosecha_id: this.cosecha?.id || null,
        fecha: hoy,
        numero_colmenas_apiario: numeroColmenas,
        nombre_plaga: nombreApiario,
        plaga_presente: '',
        daño_visible_apiario: '',
        medidas_control_celdilla: '',
        observaciones: ''
      }
    },
    async fetchRows () {
      try {
        this.loading = true
        const { data } = await this.$axios.get('/plagas', {
          params: { cosecha_id: this.cosecha?.id }
        })
        this.rows = Array.isArray(data) ? data : (data?.data || [])
      } catch (e) {
        this.$q.notify({ type: 'negative', message: 'No se pudieron cargar los registros' })
      } finally {
        this.loading = false
      }
    },
    onNuevo () {
      this.dlg.modo = 'crear'
      this.dlg.id = null
      this.resetForm()
      this.form.acopio_cosecha_id = this.cosecha?.id || null
      this.dlg.open = true
    },
    onEditar (row) {
      this.dlg.modo = 'editar'
      this.dlg.id = row.id
      this.form = { ...row, acopio_cosecha_id: this.cosecha?.id || row.acopio_cosecha_id }
      this.dlg.open = true
    },
    async onGuardar () {
      try {
        this.saving = true
        if (this.dlg.modo === 'crear') {
          await this.$axios.post('/plagas', this.form)
          this.$q.notify({ type: 'positive', message: 'Registro creado' })
        } else {
          await this.$axios.put(`/plagas/${this.dlg.id}`, this.form)
          this.$q.notify({ type: 'positive', message: 'Registro actualizado' })
        }
        this.dlg.open = false
        this.fetchRows()
      } catch (e) {
        this.$q.notify({ type: 'negative', message: 'Error al guardar' })
      } finally {
        this.saving = false
      }
    },
    async onEliminar (row) {
      this.$q.dialog({
        title: 'Eliminar',
        message: '¿Desea eliminar este registro?',
        cancel: true,
        persistent: true
      }).onOk(async () => {
        try {
          await this.$axios.delete(`/plagas/${row.id}`)
          this.$q.notify({ type: 'positive', message: 'Eliminado' })
          this.fetchRows()
        } catch (e) {
          this.$q.notify({ type: 'negative', message: 'No se pudo eliminar' })
        }
      })
    },
    // MODIFICACION 2025-11-17: Metodo para imprimir formulario de plagas
    // Abre en nueva ventana el PDF generado por el backend
    onImprimir () {
      if (!this.cosecha?.id) return
      const url = this.$axios.defaults.baseURL + `/plagas/${this.cosecha.id}/imprimir`
      window.open(url, '_blank')
    }
  }
}
</script>
