<template>
  <q-page class="q-pa-md">
    <div class="row items-center q-mb-md">
      <div class="col">
        <div class="text-h5">Dashboard de Trazabilidad Integral</div>
        <div class="text-caption text-grey-7">Sistema completo de seguimiento de producción apícola</div>
      </div>
      <div class="col-auto">
        <q-select
          v-model="gestion"
          :options="gestionesDisponibles"
          label="Gestión"
          dense
          outlined
          style="min-width: 120px"
          @update:model-value="cargarDatos"
        />
      </div>
    </div>

    <q-tabs
      v-model="tab"
      dense
      class="text-grey"
      active-color="primary"
      indicator-color="primary"
      align="justify"
    >
      <q-tab name="atras" icon="agriculture" label="Trazabilidad Hacia Atrás" no-caps />
      <q-tab name="proceso" icon="factory" label="Trazabilidad En Proceso" no-caps />
      <q-tab name="adelante" icon="local_shipping" label="Trazabilidad Hacia Adelante" no-caps />
    </q-tabs>

    <q-separator />

    <q-tab-panels v-model="tab" animated>
      <q-tab-panel name="atras">
        <trazabilidad-atras :gestion="gestion" ref="tabAtras" />
      </q-tab-panel>

      <q-tab-panel name="proceso">
        <trazabilidad-proceso :gestion="gestion" ref="tabProceso" />
      </q-tab-panel>

      <q-tab-panel name="adelante">
        <trazabilidad-adelante :gestion="gestion" ref="tabAdelante" />
      </q-tab-panel>
    </q-tab-panels>
  </q-page>
</template>

<script>
import TrazabilidadAtras from './TrazabilidadAtras.vue'
import TrazabilidadProceso from './TrazabilidadProceso.vue'
import TrazabilidadAdelante from './TrazabilidadAdelante.vue'

export default {
  name: 'DashboardTrazabilidad',
  components: {
    TrazabilidadAtras,
    TrazabilidadProceso,
    TrazabilidadAdelante
  },
  data() {
    return {
      tab: 'atras',
      gestion: new Date().getFullYear(),
      gestionesDisponibles: []
    }
  },
  mounted() {
    this.generarGestiones()
    this.cargarDatos()
  },
  methods: {
    generarGestiones() {
      const anioActual = new Date().getFullYear()
      this.gestionesDisponibles = []
      for (let i = anioActual; i >= anioActual - 5; i--) {
        this.gestionesDisponibles.push(i)
      }
    },
    cargarDatos() {
      this.$nextTick(() => {
        if (this.tab === 'atras' && this.$refs.tabAtras) {
          this.$refs.tabAtras.cargarDatos()
        } else if (this.tab === 'proceso' && this.$refs.tabProceso) {
          this.$refs.tabProceso.cargarDatos()
        } else if (this.tab === 'adelante' && this.$refs.tabAdelante) {
          this.$refs.tabAdelante.cargarDatos()
        }
      })
    }
  },
  watch: {
    tab() {
      this.cargarDatos()
    }
  }
}
</script>
