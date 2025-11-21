<template>
  <q-card-section>
    <!-- HEADER CON RESUMEN -->
    <div class="row items-center q-gutter-sm q-mb-sm">
      <div class="text-subtitle1">
        Registro de Acopios del Proveedoor
        <!--<span v-if="productor">
          — {{ productor.nombre_completo || (productor.nombre + ' ' + productor.apellidos) }}
        </span>-->
      </div>
      <q-space />
      <q-chip color="primary" text-color="white" dense>
        Total Kg: {{ totals.totalKg.toFixed(2) }}
      </q-chip>
      <q-chip color="amber-7" text-color="black" dense>
        Total compra: {{ totals.totalMonto.toFixed(2) }} Bs
      </q-chip>
      <q-chip color="grey-8" text-color="white" dense>
        Nº cosechas: {{ acopioCosechas.length }}
      </q-chip>
    </div>

    <!-- TABS -->
    <q-tabs
      v-model="tab"
      dense
      align="left"
      active-color="primary"
      indicator-color="primary"
      class="text-primary q-mb-sm"
    >
      <q-tab name="detalle" label="Detalle" icon="table_view" />
      <q-tab name="resumen" label="Proyección mensual" icon="analytics" />
      <q-tab name="gestion" label="Gestión anual" icon="event_note" />
    </q-tabs>
    <q-separator class="q-mb-md" />

    <q-tab-panels v-model="tab" animated>

      <!-- ================= DETALLE ================= -->
      <q-tab-panel name="detalle">
        <q-markup-table v-if="acopioCosechas.length > 0" dense wrap-cells flat bordered>
          <thead>
          <tr class="bg-primary text-white">
            <th>Fecha Cosecha</th>
            <th>Productor</th>
            <th>Cantidad (kg)</th>
            <th>Humedad (%)</th>
            <th>Temperatura Almacenaje (°C)</th>
            <th>Número Acta</th>
            <th>Condiciones Almacenaje</th>
            <th>Estado</th>
            <!-- MODIFICACION 2025-11-17: Nueva columna para acceso rapido a formularios de control -->
            <th style="width: 120px;">Formularios de CONTROL</th>
          </tr>
          </thead>
          <tbody>
          <tr v-for="cosecha in acopioCosechas" :key="cosecha.id">
            <td>{{ cosecha.fecha_cosecha }}</td>
            <td>
              {{ cosecha.apiario?.productor.nombre }}
              {{ cosecha.apiario?.productor.apellidos }}
            </td>
            <td>{{ Number(cosecha.cantidad_kg).toFixed(2) }}</td>
            <td>{{ cosecha.humedad }}</td>
            <td>{{ cosecha.temperatura_almacenaje }}</td>
            <td>{{ cosecha.num_acta }}</td>
            <td>{{ cosecha.condiciones_almacenaje }}</td>
            <td>
              <q-chip
                :color="cosecha.estado === 'BUENO' ? 'green' : 'red'"
                text-color="white"
                dense size="10px"
              >
                {{ cosecha.estado.replace('_', ' ') }}
              </q-chip>
            </td>
            <!-- MODIFICACION 2025-11-17: Boton para abrir dialog de formularios de control -->
            <td class="text-center">
              <q-btn
                flat
                dense
                color="primary"
                icon="description"
                size="sm"
                @click="openFormulariosDialog(cosecha)"
              >
                <q-tooltip>Ver Formularios de Control del SENASAG</q-tooltip>
              </q-btn>
            </td>
          </tr>
          </tbody>
        </q-markup-table>
        <div v-else class="text-center q-pa-md">
          <q-icon name="info" size="48px" color="grey-5" />
          <div class="text-subtitle2 text-grey-5">No se encontraron cosechas.</div>
        </div>
      </q-tab-panel>

      <!-- ================= RESUMEN MENSUAL ================= -->
      <q-tab-panel name="resumen">
        <div v-if="monthlyAggregates.length">
          <!-- Tabla resumen -->
          <q-markup-table dense flat bordered class="q-mb-md">
            <thead>
            <tr class="bg-primary text-white">
              <th>Mes</th>
              <th>Total Kg</th>
              <th>Total compra (Bs)</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="row in monthlyAggregates" :key="row.key">
              <td>{{ row.label }}</td>
              <td>{{ row.totalKg.toFixed(2) }}</td>
              <td>{{ row.totalMonto.toFixed(2) }}</td>
            </tr>
            </tbody>
          </q-markup-table>

          <!-- Gráfico Apex -->
          <div class="q-mt-md">
            <apexchart
              type="line"
              height="320"
              :options="monthlyChartOptions"
              :series="monthlyChartSeries"
            />
          </div>
        </div>
        <div v-else class="text-center q-pa-md">
          <q-icon name="insert_chart_outlined" size="48px" color="grey-5" />
          <div class="text-subtitle2 text-grey-5">
            Aún no hay datos suficientes para el resumen mensual.
          </div>
        </div>
      </q-tab-panel>

      <!-- ================= GESTIÓN ANUAL ================= -->
      <q-tab-panel name="gestion">
        <ProductorAcopiosGestion 
          v-if="productor"
          :productor="productor"
        />
      </q-tab-panel>
    </q-tab-panels>

    <!-- Dialog Crear/Editar RUNSA (lo dejo tal cual lo tenías) -->
    <q-dialog v-model="dlg.open" persistent>
      <q-card style="min-width: 720px; max-width: 95vw">
        <q-card-section class="row items-center q-gutter-sm">
          <div class="text-h6">
            {{ form.id ? 'Editar runsa' : 'Nueva runsa' }}
          </div>
          <q-space />
          <q-btn flat round dense icon="close" v-close-popup />
        </q-card-section>
        <q-separator />

        <q-card-section>
          <q-form @submit="submit" class="row q-col-gutter-sm">
            <div class="col-12 col-sm-4">
              <q-input
                v-model="form.codigo"
                dense outlined label="Codigo"
                :rules="[v=>!!v || 'Requerido']"
              />
            </div>
            <div class="col-12 col-sm-8">
              <q-input
                v-model="form.subcodigo"
                dense outlined label="Subcodigo"
              />
            </div>

            <div class="col-6 col-sm-3">
              <q-input v-model="form.fecha_registro" type="date" dense outlined label="Fec Registro" />
            </div>
            <div class="col-6 col-sm-3">
              <q-input v-model="form.fecha_vencimiento" type="date" dense outlined label="Fec Vencimiento" />
            </div>

            <div class="col-12 col-sm-3">
              <q-select
                v-model="form.estado"
                :options="['VIGENTE','VENCIDO','SUSPENDIDO']"
                dense outlined label="Estado"
              />
            </div>

            <div class="col-12 text-right q-gutter-sm q-mt-sm">
              <q-btn flat color="grey" label="Cancelar" v-close-popup :disable="saving" />
              <q-btn color="primary" :label="form.id ? 'Guardar cambios' : 'Crear'"
                     type="submit" :loading="saving" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- MODIFICACION 2025-11-17: Dialog para gestionar formularios de control (Plagas, Limpieza, Medicamentos) -->
    <!-- Este dialog se abre al hacer clic en el boton de Formularios de cada acopio -->
    <!-- Reutiliza los componentes PlagasFormulario, LimpiezasFormulario y MedicamentosFormulario -->
    <!-- MODIFICACION 2025-11-17: Se cambio de maximized a modal normal con ancho controlado -->
    <!-- para mejor usabilidad y no cubrir toda la pantalla -->
    <q-dialog v-model="formulariosDialog.open" persistent>
      <q-card style="width: 90vw; max-width: 1400px; max-height: 90vh">
        <q-card-section class="row items-center q-pa-md bg-primary text-white">
          <q-icon name="description" size="sm" class="q-mr-sm" />
          <div class="text-h6">Formularios de Control</div>
          <q-space />
          <q-btn flat round dense icon="close" v-close-popup color="white" />
        </q-card-section>

        <q-separator />

        <!-- Informacion del acopio seleccionado -->
        <q-card-section class="q-pa-md bg-grey-2">
          <div class="row q-col-gutter-sm">
            <div class="col-12 col-md-3">
              <div class="text-caption text-grey-7">Fecha Cosecha</div>
              <div class="text-body1">{{ formulariosDialog.cosecha?.fecha_cosecha || '—' }}</div>
            </div>
            <div class="col-12 col-md-3">
              <div class="text-caption text-grey-7">Productor</div>
              <div class="text-body1">
                {{ formulariosDialog.cosecha?.apiario?.productor.nombre }}
                {{ formulariosDialog.cosecha?.apiario?.productor.apellidos }}
              </div>
            </div>
            <div class="col-12 col-md-2">
              <div class="text-caption text-grey-7">Cantidad</div>
              <div class="text-body1">{{ formulariosDialog.cosecha?.cantidad_kg }} kg</div>
            </div>
            <div class="col-12 col-md-2">
              <div class="text-caption text-grey-7">Numero Acta</div>
              <div class="text-body1">{{ formulariosDialog.cosecha?.num_acta || '0' }}</div>
            </div>
            <div class="col-12 col-md-2">
              <div class="text-caption text-grey-7">Estado</div>
              <q-chip
                :color="formulariosDialog.cosecha?.estado === 'BUENO' ? 'green' : 'red'"
                text-color="white"
                dense
              >
                {{ formulariosDialog.cosecha?.estado }}
              </q-chip>
            </div>
          </div>
        </q-card-section>

        <q-separator />

        <!-- Tabs de formularios -->
        <q-card-section class="q-pa-md">
          <q-tabs
            v-model="formulariosDialog.tab"
            dense
            class="text-grey"
            active-color="primary"
            indicator-color="primary"
            align="left"
          >
            <q-tab name="plagas" icon="bug_report" label="Plagas" no-caps />
            <q-tab name="limpiezas" icon="cleaning_services" label="Limpiezas" no-caps />
            <q-tab name="medicamentos" icon="medication" label="Medicamentos" no-caps />
          </q-tabs>

          <q-separator />

          <q-tab-panels v-model="formulariosDialog.tab" animated style="max-height: 60vh; overflow-y: auto">
            <q-tab-panel name="plagas">
              <PlagasFormulario
                v-if="formulariosDialog.cosecha"
                :cosecha="formulariosDialog.cosecha"
              />
            </q-tab-panel>

            <q-tab-panel name="limpiezas">
              <LimpiezasFormulario
                v-if="formulariosDialog.cosecha"
                :cosecha="formulariosDialog.cosecha"
              />
            </q-tab-panel>

            <q-tab-panel name="medicamentos">
              <MedicamentosFormulario
                v-if="formulariosDialog.cosecha"
                :cosecha="formulariosDialog.cosecha"
              />
            </q-tab-panel>
          </q-tab-panels>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-card-section>
