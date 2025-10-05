<template>
  <q-page class="q-pa-md">

    <!-- Header -->
    <div class="row items-center q-gutter-sm q-mb-sm">
      <q-avatar icon="person_search" color="primary" text-color="white" />
      <div class="text-h6 text-weight-bold">Productores</div>
      <q-space />
      <q-btn color="primary" icon="forest" label="Actualizar árbol geo" @click="loadTree" :loading="loadingTree" no-caps />
    </div>

    <!-- Filtros -->
    <q-card flat bordered class="q-mb-md">
      <q-card-section class="row q-col-gutter-sm">
        <div class="col-12 col-md-3">
          <q-input v-model="filters.search" dense outlined placeholder="Buscar (RUNSA, nombre, carnet, celular...)" @keyup.enter="applyFilters">
            <template #append><q-icon name="search" /></template>
          </q-input>
        </div>

        <div class="col-12 col-md-2">
          <q-select v-model="filters.estado" :options="['VIGENTE','VENCIDO','ACTIVO','INACTIVO']" dense outlined label="Estado" clearable />
        </div>

        <div class="col-12 col-md-2">
          <q-select v-model="filters.sexo" :options="sexoOptions" dense outlined label="Sexo" clearable />
        </div>

        <div class="col-12 col-md-2">
          <q-input v-model="filters.fecha_desde" type="date" dense outlined label="Desde" />
        </div>
        <div class="col-12 col-md-2">
          <q-input v-model="filters.fecha_hasta" type="date" dense outlined label="Hasta" />
        </div>

        <div class="col-12 col-md-3">
          <q-select v-model="filters.departamento_id" :options="depOptions" option-label="label" option-value="value"
                    emit-value map-options dense outlined label="Departamento" @update:model-value="onDepChange" />
        </div>
        <div class="col-12 col-md-3">
          <q-select v-model="filters.provincia_id" :options="provOptions" option-label="label" option-value="value"
                    emit-value map-options dense outlined label="Provincia" :disable="!filters.departamento_id" @update:model-value="onProvChange" />
        </div>
        <div class="col-12 col-md-3">
          <q-select v-model="filters.municipio_id" :options="munOptions" option-label="label" option-value="value"
                    emit-value map-options dense outlined label="Municipio" :disable="!filters.provincia_id" />
        </div>

        <div class="col-12 col-md-auto q-gutter-sm">
          <q-btn color="secondary" icon="refresh" label="Aplicar" :loading="loading" @click="applyFilters" no-caps />
<!--          <q-btn color="positive" icon="add_circle" label="Nuevo" :loading="saving" @click="openNew" no-caps />-->
        </div>
      </q-card-section>
    </q-card>

    <!-- Tabla (q-markup-table) -->
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
        <tr v-for="row in rows" :key="row.id" class="row-click" @click="openDetails(row)">
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
        <tr v-if="!loading && rows.length === 0">
          <td colspan="9" class="text-center text-grey q-pa-md">No hay resultados</td>
        </tr>
        </tbody>
      </q-markup-table>

      <!-- Overlay de carga -->
      <div v-if="loading" class="absolute-full flex flex-center bg-transparent">
        <q-circular-progress indeterminate size="40px" />
      </div>

      <!-- Pie de paginación -->
      <div class="q-pa-sm row items-center justify-between">
        <div class="text-caption">Total: <b>{{ serverCount.toLocaleString() }}</b></div>
        <div class="row items-center q-gutter-sm">
          <q-select
            v-model="pagination.perPage"
            :options="[10,25,50,100,150,200]"
            dense outlined
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

    <!-- Diálogo Detalle / Show con Tabs -->
