<template>
  <q-card flat bordered>
    <q-card-section class="row items-center justify-between">
      <div class="text-subtitle1">Aplicación de Medicamentos</div>
      <!-- MODIFICACION 2025-11-17: Boton Nuevo solo visible en modo edicion -->
      <div class="q-gutter-sm">
        <q-btn v-if="!readOnly" color="primary" icon="add" label="Nuevo" no-caps @click="onNuevo" />
        <!-- MODIFICACION 2025-11-17: Boton Imprimir solo visible en modo solo lectura -->
        <q-btn v-if="readOnly" color="green" icon="print" label="Imprimir" no-caps @click="onImprimir" />
      </div>
    </q-card-section>

    <q-separator />

    <q-card-section>
      <q-markup-table dense wrap-cells bordered flat>
        <thead>
          <tr class="bg-primary text-white">
            <th>Acciones</th>
            <th>Fecha</th>
            <th>Nombre Producto</th>
            <th>Principio Activo</th>
            <th>Dosis Recomendada</th>
            <th>Dosis Aplicada</th>
            <th>Plagas Controladas</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in rows" :key="row.id">
            <td class="text-right">
              <!-- MODIFICACION 2025-11-17: Dropdown de acciones solo visible en modo edicion -->
              <q-btn-dropdown v-if="!readOnly" dense no-caps label="Acciones" color="primary" size="10px">
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
              <!-- MODIFICACION 2025-11-17: En modo solo lectura, mostrar guion -->
              <span v-else>—</span>
            </td>
            <td>{{ row.fecha }}</td>
            <td>{{ row.nombre_producto }}</td>
            <td>{{ row.principio_activo }}</td>
            <td>{{ row.dosis_recomendada }}</td>
            <td>{{ row.dosis_aplicada }}</td>
            <td>{{ row.plagas_controladas }}</td>
          </tr>
        </tbody>
      </q-markup-table>
    </q-card-section>

    <!-- Diálogo Crear/Editar -->
    <q-dialog v-model="dlg.open" persistent>
      <q-card style="max-width: 90vw;">
        <q-card-section class="row items-center justify-between">
          <div class="text-subtitle1">{{ dlg.modo === 'crear' ? 'Nuevo registro' : 'Editar registro' }}</div>
          <q-btn flat round dense icon="close" v-close-popup />
        </q-card-section>
        <q-separator />
        <q-card-section class="">
          <div class="row q-col-gutter-md">
            <div class="col-12 col-md-3">
              <q-input v-model="form.fecha" type="date" label="Fecha" dense outlined />
            </div>
            <div class="col-12 col-md-9">
              <q-input v-model="form.nombre_producto" label="Nombre del Producto" dense outlined />
            </div>
            <div class="col-12 col-md-6">
              <q-input v-model="form.principio_activo" label="Principio Activo" dense outlined />
            </div>
            <div class="col-12 col-md-3">
              <q-input v-model="form.dosis_recomendada" label="Dosis Recomendada" dense outlined />
            </div>
            <div class="col-12 col-md-3">
              <q-input v-model="form.dosis_aplicada" label="Dosis Aplicada" dense outlined />
            </div>
            <div class="col-12 col-md-6">
              <q-input v-model="form.plagas_controladas" label="Plagas Controladas" dense outlined />
            </div>
            <div class="col-12 col-md-6">
              <q-input v-model="form.periodo_espera_cosecha" label="Periodo de Espera para Cosecha" dense outlined />
            </div>
            <div class="col-12 col-md-6">
              <q-input v-model="form.nombre_encargado" label="Nombre del Encargado" dense outlined />
            </div>
            <div class="col-12 col-md-6">
              <q-input v-model="form.firma" label="Firma" dense outlined hint="Texto o ruta de imagen" />
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
  name: 'MedicamentosFormulario',
  props: {
    cosecha: { type: Object, required: true },
    // MODIFICACION 2025-11-17: Prop para activar modo solo lectura
    // Cuando readOnly=true: oculta botones Nuevo/Editar/Eliminar y muestra boton Imprimir
    // Usado en CosechaShow.vue para consulta, ProductorAcopios.vue para edicion
    readOnly: { type: Boolean, default: false }
  },
  data () {
    return {
      loading: false,
      saving: false,
      rows: [],
      dlg: { open: false, modo: 'crear', id: null },
      form: {
        acopio_cosecha_id: null,
        fecha: null,
        nombre_producto: '',
        principio_activo: '',
        dosis_recomendada: '',
        dosis_aplicada: '',
        plagas_controladas: '',
        periodo_espera_cosecha: '',
        nombre_encargado: '',
        firma: ''
      }
    }
  },
  mounted () {
    this.fetchRows()
  },
  methods: {
    resetForm () {
      this.form = {
        acopio_cosecha_id: this.cosecha?.id || null,
        fecha: null,
        nombre_producto: '',
        principio_activo: '',
        dosis_recomendada: '',
        dosis_aplicada: '',
        plagas_controladas: '',
        periodo_espera_cosecha: '',
        nombre_encargado: '',
        firma: ''
      }
    },
    async fetchRows () {
      try {
        this.loading = true
        const { data } = await this.$axios.get('/medicamentos', {
          params: { cosecha_id: this.cosecha?.id }
        })
        this.rows = Array.isArray(data) ? data : (data?.data || [])
      } catch (e) {
        this.$q.notify({ type: 'negative', message: 'No se pudieron cargar los registros' })
      } finally {
        this.loading = false
      }
    },
    onNuevo () {
      this.dlg.modo = 'crear'
      this.dlg.id = null
      this.resetForm()
      this.form.acopio_cosecha_id = this.cosecha?.id || null
      this.dlg.open = true
    },
    onEditar (row) {
      this.dlg.modo = 'editar'
      this.dlg.id = row.id
      this.form = { ...row, acopio_cosecha_id: this.cosecha?.id || row.acopio_cosecha_id }
      this.dlg.open = true
    },
    async onGuardar () {
      try {
        this.saving = true
        if (this.dlg.modo === 'crear') {
          await this.$axios.post('/medicamentos', this.form)
          this.$q.notify({ type: 'positive', message: 'Registro creado' })
        } else {
          await this.$axios.put(`/medicamentos/${this.dlg.id}`, this.form)
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
        message: '¿Desea eliminar este registro?',
        cancel: true,
        persistent: true
      }).onOk(async () => {
        try {
          await this.$axios.delete(`/medicamentos/${row.id}`)
          this.$q.notify({ type: 'positive', message: 'Eliminado' })
          this.fetchRows()
        } catch (e) {
          this.$q.notify({ type: 'negative', message: 'No se pudo eliminar' })
        }
      })
    },
    // MODIFICACION 2025-11-17: Metodo para imprimir formulario de medicamentos
    // Abre en nueva ventana el PDF generado por el backend
    onImprimir () {
      if (!this.cosecha?.id) return
      const url = this.$axios.defaults.baseURL + `/medicamentos/${this.cosecha.id}/imprimir`
      window.open(url, '_blank')
    }
  }
}
</script>
