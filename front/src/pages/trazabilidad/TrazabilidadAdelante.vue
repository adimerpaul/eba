<template>
  <div class="q-gutter-md">
    <q-tabs
      v-model="subTab"
      dense
      class="text-grey"
      active-color="primary"
      indicator-color="primary"
      align="justify"
    >
      <q-tab name="ventas" label="Ventas y Distribución" icon="point_of_sale" />
      <q-tab name="busqueda" label="Buscador de Lotes" icon="search" />
    </q-tabs>

    <q-separator />

    <q-tab-panels v-model="subTab" animated>
      <q-tab-panel name="ventas">
        <dashboard-ventas ref="dashboardVentas" :gestion-prop="gestion" />

        <q-card flat bordered class="q-mt-md">
          <q-card-section>
            <q-banner class="bg-grey-2">
              <template v-slot:avatar>
                <q-icon name="info" color="info" />
              </template>
              <div class="text-subtitle1">Trazabilidad Hacia Adelante</div>
              El sistema actual registra ventas y destinos finales. El tracking GPS post-venta no está implementado.
              La trazabilidad se completa con el registro del comprador y lugar de destino en cada venta.
            </q-banner>
          </q-card-section>
        </q-card>
      </q-tab-panel>

      <q-tab-panel name="busqueda">
        <q-card flat bordered>
          <q-card-section>
            <div class="text-h6 q-mb-md">Búsqueda de Lotes y Trazabilidad Completa</div>

            <div class="row q-col-gutter-md q-mb-md">
              <div class="col-12 col-md-6">
                <q-input
                  v-model="busqueda.codigo_lote"
                  label="Código de Lote"
                  outlined
                  dense
                  @keyup.enter="buscarLote"
                >
                  <template v-slot:append>
                    <q-icon name="qr_code_scanner" class="cursor-pointer" @click="escanearQR" />
                  </template>
                </q-input>
              </div>
              <div class="col-12 col-md-6">
                <q-btn
                  label="Buscar"
                  icon="search"
                  color="primary"
                  @click="buscarLote"
                  :loading="buscando"
                />
                <q-btn
                  label="Limpiar"
                  icon="clear"
                  color="grey"
                  flat
                  class="q-ml-sm"
                  @click="limpiarBusqueda"
                />
              </div>
            </div>

            <q-separator class="q-my-md" />

            <div v-if="lote">
              <div class="text-h6 q-mb-md">Información del Lote: {{ lote.codigo_lote }}</div>

              <div class="row q-col-gutter-md q-mb-md">
                <div class="col-12 col-md-4">
                  <q-card flat bordered>
                    <q-card-section>
                      <div class="text-caption text-grey-7">Fecha Envasado</div>
                      <div class="text-h6">{{ formatFecha(lote.fecha_envasado) }}</div>
                    </q-card-section>
                  </q-card>
                </div>
                <div class="col-12 col-md-4">
                  <q-card flat bordered>
                    <q-card-section>
                      <div class="text-caption text-grey-7">Producto</div>
                      <div class="text-h6">{{ lote.producto }}</div>
                    </q-card-section>
                  </q-card>
                </div>
                <div class="col-12 col-md-4">
                  <q-card flat bordered>
                    <q-card-section>
                      <div class="text-caption text-grey-7">Cantidad Original</div>
                      <div class="text-h6">{{ formatNumber(lote.cantidad_kg) }} kg</div>
                    </q-card-section>
                  </q-card>
                </div>
              </div>

              <q-timeline color="primary" layout="comfortable">
                <q-timeline-entry
                  title="Cosecha y Recepción"
                  subtitle="Origen de la materia prima"
                  icon="agriculture"
                  color="positive"
                >
                  <div v-if="lote.productores && lote.productores.length > 0">
                    <div class="text-subtitle2 q-mb-sm">Productores Origen:</div>
                    <q-list dense bordered separator>
                      <q-item v-for="prod in lote.productores" :key="prod.nombre">
                        <q-item-section>
                          <q-item-label>{{ prod.nombre }}</q-item-label>
                          <q-item-label caption>
                            Apiarios: {{ prod.apiarios }} | 
                            Fecha cosecha: {{ formatFecha(prod.fecha_cosecha) }} |
                            Cantidad: {{ formatNumber(prod.cantidad_kg) }} kg
                          </q-item-label>
                        </q-item-section>
                      </q-item>
                    </q-list>
                  </div>
                  <div v-else class="text-grey-7">No hay información de productores</div>
                </q-timeline-entry>

                <q-timeline-entry
                  title="Procesamiento"
                  subtitle="Transformación y control de calidad"
                  icon="factory"
                  color="primary"
                >
                  <div v-if="lote.proceso">
                    <q-list dense bordered>
                      <q-item>
                        <q-item-section>
                          <q-item-label>Tanque</q-item-label>
                          <q-item-label caption>{{ lote.proceso.tanque }}</q-item-label>
                        </q-item-section>
                      </q-item>
                      <q-item>
                        <q-item-section>
                          <q-item-label>Método</q-item-label>
                          <q-item-label caption>{{ lote.proceso.metodo_proceso }}</q-item-label>
                        </q-item-section>
                      </q-item>
                      <q-item>
                        <q-item-section>
                          <q-item-label>Fecha Inicio</q-item-label>
                          <q-item-label caption>{{ formatFecha(lote.proceso.fecha_inicio) }}</q-item-label>
                        </q-item-section>
                      </q-item>
                      <q-item>
                        <q-item-section>
                          <q-item-label>Fecha Finalización</q-item-label>
                          <q-item-label caption>{{ formatFecha(lote.proceso.fecha_fin) }}</q-item-label>
                        </q-item-section>
                      </q-item>
                      <q-item>
                        <q-item-section>
                          <q-item-label>Temperatura</q-item-label>
                          <q-item-label caption>{{ lote.proceso.temperatura }}°C</q-item-label>
                        </q-item-section>
                      </q-item>
                      <q-item>
                        <q-item-section>
                          <q-item-label>Merma</q-item-label>
                          <q-item-label caption>{{ lote.proceso.merma_porcentaje }}%</q-item-label>
                        </q-item-section>
                      </q-item>
                    </q-list>
                  </div>
                  <div v-else class="text-grey-7">No hay información de procesamiento</div>
                </q-timeline-entry>

                <q-timeline-entry
                  title="Almacenamiento"
                  subtitle="Stock disponible"
                  icon="inventory_2"
                  color="info"
                >
                  <div>
                    <div class="text-body1">
                      <strong>Stock Actual:</strong> {{ formatNumber(lote.stock_actual) }} kg
                    </div>
                    <div class="text-body2 text-grey-7 q-mt-xs">
                      Vendido: {{ formatNumber(lote.cantidad_vendida) }} kg
                    </div>
                  </div>
                </q-timeline-entry>

                <q-timeline-entry
                  v-if="lote.ventas && lote.ventas.length > 0"
                  title="Ventas y Distribución"
                  subtitle="Destino final"
                  icon="local_shipping"
                  color="orange"
                >
                  <div class="text-subtitle2 q-mb-sm">Registro de Ventas:</div>
                  <q-list dense bordered separator>
                    <q-item v-for="venta in lote.ventas" :key="venta.venta_id">
                      <q-item-section>
                        <q-item-label>{{ venta.comprador }}</q-item-label>
                        <q-item-label caption>
                          Fecha: {{ formatFecha(venta.fecha_venta) }} | 
                          Cantidad: {{ formatNumber(venta.cantidad_kg) }} kg |
                          Monto: Bs. {{ formatNumber(venta.monto_total) }}
                        </q-item-label>
                        <q-item-label caption v-if="venta.lugar_destino">
                          Destino: {{ venta.lugar_destino }}
                        </q-item-label>
                      </q-item-section>
                    </q-item>
                  </q-list>
                  <q-banner class="bg-blue-1 q-mt-md">
                    <template v-slot:avatar>
                      <q-icon name="info" color="info" />
                    </template>
                    El tracking GPS post-venta no está implementado. La trazabilidad finaliza con el registro del destino.
                  </q-banner>
                </q-timeline-entry>

                <q-timeline-entry
                  v-else
                  title="Sin Ventas Registradas"
                  subtitle="Lote disponible en stock"
                  icon="pending"
                  color="grey"
                >
                  <div class="text-grey-7">Este lote no tiene ventas registradas aún</div>
                </q-timeline-entry>
              </q-timeline>
            </div>

            <div v-else-if="mensajeBusqueda" class="text-center q-pa-md">
              <q-icon name="search_off" size="4em" color="grey-5" />
              <div class="text-h6 text-grey-6 q-mt-md">{{ mensajeBusqueda }}</div>
            </div>
          </q-card-section>
        </q-card>
      </q-tab-panel>
    </q-tab-panels>
  </div>
