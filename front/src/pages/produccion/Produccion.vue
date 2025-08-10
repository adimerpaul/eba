<template>
  <q-page class="q-pa-md">

    <!-- KPIs estilo dashboard -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-md-3">
        <q-card flat bordered class="kpi-card bg-green-10 text-white">
          <q-card-section>
            <div class="text-subtitle2">Registrados</div>
            <div class="text-h4 text-bold">{{ kpi.total }}</div>
            <div class="text-caption">apicultores en el sistema</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-3">
        <q-card flat bordered class="kpi-card bg-green text-white">
          <q-card-section>
            <div class="text-subtitle2">Activos</div>
            <div class="text-h4 text-bold">{{ kpi.activos }}</div>
            <div class="text-caption">estado “Activo”</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-3">
        <q-card flat bordered class="kpi-card bg-amber text-dark">
          <q-card-section>
            <div class="text-subtitle2">Mantenimiento</div>
            <div class="text-h4 text-bold">{{ kpi.mant }}</div>
            <div class="text-caption">en mantenimiento</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-3">
        <q-card flat bordered class="kpi-card bg-red-5 text-white">
          <q-card-section>
            <div class="text-subtitle2">Inactivos</div>
            <div class="text-h4 text-bold">{{ kpi.inactivos }}</div>
            <div class="text-caption">no operativos</div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <q-card flat bordered>
      <q-card-section class="row items-center q-gutter-sm">
        <div class="text-h6">Productores / Apicultores</div>
        <q-space />
        <q-input
          v-model="filters.search"
          dense outlined
          placeholder="Buscar por código, nombre, CI, municipio..."
          @keyup.enter="fetchRows"
          style="min-width: 280px"
        >
          <template #append><q-icon name="search" /></template>
        </q-input>
        <q-select
          v-model="filters.estado"
          :options="['Activo','Mantenimiento','Inactivo']"
          dense outlined clearable label="Estado" style="min-width: 170px"
        />
        <q-btn color="secondary" icon="map" label="Mapa" no-caps @click="openMap" />
        <q-btn color="primary" icon="refresh" label="Actualizar" no-caps :loading="loading" @click="fetchRows" />
        <q-btn color="positive" icon="add_circle" label="Nuevo" no-caps @click="openNew" />
      </q-card-section>

      <q-separator />

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
            <q-btn-dropdown dense color="primary" label="Opciones" no-caps size="10px">
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
            <q-chip dense :color="chipColor(row.estado)" :text-color="chipColorText(row.estado)">
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
                <q-select v-model="form.departamento"
                          :options="['La Paz','Cochabamba','Santa Cruz','Oruro','Potosí','Tarija','Chuquisaca','Beni','Pando']"
                          label="Departamento" dense outlined />
              </div>
              <div class="col-12 col-sm-4"><q-input v-model="form.municipio" label="Municipio" dense outlined /></div>
              <div class="col-12 col-sm-4"><q-input v-model="form.asociacion" label="Asociación" dense outlined /></div>

              <div class="col-12 col-sm-4"><q-input v-model.number="form.apiarios" type="number" label="Apiarios" dense outlined /></div>
              <div class="col-12 col-sm-4">
                <q-input v-model="form.ultima_inspeccion" label="Última inspección" dense outlined mask="####-##-##" hint="YYYY-MM-DD" />
              </div>

              <div class="col-12">
                <MapPicker v-model="formLocation" :center="[-16.5,-68.15]" :zoom-init="13" />
              </div>

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

    <!-- Dialogo Mapa General -->
    <q-dialog v-model="dialogMap" persistent maximized transition-show="slide-up" transition-hide="slide-down">
      <q-card>
        <q-bar class="bg-grey-2">
          <div class="text-subtitle2">Mapa de Apicultores (Bolivia)</div>
          <q-space />
          <q-btn dense flat icon="refresh" @click="refitMap = Date.now()" />
          <q-btn dense flat icon="close" v-close-popup />
        </q-bar>
        <q-separator />
        <q-card-section class="q-pa-none" style="height: calc(100vh - 80px);">
          <ApicultoresMap :items="rows" :refit-key="refitMap" />
        </q-card-section>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script>
import MapPicker from 'src/components/MapPicker.vue'
import ApicultoresMap from 'src/components/ApicultoresMap.vue'

export default {
  name: 'ApicultoresPage',
  components: { MapPicker, ApicultoresMap },
  data () {
    return {
      rows: [],
      loading: false,
      saving: false,
      dialog: false,
      dialogMap: false,
      refitMap: 0,
      form: {},
      filters: { search: '', estado: null },
      pagination: { page: 1, rowsPerPage: 20, rowsNumber: 0 },
      kpi: { total: 0, activos: 0, mant: 0, inactivos: 0 }
    }
  },
  computed: {
    formLocation: {
      get () { return { lat: this.form.lat ?? null, lng: this.form.lng ?? null } },
      set (v) { this.form.lat = v?.lat ?? null; this.form.lng = v?.lng ?? null }
    }
  },
  mounted () { this.fetchRows() },
  methods: {
    chipColor (estado) {
      if (estado === 'Activo') return 'green'
      if (estado === 'Mantenimiento') return 'amber'
      return 'red-5'
    },
    chipColorText (estado) {
      if (estado === 'Mantenimiento') return 'black'
      return 'white'
    },
    async fetchRows () {
      this.loading = true
      try {
        const res = await this.$axios.get('apicultores', {
          params: { search: this.filters.search || undefined, estado: this.filters.estado || undefined }
        })
        this.rows = res.data
        this.computeKpis()
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'No se pudo cargar')
      } finally {
        this.loading = false
      }
    },
    computeKpis () {
      const total = this.rows.length
      const activos = this.rows.filter(r => r.estado === 'Activo').length
      const mant = this.rows.filter(r => r.estado === 'Mantenimiento').length
      const inactivos = this.rows.filter(r => r.estado === 'Inactivo').length
      this.kpi = { total, activos, mant, inactivos }
    },
    openMap () {
      this.dialogMap = true
      // forzar ajuste a marcadores cuando abra
      this.$nextTick(() => { this.refitMap = Date.now() })
    },
    openNew () {
      this.form = { nombre: '', estado: 'Activo', apiarios: 0, lat: null, lng: null }
      this.dialog = true
    },
    openEdit (row) { this.form = { ...row }; this.dialog = true },
    async submit () {
      this.saving = true
      try {
        if (this.form.id) {
          const payload = { ...this.form }; delete payload.codigo
          await this.$axios.put(`apicultores/${this.form.id}`, payload)
          this.$alert.success('Actualizado')
        } else {
          const { data } = await this.$axios.post('apicultores', this.form)
          this.$alert.success('Creado'); this.form = { ...data }
        }
        this.dialog = false; this.fetchRows()
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'No se pudo guardar')
      } finally { this.saving = false }
    },
    remove (id) {
      this.$alert.dialog('¿Eliminar apicultor?').onOk(async () => {
        this.loading = true
        try { await this.$axios.delete(`apicultores/${id}`); this.$alert.success('Eliminado'); this.fetchRows() }
        catch (e) { this.$alert.error(e.response?.data?.message || 'No se pudo eliminar') }
        finally { this.loading = false }
      })
    }
  }
}
</script>

<style scoped>
.kpi-card { border-radius: 14px; }
</style>
