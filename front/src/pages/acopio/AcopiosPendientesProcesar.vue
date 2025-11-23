<template>
  <q-page class="q-pa-md">
    <!-- Header con título y estadísticas rápidas -->
    <div class="row items-center q-mb-md">
      <div class="col">
        <div class="text-h5 text-primary">Acopios Pendientes de Procesar</div>
        <div class="text-caption text-grey-7">Gestión masiva de acopios en estado BUENO</div>
      </div>
      <div class="col-auto">
        <q-btn color="primary" icon="refresh" label="Actualizar" @click="cargarAcopios" :loading="loading" dense no-caps />
      </div>
    </div>

    <!-- Cards de estadísticas -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-sm-3">
        <q-card flat bordered>
          <q-card-section class="text-center">
            <div class="text-h6 text-primary">{{ totales.total_acopios || 0 }}</div>
            <div class="text-caption text-grey-7">Acopios Disponibles</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-sm-3">
        <q-card flat bordered>
          <q-card-section class="text-center">
            <div class="text-h6 text-green">{{ formatNumber(totales.total_kg) }} kg</div>
            <div class="text-caption text-grey-7">Total Kilogramos</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-sm-3">
        <q-card flat bordered>
          <q-card-section class="text-center">
            <div class="text-h6 text-orange">{{ seleccionados.length }}</div>
            <div class="text-caption text-grey-7">Seleccionados</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-sm-3">
        <q-card flat bordered>
          <q-card-section class="text-center">
            <div class="text-h6 text-purple">{{ formatNumber(totalKgSeleccionados) }} kg</div>
            <div class="text-caption text-grey-7">Kg Seleccionados</div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Panel de filtros -->
    <q-card flat bordered class="q-mb-md">
      <q-card-section>
        <div class="text-subtitle2 q-mb-md">
          <q-icon name="filter_list" class="q-mr-xs" />
          Filtros Avanzados
        </div>
        
        <div class="row q-col-gutter-md">
          <!-- Organización -->
          <div class="col-12 col-sm-3">
            <q-select
              v-model="filtros.organizacion_id"
              :options="organizaciones"
              option-value="id"
              :option-label="opt => `${opt.nombre_organiza} - ${opt.asociacion}`"
              emit-value map-options
              label="Organización"
              dense outlined clearable
              @update:model-value="onOrganizacionChange"
            >
              <template v-slot:prepend>
                <q-icon name="business" />
              </template>
            </q-select>
          </div>

          <!-- Productor -->
          <div class="col-12 col-sm-3">
            <q-select
              v-model="filtros.productor_id"
              :options="productoresFiltrados"
              option-value="id"
              :option-label="opt => `${opt.nombre} ${opt.apellidos}`"
              emit-value map-options
              label="Productor"
              dense outlined clearable
              :disable="!filtros.organizacion_id"
            >
              <template v-slot:prepend>
                <q-icon name="person" />
              </template>
            </q-select>
          </div>

          <!-- Producto -->
          <div class="col-12 col-sm-3">
            <q-select
              v-model="filtros.producto_id"
              :options="productos"
              option-value="id"
              option-label="nombre_producto"
              emit-value map-options
              label="Producto"
              dense outlined clearable
            >
              <template v-slot:prepend>
                <q-icon name="inventory" />
              </template>
            </q-select>
          </div>

          <!-- Fecha desde -->
          <div class="col-12 col-sm-3">
            <q-input
              v-model="filtros.fecha_desde"
              type="date"
              label="Desde"
              dense outlined clearable
            >
              <template v-slot:prepend>
                <q-icon name="event" />
              </template>
            </q-input>
          </div>

          <!-- Fecha hasta -->
          <div class="col-12 col-sm-3">
            <q-input
              v-model="filtros.fecha_hasta"
              type="date"
              label="Hasta"
              dense outlined clearable
            >
              <template v-slot:prepend>
                <q-icon name="event" />
              </template>
            </q-input>
          </div>

          <!-- Cantidad mínima -->
          <div class="col-12 col-sm-2">
            <q-input
              v-model.number="filtros.cantidad_min"
              type="number"
              label="Kg Mínimo"
              dense outlined clearable
              min="0"
            />
          </div>

          <!-- Cantidad máxima -->
          <div class="col-12 col-sm-2">
            <q-input
              v-model.number="filtros.cantidad_max"
              type="number"
              label="Kg Máximo"
              dense outlined clearable
              min="0"
            />
          </div>

          <!-- Botones de acción -->
          <div class="col-12 col-sm-5 text-right">
            <q-btn color="grey-7" label="Limpiar" icon="clear" @click="limpiarFiltros" flat dense no-caps class="q-mr-sm" />
            <q-btn color="primary" label="Aplicar Filtros" icon="search" @click="aplicarFiltros" :loading="loading" dense no-caps />
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- Panel de acciones masivas -->
    <q-card flat bordered class="q-mb-md bg-grey-2">
      <q-card-section class="row items-center">
        <div class="col">
          <q-checkbox v-model="selectAll" @update:model-value="toggleSelectAll" label="Seleccionar Todos" dense />
          <span class="q-ml-md text-caption text-grey-7">
            {{ seleccionados.length }} de {{ acopios.length }} seleccionados
            <span v-if="seleccionados.length > 0" class="q-ml-sm">
              ({{ formatNumber(totalKgSeleccionados) }} kg | Bs. {{ formatNumber(totalCostoSeleccionados) }})
            </span>
          </span>
        </div>
        <div class="col-auto q-gutter-sm">
          <q-btn
            color="positive"
            icon="sync_alt"
            label="Procesar Todos"
            @click="procesarTodos"
            :disable="acopios.length === 0"
            no-caps
          />
          <q-btn
            color="primary"
            icon="check_circle"
            label="Procesar Seleccionados"
            @click="procesarSeleccionados"
            :disable="seleccionados.length === 0"
            no-caps
          />
          <q-btn
            color="negative"
            icon="cancel"
            label="Rechazar Seleccionados"
            @click="rechazarSeleccionados"
            :disable="seleccionados.length === 0"
            no-caps
          />
        </div>
      </q-card-section>
    </q-card>

    <!-- Tabla de acopios -->
    <q-card flat bordered>
      <q-table
        :rows="acopios"
        :columns="columns"
        row-key="id"
        :loading="loading"
        :pagination="pagination"
        @request="onRequest"
        binary-state-sort
        flat
      >
        <!-- Columna de selección -->
        <template v-slot:body-cell-seleccionar="props">
          <q-td :props="props">
            <q-checkbox v-model="seleccionados" :val="props.row.id" dense />
          </q-td>
        </template>

        <!-- Columna de fecha -->
        <template v-slot:body-cell-fecha_cosecha="props">
          <q-td :props="props">
            {{ props.row.fecha_cosecha }}
            <q-chip v-if="props.row.dias_espera > 7" size="sm" color="orange" text-color="white" dense>
              {{ props.row.dias_espera }}d
            </q-chip>
          </q-td>
        </template>

        <!-- Columna de organización -->
        <template v-slot:body-cell-organizacion="props">
          <q-td :props="props">
            <q-chip size="sm" :color="props.row.organizacion_nombre === 'Independiente' ? 'grey' : 'blue'" text-color="white" dense>
              {{ props.row.organizacion_nombre }}
            </q-chip>
          </q-td>
        </template>

        <!-- Columna de cantidad -->
        <template v-slot:body-cell-cantidad_kg="props">
          <q-td :props="props" class="text-right">
            <span class="text-weight-bold">{{ formatNumber(props.row.cantidad_kg) }}</span>
          </q-td>
        </template>

        <!-- Columna de costo -->
        <template v-slot:body-cell-costo_total="props">
          <q-td :props="props" class="text-right">
            {{ formatNumber(props.row.costo_total) }}
          </q-td>
        </template>
      </q-table>
    </q-card>

    <!-- Diálogo: Procesar Todos -->
    <q-dialog v-model="dialogProcesarTodos" persistent>
      <q-card style="min-width: 400px">
        <q-card-section class="row items-center bg-warning text-white">
          <q-icon name="warning" size="md" class="q-mr-sm" />
          <span class="text-h6">Confirmar Procesamiento Masivo</span>
        </q-card-section>

        <q-card-section>
          <div class="text-body1 q-mb-md">
            Está a punto de procesar <strong>TODOS</strong> los acopios disponibles:
          </div>
          <q-list dense>
            <q-item>
              <q-item-section>
                <q-item-label caption>Total de acopios</q-item-label>
                <q-item-label class="text-h6 text-primary">{{ totales.total_acopios }}</q-item-label>
              </q-item-section>
            </q-item>
            <q-item>
              <q-item-section>
                <q-item-label caption>Kilogramos totales</q-item-label>
                <q-item-label class="text-h6 text-green">{{ formatNumber(totales.total_kg) }} kg</q-item-label>
              </q-item-section>
            </q-item>
            <q-item>
              <q-item-section>
                <q-item-label caption>Costo total</q-item-label>
                <q-item-label class="text-h6 text-orange">Bs. {{ formatNumber(totales.total_costo) }}</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
          
          <q-input
            v-model="observacionesProcesar"
            type="textarea"
            label="Observaciones (opcional)"
            outlined dense
            rows="3"
            class="q-mt-md"
          />
          
          <q-checkbox
            v-model="confirmacionProcesar"
            label="Confirmo que deseo procesar todos los acopios"
            class="q-mt-md"
          />
        </q-card-section>

        <q-card-actions align="right">
          <q-btn label="Cancelar" color="grey-7" flat @click="dialogProcesarTodos = false" />
          <q-btn
            label="Procesar Todos"
            color="positive"
            icon="sync_alt"
            @click="confirmarProcesarTodos"
            :disable="!confirmacionProcesar"
            :loading="procesando"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Diálogo: Rechazar Acopios -->
    <q-dialog v-model="dialogRechazar" persistent>
      <q-card style="min-width: 500px">
        <q-card-section class="row items-center bg-negative text-white">
          <q-icon name="cancel" size="md" class="q-mr-sm" />
          <span class="text-h6">Rechazar Acopios Seleccionados</span>
        </q-card-section>

        <q-card-section>
          <div class="text-body2 q-mb-md">
            Seleccionados: <strong>{{ seleccionados.length }}</strong> acopios 
            (<strong>{{ formatNumber(totalKgSeleccionados) }} kg</strong>)
          </div>

          <q-select
            v-model="formRechazo.motivo"
            :options="motivosRechazo"
            label="Motivo del Rechazo *"
            outlined dense
            emit-value map-options
            :rules="[val => !!val || 'Requerido']"
          />

          <q-input
            v-model="formRechazo.observaciones"
            type="textarea"
            label="Observaciones Detalladas *"
            outlined dense
            rows="3"
            class="q-mt-md"
            :rules="[val => !!val || 'Requerido']"
          />

          <q-input
            v-model="formRechazo.accion_correctiva"
            type="textarea"
            label="Acción Correctiva Sugerida"
            outlined dense
            rows="2"
            class="q-mt-md"
            hint="Recomendación para el productor"
          />
        </q-card-section>

        <q-card-actions align="right">
          <q-btn label="Cancelar" color="grey-7" flat @click="dialogRechazar = false" />
          <q-btn
            label="Confirmar Rechazo"
            color="negative"
            icon="cancel"
            @click="confirmarRechazo"
            :loading="procesando"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
