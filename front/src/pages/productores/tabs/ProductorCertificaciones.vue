<template>
  <q-card-section>
    <div class="row items-center q-gutter-sm q-mb-sm">
      <div class="text-subtitle1">Certificaciones</div>
      <q-space />
      <q-btn
        dense color="primary" icon="add_circle" label="Nueva"
        @click="startCreate" :disable="saving" no-caps
      />
      <!--
      <q-btn
        dense flat icon="picture_as_pdf" label="Imprimir"
        :disable="!productor?.id" @click="printPdf" no-caps
      />
      -->
    </div>

    <!-- Tabla -->
    <q-markup-table dense>
      <thead>
      <tr class="bg-grey-3">
        <th class="text-left">#</th>
        <th class="text-left" style="width:120px;">Opciones</th>
        <th class="text-left">Tipo</th>
        <th class="text-left">Organismo</th>
        <th class="text-left">Emisión</th>
        <th class="text-left">Vencimiento</th>
        <th class="text-left">Estado</th>
        <th class="text-left">URL</th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="(c,i) in (productor?.certificaciones || [])" :key="c.id || i">
        <td>{{ i + 1 }}</td>

        <td>
          <q-btn-dropdown dense label="Opciones" color="primary" no-caps size="10px">
            <q-list>
              <q-item clickable v-ripple @click="startEdit(c)" v-close-popup>
                <q-item-section avatar><q-icon name="edit" /></q-item-section>
                <q-item-section>Editar</q-item-section>
              </q-item>
              <q-item clickable v-ripple @click="remove(c)" v-close-popup>
                <q-item-section avatar><q-icon name="delete" class="text-negative" /></q-item-section>
                <q-item-section class="text-negative">Eliminar</q-item-section>
              </q-item>
              <q-item clickable v-ripple @click="printPdf(c)" v-close-popup>
                <q-item-section avatar><q-icon name="picture_as_pdf" /></q-item-section>
                <q-item-section>Imprimir</q-item-section>
              </q-item>
            </q-list>
          </q-btn-dropdown>
        </td>

        <td>{{ c.tipo_certificacion || '-' }}</td>
        <td>{{ c.organismo_certificador || '-' }}</td>
        <td>{{ c.fecha_emision || '-' }}</td>
        <td>{{ c.fecha_vencimiento || '-' }}</td>
        <td>
          <q-chip :color="chip(c.estado)" text-color="white" size="xs" class="text-bold">
            {{ c.estado }}
          </q-chip>
        </td>
        <td>
          <a v-if="c.certificado_url" :href="c.certificado_url" target="_blank">{{ shortUrl(c.certificado_url) }}</a>
          <span v-else>-</span>
        </td>
      </tr>

      <tr v-if="!loading && (productor?.certificaciones?.length || 0) === 0">
        <td colspan="8" class="text-center text-grey">Sin registros</td>
      </tr>
      </tbody>
    </q-markup-table>

    <!-- Dialog Crear/Editar -->
    <q-dialog v-model="dlg.open" persistent>
      <q-card style="min-width: 720px; max-width: 95vw">
        <q-card-section class="row items-center q-gutter-sm">
          <div class="text-h6">
            {{ form.id ? 'Editar certificación' : 'Nueva certificación' }}
          </div>
          <q-space />
          <q-btn flat round dense icon="close" v-close-popup />
        </q-card-section>
        <q-separator />

        <q-card-section>
          <q-form @submit="submit" class="row q-col-gutter-sm">
            <div class="col-12 col-sm-4">
              <q-select
                v-model="form.tipo_certificacion"
                :options="tipoOptions"
                dense outlined label="Tipo"
                :rules="[v=>!!v || 'Requerido']"
              />
            </div>
            <div class="col-12 col-sm-8">
              <q-input
                v-model="form.organismo_certificador"
                dense outlined label="Organismo certificador"
              />
            </div>

            <div class="col-6 col-sm-3">
              <q-input v-model="form.fecha_emision" type="date" dense outlined label="Emisión" />
            </div>
            <div class="col-6 col-sm-3">
              <q-input v-model="form.fecha_vencimiento" type="date" dense outlined label="Vencimiento" />
            </div>

            <div class="col-12 col-sm-3">
              <q-select
                v-model="form.estado"
                :options="['VIGENTE','VENCIDO','SUSPENDIDO']"
                dense outlined label="Estado"
              />
            </div>

            <div class="col-12">
              <q-input v-model="form.certificado_url" dense outlined label="URL certificado (opcional)" />
            </div>

            <div class="col-12 text-right q-gutter-sm q-mt-sm">
              <q-btn flat color="grey" label="Cancelar" v-close-popup :disable="saving" />
              <q-btn color="primary" :label="form.id ? 'Guardar cambios' : 'Crear'"
                     type="submit" :loading="saving" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-card-section>
