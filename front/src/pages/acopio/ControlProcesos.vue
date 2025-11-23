<template>
  <q-page class="q-pa-md">
    <div class="row items-center q-mb-md">
      <div class="text-h5">Control de Procesos</div>
      <q-space />
      <q-btn color="primary" label="Nuevo Proceso" icon="add" @click="abrirDialogoNuevo" />
    </div>

    <q-card flat bordered>
      <q-card-section>
        <div class="row q-gutter-md">
          <q-select v-model="filtros.estado" :options="estadosOptions" label="Estado" 
            clearable dense style="min-width: 200px" @update:model-value="buscar" />
          <q-input v-model="filtros.fecha_inicio" type="date" label="Fecha Inicio" 
            dense clearable @update:model-value="buscar" />
          <q-input v-model="filtros.fecha_fin" type="date" label="Fecha Fin" 
            dense clearable @update:model-value="buscar" />
          <q-btn color="primary" label="Buscar" icon="search" @click="buscar" />
        </div>
      </q-card-section>

      <q-table
        :rows="procesos"
        :columns="columns"
        row-key="id"
        :loading="loading"
        flat
        :pagination="pagination"
        @request="onRequest"
      >
        <template v-slot:body-cell-estado="props">
          <q-td :props="props">
            <q-badge :color="getColorEstado(props.row.estado)">
              {{ props.row.estado }}
            </q-badge>
          </q-td>
        </template>

        <template v-slot:body-cell-acciones="props">
          <q-td :props="props">
            <q-btn flat dense icon="visibility" color="primary" 
              @click="verDetalle(props.row)" size="sm">
              <q-tooltip>Ver detalle</q-tooltip>
            </q-btn>
            <q-btn v-if="props.row.estado === 'EN_PROCESO'" flat dense icon="check_circle" 
              color="positive" @click="abrirDialogoFinalizar(props.row)" size="sm">
              <q-tooltip>Finalizar proceso</q-tooltip>
            </q-btn>
            <q-btn v-if="props.row.estado === 'EN_PROCESO'" flat dense icon="delete" 
              color="negative" @click="eliminar(props.row)" size="sm">
              <q-tooltip>Eliminar</q-tooltip>
            </q-btn>
          </q-td>
        </template>
      </q-table>
    </q-card>

    <q-dialog v-model="dialogoNuevo" persistent>
      <q-card style="min-width: 600px">
        <q-card-section class="row items-center">
          <div class="text-h6">Nuevo Proceso Productivo</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <q-form @submit="guardarProceso">
            <q-select
              v-model="form.acopio_ids"
              :options="acopiosDisponibles"
              option-value="id"
              :option-label="opt => `${opt.apiario?.productor?.nombre_completo || 'N/A'} - ${opt.cantidad_kg} kg - ${opt.fecha_cosecha}`"
              label="Acopios a procesar"
              multiple
              use-chips
              :rules="[val => val && val.length > 0 || 'Seleccione al menos un acopio']"
              @filter="filterAcopios"
            />

            <q-select
              v-model="form.tanque_id"
              :options="tanques"
              option-value="id"
              :option-label="opt => `${opt.nombre_tanque} - ${opt.planta?.nombre_planta || ''}`"
              label="Tanque de destino"
              :rules="[val => !!val || 'Requerido']"
              class="q-mt-md"
            />

            <q-select
              v-model="form.producto_id"
              :options="productos"
              option-value="id"
              option-label="nombre_producto"
              label="Producto"
              :rules="[val => !!val || 'Requerido']"
              class="q-mt-md"
            />

            <div class="row q-col-gutter-md q-mt-sm">
              <div class="col-6">
                <q-input
                  v-model="form.temperatura_proceso"
                  type="number"
                  label="Temperatura de proceso (C)"
                  step="0.1"
                />
              </div>
              <div class="col-6">
                <q-input
                  v-model="form.tiempo_proceso_horas"
                  type="number"
                  label="Tiempo estimado (horas)"
                  step="0.5"
                />
              </div>
            </div>

            <q-input
              v-model="form.metodo_proceso"
              label="Metodo de proceso"
              class="q-mt-md"
            />

            <q-input
              v-model="form.observaciones"
              label="Observaciones"
              type="textarea"
              rows="2"
              class="q-mt-md"
            />

            <div class="q-mt-md text-right">
              <q-btn label="Cancelar" flat @click="dialogoNuevo = false" class="q-mr-sm" />
              <q-btn label="Guardar" type="submit" color="primary" :loading="guardando" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <q-dialog v-model="dialogoFinalizar" persistent>
      <q-card style="min-width: 500px">
        <q-card-section class="row items-center">
          <div class="text-h6">Finalizar Proceso</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <q-form @submit="finalizarProceso">
            <div class="text-body2 q-mb-md">
              Cantidad de entrada: <strong>{{ procesoActual?.cantidad_entrada_kg }} kg</strong>
            </div>

            <q-input
              v-model="formFinalizar.cantidad_salida_kg"
              type="number"
              label="Cantidad de salida (kg)"
              step="0.01"
              :rules="[
                val => !!val || 'Requerido',
                val => val > 0 || 'Debe ser mayor a 0',
                val => val <= (procesoActual?.cantidad_entrada_kg || 0) || 'No puede exceder la entrada'
              ]"
            />

            <q-input
              v-model="formFinalizar.humedad_final"
              type="number"
              label="Humedad final (%)"
              step="0.1"
              class="q-mt-md"
            />

            <div class="q-mt-md text-right">
              <q-btn label="Cancelar" flat @click="dialogoFinalizar = false" class="q-mr-sm" />
              <q-btn label="Finalizar" type="submit" color="primary" :loading="guardando" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
