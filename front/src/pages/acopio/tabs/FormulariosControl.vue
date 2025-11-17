<template>
  <!-- COMPONENTE CREADO 2025-11-17 -->
  <!-- Contenedor de formularios de control del Senasag -->
  <!-- Agrupa los 3 tipos de formularios: Plagas, Limpieza y Medicamentos -->
  <!-- Este componente se utiliza en CosechaShow.vue para la tab "5) Formularios de Control" -->
  <!-- Reutiliza los componentes individuales de formularios manteniendo coherencia con ProductorAcopios.vue -->
  
  <q-card flat bordered>
    <q-card-section class="q-pb-none">
      <!-- Tabs para los 3 tipos de formularios de control -->
      <q-tabs 
        v-model="activeTab" 
        dense 
        class="text-grey" 
        active-color="primary" 
        indicator-color="primary" 
        align="left"
      >
        <q-tab name="plagas" icon="bug_report" label="Plagas" no-caps />
        <q-tab name="limpiezas" icon="cleaning_services" label="Limpiezas" no-caps />
        <q-tab name="medicamentos" icon="medication" label="Medicamentos" no-caps />
      </q-tabs>
    </q-card-section>

    <q-separator />

    <q-tab-panels v-model="activeTab" animated>
      <!-- Panel de Control de Plagas -->
      <!-- Formulario: Registro de control de plagas en colmenas -->
      <!-- MODIFICACION 2025-11-17: Se pasa prop readOnly a componente hijo -->
      <q-tab-panel name="plagas">
        <PlagasFormulario :cosecha="cosecha" :read-only="readOnly" />
      </q-tab-panel>

      <!-- Panel de Limpieza y Desinfeccion -->
      <!-- Formulario: Registro de limpieza y desinfeccion de equipos y herramientas apicolas -->
      <!-- MODIFICACION 2025-11-17: Se pasa prop readOnly a componente hijo -->
      <q-tab-panel name="limpiezas">
        <LimpiezasFormulario :cosecha="cosecha" :read-only="readOnly" />
      </q-tab-panel>

      <!-- Panel de Aplicacion de Medicamentos -->
      <!-- Formulario: Registro de aplicacion de medicamentos -->
      <!-- MODIFICACION 2025-11-17: Se pasa prop readOnly a componente hijo -->
      <q-tab-panel name="medicamentos">
        <MedicamentosFormulario :cosecha="cosecha" :read-only="readOnly" />
      </q-tab-panel>
    </q-tab-panels>
  </q-card>
</template>

<script>
// Importacion de componentes de formularios de control
// Estos componentes se reutilizan desde diferentes vistas para mantener coherencia
import PlagasFormulario from './PlagasFormulario.vue'
import LimpiezasFormulario from './LimpiezasFormulario.vue'
import MedicamentosFormulario from './MedicamentosFormulario.vue'

export default {
  name: 'FormulariosControl',
  components: {
    PlagasFormulario,
    LimpiezasFormulario,
    MedicamentosFormulario
  },
  props: {
    // Objeto cosecha/acopio que contiene el acopio_cosecha_id
    // Este prop es requerido para que los formularios puedan registrar datos
    cosecha: { 
      type: Object, 
      required: true 
    },
    // MODIFICACION 2025-11-17: Prop para activar modo solo lectura
    // Cuando readOnly=true, los formularios hijos muestran solo consulta + impresion
    // Cuando readOnly=false, los formularios hijos permiten crear/editar/eliminar
    readOnly: { 
      type: Boolean, 
      default: false 
    }
  },
  data () {
    return {
      // Tab activa por defecto
      activeTab: 'plagas'
    }
  }
}
</script>