</template>
<script>
import moment from 'moment'
// MODIFICACION 2025-11-17: Importacion de componentes de formularios de control
// Estos componentes se reutilizan desde el modulo de acopios para mantener coherencia
import PlagasFormulario from 'pages/acopio/tabs/PlagasFormulario.vue'
import LimpiezasFormulario from 'pages/acopio/tabs/LimpiezasFormulario.vue'
import MedicamentosFormulario from 'pages/acopio/tabs/MedicamentosFormulario.vue'
// MODIFICACION 2025-11-18: Importacion de componente de gestion anual
import ProductorAcopiosGestion from './ProductorAcopiosGestion.vue'

export default {
  name: 'ProductorAcopios',
  // MODIFICACION 2025-11-17: Registro de componentes de formularios
  components: {
    PlagasFormulario,
    LimpiezasFormulario,
    MedicamentosFormulario,
    ProductorAcopiosGestion // MODIFICACION 2025-11-18: Registro de componente de gestion anual
  },
  props: {
    productor: { type: Object, required: true }
  },
  emits: ['updated'],
  data () {
    return {
      loading: false,
      saving: false,
      list: [],
      dlg: { open: false },
      form: this.emptyForm(),
      acopioCosechas: [],
      tab: 'detalle',
      // MODIFICACION 2025-11-17: Estado del dialog de formularios de control
      formulariosDialog: {
        open: false,
        cosecha: null,
        tab: 'plagas'
      }
    }
  },
  mounted () { this.hydrateFromProductor() },
  watch: {
    productor () { this.hydrateFromProductor() }
  },
  computed: {
    // Totales globales
    totals () {
      let totalKg = 0
      let totalMonto = 0
      this.acopioCosechas.forEach(c => {
        const kg = parseFloat(c.cantidad_kg || 0)
        const precio = parseFloat(c.precio_compra || 0)
        totalKg += kg
        totalMonto += kg * precio
      })
      return { totalKg, totalMonto }
    },

    // Array de meses agregados
    monthlyAggregates () {
      const map = {}

      this.acopioCosechas.forEach(c => {
        if (!c.fecha_cosecha) return

        const m = moment(c.fecha_cosecha)
        if (!m.isValid()) return

        const key = m.format('YYYY-MM')       // para ordenar
        const label = m.format('MMM YYYY')   // lo que se muestra
        const kg = parseFloat(c.cantidad_kg || 0)
        const precio = parseFloat(c.precio_compra || 0)
        const monto = kg * precio

        if (!map[key]) {
          map[key] = {
            key,
            label,
            totalKg: 0,
            totalMonto: 0
          }
        }

        map[key].totalKg += kg
        map[key].totalMonto += monto
      })

      // Ordenamos por key (año-mes)
      return Object.values(map).sort((a, b) => a.key.localeCompare(b.key))
    },

    // Opciones del gráfico
    monthlyChartOptions () {
      return {
        chart: {
          id: 'acopios-mensuales',
          toolbar: { show: false }
        },
        xaxis: {
          categories: this.monthlyAggregates.map(m => m.label)
        },
        dataLabels: {
          enabled: true,
          enabledOnSeries: [0] // solo en barras (kg)
        },
        stroke: {
          width: [0, 3] // barras sin borde, línea con grosor 3
        },
        yaxis: [
          {
            title: { text: 'Kg' }
          },
          {
            opposite: true,
            title: { text: 'Bs' }
          }
        ],
        legend: {
          position: 'top'
        },
        tooltip: {
          shared: true,
          intersect: false
        }
      }
    },

    // Series del gráfico
    monthlyChartSeries () {
      const kgData = this.monthlyAggregates.map(m => m.totalKg)
      const montoData = this.monthlyAggregates.map(m => m.totalMonto)

      return [
        {
          name: 'Kg',
          type: 'column',
          data: kgData
        },
        {
          name: 'Monto (Bs)',
          type: 'line',
          data: montoData
        }
      ]
    }
  },
  methods: {
    async fetchAcopio () {
      if (!this.productor?.id) return
      this.loading = true
      try {
        const { data } = await this.$axios.post('/productorAcopios', {
          productor_id: this.productor.id
        })
        this.acopioCosechas = data
      } catch (error) {
        console.log(error)
        this.$alert?.error?.(error.response?.data?.message || 'No se pudo cargar las cosechas')
      } finally {
        this.loading = false
      }
    },
    hydrateFromProductor () {
      this.fetchAcopio()
    },

    async fetchRunsas () {
      if (!this.productor?.id) return
      this.loading = true
      try {
        const { data } = await this.$axios.get('runsas', {
          params: { productor_id: this.productor.id, paginate: false }
        })
        this.list = Array.isArray(data) ? data : (data?.data || [])
      } catch (e) {
        console.log(e)
        this.list = []
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo cargar runsas')
      } finally {
        this.loading = false
      }
    },
    emptyForm () {
      return {
        id: null,
        productor_id: this.productor?.id || null,
        codigo: null,
        subcodigo: '',
        fecha_registro: null,
        fecha_vencimiento: null,
        estado: 'VIGENTE'
      }
    },
    chip (estado) {
      if (estado === 'VIGENTE') return 'green'
      if (estado === 'VENCIDO') return 'red'
      if (estado === 'SUSPENDIDO') return 'orange'
      return 'grey'
    },

    startCreate () {
      this.form = this.emptyForm()
      this.form.productor_id = this.productor?.id || null
      this.dlg.open = true
    },
    startEdit (row) {
      this.form = {
        id: row.id,
        productor_id: this.productor?.id || row.productor_id,
        codigo: row.codigo || null,
        subcodigo: row.subcodigo || '',
        fecha_registro: row.fecha_registro || null,
        fecha_vencimiento: row.fecha_vencimiento || null,
        estado: row.estado || 'VIGENTE'
      }
      this.dlg.open = true
    },

    async submit () {
      if (!this.productor?.id) return
      this.saving = true
      try {
        const payload = { ...this.form, productor_id: this.productor.id }
        if (payload.id) {
          await this.$axios.put(`runsas/${payload.id}`, payload)
        } else {
          await this.$axios.post('runsas', payload)
        }
        this.$alert?.success?.(payload.id ? 'Runsa actualizada' : 'Runsa creada')
        this.dlg.open = false
        this.$emit('updated')
      } catch (e) {
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo guardar')
      } finally {
        this.saving = false
      }
    },

    remove (row) {
      this.$alert?.dialog?.('¿Eliminar runsa?')?.onOk(async () => {
        try {
          await this.$axios.delete(`runsas/${row.id}`)
          this.$alert?.success?.('Eliminado')
          this.$emit('updated')
        } catch (e) {
          this.$alert?.error?.(e.response?.data?.message || 'No se pudo eliminar')
        }
      })
    },

    // MODIFICACION 2025-11-17: Metodo para abrir el dialog de formularios de control
    // Recibe el objeto cosecha y lo asigna al estado del dialog
    // MODIFICACION 2025-11-18: Cargar relaciones completas de la cosecha para mostrar datos de encabezado
    async openFormulariosDialog (cosecha) {
      try {
        // MODIFICACION 2025-11-18: Obtener cosecha con todas las relaciones necesarias para encabezado
        // El backend ya carga: apiario.productor.municipio.provincia/departamento
        const { data } = await this.$axios.get(`/acopio-cosechas/${cosecha.id}`)
        this.formulariosDialog.cosecha = data
        this.formulariosDialog.tab = 'plagas'
        this.formulariosDialog.open = true
      } catch (e) {
        // Si falla la carga con relaciones, usar la cosecha original
        this.formulariosDialog.cosecha = cosecha
        this.formulariosDialog.tab = 'plagas'
        this.formulariosDialog.open = true
      }
    }
  }
}
</script>
