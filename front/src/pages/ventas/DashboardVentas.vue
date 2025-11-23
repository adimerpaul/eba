<template>
  <q-page class="q-pa-md">
    <div class="row items-center q-mb-md">
      <div class="col">
        <div class="text-h5">Dashboard de Comercialización</div>
        <div class="text-caption text-grey-7">Indicadores y análisis de ventas</div>
      </div>
      <div class="col-auto">
        <q-btn-group outline>
          <q-btn outline color="primary" icon="event" no-caps>
            <q-popup-proxy>
              <q-date v-model="rangoFechas" range :mask="'YYYY-MM-DD'">
                <div class="row items-center justify-end q-gutter-sm">
                  <q-btn label="Cancelar" flat v-close-popup />
                  <q-btn label="Aplicar" color="primary" flat @click="aplicarFiltro" v-close-popup />
                </div>
              </q-date>
            </q-popup-proxy>
            <q-tooltip>Seleccionar rango de fechas</q-tooltip>
          </q-btn>
          <q-btn outline color="primary" icon="refresh" @click="cargarDatos" :loading="loading" no-caps>
            Actualizar
          </q-btn>
        </q-btn-group>
      </div>
    </div>

    <div v-if="!loading && estadisticas" class="q-gutter-md">
      <div class="row q-col-gutter-md">
        <div class="col-12 col-sm-6 col-md-3">
          <q-card flat bordered>
            <q-card-section class="text-center">
              <q-icon name="shopping_cart" size="md" color="primary" />
              <div class="text-h4 text-primary q-mt-sm">{{ formatNumber(estadisticas.resumen.total_ventas) }}</div>
              <div class="text-caption text-grey-7">Total Ventas</div>
            </q-card-section>
          </q-card>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
          <q-card flat bordered>
            <q-card-section class="text-center">
              <q-icon name="payments" size="md" color="positive" />
              <div class="text-h4 text-positive q-mt-sm">Bs {{ formatNumber(estadisticas.resumen.monto_total) }}</div>
              <div class="text-caption text-grey-7">Monto Total</div>
            </q-card-section>
          </q-card>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
          <q-card flat bordered>
            <q-card-section class="text-center">
              <q-icon name="scale" size="md" color="orange" />
              <div class="text-h4 text-orange q-mt-sm">{{ formatNumber(estadisticas.resumen.kg_vendidos) }}</div>
              <div class="text-caption text-grey-7">Kg Vendidos</div>
            </q-card-section>
          </q-card>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
          <q-card flat bordered>
            <q-card-section class="text-center">
              <q-icon name="trending_up" size="md" color="info" />
              <div class="text-h4 text-info q-mt-sm">Bs {{ formatNumber(estadisticas.resumen.promedio_venta) }}</div>
              <div class="text-caption text-grey-7">Promedio por Venta</div>
            </q-card-section>
          </q-card>
        </div>
      </div>

      <div class="row q-col-gutter-md">
        <div class="col-12 col-md-6">
          <q-card flat bordered>
            <q-card-section>
              <div class="text-h6 q-mb-md">Ventas por Cliente (Top 10)</div>
              <q-markup-table dense flat>
                <thead>
                  <tr>
                    <th class="text-left">Cliente</th>
                    <th class="text-center">Ventas</th>
                    <th class="text-right">Monto Total</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in estadisticas.ventas_por_cliente" :key="index">
                    <td class="text-left">{{ item.cliente }}</td>
                    <td class="text-center">{{ item.total_ventas }}</td>
                    <td class="text-right">Bs {{ formatNumber(item.monto_total) }}</td>
                  </tr>
                  <tr v-if="estadisticas.ventas_por_cliente.length === 0">
                    <td colspan="3" class="text-center text-grey-7">Sin datos</td>
                  </tr>
                </tbody>
              </q-markup-table>
            </q-card-section>
          </q-card>
        </div>

        <div class="col-12 col-md-6">
          <q-card flat bordered>
            <q-card-section>
              <div class="text-h6 q-mb-md">Productos Más Vendidos</div>
              <q-markup-table dense flat>
                <thead>
                  <tr>
                    <th class="text-left">Producto</th>
                    <th class="text-right">Kg</th>
                    <th class="text-center">Ventas</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in estadisticas.productos_mas_vendidos" :key="index">
                    <td class="text-left">{{ item.producto }}</td>
                    <td class="text-right">{{ formatNumber(item.cantidad_kg) }}</td>
                    <td class="text-center">{{ item.total_ventas }}</td>
                  </tr>
                  <tr v-if="estadisticas.productos_mas_vendidos.length === 0">
                    <td colspan="3" class="text-center text-grey-7">Sin datos</td>
                  </tr>
                </tbody>
              </q-markup-table>
            </q-card-section>
          </q-card>
        </div>
      </div>

      <div class="row q-col-gutter-md">
        <div class="col-12 col-md-8">
          <q-card flat bordered>
            <q-card-section>
              <div class="text-h6 q-mb-md">Evolución de Ventas (Últimos 6 Meses)</div>
              <div style="height: 300px;">
                <canvas ref="chartVentasMes"></canvas>
              </div>
            </q-card-section>
          </q-card>
        </div>

        <div class="col-12 col-md-4">
          <q-card flat bordered>
            <q-card-section>
              <div class="text-h6 q-mb-md">Lotes con Mayor Rotación</div>
              <q-list separator>
                <q-item v-for="(item, index) in estadisticas.lotes_rotacion.slice(0, 5)" :key="index">
                  <q-item-section>
                    <q-item-label>{{ item.codigo_lote }}</q-item-label>
                    <q-item-label caption>{{ formatNumber(item.cantidad_vendida_kg) }} kg en {{ item.numero_ventas }} ventas</q-item-label>
                  </q-item-section>
                  <q-item-section side>
                    <q-badge :color="index === 0 ? 'positive' : index === 1 ? 'orange' : 'grey'" :label="`#${index + 1}`" />
                  </q-item-section>
                </q-item>
                <q-item v-if="estadisticas.lotes_rotacion.length === 0">
                  <q-item-section class="text-center text-grey-7">
                    Sin datos
                  </q-item-section>
                </q-item>
              </q-list>
            </q-card-section>
          </q-card>
        </div>
      </div>

      <div class="row q-col-gutter-md">
        <div class="col-12 col-md-6">
          <q-card flat bordered>
            <q-card-section>
              <div class="text-h6 q-mb-md">Distribución por Cliente</div>
              <div style="height: 300px;">
                <canvas ref="chartClientePie"></canvas>
              </div>
            </q-card-section>
          </q-card>
        </div>

        <div class="col-12 col-md-6">
          <q-card flat bordered>
            <q-card-section>
              <div class="text-h6 q-mb-md">Productos por Volumen</div>
              <div style="height: 300px;">
                <canvas ref="chartProductosBar"></canvas>
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>

    <div v-else-if="loading" class="flex flex-center q-pa-xl">
      <q-spinner color="primary" size="3em" />
    </div>
  </q-page>
