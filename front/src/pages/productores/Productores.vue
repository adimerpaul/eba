<template>
  <q-page class="q-pa-md">
    <div class="row items-center q-gutter-sm q-mb-sm">
      <q-avatar icon="person_search" color="primary" text-color="white" />
      <div class="text-h6 text-weight-bold">Productores</div>
      <q-space />
      <q-btn
        color="primary"
        icon="forest"
        label="Actualizar árbol geo"
        @click="loadTree"
        :loading="loadingTree"
        no-caps
      />
    </div>

    <q-card flat bordered class="q-mb-md">
      <q-card-section class="row q-col-gutter-sm">
        <div class="col-12 col-md-3">
          <q-input
            v-model="filters.search"
            dense
            outlined
            placeholder="Buscar (RUNSA, nombre, carnet, celular...)"
            @keyup.enter="applyFilters"
          >
            <template #append><q-icon name="search" /></template>
          </q-input>
        </div>

        <div class="col-12 col-md-2">
          <q-select
            v-model="filters.estado"
            :options="['VIGENTE','VENCIDO','ACTIVO','INACTIVO']"
            dense
            outlined
            label="Estado"
            clearable
          />
        </div>

        <div class="col-12 col-md-2">
          <q-select
            v-model="filters.sexo"
            :options="sexoOptions"
            dense
            outlined
            label="Sexo"
            clearable
          />
        </div>

        <div class="col-12 col-md-2">
          <q-input v-model="filters.fecha_desde" type="date" dense outlined label="Desde" />
        </div>
        <div class="col-12 col-md-2">
          <q-input v-model="filters.fecha_hasta" type="date" dense outlined label="Hasta" />
        </div>

        <div class="col-12 col-md-3">
          <q-select
            v-model="filters.departamento_id"
            :options="depOptions"
            option-label="label"
            option-value="value"
            emit-value
            map-options
            dense
            outlined
            label="Departamento"
            @update:model-value="onDepChange"
          />
        </div>
        <div class="col-12 col-md-3">
          <q-select
            v-model="filters.provincia_id"
            :options="provOptions"
            option-label="label"
            option-value="value"
            emit-value
            map-options
            dense
            outlined
            label="Provincia"
            :disable="!filters.departamento_id"
            @update:model-value="onProvChange"
          />
        </div>
        <div class="col-12 col-md-3">
          <q-select
            v-model="filters.municipio_id"
            :options="munOptions"
            option-label="label"
            option-value="value"
            emit-value
            map-options
            dense
            outlined
            label="Municipio"
            :disable="!filters.provincia_id"
          />
        </div>

        <div class="col-12 col-md-auto q-gutter-sm">
          <q-btn
            color="secondary"
            icon="refresh"
            label="Aplicar"
            :loading="loading"
            @click="applyFilters"
            no-caps
          />
<!--          btn craear productos-->
          <q-btn
            color="primary"
            icon="person_add"
            label="Nuevo Productor"
            :loading="loading"
            @click="$router.push('/productores/crear')"
            no-caps
          />
          <q-btn color="green" label="EXCEL" @click="genrarExcel" :loading="loading" />
        </div>
      </q-card-section>
    </q-card>

    <q-card flat bordered class="relative-position">
      <q-markup-table dense wrap-cells>
        <thead>
        <tr class="bg-primary text-white">
          <th class="text-left">ID</th>
          <th class="text-left">RUNSA</th>
          <th class="text-left">Sub Cód.</th>
          <th class="text-left">Nombre completo</th>
          <th class="text-left">CI</th>
          <th class="text-left">Celular</th>
          <th class="text-left">Comunidad</th>
          <th class="text-left">Estado</th>
          <th class="text-left">Registro</th>
        </tr>
        </thead>
        <tbody>
        <tr
          v-for="row in rows"
          :key="row.id"
          class="row-click"
          @click="openDetails(row)"
        >
          <td class="text-left">{{ row.id }}</td>
          <td class="text-left">{{ row.runsa || '-' }}</td>
          <td class="text-left">{{ row.sub_codigo || '-' }}</td>
          <td class="text-left">
            <div class="text-weight-medium">{{ row.nombre_completo }}</div>
            <div class="text-caption text-grey-7">
              {{ row.municipio?.nombre_municipio || '-' }}
            </div>
          </td>
          <td class="text-left">{{ row.numcarnet }}</td>
          <td class="text-left">{{ row.num_celular || '-' }}</td>
          <td class="text-left">{{ row.comunidad || '-' }}</td>
          <td class="text-left">
            <q-chip :color="chipColor(row.estado)" text-color="white" size="xs" class="text-bold">
              {{ row.estado }}
            </q-chip>
          </td>
          <td class="text-left">{{ row.fecha_registro }}</td>
        </tr>
        <tr v-if="!loading && rows?.length === 0">
          <td colspan="9" class="text-center text-grey q-pa-md">No hay resultados</td>
        </tr>
        </tbody>
      </q-markup-table>

      <div v-if="loading" class="absolute-full flex flex-center bg-transparent">
        <q-circular-progress indeterminate size="40px" />
      </div>

      <div class="q-pa-sm row items-center justify-between">
        <div class="text-caption">Total: <b>{{ serverCount?.toLocaleString() }}</b></div>
        <div class="row items-center q-gutter-sm">
          <q-select
            v-model="pagination.perPage"
            :options="[10,25,50,100,150,200]"
            dense
            outlined
            style="width: 120px"
            label="Por página"
            @update:model-value="changePerPage"
          />
          <q-pagination
            v-model="pagination.page"
            :max="pageCount"
            max-pages="8"
            direction-links
            boundary-links
            @update:model-value="changePage"
          />
        </div>
      </div>
    </q-card>

  </q-page>
