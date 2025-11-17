<template>
  <q-card flat bordered>
    <q-card-section class="row items-center justify-between">
      <div class="text-subtitle1">Control de Plagas</div>
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
            <th>N° Colmenas</th>
            <th>Nombre Plaga</th>
            <th>Plaga Presente</th>
            <th>Daño Visible</th>
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
            <td>{{ row.numero_colmenas_apiario }}</td>
            <td>{{ row.nombre_plaga }}</td>
            <td>{{ row.plaga_presente }}</td>
            <td>{{ row.daño_visible_apiario }}</td>
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
            <div class="col-12 col-md-4">
              <q-input v-model="form.fecha" type="date" label="Fecha" dense outlined />
            </div>
            <div class="col-12 col-md-4">
              <q-input v-model="form.numero_colmenas_apiario" label="N° Colmenas en Apiario" dense outlined />
            </div>
            <div class="col-12 col-md-4">
              <q-input v-model="form.nombre_plaga" label="Nombre de la Plaga" dense outlined />
            </div>
            <div class="col-12 col-md-6">
              <q-input v-model="form.plaga_presente" label="Plaga Presente en el Apiario" dense outlined />
            </div>
            <div class="col-12 col-md-6">
              <q-input v-model="form.daño_visible_apiario" label="Daño Visible en el Apiario" dense outlined />
            </div>
            <div class="col-12">
              <q-input v-model="form.medidas_control_celdilla" label="Medidas de Control por Celdilla" dense outlined />
            </div>
            <div class="col-12">
              <q-input v-model="form.observaciones" type="textarea" autogrow label="Observaciones" dense outlined />
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
  name: 'PlagasFormulario',
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
        numero_colmenas_apiario: '',
        nombre_plaga: '',
        plaga_presente: '',
        daño_visible_apiario: '',
        medidas_control_celdilla: '',
        observaciones: ''
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
        numero_colmenas_apiario: '',
        nombre_plaga: '',
        plaga_presente: '',
        daño_visible_apiario: '',
        medidas_control_celdilla: '',
        observaciones: ''
      }
    },
    async fetchRows () {
      try {
        this.loading = true
        const { data } = await this.$axios.get('/plagas', {
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
          await this.$axios.post('/plagas', this.form)
          this.$q.notify({ type: 'positive', message: 'Registro creado' })
        } else {
          await this.$axios.put(`/plagas/${this.dlg.id}`, this.form)
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
          await this.$axios.delete(`/plagas/${row.id}`)
          this.$q.notify({ type: 'positive', message: 'Eliminado' })
          this.fetchRows()
        } catch (e) {
          this.$q.notify({ type: 'negative', message: 'No se pudo eliminar' })
        }
      })
    },
    // MODIFICACION 2025-11-17: Metodo para imprimir formulario de plagas
    // Abre en nueva ventana el PDF generado por el backend
    onImprimir () {
      if (!this.cosecha?.id) return
      const url = this.$axios.defaults.baseURL + `/plagas/${this.cosecha.id}/imprimir`
      window.open(url, '_blank')
    }
  }
}
</script>
