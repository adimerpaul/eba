<template>
  <q-page class="flex flex-center">
<!--    <img-->
<!--      alt="Quasar logo"-->
<!--      src="~assets/quasar-logo-vertical.svg"-->
<!--      style="width: 200px; height: 200px"-->
<!--    >-->
<!--    descargar de public EbaAppV1.0.1.apk-->
    <a href="EbaAppV1.0.1.apk" download>
      <q-btn color="primary" label="Descargar EbaApp" />
    </a>
  </q-page>
</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'IndexPage'
});
</script>
