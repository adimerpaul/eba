<template>
  <q-page class="q-pa-md">
    <!-- Encabezado -->
    <div class="row q-mb-md items-center">
      <div class="col-12 col-md-8">
        <div class="text-h5 text-weight-bold">Historial de Transportes</div>
        <div class="text-caption text-grey-7">Registro completo de todos los viajes realizados</div>
      </div>
      <div class="col-12 col-md-4 text-right">
        <q-btn color="primary" icon="refresh" label="Actualizar" no-caps :loading="loading" @click="cargarHistorial"/>
      </div>
    </div>

    <!-- Filtros -->
    <q-card flat bordered class="q-mb-md">
      <q-card-section>
        <div class="text-subtitle2 text-weight-bold q-mb-md">Filtros de Búsqueda</div>
        <div class="row q-col-gutter-md">
          <div class="col-12 col-md-2">
            <q-select
              v-model="filtros.tipo_viaje"
              dense
              outlined
              label="Tipo de Viaje"
              :options="tipoViajeOptions"
              emit-value
              map-options
              clearable
            >
              <template #prepend>
                <q-icon name="compare_arrows" />
              </template>
            </q-select>
          </div>
          <div class="col-12 col-md-2">
            <q-input
              v-model="filtros.fecha_inicio"
              dense
              outlined
              label="Fecha Inicio"
              type="date"
            >
              <template #prepend>
                <q-icon name="event" />
              </template>
            </q-input>
          </div>
          <div class="col-12 col-md-2">
            <q-input
              v-model="filtros.fecha_fin"
              dense
              outlined
              label="Fecha Fin"
              type="date"
            >
              <template #prepend>
                <q-icon name="event" />
              </template>
            </q-input>
          </div>
          <div class="col-12 col-md-3">
            <q-select
              v-model="filtros.transporte_id"
              dense
              outlined
              label="Vehículo"
              :options="transportesOptions"
              option-value="id"
              option-label="label"
              emit-value
              map-options
              clearable
            >
              <template #prepend>
                <q-icon name="local_shipping" />
              </template>
            </q-select>
          </div>
          <div class="col-12 col-md-3">
            <q-select
              v-model="filtros.con_alertas"
              dense
              outlined
              label="Estado SENASAG"
              :options="estadoOptions"
              emit-value
              map-options
              clearable
            >
              <template #prepend>
                <q-icon name="verified" />
              </template>
            </q-select>
          </div>
        </div>
        <div class="row q-mt-md">
          <div class="col-12 text-right">
            <q-btn flat label="Limpiar Filtros" color="grey-7" @click="limpiarFiltros" class="q-mr-sm"/>
            <q-btn unelevated color="primary" label="Buscar" icon="search" @click="cargarHistorial"/>
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- Tabla de historial -->
    <q-table
      :rows="historialFiltrado"
      :columns="columns"
      row-key="id"
      flat
      bordered
      :loading="loading"
      :pagination="pagination"
      @request="onRequest"
    >
      <template #body-cell-transporte="props">
        <q-td :props="props">
          <div class="text-weight-bold">{{ props.row.transporte?.empresa }}</div>
          <div class="text-caption text-grey-7">{{ props.row.transporte?.placa }}</div>
        </q-td>
      </template>

      <template #body-cell-origen_destino="props">
        <q-td :props="props">
          <div>
            <q-icon name="location_on" color="positive" size="xs" />
            {{ props.row.lugar_origen }}
          </div>
          <div v-if="props.row.lugar_destino">
            <q-icon name="flag" color="info" size="xs" />
            {{ props.row.lugar_destino }}
          </div>
        </q-td>
      </template>

      <template #body-cell-temperatura="props">
        <q-td :props="props">
          <div v-if="props.row.temperatura_llegada">
            <q-chip 
              dense 
              size="sm"
              :color="props.row.temperatura_llegada > 35 ? 'red' : 'positive'"
              text-color="white"
            >
              🌡️ {{ props.row.temperatura_llegada }}°C
            </q-chip>
            <div class="text-caption text-grey-7">
              Var: {{ (props.row.temperatura_llegada - (props.row.temperatura_salida || 0)).toFixed(1) }}°C
            </div>
          </div>
          <span v-else class="text-grey-6">Sin datos</span>
        </q-td>
      </template>

      <template #body-cell-tiempo="props">
        <q-td :props="props">
          <div v-if="props.row.tiempo_transporte_horas">
            <q-chip 
              dense 
              size="sm"
              :color="props.row.tiempo_transporte_horas > 6 ? 'orange' : 'info'"
              text-color="white"
            >
              ⏱️ {{ props.row.tiempo_transporte_horas }} hrs
            </q-chip>
          </div>
          <span v-else class="text-grey-6">Sin datos</span>
        </q-td>
      </template>

      <template #body-cell-estado="props">
        <q-td :props="props">
          <q-badge
            :color="getEstadoColor(props.row)"
            :label="getEstadoLabel(props.row)"
          />
          <div v-if="props.row.alerta_temperatura || props.row.alerta_tiempo" class="q-mt-xs">
            <q-chip v-if="props.row.alerta_temperatura" dense size="xs" color="red" text-color="white">
              Temp
            </q-chip>
            <q-chip v-if="props.row.alerta_tiempo" dense size="xs" color="orange" text-color="white">
              Tiempo
            </q-chip>
          </div>
        </q-td>
      </template>

      <template #body-cell-actions="props">
        <q-td :props="props" class="text-right">
          <q-btn flat dense color="info" icon="visibility" @click="verDetalle(props.row)">
            <q-tooltip>Ver detalle completo</q-tooltip>
          </q-btn>
        </q-td>
      </template>
    </q-table>

    <!-- Diálogo de detalle -->
    <q-dialog v-model="dlgDetalle.open" maximized>
      <q-card v-if="dlgDetalle.log">
        <q-card-section class="bg-primary text-white">
          <div class="text-h6">
            <q-icon name="info" class="q-mr-sm" />
            Detalle Completo del Transporte
          </div>
        </q-card-section>

        <q-card-section>
          <div class="row q-col-gutter-md">
            <!-- Información del vehículo -->
            <div class="col-12 col-md-6">
              <q-card flat bordered>
                <q-card-section class="bg-grey-2">
                  <div class="text-subtitle2 text-weight-bold">
                    <q-icon name="local_shipping" color="primary" />
                    Información del Vehículo
                  </div>
                </q-card-section>
                <q-separator />
                <q-card-section>
                  <q-list dense>
                    <q-item>
                      <q-item-section>
                        <q-item-label caption>Empresa</q-item-label>
                        <q-item-label>{{ dlgDetalle.log.transporte?.empresa }}</q-item-label>
                      </q-item-section>
                    </q-item>
                    <q-item>
                      <q-item-section>
                        <q-item-label caption>Placa</q-item-label>
                        <q-item-label>{{ dlgDetalle.log.transporte?.placa }}</q-item-label>
                      </q-item-section>
                    </q-item>
                    <q-item>
                      <q-item-section>
                        <q-item-label caption>Responsable/Conductor</q-item-label>
                        <q-item-label>{{ dlgDetalle.log.transporte?.responsable }}</q-item-label>
                      </q-item-section>
                    </q-item>
                  </q-list>
                </q-card-section>
              </q-card>
            </div>

            <!-- Información del acopio -->
            <div class="col-12 col-md-6">
              <q-card flat bordered>
                <q-card-section class="bg-grey-2">
                  <div class="text-subtitle2 text-weight-bold">
                    <q-icon name="inventory_2" color="amber" />
                    Información del Acopio
                  </div>
                </q-card-section>
                <q-separator />
                <q-card-section>
                  <q-list dense v-if="dlgDetalle.log.acopio_cosecha">
                    <q-item>
                      <q-item-section>
                        <q-item-label caption>Productor</q-item-label>
                        <q-item-label>{{ dlgDetalle.log.acopio_cosecha?.apiario?.productor?.nombre }}</q-item-label>
                      </q-item-section>
                    </q-item>
                    <q-item>
                      <q-item-section>
                        <q-item-label caption>Apiario</q-item-label>
                        <q-item-label>{{ dlgDetalle.log.acopio_cosecha?.apiario?.nombre_cip }}</q-item-label>
                      </q-item-section>
                    </q-item>
                    <q-item>
                      <q-item-section>
                        <q-item-label caption>Cantidad</q-item-label>
                        <q-item-label>{{ dlgDetalle.log.acopio_cosecha?.cantidad_kg }} kg</q-item-label>
                      </q-item-section>
                    </q-item>
                  </q-list>
                </q-card-section>
              </q-card>
            </div>

            <!-- Ruta y distancia -->
            <div class="col-12">
              <q-card flat bordered>
                <q-card-section class="bg-grey-2">
                  <div class="text-subtitle2 text-weight-bold">
                    <q-icon name="route" color="positive" />
                    Ruta y Distancia
                  </div>
                </q-card-section>
                <q-separator />
                <q-card-section>
                  <div class="row items-center">
                    <div class="col">
                      <q-icon name="location_on" color="positive" size="md" />
                      <span class="text-weight-bold q-ml-sm">{{ dlgDetalle.log.lugar_origen }}</span>
                    </div>
                    <div class="col-auto">
                      <q-icon name="arrow_forward" color="grey" size="md" />
                      <span class="text-caption q-mx-sm">{{ dlgDetalle.log.distancia_km || 0 }} km</span>
                    </div>
                    <div class="col text-right">
                      <q-icon name="flag" color="info" size="md" />
                      <span class="text-weight-bold q-ml-sm">{{ dlgDetalle.log.lugar_destino || 'Planta' }}</span>
                    </div>
                  </div>
                </q-card-section>
              </q-card>
            </div>

            <!-- Control de temperatura -->
            <div class="col-12 col-md-6">
              <q-card flat bordered>
                <q-card-section class="bg-grey-2">
                  <div class="text-subtitle2 text-weight-bold">
                    <q-icon name="thermostat" color="red" />
                    Control de Temperatura
                  </div>
                </q-card-section>
                <q-separator />
                <q-card-section>
                  <div class="row q-col-gutter-sm">
                    <div class="col-6">
                      <div class="text-caption text-grey-7">Temperatura Salida</div>
                      <div class="text-h6">{{ dlgDetalle.log.temperatura_salida || '--' }}°C</div>
                    </div>
                    <div class="col-6">
                      <div class="text-caption text-grey-7">Temperatura Llegada</div>
                      <div class="text-h6" :class="dlgDetalle.log.temperatura_llegada > 35 ? 'text-red' : ''">
                        {{ dlgDetalle.log.temperatura_llegada || '--' }}°C
                      </div>
                    </div>
                    <div class="col-6">
                      <div class="text-caption text-grey-7">Temperatura Máxima</div>
                      <div class="text-h6" :class="dlgDetalle.log.temperatura_maxima > 35 ? 'text-red' : ''">
                        {{ dlgDetalle.log.temperatura_maxima || '--' }}°C
                      </div>
                    </div>
                    <div class="col-6">
                      <div class="text-caption text-grey-7">Temperatura Mínima</div>
                      <div class="text-h6">{{ dlgDetalle.log.temperatura_minima || '--' }}°C</div>
                    </div>
                  </div>
                  <q-separator class="q-my-md" />
                  <div>
                    <div class="text-caption text-grey-7">Variación de Temperatura</div>
                    <div class="text-h6">
                      {{ ((dlgDetalle.log.temperatura_llegada || 0) - (dlgDetalle.log.temperatura_salida || 0)).toFixed(1) }}°C
                    </div>
                  </div>
                  <q-banner v-if="dlgDetalle.log.alerta_temperatura" class="bg-red text-white q-mt-md">
                    <template #avatar>
                      <q-icon name="warning" />
                    </template>
                    Alerta: Temperatura excedió el límite de 35°C
                  </q-banner>
                </q-card-section>
              </q-card>
            </div>

            <!-- Control de tiempo -->
            <div class="col-12 col-md-6">
              <q-card flat bordered>
                <q-card-section class="bg-grey-2">
                  <div class="text-subtitle2 text-weight-bold">
                    <q-icon name="schedule" color="orange" />
                    Control de Tiempo
                  </div>
                </q-card-section>
                <q-separator />
                <q-card-section>
                  <div class="row q-col-gutter-sm">
                    <div class="col-12">
                      <div class="text-caption text-grey-7">Fecha/Hora Salida</div>
                      <div class="text-body1">{{ formatearFecha(dlgDetalle.log.fecha_hora_salida) }}</div>
                    </div>
                    <div class="col-12">
                      <div class="text-caption text-grey-7">Fecha/Hora Llegada</div>
                      <div class="text-body1">{{ formatearFecha(dlgDetalle.log.fecha_hora_llegada) }}</div>
                    </div>
                  </div>
                  <q-separator class="q-my-md" />
                  <div>
                    <div class="text-caption text-grey-7">Duración del Transporte</div>
                    <div class="text-h6" :class="dlgDetalle.log.tiempo_transporte_horas > 6 ? 'text-orange' : ''">
                      {{ dlgDetalle.log.tiempo_transporte_horas || '--' }} horas
                    </div>
                  </div>
                  <q-banner v-if="dlgDetalle.log.alerta_tiempo" class="bg-orange text-white q-mt-md">
                    <template #avatar>
                      <q-icon name="warning" />
                    </template>
                    Alerta: Tiempo excedió el límite de 6 horas
                  </q-banner>
                </q-card-section>
              </q-card>
            </div>

            <!-- Condiciones -->
            <div class="col-12">
              <q-card flat bordered>
                <q-card-section class="bg-grey-2">
                  <div class="text-subtitle2 text-weight-bold">
                    <q-icon name="check_circle" color="positive" />
                    Condiciones de Transporte
                  </div>
                </q-card-section>
                <q-separator />
                <q-card-section>
                  <div class="row q-col-gutter-md">
                    <div class="col-6">
                      <div class="text-caption text-grey-7">Condiciones del Envase</div>
                      <q-chip :color="getColorCondicion(dlgDetalle.log.condiciones_envase)" text-color="white">
                        {{ dlgDetalle.log.condiciones_envase || 'No especificado' }}
                      </q-chip>
                    </div>
                    <div class="col-6">
                      <div class="text-caption text-grey-7">Condiciones del Vehículo</div>
                      <q-chip :color="getColorCondicion(dlgDetalle.log.condiciones_vehiculo)" text-color="white">
                        {{ dlgDetalle.log.condiciones_vehiculo || 'No especificado' }}
                      </q-chip>
                    </div>
                    <div class="col-12" v-if="dlgDetalle.log.observaciones">
                      <div class="text-caption text-grey-7">Observaciones</div>
                      <div class="text-body2">{{ dlgDetalle.log.observaciones }}</div>
                    </div>
                  </div>
                </q-card-section>
              </q-card>
            </div>

            <!-- Estado SENASAG -->
            <div class="col-12">
              <q-card flat bordered>
                <q-card-section 
                  :class="getEstadoSenasagClass(dlgDetalle.log)"
                >
                  <div class="text-h6 text-center text-white">
                    <q-icon :name="getEstadoSenasagIcon(dlgDetalle.log)" size="lg" />
                    <div>Estado SENASAG: {{ getEstadoLabel(dlgDetalle.log) }}</div>
                  </div>
                </q-card-section>
              </q-card>
            </div>
          </div>
        </q-card-section>

        <q-separator />
        <q-card-actions align="right">
          <q-btn flat label="Cerrar" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import moment from 'moment'