export default {
  name: 'AcopiosPendientesProcesar',
  
  data() {
    return {
      loading: false,
      procesando: false,
      acopios: [],
      seleccionados: [],
      selectAll: false,
      
      totales: {
        total_acopios: 0,
        total_kg: 0,
        total_costo: 0
      },

      filtros: {
        organizacion_id: null,
        productor_id: null,
        producto_id: null,
        fecha_desde: null,
        fecha_hasta: null,
        cantidad_min: null,
        cantidad_max: null
      },

      organizaciones: [],
      productores: [],
      productos: [],

      pagination: {
        sortBy: 'fecha_cosecha',
        descending: false,
        page: 1,
        rowsPerPage: 50,
        rowsNumber: 0
      },

      columns: [
        { name: 'seleccionar', label: '', field: 'id', align: 'left', style: 'width: 50px' },
        { name: 'fecha_cosecha', label: 'Fecha', field: 'fecha_cosecha', align: 'left', sortable: true },
        { name: 'num_acta', label: 'N° Acta', field: 'num_acta', align: 'left', sortable: true },
        { name: 'productor', label: 'Productor', field: 'productor_nombre', align: 'left', sortable: true },
        { name: 'organizacion', label: 'Organización', field: 'organizacion_nombre', align: 'left', sortable: true },
        { name: 'producto', label: 'Producto', field: row => row.producto?.nombre_producto, align: 'left', sortable: true },
        { name: 'cantidad_kg', label: 'Cantidad (kg)', field: 'cantidad_kg', align: 'right', sortable: true },
        { name: 'precio', label: 'Precio Bs/kg', field: 'precio_compra', align: 'right', sortable: true },
        { name: 'costo_total', label: 'Costo Total', field: 'costo_total', align: 'right', sortable: true }
      ],

      dialogProcesarTodos: false,
      confirmacionProcesar: false,
      observacionesProcesar: '',

      dialogRechazar: false,
      formRechazo: {
        motivo: null,
        observaciones: '',
        accion_correctiva: ''
      },

      motivosRechazo: [
        { label: 'Calidad Insuficiente', value: 'CALIDAD_INSUFICIENTE' },
        { label: 'Humedad Alta', value: 'HUMEDAD_ALTA' },
        { label: 'Contaminación', value: 'CONTAMINACION' },
        { label: 'Documentación Incompleta', value: 'DOCUMENTACION_INCOMPLETA' },
        { label: 'Temperatura Incorrecta', value: 'TEMPERATURA_INCORRECTA' },
        { label: 'Peso Incorrecto', value: 'PESO_INCORRECTO' },
        { label: 'Envase Inadecuado', value: 'ENVASE_INADECUADO' },
        { label: 'Otro', value: 'OTRO' }
      ]
    }
  },

  computed: {
    productoresFiltrados() {
      if (!this.filtros.organizacion_id) return []
      return this.productores.filter(p => p.organizacion_id === this.filtros.organizacion_id)
    },

    totalKgSeleccionados() {
      return this.acopios
        .filter(a => this.seleccionados.includes(a.id))
        .reduce((sum, a) => sum + parseFloat(a.cantidad_kg || 0), 0)
    },

    totalCostoSeleccionados() {
      return this.acopios
        .filter(a => this.seleccionados.includes(a.id))
        .reduce((sum, a) => sum + parseFloat(a.costo_total || 0), 0)
    }
  },

  mounted() {
    this.cargarDatosIniciales()
    this.cargarAcopios()
  },

  methods: {
    async cargarDatosIniciales() {
      await Promise.all([
        this.cargarOrganizaciones(),
        this.cargarProductores(),
        this.cargarProductos()
      ])
    },

    async cargarOrganizaciones() {
      try {
        const { data } = await this.$axios.get('/organizaciones')
        // Asegurar que tenemos un array válido
        let organizaciones = Array.isArray(data) ? data : (data.data || [])
        
        // Eliminar duplicados por ID (por si acaso)
        const uniqueMap = new Map()
        organizaciones.forEach(org => {
          if (org && org.id && !uniqueMap.has(org.id)) {
            uniqueMap.set(org.id, org)
          }
        })
        this.organizaciones = Array.from(uniqueMap.values())
        
        console.log('Organizaciones cargadas:', this.organizaciones.length, 'únicas')
        console.log('Organizaciones:', this.organizaciones.map(o => `${o.id}: ${o.nombre_organiza}`))
      } catch (error) {
        console.error('Error cargando organizaciones:', error)
        this.$q.notify({
          type: 'negative',
          message: 'Error al cargar organizaciones',
          caption: error.response?.data?.message || error.message
        })
      }
    },

    async cargarProductores() {
      try {
        const { data } = await this.$axios.get('/productores', { params: { per_page: 1000 } })
        // Asegurar que tenemos un array válido
        this.productores = Array.isArray(data) ? data : (data.data || [])
        console.log('Productores cargados:', this.productores.length)
      } catch (error) {
        console.error('Error cargando productores:', error)
        this.$q.notify({
          type: 'negative',
          message: 'Error al cargar productores',
          caption: error.response?.data?.message || error.message
        })
      }
    },

    async cargarProductos() {
      try {
        const { data } = await this.$axios.get('/productos')
        // Asegurar que tenemos un array válido
        this.productos = Array.isArray(data) ? data : (data.data || [])
        console.log('Productos cargados:', this.productos.length)
      } catch (error) {
        console.error('Error cargando productos:', error)
        this.$q.notify({
          type: 'negative',
          message: 'Error al cargar productos',
          caption: error.response?.data?.message || error.message
        })
      }
    },

    async cargarAcopios() {
      this.loading = true
      try {
        const params = {
          ...this.filtros,
          page: this.pagination.page,
          per_page: this.pagination.rowsPerPage,
          sort_by: this.pagination.sortBy,
          sort_dir: this.pagination.descending ? 'desc' : 'asc'
        }

        const { data } = await this.$axios.get('/procesamiento-masivo/acopios-disponibles', { params })
        
        this.acopios = data.acopios.data
        this.totales = data.totales
        this.pagination.rowsNumber = data.acopios.total
        this.pagination.page = data.acopios.current_page

      } catch (error) {
        this.$q.notify({
          type: 'negative',
          message: 'Error al cargar acopios',
          caption: error.response?.data?.message || error.message
        })
      } finally {
        this.loading = false
      }
    },

    onRequest(props) {
      const { page, rowsPerPage, sortBy, descending } = props.pagination
      this.pagination.page = page
      this.pagination.rowsPerPage = rowsPerPage
      this.pagination.sortBy = sortBy
      this.pagination.descending = descending
      this.cargarAcopios()
    },

    aplicarFiltros() {
      this.pagination.page = 1
      this.cargarAcopios()
    },

    limpiarFiltros() {
      this.filtros = {
        organizacion_id: null,
        productor_id: null,
        producto_id: null,
        fecha_desde: null,
        fecha_hasta: null,
        cantidad_min: null,
        cantidad_max: null
      }
      this.aplicarFiltros()
    },

    onOrganizacionChange() {
      this.filtros.productor_id = null
    },

    toggleSelectAll(val) {
      if (val) {
        this.seleccionados = this.acopios.map(a => a.id)
      } else {
        this.seleccionados = []
      }
    },

    procesarTodos() {
      if (this.acopios.length === 0) {
        this.$q.notify({ type: 'warning', message: 'No hay acopios para procesar' })
        return
      }
      this.confirmacionProcesar = false
      this.observacionesProcesar = ''
      this.dialogProcesarTodos = true
    },

    async confirmarProcesarTodos() {
      this.procesando = true
      try {
        const acopioIds = this.acopios.map(a => a.id)
        
        const { data } = await this.$axios.post('/procesamiento-masivo/procesar', {
          acopio_ids: acopioIds,
          tipo_procesamiento: 'AUTOMATICO',
          observaciones: this.observacionesProcesar,
          filtros_aplicados: this.filtros
        })

        this.$q.notify({
          type: 'positive',
          message: 'Procesamiento completado exitosamente',
          caption: `${data.resumen.acopios_procesados} acopios procesados | ${data.resumen.total_kg} kg`,
          timeout: 5000
        })

        this.dialogProcesarTodos = false
        this.cargarAcopios()

      } catch (error) {
        this.$q.notify({
          type: 'negative',
          message: 'Error al procesar',
          caption: error.response?.data?.message || error.message
        })
      } finally {
        this.procesando = false
      }
    },

    procesarSeleccionados() {
      if (this.seleccionados.length === 0) {
        this.$q.notify({ type: 'warning', message: 'No hay acopios seleccionados' })
        return
      }

      this.$q.dialog({
        title: 'Procesar Seleccionados',
        message: `¿Confirma procesar ${this.seleccionados.length} acopios seleccionados (${this.formatNumber(this.totalKgSeleccionados)} kg)?`,
        cancel: true,
        persistent: true
      }).onOk(async () => {
        this.procesando = true
        try {
          const { data } = await this.$axios.post('/procesamiento-masivo/procesar', {
            acopio_ids: this.seleccionados,
            tipo_procesamiento: 'SELECCION',
            filtros_aplicados: this.filtros
          })

          this.$q.notify({
            type: 'positive',
            message: `${data.resumen.acopios_procesados} acopios procesados exitosamente`,
            timeout: 3000
          })

          this.seleccionados = []
          this.selectAll = false
          this.cargarAcopios()

        } catch (error) {
          this.$q.notify({
            type: 'negative',
            message: 'Error al procesar',
            caption: error.response?.data?.message || error.message
          })
        } finally {
          this.procesando = false
        }
      })
    },

    rechazarSeleccionados() {
      if (this.seleccionados.length === 0) {
        this.$q.notify({ type: 'warning', message: 'No hay acopios seleccionados' })
        return
      }
      this.formRechazo = {
        motivo: null,
        observaciones: '',
        accion_correctiva: ''
      }
      this.dialogRechazar = true
    },

    async confirmarRechazo() {
      if (!this.formRechazo.motivo || !this.formRechazo.observaciones) {
        this.$q.notify({ type: 'warning', message: 'Complete los campos requeridos' })
        return
      }

      this.procesando = true
      try {
        const rechazos = this.seleccionados.map(id => ({
          acopio_id: id,
          motivo: this.formRechazo.motivo,
          observaciones: this.formRechazo.observaciones,
          accion_correctiva: this.formRechazo.accion_correctiva
        }))

        const { data } = await this.$axios.post('/procesamiento-masivo/rechazar', { rechazos })

        this.$q.notify({
          type: 'positive',
          message: `${data.resumen.acopios_rechazados} acopios rechazados correctamente`,
          timeout: 3000
        })

        this.dialogRechazar = false
        this.seleccionados = []
        this.selectAll = false
        this.cargarAcopios()

      } catch (error) {
        this.$q.notify({
          type: 'negative',
          message: 'Error al rechazar',
          caption: error.response?.data?.message || error.message
        })
      } finally {
        this.procesando = false
      }
    },

    formatNumber(value) {
      const num = parseFloat(value)
      return isNaN(num) ? '0.00' : num.toFixed(2)
    }
  }
}
</script>

<style scoped>
.text-h6 {
  font-size: 1.5rem;
  font-weight: 500;
}
</style>
