<template>
  <div class="q-gutter-md">
    <q-card flat bordered>
      <q-card-section>
        <div class="text-h6">Recepción y Rechazos</div>
        <div class="text-caption text-grey-7">
          Control de acopio, validación y motivos de rechazo
        </div>
      </q-card-section>

      <q-separator />

      <q-card-section v-if="loading">
        <div class="text-center">
          <q-spinner color="primary" size="3em" />
        </div>
      </q-card-section>

      <q-card-section v-else-if="recepcion">
        <div class="row q-col-gutter-md q-mb-md">
          <div class="col-12 col-sm-6 col-md-3">
            <q-card flat bordered>
              <q-card-section class="text-center">
                <q-icon name="inbox" size="md" color="primary" />
                <div class="text-h4 text-primary q-mt-sm">{{ recepcion.total_acopios }}</div>
                <div class="text-caption text-grey-7">Total Acopios</div>
                <div class="text-body2 q-mt-xs">{{ formatNumber(recepcion.cantidad_total) }} kg</div>
              </q-card-section>
            </q-card>
          </div>

          <div class="col-12 col-sm-6 col-md-3">
            <q-card flat bordered>
              <q-card-section class="text-center">
                <q-icon name="check_circle" size="md" color="positive" />
                <div class="text-h4 text-positive q-mt-sm">{{ recepcion.acopios_validados }}</div>
                <div class="text-caption text-grey-7">Validados</div>
                <div class="text-body2 q-mt-xs">{{ formatNumber(recepcion.cantidad_validada) }} kg</div>
              </q-card-section>
            </q-card>
          </div>

          <div class="col-12 col-sm-6 col-md-3">
            <q-card flat bordered>
              <q-card-section class="text-center">
                <q-icon name="cancel" size="md" color="negative" />
                <div class="text-h4 text-negative q-mt-sm">{{ recepcion.acopios_rechazados }}</div>
                <div class="text-caption text-grey-7">Rechazados</div>
                <div class="text-body2 q-mt-xs">{{ formatNumber(recepcion.cantidad_rechazada) }} kg ({{ recepcion.porcentaje_rechazo }}%)</div>
              </q-card-section>
            </q-card>
          </div>

          <div class="col-12 col-sm-6 col-md-3">
            <q-card flat bordered>
              <q-card-section class="text-center">
                <q-icon name="compare" size="md" color="info" />
                <div class="text-h4 text-info q-mt-sm">{{ recepcion.tasa_validacion }}%</div>
                <div class="text-caption text-grey-7">Tasa de Validación</div>
                <div class="text-body2 q-mt-xs">Calidad promedio</div>
              </q-card-section>
            </q-card>
          </div>
        </div>

        <div class="row q-col-gutter-md">
          <div class="col-12 col-md-6">
            <div class="text-subtitle2 q-mb-sm">Motivos de Rechazo</div>
            <q-list bordered separator>
              <q-item v-for="motivo in recepcion.motivos_rechazo" :key="motivo.motivo">
                <q-item-section>
                  <q-item-label>{{ motivo.motivo }}</q-item-label>
                  <q-item-label caption>{{ motivo.casos }} casos - {{ formatNumber(motivo.cantidad_kg) }} kg</q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-badge color="negative">{{ motivo.porcentaje }}%</q-badge>
                </q-item-section>
              </q-item>
            </q-list>
          </div>

          <div class="col-12 col-md-6">
            <div class="text-subtitle2 q-mb-sm">Evolución Mensual</div>
            <div style="height: 300px">
              <canvas ref="chartRecepcion"></canvas>
            </div>
          </div>
        </div>
      </q-card-section>
    </q-card>

    <q-card flat bordered v-if="procesamiento">
      <q-card-section>
        <div class="text-h6">Procesamiento y Merma</div>
        <div class="text-caption text-grey-7">
          Eficiencia del procesamiento y análisis de pérdidas
        </div>
      </q-card-section>

      <q-separator />

      <q-card-section>
        <div class="row q-col-gutter-md q-mb-md">
          <div class="col-12 col-md-3">
            <q-card flat bordered>
              <q-card-section class="text-center">
                <q-icon name="factory" size="md" color="primary" />
                <div class="text-h4 text-primary q-mt-sm">{{ procesamiento.procesos_finalizados }}</div>
                <div class="text-caption text-grey-7">Procesos Finalizados</div>
              </q-card-section>
            </q-card>
          </div>

          <div class="col-12 col-md-3">
            <q-card flat bordered>
              <q-card-section class="text-center">
                <q-icon name="input" size="md" color="blue" />
                <div class="text-h4 text-blue q-mt-sm">{{ formatNumber(procesamiento.entrada_total) }}</div>
                <div class="text-caption text-grey-7">Entrada Total (kg)</div>
              </q-card-section>
            </q-card>
          </div>

          <div class="col-12 col-md-3">
            <q-card flat bordered>
              <q-card-section class="text-center">
                <q-icon name="output" size="md" color="positive" />
                <div class="text-h4 text-positive q-mt-sm">{{ formatNumber(procesamiento.salida_total) }}</div>
                <div class="text-caption text-grey-7">Salida Total (kg)</div>
              </q-card-section>
            </q-card>
          </div>

          <div class="col-12 col-md-3">
            <q-card flat bordered>
              <q-card-section class="text-center">
                <q-icon name="trending_down" size="md" color="warning" />
                <div class="text-h4 text-warning q-mt-sm">{{ procesamiento.merma_promedio }}%</div>
                <div class="text-caption text-grey-7">Merma Promedio</div>
                <div class="text-body2 q-mt-xs">{{ formatNumber(procesamiento.merma_total) }} kg</div>
              </q-card-section>
            </q-card>
          </div>
        </div>

        <div class="row q-col-gutter-md">
          <div class="col-12 col-md-6">
            <div class="text-subtitle2 q-mb-sm">Eficiencia por Tanque</div>
            <q-markup-table dense flat>
              <thead>
                <tr>
                  <th class="text-left">Tanque</th>
                  <th class="text-right">Procesos</th>
                  <th class="text-right">Entrada (kg)</th>
                  <th class="text-right">Salida (kg)</th>
                  <th class="text-right">Merma %</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="tanque in procesamiento.eficiencia_tanques" :key="tanque.tanque">
                  <td class="text-left">{{ tanque.tanque }}</td>
                  <td class="text-right">{{ tanque.procesos }}</td>
                  <td class="text-right">{{ formatNumber(tanque.entrada_kg) }}</td>
                  <td class="text-right">{{ formatNumber(tanque.salida_kg) }}</td>
                  <td class="text-right">
                    <q-badge :color="tanque.merma_porcentaje > 5 ? 'warning' : 'positive'">
                      {{ tanque.merma_porcentaje }}%
                    </q-badge>
                  </td>
                </tr>
              </tbody>
            </q-markup-table>
          </div>

          <div class="col-12 col-md-6">
            <div class="text-subtitle2 q-mb-sm">Distribución de Merma</div>
            <div style="height: 300px">
              <canvas ref="chartMerma"></canvas>
            </div>
          </div>
        </div>
      </q-card-section>
    </q-card>

    <q-card flat bordered v-if="lotes">
      <q-card-section>
        <div class="text-h6">Lotes y Almacenamiento</div>
        <div class="text-caption text-grey-7">
          Control de stock y ocupación de tanques
        </div>
      </q-card-section>

      <q-separator />

      <q-card-section>
        <div class="row q-col-gutter-md q-mb-md">
          <div class="col-12 col-md-3">
            <q-card flat bordered>
              <q-card-section class="text-center">
                <q-icon name="inventory_2" size="md" color="primary" />
                <div class="text-h4 text-primary q-mt-sm">{{ lotes.lotes_generados }}</div>
                <div class="text-caption text-grey-7">Lotes Generados</div>
              </q-card-section>
            </q-card>
          </div>

          <div class="col-12 col-md-3">
            <q-card flat bordered>
              <q-card-section class="text-center">
                <q-icon name="scale" size="md" color="positive" />
                <div class="text-h4 text-positive q-mt-sm">{{ formatNumber(lotes.stock_actual) }}</div>
                <div class="text-caption text-grey-7">Stock Actual (kg)</div>
              </q-card-section>
            </q-card>
          </div>

          <div class="col-12 col-md-3">
            <q-card flat bordered>
              <q-card-section class="text-center">
                <q-icon name="water_drop" size="md" color="info" />
                <div class="text-h4 text-info q-mt-sm">{{ lotes.ocupacion_tanques }}%</div>
                <div class="text-caption text-grey-7">Ocupación Tanques</div>
              </q-card-section>
            </q-card>
          </div>

          <div class="col-12 col-md-3">
            <q-card flat bordered>
              <q-card-section class="text-center">
                <q-icon name="warning" size="md" color="warning" />
                <div class="text-h4 text-warning q-mt-sm">{{ lotes.tanques_criticos }}</div>
                <div class="text-caption text-grey-7">Tanques Críticos</div>
                <div class="text-body2 q-mt-xs">&gt; 80% ocupación</div>
              </q-card-section>
            </q-card>
          </div>
        </div>

        <div class="text-subtitle2 q-mb-sm">Estado de Tanques</div>
        <q-markup-table dense flat>
          <thead>
            <tr>
              <th class="text-left">Tanque</th>
              <th class="text-left">Tipo</th>
              <th class="text-right">Capacidad (kg)</th>
              <th class="text-right">Stock (kg)</th>
              <th class="text-right">Ocupación %</th>
              <th class="text-center">Estado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="tanque in lotes.detalle_tanques" :key="tanque.tanque">
              <td class="text-left">{{ tanque.tanque }}</td>
              <td class="text-left">{{ tanque.tipo }}</td>
              <td class="text-right">{{ formatNumber(tanque.capacidad_kg) }}</td>
              <td class="text-right">{{ formatNumber(tanque.stock_actual) }}</td>
              <td class="text-right">{{ tanque.ocupacion_porcentaje }}%</td>
              <td class="text-center">
                <q-badge 
                  :color="tanque.ocupacion_porcentaje > 80 ? 'warning' : (tanque.ocupacion_porcentaje > 60 ? 'orange' : 'positive')"
                >
                  {{ getEstadoTanque(tanque.ocupacion_porcentaje) }}
                </q-badge>
              </td>
            </tr>
          </tbody>
        </q-markup-table>
      </q-card-section>
    </q-card>

    <q-card flat bordered v-if="metodos">
      <q-card-section>
        <div class="text-h6">Métodos de Procesamiento</div>
        <div class="text-caption text-grey-7">
          Análisis de eficiencia por método aplicado
        </div>
      </q-card-section>

      <q-separator />

      <q-card-section>
        <div class="row q-col-gutter-md">
          <div class="col-12 col-md-6">
            <q-list bordered separator>
              <q-item v-for="metodo in metodos.metodos_usados" :key="metodo.metodo">
                <q-item-section>
                  <q-item-label>{{ metodo.metodo }}</q-item-label>
                  <q-item-label caption>
                    {{ metodo.usos }} usos - 
                    Temp: {{ metodo.temperatura_promedio }}°C - 
                    Tiempo: {{ metodo.tiempo_promedio }}h
                  </q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-badge :color="metodo.eficiencia_promedio > 95 ? 'positive' : 'orange'">
                    {{ metodo.eficiencia_promedio }}% eficiencia
                  </q-badge>
                </q-item-section>
              </q-item>
            </q-list>
          </div>

          <div class="col-12 col-md-6">
            <div class="text-subtitle2 q-mb-sm">Eficiencia vs Uso</div>
            <div style="height: 300px">
              <canvas ref="chartMetodos"></canvas>
            </div>
          </div>
        </div>
      </q-card-section>
    </q-card>
  </div>
