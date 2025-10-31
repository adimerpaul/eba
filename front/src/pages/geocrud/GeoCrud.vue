<template>
  <q-page class="q-pa-md">

    <div class="row items-center q-gutter-sm q-mb-sm">
      <q-avatar icon="public" color="primary" text-color="white" />
      <div class="text-h6 text-weight-bold">Geografía (Departamento / Provincia / Municipio)</div>
      <q-space />
      <q-btn color="primary" icon="refresh" label="Actualizar Árbol" @click="loadTree" :loading="loadingTree" no-caps />
    </div>

    <q-tabs v-model="tab" dense class="text-primary" align="left" narrow-indicator>
      <q-tab name="departamentos" icon="apartment" label="Departamentos" />
      <q-tab name="provincias" icon="domain" label="Provincias" />
      <q-tab name="municipios" icon="location_city" label="Municipios" />
    </q-tabs>
    <q-separator />

    <q-tab-panels v-model="tab" animated>
      <!-- =================== DEPARTAMENTOS =================== -->
      <q-tab-panel name="departamentos">
        <q-card flat bordered class="q-mt-md">
          <q-card-section class="row items-center q-gutter-sm">
            <div class="text-subtitle1 text-weight-medium">Departamentos</div>
            <q-space />
            <q-input v-model="dep.filters.search" dense outlined placeholder="Buscar departamento..."
                     @keyup.enter="fetchDepartamentos" style="min-width: 260px">
              <template #append><q-icon name="search" /></template>
            </q-input>
            <q-btn color="secondary" icon="refresh" flat @click="fetchDepartamentos" :loading="dep.loading" />
            <q-btn color="positive" icon="add_circle" label="Nuevo" @click="openDepNew" :loading="dep.saving" no-caps />
          </q-card-section>
          <q-separator />
          <q-markup-table dense wrap-cells>
            <thead>
            <tr class="bg-primary text-white">
              <th style="width: 140px;">Opciones</th>
              <th>Departamento</th>
              <th class="text-right"># Provincias</th>
              <th class="text-right"># Municipios</th>
              <th class="text-right"># Productores</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(row,i) in dep.rows" :key="row.id">
              <td>
                <q-btn-dropdown dense color="primary" :label="`Opciones (${i+1})`" no-caps size="10px">
                  <q-list>
                    <q-item clickable v-close-popup @click="openDepEdit(row)">
                      <q-item-section avatar><q-icon name="edit" /></q-item-section>
                      <q-item-section>Editar</q-item-section>
                    </q-item>
                    <q-item clickable v-close-popup @click="removeDepartamento(row)">
                      <q-item-section avatar><q-icon name="delete" color="negative" /></q-item-section>
                      <q-item-section>Eliminar</q-item-section>
                    </q-item>
                    <q-item clickable v-close-popup @click="goApiariosMapa(row)">
                      <q-item-section avatar><q-icon name="map" /></q-item-section>
                      <q-item-section>Ver apiarios</q-item-section>
                    </q-item>
                  </q-list>
                </q-btn-dropdown>
