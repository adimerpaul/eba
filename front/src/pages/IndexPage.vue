<template>
  <q-page class="q-pa-md bg-grey-2">
    <div class="row q-col-gutter-md">

      <!-- Dashboard de Trazabilidad Integral -->
      <div class="col-12">
        <q-card flat bordered>
          <q-card-section>
            <div class="row items-center">
              <div class="col">
                <div class="text-h6 text-primary">Dashboard de Trazabilidad Integral</div>
                <div class="text-caption text-grey-7">
                  Sistema completo de seguimiento de producción apícola
                </div>
              </div>
              <div class="col-auto">
                <q-select
                  v-model="gestion"
                  :options="opcionesGestion"
                  label="Gestión"
                  dense
                  outlined
                  style="min-width: 120px"
                  class="q-mr-md"
                />
                <q-btn
                  label="Ver Dashboard Completo"
                  icon="analytics"
                  color="primary"
                  outline
                  no-caps
                  @click="$router.push('/dashboard-trazabilidad')"
                />
              </div>
            </div>
          </q-card-section>

          <q-separator />

          <q-card-section>
            <dashboard-trazabilidad-resumen :gestion="parseInt(gestion)" />
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script>
import { defineComponent } from 'vue';
import DashboardTrazabilidadResumen from 'components/DashboardTrazabilidadResumen.vue';

export default defineComponent({
  name: 'IndexPage',
  
  components: {
    DashboardTrazabilidadResumen
  },

  data () {
    return {
      gestion: new Date().getFullYear(),
      opcionesGestion: [2023, 2024, 2025, 2026, 2027]
    }
  }
});
</script>

<style scoped>
/* Estilos si son necesarios */
</style>
