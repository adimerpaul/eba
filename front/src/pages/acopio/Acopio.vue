<template>
  <q-page class="q-pa-xs">
    <q-card>
      <q-card-section>
        <div class="text-h6">Acopio de Cosechas</div>
      </q-card-section>
      <q-separator />
      <q-card-section>
        <q-form @submit.prevent="buscarCosechas">
          <div class="row q-gutter-sm">
            <div class="col-12 col-md-2">
              <q-input
                v-model="fechaInicio"
                label="Fecha Inicio"
                type="date"
                dense
                outlined
              />
            </div>
            <div class="col-12 col-md-2">
              <q-input
                v-model="fechaFin"
                label="Fecha Fin"
                type="date"
                dense
                outlined
              />
            </div>
<!--            estados-->
            <div class="col-12 col-md-3">
              <q-select
                v-model="estadoSeleccionado"
                :options="estados"
                label="Estado de Cosecha"
                dense
                outlined
                clearable
              />
            </div>
            <div class="col-12 col-md-3 flex flex-center">
              <q-btn
                :loading="loading"
                label="Buscar Cosechas"
                type="submit"
                color="primary"
                class="full-width"
                no-caps
                icon="search"
              />
            </div>
          </div>
        </q-form>
<!--        [-->
<!--        {-->
<!--        "id": 8809,-->
<!--        "fecha_cosecha": "2025-07-31",-->
<!--        "id_apiario": 168,-->
<!--        "cantidad_kg": "136.25",-->
<!--        "humedad": "0.00",-->
<!--        "temperatura_almacenaje": "0.00",-->
<!--        "num_acta": "0",-->
<!--        "condiciones_almacenaje": "Normales",-->
<!--        "estado": "BUENO"-->
<!--        },-->
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
                    <q-item clickable v-ripple @click="() => {}">
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
                <q-chip :color="cosecha.estado === 'BUENO' ? 'green' : (cosecha.estado === 'EN_PROCESO' ? 'orange' : 'red')" text-color="white" dense size="10px">
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
      </q-card-section>
    </q-card>
  </q-page>
</template>
<script>
import moment from "moment";

export default {
  name: 'AcopioPage',
  data: function () {
    return {
      acopioCosechas:[],
      loading: false,
      fechaInicio: moment().startOf('month').format('YYYY-MM-DD'),
      fechaFin: moment().endOf('month').format('YYYY-MM-DD'),
      estados: [
        { label: 'BUENO', value: 'BUENO' },
        { label: 'EN PROCESO', value: 'EN_PROCESO' },
        { label: 'CANCELADO', value: 'CANCELADO' },
      ],
      estadoSeleccionado: null,
    }
  },
  mounted() {
    this.buscarCosechas();
  },
  methods: {
    buscarCosechas(){
      this.loading = true;
      this.$axios.get('/acopio/cosechas', {
        params: {
          fecha_inicio: this.fechaInicio,
          fecha_fin: this.fechaFin,
          estado: this.estadoSeleccionado,
        }
      })
      .then((response) => {
        this.acopioCosechas = response.data;
      }).finally(() => {
        this.loading = false;
      });
    }
  }
}
</script>
