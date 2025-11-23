<template>
  <q-card-section>
    <!-- HEADER CON TÍTULO Y BOTÓN -->
    <div class="row items-center q-gutter-sm q-mb-md">
      <div class="text-subtitle1">
        Registro de Acopios del Proveedoor
        <!--<span v-if="productor">
          — {{ productor.nombre_completo || (productor.nombre + ' ' + productor.apellidos) }}
        </span>-->
      </div>
      <q-space />

      <!-- 2025-11-21: Botón para registro rápido de acopio -->
      <q-btn color="positive" icon="add" label="Nuevo Acopio" dense no-caps @click="abrirModalNuevoAcopio">
        <q-tooltip>Registrar nuevo acopio para este productor</q-tooltip>
      </q-btn>
    </div>

    <!-- TABS -->
    <q-tabs v-model="tab" dense align="left" active-color="primary" indicator-color="primary" class="text-primary">
      <q-tab name="detalle" label="Lista de Acopios" icon="table_view" />
      <q-tab name="resumen" label="Proyección mensual" icon="analytics" />
      <q-tab name="gestion" label="Gestión anual" icon="event_note" />
    </q-tabs>

    <!-- 2025-11-21: Chips de totales movidos debajo de tabs para dar visibilidad al botón Nuevo Acopio -->
    <div class="row items-center q-gutter-sm q-mt-sm q-mb-sm">
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

    <q-separator class="q-mb-md" />

    <q-tab-panels v-model="tab" animated>

      <!-- ================= DETALLE ================= -->
      <q-tab-panel name="detalle">
        <q-markup-table v-if="acopioCosechas.length > 0" dense wrap-cells flat bordered>
          <thead>
            <tr class="bg-primary text-white">
              <!-- 2025-11-21: Nueva columna para numeracion secuencial -->
              <th style="width: 50px;">#</th>
              <!-- 2025-11-21: Nueva columna para botones de accion -->
              <th style="width: 180px;">Opciones</th>
              <th>Fecha Cosecha</th>
              <th>Número Acta</th>
              <th>Productor</th>
              <!-- 2025-11-21: Nueva columna para tipo de materia prima -->
              <th>Tipo Materia Prima</th>
              <!-- 2025-11-21: Cambio de etiqueta de 'Cantidad (kg)' a 'Peso Total (Kg)' -->
              <th class="text-right">Peso Total (Kg)</th>
              <!-- 2025-11-21: Nueva columna para precio de compra -->
              <th class="text-right">Precio Bs.</th>
              <!-- 2025-11-21: Nueva columna para costo total calculado -->
              <th class="text-right">Costo Total</th>
              <th>Estado</th>
              <!-- MODIFICACION 2025-11-17: Nueva columna para acceso rapido a formularios de control - Movida al final 2025-11-21 -->
              <th style="width: 120px;">Formularios de CONTROL</th>
              <!-- 2025-11-21: Nueva columna para editar y eliminar -->
              <th style="width: 100px;">Acciones</th>
              <!-- 2025-11-21: Columnas ocultadas (comentadas) para no mostrar en tabla principal -->
              <!-- <th>Humedad (%)</th> -->
              <!-- <th>Temperatura Almacenaje (°C)</th> -->
              <!-- <th>Condiciones Almacenaje</th> -->
            </tr>
          </thead>
          <tbody>
            <tr v-for="(cosecha, index) in acopioCosechas" :key="cosecha.id">
              <!-- 2025-11-21: Celda de numeracion secuencial -->
              <td class="text-center">{{ index + 1 }}</td>
              <!-- 2025-11-21: Celda de botones de accion -->
              <td class="text-center">
                <div class="q-gutter-xs">
                  <q-btn flat dense round color="blue" icon="description" size="sm"
                    @click="imprimirActaConformidad(cosecha)">
                    <q-tooltip>Acta de Conformidad</q-tooltip>
                  </q-btn>
                  <q-btn flat dense round color="green" icon="local_shipping" size="sm"
                    @click="imprimirActaEntrega(cosecha)">
                    <q-tooltip>Acta de Entrega</q-tooltip>
                  </q-btn>
                  <q-btn flat dense round color="orange" icon="receipt" size="sm" @click="imprimirRecibo(cosecha)">
                    <q-tooltip>Recibo</q-tooltip>
                  </q-btn>
                  <q-btn flat dense round color="purple" icon="sync" size="sm" @click="procesarAcopio(cosecha)"
                    :disable="cosecha.estado !== 'BUENO'">
                    <q-tooltip>Procesar (Enviar a Revisión)</q-tooltip>
                  </q-btn>
                </div>
              </td>
              <td>{{ cosecha.fecha_cosecha }}</td>
              <td>{{ cosecha.num_acta }}</td>
              <td>
                {{ cosecha.apiario?.productor.nombre }}
                {{ cosecha.apiario?.productor.apellidos }}
              </td>
              <!-- 2025-11-21: Celda para tipo de materia prima (producto) - Corregido a nombre_producto -->
              <td>{{ cosecha.producto?.nombre_producto || 'N/A' }}</td>
              <!-- 2025-11-21: Celda de peso total (antes 'Cantidad kg') -->
              <td class="text-right">{{ Number(cosecha.cantidad_kg).toFixed(2) }}</td>
              <!-- 2025-11-21: Celda de precio de compra -->
              <td class="text-right">{{ Number(cosecha.precio_compra || 0).toFixed(2) }}</td>
              <!-- 2025-11-21: Celda de costo total calculado -->
              <td class="text-right">{{ calcularCostoTotal(cosecha) }}</td>
              <!-- 2025-11-21: Celda de estado con chip mejorado -->
              <td>
                <q-chip :color="obtenerColorEstado(cosecha.estado)" text-color="white" dense size="10px">
                  {{ obtenerLabelEstado(cosecha.estado) }}
                </q-chip>
              </td>
              <!-- MODIFICACION 2025-11-17: Boton para abrir dialog de formularios de control - Movido al final 2025-11-21 -->
              <td class="text-center">
                <q-btn flat dense color="primary" icon="description" size="sm" @click="openFormulariosDialog(cosecha)">
                  <q-tooltip>Ver Formularios de Control del SENASAG</q-tooltip>
                </q-btn>
              </td>
              <!-- 2025-11-21: Celda de acciones (editar y eliminar) -->
              <td class="text-center">
                <div class="q-gutter-xs">
                  <q-btn flat dense round color="primary" icon="edit" size="sm" @click="editarAcopio(cosecha)">
                    <q-tooltip>Editar acopio</q-tooltip>
                  </q-btn>
                  <q-btn flat dense round color="negative" icon="delete" size="sm" @click="eliminarAcopio(cosecha)">
                    <q-tooltip>Eliminar acopio</q-tooltip>
                  </q-btn>
                </div>
              </td>
              <!-- 2025-11-21: Celdas ocultadas (comentadas) - datos siguen disponibles en dialog de formularios -->
              <!-- <td>{{ cosecha.humedad }}</td> -->
              <!-- <td>{{ cosecha.temperatura_almacenaje }}</td> -->
              <!-- <td>{{ cosecha.condiciones_almacenaje }}</td> -->
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
            <apexchart type="line" height="320" :options="monthlyChartOptions" :series="monthlyChartSeries" />
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
        <ProductorAcopiosGestion v-if="productor" :productor="productor" />
      </q-tab-panel>
    </q-tab-panels>

    <!-- 2025-11-21: Dialog para registro rápido de acopio -->
    <q-dialog v-model="dialogNuevoAcopio" persistent>
      <q-card style="min-width: 700px; max-width: 90vw;">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">
            {{ formAcopio.id ? 'Editar' : 'Nuevo' }} Acopio - {{ productor?.nombre }} {{ productor?.apellidos }}
          </div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <q-form ref="formAcopioRef" @submit.prevent="guardarAcopio">
            <div class="row q-col-gutter-md">

              <!-- Fecha (precargada con fecha actual) -->
              <div class="col-12 col-md-4">
                <q-input v-model="formAcopio.fecha_cosecha" type="date" label="Fecha de Cosecha" dense outlined
                  :rules="[val => !!val || 'Requerido']" />
              </div>

              <!-- Número de Acta -->
              <div class="col-12 col-md-4">
                <q-input v-model="formAcopio.num_acta" label="Número de Acta" dense outlined
                  :rules="[val => !!val || 'Requerido']" />
              </div>

              <!-- Apiario (dependiente del productor actual) -->
              <div class="col-12 col-md-4">
                <q-select v-model="formAcopio.apiario_id" :options="apiarioOptionsModal" option-value="value"
                  option-label="label" emit-value map-options label="Apiario" dense outlined
                  :loading="loadingApiariosModal" :rules="[v => !!v || 'Seleccione un apiario']" />
              </div>

              <!-- Producto (precargado con Miel de Abeja) -->
              <div class="col-12 col-md-6">
                <q-select v-model="formAcopio.producto_id" :options="productosOptions" option-value="value"
                  option-label="label" emit-value map-options label="Tipo de Materia Prima" dense outlined
                  :loading="loadingProductos" :rules="[v => !!v || 'Requerido']" />
              </div>

              <!-- Cantidad KG -->
              <div class="col-12 col-md-3">
                <q-input v-model.number="formAcopio.cantidad_kg" type="number" min="0" step="0.01" label="Cantidad (kg)"
                  dense outlined suffix="kg" :rules="[v => v > 0 || 'Mayor a 0']" />
              </div>

              <!-- Precio de Compra -->
              <div class="col-12 col-md-3">
                <q-input v-model.number="formAcopio.precio_compra" type="number" min="0" step="0.01"
                  label="Precio de compra" dense outlined prefix="Bs " :rules="[v => v >= 0 || 'No negativo']" />
              </div>

              <!-- Humedad (opcional) -->
              <div class="col-6 col-md-3">
                <q-input v-model.number="formAcopio.humedad" type="number" min="0" step="0.01" label="Humedad (%)" dense
                  outlined suffix="%" />
              </div>

              <!-- Temperatura (opcional) -->
              <div class="col-6 col-md-3">
                <q-input v-model.number="formAcopio.temperatura_almacenaje" type="number" step="0.01"
                  label="Temperatura (°C)" dense outlined suffix="°C" />
              </div>

              <!-- Procedencia (opcional) -->
              <div class="col-6 col-md-3">
                <q-input v-model="formAcopio.procedencia" label="Procedencia" dense outlined maxlength="50" />
              </div>

              <!-- Tipo de Envase -->
              <div class="col-6 col-md-3">
                <q-select v-model="formAcopio.tipo_envase" label="Tipo de envase" dense outlined
                  :options="['BALDE', 'OTRO']" />
              </div>

              <!-- Observaciones -->
              <div class="col-12">
                <q-input v-model="formAcopio.observaciones" label="Observaciones" dense outlined autogrow
                  maxlength="255" />
              </div>

            </div>

            <div class="row q-gutter-sm q-mt-md justify-end">
              <q-btn label="Cancelar" color="grey-7" flat v-close-popup no-caps />
              <q-btn type="submit" :label="formAcopio.id ? 'Actualizar Acopio' : 'Guardar Acopio'" color="positive"
                icon="save" :loading="loadingGuardar" no-caps />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

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
              <q-input v-model="form.codigo" dense outlined label="Codigo" :rules="[v => !!v || 'Requerido']" />
            </div>
            <div class="col-12 col-sm-8">
              <q-input v-model="form.subcodigo" dense outlined label="Subcodigo" />
            </div>

            <div class="col-6 col-sm-3">
              <q-input v-model="form.fecha_registro" type="date" dense outlined label="Fec Registro" />
            </div>
            <div class="col-6 col-sm-3">
              <q-input v-model="form.fecha_vencimiento" type="date" dense outlined label="Fec Vencimiento" />
            </div>

            <div class="col-12 col-sm-3">
              <q-select v-model="form.estado" :options="['VIGENTE', 'VENCIDO', 'SUSPENDIDO']" dense outlined
                label="Estado" />
            </div>

            <div class="col-12 text-right q-gutter-sm q-mt-sm">
              <q-btn flat color="grey" label="Cancelar" v-close-popup :disable="saving" />
              <q-btn color="primary" :label="form.id ? 'Guardar cambios' : 'Crear'" type="submit" :loading="saving" />
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
              <q-chip :color="formulariosDialog.cosecha?.estado === 'BUENO' ? 'green' : 'red'" text-color="white" dense>
                {{ formulariosDialog.cosecha?.estado }}
              </q-chip>
            </div>
          </div>
        </q-card-section>

        <q-separator />

        <!-- Tabs de formularios -->
        <q-card-section class="q-pa-md">
          <q-tabs v-model="formulariosDialog.tab" dense class="text-grey" active-color="primary"
            indicator-color="primary" align="left">
            <q-tab name="plagas" icon="bug_report" label="Plagas" no-caps />
            <q-tab name="limpiezas" icon="cleaning_services" label="Limpiezas" no-caps />
            <q-tab name="medicamentos" icon="medication" label="Medicamentos" no-caps />
          </q-tabs>

          <q-separator />

          <q-tab-panels v-model="formulariosDialog.tab" animated style="max-height: 60vh; overflow-y: auto">
            <q-tab-panel name="plagas">
              <PlagasFormulario v-if="formulariosDialog.cosecha" :cosecha="formulariosDialog.cosecha" />
            </q-tab-panel>

            <q-tab-panel name="limpiezas">
              <LimpiezasFormulario v-if="formulariosDialog.cosecha" :cosecha="formulariosDialog.cosecha" />
            </q-tab-panel>

            <q-tab-panel name="medicamentos">
              <MedicamentosFormulario v-if="formulariosDialog.cosecha" :cosecha="formulariosDialog.cosecha" />
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
  data() {
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
      },
      // 2025-11-21: Data para modal de nuevo acopio
      dialogNuevoAcopio: false,
      loadingGuardar: false,
      loadingApiariosModal: false,
      loadingProductos: false,
      apiarioOptionsModal: [],
      productosOptions: [],
      formAcopio: {
        fecha_cosecha: null,
        num_acta: '',
        apiario_id: null,
        producto_id: null,
        cantidad_kg: null,
        precio_compra: 32,
        humedad: null,
        temperatura_almacenaje: null,
        procedencia: '',
        tipo_envase: 'BALDE',
        observaciones: '',
        estado: 'BUENO'
      }
    }
  },
  mounted() { this.hydrateFromProductor() },
  watch: {
    productor() { this.hydrateFromProductor() }
  },
  computed: {
    // Totales globales
    totals() {
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
    monthlyAggregates() {
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
    monthlyChartOptions() {
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
    monthlyChartSeries() {
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
    async fetchAcopio() {
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
    hydrateFromProductor() {
      this.fetchAcopio()
    },

    async fetchRunsas() {
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
    emptyForm() {
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
    chip(estado) {
      if (estado === 'VIGENTE') return 'green'
      if (estado === 'VENCIDO') return 'red'
      if (estado === 'SUSPENDIDO') return 'orange'
      return 'grey'
    },

    startCreate() {
      this.form = this.emptyForm()
      this.form.productor_id = this.productor?.id || null
      this.dlg.open = true
    },
    startEdit(row) {
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

    async submit() {
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

    remove(row) {
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
    async openFormulariosDialog(cosecha) {
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
    },

    /**
     * Calcular costo total de un acopio
     * Creado: 2025-11-21
     * @param {Object} cosecha - Objeto cosecha con cantidad_kg y precio_compra
     * @returns {String} Costo total formateado con 2 decimales
     */
    calcularCostoTotal(cosecha) {
      const cantidad = parseFloat(cosecha.cantidad_kg || 0)
      const precio = parseFloat(cosecha.precio_compra || 0)
      return (cantidad * precio).toFixed(2)
    },

    /**
     * Obtener color del chip segun estado
     * Creado: 2025-11-21
     * @param {String} estado - Estado del acopio (BUENO, EN_PROCESO, CANCELADO)
     * @returns {String} Color para el q-chip
     */
    obtenerColorEstado(estado) {
      const colores = {
        'BUENO': 'green',
        'EN_PROCESO': 'orange',
        'CANCELADO': 'red'
      }
      return colores[estado] || 'grey'
    },

    /**
     * Obtener label mejorado del estado
     * Creado: 2025-11-21
     * @param {String} estado - Estado del acopio (BUENO, EN_PROCESO, CANCELADO)
     * @returns {String} Label descriptivo del estado
     */
    obtenerLabelEstado(estado) {
      const labels = {
        'BUENO': 'Acopiado',
        'EN_PROCESO': 'En Revisión',
        'CANCELADO': 'Cancelado'
      }
      return labels[estado] || estado
    },

    /**
     * Procesar acopio: cambiar estado de BUENO a EN_PROCESO
     * Creado: 2025-11-21
     * @param {Object} cosecha - Objeto cosecha a procesar
     */
    procesarAcopio(cosecha) {
      if (cosecha.estado !== 'BUENO') {
        this.$q.notify({
          type: 'warning',
          message: 'Solo se pueden procesar acopios en estado Acopiado'
        })
        return
      }

      this.$q.dialog({
        title: 'Procesar Acopio',
        message: `¿Esta seguro de enviar a revision el acopio del ${cosecha.fecha_cosecha}?`,
        cancel: {
          label: 'Cancelar',
          flat: true,
          color: 'grey'
        },
        ok: {
          label: 'Procesar',
          color: 'purple'
        },
        persistent: true
      }).onOk(async () => {
        try {
          await this.$axios.put(`/acopio-cosechas/${cosecha.id}`, {
            ...cosecha,
            estado: 'EN_PROCESO'
          })
          this.$q.notify({
            type: 'positive',
            message: 'Acopio enviado a revision correctamente'
          })
          this.fetchAcopio()
        } catch (e) {
          this.$q.notify({
            type: 'negative',
            message: e.response?.data?.message || 'No se pudo procesar el acopio'
          })
        }
      })
    },

    /**
     * Imprimir Acta de Conformidad
     * Creado: 2025-11-21
     * Placeholder: Se implementara en siguiente fase
     * @param {Object} cosecha - Objeto cosecha
     */
    imprimirActaConformidad(cosecha) {
      this.$q.notify({
        type: 'info',
        message: 'Funcionalidad en desarrollo - Acta de Conformidad',
        caption: 'Se implementara en la siguiente fase'
      })
    },

    /**
     * Imprimir Acta de Entrega
     * Creado: 2025-11-21
     * Placeholder: Se implementara en siguiente fase
     * @param {Object} cosecha - Objeto cosecha
     */
    imprimirActaEntrega(cosecha) {
      this.$q.notify({
        type: 'info',
        message: 'Funcionalidad en desarrollo - Acta de Entrega',
        caption: 'Se implementara en la siguiente fase'
      })
    },

    /**
     * Imprimir Recibo de Compra
     * Creado: 2025-11-21
     * Placeholder: Se implementara en siguiente fase
     * @param {Object} cosecha - Objeto cosecha
     */
    imprimirRecibo(cosecha) {
      this.$q.notify({
        type: 'info',
        message: 'Funcionalidad en desarrollo - Recibo de Compra',
        caption: 'Se implementara en la siguiente fase'
      })
    },

    /**
     * Abrir modal de nuevo acopio
     * Creado: 2025-11-21
     * Precarga: fecha actual, productor actual, apiarios del productor, Miel de Abeja
     */
    async abrirModalNuevoAcopio() {
      if (!this.productor?.id) {
        this.$q.notify({
          type: 'warning',
          message: 'No se pudo identificar el productor'
        })
        return
      }

      // Resetear formulario con valores por defecto
      // 2025-11-21: Generar número de acta único automáticamente
      const now = new Date()
      const year = now.getFullYear()
      const timestamp = now.getTime().toString().slice(-6) // Últimos 6 dígitos del timestamp
      const numActaGenerado = `${timestamp}/${year}`

      this.formAcopio = {
        fecha_cosecha: now.toISOString().split('T')[0], // Fecha actual
        num_acta: numActaGenerado, // Número de acta generado automáticamente
        apiario_id: null,
        producto_id: null,
        cantidad_kg: null,
        precio_compra: 32,
        humedad: null,
        temperatura_almacenaje: null,
        procedencia: '',
        tipo_envase: 'BALDE',
        observaciones: '',
        estado: 'BUENO'
      }

      // Cargar apiarios del productor
      await this.cargarApiariosModal()

      // Cargar productos y preseleccionar "Miel de Abeja"
      await this.cargarProductos()

      // Abrir modal
      this.dialogNuevoAcopio = true
    },

    /**
     * Cargar apiarios del productor actual
     * Creado: 2025-11-21
     * Si solo hay un apiario, lo selecciona automaticamente
     */
    async cargarApiariosModal() {
      if (!this.productor?.id) return

      this.loadingApiariosModal = true
      try {
        const { data } = await this.$axios.get('/apiarios', {
          params: {
            productor_id: this.productor.id,
            paginate: false
          }
        })

        this.apiarioOptionsModal = (data || []).map(a => ({
          value: a.id,
          label: a.nombre_cip
            ? `${a.nombre_cip} — ${a?.municipio?.nombre_municipio ?? ''}`
            : `Apiario #${a.id} — ${a?.municipio?.nombre_municipio ?? ''}`
        }))

        // Si solo hay un apiario, seleccionarlo automaticamente
        if (this.apiarioOptionsModal.length === 1) {
          this.formAcopio.apiario_id = this.apiarioOptionsModal[0].value
        }
      } catch (error) {
        console.error('Error cargando apiarios:', error)
        this.$q.notify({
          type: 'negative',
          message: 'No se pudieron cargar los apiarios del productor'
        })
      } finally {
        this.loadingApiariosModal = false
      }
    },

    /**
     * Cargar productos tipo Materia Prima (tipo_id = 1)
     * Creado: 2025-11-21
     * Preselecciona "Miel de Abeja" automaticamente
     */
    async cargarProductos() {
      this.loadingProductos = true
      try {
        const { data } = await this.$axios.get('/productos/tipo/1')

        this.productosOptions = (data?.data || data || []).map(p => ({
          value: p.id,
          label: p.nombre_producto
        }))

        // Preseleccionar "Miel de Abeja" (buscar en label que incluya 'miel')
        const mielAbeja = this.productosOptions.find(p =>
          p.label.toLowerCase().includes('miel')
        )
        if (mielAbeja) {
          this.formAcopio.producto_id = mielAbeja.value
        }
      } catch (error) {
        console.error('Error cargando productos:', error)
        this.$q.notify({
          type: 'negative',
          message: 'No se pudieron cargar los productos'
        })
      } finally {
        this.loadingProductos = false
      }
    },

    /**
     * Guardar nuevo acopio o actualizar existente
     * Creado: 2025-11-21
     * Crea o actualiza el acopio via POST/PUT /acopio-cosechas y recarga la lista
     * Actualizado: 2025-11-21 - Agregado soporte para edición
     */
    async guardarAcopio() {
      // Validar formulario
      const isValid = await this.$refs.formAcopioRef.validate()
      if (!isValid) return

      this.loadingGuardar = true
      const isEdit = !!this.formAcopio.id

      try {
        let data
        if (isEdit) {
          // Modo edición: PUT
          const response = await this.$axios.put(`/acopio-cosechas/${this.formAcopio.id}`, this.formAcopio)
          data = response.data
        } else {
          // Modo creación: POST
          const response = await this.$axios.post('/acopio-cosechas', this.formAcopio)
          data = response.data
        }

        // Cerrar modal primero
        this.dialogNuevoAcopio = false

        // Mostrar notificación de éxito
        this.$q.notify({
          type: 'positive',
          message: isEdit ? 'Acopio actualizado exitosamente' : 'Acopio registrado exitosamente',
          caption: `Acta: ${data.num_acta || this.formAcopio.num_acta}`,
          timeout: 3000
        })

        // Recargar lista de acopios con un pequeño delay para asegurar que la transacción se completó
        setTimeout(async () => {
          try {
            await this.fetchAcopio()
          } catch (reloadError) {
            console.warn('Error recargando lista de acopios:', reloadError)
            // Intentar recargar una vez más si falla
            setTimeout(() => {
              this.fetchAcopio()
            }, 1000)
          }
        }, 500)

      } catch (error) {
        console.error('Error guardando acopio:', error)

        // Verificar si el error es por duplicado de num_acta
        const errorMsg = error.response?.data?.message || ''
        if (errorMsg.includes('num_acta')) {
          this.$q.notify({
            type: 'warning',
            message: 'El número de acta ya existe',
            caption: 'Por favor, modifique el número de acta e intente nuevamente',
            timeout: 4000
          })
        } else {
          this.$q.notify({
            type: 'negative',
            message: isEdit ? 'Error al actualizar el acopio' : 'Error al guardar el acopio',
            caption: errorMsg || 'Intente nuevamente',
            timeout: 3000
          })
        }
      } finally {
        this.loadingGuardar = false
      }
    },

    /**
     * Editar acopio existente
     * Creado: 2025-11-21
     * Abre el modal con los datos del acopio para edición
     */
    async editarAcopio(cosecha) {
      if (!cosecha?.id) {
        this.$q.notify({
          type: 'warning',
          message: 'No se pudo identificar el acopio'
        })
        return
      }

      // Cargar apiarios y productos
      await this.cargarApiariosModal()
      await this.cargarProductos()

      // Cargar datos del acopio en el formulario
      this.formAcopio = {
        id: cosecha.id, // Importante: agregar ID para modo edición
        fecha_cosecha: cosecha.fecha_cosecha,
        num_acta: cosecha.num_acta,
        apiario_id: cosecha.apiario_id,
        producto_id: cosecha.producto_id,
        cantidad_kg: cosecha.cantidad_kg,
        precio_compra: cosecha.precio_compra,
        humedad: cosecha.humedad,
        temperatura_almacenaje: cosecha.temperatura_almacenaje,
        procedencia: cosecha.procedencia,
        tipo_envase: cosecha.tipo_envase,
        observaciones: cosecha.observaciones,
        estado: cosecha.estado
      }

      // Abrir modal en modo edición
      this.dialogNuevoAcopio = true
    },

    /**
     * Eliminar acopio
     * Creado: 2025-11-21
     * Solicita confirmación y elimina el acopio
     */
    eliminarAcopio(cosecha) {
      if (!cosecha?.id) {
        this.$q.notify({
          type: 'warning',
          message: 'No se pudo identificar el acopio'
        })
        return
      }

      this.$q.dialog({
        title: 'Confirmar eliminación',
        message: `¿Está seguro de eliminar el acopio con Acta ${cosecha.num_acta}?`,
        html: true,
        ok: {
          push: true,
          color: 'negative',
          label: 'Eliminar'
        },
        cancel: {
          push: true,
          color: 'grey-7',
          label: 'Cancelar'
        },
        persistent: true
      }).onOk(async () => {
        try {
          await this.$axios.delete(`/acopio-cosechas/${cosecha.id}`)

          this.$q.notify({
            type: 'positive',
            message: 'Acopio eliminado exitosamente',
            caption: `Acta: ${cosecha.num_acta}`,
            timeout: 3000
          })

          // Recargar lista
          await this.fetchAcopio()
        } catch (error) {
          console.error('Error eliminando acopio:', error)
          this.$q.notify({
            type: 'negative',
            message: 'Error al eliminar el acopio',
            caption: error.response?.data?.message || 'Intente nuevamente',
            timeout: 3000
          })
        }
      })
    }
  }
}
</script>
