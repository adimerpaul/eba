<template>
  <q-page class="q-pa-md">
    <div class="row items-center q-gutter-sm q-mb-md">
      <q-btn flat round icon="arrow_back" @click="$router.back()" />
      <div class="text-h6">Editar Productor</div>
      <q-space />
      <q-btn flat round icon="refresh" :loading="loading" @click="fetchProductor" />
    </div>

    <q-card flat bordered>
      <q-inner-loading :showing="loading">
        <q-spinner />
      </q-inner-loading>

      <q-card-section v-if="!loading && productor">
        <ProductorForm
          :productor="productor"
          banner-msg="Actualiza los datos y guarda los cambios"
          @saved="onSaved"
          @cancel="$router.back()"
        />
      </q-card-section>

      <q-card-section v-if="!loading && !productor" class="text-grey">
        No se encontró el productor.
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script>

import ProductorForm from "pages/productores/ProductorForm.vue";

export default {
  name: 'ProductorEditar',
  components: {ProductorForm},
  data () {
    return {
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
      // Si quieres permanecer en la misma vista, refresca los datos:
      this.productor = saved
      this.$alert?.success?.('Cambios guardados')
      // O redirige:
      // this.$router.push({ name: 'productores.index' })
    }
  }
}
</script>
