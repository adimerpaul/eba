<template>
  <q-page class="q-pa-md">
    <div class="row items-center q-gutter-sm q-mb-md">
      <q-btn flat round icon="arrow_back" @click="$router.back()" />
      <div class="text-h6">Editar Productor</div>
      <q-space />
      <q-btn flat round icon="refresh" :loading="loading" @click="fetchProductor" />
    </div>

    <q-card flat bordered>
      <q-tabs v-model="tab" class="text-primary" align="left" dense>
        <q-tab name="general" icon="badge" label="1) Información general" no-caps />
        <q-tab name="certs" icon="verified" label="2) Certificaciones" no-caps />
        <q-tab name="apiarios" icon="hive" label="3)Apiarios" no-caps />
         <q-tab name="mapa" icon="map" label="4) Mapa" no-caps />
      </q-tabs>
      <q-separator />

      <q-tab-panels v-model="tab" animated>
        <q-tab-panel name="general" class="q-pa-none">
          <q-card-section v-if="!loading && productor">
            <ProductorForm
              :productor="productor"
              banner-msg="Actualiza los datos y guarda los cambios"
              @saved="onSaved"
              @cancel="$router.back()"
            />
          </q-card-section>
        </q-tab-panel>

        <q-tab-panel name="certs" class="q-pa-none">
          <ProductorCertificaciones :productor="productor" />
        </q-tab-panel>

        <q-tab-panel name="apiarios" class="q-pa-none">
          <ProductorApiarios :productor="productor" />
        </q-tab-panel>
        <q-tab-panel name="mapa" class="q-pa-none">
          <ProductorMapa :productor="productor" />
        </q-tab-panel>
      </q-tab-panels>
    </q-card>
  </q-page>
</template>

<script>
import ProductorForm from 'pages/productores/ProductorForm.vue'
import ProductorCertificaciones from "pages/productores/tabs/ProductorCertificaciones.vue";
import ProductorApiarios from "pages/productores/tabs/ProductorApiarios.vue";
import ProductorMapa from "pages/productores/tabs/ProductorMapa.vue";

export default {
  name: 'ProductorEditar',
  components: {ProductorMapa, ProductorApiarios, ProductorCertificaciones, ProductorForm },
  data () {
    return {
      tab: 'general',
      loading: false,
      productor: null,
      certsLoading: false,
      apiLoading: false,
      certsLoaded: false,
      apiLoaded: false,
      certs: [],
      apiarios: []
    }
  },
  // watch: {
  //   tab (t) {
  //     if (t === 'certs' && !this.certsLoaded) this.fetchCerts()
  //     if (t === 'apiarios' && !this.apiLoaded) this.fetchApiarios()
  //   }
  // },
  mounted () {
    this.fetchProductor()
  },
  methods: {
    async fetchProductor () {
      this.loading = true
      try {
        const id = this.$route.params.id
        const { data } = await this.$axios.get(`productores/${id}`)
        this.productor = data
      } catch (e) {
        this.productor = null
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo cargar el productor')
      } finally {
        this.loading = false
      }
    },
    onSaved (saved) {
      this.productor = saved
      this.$alert?.success?.('Cambios guardados')
    }
  }
}
</script>