</template>

<script>
export default {
  name: 'ProductorCertificaciones',
  props: {
    productor: { type: Object, required: true }
  },
  emits: ['updated'],
  data () {
    return {
      loading: false,
      saving: false,

      dlg: { open: false },

      form: this.emptyForm(),
      tipoOptions: [
        'ORGANICO',
        'COMERCIO_JUSTO',
        'DENOMINACION_ORIGEN',
        'BUENAS_PRACTICAS'
      ]
    }
  },
  methods: {
    emptyForm () {
      return {
        id: null,
        productor_id: this.productor?.id || null,
        tipo_certificacion: null,
        organismo_certificador: '',
        fecha_emision: null,
        fecha_vencimiento: null,
        certificado_url: '',
        estado: 'VIGENTE'
      }
    },
    chip (estado) {
      if (estado === 'VIGENTE') return 'green'
      if (estado === 'VENCIDO') return 'red'
      if (estado === 'SUSPENDIDO') return 'orange'
      return 'grey'
    },
    shortUrl (u) {
      try { return u && u.length > 40 ? u.slice(0, 40) + '…' : (u || '-') } catch { return u || '-' }
    },

    startCreate () {
      this.form = this.emptyForm()
      this.form.productor_id = this.productor?.id || null
      this.dlg.open = true
    },
    startEdit (row) {
      this.form = {
        id: row.id,
        productor_id: this.productor?.id || row.productor_id,
        tipo_certificacion: row.tipo_certificacion || null,
        organismo_certificador: row.organismo_certificador || '',
        fecha_emision: row.fecha_emision || null,
        fecha_vencimiento: row.fecha_vencimiento || null,
        certificado_url: row.certificado_url || '',
        estado: row.estado || 'VIGENTE'
      }
      this.dlg.open = true
    },

    async submit () {
      if (!this.productor?.id) return
      this.saving = true
      try {
        const payload = { ...this.form, productor_id: this.productor.id }
        if (payload.id) {
          await this.$axios.put(`certificaciones/${payload.id}`, payload)
        } else {
          await this.$axios.post('certificaciones', payload)
        }
        this.$alert?.success?.(payload.id ? 'Certificación actualizada' : 'Certificación creada')
        this.dlg.open = false
        this.$emit('updated') // el padre vuelve a llamar GET /productores/{id}
      } catch (e) {
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo guardar')
      } finally {
        this.saving = false
      }
    },

    remove (row) {
      this.$alert?.dialog?.('¿Eliminar certificación?')?.onOk(async () => {
        try {
          await this.$axios.delete(`certificaciones/${row.id}`)
          this.$alert?.success?.('Eliminado')
          this.$emit('updated')
        } catch (e) {
          this.$alert?.error?.(e.response?.data?.message || 'No se pudo eliminar')
        }
      })
    },

    async printPdf (cert) {
      // if (!this.productor?.id) return
      // this.printing = true
      // try {
      //   const url = `productores/${this.productor.id}/certificaciones/print`
      //   const res = await this.$axios.get(url, { responseType: 'blob' })
      //
      //   // nombre de archivo desde header o por defecto
      //   let filename = `certificaciones_${this.productor.id}.pdf`
      //   const dispo = res.headers['content-disposition']
      //   if (dispo) {
      //     const match = dispo.match(/filename="?([^"]+)"?/i)
      //     if (match && match[1]) filename = match[1]
      //   }
      //
      //   // descargar blob
      //   const blob = new Blob([res.data], { type: 'application/pdf' })
      //   const link = document.createElement('a')
      //   link.href = URL.createObjectURL(blob)
      //   link.download = filename
      //   document.body.appendChild(link)
      //   link.click()
      //   URL.revokeObjectURL(link.href)
      //   document.body.removeChild(link)
      // } catch (e) {
      //   this.$alert?.error?.(e.response?.data?.message || 'No se pudo generar el PDF')
      // } finally {
      //   this.printing = false
      // }
      this.$axios.get(`certificaciones/${cert.id}/print`, { responseType: 'blob' })
        .then(res => {
          // nombre de archivo desde header o por defecto
          let filename = `certificacion_${cert.id}.pdf`
          const dispo = res.headers['content-disposition']
          if (dispo) {
            const match = dispo.match(/filename="?([^"]+)"?/i)
            if (match && match[1]) filename = match[1]
          }

          // descargar blob
          const blob = new Blob([res.data], { type: 'application/pdf' })
          const link = document.createElement('a')
          link.href = URL.createObjectURL(blob)
          link.download = filename
          document.body.appendChild(link)
          link.click()
          URL.revokeObjectURL(link.href)
          document.body.removeChild(link)
        })
        .catch(e => {
          this.$alert?.error?.(e.response?.data?.message || 'No se pudo generar el PDF')
        })
    }
  }
}
</script>
