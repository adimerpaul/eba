<template>
  <div v-if="loading" class="text-center q-pa-md">
    <q-spinner color="primary" size="3em" />
  </div>
  
  <div v-else class="row q-col-gutter-md">
    <!-- TRAZABILIDAD ATRÁS -->
    <div class="col-12 col-md-4">
      <q-card flat bordered class="full-height">
        <q-card-section class="bg-green-1">
          <div class="row items-center">
            <q-icon name="agriculture" size="md" color="positive" class="q-mr-sm" />
            <div>
              <div class="text-subtitle1 text-weight-bold">Trazabilidad Atrás</div>
              <div class="text-caption text-grey-7">Producción Primaria</div>
            </div>
          </div>
        </q-card-section>

        <q-separator />

        <q-card-section>
          <div class="q-gutter-sm">
            <div class="row items-center">
              <div class="col">
                <div class="text-caption text-grey-7">Productores Activos</div>
                <div class="text-h6">{{ capacidad?.total_productores || 0 }}</div>
              </div>
              <div class="col">
                <div class="text-caption text-grey-7">Colmenas</div>
                <div class="text-h6">{{ capacidad?.total_colmenas || 0 }}</div>
              </div>
            </div>

            <q-separator />

            <div class="row items-center">
              <div class="col">
                <div class="text-caption text-grey-7">Capacidad Teórica</div>
                <div class="text-body2">{{ formatNumber(capacidad?.capacidad_teorica) }} kg</div>
              </div>
              <div class="col">
                <div class="text-caption text-grey-7">Producción Real</div>
                <div class="text-body2 text-positive">{{ formatNumber(capacidad?.produccion_real) }} kg</div>
              </div>
            </div>

            <q-separator />

            <div>
              <div class="text-caption text-grey-7">Eficiencia Productiva</div>
              <div class="row items-center q-mt-xs">
                <div class="col">
                  <q-linear-progress 
                    :value="(capacidad?.eficiencia || 0) / 100" 
                    color="positive" 
                    size="20px"
                  />
                </div>
                <div class="col-auto q-ml-sm">
                  <q-badge color="positive">{{ capacidad?.eficiencia || 0 }}%</q-badge>
                </div>
              </div>
            </div>

            <q-separator />

            <div class="text-center">
              <q-icon name="bug_report" size="sm" color="orange" />
              <span class="text-caption text-grey-7 q-ml-xs">
                {{ senasag?.plagas?.total_registros || 0 }} registros de plagas
              </span>
            </div>
          </div>
        </q-card-section>
      </q-card>
    </div>

    <!-- TRAZABILIDAD EN PROCESO -->
    <div class="col-12 col-md-4">
      <q-card flat bordered class="full-height">
        <q-card-section class="bg-blue-1">
          <div class="row items-center">
            <q-icon name="factory" size="md" color="primary" class="q-mr-sm" />
            <div>
              <div class="text-subtitle1 text-weight-bold">Trazabilidad En Proceso</div>
              <div class="text-caption text-grey-7">Procesamiento</div>
            </div>
          </div>
        </q-card-section>

        <q-separator />

        <q-card-section>
          <div class="q-gutter-sm">
            <div class="row items-center">
              <div class="col">
                <div class="text-caption text-grey-7">Acopios Totales</div>
                <div class="text-h6">{{ recepcion?.total_acopios || 0 }}</div>
              </div>
              <div class="col">
                <div class="text-caption text-grey-7">Validados</div>
                <div class="text-h6 text-positive">{{ recepcion?.acopios_validados || 0 }}</div>
              </div>
            </div>

            <q-separator />

            <div class="row items-center">
              <div class="col">
                <div class="text-caption text-grey-7">Procesos Finalizados</div>
                <div class="text-body2">{{ procesamiento?.procesos_finalizados || 0 }}</div>
              </div>
              <div class="col">
                <div class="text-caption text-grey-7">Merma Promedio</div>
                <div class="text-body2" :class="procesamiento?.merma_promedio > 5 ? 'text-warning' : 'text-positive'">
                  {{ procesamiento?.merma_promedio || 0 }}%
                </div>
              </div>
            </div>

            <q-separator />

            <div class="row items-center">
              <div class="col">
                <div class="text-caption text-grey-7">Lotes Generados</div>
                <div class="text-body2">{{ lotes?.lotes_generados || 0 }}</div>
              </div>
              <div class="col">
                <div class="text-caption text-grey-7">Stock Actual</div>
                <div class="text-body2 text-info">{{ formatNumber(lotes?.stock_actual) }} kg</div>
              </div>
            </div>

            <q-separator />

            <div class="text-center">
              <q-icon 
                :name="lotes?.tanques_criticos > 0 ? 'warning' : 'check_circle'" 
                size="sm" 
                :color="lotes?.tanques_criticos > 0 ? 'warning' : 'positive'" 
              />
              <span class="text-caption text-grey-7 q-ml-xs">
                {{ lotes?.tanques_criticos || 0 }} tanques críticos
              </span>
            </div>
          </div>
        </q-card-section>
      </q-card>
    </div>

    <!-- TRAZABILIDAD ADELANTE -->
    <div class="col-12 col-md-4">
      <q-card flat bordered class="full-height">
        <q-card-section class="bg-orange-1">
          <div class="row items-center">
            <q-icon name="local_shipping" size="md" color="orange" class="q-mr-sm" />
            <div>
              <div class="text-subtitle1 text-weight-bold">Trazabilidad Adelante</div>
              <div class="text-caption text-grey-7">Comercialización</div>
            </div>
          </div>
        </q-card-section>

        <q-separator />

        <q-card-section>
          <div class="q-gutter-sm text-center q-pa-md">
            <q-icon name="info" size="lg" color="info" />
            <div class="text-body2 text-grey-7 q-mt-md">
              La trazabilidad hacia adelante se visualiza en el módulo de ventas.
            </div>
            <div class="text-caption text-grey-6 q-mt-sm">
              Incluye registro de ventas, destinos y seguimiento de lotes comercializados.
            </div>
            <q-btn
              label="Ver Ventas"
              icon="point_of_sale"
              color="orange"
              outline
              no-caps
              size="sm"
              class="q-mt-md"
              @click="$router.push('/ventas/dashboard')"
            />
            <div class="q-mt-md q-pt-md" style="border-top: 1px dashed #ccc">
              <div class="text-caption text-grey-6">
                <q-icon name="search" size="xs" />
                Búsqueda de lotes por código QR disponible en el dashboard completo
              </div>
            </div>
          </div>
        </q-card-section>
      </q-card>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DashboardTrazabilidadResumen',
  props: {
    gestion: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      loading: false,
      capacidad: null,
      senasag: null,
      recepcion: null,
      procesamiento: null,
      lotes: null
    }
  },
  mounted() {
    this.cargarDatos()
  },
  methods: {
    formatNumber(value) {
      return Number(value || 0).toLocaleString('es-BO', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
      })
    },

    async cargarDatos() {
      this.loading = true
      try {
        const requests = [
          this.$axios.get('/dashboard-trazabilidad/capacidad-productiva', { params: { gestion: this.gestion } })
            .catch(err => {
              console.error('Error capacidad:', err)
              return { data: { resumen: null } }
            }),
          this.$axios.get('/dashboard-trazabilidad/senasag', { params: { gestion: this.gestion } })
            .catch(err => {
              console.error('Error senasag:', err)
              return { data: null }
            }),
          this.$axios.get('/dashboard-trazabilidad/recepcion-rechazos', { params: { gestion: this.gestion } })
            .catch(err => {
              console.error('Error recepcion:', err)
              return { data: { resumen: null } }
            }),
          this.$axios.get('/dashboard-trazabilidad/procesamiento-merma', { params: { gestion: this.gestion } })
            .catch(err => {
              console.error('Error procesamiento:', err)
              return { data: { resumen: null } }
            }),
          this.$axios.get('/dashboard-trazabilidad/lotes-almacenamiento', { params: { gestion: this.gestion } })
            .catch(err => {
              console.error('Error lotes:', err)
              return { data: { resumen: null } }
            })
        ]

        const [capacidadRes, senasagRes, recepcionRes, procesamientoRes, lotesRes] = await Promise.all(requests)

        this.capacidad = capacidadRes.data.resumen
        this.senasag = senasagRes.data
        this.recepcion = recepcionRes.data.resumen
        this.procesamiento = procesamientoRes.data.resumen
        this.lotes = lotesRes.data.resumen
      } catch (error) {
        console.error('Error general:', error)
      } finally {
        this.loading = false
      }
    }
  },
  watch: {
    gestion() {
      this.cargarDatos()
    }
  }
}
</script>