<!--    <q-dialog v-model="details.open" persistent maximized transition-show="slide-up" transition-hide="slide-down">-->
<!--      <q-card>-->
<!--        <q-bar class="bg-grey-2">-->
<!--          <div class="text-subtitle2">-->
<!--            Productor: <b>{{ details.form?.nombre }} {{ details.form?.apellidos }}</b>-->
<!--            <span class="text-caption q-ml-sm">(#{{ details.form?.id }})</span>-->
<!--          </div>-->
<!--          <q-space />-->
<!--          <q-btn dense flat icon="refresh" :loading="details.loading" @click="reloadDetails" />-->
<!--          <q-btn dense flat icon="close" v-close-popup />-->
<!--        </q-bar>-->

<!--        <q-tabs v-model="details.tab" dense class="text-primary" align="left">-->
<!--          <q-tab name="datos" icon="badge" label="Datos" />-->
<!--          <q-tab name="apiarios" icon="hive" label="Apiarios" />-->
<!--          <q-tab name="certs" icon="verified" label="Certificaciones" />-->
<!--          <q-tab name="mapa" icon="map" label="Mapa" />-->
<!--        </q-tabs>-->
<!--        <q-separator />-->

<!--        <q-tab-panels v-model="details.tab" animated>-->
<!--          &lt;!&ndash; TAB: DATOS (editar) &ndash;&gt;-->
<!--          <q-tab-panel name="datos">-->
<!--            <q-card-section>-->
<!--              <q-form @submit="saveDetails">-->
<!--                <div class="row q-col-gutter-sm">-->

<!--                  <div class="col-6 col-sm-2">-->
<!--                    <q-input v-model="details.form.runsa" label="RUNSA" dense outlined />-->
<!--                  </div>-->
<!--                  <div class="col-6 col-sm-2">-->
<!--                    <q-input v-model="details.form.sub_codigo" label="Sub Código" dense outlined />-->
<!--                  </div>-->

<!--                  <div class="col-12 col-sm-4">-->
<!--                    <q-input v-model="details.form.nombre" label="Nombre" dense outlined :rules="[v=>!!v || 'Requerido']" />-->
<!--                  </div>-->
<!--                  <div class="col-12 col-sm-4">-->
<!--                    <q-input v-model="details.form.apellidos" label="Apellidos" dense outlined :rules="[v=>!!v || 'Requerido']" />-->
<!--                  </div>-->

<!--                  <div class="col-6 col-sm-3">-->
<!--                    <q-input v-model="details.form.numcarnet" label="N° Carnet" dense outlined :rules="[v=>!!v || 'Requerido']" />-->
<!--                  </div>-->
<!--                  <div class="col-6 col-sm-2">-->
<!--                    <q-input v-model="details.form.expedido" label="Expedido" dense outlined />-->
<!--                  </div>-->
<!--                  <div class="col-12 col-sm-3">-->
<!--                    <q-input v-model="details.form.fec_nacimiento" type="date" label="Nacimiento" dense outlined />-->
<!--                  </div>-->
<!--                  <div class="col-12 col-sm-4">-->
<!--                    <q-select v-model="details.form.sexo" :options="sexoOptions" label="Sexo" dense outlined emit-value map-options />-->
<!--                  </div>-->

<!--                  <div class="col-12">-->
<!--                    <q-input v-model="details.form.direccion" type="textarea" label="Dirección" dense outlined autogrow />-->
<!--                  </div>-->

<!--                  <div class="col-12 col-sm-4">-->
<!--                    <q-input v-model="details.form.comunidad" label="Comunidad" dense outlined />-->
<!--                  </div>-->
<!--                  <div class="col-12 col-sm-4">-->
<!--                    <q-input v-model="details.form.proveedor" label="Proveedor" dense outlined />-->
<!--                  </div>-->
<!--                  <div class="col-12 col-sm-4">-->
<!--                    <q-input v-model="details.form.cip_acopio" label="CIP Acopio" dense outlined />-->
<!--                  </div>-->

<!--                  <div class="col-12 col-sm-4">-->
<!--                    <q-input v-model="details.form.num_celular" label="Celular" dense outlined />-->
<!--                  </div>-->
<!--                  <div class="col-12 col-sm-4">-->
<!--                    <q-input v-model="details.form.ocupacion" label="Ocupación" dense outlined />-->
<!--                  </div>-->
<!--                  <div class="col-12 col-sm-4">-->
<!--                    <q-input v-model.number="details.form.seleccion" type="number" label="Selección" dense outlined />-->
<!--                  </div>-->

<!--                  &lt;!&ndash; Ubicación administrativa &ndash;&gt;-->
<!--                  <div class="col-12 col-sm-4">-->
<!--                    <q-select v-model="details.form.departamento_id" :options="depOptions" option-label="label" option-value="value"-->
<!--                              emit-value map-options dense outlined label="Departamento" @update:model-value="onFormDepChange" />-->
<!--                  </div>-->
<!--                  <div class="col-12 col-sm-4">-->
<!--                    <q-select v-model="details.form.provincia_id" :options="formProvOptions" option-label="label" option-value="value"-->
<!--                              emit-value map-options dense outlined label="Provincia" :disable="!details.form.departamento_id"-->
<!--                              @update:model-value="onFormProvChange" />-->
<!--                  </div>-->
<!--                  <div class="col-12 col-sm-4">-->
<!--                    <q-select v-model="details.form.municipio_id" :options="formMunOptions" option-label="label" option-value="value"-->
<!--                              emit-value map-options dense outlined label="Municipio" :disable="!details.form.provincia_id"-->
<!--                              :rules="[v=>!!v || 'Requerido']" />-->
<!--                  </div>-->

<!--                  <div class="col-12 col-sm-4">-->
<!--                    <q-input v-model="details.form.fecha_registro" type="date" label="Fecha Registro" dense outlined />-->
<!--                  </div>-->
<!--                  <div class="col-12 col-sm-4">-->
<!--                    <q-input v-model="details.form.fecha_expiracion" type="date" label="Fecha Expiración" dense outlined />-->
<!--                  </div>-->
<!--                  <div class="col-12 col-sm-4">-->
<!--                    <q-select v-model="details.form.estado" :options="['VIGENTE','VENCIDO','ACTIVO','INACTIVO']" label="Estado" dense outlined />-->
<!--                  </div>-->

<!--                </div>-->

<!--                <div class="text-right q-mt-md">-->
<!--                  <q-btn flat label="Cerrar" color="grey" v-close-popup :loading="details.saving" />-->
<!--                  <q-btn label="Guardar" color="primary" type="submit" :loading="details.saving" class="q-ml-sm" />-->
<!--                </div>-->
<!--              </q-form>-->
<!--            </q-card-section>-->
<!--          </q-tab-panel>-->

<!--          &lt;!&ndash; TAB: APIARIOS &ndash;&gt;-->
<!--          <q-tab-panel name="apiarios">-->
<!--            <q-card-section>-->
<!--              <div class="text-subtitle1 q-mb-sm">Apiarios del productor</div>-->
<!--              <q-markup-table dense>-->
<!--                <thead>-->
<!--                <tr class="bg-grey-3">-->
<!--                  <th class="text-left">#</th>-->
<!--                  <th class="text-left">Código</th>-->
<!--                  <th class="text-left">Ubicación</th>-->
<!--                  <th class="text-left">Colmenas</th>-->
<!--                  <th class="text-left">Estado</th>-->
<!--                </tr>-->
<!--                </thead>-->
<!--                <tbody>-->
<!--                <tr v-for="(a, i) in details.apiarios" :key="a.id || i">-->
<!--                  <td>{{ i + 1 }}</td>-->
<!--                  <td>{{ a.codigo || '-' }}</td>-->
<!--                  <td>{{ a.ubicacion || '-' }}</td>-->
<!--                  <td>{{ a.colmenas ?? '-' }}</td>-->
<!--                  <td>{{ a.estado || '-' }}</td>-->
<!--                </tr>-->
<!--                <tr v-if="details.apiLoading">-->
<!--                  <td colspan="5" class="text-center">-->
<!--                    <q-circular-progress indeterminate size="28px" />-->
<!--                  </td>-->
<!--                </tr>-->
<!--                <tr v-if="!details.apiLoading && details.apiarios.length === 0">-->
<!--                  <td colspan="5" class="text-center text-grey">Sin registros</td>-->
<!--                </tr>-->
<!--                </tbody>-->
<!--              </q-markup-table>-->
<!--            </q-card-section>-->
<!--          </q-tab-panel>-->

<!--          &lt;!&ndash; TAB: CERTIFICACIONES &ndash;&gt;-->
<!--          <q-tab-panel name="certs">-->
<!--            <q-card-section>-->
<!--              <div class="text-subtitle1 q-mb-sm">Certificaciones del productor</div>-->
<!--              <q-markup-table dense>-->
<!--                <thead>-->
<!--                <tr class="bg-grey-3">-->
<!--                  <th class="text-left">#</th>-->
<!--                  <th class="text-left">Certificación</th>-->
<!--                  <th class="text-left">Entidad</th>-->
<!--                  <th class="text-left">Vigencia</th>-->
<!--                  <th class="text-left">Estado</th>-->
<!--                </tr>-->
<!--                </thead>-->
<!--                <tbody>-->
<!--                <tr v-for="(c, i) in details.certs" :key="c.id || i">-->
<!--                  <td>{{ i + 1 }}</td>-->
<!--                  <td>{{ c.nombre || '-' }}</td>-->
<!--                  <td>{{ c.entidad || '-' }}</td>-->
<!--                  <td>{{ c.vigencia || '-' }}</td>-->
<!--                  <td>{{ c.estado || '-' }}</td>-->
<!--                </tr>-->
<!--                <tr v-if="details.certsLoading">-->
<!--                  <td colspan="5" class="text-center">-->
<!--                    <q-circular-progress indeterminate size="28px" />-->
<!--                  </td>-->
<!--                </tr>-->
<!--                <tr v-if="!details.certsLoading && details.certs.length === 0">-->
<!--                  <td colspan="5" class="text-center text-grey">Sin registros</td>-->
<!--                </tr>-->
<!--                </tbody>-->
<!--              </q-markup-table>-->
<!--            </q-card-section>-->
<!--          </q-tab-panel>-->

<!--          &lt;!&ndash; TAB: MAPA &ndash;&gt;-->
<!--          <q-tab-panel name="mapa">-->
<!--            <q-card-section>-->
<!--              <div class="text-subtitle1 q-mb-sm">Mapa (referencial)</div>-->
<!--              &lt;!&ndash; Usa tu componente existente si lo tienes &ndash;&gt;-->
<!--              <MapPicker :center="mapCenter" :zoom-init="12" style="height: 60vh" />-->
<!--              <div class="text-caption text-grey q-mt-sm">-->
<!--                *Este mapa es referencial. Si deseas guardar coordenadas, añade campos latitud/longitud en el backend.-->
<!--              </div>-->
<!--            </q-card-section>-->
<!--          </q-tab-panel>-->

<!--        </q-tab-panels>-->
<!--      </q-card>-->
<!--    </q-dialog>-->

  </q-page>
</template>

<script>
// import MapPicker from 'src/components/MapPicker.vue' // si no lo tienes, quita esta línea y el tab mapa

export default {
  name: 'ProductoresPage',
  // components: { MapPicker },
  data () {
    return {
      // Datos tabla
      rows: [],
      loading: false,
      serverCount: 0,

      // Paginación manual
      pagination: { page: 1, perPage: 10 },

      // Árbol geo y filtros
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
        municipio_id: null,
      },

      // Crear nuevo (desde botón "Nuevo")
      saving: false,
      dialogNew: false, // (si quisieras un diálogo aparte para crear)

      // Detalle
      details: {
        open: false,
        tab: 'datos',
        loading: false,
        saving: false,
        form: null,
        apiarios: [],
        certs: [],
        apiLoading: false,
        certsLoading: false,
      },

      sexoOptions: [
        { label: 'Masculino', value: 1 },
        { label: 'Femenino', value: 2 },
      ],
    }
  },
  computed: {
    pageCount () {
      return Math.max(1, Math.ceil(this.serverCount / this.pagination.perPage))
    },
    depOptions () {
      return (this.tree || []).map(d => ({ label: d.nombre_departamento, value: d.id }))
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
    },

    // // opciones dependientes para el form del detalle
    // formProvOptions () {
    //   const depId = this.details.form?.departamento_id
    //   if (!depId) return []
    //   const dep = (this.tree || []).find(d => d.id === depId)
    //   return (dep?.provincias || []).map(p => ({ label: p.nombre_provincia, value: p.id }))
    // },
    // formMunOptions () {
    //   const depId = this.details.form?.departamento_id
    //   const provId = this.details.form?.provincia_id
    //   if (!depId || !provId) return []
    //   const dep = (this.tree || []).find(d => d.id === depId)
    //   const prov = (dep?.provincias || []).find(p => p.id === provId)
    //   return (prov?.municipios || []).map(m => ({ label: m.nombre_municipio, value: m.id }))
    // },
    //
    // // Centro del mapa (La Paz por defecto)
    // mapCenter () {
    //   // si tuvieras coords en el productor, úsalo. Por ahora centrado a La Paz
    //   return [-16.5, -68.15]
    // }
  },
  mounted () {
    this.loadTree()
    this.fetchPage()
  },
  methods: {
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
            municipio_id: this.filters.municipio_id || undefined,
          }
        })
        this.rows = data.data
        this.serverCount = data.total
        // en caso de que cambie el número total de páginas
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

    // openNew () {
    //   // Podrías abrir el mismo diálogo de detalles en modo "nuevo".
    //   // Para mantener simple y porque pediste show al hacer click, lo dejamos para más adelante.
    //   // this.$alert?.info?.('Usa el diálogo de Detalle para crear si lo deseas. Por ahora, centrado en el Show.')
    // },

    // ---------------- Detalle / Show ----------------
    async openDetails (row) {
      // this.details.open = true
      // this.details.tab = 'datos'
      // this.details.form = null
      // await this.reloadDetails(row.id)
      // // Cargar pestañas secundarias en paralelo
      // this.loadApiarios()
      // this.loadCerts()
      // { path: '/productores/editar/:id', component: () => import('pages/productores/ProductorEditar.vue'), meta: { requiresAuth: true, perm: 'Productores' } },
      this.$router.push('/productores/editar/' + row.id)
    },

    // async reloadDetails (id = null) {
    //   try {
    //     this.details.loading = true
    //     const productorId = id || this.details.form?.id
    //     const { data } = await this.$axios.get(`productores/${productorId}`)
    //     // mapear a form del detalle
    //     this.details.form = {
    //       id: data.id,
    //       municipio_id: data.municipio?.id || data.municipio_id || null,
    //       runsa: data.runsa || '0',
    //       sub_codigo: data.sub_codigo || '',
    //       nombre: data.nombre || '',
    //       apellidos: data.apellidos || '',
    //       numcarnet: data.numcarnet || '',
    //       expedido: data.expedido || '',
    //       fec_nacimiento: data.fec_nacimiento || null,
    //       sexo: data.sexo || null,
    //       direccion: data.direccion || '',
    //       comunidad: data.comunidad || '',
    //       proveedor: data.proveedor || '',
    //       cip_acopio: data.cip_acopio || '',
    //       num_celular: data.num_celular || '',
    //       ocupacion: data.ocupacion || '',
    //       otros: data.otros || '',
    //       seleccion: data.seleccion || 0,
    //       organizacion_id: data.organizacion?.id || data.organizacion_id || null,
    //       fecha_registro: data.fecha_registro || null,
    //       fecha_expiracion: data.fecha_expiracion || null,
    //       estado: data.estado || 'VIGENTE',
    //       // para selects dependientes
    //       departamento_id: data.municipio?.departamento_id || null,
    //       provincia_id: data.municipio?.provincia_id || null,
    //     }
    //   } catch (e) {
    //     this.$alert?.error?.(e.response?.data?.message || 'No se pudo cargar el detalle')
    //   } finally {
    //     this.details.loading = false
    //   }
    // },
    //
    // async saveDetails () {
    //   this.details.saving = true
    //   try {
    //     const p = { ...this.details.form }
    //     const send = {
    //       municipio_id: p.municipio_id,
    //       runsa: p.runsa,
    //       sub_codigo: p.sub_codigo,
    //       nombre: p.nombre,
    //       apellidos: p.apellidos,
    //       numcarnet: p.numcarnet,
    //       expedido: p.expedido,
    //       fec_nacimiento: p.fec_nacimiento,
    //       sexo: p.sexo,
    //       direccion: p.direccion,
    //       comunidad: p.comunidad,
    //       proveedor: p.proveedor,
    //       cip_acopio: p.cip_acopio,
    //       num_celular: p.num_celular,
    //       ocupacion: p.ocupacion,
    //       otros: p.otros,
    //       seleccion: p.seleccion,
    //       organizacion_id: p.organizacion_id,
    //       fecha_registro: p.fecha_registro,
    //       fecha_expiracion: p.fecha_expiracion,
    //       estado: p.estado,
    //     }
    //     await this.$axios.put(`productores/${p.id}`, send)
    //     this.$alert?.success?.('Guardado')
    //     // refrescar fila si quieres
    //     this.fetchPage()
    //   } catch (e) {
    //     this.$alert?.error?.(e.response?.data?.message || 'No se pudo guardar')
    //   } finally {
    //     this.details.saving = false
    //   }
    // },
    //
    // async loadApiarios () {
    //   this.details.apiLoading = true
    //   try {
    //     // Ajusta al endpoint real si ya lo tienes:
    //     const { data } = await this.$axios.get('apiarios', { params: { productor_id: this.details.form.id } })
    //     this.details.apiarios = Array.isArray(data?.data) ? data.data : (Array.isArray(data) ? data : [])
    //   } catch (e) {
    //     this.details.apiarios = []
    //   } finally {
    //     this.details.apiLoading = false
    //   }
    // },
    //
    // async loadCerts () {
    //   this.details.certsLoading = true
    //   try {
    //     // Ajusta al endpoint real si ya lo tienes:
    //     const { data } = await this.$axios.get('certificaciones', { params: { productor_id: this.details.form.id } })
    //     this.details.certs = Array.isArray(data?.data) ? data.data : (Array.isArray(data) ? data : [])
    //   } catch (e) {
    //     this.details.certs = []
    //   } finally {
    //     this.details.certsLoading = false
    //   }
    // },

    // selects dependientes en el form del detalle
    // onFormDepChange () {
    //   if (!this.details.form) return
    //   this.details.form.provincia_id = null
    //   this.details.form.municipio_id = null
    // },
    // onFormProvChange () {
    //   if (!this.details.form) return
    //   this.details.form.municipio_id = null
    // },
  }
}
</script>

<style scoped>
.row-click {
  cursor: pointer;
}
.row-click:hover {
  background: rgba(0,0,0,0.04);
}
</style>
