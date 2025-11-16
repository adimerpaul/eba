<template>
  <q-layout view="lHh Lpr lFf">
    <q-page-container>
      <q-page class="q-pa-md bg-grey-2">

        <div class="row items-center q-mb-md">
          <div class="col">
            <div class="text-h6 text-weight-bold">Trazabilidad de Cosecha</div>
            <div class="text-caption text-grey-7">Consulta pública vía QR</div>
          </div>
          <div class="col-auto">
<!--            btn actulizar-->
            <q-btn flat round icon="refresh" @click="fetchData" :disable="loading" />
            <q-btn flat round icon="share" @click="shareLink" :disable="!ready" />
            <q-btn flat round icon="print" @click="printPage" :disable="!ready" />
          </div>
        </div>

        <q-card flat bordered>
          <q-card-section>
            <div class="row items-center q-col-gutter-md">
              <div class="col-12 col-md-4 flex flex-center">
                <q-skeleton v-if="loading" type="QAvatar" size="200px" />
                <q-img
                  v-else
                  :src="qrUrl"
                  style="width: 200px; height: 200px"
                  alt="QR cosecha"
                />
              </div>

              <div class="col-12 col-md-8">
                <div v-if="loading">
                  <q-skeleton type="text" width="60%"/>
                  <q-skeleton type="text" width="40%"/>
                  <q-skeleton type="text" width="80%"/>
                </div>

                <div v-else-if="error">
                  <q-banner inline-actions class="bg-red-1 text-red-9 q-mb-md">
                    {{ error }}
                  </q-banner>
                </div>

                <div v-else-if="cosecha">
                  <div class="text-subtitle1 text-weight-bold q-mb-sm">
                    {{ cosecha.nombre_producto || 'Producto' }}
                  </div>

                  <div class="text-body2">
                    <div><b>NOMBRE CIP: </b> {{ cosecha.asociacion}}</div>
                    <div><b>Lote: </b> {{ cosecha.codigo_lote}}</div>
                    <div><b>Fecha Cosecha: </b> {{ fmt(cosecha.fecha_cosecha) }}</div>
                    <div><b>Fecha Envasado: </b> {{ fmt(cosecha.fecha_envasado) }}</div>
                    <div><b>Fecha Vencimiento: </b> {{ fmt(cosecha.fecha_caducidad) }}</div>
                    <!--<div><b>Humedad (%):</b> {{ cosecha.humedad }}</div>
                    <div><b>Temp. almac. (°C):</b> {{ cosecha.temperatura_almacenaje }}</div>
                    <div class="q-mt-sm"><b>N° acta:</b> {{ cosecha.num_acta }}</div>-->
                  </div>

                  <q-separator class="q-my-md" />

                  <div class="text-body2">
                    <div class="text-subtitle2 text-weight-bold q-mb-xs">Productor(a)</div>
                    <div><b>Nombre:</b> {{ cosecha.productor }}</div>
                    <div><b>Origen:</b> {{ cosecha.nombre_departamento  }} - {{ cosecha.nombre_municipio }}</div>
                  </div>
                </div>
              </div>
            </div>
          </q-card-section>
        </q-card>

        <div class="q-mt-md text-center text-caption text-grey-7">
          Esta es una vista pública de verificación de trazabilidad.
        </div>

      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script>
import axios from 'axios'

export default {
  name: 'QrFront',
  data () {
    return {
      code: this.$route.params.code,
      cosecha: null,
      loading: true,
      error: null
    }
  },
  computed: {
    ready () { return !!this.cosecha && !this.loading && !this.error 
    },
    qrUrl () {
      const base = window.location?.origin ?? ''
      const target = `${base}/qr/${this.code}`
      return `https://api.qrserver.com/v1/create-qr-code/?data=${encodeURIComponent(target)}&size=200x200`
    }
  },
  methods: {
    async fetchData () {
      try {
        this.loading = true
        this.error = null
        console.log(this.code)
        console.log(encodeURIComponent(this.code))
        const url = `/cosechas/qr/${encodeURIComponent(this.code)}`
        const { data } = await this.$axios.get(url)
        console.log(data)

        // si tu API envuelve en {data: {...}}, ajusta:
        this.cosecha = data?.data?? data[0]
      } catch (e) {
        this.error = 'No se encontró la cosecha o el código es inválido.'
      } finally {
        this.loading = false
      }
    },
    fmt (iso) {
      if (!iso) return '—'
      try {
        const d = new Date(iso)
        return d.toLocaleDateString(undefined, { year:'numeric', month:'2-digit', day:'2-digit' })
      } catch { return iso }
    },
    openMap (lat, lng) {
      if (!lat || !lng) return
      // const url = `https://www.openstreetmap.org/?mlat=${lat}&mlon=${lng}#map=14/${lat}/${lng}`
      // window.open(url, '_blank')
      // abriri google masp
      const url = `https://www.google.com/maps/search/?api=1&query=${lat},${lng}`
      window.open(url, '_blank')
    },
    async shareLink () {
      const link = window.location.href
      if (navigator.share) {
        try { await navigator.share({ title: 'Cosecha', url: link }) } catch {}
      } else {
        await navigator.clipboard.writeText(link)
        this.$q.notify({ type: 'positive', message: 'Enlace copiado al portapapeles' })
      }
    },
    printPage () {
      window.print()
    }
  },
  async created () {
    await this.fetchData()
  }
}
</script>
