<template>
  <q-card flat bordered>
    <q-card-section class="row items-center justify-between">
      <div class="text-h6">Registrar Acopio</div>
      <q-badge color="primary" outline v-if="estado === 'crear'">Nuevo</q-badge>
    </q-card-section>

    <q-separator />

    <q-card-section>
      <q-form ref="formRef" @submit.prevent="onSubmit">
        <div class="row q-col-gutter-md">

          <!-- Fecha de cosecha -->
          <div class="col-12 col-md-3">
            <q-input
              v-model="form.fecha_cosecha"
              type="date"
              label="Fecha de Cosecha"
              dense outlined
              :rules="[val => !!val || 'Requerido']"
            />
          </div>

          <!-- PRODUCTOR (búsqueda) -->
          <div class="col-12 col-md-6">
            <q-select
              v-model="form.productor_id"
              :options="productorOptions"
              option-value="value"
              option-label="label"
              emit-value
              map-options
              use-input
              input-debounce="400"
              @filter="filterProductores"
              label="Productor"
              dense outlined
              :loading="loadingProductores"
              clearable
              :rules="[v => !!v || 'Seleccione un productor']"
            >
              <template v-slot:no-option>
                <q-item>
                  <q-item-section class="text-grey">Escriba al menos 2 caracteres…</q-item-section>
                </q-item>
              </template>
            </q-select>
            <div class="text-caption text-grey-7 q-mt-xs" v-if="productorDetalle">
              <q-icon name="badge" size="16px" class="q-mr-xs" />
              {{ productorDetalle?.label }}
            </div>
          </div>

          <!-- APIARIO (dependiente del productor) -->
          <div class="col-12 col-md-6">
            <q-select
              v-model="form.apiario_id"
              :options="apiarioOptions"
              option-value="value"
              option-label="label"
              emit-value
              map-options
              label="Apiario"
              dense outlined
              :loading="loadingApiarios"
              :disable="!form.productor_id"
              clearable
              :rules="[v => !!v || 'Seleccione un apiario']"
            />
            <div class="text-caption text-grey-7 q-mt-xs" v-if="apiarioDetalle">
              <q-icon name="place" size="16px" class="q-mr-xs" />
              {{ apiarioDetalle?.municipio || '—' }}
            </div>
          </div>

          <!-- Cantidad KG -->
          <div class="col-6 col-md-3">
            <q-input
              v-model.number="form.cantidad_kg"
              type="number"
              min="0"
              step="0.01"
              label="Cantidad (kg)"
              dense outlined
              suffix="kg"
              :rules="[v => v > 0 || 'Mayor a 0']"
            />
          </div>

<!--          Producto-->
          <div class="col-6 col-md-3">
            <q-select
              v-model="form.producto_id"
              :options="productos.map(p => ({ label: p.nombre_producto, value: p.id }))"
              label="Producto"
              dense outlined
              emit-value
              map-options
              :rules="[v => !!v || 'Requerido']"
            />
            <pre>{{form.producto_id}}</pre>
          </div>

          <!-- Precio compra -->
          <div class="col-6 col-md-3">
            <q-input
              v-model.number="form.precio_compra"
              type="number"
              min="0"
              step="0.01"
              label="Precio de compra"
              dense outlined
              prefix="Bs "
              :rules="[v => v >= 0 || 'No negativo']"
            />
          </div>

          <!-- Humedad -->
          <div class="col-6 col-md-3">
            <q-input
              v-model.number="form.humedad"
              type="number"
              min="0"
              step="0.01"
              label="Humedad (%)"
              dense outlined
              suffix="%"
            />
          </div>

          <!-- Temperatura -->
          <div class="col-6 col-md-3">
            <q-input
              v-model.number="form.temperatura_almacenaje"
              type="number"
              min="-50"
              step="0.01"
              label="Temperatura Almacenaje (°C)"
              dense outlined
              suffix="°C"
            />
          </div>

          <!-- Procedencia -->
          <div class="col-6 col-md-3">
            <q-input
              v-model="form.procedencia"
              label="Procedencia"
              dense outlined
              maxlength="50"
            />
          </div>

          <!-- Tipo de envase -->
          <div class="col-6 col-md-3">
            <q-input
              v-model="form.tipo_envase"
              label="Tipo de envase"
              dense outlined
              maxlength="100"
            />
          </div>

          <!-- Observaciones -->
          <div class="col-12 col-md-6">
            <q-input
              v-model="form.observaciones"
              label="Observaciones"
              dense outlined
              autogrow
              maxlength="255"
            />
          </div>

          <!-- Estado -->
          <div class="col-12 col-md-3">
            <q-select
              v-model="form.estado"
              :options="estados"
              emit-value
              map-options
              label="Estado"
              dense outlined
              :rules="[v => !!v || 'Requerido']"
            />
          </div>
        </div>

        <div class="row q-gutter-sm q-mt-md">
          <q-btn type="submit" color="primary" icon="save" :loading="loading" no-caps label="Guardar" />
          <q-btn type="reset" color="grey-7" flat icon="history" no-caps label="Limpiar" @click="onReset" />
        </div>
      </q-form>
    </q-card-section>
  </q-card>
</template>

<script>
import moment from 'moment'

