<template>
  <q-page class="q-pa-md">
    <!-- Header -->
    <div class="row items-center q-mb-md">
      <div class="col">
        <div class="text-h5 text-primary">Historial de Procesamientos Masivos</div>
        <div class="text-caption text-grey-7">Registro completo de operaciones de procesamiento</div>
      </div>
      <div class="col-auto">
        <q-btn color="primary" icon="refresh" label="Actualizar" @click="cargarHistorial" :loading="loading" dense no-caps />
      </div>
    </div>

    <!-- Filtros -->
    <q-card flat bordered class="q-mb-md">
      <q-card-section>
        <div class="text-subtitle2 q-mb-md">
          <q-icon name="filter_list" class="q-mr-xs" />
          Filtros
        </div>
        
        <div class="row q-col-gutter-md">
          <div class="col-12 col-sm-3">
            <q-select
              v-model="filtros.tipo_procesamiento"
              :options="tiposProcesamiento"
              label="Tipo de Procesamiento"
              dense outlined clearable
              emit-value map-options
            >
              <template v-slot:prepend>
                <q-icon name="category" />
              </template>
            </q-select>
          </div>

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

          <div class="col-12 col-sm-3 text-right">
            <q-btn color="grey-7" label="Limpiar" icon="clear" @click="limpiarFiltros" flat dense no-caps class="q-mr-sm" />
            <q-btn color="primary" label="Aplicar" icon="search" @click="aplicarFiltros" :loading="loading" dense no-caps />
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- Tabla de historial -->
    <q-card flat bordered>
      <q-table
        :rows="historial"
        :columns="columns"
        row-key="id"
        :loading="loading"
        :pagination="pagination"
        @request="onRequest"
        binary-state-sort
        flat
      >
        <!-- Fecha -->
        <template v-slot:body-cell-fecha_ejecucion="props">
          <q-td :props="props">
            <div>{{ formatFecha(props.row.fecha_ejecucion) }}</div>
            <div class="text-caption text-grey-7">{{ formatHora(props.row.fecha_ejecucion) }}</div>
          </q-td>
        </template>

        <!-- Tipo -->
        <template v-slot:body-cell-tipo_procesamiento="props">
          <q-td :props="props">
            <q-chip 
              :color="getTipoColor(props.row.tipo_procesamiento)" 
              text-color="white" 
              size="sm" 
              dense
            >
              {{ getTipoLabel(props.row.tipo_procesamiento) }}
            </q-chip>
          </q-td>
        </template>

        <!-- Usuario -->
        <template v-slot:body-cell-usuario="props">
          <q-td :props="props">
            <div>{{ props.row.usuario?.name }}</div>
            <div class="text-caption text-grey-7">{{ props.row.usuario?.email }}</div>
          </q-td>
        </template>

        <!-- Acopios procesados -->
        <template v-slot:body-cell-acopios_procesados="props">
          <q-td :props="props" class="text-center">
            <div class="text-weight-bold text-positive">{{ props.row.acopios_procesados }}</div>
            <div class="text-caption text-grey-7" v-if="props.row.acopios_rechazados > 0">
              {{ props.row.acopios_rechazados }} rechazados
            </div>
            <div class="text-caption text-grey-7" v-if="props.row.acopios_fallidos > 0">
              <span class="text-negative">{{ props.row.acopios_fallidos }} fallidos</span>
            </div>
          </q-td>
        </template>

        <!-- Total KG -->
        <template v-slot:body-cell-total_kg_procesado="props">
          <q-td :props="props" class="text-right">
            <span class="text-weight-bold">{{ formatNumber(props.row.total_kg_procesado) }}</span>
          </q-td>
        </template>

        <!-- Costo -->
        <template v-slot:body-cell-total_costo="props">
          <q-td :props="props" class="text-right">
            {{ formatNumber(props.row.total_costo) }}
          </q-td>
        </template>

        <!-- Duración -->
        <template v-slot:body-cell-duracion_segundos="props">
          <q-td :props="props" class="text-center">
            <q-chip size="sm" :color="getDuracionColor(props.row.duracion_segundos)" text-color="white" dense>
              {{ formatDuracion(props.row.duracion_segundos) }}
            </q-chip>
          </q-td>
        </template>

        <!-- Acciones -->
        <template v-slot:body-cell-acciones="props">
          <q-td :props="props">
            <q-btn 
              icon="visibility" 
              color="primary" 
              flat dense round
              @click="verDetalle(props.row)"
            >
              <q-tooltip>Ver Detalle</q-tooltip>
            </q-btn>
          </q-td>
        </template>
      </q-table>
    </q-card>

    <!-- Diálogo: Detalle del procesamiento -->
    <q-dialog v-model="dialogDetalle" maximized>
      <q-card v-if="registroSeleccionado">
        <q-card-section class="row items-center bg-primary text-white">
          <q-icon name="article" size="md" class="q-mr-sm" />
          <span class="text-h6">Detalle del Procesamiento #{{ registroSeleccionado.id }}</span>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <div class="row q-col-gutter-md">
            <!-- Información general -->
            <div class="col-12 col-md-6">
              <q-card flat bordered>
                <q-card-section>
                  <div class="text-subtitle1 text-primary q-mb-md">
                    <q-icon name="info" />
                    Información General
                  </div>
                  
                  <q-list dense>
                    <q-item>
                      <q-item-section>
                        <q-item-label caption>Tipo de Procesamiento</q-item-label>
                        <q-item-label>
                          <q-chip 
                            :color="getTipoColor(registroSeleccionado.tipo_procesamiento)" 
                            text-color="white" 
                            size="sm"
                          >
                            {{ getTipoLabel(registroSeleccionado.tipo_procesamiento) }}
                          </q-chip>
                        </q-item-label>
                      </q-item-section>
                    </q-item>

                    <q-item>
                      <q-item-section>
                        <q-item-label caption>Fecha de Ejecución</q-item-label>
                        <q-item-label>
                          {{ formatFechaCompleta(registroSeleccionado.fecha_ejecucion) }}
                        </q-item-label>
                      </q-item-section>
                    </q-item>

                    <q-item>
                      <q-item-section>
                        <q-item-label caption>Usuario Responsable</q-item-label>
                        <q-item-label>{{ registroSeleccionado.usuario?.name }}</q-item-label>
                        <q-item-label caption>{{ registroSeleccionado.usuario?.email }}</q-item-label>
                      </q-item-section>
                    </q-item>

                    <q-item>
                      <q-item-section>
                        <q-item-label caption>Duración</q-item-label>
                        <q-item-label>{{ formatDuracion(registroSeleccionado.duracion_segundos) }}</q-item-label>
                      </q-item-section>
                    </q-item>

                    <q-item v-if="registroSeleccionado.observaciones">
                      <q-item-section>
                        <q-item-label caption>Observaciones</q-item-label>
                        <q-item-label>{{ registroSeleccionado.observaciones }}</q-item-label>
                      </q-item-section>
                    </q-item>
                  </q-list>
                </q-card-section>
              </q-card>
            </div>

            <!-- Estadísticas -->
            <div class="col-12 col-md-6">
              <q-card flat bordered>
                <q-card-section>
                  <div class="text-subtitle1 text-primary q-mb-md">
                    <q-icon name="assessment" />
                    Estadísticas
                  </div>
                  
                  <div class="row q-col-gutter-sm">
                    <div class="col-6">
                      <q-card flat class="bg-positive text-white text-center q-pa-md">
                        <div class="text-h5">{{ registroSeleccionado.acopios_procesados }}</div>
                        <div class="text-caption">Acopios Procesados</div>
                      </q-card>
                    </div>
                    <div class="col-6">
                      <q-card flat class="bg-green text-white text-center q-pa-md">
                        <div class="text-h5">{{ formatNumber(registroSeleccionado.total_kg_procesado) }}</div>
                        <div class="text-caption">Kilogramos</div>
                      </q-card>
                    </div>
                    <div class="col-6">
                      <q-card flat class="bg-orange text-white text-center q-pa-md">
                        <div class="text-h5">{{ formatNumber(registroSeleccionado.total_costo) }}</div>
                        <div class="text-caption">Costo Total (Bs)</div>
                      </q-card>
                    </div>
                    <div class="col-6" v-if="registroSeleccionado.acopios_rechazados > 0">
                      <q-card flat class="bg-warning text-white text-center q-pa-md">
                        <div class="text-h5">{{ registroSeleccionado.acopios_rechazados }}</div>
                        <div class="text-caption">Rechazados</div>
                      </q-card>
                    </div>
                    <div class="col-6" v-if="registroSeleccionado.acopios_fallidos > 0">
                      <q-card flat class="bg-negative text-white text-center q-pa-md">
                        <div class="text-h5">{{ registroSeleccionado.acopios_fallidos }}</div>
                        <div class="text-caption">Fallidos</div>
                      </q-card>
                    </div>
                  </div>
                </q-card-section>
              </q-card>
            </div>

            <!-- Filtros aplicados -->
            <div class="col-12" v-if="registroSeleccionado.filtros_aplicados">
              <q-card flat bordered>
                <q-card-section>
                  <div class="text-subtitle1 text-primary q-mb-md">
                    <q-icon name="tune" />
                    Filtros Aplicados
                  </div>
                  
                  <div class="row q-col-gutter-sm">
                    <div 
                      v-for="(value, key) in registroSeleccionado.filtros_aplicados" 
                      :key="key"
                      class="col-12 col-sm-6 col-md-4"
                    >
                      <q-chip outline color="primary" size="md" v-if="value">
                        <strong>{{ formatFiltroKey(key) }}:</strong>&nbsp;{{ value }}
                      </q-chip>
                    </div>
                  </div>
                </q-card-section>
              </q-card>
            </div>

            <!-- Acopios procesados -->
            <div class="col-12">
              <q-card flat bordered>
                <q-card-section>
                  <div class="text-subtitle1 text-primary q-mb-md">
                    <q-icon name="list" />
                    Acopios Procesados ({{ registroSeleccionado.acopio_ids?.length || 0 }})
                  </div>
                  
                  <div class="row q-col-gutter-xs">
                    <div 
                      v-for="acopioId in registroSeleccionado.acopio_ids" 
                      :key="acopioId"
                      class="col-auto"
                    >
                      <q-chip size="sm" color="blue-grey-2">
                        Acopio #{{ acopioId }}
                      </q-chip>
                    </div>
                  </div>
                </q-card-section>
              </q-card>
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn label="Cerrar" color="primary" flat v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import moment from 'moment'

