<template>
  <!-- Contenedor principal con sub-tabs para los 4 tipos de formularios -->
  <q-card flat bordered>
    <q-card-section class="q-pb-none">
      <q-tabs v-model="subTab" dense class="text-grey" active-color="primary" indicator-color="primary" align="left">
        <q-tab name="documentos" label="Documentos" no-caps />
        <q-tab name="plagas" icon="bug_report" label="Plagas" no-caps />
        <q-tab name="limpiezas" icon="cleaning_services" label="Limpiezas" no-caps />
        <q-tab name="medicamentos" icon="medication" label="Medicamentos" no-caps />
      </q-tabs>
    </q-card-section>

    <q-separator />

    <q-tab-panels v-model="subTab" animated>
      <!-- Panel original de Documentos con editor HTML -->
      <q-tab-panel name="documentos">
        <DocumentosOriginal :cosecha="cosecha" />
      </q-tab-panel>

      <!-- Panel de Control de Plagas -->
      <q-tab-panel name="plagas">
        <PlagasFormulario :cosecha="cosecha" />
      </q-tab-panel>

      <!-- Panel de Limpieza y Desinfección -->
      <q-tab-panel name="limpiezas">
        <LimpiezasFormulario :cosecha="cosecha" />
      </q-tab-panel>

      <!-- Panel de Aplicación de Medicamentos -->
      <q-tab-panel name="medicamentos">
        <MedicamentosFormulario :cosecha="cosecha" />
      </q-tab-panel>
    </q-tab-panels>
  </q-card>
</template>

<script>
// Importación de componentes para sub-tabs
import DocumentosOriginal from './DocumentosOriginal.vue'
import PlagasFormulario from './PlagasFormulario.vue'
import LimpiezasFormulario from './LimpiezasFormulario.vue'
import MedicamentosFormulario from './MedicamentosFormulario.vue'

export default {
  name: 'Documentos',
  components: {
    DocumentosOriginal,
    PlagasFormulario,
    LimpiezasFormulario,
    MedicamentosFormulario
  },
  props: {
    cosecha: { type: Object, required: true }
  },
  data () {
    return {
      subTab: 'documentos'
    }
  }
}
</script>
