<template>
  <q-page class="q-pa-md">
    <!-- Header -->
    <div class="row items-center q-mb-md">
      <div class="col">
        <div class="text-h5 text-primary">Acopios Rechazados</div>
        <div class="text-caption text-grey-7">Gestión de acopios rechazados y seguimiento de devoluciones</div>
      </div>
      <div class="col-auto">
        <q-btn color="primary" icon="refresh" label="Actualizar" @click="cargarRechazados" :loading="loading" dense no-caps />
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-sm-3">
        <q-card flat bordered class="cursor-pointer" @click="filtros.estado_devolucion = 'PENDIENTE'; aplicarFiltros()">
          <q-card-section class="text-center">
            <div class="text-h6 text-warning">{{ stats.pendientes }}</div>
            <div class="text-caption text-grey-7">Pendientes</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-sm-3">
        <q-card flat bordered class="cursor-pointer" @click="filtros.estado_devolucion = 'NOTIFICADO'; aplicarFiltros()">
          <q-card-section class="text-center">
            <div class="text-h6 text-info">{{ stats.notificados }}</div>
            <div class="text-caption text-grey-7">Notificados</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-sm-3">
        <q-card flat bordered class="cursor-pointer" @click="filtros.estado_devolucion = 'DEVUELTO'; aplicarFiltros()">
          <q-card-section class="text-center">
            <div class="text-h6 text-positive">{{ stats.devueltos }}</div>
            <div class="text-caption text-grey-7">Devueltos</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-sm-3">
        <q-card flat bordered class="cursor-pointer" @click="filtros.estado_devolucion = 'CANCELADO'; aplicarFiltros()">
          <q-card-section class="text-center">
            <div class="text-h6 text-grey-7">{{ stats.cancelados }}</div>
            <div class="text-caption text-grey-7">Cancelados</div>
          </q-card-section>
        </q-card>
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
              v-model="filtros.estado_devolucion"
              :options="estadosDevolucion"
              label="Estado"
              dense outlined clearable
              emit-value map-options
            >
              <template v-slot:prepend>
                <q-icon name="label" />
              </template>
            </q-select>
          </div>

          <div class="col-12 col-sm-3">
            <q-select
              v-model="filtros.motivo_rechazo"
              :options="motivosRechazo"
              label="Motivo"
              dense outlined clearable
              emit-value map-options
            >
              <template v-slot:prepend>
                <q-icon name="warning" />
              </template>
            </q-select>
          </div>

          <div class="col-12 col-sm-3">
            <q-select
              v-model="filtros.organizacion_id"
              :options="organizaciones"
              option-value="id"
              option-label="nombre_organizacion"
              emit-value map-options
              label="Organización"
              dense outlined clearable
            >
              <template v-slot:prepend>
                <q-icon name="business" />
              </template>
            </q-select>
          </div>

          <div class="col-12 col-sm-3 text-right">
            <q-btn color="grey-7" label="Limpiar" icon="clear" @click="limpiarFiltros" flat dense no-caps class="q-mr-sm" />
            <q-btn color="primary" label="Aplicar" icon="search" @click="aplicarFiltros" :loading="loading" dense no-caps />
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- Tabla de rechazados -->
    <q-card flat bordered>
      <q-table
        :rows="rechazados"
        :columns="columns"
        row-key="id"
        :loading="loading"
        :pagination="pagination"
        @request="onRequest"
        binary-state-sort
        flat
      >
        <!-- Fecha Rechazo -->
        <template v-slot:body-cell-fecha_rechazo="props">
          <q-td :props="props">
            <div>{{ formatFecha(props.row.fecha_rechazo) }}</div>
            <q-chip size="xs" :color="getDiasColor(props.row.dias_desde_rechazo)" text-color="white" dense>
              Hace {{ props.row.dias_desde_rechazo }}d
            </q-chip>
          </q-td>
        </template>

        <!-- Acopio -->
        <template v-slot:body-cell-acopio="props">
          <q-td :props="props">
            <div class="text-weight-bold">#{{ props.row.acopio_cosecha?.num_acta }}</div>
            <div class="text-caption text-grey-7">
              {{ props.row.acopio_cosecha?.producto?.nombre_producto }}
            </div>
            <div class="text-caption">
              {{ formatNumber(props.row.acopio_cosecha?.cantidad_kg) }} kg
            </div>
          </q-td>
        </template>

        <!-- Productor -->
        <template v-slot:body-cell-productor="props">
          <q-td :props="props">
            <div>{{ props.row.acopio_cosecha?.productor?.nombre }} {{ props.row.acopio_cosecha?.productor?.apellidos }}</div>
            <div class="text-caption text-grey-7">
              {{ props.row.acopio_cosecha?.organizacion?.nombre_organizacion || 'Independiente' }}
            </div>
          </q-td>
        </template>

        <!-- Motivo -->
        <template v-slot:body-cell-motivo_rechazo="props">
          <q-td :props="props">
            <q-chip size="sm" color="negative" text-color="white" dense>
              {{ getMotivoLabel(props.row.motivo_rechazo) }}
            </q-chip>
            <div class="text-caption q-mt-xs" v-if="props.row.observaciones">
              {{ truncate(props.row.observaciones, 50) }}
            </div>
          </q-td>
        </template>

        <!-- Estado -->
        <template v-slot:body-cell-estado_devolucion="props">
          <q-td :props="props">
            <q-chip 
              size="sm" 
              :color="getEstadoColor(props.row.estado_devolucion)" 
              text-color="white" 
              dense
            >
              {{ getEstadoLabel(props.row.estado_devolucion) }}
            </q-chip>
            <div class="text-caption q-mt-xs" v-if="props.row.fecha_devolucion">
              Devuelto: {{ formatFecha(props.row.fecha_devolucion) }}
            </div>
          </q-td>
        </template>

        <!-- Rechazado Por -->
        <template v-slot:body-cell-rechazado_por="props">
          <q-td :props="props">
            <div>{{ props.row.rechazado_por_usuario?.name }}</div>
            <div class="text-caption text-grey-7">{{ props.row.rechazado_por_usuario?.email }}</div>
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
            
            <q-btn 
              v-if="props.row.estado_devolucion === 'PENDIENTE'"
              icon="notifications" 
              color="info" 
              flat dense round
              @click="marcarComoNotificado(props.row)"
            >
              <q-tooltip>Marcar como Notificado</q-tooltip>
            </q-btn>

            <q-btn 
              v-if="['PENDIENTE', 'NOTIFICADO'].includes(props.row.estado_devolucion)"
              icon="check_circle" 
              color="positive" 
              flat dense round
              @click="marcarComoDevuelto(props.row)"
            >
              <q-tooltip>Marcar como Devuelto</q-tooltip>
            </q-btn>
          </q-td>
        </template>
      </q-table>
    </q-card>

    <!-- Diálogo: Detalle del rechazo -->
    <q-dialog v-model="dialogDetalle" maximized>
      <q-card v-if="rechazoSeleccionado">
        <q-card-section class="row items-center bg-negative text-white">
          <q-icon name="cancel" size="md" class="q-mr-sm" />
          <span class="text-h6">Detalle del Rechazo #{{ rechazoSeleccionado.id }}</span>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <div class="row q-col-gutter-md">
            <!-- Información del Acopio -->
            <div class="col-12 col-md-6">
              <q-card flat bordered>
                <q-card-section>
                  <div class="text-subtitle1 text-primary q-mb-md">
                    <q-icon name="inventory" />
                    Información del Acopio
                  </div>
                  
                  <q-list dense>
                    <q-item>
                      <q-item-section>
                        <q-item-label caption>N° Acta</q-item-label>
                        <q-item-label class="text-h6">#{{ rechazoSeleccionado.acopio_cosecha?.num_acta }}</q-item-label>
                      </q-item-section>
                    </q-item>

                    <q-item>
                      <q-item-section>
                        <q-item-label caption>Producto</q-item-label>
                        <q-item-label>{{ rechazoSeleccionado.acopio_cosecha?.producto?.nombre_producto }}</q-item-label>
                      </q-item-section>
                    </q-item>

                    <q-item>
                      <q-item-section>
                        <q-item-label caption>Cantidad</q-item-label>
                        <q-item-label class="text-h6 text-green">
                          {{ formatNumber(rechazoSeleccionado.acopio_cosecha?.cantidad_kg) }} kg
                        </q-item-label>
                      </q-item-section>
                    </q-item>

                    <q-item>
                      <q-item-section>
                        <q-item-label caption>Fecha Cosecha</q-item-label>
                        <q-item-label>{{ rechazoSeleccionado.acopio_cosecha?.fecha_cosecha }}</q-item-label>
                      </q-item-section>
                    </q-item>

                    <q-item>
                      <q-item-section>
                        <q-item-label caption>Productor</q-item-label>
                        <q-item-label>
                          {{ rechazoSeleccionado.acopio_cosecha?.productor?.nombre }} 
                          {{ rechazoSeleccionado.acopio_cosecha?.productor?.apellidos }}
                        </q-item-label>
                      </q-item-section>
                    </q-item>

                    <q-item>
                      <q-item-section>
                        <q-item-label caption>Organización</q-item-label>
                        <q-item-label>
                          {{ rechazoSeleccionado.acopio_cosecha?.organizacion?.nombre_organizacion || 'Independiente' }}
                        </q-item-label>
                      </q-item-section>
                    </q-item>
                  </q-list>
                </q-card-section>
              </q-card>
            </div>

            <!-- Información del Rechazo -->
            <div class="col-12 col-md-6">
              <q-card flat bordered>
                <q-card-section>
                  <div class="text-subtitle1 text-primary q-mb-md">
                    <q-icon name="cancel" />
                    Información del Rechazo
                  </div>
                  
                  <q-list dense>
                    <q-item>
                      <q-item-section>
                        <q-item-label caption>Motivo</q-item-label>
                        <q-chip size="md" color="negative" text-color="white">
                          {{ getMotivoLabel(rechazoSeleccionado.motivo_rechazo) }}
                        </q-chip>
                      </q-item-section>
                    </q-item>

                    <q-item>
                      <q-item-section>
                        <q-item-label caption>Estado</q-item-label>
                        <q-chip 
                          size="md" 
                          :color="getEstadoColor(rechazoSeleccionado.estado_devolucion)" 
                          text-color="white"
                        >
                          {{ getEstadoLabel(rechazoSeleccionado.estado_devolucion) }}
                        </q-chip>
                      </q-item-section>
                    </q-item>

                    <q-item>
                      <q-item-section>
                        <q-item-label caption>Fecha de Rechazo</q-item-label>
                        <q-item-label>{{ formatFechaCompleta(rechazoSeleccionado.fecha_rechazo) }}</q-item-label>
                        <q-item-label caption>Hace {{ rechazoSeleccionado.dias_desde_rechazo }} días</q-item-label>
                      </q-item-section>
                    </q-item>

                    <q-item>
                      <q-item-section>
                        <q-item-label caption>Rechazado por</q-item-label>
                        <q-item-label>{{ rechazoSeleccionado.rechazado_por_usuario?.name }}</q-item-label>
                        <q-item-label caption>{{ rechazoSeleccionado.rechazado_por_usuario?.email }}</q-item-label>
                      </q-item-section>
                    </q-item>

                    <q-item v-if="rechazoSeleccionado.fecha_notificacion">
                      <q-item-section>
                        <q-item-label caption>Fecha Notificación</q-item-label>
                        <q-item-label>{{ formatFechaCompleta(rechazoSeleccionado.fecha_notificacion) }}</q-item-label>
                      </q-item-section>
                    </q-item>

                    <q-item v-if="rechazoSeleccionado.fecha_devolucion">
                      <q-item-section>
                        <q-item-label caption>Fecha Devolución</q-item-label>
                        <q-item-label>{{ formatFechaCompleta(rechazoSeleccionado.fecha_devolucion) }}</q-item-label>
                      </q-item-section>
                    </q-item>

                    <q-item v-if="rechazoSeleccionado.devuelto_por_usuario">
                      <q-item-section>
                        <q-item-label caption>Devuelto por</q-item-label>
                        <q-item-label>{{ rechazoSeleccionado.devuelto_por_usuario?.name }}</q-item-label>
                      </q-item-section>
                    </q-item>
                  </q-list>
                </q-card-section>
              </q-card>
            </div>

            <!-- Observaciones -->
            <div class="col-12">
              <q-card flat bordered>
                <q-card-section>
                  <div class="text-subtitle1 text-primary q-mb-md">
                    <q-icon name="description" />
                    Observaciones Detalladas
                  </div>
                  <p>{{ rechazoSeleccionado.observaciones }}</p>
                </q-card-section>
              </q-card>
            </div>

            <!-- Acción Correctiva -->
            <div class="col-12" v-if="rechazoSeleccionado.accion_correctiva">
              <q-card flat bordered>
                <q-card-section>
                  <div class="text-subtitle1 text-primary q-mb-md">
                    <q-icon name="build" />
                    Acción Correctiva Sugerida
                  </div>
                  <p>{{ rechazoSeleccionado.accion_correctiva }}</p>
                </q-card-section>
              </q-card>
            </div>

            <!-- Evidencias -->
            <div class="col-12" v-if="rechazoSeleccionado.evidencias && rechazoSeleccionado.evidencias.length > 0">
              <q-card flat bordered>
                <q-card-section>
                  <div class="text-subtitle1 text-primary q-mb-md">
                    <q-icon name="photo_library" />
                    Evidencias
                  </div>
                  <div class="row q-col-gutter-sm">
                    <div v-for="(evidencia, index) in rechazoSeleccionado.evidencias" :key="index" class="col-auto">
                      <q-chip>{{ evidencia }}</q-chip>
                    </div>
                  </div>
                </q-card-section>
              </q-card>
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn 
            v-if="rechazoSeleccionado.estado_devolucion === 'PENDIENTE'"
            label="Marcar como Notificado" 
            color="info" 
            icon="notifications"
            @click="marcarComoNotificado(rechazoSeleccionado)"
            flat 
          />
          <q-btn 
            v-if="['PENDIENTE', 'NOTIFICADO'].includes(rechazoSeleccionado.estado_devolucion)"
            label="Marcar como Devuelto" 
            color="positive" 
            icon="check_circle"
            @click="marcarComoDevuelto(rechazoSeleccionado)"
            flat 
          />
          <q-btn label="Cerrar" color="primary" flat v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import moment from 'moment'