</template>

<script>
import moment from 'moment';
export default {
  name: 'ProductoresPage',
  data () {
    return {

      rows: [],
      loading: false,
      producto: {},
      serverCount: 0,

      pagination: { page: 1, perPage: 10 },

      loadingTree: false,
      tree: [],
      filters: {
        search: '',
        estado: null,
        sexo: null,
        fecha_desde: null,
        fecha_hasta: null,
        departamento_id: null,
        provincia_id: null,
        municipio_id: null
      },

      sexoOptions: [
        { label: 'Masculino', value: 1 },
        { label: 'Femenino', value: 2 }
      ]
    }
  },
  computed: {
    pageCount () {
      return Math.max(1, Math.ceil(this.serverCount / this.pagination.perPage))
    },
    depOptions () {
      return Array.isArray(this.tree) 
      ? this.tree.map(d => ({ label: d.nombre_departamento, value: d.id })) 
      : [];

    },
    provOptions () {
      const depId = this.filters.departamento_id
      if (!depId) return []
      const dep = (this.tree || []).find(d => d.id === depId)
      return (dep?.provincias || []).map(p => ({ label: p.nombre_provincia, value: p.id }))
    },
    munOptions () {
      const depId = this.filters.departamento_id
      const provId = this.filters.provincia_id
      if (!depId || !provId) return []
      const dep = (this.tree || []).find(d => d.id === depId)
      const prov = (dep?.provincias || []).find(p => p.id === provId)
      return (prov?.municipios || []).map(m => ({ label: m.nombre_municipio, value: m.id }))
    }
  },
  mounted () {
    this.loadTree()
    this.fetchPage()
  },
  methods: {
    async genrarExcel(){
      this.loading = true
      await this.$axios.post('productorExcel',       {
            page: this.pagination.page,
            per_page: this.pagination.perPage,
            sort_by: 'id',
            sort_dir: 'desc',
            search: this.filters.search || undefined,
            estado: this.filters.estado || undefined,
            sexo: this.filters.sexo || undefined,
            fecha_desde: this.filters.fecha_desde || undefined,
            fecha_hasta: this.filters.fecha_hasta || undefined,
            departamento_id: this.filters.departamento_id || undefined,
            provincia_id: this.filters.provincia_id || undefined,
            municipio_id: this.filters.municipio_id || undefined
    }, { responseType: 'blob' }).then((res) => {
            const blob = new Blob([res.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
        const url = window.URL.createObjectURL(blob)
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', 'proveedores.xlsx') // nombre del archivo
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
        window.URL.revokeObjectURL(url)
        }).catch((e) => {
          this.$alert?.error?.(e.response?.data?.message || 'No se pudo generar el reporte.')
        }).finally(() => {
          this.loading = false
        })
    },
    chipColor (estado) {
      if (estado === 'VIGENTE' || estado === 'ACTIVO') return 'green'
      if (estado === 'VENCIDO' || estado === 'INACTIVO') return 'red'
      return 'grey'
    },

    async loadTree () {
      this.loadingTree = true
      try {
        const { data } = await this.$axios.get('geo/tree')
        this.tree = data
      } catch (e) {
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo cargar el árbol geográfico')
      } finally {
        this.loadingTree = false
      }
    },

    async fetchPage () {
      this.loading = true
      this.rows=[]
      try {
        const { data } = await this.$axios.get('productores', {
          params: {
            page: this.pagination.page,
            per_page: this.pagination.perPage,
            sort_by: 'id',
            sort_dir: 'desc',
            search: this.filters.search || undefined,
            estado: this.filters.estado || undefined,
            sexo: this.filters.sexo || undefined,
            fecha_desde: this.filters.fecha_desde || undefined,
            fecha_hasta: this.filters.fecha_hasta || undefined,
            departamento_id: this.filters.departamento_id || undefined,
            provincia_id: this.filters.provincia_id || undefined,
            municipio_id: this.filters.municipio_id || undefined
          }
        })
        console.log(data)
        this.rows = data.data
        this.serverCount = data.total
        if (this.pagination.page > this.pageCount) this.pagination.page = this.pageCount
      } catch (e) {
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo cargar productores')
      } finally {
        this.loading = false
      }
    },

    changePage () {
      this.fetchPage()
    },
    changePerPage () {
      this.pagination.page = 1
      this.fetchPage()
    },
    applyFilters () {
      this.pagination.page = 1
      this.fetchPage()
    },
    onDepChange () {
      this.filters.provincia_id = null
      this.filters.municipio_id = null
      this.applyFilters()
    },
    onProvChange () {
      this.filters.municipio_id = null
      this.applyFilters()
    },

    openDetails (row) {
      this.$router.push('/productores/editar/' + row.id)
    }
  }
}
</script>

<style scoped>
.row-click { cursor: pointer; }
.row-click:hover { background: rgba(0,0,0,0.04); }
</style>