</template>

<script>
import moment from 'moment'
import DashboardVentas from '../ventas/DashboardVentas.vue'

export default {
  name: 'TrazabilidadAdelante',
  components: {
    DashboardVentas
  },
  props: {
    gestion: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      subTab: 'ventas',
      busqueda: {
        codigo_lote: ''
      },
      buscando: false,
      lote: null,
      mensajeBusqueda: null
    }
  },
  methods: {
    formatNumber(value) {
      return Number(value || 0).toLocaleString('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      })
    },

    formatFecha(fecha) {
      if (!fecha) return 'N/A'
      return moment(fecha).format('DD/MM/YYYY')
    },

    cargarDatos() {
      if (this.$refs.dashboardVentas && this.subTab === 'ventas') {
        this.$refs.dashboardVentas.cargarDatos()
      }
    },

    async buscarLote() {
      if (!this.busqueda.codigo_lote.trim()) {
        this.$q.notify({
          type: 'warning',
          message: 'Ingrese un código de lote'
        })
        return
      }

      this.buscando = true
      this.lote = null
      this.mensajeBusqueda = null

      try {
        const { data } = await this.$axios.get('/lotes/trazabilidad', {
          params: { codigo_lote: this.busqueda.codigo_lote.trim() }
        })

        if (data) {
          this.lote = data
        } else {
          this.mensajeBusqueda = 'No se encontró el lote especificado'
        }
      } catch (error) {
        if (error.response && error.response.status === 404) {
          this.mensajeBusqueda = 'No se encontró el lote especificado'
        } else {
          this.$q.notify({
            type: 'negative',
            message: 'Error al buscar el lote'
          })
        }
      } finally {
        this.buscando = false
      }
    },

    limpiarBusqueda() {
      this.busqueda.codigo_lote = ''
      this.lote = null
      this.mensajeBusqueda = null
    },

    escanearQR() {
      this.$q.notify({
        type: 'info',
        message: 'Funcionalidad de escaneo QR en desarrollo'
      })
    }
  },
  watch: {
    gestion() {
      this.cargarDatos()
    },
    subTab() {
      if (this.subTab === 'ventas') {
        this.$nextTick(() => {
          this.cargarDatos()
        })
      }
    }
  }
}
</script>
