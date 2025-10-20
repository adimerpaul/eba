<template>
  <q-page class="q-pa-md">
    <AcopioFormulario :estado="'crear'"/>
  </q-page>
</template>
<script>
import AcopioFormulario from "pages/acopio/AcopioFormulario.vue";
export default {
  name: 'AcopioCrear',
  components: {AcopioFormulario}
}
</script>
