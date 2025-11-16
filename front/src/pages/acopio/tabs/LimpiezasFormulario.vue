<template>
  <q-card flat bordered>
    <q-card-section class="row items-center justify-between">
      <div class="text-subtitle1">Limpieza y Desinfección</div>
      <q-btn color="primary" icon="add" label="Nuevo" no-caps @click="onNuevo" />
    </q-card-section>

    <q-separator />

    <q-card-section>
      <q-markup-table dense wrap-cells bordered flat>
        <thead>
          <tr class="bg-primary text-white">
            <th>Acciones</th>
            <th>Equipo/Herramienta</th>
            <th>Material Recubrimiento</th>
            <th>Método Limpieza</th>
            <th>Producto Químico</th>
            <th>Fecha Aplicación</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in rows" :key="row.id">
            <td class="text-right">
              <q-btn-dropdown dense no-caps label="Acciones" color="primary" size="10px">
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
            <td>{{ row.equipo_herramienta_material }}</td>
            <td>{{ row.material_recubrimiento }}</td>
            <td>{{ row.metodo_limpieza_utilizado }}</td>
            <td>{{ row.producto_quimico_desinfeccion }}</td>
            <td>{{ row.fecha_aplicacion }}</td>
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
            <div class="col-12 col-md-6">
              <q-input v-model="form.equipo_herramienta_material" label="Equipo, Herramienta o Material Apícola" dense outlined />
            </div>
            <div class="col-12 col-md-6">
              <q-input v-model="form.material_recubrimiento" label="Material de Recubrimiento" dense outlined />
            </div>
            <div class="col-12 col-md-6">
              <q-input v-model="form.metodo_limpieza_utilizado" label="Método de Limpieza Utilizado" dense outlined />
            </div>
            <div class="col-12 col-md-6">
              <q-input v-model="form.producto_quimico_desinfeccion" label="Producto Químico o Desinfección" dense outlined />
            </div>
            <div class="col-12 col-md-6">
              <q-input v-model="form.fecha_aplicacion" type="date" label="Fecha de Aplicación" dense outlined />
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
  name: 'LimpiezasFormulario',
  props: {
    cosecha: { type: Object, required: true }
  },
  data () {
    return {
      loading: false,
      saving: false,
      rows: [],
      dlg: { open: false, modo: 'crear', id: null },
      form: {
        acopio_cosecha_id: null,
        equipo_herramienta_material: '',
        material_recubrimiento: '',
        metodo_limpieza_utilizado: '',
        producto_quimico_desinfeccion: '',
        fecha_aplicacion: null
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
        equipo_herramienta_material: '',
        material_recubrimiento: '',
        metodo_limpieza_utilizado: '',
        producto_quimico_desinfeccion: '',
        fecha_aplicacion: null
      }
    },
    async fetchRows () {
      try {
        this.loading = true
        const { data } = await this.$axios.get('/limpiezas', {
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
          await this.$axios.post('/limpiezas', this.form)
          this.$q.notify({ type: 'positive', message: 'Registro creado' })
        } else {
          await this.$axios.put(`/limpiezas/${this.dlg.id}`, this.form)
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
          await this.$axios.delete(`/limpiezas/${row.id}`)
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
