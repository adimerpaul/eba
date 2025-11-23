<template>
  <q-page class="q-pa-md">
    <div class="row items-center q-gutter-sm q-mb-md">
      <q-btn flat round icon="arrow_back" @click="$router.push('/control-procesos')"/>
      <div class="text-h6">Proceso Productivo</div>
      <q-space/>
      <q-btn flat round icon="refresh" :loading="loading" @click="cargarProceso"/>
    </div>

    <q-card v-if="proceso" flat bordered>
      <q-card-section>
        <div class="row q-col-gutter-md">
          <div class="col-12 col-md-6">
            <q-list>
              <q-item>
                <q-item-section>
                  <q-item-label caption>Estado</q-item-label>
                  <q-item-label>
                    <q-badge :color="proceso.estado === 'EN_PROCESO' ? 'warning' : 'positive'">
                      {{ proceso.estado }}
                    </q-badge>
                  </q-item-label>
                </q-item-section>
              </q-item>
              <q-item>
                <q-item-section>
                  <q-item-label caption>Fecha de Proceso</q-item-label>
                  <q-item-label>{{ formatFecha(proceso.fecha_proceso) }}</q-item-label>
                </q-item-section>
              </q-item>
              <q-item>
                <q-item-section>
                  <q-item-label caption>Tanque</q-item-label>
                  <q-item-label>{{ proceso.tanque?.nombre_tanque || 'N/A' }}</q-item-label>
                </q-item-section>
              </q-item>
              <q-item>
                <q-item-section>
                  <q-item-label caption>Planta</q-item-label>
                  <q-item-label>{{ proceso.tanque?.planta?.nombre_planta || 'N/A' }}</q-item-label>
                </q-item-section>
              </q-item>
              <q-item>
                <q-item-section>
                  <q-item-label caption>Operador</q-item-label>
                  <q-item-label>{{ proceso.user?.username || 'N/A' }}</q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </div>
          <div class="col-12 col-md-6">
            <q-list>
              <q-item>
                <q-item-section>
                  <q-item-label caption>Cantidad Entrada</q-item-label>
                  <q-item-label>{{ proceso.cantidad_entrada_kg }} kg</q-item-label>
                </q-item-section>
              </q-item>
              <q-item v-if="proceso.cantidad_salida_kg">
                <q-item-section>
                  <q-item-label caption>Cantidad Salida</q-item-label>
                  <q-item-label>{{ proceso.cantidad_salida_kg }} kg</q-item-label>
                </q-item-section>
              </q-item>
              <q-item v-if="proceso.merma_kg">
                <q-item-section>
                  <q-item-label caption>Merma</q-item-label>
                  <q-item-label>{{ proceso.merma_kg }} kg ({{ proceso.merma_porcentaje }}%)</q-item-label>
                </q-item-section>
              </q-item>
              <q-item v-if="proceso.temperatura_proceso">
                <q-item-section>
                  <q-item-label caption>Temperatura de Proceso</q-item-label>
                  <q-item-label>{{ proceso.temperatura_proceso }} C</q-item-label>
                </q-item-section>
              </q-item>
              <q-item v-if="proceso.tiempo_proceso_horas">
                <q-item-section>
                  <q-item-label caption>Tiempo de Proceso</q-item-label>
                  <q-item-label>{{ proceso.tiempo_proceso_horas }} horas</q-item-label>
                </q-item-section>
              </q-item>
              <q-item v-if="proceso.metodo_proceso">
                <q-item-section>
                  <q-item-label caption>Metodo</q-item-label>
                  <q-item-label>{{ proceso.metodo_proceso }}</q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </div>
        </div>
        <q-item v-if="proceso.observaciones">
          <q-item-section>
            <q-item-label caption>Observaciones</q-item-label>
            <q-item-label>{{ proceso.observaciones }}</q-item-label>
          </q-item-section>
        </q-item>
      </q-card-section>

      <q-separator />

      <q-card-section>
        <div class="text-h6 q-mb-md">Acopios Procesados</div>
        <q-table
          :rows="proceso.acopios || []"
          :columns="columnasAcopios"
          row-key="id"
          flat
          :pagination="{ rowsPerPage: 0 }"
          hide-pagination
        >
          <template v-slot:body-cell-productor="props">
            <q-td :props="props">
              {{ props.row.apiario?.productor?.nombre_completo || 'N/A' }}
            </q-td>
          </template>
        </q-table>
      </q-card-section>

      <q-separator />

      <q-card-section>
        <div class="text-h6 q-mb-md">Lotes Generados</div>
        <q-table
          :rows="proceso.lotes || []"
          :columns="columnasLotes"
          row-key="id"
          flat
          :pagination="{ rowsPerPage: 0 }"
          hide-pagination
        >
          <template v-slot:body-cell-qr="props">
            <q-td :props="props">
              <q-btn flat dense icon="qr_code" color="primary" @click="verQR(props.row)" size="sm">
                <q-tooltip>Ver QR</q-tooltip>
              </q-btn>
            </q-td>
          </template>
        </q-table>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script>
export default {
  name: 'ProcesoDetalle',
  data() {
    return {
      proceso: null,
      loading: false,
      columnasAcopios: [
        { name: 'id', label: 'ID', field: 'id', align: 'left' },
        { name: 'fecha', label: 'Fecha', field: 'fecha_cosecha', align: 'left' },
        { name: 'productor', label: 'Productor', field: 'productor', align: 'left' },
        { name: 'cantidad', label: 'Cantidad (kg)', field: row => row.pivot?.cantidad_kg || row.cantidad_kg, 
          align: 'right', format: val => Number(val).toFixed(2) }
      ],
      columnasLotes: [
        { name: 'codigo', label: 'Codigo Lote', field: 'codigo_lote', align: 'left' },
        { name: 'producto', label: 'Producto', field: row => row.producto?.nombre_producto || '', align: 'left' },
        { name: 'cantidad', label: 'Cantidad (kg)', field: 'cantidad_kg', align: 'right', 
          format: val => Number(val).toFixed(2) },
        { name: 'fecha_envasado', label: 'Fecha Envasado', field: 'fecha_envasado', align: 'left' },
        { name: 'qr', label: 'QR', field: 'qr', align: 'center' }
      ]
    }
  },
  mounted() {
    this.cargarProceso()
  },
  methods: {
    async cargarProceso() {
      this.loading = true
      try {
        const { data } = await this.$axios.get(`/control-procesos/${this.$route.params.id}`)
        this.proceso = data
      } catch (e) {
        this.$q.notify({ type: 'negative', message: 'Error al cargar proceso' })
        this.$router.push('/control-procesos')
      } finally {
        this.loading = false
      }
    },
    formatFecha(fecha) {
      return fecha ? new Date(fecha).toLocaleString() : ''
    },
    verQR(lote) {
      window.open(`/qr/${lote.codigo_lote}`, '_blank')
    }
  }
}
</script>
