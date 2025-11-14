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

    <!-- PANEL DE TRAZABILIDAD POR TEMPORADA -->
    <!-- Permite visualizar entregas mensuales en tabla principal -->
    <q-card flat bordered class="q-mb-md">
      <q-card-section class="row q-col-gutter-md items-center bg-blue-1">
        <div class="col-auto">
          <q-toggle
            v-model="trazabilidadActiva"
            label="Ver Trazabilidad por Temporada"
            color="primary"
            @update:model-value="toggleTrazabilidad"
            icon="analytics"
          />
        </div>

        <template v-if="trazabilidadActiva">
          <div class="col-auto">
            <q-select
              v-model="trazabilidad.gestion"
              :options="gestionesOptions"
              label="Gestion"
              dense outlined
              style="min-width: 130px"
              @update:model-value="cargarTrazabilidad"
              :loading="loadingTrazabilidad"
            >
              <template v-slot:prepend>
                <q-icon name="event" />
              </template>
            </q-select>
          </div>

          <div class="col-auto">
            <q-select
              v-model="trazabilidad.producto_id"
              :options="productosOptions"
              option-label="nombre_producto"
              option-value="id"
              emit-value
              map-options
              label="Producto"
              dense outlined
              style="min-width: 200px"
              @update:model-value="cargarTrazabilidad"
              :loading="loadingTrazabilidad"
            >
              <template v-slot:prepend>
                <q-icon name="inventory" />
              </template>
            </q-select>
          </div>

          <div class="col-auto">
            <q-chip color="primary" text-color="white" dense>
              <q-icon name="info" size="16px" class="q-mr-xs" />
              Temporada {{ trazabilidad.gestion }}-07 a {{ trazabilidad.gestion + 1 }}-06
            </q-chip>
          </div>
        </template>
      </q-card-section>
    </q-card>

    <q-card flat bordered class="relative-position">
      <q-markup-table dense wrap-cells>
        <thead>
        <tr class="bg-primary text-white">
          <!-- Columnas base mantenidas para funcionalidad original -->
          <th class="text-left">ID</th>
          <th class="text-left">RUNSA</th>
          <th class="text-left" v-if="!trazabilidadActiva">Sub Cod.</th>
          <th class="text-left">Nombre completo</th>
          <th class="text-left" v-if="!trazabilidadActiva">CI</th>
          <th class="text-left" v-if="!trazabilidadActiva">Celular</th>
          <th class="text-left" v-if="!trazabilidadActiva">Comunidad</th>
          <th class="text-left">Estado</th>
          <th class="text-left" v-if="!trazabilidadActiva">Registro</th>

          <!-- Columnas dinamicas mensuales cuando trazabilidad esta activa -->
          <th v-if="trazabilidadActiva" 
              v-for="mes in mesesTemporada" 
              :key="mes.offset"
              class="text-right bg-blue-10 mes-column"
          >
            <div class="text-caption">{{ mes.nombre }}</div>
            <div class="text-caption text-weight-light">{{ mes.offset <= 5 ? trazabilidad.gestion : trazabilidad.gestion + 1 }}</div>
          </th>

          <!-- Columna Total cuando trazabilidad esta activa -->
          <th v-if="trazabilidadActiva" class="text-right bg-blue-10 total-column">
            <div class="text-caption">TOTAL</div>
            <div class="text-caption text-weight-light">KG</div>
          </th>
        </tr>
        </thead>
        <tbody>
        <tr
          v-for="row in rows"
          :key="row.id"
          class="row-click"
          @click="openDetails(row)"
        >
          <!-- Columnas base originales -->
          <td class="text-left">{{ row.id }}</td>
          <td class="text-left">{{ row.runsa || '-' }}</td>
          <td class="text-left" v-if="!trazabilidadActiva">{{ row.sub_codigo || '-' }}</td>
          <td class="text-left">
            <div class="text-weight-medium">{{ row.nombre_completo }}</div>
            <div class="text-caption text-grey-7">
              {{ row.municipio?.nombre_municipio || '-' }}
            </div>
          </td>
          <td class="text-left" v-if="!trazabilidadActiva">{{ row.numcarnet }}</td>
          <td class="text-left" v-if="!trazabilidadActiva">{{ row.num_celular || '-' }}</td>
          <td class="text-left" v-if="!trazabilidadActiva">{{ row.comunidad || '-' }}</td>
          <td class="text-left">
            <q-chip :color="chipColor(row.estado)" text-color="white" size="xs" class="text-bold">
              {{ row.estado }}
            </q-chip>
          </td>
          <td class="text-left" v-if="!trazabilidadActiva">{{ row.fecha_registro }}</td>

          <!-- Columnas dinamicas de acopios mensuales -->
          <td v-if="trazabilidadActiva"
              v-for="mes in mesesTemporada"
              :key="`${row.id}-${mes.offset}`"
              class="text-right mes-column"
              :class="[
                row.acopios_meses?.[mes.offset] > 0 ? colorPorCantidad(row.acopios_meses[mes.offset]) : 'bg-grey-2'
              ]"
          >
            <template v-if="row.acopios_meses?.[mes.offset] > 0">
              <div class="text-weight-medium">
                {{ formatNumber(row.acopios_meses[mes.offset]) }}
              </div>
            </template>
            <span v-else class="text-grey-5">-</span>
          </td>

          <!-- Columna Total -->
          <td v-if="trazabilidadActiva" class="text-right text-weight-bold text-primary total-column">
            {{ formatNumber(row.total_acopios || 0) }}
          </td>
        </tr>
        <tr v-if="!loading && rows?.length === 0">
          <td :colspan="trazabilidadActiva ? 15 : 9" class="text-center text-grey q-pa-md">No hay resultados</td>
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
      ],

      // NUEVO: Datos para funcionalidad de trazabilidad por temporada
      // Permite visualizar entregas mensuales en la tabla principal
      trazabilidadActiva: false,
      trazabilidad: {
        gestion: moment().year(),
        producto_id: 1
      },
      gestionesOptions: [],
      productosOptions: [],
      loadingTrazabilidad: false,
      mesesTemporada: [
        { offset: 0, nombre: 'Jul' },
        { offset: 1, nombre: 'Ago' },
        { offset: 2, nombre: 'Sep' },
        { offset: 3, nombre: 'Oct' },
        { offset: 4, nombre: 'Nov' },
        { offset: 5, nombre: 'Dic' },
        { offset: 6, nombre: 'Ene' },
        { offset: 7, nombre: 'Feb' },
        { offset: 8, nombre: 'Mar' },
        { offset: 9, nombre: 'Abr' },
        { offset: 10, nombre: 'May' },
        { offset: 11, nombre: 'Jun' }
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
    this.generarGestiones()
    this.cargarProductos()
    this.fetchPage()
  },
  methods: {
    async genrarExcel(){
      this.loading = true
      
      // PREPARAR PAYLOAD CON PARAMETROS DE TRAZABILIDAD SI ESTA ACTIVA
      // Permite exportar Excel con columnas mensuales cuando modo trazabilidad esta habilitado
      const payload = {
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
      };
      
      // Si la trazabilidad esta activa, agregar parametros adicionales
      if (this.trazabilidadActiva) {
        payload.incluir_trazabilidad = true;
        payload.gestion = this.trazabilidad.gestion;
        payload.producto_id = this.trazabilidad.producto_id;
      }
      
      await this.$axios.post('productorExcel', payload, { responseType: 'blob' }).then((res) => {
            const blob = new Blob([res.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
        const url = window.URL.createObjectURL(blob)
        const link = document.createElement('a')
        link.href = url
        
        // Nombre de archivo diferente si incluye trazabilidad
        const fileName = this.trazabilidadActiva ? 'proveedores_trazabilidad.xlsx' : 'proveedores.xlsx';
        link.setAttribute('download', fileName)
        
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
        this.rows = data.data
        this.serverCount = data.total
        
        // NUEVO: Cargar datos de trazabilidad si el modo esta activo
        // Permite visualizar entregas mensuales despues de cargar productores
        if (this.trazabilidadActiva) {
          await this.cargarTrazabilidad()
        }
        
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
    },

    // NUEVOS METODOS PARA TRAZABILIDAD POR TEMPORADA
    // Permiten visualizar entregas mensuales en tabla principal

    generarGestiones() {
      const currentYear = moment().year()
      const years = []
      for (let i = currentYear; i >= 2020; i--) {
        years.push(i)
      }
      this.gestionesOptions = years
    },

    async cargarProductos() {
      try {
        const { data } = await this.$axios.get('/productos/tipo/1')
        this.productosOptions = data?.data || data || []
      } catch (e) {
        this.$alert?.error?.('No se pudieron cargar los productos')
      }
    },

    async toggleTrazabilidad(value) {
      if (value) {
        await this.cargarTrazabilidad()
      } else {
        this.limpiarAcopiosMensuales()
      }
    },

    async cargarTrazabilidad() {
      if (!this.trazabilidadActiva || this.rows.length === 0) return

      this.loadingTrazabilidad = true
      try {
        const productorIds = this.rows.map(r => r.id)

        const { data } = await this.$axios.post('/productores/acopios-gestion-lote', {
          productor_ids: productorIds,
          gestion: this.trazabilidad.gestion,
          producto_id: this.trazabilidad.producto_id
        })

        this.rows.forEach(row => {
          const datosProductor = data.find(d => d.productor_id === row.id)
          if (datosProductor) {
            row.acopios_meses = datosProductor.meses_array
            row.total_acopios = datosProductor.total_kg
          } else {
            row.acopios_meses = {}
            row.total_acopios = 0
          }
        })
      } catch (e) {
        this.$alert?.error?.('Error al cargar datos de trazabilidad')
      } finally {
        this.loadingTrazabilidad = false
      }
    },

    limpiarAcopiosMensuales() {
      this.rows.forEach(row => {
        row.acopios_meses = {}
        row.total_acopios = 0
      })
    },

    formatNumber(value) {
      const num = Number(value)
      return isNaN(num) ? '0.00' : num.toFixed(2)
    },

    colorPorCantidad(cantidad) {
      const num = Number(cantidad)
      if (num > 30) return 'cantidad-alta'
      if (num >= 10) return 'cantidad-media'
      return 'cantidad-baja'
    },

    mesConAnio(mes) {
      const anio = mes.offset <= 5 
        ? this.trazabilidad.gestion 
        : this.trazabilidad.gestion + 1
      return `${mes.nombre} ${anio.toString().slice(-2)}`
    }
  }
}
</script>

<style scoped>
.row-click { cursor: pointer; }
.row-click:hover { background: rgba(0,0,0,0.04); }

/* Estilos para columnas de trazabilidad mensual */
.mes-column {
  min-width: 80px;
  max-width: 100px;
  white-space: nowrap;
  padding: 8px 4px;
}

.cantidad-alta {
  background-color: #e8f5e9;
  color: #2e7d32;
  font-weight: 600;
}

.cantidad-media {
  background-color: #fff8e1;
  color: #f57c00;
  font-weight: 500;
}

.cantidad-baja {
  background-color: #f5f5f5;
  color: #757575;
}

.total-column {
  background-color: #e3f2fd;
  font-weight: bold;
  min-width: 100px;
  position: sticky;
  right: 0;
}
</style>