</template>

<script>
import moment from 'moment'
import Chart from 'chart.js/auto'

export default {
  name: 'TrazabilidadProceso',
  props: {
    gestion: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      loading: false,
      recepcion: null,
      procesamiento: null,
      lotes: null,
      metodos: null,
      chartRecepcionInstance: null,
      chartMermaInstance: null,
      chartMetodosInstance: null
    }
  },
  mounted() {
    this.cargarDatos()
  },
  beforeUnmount() {
    if (this.chartRecepcionInstance) this.chartRecepcionInstance.destroy()
    if (this.chartMermaInstance) this.chartMermaInstance.destroy()
    if (this.chartMetodosInstance) this.chartMetodosInstance.destroy()
  },
  methods: {
    formatNumber(value) {
      return Number(value || 0).toLocaleString('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      })
    },

    getEstadoTanque(ocupacion) {
      if (ocupacion > 80) return 'Crítico'
      if (ocupacion > 60) return 'Medio'
      return 'Normal'
    },

    async cargarDatos() {
      this.loading = true
      try {
        await Promise.all([
          this.cargarRecepcion(),
          this.cargarProcesamiento(),
          this.cargarLotes(),
          this.cargarMetodos()
        ])
      } finally {
        this.loading = false
      }
    },

    async cargarRecepcion() {
      try {
        const { data } = await this.$axios.get('/dashboard-trazabilidad/recepcion-rechazos', {
          params: { gestion: this.gestion }
        })
        this.recepcion = data.resumen
        this.recepcion.motivos_rechazo = data.motivos_rechazo
        this.recepcion.evolucion_mensual = data.evolucion_mensual

        this.$nextTick(() => {
          this.renderChartRecepcion()
        })
      } catch (error) {
        this.$q.notify({
          type: 'negative',
          message: 'Error al cargar recepción'
        })
      }
    },

    async cargarProcesamiento() {
      try {
        const { data } = await this.$axios.get('/dashboard-trazabilidad/procesamiento-merma', {
          params: { gestion: this.gestion }
        })
        this.procesamiento = data.resumen
        this.procesamiento.eficiencia_tanques = data.eficiencia_tanques

        this.$nextTick(() => {
          this.renderChartMerma()
        })
      } catch (error) {
        this.$q.notify({
          type: 'negative',
          message: 'Error al cargar procesamiento'
        })
      }
    },

    async cargarLotes() {
      try {
        const { data } = await this.$axios.get('/dashboard-trazabilidad/lotes-almacenamiento', {
          params: { gestion: this.gestion }
        })
        this.lotes = data.resumen
        this.lotes.detalle_tanques = data.detalle_tanques
      } catch (error) {
        this.$q.notify({
          type: 'negative',
          message: 'Error al cargar lotes'
        })
      }
    },

    async cargarMetodos() {
      try {
        const { data } = await this.$axios.get('/dashboard-trazabilidad/metodos-procesamiento', {
          params: { gestion: this.gestion }
        })
        this.metodos = data

        this.$nextTick(() => {
          this.renderChartMetodos()
        })
      } catch (error) {
        this.$q.notify({
          type: 'negative',
          message: 'Error al cargar métodos'
        })
      }
    },

    renderChartRecepcion() {
      if (!this.recepcion || !this.recepcion.evolucion_mensual) return

      if (this.chartRecepcionInstance) {
        this.chartRecepcionInstance.destroy()
      }

      const ctx = this.$refs.chartRecepcion
      if (!ctx) return

      const data = this.recepcion.evolucion_mensual

      this.chartRecepcionInstance = new Chart(ctx, {
        type: 'line',
        data: {
          labels: data.map(d => `${d.mes}/${this.gestion}`),
          datasets: [
            {
              label: 'Validados',
              data: data.map(d => d.validados),
              borderColor: '#21BA45',
              backgroundColor: 'rgba(33, 186, 69, 0.1)',
              tension: 0.4
            },
            {
              label: 'Rechazados',
              data: data.map(d => d.rechazados),
              borderColor: '#C10015',
              backgroundColor: 'rgba(193, 0, 21, 0.1)',
              tension: 0.4
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom'
            }
          },
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      })
    },

    renderChartMerma() {
      if (!this.procesamiento || !this.procesamiento.eficiencia_tanques) return

      if (this.chartMermaInstance) {
        this.chartMermaInstance.destroy()
      }

      const ctx = this.$refs.chartMerma
      if (!ctx) return

      const data = this.procesamiento.eficiencia_tanques

      this.chartMermaInstance = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: data.map(t => t.tanque),
          datasets: [
            {
              label: 'Merma %',
              data: data.map(t => t.merma_porcentaje),
              backgroundColor: data.map(t => 
                t.merma_porcentaje > 5 ? '#F2C037' : '#21BA45'
              )
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: 'Porcentaje de Merma'
              }
            }
          }
        }
      })
    },

    renderChartMetodos() {
      if (!this.metodos || !this.metodos.metodos_usados) return

      if (this.chartMetodosInstance) {
        this.chartMetodosInstance.destroy()
      }

      const ctx = this.$refs.chartMetodos
      if (!ctx) return

      const data = this.metodos.metodos_usados

      this.chartMetodosInstance = new Chart(ctx, {
        type: 'scatter',
        data: {
          datasets: data.map(m => ({
            label: m.metodo,
            data: [{
              x: m.usos,
              y: m.eficiencia_promedio
            }],
            backgroundColor: m.eficiencia_promedio > 95 ? '#21BA45' : '#F2C037'
          }))
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom'
            }
          },
          scales: {
            x: {
              title: {
                display: true,
                text: 'Número de Usos'
              }
            },
            y: {
              title: {
                display: true,
                text: 'Eficiencia %'
              },
              min: 90,
              max: 100
            }
          }
        }
      })
    }
  },
  watch: {
    gestion() {
      this.cargarDatos()
    }
  }
}
</script>
