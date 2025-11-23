<template>
  <q-card-section>
    <div class="text-h6 q-mb-md">Procesos Asociados a esta Cosecha</div>
    
    <q-table
      :rows="procesos"
      :columns="columns"
      row-key="id"
      :loading="loading"
      flat
      :pagination="{ rowsPerPage: 0 }"
      hide-pagination
    >
      <template v-slot:body-cell-estado="props">
        <q-td :props="props">
          <q-badge :color="props.row.estado === 'EN_PROCESO' ? 'warning' : 'positive'">
            {{ props.row.estado }}
          </q-badge>
        </q-td>
      </template>

      <template v-slot:body-cell-acciones="props">
        <q-td :props="props">
          <q-btn flat dense icon="visibility" color="primary" @click="verDetalle(props.row)" size="sm">
            <q-tooltip>Ver detalle</q-tooltip>
          </q-btn>
        </q-td>
      </template>

      <template v-slot:no-data>
        <div class="full-width row flex-center text-grey q-pa-md">
          <span>Esta cosecha no tiene procesos asociados aun</span>
        </div>
      </template>
    </q-table>
  </q-card-section>
</template>

<script>
export default {
  name: 'ProcesoTab',
  props: {
    cosecha: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      procesos: [],
      loading: false,
      columns: [
        { name: 'id', label: 'ID', field: 'id', align: 'left' },
        { name: 'fecha', label: 'Fecha', field: 'fecha_proceso', align: 'left', 
          format: val => val ? new Date(val).toLocaleDateString() : '' },
        { name: 'tanque', label: 'Tanque', field: row => row.tanque?.nombre_tanque || '', align: 'left' },
        { name: 'entrada', label: 'Entrada (kg)', field: 'cantidad_entrada_kg', align: 'right', 
          format: val => val ? Number(val).toFixed(2) : '0.00' },
        { name: 'salida', label: 'Salida (kg)', field: 'cantidad_salida_kg', align: 'right', 
          format: val => val ? Number(val).toFixed(2) : '0.00' },
        { name: 'estado', label: 'Estado', field: 'estado', align: 'center' },
        { name: 'acciones', label: 'Acciones', field: 'acciones', align: 'center' }
      ]
    }
  },
  mounted() {
    this.cargarProcesos()
  },
  methods: {
    async cargarProcesos() {
      this.loading = true
      try {
        const { data } = await this.$axios.get('/control-procesos', {
          params: { cosecha_id: this.cosecha.id }
        })
        this.procesos = data.data || []
      } catch (e) {
        this.$q.notify({ type: 'negative', message: 'Error al cargar procesos' })
      } finally {
        this.loading = false
      }
    },
    verDetalle(proceso) {
      this.$router.push(`/control-procesos/${proceso.id}`)
    }
  }
}
</script>
