<!-- src/pages/ventas/VentaCrear.vue -->
<template>
  <q-page class="q-pa-md">
    <q-form @submit.prevent="onSubmit" ref="formRef">
      <q-card flat bordered>
        <!-- CABECERA -->
        <q-card-section class="row q-col-gutter-md">
          <div class="col-12">
            <div class="text-h6">Nueva Venta</div>
          </div>

          <div class="col-12 col-md-6">
            <q-select
              v-model="venta.cliente_id"
              :options="clienteOptions"
              option-value="id"
              option-label="nombre"
              emit-value map-options
              input-debounce="300"
              @filter="filterClientes"
              :loading="loadingClientes"
              dense filled
              label="Cliente"
              :rules="[v => !!v || 'Selecciona un cliente']"
            >
              <template #no-option>
                <q-item><q-item-section class="text-grey">Sin resultados</q-item-section></q-item>
              </template>
            </q-select>
          </div>

          <div class="col-12 col-md-6">
            <q-select
              v-model="venta.transporte_id"
              :options="transporteOptions"
              option-value="id"
              option-label="nombre"
              emit-value map-options
              input-debounce="300"
              @filter="filterTransportes"
              :loading="loadingTransportes"
              dense filled
              label="Transporte"
              :rules="[v => !!v || 'Selecciona un transporte']"
            >
              <template #no-option>
                <q-item><q-item-section class="text-grey">Sin resultados</q-item-section></q-item>
              </template>
            </q-select>
          </div>

          <div class="col-12 col-md-4">
            <q-input v-model="venta.fecha_venta" type="datetime-local" dense filled label="Fecha de venta"
                     :rules="[v => !!v || 'Requerido']"/>
          </div>

          <div class="col-12 col-md-4">
            <q-input v-model="venta.destino_final" dense filled label="Destino final"/>
          </div>

          <div class="col-12 col-md-2">
            <q-input v-model="venta.guia_remision" dense filled label="Guía de remisión"/>
          </div>

          <div class="col-12 col-md-2">
            <q-input v-model="venta.num_factura" dense filled label="N° factura"/>
          </div>
        </q-card-section>

        <q-separator/>

        <!-- DETALLES / LOTES -->
        <q-card-section>
          <div class="row items-center q-gutter-sm q-mb-sm">
            <div class="text-subtitle2">Detalle de lotes</div>
            <q-space />
            <q-btn color="primary" icon="add" label="Agregar lote" no-caps @click="addItem"/>
          </div>

          <q-markup-table dense flat bordered class="full-width">
            <thead>
            <tr>
              <th style="width: 28%">Lote</th>
              <th style="width: 18%">Producto</th>
              <th style="width: 14%" class="text-right">Disp. (kg)</th>
              <th style="width: 14%">Cantidad (kg)</th>
              <th style="width: 12%">Precio</th>
              <th style="width: 10%" class="text-right">Subtotal</th>
              <th style="width: 4%"></th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(it, idx) in items" :key="it.uid">
              <td>
                <q-select
                  v-model="it.lote_id"
                  :options="loteOptions"
                  option-value="id"
                  option-label="label"
                  emit-value map-options
                  dense filled
                  input-debounce="300"
                  @filter="filterLotes"
                  :loading="loadingLotes"
                  @update:model-value="onPickLote(idx)"
                >
                  <template #option="scope">
                    <q-item v-bind="scope.itemProps">
                      <q-item-section>
                        <q-item-label>{{ scope.opt.codigo_lote }}</q-item-label>
                        <q-item-label caption>
                          {{ scope.opt.producto_nombre }} — Disp: {{ fmt(scope.opt.disponible_kg) }} kg
                        </q-item-label>
                      </q-item-section>
                    </q-item>
                  </template>
                  <template #selected-item="scope">
                    <q-chip square dense>{{ findLoteLabel(scope.opt) }}</q-chip>
                  </template>
                  <template #no-option>
                    <q-item><q-item-section class="text-grey">Sin resultados</q-item-section></q-item>
                  </template>
                </q-select>
              </td>
              <td>
                <q-input v-model="it.producto_nombre" dense filled readonly/>
              </td>
              <td class="text-right">
                <div class="q-pa-xs">{{ fmt(it.disponible_kg) }}</div>
              </td>
              <td>
                <q-input v-model.number="it.cantidad_salida" type="number" min="0" step="0.01" dense filled
                         @update:model-value="recalcSubtotal(idx)"
                         :error="!!it.err"
                         :error-message="it.err || ''"/>
              </td>
              <td>
                <q-input v-model.number="it.precio_venta" type="number" min="0" step="0.01" dense filled
                         @update:model-value="recalcSubtotal(idx)"/>
              </td>
              <td class="text-right">
                <div class="q-pa-xs">{{ fmt(it.subtotal) }}</div>
              </td>
              <td class="text-right">
                <q-btn dense flat icon="delete" color="negative" @click="removeItem(idx)"/>
              </td>
            </tr>

            <tr v-if="!items.length">
              <td colspan="7" class="text-center text-grey">Sin ítems. Agrega un lote.</td>
            </tr>
            </tbody>
          </q-markup-table>

          <div class="row justify-end q-mt-md">
            <div class="col-auto">
              <div class="text-subtitle1">
                Total: <b>{{ fmt(total) }}</b> Bs.
              </div>
            </div>
          </div>
        </q-card-section>

        <q-separator/>

        <!-- ACCIONES -->
        <q-card-actions align="right">
          <q-btn flat label="Cancelar" @click="$router.back()" />
          <q-btn color="primary" :loading="saving" :disable="!canSubmit" label="Guardar venta" type="submit" />
        </q-card-actions>
      </q-card>
    </q-form>
  </q-page>
