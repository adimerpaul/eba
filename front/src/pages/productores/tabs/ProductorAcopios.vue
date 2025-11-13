<template>
      <q-card-section>
        <div class="text-h6">Acopio de Cosechas</div>
      </q-card-section>
      <q-separator />
        <q-markup-table v-if="acopioCosechas.length > 0" dense wrap-cells flat bordered>
          <thead>
            <tr class="bg-primary text-white">
              <th>Opciones</th>
              <th>Fecha Cosecha</th>
              <th>Productor</th>
              <th>Cantidad (kg)</th>
              <th>Humedad (%)</th>
              <th>Temperatura Almacenaje (°C)</th>
              <th>Número Acta</th>
              <th>Condiciones Almacenaje</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="cosecha in acopioCosechas" :key="cosecha.id">
              <td>
                <q-btn-dropdown dense label="Opciones" color="primary" no-caps size="10px">
                  <q-list>
                    <q-item clickable v-ripple :to="`/acopio/cosechas/${cosecha.id}`">
                      <q-item-section avatar>
                        <q-icon name="visibility" />
                      </q-item-section>
                      <q-item-section>Ver Detalles</q-item-section>
                    </q-item>
                  </q-list>
                </q-btn-dropdown>
              </td>
              <td>{{ cosecha.fecha_cosecha }}</td>
              <td>
                {{ cosecha.apiario?.productor.nombre }}
                {{ cosecha.apiario?.productor.apellidos }}
              </td>
              <td>{{ cosecha.cantidad_kg }}</td>
              <td>{{ cosecha.humedad }}</td>
              <td>{{ cosecha.temperatura_almacenaje }}</td>
              <td>{{ cosecha.num_acta }}</td>
              <td>{{ cosecha.condiciones_almacenaje }}</td>
              <td>
                <q-chip :color="cosecha.estado === 'BUENO' ? 'green' : 'red'" text-color="white" dense size="10px">
                  {{ cosecha.estado.replace('_', ' ') }}
                </q-chip>
              </td>
            </tr>
          </tbody>
        </q-markup-table>
        <div v-else class="text-center q-pa-md">
          <q-icon name="info" size="48px" color="grey-5" />
          <div class="text-subtitle2 text-grey-5">No se encontraron cosechas.</div>
        </div>
    </div>
</template>
<script>
import moment from "moment";

export default {
  name: 'ProductorAcopioPage',
    props: { productor: { type: Object, required: true } },
  emits: ['updated'],
  data: function () {
    return {
      numActa: null,
      acopioCosechas:[],
      loadingProductores: false,
      productor: null,
      productorOptions: [],
      productos: [],
      producto: null,
      loading: false,
      fechaInicio: moment().startOf('month').format('YYYY-MM-DD'),
      fechaFin: moment().endOf('month').format('YYYY-MM-DD'),
      estados: [
        { label: 'BUENO', value: 'BUENO' },
        { label: 'EN PROCESO', value: 'EN_PROCESO' },
        { label: 'CANCELADO', value: 'CANCELADO' },
      ],
      estadoSeleccionado: null,
      departamentos: [],
      departamentoSeleccionado: null,
      provincias: [],
      provinciaSeleccionado: null,
      municipios: [],
      municipioSeleccionado: null
    }
  },
  mounted () { this.hydrateFromProductor()
    this.buscarCosechas();

   },
  watch: { productor () { this.hydrateFromProductor() } },

  methods: {
        hydrateFromProductor () {
      if (this.productor?.apiarios) this.list = this.productor.apiarios
      else this.fetchApiarios()
    },
    async generarExcel() {
      this.loading = true;
      await this.$axios.post('acopioExcel',{fecha_inicio: this.fechaInicio,
          fecha_fin: this.fechaFin,
          estado: this.estadoSeleccionado,}, {
        responseType: 'blob' // 👈 importante para manejar archivos binarios
      })
      .then((res) => {
        // Crear blob y enlace de descarga
        const blob = new Blob([res.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
        const url = window.URL.createObjectURL(blob)
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', 'acopio.xlsx') // nombre del archivo
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
        window.URL.revokeObjectURL(url)
      }).catch((e) => {
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo generar el reporte.')
      }).finally(() => {
        this.loading = false
      })
    },
    buscarCosechas(){
      this.loading = true;
      console.log(this.producto);
      this.$axios.get('/acopio/cosechas', {
        params: {
          fecha_inicio: '0001-01-01',
          fecha_fin: this.fechaFin,
          estado: null,
          productor_id: null,
          num_acta: null,
          producto_id: null,
          departamento_id: null,
          municipio_id: null
        }
      })
      .then((response) => {
        console.log(response.data);
       // return
        this.acopioCosechas = response.data;
      }).finally(() => {
        this.loading = false;
      });
    }
  }
}
</script>
