<template>
  <q-page class="q-pa-md">
    <q-card flat bordered>
      <q-card-section class="row items-center q-gutter-sm">
        <div class="text-h6">Gestionar Ventas</div>
        <q-space/>
        <q-input v-model="inicio" type="date" label="Fecha inicio" dense outlined @update:model-value="fetch" />
        <q-input v-model="fin" type="date" label="Fecha fin" dense outlined @update:model-value="fetch" />
        <q-input v-model="filter" dense outlined placeholder="Buscar cliente / factura / guía" @update:model-value="fetchDebounced">
          <template #append><q-icon name="search"/></template>
        </q-input>
        <q-btn color="positive" icon="add_circle_outline" label="Crear venta" no-caps @click="$router.push('/ventas/crear')"/>
        <q-btn color="primary" icon="refresh" label="Actualizar" no-caps :loading="loading" @click="fetch"/>
        <q-btn color="green"  label="EXCEL" @click="generarExcel" :loading="loading"/>
      </q-card-section>

      <q-separator/>

      <q-table
        flat bordered dense wrap-cells
        :rows="rows" :columns="columns"
        row-key="id"
        :loading="loading"
        :rows-per-page-options="[0]"
      >
        <template #body-cell-actions="props">
          <q-td :props="props" class="text-right">
<!--            <q-btn dense flat icon="visibility" @click="openDetalle(props.row)"/>-->
<!--            <q-btn dense flat icon="print" color="primary" @click="printVenta(props.row)"/>-->
            <q-btn-dropdown dense label="Opciones" color="primary" no-caps size="10px">
              <q-list>
                <q-item clickable v-ripple @click="openDetalle(props.row)" v-close-popup>
                  <q-item-section avatar><q-icon name="visibility"/></q-item-section>
                  <q-item-section>Ver detalle</q-item-section>
                </q-item>
                <q-item clickable v-ripple @click="printVenta(props.row)" v-close-popup>
                  <q-item-section avatar><q-icon name="print" color="primary"/></q-item-section>
                  <q-item-section>Imprimir nota</q-item-section>
                </q-item>
              </q-list>
            </q-btn-dropdown>
          </q-td>
        </template>

        <template #body-cell-cliente="p">
          <q-td :props="p">{{ p.row.cliente?.nombre_cliente || '-' }}</q-td>
        </template>

        <template #body-cell-fecha_venta="p">
          <q-td :props="p">{{ fmtDate(p.row.fecha_venta) }}</q-td>
        </template>

        <template #body-cell-precio_total="p">
          <q-td :props="p" class="text-right">{{ fmt(p.row.precio_total) }}</q-td>
        </template>
      </q-table>
    </q-card>

    <!-- Detalle -->
    <q-dialog v-model="dlg.open">
      <q-card style="min-width: 640px">
        <q-card-section class="text-h6">Detalle de Venta #{{ dlg.row?.id }}</q-card-section>
        <q-card-section class="q-pt-none">
          <div class="q-mb-sm"><b>Cliente:</b> {{ dlg.row?.cliente?.nombre_cliente }}</div>
          <div class="q-mb-sm"><b>Fecha:</b> {{ fmtDate(dlg.row?.fecha_venta) }}</div>
          <q-markup-table dense flat bordered>
            <thead>
            <tr>
              <th>Lote</th>
              <th>Producto</th>
              <th class="text-right">Kg</th>
              <th class="text-right">Precio</th>
              <th class="text-right">Subtotal</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="d in dlg.detalles" :key="d.id">
              <td>{{ d.lote?.codigo_lote }}</td>
              <td>{{ d.producto?.nombre_producto }}</td>
              <td class="text-right">{{ fmt(d.cantidad_salida) }}</td>
              <td class="text-right">{{ fmt(d.precio_venta) }}</td>
              <td class="text-right">{{ fmt(d.cantidad_salida * d.precio_venta) }}</td>
            </tr>
            <tr>
              <td colspan="4" class="text-right text-bold">Total</td>
              <td class="text-right text-bold">{{ fmt(dlg.row?.precio_total) }}</td>
            </tr>
            </tbody>
          </q-markup-table>
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Cerrar" v-close-popup/>
          <q-btn color="primary" icon="print" label="Imprimir" @click="printVenta(dlg.row)"/>
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import moment from "moment";
export default {
  name: 'VentasList',
  data () {
    return {
      inicio: moment().format('YYYY-MM-DD'),
      fin: moment().format('YYYY-MM-DD'),
      loading: false,
      rows: [],
      filter: '',
      columns: [
        { name: 'actions', label: 'Acciones', align: 'right' },
        { name: 'id', label: '#', align: 'left', field: 'id' },
        { name: 'fecha_venta', label: 'Fecha', align: 'left', field: 'fecha_venta' },
        { name: 'cliente', label: 'Cliente', align: 'left', field: 'cliente' },
        { name: 'num_factura', label: 'Factura', align: 'left', field: 'num_factura' },
        { name: 'guia_remision', label: 'Guía', align: 'left', field: 'guia_remision' },
        { name: 'precio_total', label: 'Total (Bs)', align: 'right', field: 'precio_total' },
      ],
      dlg: { open: false, row: null, detalles: [] },
    }
  },
  mounted () { this.fetch() },
  methods: {
    generarExcel () {
        if(!this.inicio || !this.fin || this.inicio > this.fin){ this.$q.notify({ type: 'negative', message: 'Seleccione el rango de fechas' }); return }

        const params = {inicio: this.inicio, fin: this.fin,q:this.filter}
        this.loading=true
        this.$axios.post('ventaExcel',params,{ responseType: 'blob' }).then((res) => {
            const blob = new Blob([res.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
        const url = window.URL.createObjectURL(blob)
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', 'ventas.xlsx') // nombre del archivo
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
    fmt (v) { return Number(v || 0).toFixed(2) },
    fmtDate (iso) { return iso ? new Date(iso).toLocaleString() : '-' },

    async fetch () {
      if(!this.inicio || !this.fin || this.inicio > this.fin){ this.$q.notify({ type: 'negative', message: 'Seleccione el rango de fechas' }); return }
      this.loading = true
      try {
        const params = {inicio: this.inicio, fin: this.fin}
        if (this.filter) params.q = this.filter
        const { data } = await this.$axios.get('/ventas', { params })
        this.rows = Array.isArray(data) ? data : (data.data || [])
        console.log(data)
      } catch (e) {
        this.$q.notify({ type: 'negative', message: e.response?.data?.message || 'Error al listar ventas' })
      } finally { this.loading = false }
    },

    fetchDebounced: (() => {
      let t
      return function() {
        clearTimeout(t)
        t = setTimeout(() => this.fetch(), 350)
      }
    })(),

    async openDetalle (row) {
      try {
        const { data } = await this.$axios.get(`/ventas/${row.id}`)
        this.dlg = { open: true, row: data, detalles: data.detalles || [] }
      } catch (e) {
        this.$q.notify({ type: 'negative', message: 'No se pudo cargar el detalle' })
      }
    },

    printVenta (row) {
      // Ruta web que devuelve el PDF
      const url = `${this.$url}/ventas/${row.id}/nota`
      window.open(url, '_blank')
    }
  }
}
</script>
