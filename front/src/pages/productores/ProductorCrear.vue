<template>
  <q-page class="q-pa-md">
    <div class="row items-center q-gutter-sm q-mb-md">
      <q-btn flat round icon="arrow_back" @click="$router.push('/productores')" />
      <div class="text-h6">Nuevo Productor</div>
    </div>

    <q-card flat bordered>
      <q-card-section>
        <!-- No pasamos 'productor' → el form crea desde cero -->
        <ProductorForm banner-msg="Completa los datos para registrar un nuevo productor" @saved="onSaved"
          @cancel="$router.back()" />
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script>

import ProductorForm from "pages/productores/ProductorForm.vue";

export default {
  name: 'ProductorCrear',
  components: { ProductorForm },
  methods: {
    onSaved(saved) {
      // Redirige como prefieras:
      // 1) a la lista
      // this.$router.push({ name: 'productores.index' })
      // 2) o a la página de edición del recién creado
      this.$router.push({ name: 'productores.editar', params: { id: saved.id } })
    }
  }
}
</script>