export default {
  name: 'AcopioFormulario',
  props: {
    estado: {
      type: String,
      required: true,
      validator: v => ['crear', 'editar'].indexOf(v) !== -1
    }
  },
  data () {
    return {
      loading: false,
      productos: [],
      // selects
      productorOptions: [],
      apiarioOptions: [],
      loadingProductores: false,
      loadingApiarios: false,

      // form
      form: {
        fecha_cosecha: moment().format('YYYY-MM-DD'),
        productor_id: null,
        apiario_id: null,
        cantidad_kg: null,
        precio_compra: 32,
        humedad: null,
        temperatura_almacenaje: null,
        procedencia: '',
        tipo_envase: '',
        observaciones: '',
        estado: 'BUENO'
      },

      estados: [
        { label: 'BUENO', value: 'BUENO' },
        { label: 'EN PROCESO', value: 'EN PROCESO' },
        { label: 'CANCELADO', value: 'CANCELADO' }
      ]
    }
  },
  mounted() {
    this.productoGet();
  },
  computed: {
    productorDetalle () {
      if (!this.form.productor_id) return null
      return this.productorOptions.find(o => o.value === this.form.productor_id) || null
    },
    apiarioDetalle () {
      if (!this.form.apiario_id) return null
      return this.apiarioOptions.find(o => o.value === this.form.apiario_id)?.raw || null
    }
  },

  watch: {
    // Al elegir productor, cargar sus apiarios
    'form.productor_id' (val) {
      this.form.apiario_id = null
      this.apiarioOptions = []
      if (val) this.loadApiariosByProductor(val)
    }
  },

  methods: {
    async productoGet() {
      this.$axios.get('/productos').then(({ data }) => {
        this.productos = data?.data || data || []
      // //   [
      //   {
      //     "id": 1,
      //     "tipo_id": 1,
      //     "codigo_producto": "0001",
      //     "nombre_producto": "Miel de Abeja",
      //     "presentacion": "Kilos",
      //     "cantidad_kg": "0.00",
      //     "costo": "0.00",
      //     "precio": "0.00",
      //     "fecha_vencimiento": null,
      //     "nro_lote": null,
      //     "codigo_barra": null,
      //     "imagen": null
      //   },
      }).catch(() => {
        this.$q.notify({ type: 'negative', message: 'No se pudieron cargar los productos' })
      })
    },
    onReset () {
      this.$refs.formRef.resetValidation()
      this.form = Object.assign({}, this.form, {
        fecha_cosecha: moment().format('YYYY-MM-DD'),
        productor_id: null,
        apiario_id: null,
        cantidad_kg: null,
        precio_compra: 32,
        humedad: null,
        temperatura_almacenaje: null,
        procedencia: '',
        tipo_envase: '',
        observaciones: '',
        estado: 'BUENO'
      })
    },

    // === PRODUCTORES: filtro remoto ===
    filterProductores (val, update, abort) {
      if (!val || val.length < 2) {
        update(() => { this.productorOptions = [] })
        return
      }
      update(async () => {
        this.loadingProductores = true
        try {
          const { data } = await this.$axios.get('/productores', {
            params: {
              search: val,
              per_page: 20
            }
          })
          const items = (data?.data || data || []).map(p => ({
            value: p.id,
            label: `${p.nombre ?? ''} ${p.apellidos ?? ''} — ${p.municipio?.nombre_municipio ?? ''}`.trim(),
            raw: p
          }))
          this.productorOptions = items
        } catch (e) {
          this.$q.notify({ type: 'negative', message: 'No se pudo cargar productores' })
        } finally {
          this.loadingProductores = false
        }
      })
    },

    // === APIARIOS por productor ===
    async loadApiariosByProductor (productorId) {
      this.loadingApiarios = true
      try {
        const { data } = await this.$axios.get('/apiarios', {
          params: { productor_id: productorId, paginate: false }
        })
        this.apiarioOptions = (data || []).map(a => ({
          value: a.id,
          label: a.nombre_cip
            ? `${a.nombre_cip} — ${a?.municipio?.nombre_municipio ?? ''}`
            : `Apiario #${a.id} — ${a?.municipio?.nombre_municipio ?? ''}`,
          raw: { municipio: a?.municipio?.nombre_municipio || '—' }
        }))
      } catch (e) {
        this.$q.notify({ type: 'negative', message: 'No se pudieron cargar apiarios' })
      } finally {
        this.loadingApiarios = false
      }
    },

    // === SUBMIT ===
    async onSubmit () {
      try {
        const ok = await this.$refs.formRef.validate()
        if (!ok) return

        this.loading = true

        // const payload = {
        //   fecha_cosecha: this.form.fecha_cosecha,
        //   apiario_id: this.form.apiario_id,
        //   // si manejas producto_id dinámico, envíalo; si no, el backend tiene default=1
        //   // producto_id: this.form.producto_id || 1,
        //   cantidad_kg: this.form.cantidad_kg,
        //   precio_compra: this.form.precio_compra,
        //   humedad: this.form.humedad,
        //   temperatura_almacenaje: this.form.temperatura_almacenaje,
        //   observaciones: this.form.observaciones,
        //   procedencia: this.form.procedencia,
        //   tipo_envase: this.form.tipo_envase,
        //   estado: this.form.estado
        // }

        const { data } = await this.$axios.post('/acopio-cosechas', this.form)
        this.$q.notify({ type: 'positive', message: 'Acopio guardado' })
        this.$emit('saved', data)
        this.onReset()
      } catch (e) {
        this.$q.notify({ type: 'negative', message: 'Error al guardar' })
      } finally {
        this.loading = false
      }
    }
  }
}
</script>
