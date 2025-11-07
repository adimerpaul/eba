<template>
  <q-card flat bordered>
    <q-card-section class="row items-center justify-between">
      <div class="text-subtitle1">Formularios / Documentos</div>
      <q-btn color="primary" icon="add" label="Nuevo" no-caps @click="onNuevo"/>
    </q-card-section>

    <q-separator/>

    <q-card-section>
      <q-markup-table dense wrap-cells bordered flat>
        <thead>
        <tr class="bg-primary text-white">
          <th style="width: 120px;">Acciones</th>
          <th>Fecha</th>
          <th>Nombre</th>
          <th>Usuario</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="row in rows" :key="row.id">
          <td class="text-right">
            <q-btn-dropdown dense no-caps label="Acciones" color="primary" size="10px">
              <q-list>
                <q-item clickable v-ripple @click="onEditar(row)" v-close-popup>
                  <q-item-section avatar>
                    <q-icon name="edit"/>
                  </q-item-section>
                  <q-item-section>Editar</q-item-section>
                </q-item>
                <q-item clickable v-ripple @click="onEliminar(row)" v-close-popup>
                  <q-item-section avatar>
                    <q-icon name="delete" color="negative"/>
                  </q-item-section>
                  <q-item-section>Eliminar</q-item-section>
                </q-item>
<!--                imprimir-->
                <q-item clickable v-ripple @click="imprimir(row)" v-close-popup>
                  <q-item-section avatar>
                    <q-icon name="print"/>
                  </q-item-section>
                  <q-item-section>Imprimir</q-item-section>
                </q-item>
              </q-list>
            </q-btn-dropdown>
          </td>
          <td>{{ row.fecha || '—' }}</td>
          <td>
<!--            <div class="ellipsis-2-lines" v-html="preview(row.html)"></div>-->
            {{ row.nombre || 'Documento sin nombre' }}
          </td>
          <td>{{ row.user?.name || '—' }}</td>
        </tr>
        </tbody>
      </q-markup-table>
    </q-card-section>

    <!-- Diálogo Crear/Editar -->
    <q-dialog v-model="dlg.open" persistent>
      <q-card style="max-width: 90vw;">
        <q-card-section class="row items-center justify-between">
          <div class="text-subtitle1">{{ dlg.modo === 'crear' ? 'Nuevo documento' : 'Editar documento' }}</div>
          <q-btn flat round dense icon="close" v-close-popup/>
        </q-card-section>

        <q-separator/>

        <q-card-section class="">
          <div class="row q-col-gutter-md">
            <div class="col-12 col-md-6">
              <q-input v-model="form.nombre" label="Nombre" dense outlined/>
            </div>
            <div class="col-12 col-md-6">
              <q-select
                v-model="plantillaSeleccionada"
                :options="planillas"
                option-value="id"
                option-label="nombre"
                emit-value
                map-options
                dense outlined
                label="Plantilla"
                clearable
                @update:model-value="aplicarPlantilla"
              />
            </div>
            <div class="col-12">
              <div class="text-caption text-grey-7 q-mb-xs">Contenido (HTML)</div>
              <q-editor
                v-model="form.html"
                min-height="320px"
                placeholder="Escriba aquí el contenido de la sesión..."
                :toolbar="[
                ['left','center','right','justify'],
                ['bold','italic','strike','underline'],
                ['quote','unordered','ordered','outdent','indent'],
                ['hr','link'],
                ['undo','redo'],
                ['removeFormat']
              ]"
              />
            </div>
          </div>
        </q-card-section>

        <q-separator/>

        <q-card-actions align="right">
          <q-btn flat label="Cancelar" v-close-popup/>
          <q-btn color="primary" :label="dlg.modo === 'crear' ? 'Guardar' : 'Actualizar'" @click="onGuardar"
                 :loading="saving"/>
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-card>
</template>

<script>

// import {actaRecepcionConformidad} from "src/planillas/documentoPlanilla.js";

import {actaRecepcionConformidadPDF} from "src/planillas/documentoPlanilla.js";
import {reciboCompraPolenPDF} from "src/planillas/recibocomprapolen.js";
import {reciboCompraCeraPDF} from "src/planillas/recibocompracera.js";

