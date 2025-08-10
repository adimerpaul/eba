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
        <q-select v-model="filters.estado" :options="['Activo','Mantenimiento','Inactivo']" dense outlined clearable label="Estado" style="min-width: 170px" />
        <q-btn color="primary" icon="refresh" label="Actualizar" no-caps :loading="loading" @click="fetchRows" />
        <q-btn color="positive" icon="add_circle" label="Nuevo" no-caps @click="openNew" />
      </q-card-section>
      <q-separator />
<!--      <q-table-->
<!--        flat bordered-->
<!--        :rows="rows"-->
<!--        :columns="columns"-->
<!--        row-key="id"-->
<!--        :loading="loading"-->
<!--        :pagination="pagination"-->
<!--        @request="onRequest"-->
<!--        :rows-per-page-options="[10,20,50]"-->
<!--      >-->
<!--        <template #body-cell-estado="props">-->
<!--          <q-td :props="props">-->
<!--            <q-chip-->
<!--              dense-->
<!--              :color="chipColor(props.row.estado)"-->
<!--              text-color="white"-->
<!--            >-->
<!--              {{ props.row.estado }}-->
<!--            </q-chip>-->
<!--          </q-td>-->
<!--        </template>-->

<!--        <template #body-cell-actions="props">-->
<!--          <q-td :props="props">-->
<!--            <q-btn dense flat icon="edit" @click="openEdit(props.row)" />-->
<!--            <q-btn dense flat icon="delete" color="negative" @click="remove(props.row.id)" />-->
<!--          </q-td>-->
<!--        </template>-->
<!--      </q-table>-->

<!--      protected $fillable = [-->
<!--      'codigo','nombre','ci','telefono','email',-->
<!--      'departamento','municipio','asociacion','estado',-->
<!--      'apiarios','ultima_inspeccion','lat','lng','observaciones'-->
<!--      ];-->
      <q-markup-table dense wrap-cells>
        <thead>
        <tr class="bg-primary text-white">
          <th>Opciones</th>
          <th>Código</th>
          <th>Nombre</th>
          <th>CI</th>
          <th>Teléfono</th>
          <th>Municipio</th>
          <th>Asociación</th>
          <th class="text-right">Apiarios</th>
          <th>Últ. Inspección</th>
          <th>Estado</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="row in rows" :key="row.id">
          <td>
<!--            <q-btn dense flat icon="edit" @click="openEdit(row)" />-->
<!--            <q-btn dense flat icon="delete" color="negative" @click="remove(row.id)" />-->
            <q-btn-dropdown dense color="primary" label="Opciones" no-caps size="10px" >
              <q-list>
                <q-item clickable @click="openEdit(row)" v-close-popup>
                  <q-item-section avatar><q-icon name="edit" /></q-item-section>
                  <q-item-section>Editar</q-item-section>
                </q-item>
                <q-item clickable @click="remove(row.id)" v-close-popup>
                  <q-item-section avatar><q-icon name="delete" color="negative" /></q-item-section>
                  <q-item-section>Eliminar</q-item-section>
                </q-item>
              </q-list>
            </q-btn-dropdown>
          </td>
          <td>{{ row.codigo }}</td>
          <td>{{ row.nombre }}</td>
          <td>{{ row.ci }}</td>
          <td>{{ row.telefono }}</td>
          <td>{{ row.municipio }}</td>
          <td>{{ row.asociacion }}</td>
          <td class="text-right">{{ row.apiarios }}</td>
          <td>{{ row.ultima_inspeccion || '-' }}</td>
          <td>
            <q-chip dense :color="chipColor(row.estado)" text-color="white">
              {{ row.estado }}
            </q-chip>
          </td>
        </tr>
        </tbody>
      </q-markup-table>
    </q-card>

    <!-- Dialogo crear/editar -->
    <q-dialog v-model="dialog" persistent>
      <q-card style="min-width: 300px;max-width: 90vw">
        <q-card-section class="row items-center q-gutter-sm">
          <div class="text-h6">{{ form.id ? 'Editar Apicultor' : 'Nuevo Apicultor' }}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-separator />
        <q-card-section>
          <q-form @submit="submit">
            <div class="row q-col-gutter-sm">
              <!-- Código: solo mostrar si existe -->
              <div class="col-12" v-if="form.codigo">
                <q-input v-model="form.codigo" label="Código" dense outlined readonly />
              </div>

              <div class="col-12 col-sm-8">
                <q-input v-model="form.nombre" label="Nombre" dense outlined :rules="[v => !!v || 'Requerido']" />
              </div>
              <div class="col-12 col-sm-4">
                <q-select v-model="form.estado" :options="['Activo','Mantenimiento','Inactivo']" label="Estado" dense outlined />
              </div>

              <div class="col-12 col-sm-4"><q-input v-model="form.ci" label="CI" dense outlined /></div>
              <div class="col-12 col-sm-4"><q-input v-model="form.telefono" label="Teléfono" dense outlined /></div>
              <div class="col-12 col-sm-4"><q-input v-model="form.email" label="Email" type="email" dense outlined /></div>

              <div class="col-12 col-sm-4">
<!--                <q-input v-model="form.departamento" label="Departamento" dense outlined />-->
                <q-select v-model="form.departamento" :options="['La Paz', 'Cochabamba', 'Santa Cruz', 'Oruro', 'Potosí', 'Tarija', 'Chuquisaca', 'Beni', 'Pando']" label="Departamento" dense outlined />
              </div>
              <div class="col-12 col-sm-4">
                <q-input v-model="form.municipio" label="Municipio" dense outlined />

              </div>
              <div class="col-12 col-sm-4"><q-input v-model="form.asociacion" label="Asociación" dense outlined /></div>

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
  mounted () { this.fetchRows() },
  methods: {
    chipColor (estado) {
      if (estado === 'Activo') return 'green'
      if (estado === 'Mantenimiento') return 'amber'
      return 'grey'
    },
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
            // page, per_page: rowsPerPage,
            search: this.filters.search || undefined,
            estado: this.filters.estado || undefined
          }
        })
        this.rows = res.data
        // this.pagination.rowsNumber = res.data.total
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'No se pudo cargar')
      } finally {
        this.loading = false
      }
    },
    openNew () {
      this.form = {
        // codigo: auto
        nombre: '', estado: 'Activo',
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
          const payload = { ...this.form }
          delete payload.codigo // no editable
          await this.$axios.put(`apicultores/${this.form.id}`, payload)
          this.$alert.success('Actualizado')
        } else {
          const { data } = await this.$axios.post('apicultores', this.form)
          this.$alert.success('Creado')
          // mostrar código generado en el form si quieres mantener el diálogo abierto
          this.form = { ...data }
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
