<template>
  <q-page class="q-pa-md">
    <!-- Tarjetas de estadísticas -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-sm-6 col-md-3">
        <q-card flat bordered>
          <q-card-section class="text-center">
            <div class="text-h6 text-primary">{{ stats.total_tanques || 0 }}</div>
            <div class="text-caption text-grey-7">Total Tanques</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-sm-6 col-md-3">
        <q-card flat bordered>
          <q-card-section class="text-center">
            <div class="text-h6 text-positive">{{ stats.operativos || 0 }}</div>
            <div class="text-caption text-grey-7">Operativos</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-sm-6 col-md-3">
        <q-card flat bordered>
          <q-card-section class="text-center">
            <div class="text-h6 text-orange">{{ stats.ocupacion_promedio || 0 }}%</div>
            <div class="text-caption text-grey-7">Ocupación Promedio</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-sm-6 col-md-3">
        <q-card flat bordered>
          <q-card-section class="text-center">
            <div class="text-h6 text-negative">{{ stats.tanques_llenos || 0 }}</div>
            <div class="text-caption text-grey-7">Tanques &gt;90%</div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Tabla principal -->
    <q-table
      :rows="rows"
      :columns="columns"
      row-key="id"
      flat bordered dense wrap-cells
      :rows-per-page-options="[0]"
      title="Tanques de Almacenamiento"
      :loading="loading"
      :filter="filter"
    >
      <template #top-right>
        <div class="row items-center q-gutter-sm">
          <q-select
            v-model="plantaId"
            :options="plantaOptions"
            option-value="id"
            option-label="nombre_planta"
            emit-value map-options
            dense filled clearable
            label="Planta"
            style="min-width: 220px"
            @update:model-value="fetch"
          />
          <q-input
            v-model="filter"
            dense outlined
            placeholder="Buscar por código, nombre"
            @update:model-value="fetchDebounced"
          >
            <template #append><q-icon name="search" /></template>
          </q-input>
          <q-btn color="positive" icon="add_circle_outline" label="Nuevo" no-caps :loading="loading" @click="openCreate"/>
          <q-btn color="primary"  icon="refresh"            label="Actualizar" no-caps :loading="loading" @click="fetch"/>
        </div>
      </template>

      <template #body-cell-planta="props">
        <q-td :props="props">
          {{ props.row.planta?.nombre_planta || '-' }}
        </q-td>
      </template>

      <template #body-cell-capacidad_kg="props">
        <q-td :props="props" class="text-right">
          {{ Number(props.row.capacidad_kg || 0).toFixed(2) }}
        </q-td>
      </template>

      <template #body-cell-ocupacion="props">
        <q-td :props="props">
          <div v-if="props.row.capacidad_kg && props.row.porcentaje_ocupacion !== null" class="row items-center q-gutter-xs">
            <div class="col">
              <q-linear-progress
                :value="props.row.porcentaje_ocupacion / 100"
                :color="getOcupacionColor(props.row.porcentaje_ocupacion)"
                size="18px"
                rounded
              >
                <div class="absolute-full flex flex-center">
                  <q-badge
                    color="transparent"
                    text-color="white"
                    :label="`${props.row.porcentaje_ocupacion.toFixed(1)}%`"
                  />
                </div>
              </q-linear-progress>
            </div>
            <div class="text-caption">
              {{ Number(props.row.ocupacion_actual_kg || 0).toFixed(2) }} kg
            </div>
          </div>
          <div v-else class="text-grey-7 text-caption">
            Sin capacidad configurada
          </div>
        </q-td>
      </template>

      <template #body-cell-estado_operativo="props">
        <q-td :props="props">
          <q-chip
            :color="props.row.estado_operativo === 'OPERATIVO' ? 'positive' : props.row.estado_operativo === 'MANTENIMIENTO' ? 'orange' : 'negative'"
            text-color="white"
            size="sm"
            dense
          >
            {{ props.row.estado_operativo }}
          </q-chip>
        </q-td>
      </template>

      <template #body-cell-actions="props">
        <q-td :props="props" class="text-right">
          <q-btn-dropdown label="Opciones" dense color="primary" no-caps size="10px">
            <q-list>
              <q-item clickable v-ripple @click="openOcupacion(props.row)" v-close-popup>
                <q-item-section avatar><q-icon name="bar_chart" /></q-item-section>
                <q-item-section>Ver Ocupación</q-item-section>
              </q-item>
              <q-item clickable v-ripple @click="openHistorial(props.row)" v-close-popup>
                <q-item-section avatar><q-icon name="history" /></q-item-section>
                <q-item-section>Historial</q-item-section>
              </q-item>
              <q-separator />
              <q-item clickable v-ripple @click="openEdit(props.row)" v-close-popup>
                <q-item-section avatar><q-icon name="edit" /></q-item-section>
                <q-item-section>Editar</q-item-section>
              </q-item>
              <q-item clickable v-ripple @click="onDelete(props.row)" v-close-popup>
                <q-item-section avatar><q-icon name="delete" color="negative" /></q-item-section>
                <q-item-section><span class="text-negative">Eliminar</span></q-item-section>
              </q-item>
            </q-list>
          </q-btn-dropdown>
        </q-td>
      </template>
    </q-table>

    <!-- Diálogo CRUD -->
    <q-dialog v-model="dlg.open" persistent>
      <q-card style="min-width: 560px; max-width: 820px">
        <q-card-section class="text-h6">
          {{ dlg.mode === 'create' ? 'Nuevo Tanque' : 'Editar Tanque' }}
        </q-card-section>
        <q-separator/>
        <q-card-section>
          <q-form @submit.prevent="onSubmit" ref="formRef">
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-4">
                <q-input v-model="form.codigo_tanque" dense filled label="Código tanque *" />
              </div>
              <div class="col-12 col-md-8">
                <q-input v-model="form.nombre_tanque" dense filled label="Nombre tanque *" />
              </div>
              <div class="col-12 col-md-6">
                <q-select
                  v-model="form.planta_id"
                  :options="plantaOptions"
                  option-value="id"
                  option-label="nombre_planta"
                  emit-value map-options
                  dense filled
                  label="Planta *"
                />
              </div>
              <div class="col-12 col-md-6">
                <q-select
                  v-model="form.estado_operativo"
                  :options="['OPERATIVO', 'MANTENIMIENTO', 'FUERA_DE_SERVICIO']"
                  dense filled
                  label="Estado operativo *"
                />
              </div>
              <div class="col-12 col-md-6">
                <q-input
                  v-model.number="form.capacidad_litros"
                  type="number"
                  step="0.01"
                  dense filled
                  label="Capacidad (litros) *"
                />
              </div>
              <div class="col-12 col-md-6">
                <q-input
                  v-model.number="form.capacidad_kg"
                  type="number"
                  step="0.01"
                  dense filled
                  label="Capacidad (kg) *"
                />
              </div>
              <div class="col-12">
                <q-input v-model="form.descripcion" type="textarea" autogrow dense filled label="Descripción" />
              </div>
            </div>
          </q-form>
        </q-card-section>
        <q-separator/>
        <q-card-actions align="right">
          <q-btn flat label="Cancelar" v-close-popup />
          <q-btn color="primary" :loading="saving" :disable="!canSubmit" label="Guardar" @click="onSubmit" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Diálogo Detalle de Ocupación -->
    <q-dialog v-model="dlgOcupacion.open">
      <q-card style="min-width: 600px">
        <q-card-section class="text-h6">
          Detalle de Ocupación - {{ dlgOcupacion.row?.codigo_tanque }}
        </q-card-section>
        <q-separator/>
        <q-card-section>
          <div v-if="loadingOcupacion" class="text-center q-pa-md">
            <q-spinner color="primary" size="3em" />
          </div>
          <div v-else-if="ocupacionData">
            <div class="row q-col-gutter-md q-mb-md">
              <div class="col-6">
                <div class="text-caption text-grey-7">Capacidad Total</div>
                <div class="text-h6">{{ Number(ocupacionData.capacidad_kg || 0).toFixed(2) }} kg</div>
              </div>
              <div class="col-6">
                <div class="text-caption text-grey-7">Ocupación Actual</div>
                <div class="text-h6">{{ Number(ocupacionData.ocupacion_actual_kg || 0).toFixed(2) }} kg</div>
              </div>
              <div class="col-6">
                <div class="text-caption text-grey-7">Disponible</div>
                <div class="text-h6 text-positive">{{ Number(ocupacionData.capacidad_disponible_kg || 0).toFixed(2) }} kg</div>
              </div>
              <div class="col-6">
                <div class="text-caption text-grey-7">Porcentaje</div>
                <div class="text-h6" :class="`text-${getOcupacionColor(ocupacionData.porcentaje_ocupacion)}`">
                  {{ Number(ocupacionData.porcentaje_ocupacion || 0).toFixed(1) }}%
                </div>
              </div>
            </div>

            <q-separator class="q-my-md" />

            <div class="text-subtitle2 q-mb-sm">Procesos en Curso</div>
            <q-list bordered separator dense>
              <q-item v-for="proceso in ocupacionData.procesos_activos" :key="proceso.id">
                <q-item-section>
                  <q-item-label>Proceso #{{ proceso.id }} - {{ proceso.producto?.nombre_producto }}</q-item-label>
                  <q-item-label caption>{{ proceso.fecha_proceso }}</q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-item-label>{{ Number(proceso.cantidad_entrada_kg || 0).toFixed(2) }} kg</q-item-label>
                </q-item-section>
              </q-item>
              <q-item v-if="!ocupacionData.procesos_activos?.length">
                <q-item-section class="text-grey-7 text-center">
                  Sin procesos activos
                </q-item-section>
              </q-item>
            </q-list>

            <div class="text-subtitle2 q-my-sm">Lotes Almacenados</div>
            <q-list bordered separator dense>
              <q-item v-for="lote in ocupacionData.lotes_almacenados" :key="lote.id">
                <q-item-section>
                  <q-item-label>{{ lote.codigo_lote }} - {{ lote.producto?.nombre_producto }}</q-item-label>
                  <q-item-label caption>{{ lote.fecha_produccion }}</q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-item-label>{{ Number(lote.cantidad_kg || 0).toFixed(2) }} kg</q-item-label>
                </q-item-section>
              </q-item>
              <q-item v-if="!ocupacionData.lotes_almacenados?.length">
                <q-item-section class="text-grey-7 text-center">
                  Sin lotes almacenados
                </q-item-section>
              </q-item>
            </q-list>
          </div>
        </q-card-section>
        <q-separator/>
        <q-card-actions align="right">
          <q-btn flat label="Cerrar" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Diálogo Historial de Procesos -->
    <q-dialog v-model="dlgHistorial.open">
      <q-card style="min-width: 700px; max-width: 90vw">
        <q-card-section class="text-h6">
          Historial de Procesos - {{ dlgHistorial.row?.codigo_tanque }}
        </q-card-section>
        <q-separator/>
        <q-card-section>
          <div v-if="loadingHistorial" class="text-center q-pa-md">
            <q-spinner color="primary" size="3em" />
          </div>
          <q-table
            v-else
            :rows="historialData"
            :columns="historialColumns"
            row-key="id"
            flat bordered dense
            :rows-per-page-options="[10]"
          >
            <template #body-cell-estado="props">
              <q-td :props="props">
                <q-chip
                  :color="props.row.estado === 'FINALIZADO' ? 'positive' : 'orange'"
                  text-color="white"
                  size="sm"
                  dense
                >
                  {{ props.row.estado }}
                </q-chip>
              </q-td>
            </template>
          </q-table>
        </q-card-section>
        <q-separator/>
        <q-card-actions align="right">
          <q-btn flat label="Cerrar" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