</template>

<script>
import { Chart, registerables } from 'chart.js'
import moment from 'moment'

Chart.register(...registerables)

export default {
  name: 'DashboardVentas',
  data() {
    return {
      loading: false,
      estadisticas: null,
      rangoFechas: {
        from: moment().startOf('month').format('YYYY-MM-DD'),
        to: moment().endOf('month').format('YYYY-MM-DD')
      },
      charts: {
        ventasMes: null,
        clientePie: null,
        productosBar: null
      }
    }
  },
  mounted() {
    this.cargarDatos()
  },
  beforeUnmount() {
    this.destruirGraficos()
  },
  methods: {
    formatNumber(value) {
      return Number(value || 0).toLocaleString('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      })
    },

    aplicarFiltro() {
      this.cargarDatos()
    },

    async cargarDatos() {
      this.loading = true
      try {
        const params = {
          fecha_inicio: this.rangoFechas.from,
          fecha_fin: this.rangoFechas.to
        }

        const { data } = await this.$axios.get('/ventas/estadisticas', { params })
        this.estadisticas = data

        this.$nextTick(() => {
          this.destruirGraficos()
          this.crearGraficos()
        })
      } catch (e) {
        this.$q.notify({
          type: 'negative',
          message: 'Error al cargar estadísticas'
        })
      } finally {
        this.loading = false
      }
    },

    destruirGraficos() {
      Object.values(this.charts).forEach(chart => {
        if (chart) chart.destroy()
      })
      this.charts = {
        ventasMes: null,
        clientePie: null,
        productosBar: null
      }
    },

    crearGraficos() {
      this.crearGraficoVentasMes()
      this.crearGraficoClientePie()
      this.crearGraficoProductosBar()
    },

    crearGraficoVentasMes() {
      if (!this.$refs.chartVentasMes) return

      const ctx = this.$refs.chartVentasMes.getContext('2d')
      const data = this.estadisticas.ventas_por_mes

      this.charts.ventasMes = new Chart(ctx, {
        type: 'line',
        data: {
          labels: data.map(item => moment(item.mes, 'YYYY-MM').format('MMM YYYY')),
          datasets: [
            {
              label: 'Ventas',
              data: data.map(item => item.total_ventas),
              borderColor: '#1976d2',
              backgroundColor: 'rgba(25, 118, 210, 0.1)',
              tension: 0.4,
              yAxisID: 'y'
            },
            {
              label: 'Monto (Bs)',
              data: data.map(item => item.monto_total),
              borderColor: '#4caf50',
              backgroundColor: 'rgba(76, 175, 80, 0.1)',
              tension: 0.4,
              yAxisID: 'y1'
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: {
            mode: 'index',
            intersect: false
          },
          plugins: {
            legend: {
              display: true,
              position: 'top'
            }
          },
          scales: {
            y: {
              type: 'linear',
              display: true,
              position: 'left',
              title: {
                display: true,
                text: 'Número de Ventas'
              }
            },
            y1: {
              type: 'linear',
              display: true,
              position: 'right',
              title: {
                display: true,
                text: 'Monto (Bs)'
              },
              grid: {
                drawOnChartArea: false
              }
            }
          }
        }
      })
    },

    crearGraficoClientePie() {
      if (!this.$refs.chartClientePie) return

      const ctx = this.$refs.chartClientePie.getContext('2d')
      const data = this.estadisticas.ventas_por_cliente.slice(0, 5)

      this.charts.clientePie = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: data.map(item => item.cliente),
          datasets: [{
            data: data.map(item => item.monto_total),
            backgroundColor: [
              '#1976d2',
              '#4caf50',
              '#ff9800',
              '#f44336',
              '#9c27b0'
            ]
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: true,
              position: 'bottom'
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  return context.label + ': Bs ' + context.parsed.toFixed(2)
                }
              }
            }
          }
        }
      })
    },

    crearGraficoProductosBar() {
      if (!this.$refs.chartProductosBar) return

      const ctx = this.$refs.chartProductosBar.getContext('2d')
      const data = this.estadisticas.productos_mas_vendidos.slice(0, 8)

      this.charts.productosBar = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: data.map(item => item.producto),
          datasets: [{
            label: 'Kg Vendidos',
            data: data.map(item => item.cantidad_kg),
            backgroundColor: '#ff9800'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          indexAxis: 'y',
          plugins: {
            legend: {
              display: false
            }
          },
          scales: {
            x: {
              beginAtZero: true,
              title: {
                display: true,
                text: 'Kilogramos'
              }
            }
          }
        }
      })
    }
  }
}
</script>

<style scoped>
.q-card {
  height: 100%;
}
</style>
