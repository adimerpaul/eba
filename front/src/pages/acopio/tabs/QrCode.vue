<template>
  <q-card flat bordered>
    <q-card-section class="row items-center justify-between">
      <div class="text-subtitle1">Formularios / Documentos</div>
    </q-card-section>

    <q-separator />

    <q-card-section class="q-pa-md">
      <div class="column items-center">

        <!-- QR -->
        <q-card flat bordered class="q-pa-md radius-qr">
          <q-img
            :src="qrUrl"
            alt="Código QR de la Cosecha"
            style="width: 200px; height: 200px"
            ratio="1"
          />
        </q-card>

        <!-- Botón principal: ir a la ficha pública -->
        <q-btn
          class="q-mt-md"
          color="primary"
          icon="open_in_new"
          label="Abrir ficha pública"
          @click="goPublic"
          no-caps
        />

        <!-- Acciones rápidas -->
        <div class="row q-gutter-sm q-mt-sm justify-center">
          <q-btn outline dense icon="content_copy" label="Copiar enlace" @click="copyLink" no-caps />
          <q-btn outline dense icon="ios_share" label="Compartir" @click="shareLink" :disable="!canShare" no-caps />
          <q-btn outline dense icon="download" label="Descargar QR" @click="downloadQr" no-caps />
        </div>

        <!-- URL visible y clicable -->
        <q-chip
          clickable
          color="grey-2"
          text-color="primary"
          icon="link"
          class="q-mt-sm"
          @click="goPublic"
        >
          {{ targetUrl }}
        </q-chip>

        <div class="text-center q-mt-md text-body2">
          Escanea este código QR o pulsa “Abrir ficha pública” para ver la información de la cosecha.
        </div>
      </div>

      <!-- Debug opcional -->
<!--      <pre class="q-mt-md">{{ cosecha }}</pre>-->
    </q-card-section>
  </q-card>
</template>

<script>
export default {
  name: 'QrCode',
  props: {
    cosecha: {
      type: Object,
      required: true
    }
  },
  computed: {
    baseOrigin () {
      return window.location?.origin ?? ''
    },
    targetPath () {
      // ruta interna del router
      return `/qr/${this.cosecha.qr_code}`
    },
    targetUrl () {
      // URL absoluta (útil para compartir/copiar)
      return `${this.baseOrigin}${this.targetPath}`
    },
    qrUrl () {
      const data = encodeURIComponent(this.targetUrl)
      return `https://api.qrserver.com/v1/create-qr-code/?data=${data}&size=200x200`
    },
    canShare () {
      return !!navigator.share
    }
  },
  methods: {
    goPublic () {
      // navega a la página pública (router interno)
      // this.$router.push(this.targetPath)
      // target black windows url
      window.open(this.targetUrl, '_blank')
    },
    async copyLink () {
      try {
        await navigator.clipboard.writeText(this.targetUrl)
        this.$q.notify({ type: 'positive', message: 'Enlace copiado al portapapeles' })
      } catch (e) {
        this.$q.notify({ type: 'negative', message: 'No se pudo copiar el enlace' })
      }
    },
    async shareLink () {
      try {
        if (navigator.share) {
          await navigator.share({
            title: 'Ficha de cosecha',
            text: 'Consulta la ficha pública de la cosecha',
            url: this.targetUrl
          })
        } else {
          await this.copyLink()
        }
      } catch (_) { /* usuario canceló */ }
    },
    async downloadQr () {
      try {
        // descarga segura del PNG del QR
        const res = await fetch(this.qrUrl, { mode: 'no-cors' })
        // algunos CORS bloquean blob; fallback directo:
        const a = document.createElement('a')
        a.href = this.qrUrl
        a.download = `qr_${this.cosecha.qr_code}.png`
        document.body.appendChild(a)
        a.click()
        a.remove()
      } catch (e) {
        // fallback directo si fetch falla
        const a = document.createElement('a')
        a.href = this.qrUrl
        a.download = `qr_${this.cosecha.qr_code}.png`
        document.body.appendChild(a)
        a.click()
        a.remove()
      }
    }
  }
}
</script>

<style scoped>
.radius-qr {
  border-radius: 16px;
}
</style>
