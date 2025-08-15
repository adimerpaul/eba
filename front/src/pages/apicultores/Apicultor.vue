<template>
  <q-page class="q-pa-md">

    <!-- KPIs estilo dashboard (adaptados a nuevos campos) -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-md-3">
        <q-card flat bordered class="kpi-card bg-green-10 text-white">
          <q-card-section>
            <div class="text-subtitle2">Apicultores</div>
            <div class="text-h4 text-bold">{{ kpi.total }}</div>
            <div class="text-caption">registrados</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-3">
        <q-card flat bordered class="kpi-card bg-green text-white">
          <q-card-section>
            <div class="text-subtitle2">Colmenas (producción)</div>
            <div class="text-h4 text-bold">{{ kpi.colmenasProd }}</div>
            <div class="text-caption">suma n_colmenas_produccion</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-3">
        <q-card flat bordered class="kpi-card bg-amber text-dark">
          <q-card-section>
            <div class="text-subtitle2">Proyección total (kg)</div>
            <div class="text-h5 text-bold">{{ kpi.proyKg }}</div>
            <div class="text-caption">suma proyeccion_produccion_total</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-3">
        <q-card flat bordered class="kpi-card bg-red-5 text-white">
          <q-card-section>
            <div class="text-subtitle2">Beneficiarios</div>
            <div class="text-h4 text-bold">{{ kpi.beneficiarios }}</div>
            <div class="text-caption">total_beneficiarios</div>
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
          placeholder="Buscar por RUNSA, subcódigo, nombre, CI, asociación, lugar..."
          @keyup.enter="fetchRows"
          style="min-width: 320px"
        >
          <template #append><q-icon name="search" /></template>
        </q-input>
        <q-btn color="secondary" icon="map" label="Mapa" no-caps @click="openMap" :loading="loading" />
        <q-btn color="primary" icon="refresh" label="Actualizar" no-caps :loading="loading" @click="fetchRows" />
        <q-btn color="positive" icon="add_circle" label="Nuevo" no-caps @click="openNew" :loading="loading" />
      </q-card-section>

      <q-separator />

      <q-markup-table dense wrap-cells>
        <thead>
        <tr class="bg-primary text-white">
          <th>Opciones</th>
          <th>RUNSA</th>
          <th>Nombre</th>
          <th>CI / Exp.</th>
          <th>Celular</th>
          <th>Lugar Apiario</th>
          <th>Asociación</th>
          <th class="text-right">Colmenas (RUNSA)</th>
          <th class="text-right">Colmenas (Prod)</th>
          <th class="text-right">Prod. Prom (kg)</th>
          <th class="text-right">Proy. Total (kg)</th>
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
          <td>
            <div class="text-weight-medium">{{ row.codigo_runsa || '-' }}</div>
            <div class="text-caption">Subcódigo: {{ row.subcodigo || '-' }}</div>
          </td>
          <td>{{ row.nombre_apellido }}</td>
          <td>{{ row.ci || '-' }} <span v-if="row.expedido">/ {{ row.expedido }}</span></td>
          <td>{{ row.celular || '-' }}</td>
          <td>{{ row.lugar_apiario || '-' }}</td>
          <td>{{ row.asociacion || '-' }}</td>
          <td class="text-right">{{ row.n_colmenas_runsa ?? 0 }}</td>
          <td class="text-right">{{ row.n_colmenas_produccion ?? 0 }}</td>
          <td class="text-right">{{ fmt(row.produccion_promedio) }}</td>
          <td class="text-right">{{ fmt(row.proyeccion_produccion_total) }}</td>
        </tr>
        </tbody>
      </q-markup-table>
    </q-card>

    <!-- Diálogo crear/editar -->
    <q-dialog v-model="dialog" persistent>
      <q-card style="min-width: 320px; max-width: 90vw">
        <q-card-section class="row items-center q-gutter-sm">
          <div class="text-h6">{{ form.id ? 'Editar Apicultor' : 'Nuevo Apicultor' }}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-separator />
        <q-card-section>
          <q-form @submit="submit">
            <div class="row q-col-gutter-sm">

              <div class="col-12 col-sm-4">
                <q-input v-model="form.codigo_runsa" label="Código RUNSA" dense outlined />
              </div>
              <div class="col-12 col-sm-4">
                <q-input v-model="form.subcodigo" label="Subcódigo" dense outlined />
              </div>
              <div class="col-12 col-sm-4">
                <q-input v-model="form.runsa" label="RUNSA" dense outlined />
              </div>

              <div class="col-12 col-sm-8">
                <q-input v-model="form.nombre_apellido" label="Nombre y Apellido" dense outlined :rules="[v => !!v || 'Requerido']" />
              </div>
              <div class="col-6 col-sm-2">
                <q-input v-model="form.ci" label="CI" dense outlined />
              </div>
              <div class="col-6 col-sm-2">
                <q-input v-model="form.expedido" label="Expedido" dense outlined hint="LP, CB, SC, OR..." />
              </div>

              <div class="col-12 col-sm-4">
                <q-input v-model="form.celular" label="Celular" dense outlined />
              </div>
              <div class="col-12 col-sm-8">
                <q-input v-model="form.lugar_apiario" label="Lugar del Apiario" dense outlined />
              </div>

              <div class="col-12 col-sm-4">
                <q-input v-model.number="form.n_colmenas_runsa" type="number" label="N° Colmenas (RUNSA)" dense outlined />
              </div>
              <div class="col-12 col-sm-4">
                <q-input v-model.number="form.n_colmenas_produccion" type="number" label="N° Colmenas (Producción)" dense outlined />
              </div>
              <div class="col-12 col-sm-4">
                <q-input v-model.number="form.produccion_promedio" type="number" step="0.01" label="Producción Prom. (kg)" dense outlined />
              </div>

              <div class="col-12 col-sm-6">
                <q-input v-model.number="form.proyeccion_produccion_total" type="number" step="0.01" label="Proyección Total (kg)" dense outlined />
              </div>
              <div class="col-12 col-sm-6">
                <q-input v-model.number="form.proyeccion_produccion_toneladas" type="number" step="0.001" label="Proyección (ton)" dense outlined />
              </div>

              <div class="col-12 col-sm-4">
                <q-input v-model="form.asociacion" label="Asociación" dense outlined />
              </div>
              <div class="col-12 col-sm-4">
                <q-input v-model="form.fomento" label="Fomento" dense outlined />
              </div>
              <div class="col-12 col-sm-4">
                <q-input v-model="form.fortalecimiento" label="Fortalecimiento" dense outlined />
              </div>

              <div class="col-12 col-sm-3">
                <q-input v-model.number="form.total_beneficiarios" type="number" label="Total Beneficiarios" dense outlined />
              </div>
              <div class="col-12 col-sm-3">
                <q-input v-model.number="form.nativas" type="number" label="Nativas" dense outlined />
              </div>
              <div class="col-12 col-sm-3">
                <q-input v-model.number="form.fom" type="number" label="FOM" dense outlined />
              </div>
              <div class="col-12 col-sm-3">
                <q-input v-model.number="form.fort" type="number" label="FORT" dense outlined />
              </div>

              <div class="col-12 col-sm-6">
                <q-input v-model.number="form.suma_nuevos" type="number" label="Suma Nuevos" dense outlined />
              </div>
              <div class="col-12 col-sm-3">
                <q-input v-model="form.n_acta" label="N° Acta" dense outlined />
              </div>
              <div class="col-12 col-sm-3">
                <q-input v-model="form.lote" label="Lote" dense outlined />
              </div>