export default {
  name: 'HistorialProcesamientos',
  
  data() {
    return {
      loading: false,
      historial: [],
      
      filtros: {
        tipo_procesamiento: null,
        fecha_desde: null,
        fecha_hasta: null
      },

      tiposProcesamiento: [
        { label: 'Automático', value: 'AUTOMATICO' },
        { label: 'Manual', value: 'MANUAL' },
        { label: 'Selección', value: 'SELECCION' }
      ],

      pagination: {
        sortBy: 'fecha_ejecucion',
        descending: true,
        page: 1,
        rowsPerPage: 15,
        rowsNumber: 0
      },

      columns: [
        { name: 'fecha_ejecucion', label: 'Fecha', field: 'fecha_ejecucion', align: 'left', sortable: true },
        { name: 'tipo_procesamiento', label: 'Tipo', field: 'tipo_procesamiento', align: 'center', sortable: true },
        { name: 'usuario', label: 'Usuario', field: row => row.usuario?.name, align: 'left', sortable: true },
        { name: 'acopios_procesados', label: 'Procesados', field: 'acopios_procesados', align: 'center', sortable: true },
        { name: 'total_kg_procesado', label: 'Kg Total', field: 'total_kg_procesado', align: 'right', sortable: true },
        { name: 'total_costo', label: 'Costo (Bs)', field: 'total_costo', align: 'right', sortable: true },
        { name: 'duracion_segundos', label: 'Duración', field: 'duracion_segundos', align: 'center', sortable: true },
        { name: 'acciones', label: 'Acciones', align: 'center', style: 'width: 80px' }
      ],

      dialogDetalle: false,
      registroSeleccionado: null
    }
  },

  mounted() {
    this.cargarHistorial()
  },

  methods: {
    async cargarHistorial() {
      this.loading = true
      try {
        const params = {
          ...this.filtros,
          page: this.pagination.page,
          per_page: this.pagination.rowsPerPage,
          sort_by: this.pagination.sortBy,
          sort_dir: this.pagination.descending ? 'desc' : 'asc'
        }

        const { data } = await this.$axios.get('/procesamiento-masivo/historial', { params })
        
        this.historial = data.data
        this.pagination.rowsNumber = data.total
        this.pagination.page = data.current_page

      } catch (error) {
        this.$q.notify({
          type: 'negative',
          message: 'Error al cargar historial',
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
      this.cargarHistorial()
    },

    aplicarFiltros() {
      this.pagination.page = 1
      this.cargarHistorial()
    },

    limpiarFiltros() {
      this.filtros = {
        tipo_procesamiento: null,
        fecha_desde: null,
        fecha_hasta: null
      }
      this.aplicarFiltros()
    },

    verDetalle(registro) {
      this.registroSeleccionado = registro
      this.dialogDetalle = true
    },

    getTipoColor(tipo) {
      const colores = {
        'AUTOMATICO': 'positive',
        'MANUAL': 'primary',
        'SELECCION': 'purple'
      }
      return colores[tipo] || 'grey'
    },

    getTipoLabel(tipo) {
      const labels = {
        'AUTOMATICO': 'Automático',
        'MANUAL': 'Manual',
        'SELECCION': 'Selección'
      }
      return labels[tipo] || tipo
    },

    getDuracionColor(segundos) {
      if (segundos < 5) return 'positive'
      if (segundos < 15) return 'orange'
      return 'negative'
    },

    formatNumber(value) {
      const num = parseFloat(value)
      return isNaN(num) ? '0.00' : num.toFixed(2)
    },

    formatFecha(fecha) {
      return moment(fecha).format('DD/MM/YYYY')
    },

    formatHora(fecha) {
      return moment(fecha).format('HH:mm:ss')
    },

    formatFechaCompleta(fecha) {
      return moment(fecha).format('DD/MM/YYYY HH:mm:ss')
    },

    formatDuracion(segundos) {
      if (segundos < 60) return `${segundos}s`
      const minutos = Math.floor(segundos / 60)
      const segs = segundos % 60
      return `${minutos}m ${segs}s`
    },

    formatFiltroKey(key) {
      const labels = {
        'organizacion_id': 'Organización',
        'productor_id': 'Productor',
        'producto_id': 'Producto',
        'fecha_desde': 'Fecha Desde',
        'fecha_hasta': 'Fecha Hasta',
        'cantidad_min': 'Cantidad Mín',
        'cantidad_max': 'Cantidad Máx'
      }
      return labels[key] || key
    }
  }
}
</script>

<style scoped>
.q-chip {
  font-size: 0.875rem;
}
</style>
