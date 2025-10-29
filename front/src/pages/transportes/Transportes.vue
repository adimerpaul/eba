<template>
  <q-page class="q-pa-md">
    <q-table
      :rows="rows"
      :columns="columns"
      row-key="id"
      flat bordered dense wrap-cells
      :rows-per-page-options="[0]"
      title="Transportes"
      :loading="loading"
      :filter="filter"
    >
      <template #top-right>
        <div class="row items-center q-gutter-sm">
          <q-input
            v-model="filter"
            dense outlined
            placeholder="Buscar por empresa, placa o responsable"
            @update:model-value="fetchDebounced"
          >
            <template #append><q-icon name="search" /></template>
          </q-input>
          <q-btn color="positive" icon="add_circle_outline" label="Nuevo" no-caps :loading="loading" @click="openCreate"/>
          <q-btn color="primary"  icon="refresh"            label="Actualizar" no-caps :loading="loading" @click="fetch"/>
        </div>
      </template>

      <template #body-cell-actions="props">
        <q-td :props="props" class="text-right">
          <q-btn-dropdown label="Opciones" dense color="primary" no-caps size="10px">
            <q-list>
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

    <!-- Diálogo crear/editar -->
    <q-dialog v-model="dlg.open" persistent>
      <q-card style="min-width: 520px; max-width: 760px">
        <q-card-section class="text-h6">
          {{ dlg.mode === 'create' ? 'Nuevo Transporte' : 'Editar Transporte' }}
        </q-card-section>
        <q-separator/>
        <q-card-section>
          <q-form @submit.prevent="onSubmit" ref="formRef">
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-6">
                <q-input v-model="form.empresa" dense filled label="Empresa / Razón social" />
              </div>
              <div class="col-12 col-md-6">
                <q-input v-model="form.placa" dense filled label="Placa" />
              </div>
              <div class="col-12">
                <q-input v-model="form.responsable" dense filled label="Responsable / Conductor" />
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
  </q-page>
</template>

<script>
export default {
  name: 'TransportesPage',
  data () {
    return {
      loading: false,
      rows: [],
      columns: [
        { name: 'actions',     label: 'Acciones',     align: 'right' },
        { name: 'empresa',     label: 'Empresa',      align: 'left',  field: 'empresa' },
        { name: 'placa',       label: 'Placa',        align: 'left',  field: 'placa' },
        { name: 'responsable', label: 'Responsable',  align: 'left',  field: 'responsable' },
      ],
      filter: '',

      // CRUD
      dlg: { open: false, mode: 'create', row: null },
      form: {
        empresa: '',
        placa: '',
        responsable: ''
      },
      saving: false,
    }
  },
  computed: {
    canSubmit () {
      const f = this.form
      // Puedes volver obligatorios los que quieras; por ahora solo empresa o responsable
      return !!(f.empresa || f.responsable)
    }
  },
  mounted () {
    this.fetch()
  },
  methods: {
    async fetch () {
      this.loading = true
      try {
        const params = {}
        if (this.filter) params.q = this.filter
        const { data } = await this.$axios.get('/transportes', { params })
        this.rows = Array.isArray(data) ? data : (data.data || [])
      } catch (e) {
        this.$q.notify({ type: 'negative', message: e.response?.data?.message || 'Error al listar transportes' })
      } finally { this.loading = false }
    },

    fetchDebounced: (() => {
      let t
      return function () {
        clearTimeout(t)
        t = setTimeout(() => this.fetch(), 350)
      }
    })(),

    openCreate () {
      this.dlg = { open: true, mode: 'create', row: null }
      this.form = { empresa: '', placa: '', responsable: '' }
    },

    openEdit (row) {
      this.dlg = { open: true, mode: 'edit', row }
      this.form = {
        empresa: row.empresa || '',
        placa: row.placa || '',
        responsable: row.responsable || ''
      }
    },

    async onSubmit () {
      if (!this.canSubmit) return
      this.saving = true
      try {
        if (this.dlg.mode === 'create') {
          await this.$axios.post('/transportes', this.form)
        } else {
          await this.$axios.put(`/transportes/${this.dlg.row.id}`, this.form)
        }
        this.$q.notify({ type: 'positive', message: 'Guardado' })
        this.dlg.open = false
        await this.fetch()
      } catch (e) {
        this.$q.notify({ type: 'negative', message: e.response?.data?.message || 'No se pudo guardar' })
      } finally { this.saving = false }
    },

    async onDelete (row) {
      this.$q.dialog({
        title: 'Eliminar',
        message: `¿Eliminar el transporte ${row.empresa || row.placa || row.responsable}?`,
        cancel: true, persistent: true
      }).onOk(async () => {
        try {
          await this.$axios.delete(`/transportes/${row.id}`)
          this.$q.notify({ type: 'positive', message: 'Eliminado' })
          await this.fetch()
        } catch (e) {
          this.$q.notify({ type: 'negative', message: e.response?.data?.message || 'No se pudo eliminar' })
        }
      })
    },
  }
}
</script>

<style scoped>
/* Ajustes opcionales */
</style>