export default {
  name: 'AcopiosRechazados',
  
  data() {
    return {
      loading: false,
      rechazados: [],
      
      stats: {
        pendientes: 0,
        notificados: 0,
        devueltos: 0,
        cancelados: 0
      },

      filtros: {
        estado_devolucion: null,
        motivo_rechazo: null,
        organizacion_id: null
      },

      organizaciones: [],

      estadosDevolucion: [
        { label: 'Pendiente', value: 'PENDIENTE' },
        { label: 'Notificado', value: 'NOTIFICADO' },
        { label: 'Devuelto', value: 'DEVUELTO' },
        { label: 'Cancelado', value: 'CANCELADO' }
      ],

      motivosRechazo: [
        { label: 'Calidad Insuficiente', value: 'CALIDAD_INSUFICIENTE' },
        { label: 'Humedad Alta', value: 'HUMEDAD_ALTA' },
        { label: 'Contaminación', value: 'CONTAMINACION' },
        { label: 'Documentación Incompleta', value: 'DOCUMENTACION_INCOMPLETA' },
        { label: 'Temperatura Incorrecta', value: 'TEMPERATURA_INCORRECTA' },
        { label: 'Peso Incorrecto', value: 'PESO_INCORRECTO' },
        { label: 'Envase Inadecuado', value: 'ENVASE_INADECUADO' },
        { label: 'Otro', value: 'OTRO' }
      ],

      pagination: {
        sortBy: 'fecha_rechazo',
        descending: true,
        page: 1,
        rowsPerPage: 15,
        rowsNumber: 0
      },

      columns: [
        { name: 'fecha_rechazo', label: 'Fecha Rechazo', field: 'fecha_rechazo', align: 'left', sortable: true },
        { name: 'acopio', label: 'Acopio', field: 'acopio_cosecha_id', align: 'left', sortable: false },
        { name: 'productor', label: 'Productor', field: row => row.acopio_cosecha?.productor?.nombre, align: 'left', sortable: true },
        { name: 'motivo_rechazo', label: 'Motivo', field: 'motivo_rechazo', align: 'left', sortable: true },
        { name: 'estado_devolucion', label: 'Estado', field: 'estado_devolucion', align: 'center', sortable: true },
        { name: 'rechazado_por', label: 'Rechazado Por', field: row => row.rechazado_por_usuario?.name, align: 'left', sortable: true },
        { name: 'acciones', label: 'Acciones', align: 'center', style: 'width: 150px' }
      ],

      dialogDetalle: false,
      rechazoSeleccionado: null
    }
  },

  mounted() {
    this.cargarOrganizaciones()
    this.cargarRechazados()
    this.cargarEstadisticas()
  },

  methods: {
    async cargarOrganizaciones() {
      try {
        const { data } = await this.$axios.get('/organizaciones')
        this.organizaciones = data.data || data
      } catch (error) {
        console.error('Error cargando organizaciones:', error)
      }
    },

    async cargarRechazados() {
      this.loading = true
      try {
        const params = {
          ...this.filtros,
          page: this.pagination.page,
          per_page: this.pagination.rowsPerPage,
          sort_by: this.pagination.sortBy,
          sort_dir: this.pagination.descending ? 'desc' : 'asc'
        }

        // Este endpoint debe crearse en el backend
        const { data } = await this.$axios.get('/acopio-rechazos', { params })
        
        this.rechazados = data.data
        this.pagination.rowsNumber = data.total
        this.pagination.page = data.current_page

      } catch (error) {
        this.$q.notify({
          type: 'negative',
          message: 'Error al cargar rechazados',
          caption: error.response?.data?.message || error.message
        })
      } finally {
        this.loading = false
      }
    },

    async cargarEstadisticas() {
      try {
        const { data } = await this.$axios.get('/acopio-rechazos/estadisticas')
        this.stats = data
      } catch (error) {
        console.error('Error cargando estadísticas:', error)
      }
    },

    onRequest(props) {
      const { page, rowsPerPage, sortBy, descending } = props.pagination
      this.pagination.page = page
      this.pagination.rowsPerPage = rowsPerPage
      this.pagination.sortBy = sortBy
      this.pagination.descending = descending
      this.cargarRechazados()
    },

    aplicarFiltros() {
      this.pagination.page = 1
      this.cargarRechazados()
    },

    limpiarFiltros() {
      this.filtros = {
        estado_devolucion: null,
        motivo_rechazo: null,
        organizacion_id: null
      }
      this.aplicarFiltros()
    },

    verDetalle(rechazo) {
      this.rechazoSeleccionado = rechazo
      this.dialogDetalle = true
    },

    async marcarComoNotificado(rechazo) {
      try {
        await this.$axios.patch(`/acopio-rechazos/${rechazo.id}/notificar`)
        
        this.$q.notify({
          type: 'positive',
          message: 'Marcado como notificado exitosamente'
        })
        
        this.cargarRechazados()
        this.cargarEstadisticas()
        
        if (this.dialogDetalle) {
          this.dialogDetalle = false
        }
      } catch (error) {
        this.$q.notify({
          type: 'negative',
          message: 'Error al marcar como notificado',
          caption: error.response?.data?.message || error.message
        })
      }
    },

    async marcarComoDevuelto(rechazo) {
      this.$q.dialog({
        title: 'Confirmar Devolución',
        message: '¿Confirma que el acopio ha sido devuelto al productor?',
        cancel: true,
        persistent: true
      }).onOk(async () => {
        try {
          await this.$axios.patch(`/acopio-rechazos/${rechazo.id}/devolver`)
          
          this.$q.notify({
            type: 'positive',
            message: 'Marcado como devuelto exitosamente'
          })
          
          this.cargarRechazados()
          this.cargarEstadisticas()
          
          if (this.dialogDetalle) {
            this.dialogDetalle = false
          }
        } catch (error) {
          this.$q.notify({
            type: 'negative',
            message: 'Error al marcar como devuelto',
            caption: error.response?.data?.message || error.message
          })
        }
      })
    },

    getMotivoLabel(motivo) {
      const labels = {
        'CALIDAD_INSUFICIENTE': 'Calidad Insuficiente',
        'HUMEDAD_ALTA': 'Humedad Alta',
        'CONTAMINACION': 'Contaminación',
        'DOCUMENTACION_INCOMPLETA': 'Documentación Incompleta',
        'TEMPERATURA_INCORRECTA': 'Temperatura Incorrecta',
        'PESO_INCORRECTO': 'Peso Incorrecto',
        'ENVASE_INADECUADO': 'Envase Inadecuado',
        'OTRO': 'Otro'
      }
      return labels[motivo] || motivo
    },

    getEstadoLabel(estado) {
      const labels = {
        'PENDIENTE': 'Pendiente',
        'NOTIFICADO': 'Notificado',
        'DEVUELTO': 'Devuelto',
        'CANCELADO': 'Cancelado'
      }
      return labels[estado] || estado
    },

    getEstadoColor(estado) {
      const colores = {
        'PENDIENTE': 'warning',
        'NOTIFICADO': 'info',
        'DEVUELTO': 'positive',
        'CANCELADO': 'grey'
      }
      return colores[estado] || 'grey'
    },

    getDiasColor(dias) {
      if (dias <= 3) return 'positive'
      if (dias <= 7) return 'warning'
      return 'negative'
    },

    formatNumber(value) {
      const num = parseFloat(value)
      return isNaN(num) ? '0.00' : num.toFixed(2)
    },

    formatFecha(fecha) {
      return moment(fecha).format('DD/MM/YYYY')
    },

    formatFechaCompleta(fecha) {
      return moment(fecha).format('DD/MM/YYYY HH:mm')
    },

    truncate(text, length) {
      if (!text) return ''
      return text.length > length ? text.substring(0, length) + '...' : text
    }
  }
}
</script>

<style scoped>
.cursor-pointer {
  cursor: pointer;
  transition: all 0.3s;
}
.cursor-pointer:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>
