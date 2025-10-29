<template>
  <q-page class="q-pa-md">
    <q-table
      :rows="rows"
      :columns="columns"
      row-key="id"
      flat bordered dense wrap-cells
      :rows-per-page-options="[0]"
      title="Plantas"
      :loading="loading"
      :filter="filter"
    >
      <template #top-right>
        <div class="row items-center q-gutter-sm">
          <q-select
            v-model="municipioId"
            :options="municipioOptions"
            option-value="id"
            option-label="nombre"
            emit-value map-options
            dense filled clearable
            label="Municipio"
            style="min-width: 220px"
            @update:model-value="fetch"
          />
          <q-input
            v-model="filter"
            dense outlined
            placeholder="Buscar por código, nombre, registro o dirección"
            @update:model-value="fetchDebounced"
          >
            <template #append><q-icon name="search" /></template>
          </q-input>
          <q-btn color="positive" icon="add_circle_outline" label="Nuevo" no-caps :loading="loading" @click="openCreate"/>
          <q-btn color="primary"  icon="refresh"            label="Actualizar" no-caps :loading="loading" @click="fetch"/>
        </div>
      </template>

      <template #body-cell-municipio="props">
        <q-td :props="props">
          {{ props.row.municipio?.nombre || findMunicipioNombre(props.row.municipio_id) }}
        </q-td>
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
      <q-card style="min-width: 560px; max-width: 820px">
        <q-card-section class="text-h6">
          {{ dlg.mode === 'create' ? 'Nueva Planta' : 'Editar Planta' }}
        </q-card-section>
        <q-separator/>
        <q-card-section>
          <q-form @submit.prevent="onSubmit" ref="formRef">
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-4">
                <q-input v-model="form.codigo_planta" dense filled label="Código planta" />
              </div>
              <div class="col-12 col-md-8">
                <q-input v-model="form.nombre_planta" dense filled label="Nombre planta" />
              </div>
              <div class="col-12 col-md-6">
                <q-input v-model="form.registro_sanitario" dense filled label="Registro sanitario" />
              </div>
              <div class="col-12 col-md-6">
                <q-select
                  v-model="form.municipio_id"
                  :options="municipioOptions"
                  option-value="id"
                  option-label="nombre_municipio"
                  emit-value map-options
                  dense filled
                  label="Municipio"
                />
<!--                <pre>{{municipioOptions}}</pre>-->
              </div>
              <div class="col-12">
                <q-input v-model="form.direccion" type="textarea" autogrow dense filled label="Dirección" />
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
  name: 'PlantasPage',
  data () {
    return {
      loading: false,
      rows: [],
      columns: [
        { name: 'actions',           label: 'Acciones',          align: 'right' },
        { name: 'codigo_planta',     label: 'Código',            align: 'left',  field: 'codigo_planta' },
        { name: 'nombre_planta',     label: 'Nombre',            align: 'left',  field: 'nombre_planta' },
        { name: 'registro_sanitario',label: 'Reg. sanitario',    align: 'left',  field: 'registro_sanitario' },
        { name: 'municipio',         label: 'Municipio',         align: 'left',  field: row => row.municipio?.nombre || '' },
        { name: 'direccion',         label: 'Dirección',         align: 'left',  field: 'direccion' },
      ],
      filter: '',
      municipioId: null,
      municipioOptions: [],

      // CRUD
      dlg: { open: false, mode: 'create', row: null },
      form: {
        codigo_planta: '',
        nombre_planta: '',
        registro_sanitario: '',
        direccion: '',
        municipio_id: null
      },
      saving: false,
    }
  },
  computed: {
    canSubmit () {
      const f = this.form
      return !!(f.codigo_planta && f.nombre_planta)
    }
  },
  mounted () {
    this.fetchMunicipios()
    this.fetch()
  },
  methods: {
    findMunicipioNombre (id) {
      const m = this.municipioOptions.find(x => x.id === id)
      return m ? m.nombre : id
    },

    async fetch () {
      this.loading = true
      try {
        const params = {}
        if (this.filter) params.q = this.filter
        if (this.municipioId) params.municipio_id = this.municipioId
        const { data } = await this.$axios.get('/plantas', { params })
        this.rows = Array.isArray(data) ? data : (data.data || [])
      } catch (e) {
        this.$q.notify({ type: 'negative', message: e.response?.data?.message || 'Error al listar plantas' })
      } finally { this.loading = false }
    },

    fetchDebounced: (() => {
      let t
      return function () {
        clearTimeout(t)
        t = setTimeout(() => this.fetch(), 350)
      }
    })(),

    async fetchMunicipios () {
      try {
        const { data } = await this.$axios.get('/municipios')
        this.municipioOptions = data
      } catch (e) {
        this.$q.notify({ type: 'warning', message: 'No se pudieron cargar municipios' })
      }
    },

    openCreate () {
      this.dlg = { open: true, mode: 'create', row: null }
      this.form = {
        codigo_planta: '',
        nombre_planta: '',
        registro_sanitario: '',
        direccion: '',
        municipio_id: null
      }
    },

    openEdit (row) {
      this.dlg = { open: true, mode: 'edit', row }
      this.form = {
        codigo_planta: row.codigo_planta || '',
        nombre_planta: row.nombre_planta || '',
        registro_sanitario: row.registro_sanitario || '',
        direccion: row.direccion || '',
        municipio_id: row.municipio_id || row.municipio?.id || null
      }
    },

    async onSubmit () {
      if (!this.canSubmit) return
      this.saving = true
      try {
        if (this.dlg.mode === 'create') {
          await this.$axios.post('/plantas', this.form)
        } else {
          await this.$axios.put(`/plantas/${this.dlg.row.id}`, this.form)
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
        message: `¿Eliminar la planta ${row.nombre_planta}?`,
        cancel: true, persistent: true
      }).onOk(async () => {
        try {
          await this.$axios.delete(`/plantas/${row.id}`)
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
