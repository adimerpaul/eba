<template>
  <q-page class="q-pa-md">
    <div class="row items-center q-gutter-sm q-mb-md">
      <q-btn flat round icon="arrow_back" @click="$router.push('/acopios')"/>
      <div class="text-h6">Cosecha</div>
      <q-space/>
      <q-btn flat round icon="refresh" :loading="loading" @click="acopioCosechaGet"/>
    </div>

    <q-card flat bordered>
      <q-tabs v-model="tab" class="text-primary" align="left" dense>
        <q-tab name="general" icon="badge" label="1) Información general" no-caps/>
        <q-tab name="analisis" icon="science" label="2) Análisis de calidad" no-caps/>
        <q-tab name="formularios" icon="description" label="3) Formularios" no-caps/>
        <q-tab name="qr" icon="qr_code" label="4) Código QR" no-caps/>
        <q-tab name="lotes" icon="view_list" label="5) Lotes" no-caps/>
      </q-tabs>
      <q-separator/>

      <q-tab-panels v-model="tab" animated>
        <q-tab-panel name="general" class="q-pa-none">
          <q-card-section v-if="!loading && cosecha">
            <AcopioFormulario :estado="'editar'" :cosecha="cosecha"/>
          </q-card-section>
        </q-tab-panel>

        <q-tab-panel name="analisis" class="q-pa-none">
          <AnalisisCalidad v-if="!loading && cosecha" :cosecha="cosecha"/>
        </q-tab-panel>

        <q-tab-panel name="formularios" class="q-pa-none">
          <Documentos v-if="!loading && cosecha" :cosecha="cosecha"/>
        </q-tab-panel>

        <q-tab-panel name="qr" class="q-pa-none">
          <QrCode v-if="!loading && cosecha" :cosecha="cosecha"/>
        </q-tab-panel>
        <q-tab-panel name="lotes" class="q-pa-none">
          <CosechaLotes v-if="!loading && cosecha" :cosecha="cosecha"/>
        </q-tab-panel>
      </q-tab-panels>
    </q-card>
    <!--    <AcopioFormulario :estado="'crear'" v-if="cosecha" :cosecha="cosecha"/>-->
  </q-page>
</template>
<script>
import AcopioFormulario from "pages/acopio/AcopioFormulario.vue";
import ProductorMapa from "pages/productores/tabs/ProductorMapa.vue";
import ProductorForm from "pages/productores/ProductorForm.vue";
import ProductorApiarios from "pages/productores/tabs/ProductorApiarios.vue";
import ProductorCertificaciones from "pages/productores/tabs/ProductorCertificaciones.vue";
import AnalisisCalidad from "pages/acopio/tabs/AnalisisCalidad.vue";
import Documentos from "pages/acopio/tabs/Documentos.vue";
import QrCode from "pages/acopio/tabs/QrCode.vue";
import CosechaLotes from "pages/acopio/tabs/CosechaLotes.vue";

export default {
  name: 'AcopioCrear',
  components: {
    CosechaLotes,
    QrCode,
    Documentos,
    AnalisisCalidad,
    ProductorCertificaciones, ProductorApiarios, ProductorForm, ProductorMapa, AcopioFormulario
  },
  data() {
    return {
      cosecha: null,
      loading: false,
      tab: 'general'
    }
  },
  mounted() {
    this.acopioCosechaGet();
  },
  methods: {
    // async acopioCosechaGet() {
    //   const cosechaId = this.$route.params.id;
    //   try {
    //     this.loading = true;
    //     const response = await this.$axios.get(`/acopio-cosechas/${cosechaId}`);
    //     this.cosecha = response.data;
    //   } catch (error) {
    //     console.error('Error fetching cosecha data:', error);
    //   } finally {
    //     this.loading = false;
    //   }
    // }
    acopioCosechaGet() {
      this.loading = true;
      const cosechaId = this.$route.params.id;
      this.$axios.get(`/acopio-cosechas/${cosechaId}`).then(response => {
        this.cosecha = response.data;
      }).catch(error => {
        this.$alert.error(error.response?.data?.message || 'Error fetching cosecha data');
      }).finally(() => {
        this.loading = false;
      });
    }
  }
}
</script>
