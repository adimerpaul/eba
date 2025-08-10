<template>
  <q-page class="q-pa-xs">
    <q-card flat bordered>
      <q-card-section class="row items-center q-gutter-sm">
        <div class="text-h6">Productores / Apicultores</div>
        <q-space />
        <q-input v-model="filters.search" dense outlined placeholder="Buscar por código, nombre, CI, municipio..."
                 @keyup.enter="fetchRows" style="min-width: 280px">
          <template #append><q-icon name="search" /></template>
        </q-input>
        <q-select v-model="filters.estado" :options="['Activo','Inactivo']" dense outlined clearable label="Estado" style="min-width: 150px" />
        <q-btn color="primary" icon="refresh" label="Actualizar" no-caps :loading="loading" @click="fetchRows" />
        <q-btn color="positive" icon="add_circle" label="Nuevo" no-caps @click="openNew" />
      </q-card-section>

      <q-separator />

      <q-table
        flat bordered
        :rows="rows"
        :columns="columns"
        row-key="id"
        :loading="loading"
        :pagination="pagination"
        @request="onRequest"
        :rows-per-page-options="[10,20,50]"
      >
        <template #body-cell-estado="props">
          <q-td :props="props">
            <q-chip dense :color="props.row.estado === 'Activo' ? 'green' : 'grey'" text-color="white">
              {{ props.row.estado }}
            </q-chip>
          </q-td>
        </template>

        <template #body-cell-actions="props">
          <q-td :props="props">
            <q-btn dense flat icon="edit" @click="openEdit(props.row)" />
            <q-btn dense flat icon="delete" color="negative" @click="remove(props.row.id)" />
          </q-td>
        </template>
      </q-table>
    </q-card>

    <!-- Dialogo crear/editar -->
    <q-dialog v-model="dialog" persistent>
      <q-card style="min-width: 520px;max-width: 90vw">
        <q-card-section class="row items-center q-gutter-sm">
          <div class="text-h6">{{ form.id ? 'Editar Apicultor' : 'Nuevo Apicultor' }}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-separator />
        <q-card-section>
          <q-form @submit="submit">
            <div class="row q-col-gutter-sm">
              <div class="col-12 col-sm-4"><q-input v-model="form.codigo" label="Código" dense outlined :rules="[v => !!v || 'Requerido']" /></div>
              <div class="col-12 col-sm-8"><q-input v-model="form.nombre" label="Nombre" dense outlined :rules="[v => !!v || 'Requerido']" /></div>

              <div class="col-12 col-sm-4"><q-input v-model="form.ci" label="CI" dense outlined /></div>
              <div class="col-12 col-sm-4"><q-input v-model="form.telefono" label="Teléfono" dense outlined /></div>
              <div class="col-12 col-sm-4"><q-input v-model="form.email" label="Email" type="email" dense outlined /></div>

              <div class="col-12 col-sm-4"><q-input v-model="form.departamento" label="Departamento" dense outlined /></div>
              <div class="col-12 col-sm-4"><q-input v-model="form.municipio" label="Municipio" dense outlined /></div>
              <div class="col-12 col-sm-4"><q-input v-model="form.asociacion" label="Asociación" dense outlined /></div>

              <div class="col-12 col-sm-4">
                <q-select v-model="form.estado" :options="['Activo','Inactivo']" label="Estado" dense outlined />
              </div>
              <div class="col-12 col-sm-4">
                <q-input v-model.number="form.apiarios" type="number" label="Apiarios" dense outlined />
              </div>
              <div class="col-12 col-sm-4">
                <q-input v-model="form.ultima_inspeccion" label="Última inspección" dense outlined mask="####-##-##" hint="YYYY-MM-DD" />
              </div>

              <div class="col-12 col-sm-6"><q-input v-model.number="form.lat" label="Lat" dense outlined /></div>
              <div class="col-12 col-sm-6"><q-input v-model.number="form.lng" label="Lng" dense outlined /></div>

              <div class="col-12"><q-input v-model="form.observaciones" type="textarea" label="Observaciones" outlined /></div>
            </div>

            <div class="text-right q-mt-md">
              <q-btn flat label="Cancelar" color="negative" v-close-popup :loading="saving" />
              <q-btn label="Guardar" color="primary" type="submit" :loading="saving" class="q-ml-sm" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
export default {
  name: 'ApicultoresPage',
  data () {
    return {
      rows: [],
      loading: false,
      saving: false,
      dialog: false,
      form: {},
      filters: { search: '', estado: null },
      pagination: { page: 1, rowsPerPage: 20, rowsNumber: 0 },
      columns: [
        { name: 'actions', label: 'Acciones', align: 'left', field: 'id' },
        { name: 'codigo', label: 'Código', align: 'left', field: 'codigo', sortable: true },
        { name: 'nombre', label: 'Nombre', align: 'left', field: 'nombre', sortable: true },
        { name: 'ci', label: 'CI', align: 'left', field: 'ci' },
        { name: 'telefono', label: 'Teléfono', align: 'left', field: 'telefono' },
        { name: 'municipio', label: 'Municipio', align: 'left', field: 'municipio' },
        { name: 'asociacion', label: 'Asociación', align: 'left', field: 'asociacion' },
        { name: 'apiarios', label: 'Apiarios', align: 'right', field: 'apiarios', sortable: true },
        { name: 'ultima_inspeccion', label: 'Últ. Inspección', align: 'left', field: row => row.ultima_inspeccion ?? '-' },
        { name: 'estado', label: 'Estado', align: 'left', field: 'estado' },
      ]
    }
  },
  mounted () {
    this.fetchRows()
  },
  methods: {
    onRequest (props) {
      this.pagination = props.pagination
      this.fetchRows()
    },
    async fetchRows () {
      this.loading = true
      try {
        const { page, rowsPerPage } = this.pagination
        const res = await this.$axios.get('apicultores', {
          params: {
            page, per_page: rowsPerPage,
            search: this.filters.search || undefined,
            estado: this.filters.estado || undefined
          }
        })
        this.rows = res.data.data
        this.pagination.rowsNumber = res.data.total
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'No se pudo cargar')
      } finally {
        this.loading = false
      }
    },
    openNew () {
      this.form = {
        codigo: '', nombre: '', estado: 'Activo',
        apiarios: 0
      }
      this.dialog = true
    },
    openEdit (row) {
      this.form = { ...row }
      this.dialog = true
    },
    async submit () {
      this.saving = true
      try {
        if (this.form.id) {
          await this.$axios.put(`apicultores/${this.form.id}`, this.form)
          this.$alert.success('Actualizado')
        } else {
          await this.$axios.post('apicultores', this.form)
          this.$alert.success('Creado')
        }
        this.dialog = false
        this.fetchRows()
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'No se pudo guardar')
      } finally {
        this.saving = false
      }
    },
    remove (id) {
      this.$alert.dialog('¿Eliminar apicultor?')
        .onOk(async () => {
          this.loading = true
          try {
            await this.$axios.delete(`apicultores/${id}`)
            this.$alert.success('Eliminado')
            this.fetchRows()
          } catch (e) {
            this.$alert.error(e.response?.data?.message || 'No se pudo eliminar')
          } finally {
            this.loading = false
          }
        })
    }
  }
}
</script>
