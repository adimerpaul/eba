<template>
  <q-card-section>
    <!-- ENCABEZADO CON SELECTORES -->
    <div class="row items-center q-gutter-sm q-mb-md">
      <div class="text-subtitle1 text-weight-medium">Acopios por Gestión (Julio-Junio)</div>
      <q-space/>
      
      <!-- Selector de Gestión -->
      <q-select 
        v-model="gestionSeleccionada" 
        :options="gestiones" 
        label="Gestión" 
        dense 
        outlined 
        style="min-width: 130px"
        @update:model-value="cargarAcopios"
        :loading="loading"
      >
        <template v-slot:prepend>
          <q-icon name="event" />
        </template>
      </q-select>

      <!-- Selector de Producto -->
      <q-select 
        v-model="productoSeleccionado" 
        :options="productos" 
        option-label="nombre_producto" 
        option-value="id"
        emit-value
        map-options
        label="Producto" 
        dense 
        outlined 
        style="min-width: 200px"
        @update:model-value="cargarAcopios"
        :loading="loadingProductos"
      >
        <template v-slot:prepend>
          <q-icon name="inventory" />
        </template>
      </q-select>

      <!-- Botón Exportar Excel -->
      <q-btn 
        color="green" 
        icon="download" 
        label="Excel" 
        dense 
        no-caps
        @click="exportarExcel"
        :loading="loadingExcel"
        :disable="!datosDisponibles"
      >
        <q-tooltip v-if="!datosDisponibles">
          No hay datos para exportar
        </q-tooltip>
      </q-btn>

      <q-btn 
        color="primary" 
        icon="refresh" 
        dense
        round
        flat
        @click="cargarAcopios"
        :loading="loading"
      >
        <q-tooltip>Recargar datos</q-tooltip>
      </q-btn>
    </div>

    <!-- INFORMACIÓN DEL PRODUCTOR -->
    <q-banner v-if="datosProductor" dense class="bg-blue-1 q-mb-md" rounded>
      <template v-slot:avatar>
        <q-icon name="person" color="primary" />
      </template>
      <div class="row q-col-gutter-md">
        <div class="col-auto">
          <div class="text-caption text-grey-7">Productor</div>
          <div class="text-weight-medium">{{ datosProductor.productor }}</div>
        </div>
        <div class="col-auto" v-if="datosProductor.runsa">
          <div class="text-caption text-grey-7">RUNSA</div>
          <div class="text-weight-medium">{{ datosProductor.runsa }}</div>
        </div>
        <div class="col-auto" v-if="datosProductor.municipio">
          <div class="text-caption text-grey-7">Municipio</div>
          <div class="text-weight-medium">{{ datosProductor.municipio }}</div>
        </div>
        <div class="col-auto" v-if="datosProductor.organizacion">
          <div class="text-caption text-grey-7">Organización</div>
          <div class="text-weight-medium">{{ datosProductor.organizacion }}</div>
        </div>
      </div>
    </q-banner>

    <!-- TABLA DE ACOPIOS MENSUALES -->
    <q-card flat bordered>
      <q-card-section class="q-pa-none">
        <q-markup-table dense flat>
          <thead>
            <tr class="bg-primary text-white">
              <th class="text-left" style="width: 30%">Mes</th>
              <th class="text-right" style="width: 25%">Cantidad (kg)</th>
              <th class="text-right" style="width: 20%">N° Entregas</th>
              <th class="text-right" style="width: 25%">Promedio/Entrega</th>
            </tr>
          </thead>
          <tbody>
            <!-- Filas de datos de cada mes -->
            <tr v-for="mes in meses" :key="mes.offset_mes" :class="{'bg-grey-2': mes.num_entregas === 0}">
              <td class="text-left">
                <span class="text-weight-medium">{{ mes.mes_nombre }}</span>
                <span class="text-caption text-grey-7 q-ml-xs" v-if="mes.anio_mes">{{ mes.anio_mes }}</span>
              </td>
              <td class="text-right">
                <span :class="{'text-weight-bold': mes.num_entregas > 0}">
                  {{ formatNumber(mes.cantidad_kg) }}
                </span>
              </td>
              <td class="text-right">
                <q-chip 
                  v-if="mes.num_entregas > 0" 
                  dense 
                  size="sm" 
                  color="blue-2" 
                  text-color="blue-9"
                >
                  {{ mes.num_entregas }}
                </q-chip>
                <span v-else class="text-grey-5">-</span>
              </td>
              <td class="text-right">
                <span v-if="mes.num_entregas > 0" class="text-grey-8">
                  {{ formatNumber(mes.cantidad_kg / mes.num_entregas) }}
                </span>
                <span v-else class="text-grey-5">-</span>
              </td>
            </tr>

            <!-- Fila de totales -->
            <tr class="bg-grey-3 text-weight-bold">
              <td class="text-left text-uppercase">
                Total Gestión {{ gestionSeleccionada }}
              </td>
              <td class="text-right text-primary">
                {{ formatNumber(totalKg) }}
              </td>
              <td class="text-right">
                <q-chip dense color="primary" text-color="white">
                  {{ totalEntregas }}
                </q-chip>
              </td>
              <td class="text-right text-grey-8">
                {{ formatNumber(promedioEntrega) }}
              </td>
            </tr>
          </tbody>
        </q-markup-table>
      </q-card-section>

      <!-- MENSAJE SI NO HAY DATOS -->
      <q-card-section v-if="!loading && meses.length === 0" class="text-center q-pa-lg">
        <q-icon name="info_outline" size="48px" color="grey-5" />
        <div class="text-subtitle2 text-grey-6 q-mt-sm">
          Sin acopios registrados para esta gestión
        </div>
        <div class="text-caption text-grey-5">
          Período: {{ gestionSeleccionada }}-07-01 a {{ gestionSeleccionada + 1 }}-06-30
        </div>
      </q-card-section>

      <!-- LOADING -->
      <q-inner-loading :showing="loading">
        <q-spinner-dots size="50px" color="primary" />
      </q-inner-loading>
    </q-card>

    <!-- GRÁFICO DE BARRAS -->
    <q-card flat bordered class="q-mt-md" v-if="datosDisponibles && !loading">
      <q-card-section>
        <div class="text-subtitle2 q-mb-md">
          <q-icon name="bar_chart" color="primary" class="q-mr-xs" />
          Gráfica de Acopios Mensuales - Gestión {{ gestionSeleccionada }}
        </div>
        <canvas ref="chartCanvas" height="100"></canvas>
      </q-card-section>
    </q-card>
  </q-card-section>