</template>

<script>
export default {
  name: 'VentaCrear',
  data () {
    return {
      // cabecera
      venta: {
        cliente_id: null,
        transporte_id: null,
        fecha_venta: new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000).toISOString().slice(0,16),
        destino_final: '',
        guia_remision: '',
        num_factura: ''
      },

      // selects
      clienteOptions: [],
      transporteOptions: [],
      loteOptions: [],

      loadingClientes: false,
      loadingTransportes: false,
      loadingLotes: false,

      // detalle
      items: [],

      // estado
      saving: false
    }
  },
  computed: {
    total () {
      return this.items.reduce((acc, it) => acc + (Number(it.subtotal) || 0), 0)
    },
    canSubmit () {
      const headOk = !!(this.venta.cliente_id && this.venta.transporte_id && this.venta.fecha_venta)
      const detOk = this.items.length > 0 && this.items.every(it =>
        it.lote_id && it.cantidad_salida > 0 && it.cantidad_salida <= it.disponible_kg && it.precio_venta >= 0
      )
      return headOk && detOk
    }
  },
  mounted () {
    this.seedCombos()
  },
  methods: {
    fmt (v) { return Number(v || 0).toFixed(2) },

    async seedCombos () {
      await Promise.all([this.filterClientes(''), this.filterTransportes(''), this.filterLotes('')])
    },

    // --- Filtros remotos (typeahead) ---
    async filterClientes (val, update) {
      if (update) update()
      this.loadingClientes = true
      try {
        const { data } = await this.$axios.get('/clientes', { params: { q: val || '' } })
        this.clienteOptions = (Array.isArray(data) ? data : (data.data || [])).map(c => ({
          id: c.id, nombre: c.nombre_cliente
        }))
      } finally { this.loadingClientes = false }
    },

    async filterTransportes (val, update) {
      if (update) update()
      this.loadingTransportes = true
      try {
        const { data } = await this.$axios.get('/transportes', { params: { q: val || '' } })
        this.transporteOptions = (Array.isArray(data) ? data : (data.data || [])).map(t => ({
          id: t.id, nombre: t.empresa || t.responsable || t.placa
        }))
      } finally { this.loadingTransportes = false }
    },

    async filterLotes (val, update) {
      if (update) update()
      this.loadingLotes = true
      try {
        const { data } = await this.$axios.get('/lotes/disponibles', { params: { q: val || '' } })
        // Se espera que el backend devuelva: id, codigo_lote, producto_id, producto_nombre, disponible_kg
        this.loteOptions = (Array.isArray(data) ? data : (data.data || [])).map(l => ({
          id: l.id,
          codigo_lote: l.codigo_lote,
          producto_id: l.producto_id,
          producto_nombre: l.producto_nombre || l.producto?.nombre_producto,
          disponible_kg: Number(l.disponible_kg ?? l.cantidad_kg ?? 0),
          label: `${l.codigo_lote} — ${l.producto_nombre || l.producto?.nombre_producto} (Disp: ${Number(l.disponible_kg ?? l.cantidad_kg ?? 0).toFixed(2)} kg)`
        }))
      } finally { this.loadingLotes = false }
    },

    // --- Detalle ---
    addItem () {
      this.items.push({
        uid: Math.random().toString(36).slice(2),
        lote_id: null,
        producto_id: null,
        producto_nombre: '',
        disponible_kg: 0,
        cantidad_salida: null,
        precio_venta: 0,
        subtotal: 0,
        err: ''
      })
    },
    removeItem (idx) {
      this.items.splice(idx, 1)
    },
    findLoteLabel (idOrObj) {
      const id = typeof idOrObj === 'object' ? idOrObj.id : idOrObj
      const o = this.loteOptions.find(x => x.id === id)
      return o ? o.codigo_lote : id
    },
    onPickLote (idx) {
      const it = this.items[idx]
      const lot = this.loteOptions.find(x => x.id === it.lote_id)
      if (!lot) return
      it.producto_id = lot.producto_id
      it.producto_nombre = lot.producto_nombre
      it.disponible_kg = lot.disponible_kg
      // reset valores dependientes
      if (it.cantidad_salida > lot.disponible_kg) it.cantidad_salida = lot.disponible_kg
      this.recalcSubtotal(idx)
    },
    recalcSubtotal (idx) {
      const it = this.items[idx]
      it.err = ''
      const cant = Number(it.cantidad_salida || 0)
      const disp = Number(it.disponible_kg || 0)
      if (cant > disp) {
        it.err = `No puede exceder ${this.fmt(disp)} kg`
      } else if (cant <= 0) {
        it.err = 'Ingresa una cantidad válida'
      }
      const precio = Number(it.precio_venta || 0)
      it.subtotal = (cant > 0 && precio >= 0 && cant <= disp) ? (cant * precio) : 0
    },

    // --- Submit ---
    async onSubmit () {
      if (!this.canSubmit) return
      this.saving = true
      try {
        const payload = {
          ...this.venta,
          precio_total: this.total,
          detalles: this.items.map(it => ({
            lote_id: it.lote_id,
            producto_id: it.producto_id,
            cantidad_salida: Number(it.cantidad_salida),
            precio_venta: Number(it.precio_venta)
          }))
        }
        await this.$axios.post('/ventas', payload)
        this.$q.notify({ type: 'positive', message: 'Venta registrada' })
        this.$router.push('/ventas') // ajusta la ruta según tu app
      } catch (e) {
        const msg = e.response?.data?.message || 'No se pudo registrar la venta'
        this.$q.notify({ type: 'negative', message: msg })
      } finally {
        this.saving = false
      }
    }
  }
}
</script>

<style scoped>
/* Opcional: estilos finos */
</style>