<!--                <q-btn-->
<!--                  dense no-caps color="secondary" icon="map"-->
<!--                  label="Ver apiarios"-->
<!--                  size="10px"-->
<!--                  @click="goApiariosMapa(row)"-->
<!--                />-->
              </td>
              <td>{{ row.nombre_departamento }}</td>
              <td class="text-right">{{ row.provincias_count ?? '-' }}</td>
              <td class="text-right">{{ row.municipios_count ?? '-' }}</td>
              <td class="text-right">{{ row.productores_count ?? '0' }}</td>
            </tr>
            </tbody>
          </q-markup-table>
        </q-card>
      </q-tab-panel>

      <!-- =================== PROVINCIAS =================== -->
      <q-tab-panel name="provincias">
        <q-card flat bordered class="q-mt-md">
          <q-card-section class="row items-center q-gutter-sm">
            <div class="text-subtitle1 text-weight-medium">Provincias</div>
            <q-space />
            <q-select v-model="prov.filters.departamento_id" :options="depOptions" option-label="label" option-value="value"
                      emit-value map-options dense outlined label="Departamento" style="min-width: 240px"
                      @update:model-value="fetchProvincias" />
            <q-input v-model="prov.filters.search" dense outlined placeholder="Buscar provincia..."
                     @keyup.enter="fetchProvincias" style="min-width: 260px">
              <template #append><q-icon name="search" /></template>
            </q-input>
            <q-btn color="secondary" icon="refresh" flat @click="fetchProvincias" :loading="prov.loading" />
            <q-btn color="positive" icon="add_circle" label="Nueva" @click="openProvNew" :loading="prov.saving" no-caps />
          </q-card-section>
          <q-separator />
          <q-markup-table dense wrap-cells>
            <thead>
            <tr class="bg-primary text-white">
              <th style="width: 140px;">Opciones</th>
              <th>Provincia</th>
              <th>Departamento</th>
              <th class="text-right"># Municipios</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(row,i) in prov.rows" :key="row.id">
              <td>
                <q-btn-dropdown dense color="primary" :label="`Opciones (${i+1})`" no-caps size="10px">
                  <q-list>
                    <q-item clickable v-close-popup @click="openProvEdit(row)">
                      <q-item-section avatar><q-icon name="edit" /></q-item-section>
                      <q-item-section>Editar</q-item-section>
                    </q-item>
                    <q-item clickable v-close-popup @click="removeProvincia(row)">
                      <q-item-section avatar><q-icon name="delete" color="negative" /></q-item-section>
                      <q-item-section>Eliminar</q-item-section>
                    </q-item>
                  </q-list>
                </q-btn-dropdown>
              </td>
              <td>{{ row.nombre_provincia }}</td>
              <td>{{ row.departamento?.nombre_departamento || '-' }}</td>
              <td class="text-right">{{ row.municipios_count ?? '-' }}</td>
            </tr>
            </tbody>
          </q-markup-table>
        </q-card>
      </q-tab-panel>

      <!-- =================== MUNICIPIOS =================== -->
      <q-tab-panel name="municipios">
        <q-card flat bordered class="q-mt-md">
          <q-card-section class="row items-center q-gutter-sm">
            <div class="text-subtitle1 text-weight-medium">Municipios</div>
            <q-space />
            <q-select v-model="mun.filters.departamento_id" :options="depOptions" option-label="label" option-value="value"
                      emit-value map-options dense outlined label="Departamento" style="min-width: 240px"
                      @update:model-value="onMunDepartamentoChange" />
            <q-select v-model="mun.filters.provincia_id" :options="provOptionsFiltered" option-label="label" option-value="value"
                      emit-value map-options dense outlined label="Provincia" style="min-width: 240px"
                      @update:model-value="fetchMunicipios" />
            <q-input v-model="mun.filters.search" dense outlined placeholder="Buscar municipio / zona / región..."
                     @keyup.enter="fetchMunicipios" style="min-width: 260px">
              <template #append><q-icon name="search" /></template>
            </q-input>
            <q-btn color="secondary" icon="refresh" flat @click="fetchMunicipios" :loading="mun.loading" />
            <q-btn color="positive" icon="add_circle" label="Nuevo" @click="openMunNew" :loading="mun.saving" no-caps />
          </q-card-section>
          <q-separator />
          <q-markup-table dense wrap-cells>
            <thead>
            <tr class="bg-primary text-white">
              <th style="width: 140px;">Opciones</th>
              <th>Municipio</th>
              <th>Departamento</th>
              <th>Provincia</th>
              <th>Zona</th>
              <th>Región</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(row,i) in mun.rows" :key="row.id">
              <td>
                <q-btn-dropdown dense color="primary" :label="`Opciones (${i+1})`" no-caps size="10px">
                  <q-list>
                    <q-item clickable v-close-popup @click="openMunEdit(row)">
                      <q-item-section avatar><q-icon name="edit" /></q-item-section>
                      <q-item-section>Editar</q-item-section>
                    </q-item>
                    <q-item clickable v-close-popup @click="removeMunicipio(row)">
                      <q-item-section avatar><q-icon name="delete" color="negative" /></q-item-section>
                      <q-item-section>Eliminar</q-item-section>
                    </q-item>
                  </q-list>
                </q-btn-dropdown>
              </td>
              <td>{{ row.nombre_municipio }}</td>
              <td>{{ row.departamento?.nombre_departamento || '-' }}</td>
              <td>{{ row.provincia?.nombre_provincia || '-' }}</td>
              <td>{{ row.zona || '-' }}</td>
              <td>{{ row.region || '-' }}</td>
            </tr>
            </tbody>
          </q-markup-table>
        </q-card>
      </q-tab-panel>
    </q-tab-panels>

    <!-- ===== Diálogo Departamento ===== -->
    <q-dialog v-model="dep.dialog" persistent>
      <q-card style="min-width: 320px; max-width: 90vw">
        <q-card-section class="row items-center q-gutter-sm">
          <div class="text-h6">{{ dep.form.id ? 'Editar Departamento' : 'Nuevo Departamento' }}</div>
          <q-space /><q-btn flat round dense icon="close" v-close-popup />
        </q-card-section>
        <q-separator />
        <q-card-section>
          <q-form @submit="submitDepartamento">
            <q-input v-model="dep.form.nombre_departamento" label="Nombre departamento" dense outlined
                     :rules="[v=>!!v || 'Requerido']" />
            <div class="text-right q-mt-md">
              <q-btn flat color="negative" v-close-popup label="Cancelar" :loading="dep.saving" />
              <q-btn color="primary" type="submit" label="Guardar" :loading="dep.saving" class="q-ml-sm" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- ===== Diálogo Provincia ===== -->
    <q-dialog v-model="prov.dialog" persistent>
      <q-card style="min-width: 420px; max-width: 90vw">
        <q-card-section class="row items-center q-gutter-sm">
          <div class="text-h6">{{ prov.form.id ? 'Editar Provincia' : 'Nueva Provincia' }}</div>
          <q-space /><q-btn flat round dense icon="close" v-close-popup />
        </q-card-section>
        <q-separator />
        <q-card-section>
          <q-form @submit="submitProvincia">
            <div class="row q-col-gutter-sm">
              <div class="col-12">
                <q-select v-model="prov.form.departamento_id" :options="depOptions" option-label="label" option-value="value"
                          emit-value map-options dense outlined label="Departamento" :rules="[v=>!!v || 'Requerido']" />
              </div>
              <div class="col-12">
                <q-input v-model="prov.form.nombre_provincia" label="Nombre provincia" dense outlined
                         :rules="[v=>!!v || 'Requerido']" />
              </div>
            </div>
            <div class="text-right q-mt-md">
              <q-btn flat color="negative" v-close-popup label="Cancelar" :loading="prov.saving" />
              <q-btn color="primary" type="submit" label="Guardar" :loading="prov.saving" class="q-ml-sm" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- ===== Diálogo Municipio ===== -->
    <q-dialog v-model="mun.dialog" persistent>
      <q-card style="min-width: 520px; max-width: 90vw">
        <q-card-section class="row items-center q-gutter-sm">
          <div class="text-h6">{{ mun.form.id ? 'Editar Municipio' : 'Nuevo Municipio' }}</div>
          <q-space /><q-btn flat round dense icon="close" v-close-popup />
        </q-card-section>
        <q-separator />
        <q-card-section>
          <q-form @submit="submitMunicipio">
            <div class="row q-col-gutter-sm">
              <div class="col-12 col-sm-6">
                <q-select v-model="mun.form.departamento_id" :options="depOptions" option-label="label" option-value="value"
                          emit-value map-options dense outlined label="Departamento" :rules="[v=>!!v || 'Requerido']"
                          @update:model-value="onMunFormDepChange" />
              </div>
              <div class="col-12 col-sm-6">
                <q-select v-model="mun.form.provincia_id" :options="provOptionsForForm" option-label="label" option-value="value"
                          emit-value map-options dense outlined label="Provincia" :rules="[v=>!!v || 'Requerido']" />
              </div>
              <div class="col-12">
                <q-input v-model="mun.form.nombre_municipio" label="Nombre municipio" dense outlined
                         :rules="[v=>!!v || 'Requerido']" />
              </div>
              <div class="col-12 col-sm-6">
                <q-input v-model="mun.form.zona" label="Zona" dense outlined />
              </div>
              <div class="col-12 col-sm-6">
                <q-input v-model="mun.form.region" label="Región" dense outlined />
              </div>
            </div>
            <div class="text-right q-mt-md">
              <q-btn flat color="negative" v-close-popup label="Cancelar" :loading="mun.saving" />
              <q-btn color="primary" type="submit" label="Guardar" :loading="mun.saving" class="q-ml-sm" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script>
