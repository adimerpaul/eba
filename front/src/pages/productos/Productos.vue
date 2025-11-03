<template>
  <q-page class="q-pa-md">
    <q-table
      :rows="rows"
      :columns="columns"
      row-key="id"
      flat bordered dense wrap-cells
      :rows-per-page-options="[0]"
      title="Productos"
      :loading="loading"
      :filter="filter"
    >
      <template #top-right>
        <div class="row items-center q-gutter-sm">
          <q-select
            v-model="tipo"
            :options="tipoOptions"
            option-value="id"
            option-label="nombre_tipo"
            emit-value map-options
            dense filled clearable
            label="Tipo"
            style="min-width: 180px"
            @update:model-value="fetch"
          />
          <q-input v-model="filter" dense outlined placeholder="Buscar código / nombre / barra" @update:model-value="fetchDebounced">
            <template #append><q-icon name="search" /></template>
          </q-input>
          <q-btn color="positive" icon="add_circle_outline" label="Nuevo" no-caps :loading="loading" @click="openCreate"/>
          <q-btn color="primary"  icon="refresh"            label="Actualizar" no-caps :loading="loading" @click="fetch"/>
          <q-btn color="green"    label="EXCEL" :loading="loading" @click="generarExcel"/>
        </div>
      </template>

      <template #body-cell-imagen="props">
        <q-td :props="props">
          <q-avatar square size="42px" v-if="props.row.imagen">
            <q-img :src="imgUrl(props.row.imagen)" :ratio="1" />
          </q-avatar>
          <q-icon name="image_not_supported" v-else />
        </q-td>
      </template>

      <template #body-cell-tipo="props">
        <q-td :props="props">{{ findTipoNombre(props.row.tipo_id) }}</q-td>
      </template>

      <template #body-cell-actions="props">
        <q-td :props="props" class="text-right">
          <q-btn-dropdown label="Opciones" dense color="primary" no-caps size="10px">
            <q-list>
              <q-item clickable v-ripple @click="verkardex(props.row)" v-close-popup>
                <q-item-section avatar><q-icon name="article" /></q-item-section>
                <q-item-section><span >Kardex</span></q-item-section>
              </q-item>
              <q-item clickable v-ripple @click="openImagen(props.row)" v-close-popup>
                <q-item-section avatar><q-icon name="image" /></q-item-section>
                <q-item-section>Imagen</q-item-section>
              </q-item>
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

    <!-- Dialogo crear/editar -->
    <q-dialog v-model="dlg.open" persistent>
      <q-card style="min-width: 520px; max-width: 760px">
        <q-card-section class="text-h6">
          {{ dlg.mode === 'create' ? 'Nuevo Producto' : 'Editar Producto' }}
        </q-card-section>
        <q-separator/>
        <q-card-section>
          <q-form @submit.prevent="onSubmit" ref="formRef">
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-6">
                <q-select
                  v-model="form.tipo_id"
                  :options="tipoOptions"
                  option-value="id"
                  option-label="nombre_tipo"
                  emit-value map-options
                  dense filled
                  label="Tipo"
                />
              </div>
              <div class="col-12 col-md-6">
                <q-input v-model="form.codigo_producto" dense filled label="Código" />
              </div>
              <div class="col-12">
                <q-input v-model="form.nombre_producto" dense filled label="Nombre" />
              </div>
              <div class="col-12 col-md-6">
                <q-input v-model="form.presentacion" dense filled label="Presentación" />
              </div>
              <div class="col-12 col-md-6">
                <q-input v-model.number="form.cantidad_kg" type="number" min="0" step="0.01" dense filled label="Cantidad (kg)" />
              </div>
              <div class="col-12 col-md-6">
                <q-input v-model.number="form.costo" type="number" min="0" step="0.01" dense filled label="Costo" />
              </div>
              <div class="col-12 col-md-6">
                <q-input v-model.number="form.precio" type="number" min="0" step="0.01" dense filled label="Precio" />
              </div>
              <div class="col-12 col-md-6">
                <q-input v-model="form.fecha_vencimiento" type="date" dense filled label="Fecha venc." />
              </div>
              <div class="col-12 col-md-6">
                <q-input v-model="form.nro_lote" dense filled label="Nro lote" />
              </div>
              <div class="col-12 col-md-6">
                <q-input v-model="form.codigo_barra" dense filled label="Código de barras" />
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

    <!-- Diálogo de imagen -->
    <q-dialog v-model="dlgImg.open" persistent>
      <q-card style="min-width: 420px">
        <q-card-section class="text-h6">Imagen de producto</q-card-section>
        <q-card-section>
          <div class="row items-center q-col-gutter-md">
            <div class="col-auto">
              <q-avatar square size="140px">
                <q-img v-if="dlgImg.row?.imagen" :src="imgUrl(dlgImg.row.imagen)" :ratio="1" />
                <q-icon v-else name="image" size="80px" />
              </q-avatar>
            </div>
            <div class="col">
              <input ref="fileInput" type="file" accept="image/*" @change="onFileChange" style="display:none" />
              <q-btn outline no-caps color="primary" icon="upload" label="Subir imagen" @click="$refs.fileInput.click()" :loading="uploading" />
            </div>
          </div>
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Cerrar" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>

        <q-dialog v-model="dialogKardex" >
      <q-card style="min-width: 400px; max-width: 90vw">
        <q-card-section class="row items-center">
          <q-avatar icon="article" color="primary" text-color="white" size="lg" />
          <span class="q-ml-sm">Seleccione el rango de fechas producto <b>{{ producto.nombre_producto }}</b></span>
        </q-card-section>
        <q-card-section>
          <div class="row">
            <div class="col-6">
              <q-input dense v-model="inicio" label="Desde" type="date"  filled @update:model-value="generarKardex"/>
            </div>
            <div class="col-6">
              <q-input dense v-model="fin" label="Hasta" type="date" filled @update:model-value="generarKardex"/>
            </div>
          </div>
          <br>
          <q-table
            :rows="kardex"
            :columns="colkardex"
            row-key="name"
            dense
          />
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import moment from 'moment';
export default {
  name: 'ProductosPage',
  data () {
    return {
      inicio: moment().format('YYYY-MM-DD'),
      fin: moment().format('YYYY-MM-DD'),
      dialogKardex: false,
      kardex: [],
      loading: false,
      rows: [],
      columns: [
        { name: 'actions', label: 'Acciones', align: 'right' },
        { name: 'imagen', label: 'Imagen', align: 'left', field: 'imagen' },
        { name: 'codigo_producto', label: 'Código', align: 'left', field: 'codigo_producto' },
        { name: 'nombre_producto', label: 'Nombre', align: 'left', field: 'nombre_producto' },
        { name: 'tipo', label: 'Tipo', align: 'left', field: 'tipo_id' },
        { name: 'presentacion', label: 'Presentación', align: 'left', field: 'presentacion' },
        { name: 'cantidad_kg', label: 'Kg', align: 'right', field: 'cantidad_kg', format: v => Number(v||0).toFixed(2) },
        { name: 'costo', label: 'Costo', align: 'right', field: 'costo', format: v => Number(v||0).toFixed(2) },
        { name: 'precio', label: 'Precio', align: 'right', field: 'precio', format: v => Number(v||0).toFixed(2) },
        { name: 'fecha_vencimiento', label: 'Vence', align: 'left', field: 'fecha_vencimiento' },
        { name: 'nro_lote', label: 'Nro lote', align: 'left', field: 'nro_lote' },
        { name: 'codigo_barra', label: 'Barras', align: 'left', field: 'codigo_barra' },
      ],
      filter: '',
      tipo: null,
      tipoOptions: [],

      // CRUD
      dlg: { open: false, mode: 'create', row: null },
      form: {
        tipo_id: null,
        codigo_producto: '',
        nombre_producto: '',
        presentacion: 'PIEZA',
        cantidad_kg: 0,
        costo: 0,
        precio: 0,
        fecha_vencimiento: '',
        nro_lote: '',
        codigo_barra: ''
      },
      saving: false,

      // Imagen
      dlgImg: { open: false, row: null },
      uploading: false,
      colkardex: [
        { name: 'fecha_registro', label: 'Fecha', align: 'left', field: 'fecha_registro' },
        { name: 'cantidad_procesada', label: 'Procesada', align: 'right', field: 'cantidad_procesada', format: v => Number(v||0).toFixed(2) },
        { name: 'cantidad_salida', label: 'Salida', align: 'right', field: 'cantidad_salida', format: v => Number(v||0).toFixed(2) },
      ]
    }
  },
  computed: {

    canSubmit () {
      const f = this.form
      return !!(f.tipo_id && f.codigo_producto && f.nombre_producto)
    }
  },
  mounted () {
    this.fetchTipos()
  },
  methods: {
        generarExcel(){
      this.loading=true
          this.$axios.post('productoExcel', {
          responseType: 'blob' // 👈 importante para manejar archivos binarios
        }).then((res) => {
                  // Crear blob y enlace de descarga
        const blob = new Blob([res.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
        const url = window.URL.createObjectURL(blob)
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', 'productos.xlsx') // nombre del archivo
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
    verkardex (p) {
       this.producto = p
       this.dialogKardex = true
       this.generarKardex()
    },
    generarKardex () {

        if(!this.inicio || !this.fin || !this.producto || this.inicio > this.fin){
            this.$q.notify({ type: 'negative', message: 'Seleccione el rango de fechas' })
            return
        }
        this.loading = true
        this.$axios.post('getKardex',{inicio: this.inicio, fin: this.fin, productoid: this.producto.id}).then(res => {
          console.log(res.data)
            this.kardex = res.data
        }).catch(e => {
            this.$q.notify({ type: 'negative', message: e.response?.data?.message || 'No se pudo generar el reporte.' })
        }).finally(() => {
            this.loading = false
        })
    },
    imgUrl (p) {
      return p?.startsWith('http') ? p : `${this.$url}/../storage/${p}`
    },
    findTipoNombre (id) {
      const t = this.tipoOptions.find(x => x.id === id)
      return t ? t.nombre : id
    },

    async fetch () {
      this.loading = true
      try {
        const params = {}
        if (this.filter) params.q = this.filter
        if (this.tipo) params.tipo = this.tipo
        const { data } = await this.$axios.get('/productos', { params })
        this.rows = Array.isArray(data) ? data : (data.data || [])
        console.log(data)
      } catch (e) {
        this.$q.notify({ type: 'negative', message: e.response?.data?.message || 'Error al listar productos' })
      } finally { this.loading = false }
    },

    fetchDebounced: (() => {
      let t
      return function () {
        clearTimeout(t)
        t = setTimeout(() => this.fetch(), 350)
      }
    })(),

    async fetchTipos () {
      try {
        const { data } = await this.$axios.get('/tipo-productos')
        this.tipoOptions = data
        // asignar a tipo el que indique 'Producto Terminado' de tipoOptions
        this.tipo = this.tipoOptions.find(x => x.nombre_tipo === 'Producto Terminado')??null
        this.fetch()
      } catch (e) { /* noop */ }
    },

    openCreate () {
      this.dlg = { open: true, mode: 'create', row: null }
      this.form = {
        tipo_id: null,
        codigo_producto: '',
        nombre_producto: '',
        presentacion: 'PIEZA',
        cantidad_kg: 0,
        costo: 0,
        precio: 0,
        fecha_vencimiento: '',
        nro_lote: '',
        codigo_barra: ''
      }
    },

    openEdit (row) {
      this.dlg = { open: true, mode: 'edit', row }
      this.form = {
        tipo_id: row.tipo_id,
        codigo_producto: row.codigo_producto,
        nombre_producto: row.nombre_producto,
        presentacion: row.presentacion || 'PIEZA',
        cantidad_kg: Number(row.cantidad_kg || 0),
        costo: Number(row.costo || 0),
        precio: Number(row.precio || 0),
        fecha_vencimiento: row.fecha_vencimiento || '',
        nro_lote: row.nro_lote || '',
        codigo_barra: row.codigo_barra || ''
      }
    },

    async onSubmit () {
      if (!this.canSubmit) return
      this.saving = true
      try {
        if (this.dlg.mode === 'create') {
          await this.$axios.post('/productos', this.form)
        } else {
          await this.$axios.put(`/productos/${this.dlg.row.id}`, this.form)
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
        message: `¿Eliminar el producto ${row.nombre_producto}?`,
        cancel: true, persistent: true
      }).onOk(async () => {
        try {
          await this.$axios.delete(`/productos/${row.id}`)
          this.$q.notify({ type: 'positive', message: 'Eliminado' })
          await this.fetch()
        } catch (e) {
          this.$q.notify({ type: 'negative', message: e.response?.data?.message || 'No se pudo eliminar' })
        }
      })
    },

    openImagen (row) {
      this.dlgImg = { open: true, row }
    },

    async onFileChange (ev) {
      const file = ev.target.files?.[0]
      if (!file || !this.dlgImg.row) return
      this.uploading = true
      try {
        const fd = new FormData()
        fd.append('file', file)
        const { data } = await this.$axios.post(`/productos/${this.dlgImg.row.id}/imagen`, fd, {
          headers: { 'Content-Type': 'multipart/form-data' }
        })
        this.$q.notify({ type: 'positive', message: 'Imagen actualizada' })
        this.dlgImg.row = data.producto
        await this.fetch()
      } catch (e) {
        this.$q.notify({ type: 'negative', message: e.response?.data?.message || 'No se pudo subir' })
      } finally {
        this.uploading = false
      }
    }
  }
}
</script>

<style scoped>
/* Ajustes opcionales */
</style>
