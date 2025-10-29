<!-- src/pages/acopio/tabs/CosechaLotes.vue -->
<template>
  <div class="q-pa-md">
    <q-card flat bordered>
      <q-card-section class="row items-center q-gutter-sm">
        <div class="text-h6">Lotes</div>
        <q-space/>
        <q-chip square color="primary" text-color="white">
          Capacidad: {{ fmt(capacidadKg) }} kg
        </q-chip>
        <q-chip square color="orange" text-color="white">
          Asignado: {{ fmt(asignadoKg) }} kg
        </q-chip>
        <q-chip square :color="restanteKg > 0 ? 'green' : 'negative'" text-color="white">
          Restante: {{ fmt(restanteKg) }} kg
        </q-chip>
        <q-btn
          color="primary"
          :disable="restanteKg <= 0"
          icon="add"
          label="Nuevo Lote"
          no-caps
          @click="openCreate"
        />
      </q-card-section>

      <q-separator/>

      <q-table
        flat bordered dense wrap-cells
        :rows="rows" :columns="columns"
        row-key="id"
        :loading="loading"
        :rows-per-page-options="[0]"
      >
        <template #body-cell-actions="props">
          <q-td :props="props" class="text-right">
<!--            <q-btn dense flat icon="edit" @click="openEdit(props.row)"/>-->
<!--            <q-btn dense flat icon="delete" color="negative" @click="onDelete(props.row)"/>-->
            <q-btn-dropdown label="Opciones" dense size="10px" color="primary" no-caps>
              <q-list>
                <q-item clickable v-ripple @click="openEdit(props.row)" v-close-popup>
                  <q-item-section avatar>
                    <q-icon name="edit"/>
                  </q-item-section>
                  <q-item-section>Editar</q-item-section>
                </q-item>
                <q-item clickable v-ripple @click="onDelete(props.row)" v-close-popup>
                  <q-item-section avatar>
                    <q-icon name="delete" color="negative"/>
                  </q-item-section>
                  <q-item-section class="text-negative">Eliminar</q-item-section>
                </q-item>
              </q-list>
            </q-btn-dropdown>
          </q-td>
        </template>
      </q-table>
    </q-card>

    <q-dialog v-model="dlg.open" persistent>
      <q-card style="min-width: 560px; max-width: 760px">
        <q-card-section class="text-h6">
          {{ dlg.mode === 'create' ? 'Crear Lote' : 'Editar Lote' }}
        </q-card-section>
        <q-separator/>
        <q-card-section>
          <q-form @submit.prevent="onSubmit" ref="formRef">
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-6">
                <q-select
                  v-model="form.producto_id"
                  :options="productoOptions"
                  option-value="id"
                  option-label="nombre"
                  emit-value map-options
                  filled dense
                  label="Producto "
                  :loading="loadingProductos"
                />
<!--                <pre>{{form.producto_id}}</pre>-->
              </div>
              <div class="col-12 col-md-6">
                <q-select
                  v-model="form.tanque_id"
                  :options="tanqueOptions"
                  option-value="id"
                  option-label="nombre"
                  emit-value map-options
                  filled dense
                  label="Tanque"
                  :loading="loadingTanques"
                />
              </div>
              <div class="col-12 col-md-6">
                <q-input
                  v-model.number="form.cantidad_kg"
                  type="number"
                  min="0.01" step="0.01"
                  filled dense
                  label="Cantidad (kg)"
                  @update:model-value="recalcPreview"
                />
                <div class="text-caption text-grey-7 q-mt-xs">
                  Disponible para asignar:
                  <b>{{ fmt(disponibleParaEsteFormulario) }} kg</b>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <q-input v-model="form.codigo_lote" filled dense label="Código de lote (opcional)"/>
              </div>
              <div class="col-12 col-md-6">
                <q-input v-model="form.fecha_envasado" type="date" filled dense label="Fecha envasado"/>
              </div>
              <div class="col-12 col-md-6">
                <q-input v-model="form.fecha_caducidad" type="date" filled dense label="Fecha caducidad"/>
              </div>
              <div class="col-12">
                <q-input v-model="form.tipo_envase" filled dense label="Tipo de envase (vidrio, sachet, etc.)"/>
              </div>
            </div>
          </q-form>
        </q-card-section>
        <q-separator/>
        <q-card-actions align="right">
          <q-btn flat label="Cancelar" v-close-popup/>
          <q-btn
            color="primary"
            :loading="saving"
            :disable="!canSubmit"
            label="Guardar"
            @click="onSubmit"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script>
