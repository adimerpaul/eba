<template>
  <q-page class="q-pa-md">
    <div class="row items-center q-gutter-sm q-mb-md">
      <q-btn flat round icon="arrow_back" @click="$router.push('/productores')" />
      <div class="text-h6">Editar Productor</div>
      <q-space />
      <q-btn flat round icon="refresh" :loading="loading" @click="fetchProductor" />
    </div>

    <q-card flat bordered>
      <q-tabs v-model="tab" class="text-primary" align="left" dense>
        <q-tab name="general" icon="badge" label="1) Información general" no-caps />
        <q-tab name="runsa" icon="assured_workload" label="2) Runsa" no-caps />
        <q-tab name="certs" icon="verified" label="3) Certificaciones" no-caps />
        <q-tab name="apiarios" icon="hive" label="4) Apiarios" no-caps />
        <q-tab name="mapa" icon="map" label="5) Mapa" no-caps />
        <q-tab name="acopio" icon="send" label="6) Acopio" no-caps />
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
          <ProductorCertificaciones :productor="productor" @updated="fetchProductor" />
        </q-tab-panel>
        
        <q-tab-panel name="runsa" class="q-pa-none">
          <ProductorRunsa :productor="productor" @updated="fetchProductor" />
        </q-tab-panel>

        <q-tab-panel name="apiarios" class="q-pa-none">
          <ProductorApiarios :productor="productor" @updated="fetchProductor" />
        </q-tab-panel>

        <q-tab-panel name="mapa" class="q-pa-none">
          <ProductorMapa :productor="productor" @updated="fetchProductor" />
        </q-tab-panel>
<<<<<<< HEAD

        <!-- NUEVO PANEL: Tab de Acopios por Gestión -->
        <q-tab-panel name="acopios" class="q-pa-none">
          <ProductorAcopiosGestion v-if="!loading && productor" :productor="productor" @updated="fetchProductor" />
        </q-tab-panel>
      </q-tab-panels>
=======
>>>>>>> f04b519dcced93c84a639dada6a8506e143503cb

        <q-tab-panel name="acopio" class="q-pa-none">
          <ProductorAcopios :productor="productor" @updated="fetchProductor" />
        </q-tab-panel>
      </q-tab-panels>
    </q-card>
  </q-page>
</template>

<script>
import ProductorForm from 'pages/productores/ProductorForm.vue'
import ProductorCertificaciones from 'pages/productores/tabs/ProductorCertificaciones.vue'
import ProductorApiarios from 'pages/productores/tabs/ProductorApiarios.vue'
import ProductorMapa from 'pages/productores/tabs/ProductorMapa.vue'
import ProductorRunsa from 'pages/productores/tabs/ProductorRunsa.vue'
<<<<<<< HEAD
// NUEVO: Importar componente de Acopios por Gestión
import ProductorAcopiosGestion from 'pages/productores/tabs/ProductorAcopiosGestion.vue'

export default {
  name: 'ProductorShow',
  components: { 
    ProductorMapa, 
    ProductorApiarios, 
    ProductorCertificaciones, 
    ProductorForm, 
    ProductorRunsa,
    ProductorAcopiosGestion // Registrar nuevo componente
  },
=======
import ProductorAcopios from 'pages/productores/tabs/ProductorAcopios.vue'

export default {
  name: 'ProductorShow',
  components: { ProductorMapa, ProductorApiarios, ProductorCertificaciones, ProductorForm, ProductorRunsa, ProductorAcopios },
>>>>>>> f04b519dcced93c84a639dada6a8506e143503cb
  data () {
    return {
      tab: 'general',
      loading: false,
      productor: null
    }
  },
  mounted () { this.fetchProductor() },
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
