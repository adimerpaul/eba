<template>
  <q-page class="q-pa-md bg-grey-2">
    <div class="row q-col-gutter-md">

      <!-- Panel lateral de filtros / resumen -->
      <div class="col-12 col-md-3">
        <q-card flat bordered>
          <q-card-section>
            <div class="text-h6 text-primary">
              Resumen de Acopios
            </div>
            <div class="text-caption text-grey-7">
              Datos desde la tabla <b>acopio_cosechas</b>
            </div>
          </q-card-section>

          <q-separator />

          <q-card-section>
            <q-input
              v-model="gestion"
              type="number"
              label="Gestión"
              dense
              outlined
              class="q-mb-sm"
            />

            <q-btn
              color="primary"
              icon="refresh"
              label="Actualizar"
              dense
              no-caps
              :loading="loading"
              class="full-width q-mb-md"
              @click="fetchResumen"
            />

            <div class="text-subtitle2 q-mb-xs">
              Total acopiado {{ gestion }}
            </div>
            <div class="text-h5 text-weight-bold">
              {{ totalKgAno }} kg
            </div>

            <q-separator class="q-my-md" />

            <div class="text-caption text-grey-7">
              Productos en gráfico:
              <b>{{ resumenProducto.labels.length }}</b>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Gráfico mensual -->
      <div class="col-12 col-md-9">
        <q-card flat bordered>
          <q-card-section>
            <div class="row items-center">
              <div class="col-grow">
                <div class="text-subtitle1 text-weight-bold">
                  Acopio mensual (kg) - Gestión {{ gestion }}
                </div>
                <div class="text-caption text-grey-7">
                  Suma de <b>cantidad_kg</b> por mes
                </div>
              </div>
              <div class="col-auto">
                <q-badge color="primary" outline>
                  Total: {{ totalKgAno }} kg
                </q-badge>
              </div>
            </div>
          </q-card-section>

          <q-card-section>
            <div class="chart-container">
              <canvas ref="graficoMes"></canvas>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Gráfico por producto -->
      <div class="col-12 col-md-6">
        <q-card flat bordered>
          <q-card-section>
            <div class="text-subtitle1 text-weight-bold">
              Distribución por producto (kg)
            </div>
            <div class="text-caption text-grey-7">
              Suma de <b>cantidad_kg</b> por producto
              <span v-if="gestion"> en {{ gestion }}</span>
            </div>
          </q-card-section>

          <q-card-section>
            <div class="chart-container">
              <canvas ref="graficoProducto"></canvas>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Tabla simple de productos para referencia -->
      <div class="col-12 col-md-6">
        <q-card flat bordered>
          <q-card-section>
            <div class="text-subtitle1 text-weight-bold">
              Detalle por producto
            </div>
          </q-card-section>

          <q-card-section>
            <q-markup-table dense flat bordered>
              <thead>
              <tr>
                <th class="text-left">Producto</th>
                <th class="text-right">Kg acopiados</th>
              </tr>
              </thead>
              <tbody>
              <tr
                v-for="(label, index) in resumenProducto.labels"
                :key="index"
              >
                <td class="text-left">{{ label }}</td>
                <td class="text-right">
                  {{ (resumenProducto.data[index] ?? 0).toFixed(2) }}
                </td>
              </tr>
              <tr v-if="resumenProducto.labels.length">
                <td class="text-right"><strong>TOTAL</strong></td>
                <td class="text-right">
                  <strong>{{ totalKgProductos }} kg</strong>
                </td>
              </tr>
              </tbody>
            </q-markup-table>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script>
import { defineComponent } from 'vue';
import Chart from 'chart.js/auto';
import moment from 'moment';

