<template>
  <q-page class="q-pa-md">
    <!-- Encabezado -->
    <div class="row q-mb-md items-center">
      <div class="col-12 col-md-8">
        <div class="text-h5 text-weight-bold">Dashboard de Transportes</div>
        <div class="text-caption text-grey-7">Análisis y estadísticas de cumplimiento SENASAG</div>
      </div>
      <div class="col-12 col-md-4 text-right">
        <q-btn-group>
          <q-btn color="primary" icon="refresh" label="Actualizar" no-caps :loading="loading" @click="cargarDatos"/>
          <q-btn color="info" icon="filter_list" label="Filtrar" no-caps @click="mostrarFiltros = !mostrarFiltros"/>
        </q-btn-group>
      </div>
    </div>

    <!-- Filtros de fecha -->
    <q-slide-transition>
      <q-card flat bordered class="q-mb-md" v-show="mostrarFiltros">
        <q-card-section>
          <div class="row q-col-gutter-md items-center">
            <div class="col-12 col-md-4">
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
            <div class="col-12 col-md-4">
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
            <div class="col-12 col-md-4">
              <q-btn unelevated color="primary" label="Aplicar Filtros" icon="search" @click="cargarDatos" class="full-width"/>
            </div>
          </div>
        </q-card-section>
      </q-card>
    </q-slide-transition>

    <!-- Tarjetas de métricas principales -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-sm-6 col-md-3">
        <q-card flat bordered>
          <q-card-section class="bg-primary text-white">
            <div class="row items-center">
              <div class="col">
                <div class="text-h4">{{ estadisticas.total_transportes || 0 }}</div>
                <div class="text-caption">Total Transportes</div>
              </div>
              <div class="col-auto">
                <q-icon name="local_shipping" size="48px" />
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card flat bordered>
          <q-card-section class="bg-positive text-white">
            <div class="row items-center">
              <div class="col">
                <div class="text-h4">{{ estadisticas.porcentaje_conforme?.toFixed(1) || 0 }}%</div>
                <div class="text-caption">Cumplimiento SENASAG</div>
              </div>
              <div class="col-auto">
                <q-icon name="verified" size="48px" />
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card flat bordered>
          <q-card-section class="bg-orange text-white">
            <div class="row items-center">
              <div class="col">
                <div class="text-h4">{{ estadisticas.total_alertas || 0 }}</div>
                <div class="text-caption">Alertas Activas</div>
              </div>
              <div class="col-auto">
                <q-icon name="warning" size="48px" />
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card flat bordered>
          <q-card-section class="bg-info text-white">
            <div class="row items-center">
              <div class="col">
                <div class="text-h4">{{ estadisticas.temperatura_promedio_llegada?.toFixed(1) || 0 }}°C</div>
                <div class="text-caption">Temperatura Promedio</div>
              </div>
              <div class="col-auto">
                <q-icon name="thermostat" size="48px" />
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Tarjetas de análisis detallado -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-md-6">
        <q-card flat bordered>
          <q-card-section>
            <div class="text-h6 q-mb-md">
              <q-icon name="schedule" color="orange" />
              Análisis de Tiempo de Transporte
            </div>
            <div class="row q-col-gutter-sm">
              <div class="col-6">
                <q-card flat class="bg-grey-2">
                  <q-card-section>
                    <div class="text-caption text-grey-7">Duración Promedio</div>
                    <div class="text-h5">{{ estadisticas.duracion_promedio_horas?.toFixed(1) || 0 }} hrs</div>
                  </q-card-section>
                </q-card>
              </div>
              <div class="col-6">
                <q-card flat class="bg-grey-2">
                  <q-card-section>
                    <div class="text-caption text-grey-7">Alertas de Tiempo</div>
                    <div class="text-h5 text-orange">{{ estadisticas.con_alerta_tiempo || 0 }}</div>
                  </q-card-section>
                </q-card>
              </div>
              <div class="col-12">
                <q-linear-progress 
                  :value="tiempoCumplimientoPercentage" 
                  size="25px" 
                  :color="tiempoCumplimientoPercentage > 90 ? 'positive' : tiempoCumplimientoPercentage > 70 ? 'orange' : 'negative'"
                  class="q-mt-md"
                >
                  <div class="absolute-full flex flex-center">
                    <q-badge color="white" text-color="black" :label="`${tiempoCumplimientoPercentage.toFixed(0)}% sin alertas de tiempo`" />
                  </div>
                </q-linear-progress>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-md-6">
        <q-card flat bordered>
          <q-card-section>
            <div class="text-h6 q-mb-md">
              <q-icon name="thermostat" color="red" />
              Análisis de Temperatura
            </div>
            <div class="row q-col-gutter-sm">
              <div class="col-6">
                <q-card flat class="bg-grey-2">
                  <q-card-section>
                    <div class="text-caption text-grey-7">Temperatura Máxima</div>
                    <div class="text-h5" :class="estadisticas.temperatura_maxima_registrada > 35 ? 'text-red' : ''">
                      {{ estadisticas.temperatura_maxima_registrada?.toFixed(1) || 0 }}°C
                    </div>
                  </q-card-section>
                </q-card>
              </div>
              <div class="col-6">
                <q-card flat class="bg-grey-2">
                  <q-card-section>
                    <div class="text-caption text-grey-7">Alertas de Temperatura</div>
                    <div class="text-h5 text-red">{{ estadisticas.con_alerta_temperatura || 0 }}</div>
                  </q-card-section>
                </q-card>
              </div>
              <div class="col-12">
                <q-linear-progress 
                  :value="temperaturaCumplimientoPercentage" 
                  size="25px" 
                  :color="temperaturaCumplimientoPercentage > 90 ? 'positive' : temperaturaCumplimientoPercentage > 70 ? 'orange' : 'negative'"
                  class="q-mt-md"
                >
                  <div class="absolute-full flex flex-center">
                    <q-badge color="white" text-color="black" :label="`${temperaturaCumplimientoPercentage.toFixed(0)}% sin alertas de temperatura`" />
                  </div>
                </q-linear-progress>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Tarjeta de distancia -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12">
        <q-card flat bordered>
          <q-card-section>
            <div class="text-h6 q-mb-md">
              <q-icon name="place" color="info" />
              Análisis de Distancias
            </div>
            <div class="row q-col-gutter-sm">
              <div class="col-12 col-md-4">
                <q-card flat class="bg-info text-white">
                  <q-card-section>
                    <div class="text-caption">Distancia Total Recorrida</div>
                    <div class="text-h4">{{ estadisticas.distancia_total_km?.toFixed(2) || 0 }} km</div>
                  </q-card-section>
                </q-card>
              </div>
              <div class="col-12 col-md-4">
                <q-card flat class="bg-grey-2">
                  <q-card-section>
                    <div class="text-caption text-grey-7">Distancia Promedio por Viaje</div>
                    <div class="text-h5">{{ distanciaPromedio.toFixed(2) }} km</div>
                  </q-card-section>
                </q-card>
              </div>
              <div class="col-12 col-md-4">
                <q-card flat class="bg-grey-2">
                  <q-card-section>
                    <div class="text-caption text-grey-7">Velocidad Promedio</div>
                    <div class="text-h5">{{ velocidadPromedio.toFixed(1) }} km/h</div>
                  </q-card-section>
                </q-card>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Últimas alertas -->
    <div class="row q-col-gutter-md">
      <div class="col-12">
        <q-card flat bordered>
          <q-card-section class="bg-orange-2">
            <div class="text-h6">
              <q-icon name="warning" color="orange" />
              Últimas Alertas SENASAG
            </div>
          </q-card-section>
          <q-separator />
          <q-card-section v-if="ultimasAlertas.length > 0">
            <q-list separator>
              <q-item v-for="alerta in ultimasAlertas" :key="alerta.id">
                <q-item-section avatar>
                  <q-avatar :color="alerta.alerta_temperatura ? 'red' : 'orange'" text-color="white" :icon="alerta.alerta_temperatura ? 'thermostat' : 'schedule'" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>{{ alerta.transporte?.empresa }} - {{ alerta.transporte?.placa }}</q-item-label>
                  <q-item-label caption>
                    {{ formatearFecha(alerta.fecha_hora_salida) }} | 
                    {{ alerta.lugar_origen }} → {{ alerta.lugar_destino || 'Planta' }}
                  </q-item-label>
                </q-item-section>
                <q-item-section side>
                  <div>
                    <q-chip v-if="alerta.alerta_temperatura" dense color="red" text-color="white" icon="thermostat">
                      {{ alerta.temperatura_llegada }}°C
                    </q-chip>
                    <q-chip v-if="alerta.alerta_tiempo" dense color="orange" text-color="white" icon="schedule">
                      {{ alerta.tiempo_transporte_horas }} hrs
                    </q-chip>
                  </div>
                </q-item-section>
                <q-item-section side>
                  <q-btn flat dense color="info" icon="visibility" :to="`/historial-transportes`">
                    <q-tooltip>Ver en historial</q-tooltip>
                  </q-btn>
                </q-item-section>
              </q-item>
            </q-list>
          </q-card-section>
          <q-card-section v-else>
            <div class="text-center text-grey-6">
              <q-icon name="check_circle" size="lg" color="positive" />
              <div>No hay alertas recientes</div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script>