</template>

<script>
import Chart from 'chart.js/auto'
import moment from 'moment'

export default {
  name: 'ProductorAcopiosGestion',
  props: {
    productor: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      // Estado de carga
      loading: false,
      loadingProductos: false,
      loadingExcel: false, // Estado específico para exportación Excel

      // Selecciones del usuario
      gestionSeleccionada: moment().year(), // Año actual por defecto
      productoSeleccionado: 1, // Default: miel (id=1)

      // Opciones disponibles
      gestiones: [],
      productos: [],

      // Datos del reporte
      datosProductor: null,
      meses: [],
      totalKg: 0,
      totalEntregas: 0,
      promedioEntrega: 0,

      // Instancia del gráfico
      chartInstance: null
    }
  },
  computed: {
    /**
     * Indica si hay datos disponibles para mostrar
     */
    datosDisponibles() {
      return this.meses.length > 0 && this.totalKg > 0
    }
  },
  mounted() {
    this.generarGestiones()
    this.cargarProductos()
    this.cargarAcopios()
  },
  beforeUnmount() {
    // Destruir gráfico al desmontar componente
    if (this.chartInstance) {
      this.chartInstance.destroy()
    }
  },
  methods: {
    /**
     * Genera lista de gestiones disponibles (desde 2020 hasta año actual)
     */
    generarGestiones() {
      const currentYear = moment().year()
      const years = []
      for (let i = currentYear; i >= 2020; i--) {
        years.push(i)
      }
      this.gestiones = years
    },

    /**
     * Carga lista de productos tipo miel (tipo_id = 1)
     */
    async cargarProductos() {
      this.loadingProductos = true
      try {
        const { data } = await this.$axios.get('/productos/tipo/1')
        this.productos = data?.data || data || []
        
        // Si no hay productos, mostrar alerta
        if (this.productos.length === 0) {
          this.$alert?.warning?.('No se encontraron productos tipo miel')
        }
      } catch (e) {
        console.error('Error cargando productos:', e)
        this.$alert?.error?.('No se pudieron cargar los productos')
        this.productos = []
      } finally {
        this.loadingProductos = false
      }
    },

    /**
     * Carga los acopios mensuales del productor para la gestión seleccionada
     */
    async cargarAcopios() {
      // Validar que haya un productor
      if (!this.productor?.id) {
        console.error('No hay productor seleccionado')
        return
      }

      this.loading = true
      try {
        // Llamar al endpoint del backend
        const { data } = await this.$axios.get(
          `/productores/${this.productor.id}/acopios-gestion`,
          {
            params: {
              gestion: this.gestionSeleccionada,
              producto_id: this.productoSeleccionado
            }
          }
        )

        // Asignar datos recibidos
        this.datosProductor = {
          productor: data.productor,
          runsa: data.runsa,
          municipio: data.municipio,
          organizacion: data.organizacion
        }
        this.meses = data.meses || []
        this.totalKg = Number(data.total_kg || 0)
        this.totalEntregas = Number(data.total_entregas || 0)
        this.promedioEntrega = Number(data.promedio_entrega || 0)

        // Renderizar gráfico después de cargar datos
        this.$nextTick(() => {
          this.renderChart()
        })

      } catch (e) {
        console.error('Error cargando acopios:', e)
        this.$alert?.error?.(e.response?.data?.message || 'Error al cargar acopios por gestión')
        
        // Limpiar datos en caso de error
        this.meses = []
        this.totalKg = 0
        this.totalEntregas = 0
        this.promedioEntrega = 0
      } finally {
        this.loading = false
      }
    },

    /**
     * Renderiza el gráfico de barras con los datos mensuales
     */
    renderChart() {
      // Validar que exista el canvas
      if (!this.$refs.chartCanvas) {
        console.warn('Canvas no está disponible')
        return
      }

      // Destruir gráfico anterior si existe
      if (this.chartInstance) {
        this.chartInstance.destroy()
      }

      // Preparar datos para el gráfico
      const labels = this.meses.map(m => m.mes_nombre)
      const data = this.meses.map(m => Number(m.cantidad_kg))
      const entregas = this.meses.map(m => Number(m.num_entregas))

      // Crear nuevo gráfico
      this.chartInstance = new Chart(this.$refs.chartCanvas, {
        type: 'bar',
        data: {
          labels,
          datasets: [
            {
              label: 'Cantidad (kg)',
              data,
              backgroundColor: 'rgba(54, 162, 235, 0.6)',
              borderColor: 'rgba(54, 162, 235, 1)',
              borderWidth: 1,
              yAxisID: 'y'
            },
            {
              label: 'N° Entregas',
              data: entregas,
              backgroundColor: 'rgba(255, 159, 64, 0.6)',
              borderColor: 'rgba(255, 159, 64, 1)',
              borderWidth: 1,
              type: 'line',
              yAxisID: 'y1'
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          interaction: {
            mode: 'index',
            intersect: false
          },
          plugins: {
            legend: {
              display: true,
              position: 'top'
            },
            title: {
              display: true,
              text: `Acopios Gestión ${this.gestionSeleccionada} (Julio-Junio)`
            },
            tooltip: {
              callbacks: {
                footer: (tooltipItems) => {
                  const index = tooltipItems[0].dataIndex
                  const mes = this.meses[index]
                  if (mes && mes.num_entregas > 0) {
                    const promedio = (mes.cantidad_kg / mes.num_entregas).toFixed(2)
                    return `Promedio/entrega: ${promedio} kg`
                  }
                  return ''
                }
              }
            }
          },
          scales: {
            y: {
              type: 'linear',
              position: 'left',
              beginAtZero: true,
              title: {
                display: true,
                text: 'Kilogramos (kg)'
              }
            },
            y1: {
              type: 'linear',
              position: 'right',
              beginAtZero: true,
              title: {
                display: true,
                text: 'Número de entregas'
              },
              grid: {
                drawOnChartArea: false
              }
            }
          }
        }
      })
    },

    /**
     * Formatea números para mostrar con 2 decimales
     */
    formatNumber(value) {
      const num = Number(value)
      return isNaN(num) ? '0.00' : num.toFixed(2)
    },

    /**
     * Exportar datos a Excel
     * Llama al endpoint del backend que genera el archivo Excel
     */
    async exportarExcel() {
      // Validar que haya datos para exportar
      if (!this.datosDisponibles) {
        this.$alert?.warning?.('No hay datos para exportar')
        return
      }

      this.loadingExcel = true
      try {
        // Llamar endpoint de exportación
        const response = await this.$axios.post(
          `/productores/${this.productor.id}/acopios-gestion-excel`,
          {
            gestion: this.gestionSeleccionada,
            producto_id: this.productoSeleccionado
          },
          {
            responseType: 'blob' // Importante para recibir archivo binario
          }
        )

        // Crear blob y descargar archivo
        const blob = new Blob([response.data], {
          type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        })
        const url = window.URL.createObjectURL(blob)
        const link = document.createElement('a')
        link.href = url
        
        // Nombre del archivo con información del productor y gestión
        const nombreProductor = this.datosProductor?.productor?.replace(/\s+/g, '_') || 'productor'
        link.setAttribute('download', `acopios_${nombreProductor}_${this.gestionSeleccionada}.xlsx`)
        
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
        window.URL.revokeObjectURL(url)

        this.$alert?.success?.('Archivo Excel descargado correctamente')

      } catch (e) {
        console.error('Error exportando a Excel:', e)
        this.$alert?.error?.(
          e.response?.data?.message || 'No se pudo generar el archivo Excel'
        )
      } finally {
        this.loadingExcel = false
      }
    }
  }
}
</script>

<style scoped>
/* Estilos específicos del componente */
</style>