export default defineComponent({
  name: 'IndexPage',

  data () {
    return {
      gestion: moment().format('YYYY'),
      loading: false,

      // datos crudos
      resumenMes: {
        labels: [],
        data: []
      },
      resumenProducto: {
        labels: [],
        data: []
      },

      // instancias de Chart.js
      chartMes: null,
      chartProducto: null
    };
  },

  computed: {
    totalKgAno () {
      if (!this.resumenMes.data) return '0.00';
      const total = this.resumenMes.data.reduce(
        (sum, v) => sum + (parseFloat(v) || 0),
        0
      );
      return total.toFixed(2);
    },
    totalKgProductos () {
      if (!this.resumenProducto.data) return '0.00';
      const total = this.resumenProducto.data.reduce(
        (sum, v) => sum + (parseFloat(v) || 0),
        0
      );
      return total.toFixed(2);
    }
  },

  mounted () {
    this.fetchResumen();
  },

  beforeUnmount () {
    if (this.chartMes) this.chartMes.destroy();
    if (this.chartProducto) this.chartProducto.destroy();
  },

  methods: {
    async fetchResumen () {
      this.loading = true;
      try {
        await Promise.all([
          this.fetchResumenMensual(),
          this.fetchResumenPorProducto()
        ]);
      } finally {
        this.loading = false;
      }
    },

    // === MENSUAL ===
    async fetchResumenMensual () {
      try {
        // acopiore2 -> resumenMensual
        const { data } = await this.$axios.get('acopiore2', {
          params: { year: this.gestion }
        });

        // el backend devuelve { labels: [...], data: [...], year: 2025 }
        this.resumenMes = {
          labels: data.labels || [],
          data: data.data || []
        };

        this.renderChartMes();
      } catch (e) {
        console.error(e);
        this.$alert?.error?.('Error al cargar resumen mensual de acopios');
      }
    },

    // === POR PRODUCTO ===
    async fetchResumenPorProducto () {
      try {
        // acopiore1 -> resumenPorProducto
        const { data } = await this.$axios.get('acopiore1', {
          params: { year: this.gestion }
        });

        // el backend devuelve { labels: [...], data: [...] }
        this.resumenProducto = {
          labels: data.labels || [],
          data: data.data || []
        };

        this.renderChartProducto();
      } catch (e) {
        console.error(e);
        this.$alert?.error?.('Error al cargar resumen por producto');
      }
    },

    renderChartMes () {
      if (!this.$refs.graficoMes) {
        console.error('Canvas graficoMes no montado aún');
        return;
      }

      if (this.chartMes) {
        this.chartMes.destroy();
      }

      this.chartMes = new Chart(this.$refs.graficoMes, {
        type: 'bar',
        data: {
          labels: this.resumenMes.labels,
          datasets: [
            {
              label: 'Kg acopiados',
              data: this.resumenMes.data,
              backgroundColor: 'rgba(54, 162, 235, 0.7)',
              borderWidth: 1
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              callbacks: {
                label: ctx => `${ctx.parsed.y} kg`
              }
            },
            title: {
              display: false
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: 'Kg'
              }
            }
          }
        }
      });
    },

    renderChartProducto () {
      if (!this.$refs.graficoProducto) {
        console.error('Canvas graficoProducto no montado aún');
        return;
      }

      if (this.chartProducto) {
        this.chartProducto.destroy();
      }

      this.chartProducto = new Chart(this.$refs.graficoProducto, {
        type: 'doughnut',
        data: {
          labels: this.resumenProducto.labels,
          datasets: [
            {
              label: 'Kg acopiados',
              data: this.resumenProducto.data,
              backgroundColor: [
                'rgba(54, 162, 235, 0.7)',
                'rgba(255, 99, 132, 0.7)',
                'rgba(255, 206, 86, 0.7)',
                'rgba(75, 192, 192, 0.7)',
                'rgba(153, 102, 255, 0.7)',
                'rgba(255, 159, 64, 0.7)'
              ],
              borderWidth: 1
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'right'
            },
            tooltip: {
              callbacks: {
                label: ctx => {
                  const valor = ctx.parsed;
                  const total = this.resumenProducto.data.reduce(
                    (sum, v) => sum + (parseFloat(v) || 0),
                    0
                  );
                  const porcentaje = total > 0
                    ? ((valor / total) * 100).toFixed(1)
                    : 0;
                  return `${ctx.label}: ${valor.toFixed(2)} kg (${porcentaje}%)`;
                }
              }
            },
            title: {
              display: false
            }
          }
        }
      });
    }
  }
});
</script>


<style scoped>
.chart-container {
  width: 100%;
  max-width: 800px;
  margin: 0 auto;
  min-height: 260px;
}
.chart-container canvas {
  width: 100% !important;
  height: 100% !important;
}
</style>