export default {
  name: 'Documentos',
  props: {
    cosecha: {type: Object, required: true},
    // opcional: si ya tienes el id de usuario en tu layout, puedes pasarlo aquí
    currentUserId: {type: [Number, String], default: null}
  },
  data() {
    return {
      rows: [],
      loading: false,
      saving: false,
      dlg: {open: false, modo: 'crear', id: null},
      form: {
        acopio_cosecha_id: null,
        user_id: null,
        fecha: null,
        html: ''
      },
      plantillaSeleccionada : null,
      planillas: [
        // ACTA DE RECEPCIÓN Y CONFORMIDAD DE MATERIA PRIMA
        // ACTA DE CONTROL CALIDAD Y CONFORMIDAD DE MATERIA PRIMA
        // ACTA DE ENTREGA DE MATERIA PRIMA MIEL
        // ACTA DE ENTREGA DE MIEL EBA MONTEAGUDO
        // RECEPCION DE MATERIA PRIMA
        // NOTA DE INGRESO DE MATERIA PRIMA
        // RECIBO COMPRA DE MIEL
        {
          id: 1,
          nombre: 'Acta de Recepción y Conformidad de Materia Prima',
          html: '<h1>Acta de Recepción y Conformidad de Materia Prima</h1><p>Contenido del acta...</p>'
        },
        {
          id: 2,
          nombre: 'Acta de Control Calidad y Conformidad de Materia Prima',
          html: '<h1>Acta de Control Calidad y Conformidad de Materia Prima</h1><p>Contenido del acta...</p>'
        },
        {
          id: 3,
          nombre: 'Acta de Entrega de Materia Prima Miel',
          html: '<h1>Acta de Entrega de Materia Prima Miel</h1><p>Contenido del acta...</p>'
        },
        {
          id: 4,
          nombre: 'Acta de Entrega de Miel EBA Monteagudo',
          html: '<h1>Acta de Entrega de Miel EBA Monteagudo</h1><p>Contenido del acta...</p>'
        },
        {
          id: 5,
          nombre: 'Recepción de Materia Prima',
          html: '<h1>Recepción de Materia Prima</h1><p>Contenido del documento...</p>'
        },
        {
          id: 6,
          nombre: 'Nota de Ingreso de Materia Prima',
          html: '<h1>Nota de Ingreso de Materia Prima</h1><p>Contenido del documento...</p>'
        },
        {
          id: 7,
          nombre: 'Recibo Compra de Miel',
          html: '<h1>Recibo Compra de Miel</h1><p>Contenido del recibo...</p>'
        },
        {
          id: 8,
          nombre: 'Recibo Compra de Polen',
          html: '<h1>Recibo Compra de Polen</h1><p>Contenido del recibo...</p>'
        },
        {
          id: 9,
          nombre: 'Recibo Compra de Cera de Abeja',
          html: '<h1>Recibo Compra de Cera de Abeja</h1><p>Contenido del recibo...</p>'
        }
      ]
    }
  },
  mounted() {
    this.fetchRows()
    // si no te pasan currentUserId por props y quieres traerlo del backend:
    // if (!this.currentUserId) this.fetchMe()
  },
  methods: {
    aplicarPlantilla(plantillaId) {
      const plantilla = this.planillas.find(p => p.id === plantillaId)

      if (plantilla) {
        this.form.html = ''
        if (plantilla.nombre === 'Acta de Recepción y Conformidad de Materia Prima' ){
          this.form.html = actaRecepcionConformidadPDF(this.cosecha);
        }
        if (plantilla.nombre === 'Recibo Compra de Polen' ){
          this.form.html = reciboCompraPolenPDF(this.cosecha);
        }
        if (plantilla.nombre === 'Recibo Compra de Cera de Abeja' ){
          this.form.html = reciboCompraCeraPDF(this.cosecha);
        }
        this.form.nombre = plantilla.nombre
        console.log(this.form.html)
      }
    },
    // async fetchMe() {
    //   try {
    //     const {data} = await this.$axios.get('/me')
    //     this.form.user_id = data?.id || null
    //   } catch (e) {
    //     // silencioso: si falla, igual te dejará guardar si seteas user_id manualmente
    //   }
    // },
    // preview(html) {
    //   if (!html) return ''
    //   const text = html.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim()
    //   return text.length > 140 ? text.slice(0, 140) + '…' : text
    // },
    resetForm() {
      this.form = {
        acopio_cosecha_id: this.cosecha?.id || null,
        user_id: this.currentUserId || this.form.user_id || null,
        fecha: null,
        html: ''
      }
    },
    async fetchRows() {
      try {
        this.loading = true
        const {data} = await this.$axios.get('/documentos', {
          params: {cosecha_id: this.cosecha?.id}
        })
        this.rows = Array.isArray(data) ? data : (data?.data || [])
      } catch (e) {
        this.$q.notify({type: 'negative', message: 'No se pudieron cargar los documentos'})
      } finally {
        this.loading = false
      }
    },
    onNuevo() {
      this.dlg.modo = 'crear'
      this.dlg.id = null
      this.resetForm()
      this.dlg.open = true
    },
    onEditar(row) {
      this.dlg.modo = 'editar'
      this.dlg.id = row.id
      this.form = {
        acopio_cosecha_id: this.cosecha?.id || row.acopio_cosecha_id,
        user_id: row.user_id || this.currentUserId || this.form.user_id,
        fecha: row.fecha || null,
        html: row.html || '',
        nombre: row.nombre || ''
      }
      this.dlg.open = true
    },
    async onGuardar() {
      try {
        this.saving = true
        if (!this.form.user_id && this.currentUserId) {
          this.form.user_id = this.currentUserId
        }
        if (this.dlg.modo === 'crear') {
          await this.$axios.post('/documentos', this.form)
          this.$q.notify({type: 'positive', message: 'Documento creado'})
        } else {
          await this.$axios.put(`/documentos/${this.dlg.id}`, this.form)
          this.$q.notify({type: 'positive', message: 'Documento actualizado'})
        }
        this.dlg.open = false
        this.fetchRows()
      } catch (e) {
        this.$q.notify({type: 'negative', message: 'Error al guardar'})
      } finally {
        this.saving = false
      }
    },
    async onEliminar(row) {
      this.$q.dialog({
        title: 'Eliminar',
        message: '¿Desea eliminar este documento?',
        cancel: true,
        persistent: true
      }).onOk(async () => {
        try {
          await this.$axios.delete(`/documentos/${row.id}`)
          this.$q.notify({type: 'positive', message: 'Eliminado'})
          this.fetchRows()
        } catch (e) {
          this.$q.notify({type: 'negative', message: 'No se pudo eliminar'})
        }
      })
    },
    imprimir(row) {
      const url = this.$axios.defaults.baseURL + `/documentos/${row.id}/imprimir`;
      window.open(url, '_blank');
    }
  }
}
</script>

<style scoped>
.ellipsis-2-lines {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