export default {
  name: 'GeoCrud',
  data () {
    return {
      tab: 'departamentos',
      loadingTree: false,
      tree: [], // [{id,nombre_departamento, provincias:[{id,nombre_provincia, municipios:[...]}]}]

      dep: {
        rows: [],
        loading: false,
        saving: false,
        dialog: false,
        form: {},
        filters: { search: '' }
      },

      prov: {
        rows: [],
        loading: false,
        saving: false,
        dialog: false,
        form: {},
        filters: { search: '', departamento_id: null }
      },

      mun: {
        rows: [],
        loading: false,
        saving: false,
        dialog: false,
        form: {},
        filters: { search: '', departamento_id: null, provincia_id: null }
      }
    }
  },
  computed: {
    depOptions () {
      return (this.tree || []).map(d => ({ label: d.nombre_departamento, value: d.id }))
    },
    provOptionsFiltered () {
      const depId = this.mun.filters.departamento_id
      if (!depId) return []
      const dep = (this.tree || []).find(d => d.id === depId)
      return (dep?.provincias || []).map(p => ({ label: p.nombre_provincia, value: p.id }))
    },
    provOptionsForForm () {
      const depId = this.mun.form.departamento_id
      if (!depId) return []
      const dep = (this.tree || []).find(d => d.id === depId)
      return (dep?.provincias || []).map(p => ({ label: p.nombre_provincia, value: p.id }))
    }
  },
  mounted () {
    this.loadTree()
    this.fetchDepartamentos()
    this.fetchProvincias()
    this.fetchMunicipios()
  },
  methods: {
    goApiariosMapa (row) {
      this.$router.push({ name: 'geo.apiarios', query: { departamento_id: row.id, nombre: row.nombre_departamento } })
    },

    async loadTree () {
      this.loadingTree = true
      try {
        const { data } = await this.$axios.get('geo/tree')
        this.tree = data
      } catch (e) {
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo cargar el árbol')
      } finally {
        this.loadingTree = false
      }
    },

    // --------- Departamentos ---------
    async fetchDepartamentos () {
      this.dep.loading = true
      try {
        // CAMBIA a geo/departamentos para traer counts listos
        const { data } = await this.$axios.get('geo/departamentos', {
          params: { search: this.dep.filters.search || undefined }
        })
        this.dep.rows = data
        // Ya NO hace falta pedir show por cada uno: vienen provincias_count, municipios_count y productores_count
      } catch (e) {
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo cargar departamentos')
      } finally {
        this.dep.loading = false
      }
    },
    openDepNew () {
      this.dep.form = { nombre_departamento: '' }
      this.dep.dialog = true
    },
    openDepEdit (row) {
      this.dep.form = { id: row.id, nombre_departamento: row.nombre_departamento }
      this.dep.dialog = true
    },
    async submitDepartamento () {
      this.dep.saving = true
      try {
        if (this.dep.form.id) {
          await this.$axios.put(`departamentos/${this.dep.form.id}`, this.dep.form)
          this.$alert?.success?.('Departamento actualizado')
        } else {
          await this.$axios.post('departamentos', this.dep.form)
          this.$alert?.success?.('Departamento creado')
        }
        this.dep.dialog = false
        this.loadTree()
        this.fetchDepartamentos()
      } catch (e) {
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo guardar')
      } finally {
        this.dep.saving = false
      }
    },
    removeDepartamento (row) {
      this.$alert?.dialog?.(`¿Eliminar el departamento "${row.nombre_departamento}"?`)?.onOk(async () => {
        try {
          await this.$axios.delete(`departamentos/${row.id}`)
          this.$alert?.success?.('Eliminado')
          this.loadTree()
          this.fetchDepartamentos()
        } catch (e) {
          this.$alert?.error?.(e.response?.data?.message || 'No se pudo eliminar')
        }
      })
    },

    // --------- Provincias ---------
    async fetchProvincias () {
      this.prov.loading = true
      try {
        const { data } = await this.$axios.get('provincias', {
          params: {
            search: this.prov.filters.search || undefined,
            departamento_id: this.prov.filters.departamento_id || undefined
          }
        })
        // Si quieres counts: podrías llamar show por cada una. Para simplicidad dejamos así.
        this.prov.rows = data
      } catch (e) {
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo cargar provincias')
      } finally {
        this.prov.loading = false
      }
    },
    openProvNew () {
      this.prov.form = { nombre_provincia: '', departamento_id: this.prov.filters.departamento_id || null }
      this.prov.dialog = true
    },
    openProvEdit (row) {
      this.prov.form = {
        id: row.id,
        nombre_provincia: row.nombre_provincia,
        departamento_id: row.departamento?.id || row.departamento_id || null
      }
      this.prov.dialog = true
    },
    async submitProvincia () {
      this.prov.saving = true
      try {
        if (this.prov.form.id) {
          await this.$axios.put(`provincias/${this.prov.form.id}`, this.prov.form)
          this.$alert?.success?.('Provincia actualizada')
        } else {
          await this.$axios.post('provincias', this.prov.form)
          this.$alert?.success?.('Provincia creada')
        }
        this.prov.dialog = false
        await this.loadTree()
        this.fetchProvincias()
        // refrescar municipios si el filtro dependía de esta provincia
        if (this.mun.filters.departamento_id === this.prov.form.departamento_id) {
          this.fetchMunicipios()
        }
      } catch (e) {
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo guardar')
      } finally {
        this.prov.saving = false
      }
    },
    removeProvincia (row) {
      this.$alert?.dialog?.(`¿Eliminar la provincia "${row.nombre_provincia}"?`)?.onOk(async () => {
        try {
          await this.$axios.delete(`provincias/${row.id}`)
          this.$alert?.success?.('Eliminado')
          await this.loadTree()
          this.fetchProvincias()
          this.fetchMunicipios()
        } catch (e) {
          this.$alert?.error?.(e.response?.data?.message || 'No se pudo eliminar')
        }
      })
    },

    // --------- Municipios ---------
    async fetchMunicipios () {
      this.mun.loading = true
      try {
        const { data } = await this.$axios.get('municipios', {
          params: {
            search: this.mun.filters.search || undefined,
            departamento_id: this.mun.filters.departamento_id || undefined,
            provincia_id: this.mun.filters.provincia_id || undefined
          }
        })
        this.mun.rows = data
      } catch (e) {
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo cargar municipios')
      } finally {
        this.mun.loading = false
      }
    },
    onMunDepartamentoChange () {
      // vaciar provincia y recargar listado
      this.mun.filters.provincia_id = null
      this.fetchMunicipios()
    },
    openMunNew () {
      this.mun.form = {
        nombre_municipio: '',
        departamento_id: this.mun.filters.departamento_id || null,
        provincia_id: this.mun.filters.provincia_id || null,
        zona: '',
        region: ''
      }
      this.mun.dialog = true
    },
    openMunEdit (row) {
      this.mun.form = {
        id: row.id,
        nombre_municipio: row.nombre_municipio,
        departamento_id: row.departamento?.id || row.departamento_id || null,
        provincia_id: row.provincia?.id || row.provincia_id || null,
        zona: row.zona || '',
        region: row.region || ''
      }
      this.mun.dialog = true
    },
    onMunFormDepChange () {
      // Si cambia el dep en el form, resetear la provincia del form
      this.mun.form.provincia_id = null
    },
    async submitMunicipio () {
      this.mun.saving = true
      try {
        if (this.mun.form.id) {
          await this.$axios.put(`municipios/${this.mun.form.id}`, this.mun.form)
          this.$alert?.success?.('Municipio actualizado')
        } else {
          await this.$axios.post('municipios', this.mun.form)
          this.$alert?.success?.('Municipio creado')
        }
        this.mun.dialog = false
        await this.loadTree()
        this.fetchMunicipios()
      } catch (e) {
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo guardar')
      } finally {
        this.mun.saving = false
      }
    },
    removeMunicipio (row) {
      this.$alert?.dialog?.(`¿Eliminar el municipio "${row.nombre_municipio}"?`)?.onOk(async () => {
        try {
          await this.$axios.delete(`municipios/${row.id}`)
          this.$alert?.success?.('Eliminado')
          await this.loadTree()
          this.fetchMunicipios()
        } catch (e) {
          this.$alert?.error?.(e.response?.data?.message || 'No se pudo eliminar')
        }
      })
    }
  }
}
</script>

<style scoped>
.q-tab-panel { padding-left: 0; padding-right: 0; }
</style>