export default {
  name: 'HistorialTransportesPage',
  data () {
    return {
      loading: false,
      historial: [],
      columns: [
        { name: 'id', label: 'ID', align: 'left', field: 'id', sortable: true, style: 'width: 70px' },
        { name: 'transporte', label: 'Vehículo', align: 'left', style: 'width: 180px' },
        { name: 'origen_destino', label: 'Ruta', align: 'left', style: 'width: 250px' },
        { name: 'fecha_hora_salida', label: 'Fecha Salida', align: 'left', field: 'fecha_hora_salida', sortable: true, format: val => moment(val).format('DD/MM/YYYY HH:mm') },
        { name: 'distancia_km', label: 'Distancia', align: 'center', field: 'distancia_km', format: val => val ? `${val} km` : '--', style: 'width: 100px' },
        { name: 'temperatura', label: 'Temperatura', align: 'center', style: 'width: 150px' },
        { name: 'tiempo', label: 'Duración', align: 'center', style: 'width: 120px' },
        { name: 'estado', label: 'Estado', align: 'center', style: 'width: 150px' },
        { name: 'actions', label: 'Acciones', align: 'center', style: 'width: 100px' },
      ],
      pagination: {
        rowsPerPage: 15,
        page: 1
      },

      // Filtros
      filtros: {
        tipo_viaje: null,
        fecha_inicio: '',
        fecha_fin: '',
        transporte_id: null,
        con_alertas: null
      },

      transportesOptions: [],
      tipoViajeOptions: [
        { label: 'Todos', value: null },
        { label: 'Entrada (Acopios)', value: 'entrada' },
        { label: 'Salida (Ventas)', value: 'salida' },
      ],
      estadoOptions: [
        { label: 'Todos', value: null },
        { label: 'Conforme', value: 'conforme' },
        { label: 'Con Alertas', value: 'alertas' },
      ],

      // Detalle
      dlgDetalle: { open: false, log: null }
    }
  },
  computed: {
    historialFiltrado() {
      if (!this.filtros.tipo_viaje) {
        return this.historial
      }
      
      // Nota: actualmente solo se carga historial de ENTRADA (acopio_transporte_log)
      // Para implementar completo se necesitaría un endpoint unificado que incluya también ventas
      if (this.filtros.tipo_viaje === 'entrada') {
        return this.historial // Todos los registros actuales son de entrada
      } else if (this.filtros.tipo_viaje === 'salida') {
        return [] // No tenemos datos de salida en este endpoint
      }
      
      return this.historial
    }
  },
  mounted () {
    this.cargarTransportes()
    this.cargarHistorial()
  },
  methods: {
    async cargarHistorial () {
      this.loading = true
      try {
        const params = {}
        if (this.filtros.fecha_inicio) params.fecha_inicio = this.filtros.fecha_inicio
        if (this.filtros.fecha_fin) params.fecha_fin = this.filtros.fecha_fin
        if (this.filtros.transporte_id) params.transporte_id = this.filtros.transporte_id
        if (this.filtros.con_alertas === 'alertas') params.con_alertas = 1

        const { data } = await this.$axios.get('/transporte-logs-alertas', { params })
        this.historial = Array.isArray(data) ? data : (data.data || [])
      } catch (e) {
        this.$q.notify({ type: 'negative', message: 'Error al cargar historial' })
      } finally {
        this.loading = false
      }
    },

    async cargarTransportes () {
      try {
        const { data } = await this.$axios.get('/transportes')
        const transportes = Array.isArray(data) ? data : (data.data || [])
        this.transportesOptions = transportes.map(t => ({
          id: t.id,
          label: `${t.empresa} - ${t.placa}`
        }))
      } catch (e) {
        console.error('Error al cargar transportes:', e)
      }
    },

    limpiarFiltros () {
      this.filtros = {
        fecha_inicio: '',
        fecha_fin: '',
        transporte_id: null,
        con_alertas: null
      }
      this.cargarHistorial()
    },

    onRequest (props) {
      this.pagination = props.pagination
      this.cargarHistorial()
    },

    async verDetalle (log) {
      try {
        const { data } = await this.$axios.get(`/transporte-logs/${log.id}`)
        this.dlgDetalle = { open: true, log: data.data || data }
      } catch (e) {
        this.$q.notify({ type: 'negative', message: 'Error al cargar detalle' })
      }
    },

    getEstadoColor (row) {
      if (row.alerta_temperatura || row.alerta_tiempo) return 'negative'
      if (!row.temperatura_llegada || !row.tiempo_transporte_horas) return 'grey'
      return 'positive'
    },

    getEstadoLabel (row) {
      if (row.alerta_temperatura || row.alerta_tiempo) return 'NO CONFORME'
      if (!row.temperatura_llegada || !row.tiempo_transporte_horas) return 'SIN DATOS'
      return 'CONFORME'
    },

    getEstadoSenasagClass (log) {
      const estado = this.getEstadoLabel(log)
      if (estado === 'CONFORME') return 'bg-positive'
      if (estado === 'NO CONFORME') return 'bg-negative'
      return 'bg-grey'
    },

    getEstadoSenasagIcon (log) {
      const estado = this.getEstadoLabel(log)
      if (estado === 'CONFORME') return 'check_circle'
      if (estado === 'NO CONFORME') return 'error'
      return 'help'
    },

    getColorCondicion (condicion) {
      if (!condicion) return 'grey'
      if (condicion === 'EXCELENTE' || condicion === 'BUENAS' || condicion === 'BUENO') return 'positive'
      if (condicion === 'REGULARES' || condicion === 'REGULAR') return 'orange'
      return 'negative'
    },

    formatearFecha (fecha) {
      return fecha ? moment(fecha).format('DD/MM/YYYY HH:mm') : '--'
    }
  }
}
</script>

<style scoped>
.q-card {
  border-radius: 8px;
}
</style>
