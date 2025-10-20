<template>
  <q-card flat bordered>
    <q-card-section class="row items-center justify-between">
      <div class="text-subtitle1">Análisis de calidad</div>
      <q-btn color="primary" icon="add" label="Nuevo" no-caps @click="onNuevo" />
    </q-card-section>

    <q-separator />

    <q-card-section>
<!--      <q-table-->
<!--        flat bordered-->
<!--        :rows="rows"-->
<!--        :columns="columns"-->
<!--        row-key="id"-->
<!--        :loading="loading"-->
<!--        no-data-label="Sin registros"-->
<!--        hide-pagination-->
<!--        :rows-per-page-options="[0]"-->
<!--      >-->
<!--        <template v-slot:body-cell-acciones="props">-->
<!--          <q-td :props="props">-->
<!--            <q-btn dense flat icon="edit" @click="onEditar(props.row)" />-->
<!--            <q-btn dense flat color="negative" icon="delete" @click="onEliminar(props.row)" />-->
<!--          </q-td>-->
<!--        </template>-->
<!--      </q-table>-->
      <q-markup-table dense wrap-cells bordered flat>
        <thead>
          <tr class="bg-primary text-white">
            <th>Acciones</th>
            <th>Fecha</th>
            <th>Humedad</th>
            <th>pH</th>
            <th>Brix</th>
            <th>Color</th>
            <th>Lab.</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in rows" :key="row.id">
            <td class="text-right">
              <q-btn-dropdown dense  no-caps label="Acciones" color="primary" size="10px">
                <q-list>
                  <q-item clickable v-ripple @click="onEditar(row)" v-close-popup>
                    <q-item-section avatar><q-icon name="edit" /></q-item-section>
                    <q-item-section>Editar</q-item-section>
                  </q-item>
                  <q-item clickable v-ripple @click="onEliminar(row)" v-close-popup>
                    <q-item-section avatar><q-icon name="delete" color="negative" /></q-item-section>
                    <q-item-section>Eliminar</q-item-section>
                  </q-item>
                </q-list>
              </q-btn-dropdown>
            </td>
            <td>{{ row.fecha_analisis }}</td>
            <td class="text-right">{{ row.humedad }}</td>
            <td class="text-right">{{ row.ph }}</td>
            <td class="text-right">{{ row.brix }}</td>
            <td>{{ row.color }}</td>
            <td>{{ row.laboratorio }}</td>
          </tr>
        </tbody>
      </q-markup-table>
    </q-card-section>

    <!-- Diálogo Crear/Editar -->
    <q-dialog v-model="dlg.open" persistent>
      <q-card style="min-width: 900px; max-width: 95vw;">
        <q-card-section class="row items-center justify-between">
          <div class="text-subtitle1">{{ dlg.modo === 'crear' ? 'Nuevo análisis' : 'Editar análisis' }}</div>
          <q-btn flat round dense icon="close" v-close-popup />
        </q-card-section>
        <q-separator />
        <q-card-section class="">
          <div class="row q-col-gutter-md">
            <div class="col-12 col-md-3"><q-input v-model="form.fecha_analisis" type="date" label="Fecha análisis" dense outlined /></div>
            <div class="col-6 col-md-2"><q-input v-model.number="form.humedad" type="number" step="0.01" label="Humedad" dense outlined /></div>
            <div class="col-6 col-md-2"><q-input v-model.number="form.ph" type="number" step="0.01" label="pH" dense outlined /></div>
            <div class="col-6 col-md-2"><q-input v-model.number="form.brix" type="number" step="0.01" label="Brix" dense outlined /></div>

            <div class="col-6 col-md-3"><q-input v-model="form.color" label="Color" dense outlined /></div>
            <div class="col-6 col-md-3"><q-input v-model="form.sabor" label="Sabor" dense outlined /></div>
            <div class="col-6 col-md-3"><q-input v-model="form.olor" label="Olor" dense outlined /></div>
            <div class="col-6 col-md-3"><q-input v-model="form.consistencia" label="Consistencia" dense outlined /></div>

            <div class="col-6 col-md-3"><q-input v-model.number="form.porcentaje_polen" type="number" step="0.01" label="% Polen" dense outlined /></div>
            <div class="col-6 col-md-3"><q-input v-model.number="form.conductividad_electrica" type="number" step="0.01" label="Conductividad" dense outlined /></div>

            <div class="col-6 col-md-2"><q-input v-model.number="form.glucosa" type="number" step="0.01" label="Glucosa" dense outlined /></div>
            <div class="col-6 col-md-2"><q-input v-model.number="form.fructosa" type="number" step="0.01" label="Fructosa" dense outlined /></div>
            <div class="col-6 col-md-2"><q-input v-model.number="form.sacarosa" type="number" step="0.01" label="Sacarosa" dense outlined /></div>

            <div class="col-6 col-md-3"><q-input v-model.number="form.diastasa_enzimatica" type="number" step="0.01" label="Diastasa" dense outlined /></div>
            <div class="col-6 col-md-3"><q-input v-model.number="form.prolina_mgkg" type="number" step="0.01" label="Prolina (mg/kg)" dense outlined /></div>
            <div class="col-6 col-md-3"><q-input v-model.number="form.rotacion_especifica" type="number" step="0.01" label="Rotación esp." dense outlined /></div>
            <div class="col-6 col-md-3"><q-input v-model.number="form.hidroximetilfurfural" type="number" step="0.01" label="HMF" dense outlined /></div>

            <div class="col-12 col-md-4"><q-input v-model="form.metodo_analisis" label="Método análisis" dense outlined /></div>
            <div class="col-12 col-md-4"><q-input v-model="form.laboratorio" label="Laboratorio" dense outlined /></div>
            <div class="col-12 col-md-4"><q-input v-model="form.certificado_url" label="URL certificado" dense outlined /></div>

            <div class="col-12"><q-input v-model="form.observaciones" type="textarea" autogrow label="Observaciones" dense outlined /></div>

            <div class="col-12">
              <q-toggle v-model="form.cumplimiento_normativa" label="Cumple normativa" />
            </div>
          </div>
        </q-card-section>
        <q-separator />
        <q-card-actions align="right">
          <q-btn flat label="Cancelar" v-close-popup />
          <q-btn color="primary" :label="dlg.modo === 'crear' ? 'Guardar' : 'Actualizar'" @click="onGuardar" :loading="saving" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-card>