<!--              estado-->
              <div class="col-12 col-sm-6">
                <q-select
                  v-model="form.estado"
                  :options="['Activo', 'Inactivo', 'Mantenimiento']"
                  label="Estado"
                  dense outlined
                  emit-value map-options
                />
              </div>


              <div class="col-12">
                <MapPicker v-model="formLocation" :center="[-16.5,-68.15]" :zoom-init="13" />
              </div>
            </div>

            <div class="text-right q-mt-md">
              <q-btn flat label="Cancelar" color="negative" v-close-popup :loading="saving" />
              <q-btn label="Guardar" color="primary" type="submit" :loading="saving" class="q-ml-sm" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- Diálogo Mapa General -->
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
      filters: { search: '' },
      kpi: { total: 0, colmenasProd: 0, proyKg: 0, beneficiarios: 0 }
    }
  },
  computed: {
    // Mapea al nuevo nombre de campos de geolocalización
    formLocation: {
      get () { return { lat: this.form.latitud ?? null, lng: this.form.longitud ?? null } },
      set (v) { this.form.latitud = v?.lat ?? null; this.form.longitud = v?.lng ?? null }
    }
  },
  mounted () { this.fetchRows() },
  methods: {
    fmt (v) {
      const n = Number(v || 0)
      return Number.isFinite(n) ? n.toLocaleString() : '0'
    },
    async fetchRows () {
      this.loading = true
      try {
        const res = await this.$axios.get('apicultores', {
          params: { search: this.filters.search || undefined }
        })
        this.rows = res.data
        this.computeKpis()
      } catch (e) {
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo cargar')
      } finally {
        this.loading = false
      }
    },
    computeKpis () {
      const total = this.rows.length
      const colmenasProd = this.rows.reduce((a, r) => a + (Number(r.n_colmenas_produccion || 0)), 0)
      const proyKg = this.rows.reduce((a, r) => a + (Number(r.proyeccion_produccion_total || 0)), 0)
      const beneficiarios = this.rows.reduce((a, r) => a + (Number(r.total_beneficiarios || 0)), 0)
      this.kpi = { total, colmenasProd, proyKg, beneficiarios }
    },
    openMap () {
      this.dialogMap = true
      this.$nextTick(() => { this.refitMap = Date.now() })
    },
    openNew () {
      this.form = {
        nombre_apellido: '',
        n_colmenas_runsa: 0,
        n_colmenas_produccion: 0,
        produccion_promedio: 0,
        proyeccion_produccion_total: 0,
        proyeccion_produccion_toneladas: 0,
        total_beneficiarios: 0,
        nativas: 0, fom: 0, fort: 0, suma_nuevos: 0,
        latitud: null, longitud: null
      }
      this.dialog = true
    },
    openEdit (row) { this.form = { ...row }; this.dialog = true },
    async submit () {
      this.saving = true
      try {
        if (this.form.id) {
          await this.$axios.put(`apicultores/${this.form.id}`, this.form)
          this.$alert?.success?.('Actualizado')
        } else {
          const { data } = await this.$axios.post('apicultores', this.form)
          this.$alert?.success?.('Creado'); this.form = { ...data }
        }
        this.dialog = false
        this.fetchRows()
      } catch (e) {
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo guardar')
      } finally {
        this.saving = false
      }
    },
    remove (id) {
      this.$alert?.dialog?.('¿Eliminar apicultor?')?.onOk(async () => {
        this.loading = true
        try {
          await this.$axios.delete(`apicultores/${id}`)
          this.$alert?.success?.('Eliminado')
          this.fetchRows()
        } catch (e) {
          this.$alert?.error?.(e.response?.data?.message || 'No se pudo eliminar')
        } finally {
          this.loading = false
        }
      })
    }
  }
}
</script>

<style scoped>
.kpi-card { border-radius: 14px; }
</style>