import moment from 'moment'

export default {
  name: 'DashboardTransportesPage',
  data () {
    return {
      loading: false,
      mostrarFiltros: false,
      
      filtros: {
        fecha_inicio: '',
        fecha_fin: ''
      },

      estadisticas: {
        total_transportes: 0,
        con_alerta_temperatura: 0,
        con_alerta_tiempo: 0,
        total_alertas: 0,
        porcentaje_conforme: 100,
        duracion_promedio_horas: 0,
        temperatura_promedio_llegada: 0,
        temperatura_maxima_registrada: 0,
        distancia_total_km: 0
      },

      ultimasAlertas: []
    }
  },
  computed: {
    tiempoCumplimientoPercentage () {
      if (!this.estadisticas.total_transportes) return 100
      return ((this.estadisticas.total_transportes - (this.estadisticas.con_alerta_tiempo || 0)) / this.estadisticas.total_transportes) * 100
    },
    temperaturaCumplimientoPercentage () {
      if (!this.estadisticas.total_transportes) return 100
      return ((this.estadisticas.total_transportes - (this.estadisticas.con_alerta_temperatura || 0)) / this.estadisticas.total_transportes) * 100
    },
    distanciaPromedio () {
      if (!this.estadisticas.total_transportes) return 0
      return this.estadisticas.distancia_total_km / this.estadisticas.total_transportes
    },
    velocidadPromedio () {
      if (!this.estadisticas.duracion_promedio_horas || !this.distanciaPromedio) return 0
      return this.distanciaPromedio / this.estadisticas.duracion_promedio_horas
    }
  },
  mounted () {
    // Establecer últimos 30 días por defecto
    const hoy = moment()
    const hace30Dias = moment().subtract(30, 'days')
    this.filtros.fecha_fin = hoy.format('YYYY-MM-DD')
    this.filtros.fecha_inicio = hace30Dias.format('YYYY-MM-DD')
    
    this.cargarDatos()
  },
  methods: {
    async cargarDatos () {
      this.loading = true
      try {
        await Promise.all([
          this.cargarEstadisticas(),
          this.cargarAlertas()
        ])
      } catch (e) {
        console.error('Error al cargar datos:', e)
      } finally {
        this.loading = false
      }
    },

    async cargarEstadisticas () {
      try {
        const params = {}
        if (this.filtros.fecha_inicio) params.fecha_inicio = this.filtros.fecha_inicio
        if (this.filtros.fecha_fin) params.fecha_fin = this.filtros.fecha_fin

        const { data } = await this.$axios.get('/transporte-logs-estadisticas', { params })
        
        this.estadisticas = {
          total_transportes: data.total_transportes || 0,
          con_alerta_temperatura: data.con_alerta_temperatura || 0,
          con_alerta_tiempo: data.con_alerta_tiempo || 0,
          total_alertas: (data.con_alerta_temperatura || 0) + (data.con_alerta_tiempo || 0),
          porcentaje_conforme: data.porcentaje_conforme || 100,
          duracion_promedio_horas: data.duracion_promedio_horas || 0,
          temperatura_promedio_llegada: data.temperatura_promedio_llegada || 0,
          temperatura_maxima_registrada: data.temperatura_maxima_registrada || 0,
          distancia_total_km: data.distancia_total_km || 0
        }
      } catch (e) {
        this.$q.notify({ type: 'negative', message: 'Error al cargar estadísticas' })
      }
    },

    async cargarAlertas () {
      try {
        const params = {}
        if (this.filtros.fecha_inicio) params.fecha_inicio = this.filtros.fecha_inicio
        if (this.filtros.fecha_fin) params.fecha_fin = this.filtros.fecha_fin

        const { data } = await this.$axios.get('/transporte-logs-alertas', { params })
        this.ultimasAlertas = (Array.isArray(data) ? data : (data.data || [])).slice(0, 5)
      } catch (e) {
        console.error('Error al cargar alertas:', e)
      }
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