</template>

<script>
export default {
  name: 'AnalisisCalidad',
  props: {
    cosecha: { type: Object, required: true }
  },
  data () {
    return {
      loading: false,
      saving: false,
      rows: [],
      // columns: [
      //   { name: 'fecha_analisis', label: 'Fecha', field: 'fecha_analisis', align: 'left' },
      //   { name: 'humedad', label: 'Humedad', field: 'humedad', align: 'right' },
      //   { name: 'ph', label: 'pH', field: 'ph', align: 'right' },
      //   { name: 'brix', label: 'Brix', field: 'brix', align: 'right' },
      //   { name: 'color', label: 'Color', field: 'color', align: 'left' },
      //   { name: 'laboratorio', label: 'Lab.', field: 'laboratorio', align: 'left' },
      //   { name: 'acciones', label: 'Acciones', field: 'acciones', align: 'right' }
      // ],
      dlg: { open: false, modo: 'crear', id: null },
      form: {
        cosecha_id: null,
        fecha_analisis: null,
        humedad: null,
        ph: null,
        brix: null,
        color: '',
        sabor: '',
        olor: '',
        consistencia: '',
        porcentaje_polen: null,
        conductividad_electrica: null,
        glucosa: null,
        fructosa: null,
        sacarosa: null,
        diastasa_enzimatica: null,
        prolina_mgkg: null,
        rotacion_especifica: null,
        hidroximetilfurfural: null,
        metodo_analisis: '',
        laboratorio: '',
        certificado_url: '',
        observaciones: '',
        cumplimiento_normativa: true
      }
    }
  },
  mounted () {
    this.fetchRows()
  },
  methods: {
    resetForm () {
      this.form = {
        cosecha_id: this.cosecha?.id || null,
        fecha_analisis: null,
        humedad: null, ph: null, brix: null,
        color: '', sabor: '', olor: '', consistencia: '',
        porcentaje_polen: null, conductividad_electrica: null,
        glucosa: null, fructosa: null, sacarosa: null,
        diastasa_enzimatica: null, prolina_mgkg: null,
        rotacion_especifica: null, hidroximetilfurfural: null,
        metodo_analisis: '', laboratorio: '', certificado_url: '',
        observaciones: '', cumplimiento_normativa: true
      }
    },
    async fetchRows () {
      try {
        this.loading = true
        const { data } = await this.$axios.get('/analisis-calidad', {
          params: { cosecha_id: this.cosecha?.id }
        })
        this.rows = Array.isArray(data) ? data : (data?.data || [])
      } catch (e) {
        this.$q.notify({ type: 'negative', message: 'No se pudieron cargar los análisis' })
      } finally {
        this.loading = false
      }
    },
    onNuevo () {
      this.dlg.modo = 'crear'
      this.dlg.id = null
      this.resetForm()
      this.form.cosecha_id = this.cosecha?.id || null
      this.dlg.open = true
    },
    onEditar (row) {
      this.dlg.modo = 'editar'
      this.dlg.id = row.id
      this.form = { ...row, cosecha_id: this.cosecha?.id || row.cosecha_id }
      this.dlg.open = true
    },
    async onGuardar () {
      try {
        this.saving = true
        if (this.dlg.modo === 'crear') {
          await this.$axios.post('/analisis-calidad', this.form)
          this.$q.notify({ type: 'positive', message: 'Registro creado' })
        } else {
          await this.$axios.put(`/analisis-calidad/${this.dlg.id}`, this.form)
          this.$q.notify({ type: 'positive', message: 'Registro actualizado' })
        }
        this.dlg.open = false
        this.fetchRows()
      } catch (e) {
        this.$q.notify({ type: 'negative', message: 'Error al guardar' })
      } finally {
        this.saving = false
      }
    },
    async onEliminar (row) {
      this.$q.dialog({
        title: 'Eliminar',
        message: '¿Desea eliminar este análisis?',
        cancel: true,
        persistent: true
      }).onOk(async () => {
        try {
          await this.$axios.delete(`/analisis-calidad/${row.id}`)
          this.$q.notify({ type: 'positive', message: 'Eliminado' })
          this.fetchRows()
        } catch (e) {
          this.$q.notify({ type: 'negative', message: 'No se pudo eliminar' })
        }
      })
    }
  }
}
</script>
