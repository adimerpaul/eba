<template>
  <!-- MODIFICACION 2025-11-17: Simplificacion del componente Documentos.vue -->
  <!-- Anteriormente contenia sub-tabs para plagas, limpiezas y medicamentos -->
  <!-- Ahora solo muestra el editor de documentos HTML (DocumentosOriginal.vue) -->
  <!-- Los formularios de control se movieron a una tab separada en CosechaShow.vue -->
  <!-- y estan disponibles desde ProductorAcopios.vue mediante un dialog -->
  
  <!-- Contenedor principal sin sub-tabs, solo el contenido de documentos -->
  <q-card flat bordered>
    <DocumentosOriginal :cosecha="cosecha" />
  </q-card>
  
  <!-- CODIGO ANTERIOR COMENTADO (2025-11-17) -->
  <!-- Se eliminaron las sub-tabs de plagas, limpiezas y medicamentos -->
  <!--
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
      <q-tab-panel name="documentos">
        <DocumentosOriginal :cosecha="cosecha" />
      </q-tab-panel>

      <q-tab-panel name="plagas">
        <PlagasFormulario :cosecha="cosecha" />
      </q-tab-panel>

      <q-tab-panel name="limpiezas">
        <LimpiezasFormulario :cosecha="cosecha" />
      </q-tab-panel>

      <q-tab-panel name="medicamentos">
        <MedicamentosFormulario :cosecha="cosecha" />
      </q-tab-panel>
    </q-tab-panels>
  </q-card>
  -->
</template>

<script>
// MODIFICACION 2025-11-17: Importacion de componentes de formularios eliminada
// Solo se mantiene DocumentosOriginal
import DocumentosOriginal from './DocumentosOriginal.vue'

// IMPORTS ANTERIORES COMENTADOS (2025-11-17)
// import PlagasFormulario from './PlagasFormulario.vue'
// import LimpiezasFormulario from './LimpiezasFormulario.vue'
// import MedicamentosFormulario from './MedicamentosFormulario.vue'

export default {
  name: 'Documentos',
  components: {
    DocumentosOriginal
    // COMPONENTES ANTERIORES COMENTADOS (2025-11-17)
    // PlagasFormulario,
    // LimpiezasFormulario,
    // MedicamentosFormulario
  },
  props: {
    cosecha: { type: Object, required: true }
  }
  // DATA ANTERIOR COMENTADO (2025-11-17)
  // Ya no se necesita el estado de subTab
  // data () {
  //   return {
  //     subTab: 'documentos'
  //   }
  // }
}
</script>
