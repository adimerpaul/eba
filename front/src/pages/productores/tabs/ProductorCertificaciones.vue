<template>
  <q-card-section >
    <pre>{{productor}}</pre>
  </q-card-section>
</template>
<script>
export default {
  name: 'ProductorCertificaciones',
  props: {
    productor: {
      type: Object,
      required: true
    },
  },
  data () {
    return {
    }
  },
  methods: {
  }
}
</script>