export default {
  name: 'TanquesPage',
  data () {
    return {
      loading: false,
      rows: [],
      stats: {
        total_tanques: 0,
        operativos: 0,
        ocupacion_promedio: 0,
        tanques_llenos: 0
      },
      columns: [
        { name: 'actions',           label: 'Acciones',          align: 'right' },
        { name: 'codigo_tanque',     label: 'Código',            align: 'left',  field: 'codigo_tanque' },
        { name: 'nombre_tanque',     label: 'Nombre',            align: 'left',  field: 'nombre_tanque' },
        { name: 'planta',            label: 'Planta',            align: 'left' },
        { name: 'capacidad_kg',      label: 'Capacidad (kg)',    align: 'right', field: 'capacidad_kg' },
        { name: 'ocupacion',         label: 'Ocupación',         align: 'left' },
        { name: 'estado_operativo',  label: 'Estado',            align: 'left',  field: 'estado_operativo' },
      ],
      filter: '',
      plantaId: null,
      plantaOptions: [],

      // CRUD
      dlg: { open: false, mode: 'create', row: null },
      form: {
        codigo_tanque: '',
        nombre_tanque: '',
        planta_id: null,
        capacidad_litros: 0,
        capacidad_kg: 0,
        estado_operativo: 'OPERATIVO',
        descripcion: ''
      },
      saving: false,

      // Ocupación
      dlgOcupacion: { open: false, row: null },
      loadingOcupacion: false,
      ocupacionData: null,

      // Historial
      dlgHistorial: { open: false, row: null },
      loadingHistorial: false,
      historialData: [],
      historialColumns: [
        { name: 'id', label: '#', align: 'left', field: 'id' },
        { name: 'fecha_proceso', label: 'Fecha', align: 'left', field: 'fecha_proceso' },
        { name: 'producto', label: 'Producto', align: 'left', field: row => row.producto?.nombre_producto || '-' },
        { name: 'cantidad_entrada_kg', label: 'Entrada (kg)', align: 'right', field: 'cantidad_entrada_kg', format: v => Number(v || 0).toFixed(2) },
        { name: 'cantidad_salida_kg', label: 'Salida (kg)', align: 'right', field: 'cantidad_salida_kg', format: v => Number(v || 0).toFixed(2) },
        { name: 'estado', label: 'Estado', align: 'left', field: 'estado' },
      ]
    }
  },
  computed: {
    canSubmit () {
      const f = this.form
      return !!(f.codigo_tanque && f.nombre_tanque && f.planta_id && f.capacidad_kg > 0 && f.estado_operativo)
    }
  },
  mounted () {
    this.fetchPlantas()
    this.fetchEstadisticas()
    this.fetch()
  },
  methods: {
    async fetch () {
      this.loading = true
      try {
        const params = {}
        if (this.plantaId) params.planta_id = this.plantaId
        if (this.filter) params.search = this.filter

        const { data } = await this.$axios.get('/tanques', { params })
        this.rows = data
      } catch (e) {
        this.$q.notify({ type: 'negative', message: 'No se pudieron cargar los tanques' })
      } finally { this.loading = false }
    },

    fetchDebounced () {
      clearTimeout(this._fetchTimer)
      this._fetchTimer = setTimeout(() => this.fetch(), 500)
    },

    async fetchPlantas () {
      try {
        const { data } = await this.$axios.get('/plantas')
        this.plantaOptions = data
      } catch (e) {
        this.$q.notify({ type: 'warning', message: 'No se pudieron cargar plantas' })
      }
    },

    async fetchEstadisticas () {
      try {
        const { data } = await this.$axios.get('/tanques/estadisticas')
        this.stats = data
      } catch (e) {
        console.error('Error cargando estadísticas:', e)
      }
    },

    getOcupacionColor (porcentaje) {
      if (porcentaje >= 90) return 'negative'
      if (porcentaje >= 70) return 'orange'
      return 'positive'
    },

    openCreate () {
      this.dlg = { open: true, mode: 'create', row: null }
      this.form = {
        codigo_tanque: '',
        nombre_tanque: '',
        planta_id: null,
        capacidad_litros: 0,
        capacidad_kg: 0,
        estado_operativo: 'OPERATIVO',
        descripcion: ''
      }
    },

    openEdit (row) {
      this.dlg = { open: true, mode: 'edit', row }
      this.form = {
        codigo_tanque: row.codigo_tanque || '',
        nombre_tanque: row.nombre_tanque || '',
        planta_id: row.planta_id || row.planta?.id || null,
        capacidad_litros: row.capacidad_litros || 0,
        capacidad_kg: row.capacidad_kg || 0,
        estado_operativo: row.estado_operativo || 'OPERATIVO',
        descripcion: row.descripcion || ''
      }
    },

    async onSubmit () {
      if (!this.canSubmit) return

      this.saving = true
      try {
        if (this.dlg.mode === 'create') {
          await this.$axios.post('/tanques', this.form)
        } else {
          await this.$axios.put(`/tanques/${this.dlg.row.id}`, this.form)
        }
        this.$q.notify({ type: 'positive', message: 'Guardado' })
        this.dlg.open = false
        await this.fetch()
        await this.fetchEstadisticas()
      } catch (e) {
        this.$q.notify({ type: 'negative', message: e.response?.data?.message || 'No se pudo guardar' })
      } finally { this.saving = false }
    },

    async onDelete (row) {
      this.$q.dialog({
        title: 'Eliminar',
        message: `¿Eliminar el tanque ${row.nombre_tanque}?`,
        cancel: true, persistent: true
      }).onOk(async () => {
        try {
          await this.$axios.delete(`/tanques/${row.id}`)
          this.$q.notify({ type: 'positive', message: 'Eliminado' })
          await this.fetch()
          await this.fetchEstadisticas()
        } catch (e) {
          this.$q.notify({ type: 'negative', message: e.response?.data?.message || 'No se pudo eliminar' })
        }
      })
    },

    async openOcupacion (row) {
      this.dlgOcupacion = { open: true, row }
      this.loadingOcupacion = true
      this.ocupacionData = null
      try {
        const { data } = await this.$axios.get(`/tanques/${row.id}/ocupacion`)
        this.ocupacionData = data
      } catch (e) {
        this.$q.notify({ type: 'negative', message: 'No se pudo cargar detalle de ocupación' })
      } finally { this.loadingOcupacion = false }
    },

    async openHistorial (row) {
      this.dlgHistorial = { open: true, row }
      this.loadingHistorial = true
      this.historialData = []
      try {
        const { data } = await this.$axios.get(`/tanques/${row.id}/historial`)
        this.historialData = data
      } catch (e) {
        this.$q.notify({ type: 'negative', message: 'No se pudo cargar historial' })
      } finally { this.loadingHistorial = false }
    }
  }
}
</script>

<style scoped>
/* Ajustes opcionales */
</style>