export default {
  name: 'ControlProcesos',
  data() {
    return {
      procesos: [],
      loading: false,
      pagination: {
        page: 1,
        rowsPerPage: 20,
        rowsNumber: 0
      },
      filtros: {
        estado: null,
        fecha_inicio: null,
        fecha_fin: null
      },
      estadosOptions: ['EN_PROCESO', 'FINALIZADO'],
      dialogoNuevo: false,
      dialogoFinalizar: false,
      guardando: false,
      form: {
        acopio_ids: [],
        tanque_id: null,
        producto_id: null,
        temperatura_proceso: null,
        tiempo_proceso_horas: null,
        metodo_proceso: null,
        observaciones: null
      },
      formFinalizar: {
        cantidad_salida_kg: null,
        humedad_final: null
      },
      procesoActual: null,
      acopiosDisponibles: [],
      tanques: [],
      productos: [],
      columns: [
        { name: 'id', label: 'ID', field: 'id', align: 'left', sortable: true },
        { name: 'fecha_proceso', label: 'Fecha', field: 'fecha_proceso', align: 'left', 
          format: val => val ? new Date(val).toLocaleDateString() : '' },
        { name: 'tanque', label: 'Tanque', field: row => row.tanque?.nombre_tanque || '', align: 'left' },
        { name: 'cantidad_entrada', label: 'Entrada (kg)', field: 'cantidad_entrada_kg', align: 'right', 
          format: val => val ? Number(val).toFixed(2) : '0.00' },
        { name: 'cantidad_salida', label: 'Salida (kg)', field: 'cantidad_salida_kg', align: 'right', 
          format: val => val ? Number(val).toFixed(2) : '0.00' },
        { name: 'merma', label: 'Merma (%)', field: 'merma_porcentaje', align: 'right', 
          format: val => val ? Number(val).toFixed(2) : '0.00' },
        { name: 'estado', label: 'Estado', field: 'estado', align: 'center' },
        { name: 'acciones', label: 'Acciones', field: 'acciones', align: 'center' }
      ]
    }
  },
  mounted() {
    this.buscar()
    this.cargarCatalogos()
  },
  methods: {
    async buscar() {
      this.loading = true
      try {
        const params = {
          page: this.pagination.page,
          per_page: this.pagination.rowsPerPage,
          ...this.filtros
        }
        const { data } = await this.$axios.get('/control-procesos', { params })
        this.procesos = data.data
        this.pagination.rowsNumber = data.total
      } catch (e) {
        this.$q.notify({ type: 'negative', message: e.response?.data?.message || 'Error al cargar procesos' })
      } finally {
        this.loading = false
      }
    },
    async onRequest(props) {
      this.pagination = props.pagination
      await this.buscar()
    },
    async cargarCatalogos() {
      try {
        const [acopios, tanques, productos] = await Promise.all([
          this.$axios.get('/acopios-disponibles-proceso'),
          this.$axios.get('/tanques'),
          this.$axios.get('/productos')
        ])
        this.acopiosDisponibles = acopios.data
        this.tanques = tanques.data
        this.productos = productos.data
      } catch (e) {
        this.$q.notify({ type: 'negative', message: 'Error al cargar catalogos' })
      }
    },
    filterAcopios(val, update) {
      update(() => {
        if (val === '') {
          this.cargarCatalogos()
        }
      })
    },
    abrirDialogoNuevo() {
      this.form = {
        acopio_ids: [],
        tanque_id: null,
        producto_id: null,
        temperatura_proceso: null,
        tiempo_proceso_horas: null,
        metodo_proceso: null,
        observaciones: null
      }
      this.dialogoNuevo = true
    },
    async guardarProceso() {
      this.guardando = true
      try {
        const payload = {
          ...this.form,
          acopio_ids: this.form.acopio_ids.map(a => a.id)
        }
        await this.$axios.post('/control-procesos', payload)
        this.$q.notify({ type: 'positive', message: 'Proceso creado exitosamente' })
        this.dialogoNuevo = false
        this.buscar()
        this.cargarCatalogos()
      } catch (e) {
        this.$q.notify({ type: 'negative', message: e.response?.data?.message || 'Error al crear proceso' })
      } finally {
        this.guardando = false
      }
    },
    abrirDialogoFinalizar(proceso) {
      this.procesoActual = proceso
      this.formFinalizar = {
        cantidad_salida_kg: proceso.cantidad_entrada_kg,
        humedad_final: null
      }
      this.dialogoFinalizar = true
    },
    async finalizarProceso() {
      this.guardando = true
      try {
        await this.$axios.put(`/control-procesos/${this.procesoActual.id}/finalizar`, this.formFinalizar)
        this.$q.notify({ type: 'positive', message: 'Proceso finalizado exitosamente' })
        this.dialogoFinalizar = false
        this.buscar()
      } catch (e) {
        this.$q.notify({ type: 'negative', message: e.response?.data?.message || 'Error al finalizar proceso' })
      } finally {
        this.guardando = false
      }
    },
    verDetalle(proceso) {
      this.$router.push(`/control-procesos/${proceso.id}`)
    },
    async eliminar(proceso) {
      this.$q.dialog({
        title: 'Confirmar',
        message: 'Esta seguro de eliminar este proceso?',
        cancel: true,
        persistent: true
      }).onOk(async () => {
        try {
          await this.$axios.delete(`/control-procesos/${proceso.id}`)
          this.$q.notify({ type: 'positive', message: 'Proceso eliminado' })
          this.buscar()
        } catch (e) {
          this.$q.notify({ type: 'negative', message: e.response?.data?.message || 'Error al eliminar' })
        }
      })
    },
    getColorEstado(estado) {
      return estado === 'EN_PROCESO' ? 'warning' : 'positive'
    }
  }
}
</script>