export default {
  name: 'CosechaLotes',
  props: {
    cosecha: { type: Object, required: true }
  },
  emits: ['updated'],
  data () {
    return {
      loading: false,
      rows: [],
      meta: { asignado_kg: 0, capacidad_kg: 0, restante_kg: 0 },
      columns: [
        { name: 'actions', label: 'Opciones', field: 'id', align: 'right' },
        { name: 'codigo_lote', label: 'Código', field: 'codigo_lote', align: 'left' },
        { name: 'producto', label: 'Producto', field: r => r.producto?.nombre_producto, align: 'left' },
        { name: 'tanque', label: 'Tanque', field: r => r.tanque?.nombre_tanque, align: 'left' },
        {
          name: 'cantidad_kg', label: 'Kg', field: 'cantidad_kg', align: 'right',
          format: v => Number(v).toFixed(2)
        },
        { name: 'fecha_envasado', label: 'Envasado', field: 'fecha_envasado', align: 'left' },
        { name: 'fecha_caducidad', label: 'Vence', field: 'fecha_caducidad', align: 'left' },
        { name: 'tipo_envase', label: 'Envase', field: 'tipo_envase', align: 'left' },
      ],

      // diálogo
      dlg: { open: false, mode: 'create', row: null },
      form: {
        producto_id: null,
        tanque_id: null,
        cantidad_kg: null,
        codigo_lote: '',
        fecha_envasado: '',
        fecha_caducidad: '',
        tipo_envase: ''
      },
      saving: false,

      // selects
      productoOptions: [],
      tanqueOptions: [],
      loadingProductos: false,
      loadingTanques: false
    }
  },
  computed: {
    asignadoKg () { return Number(this.meta.asignado_kg || 0) },
    capacidadKg () { return Number(this.meta.capacidad_kg || 0) },
    restanteKg () { return Number(this.meta.restante_kg || 0) },

    // En edición, permitimos usar el espacio del propio registro
    disponibleParaEsteFormulario () {
      const actual = this.dlg.mode === 'edit' ? Number(this.dlg.row?.cantidad_kg || 0) : 0
      return this.restanteKg + actual
    },
    canSubmit () {
      const okBase = this.form.producto_id && this.form.tanque_id && Number(this.form.cantidad_kg) > 0
      const noExcede = Number(this.form.cantidad_kg || 0) <= this.disponibleParaEsteFormulario
      return okBase && noExcede
    }
  },
  watch: {
    'cosecha.id': {
      immediate: true,
      handler () {
        if (this.cosecha?.id) this.fetchAll()
      }
    }
  },
  methods: {
    fmt (v) { return Number(v || 0).toFixed(2) },

    async fetchAll () {
      await Promise.all([this.fetchLotes(), this.fetchProductos(), this.fetchTanques()])
    },

    async fetchLotes () {
      this.loading = true
      try {
        const { data } = await this.$axios.get(`/acopio-cosechas/${this.cosecha.id}/lotes`)
        this.rows = data.rows
        this.meta = data.meta
      } catch (e) {
        this.$q.notify({ type: 'negative', message: e.response?.data?.message || 'Error al listar lotes' })
      } finally {
        this.loading = false
      }
    },

    async fetchProductos () {
      this.loadingProductos = true
      try {
        // productos/tipo/{tipo}
        const { data } = await this.$axios.get('/productos/tipo/3')
        this.productoOptions = data.map(p => ({
          id: p.id,
          nombre: `${p.nombre_producto} (${p.codigo_producto})`
        }))
      } catch (e) {
        this.$q.notify({ type: 'warning', message: 'No se pudieron cargar productos' })
      } finally {
        this.loadingProductos = false
      }
    },

    async fetchTanques () {
      this.loadingTanques = true
      try {
        // Ajusta si tu endpoint difiere
        const { data } = await this.$axios.get('/tanques')
        this.tanqueOptions = data.map(t => ({ id: t.id, nombre: t.nombre_tanque }))
      } catch (e) {
        this.$q.notify({ type: 'warning', message: 'No se pudieron cargar tanques' })
      } finally {
        this.loadingTanques = false
      }
    },

    openCreate () {
      this.dlg = { open: true, mode: 'create', row: null }
      this.form = {
        producto_id: null,
        tanque_id: null,
        cantidad_kg: null,
        codigo_lote: '',
        fecha_envasado: '',
        fecha_caducidad: '',
        tipo_envase: ''
      }
    },

    openEdit (row) {
      this.dlg = { open: true, mode: 'edit', row }
      this.form = {
        producto_id: row.producto_id,
        tanque_id: row.tanque_id,
        cantidad_kg: Number(row.cantidad_kg),
        codigo_lote: row.codigo_lote || '',
        fecha_envasado: row.fecha_envasado || '',
        fecha_caducidad: row.fecha_caducidad || '',
        tipo_envase: row.tipo_envase || ''
      }
    },

    recalcPreview () {
      // hook por si luego haces cálculos/validaciones en vivo
    },

    async onSubmit () {
      if (!this.canSubmit) return
      this.saving = true
      try {
        if (this.dlg.mode === 'create') {
          await this.$axios.post(`/acopio-cosechas/${this.cosecha.id}/lotes`, this.form)
        } else {
          await this.$axios.put(`/lotes/${this.dlg.row.id}`, this.form)
        }
        this.$q.notify({ type: 'positive', message: 'Guardado' })
        this.dlg.open = false
        await this.fetchLotes()
        this.$emit('updated')
      } catch (e) {
        const msg = e.response?.data?.message || 'Error al guardar'
        const restante = e.response?.data?.restante
        this.$q.notify({
          type: 'negative',
          message: restante !== undefined ? `${msg}. Restante: ${this.fmt(restante)} kg` : msg
        })
      } finally {
        this.saving = false
      }
    },

    async onDelete (row) {
      this.$q.dialog({
        title: 'Eliminar',
        message: `¿Eliminar el lote ${row.codigo_lote || row.id}?`,
        cancel: true,
        persistent: true
      }).onOk(async () => {
        try {
          await this.$axios.delete(`/lotes/${row.id}`)
          this.$q.notify({ type: 'positive', message: 'Eliminado' })
          await this.fetchLotes()
          this.$emit('updated')
        } catch (e) {
          this.$q.notify({ type: 'negative', message: e.response?.data?.message || 'No se pudo eliminar' })
        }
      })
    }
  }
}
</script>

<style scoped>
/* Opcional: ajustes finos de tabla/chips si quieres */
</style>
