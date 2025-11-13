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
                        <!-- PRODUCTOR (búsqueda) -->
          <div class="col-12 col-md-3">
            <q-select
              v-model="productor"
              :options="productorOptions"
              option-label="label"
              emit-value
              map-options
              use-input
              input-debounce="400"
              @filter="filterProductores"
              label="Productor"
              dense outlined
              :loading="loadingProductores"
              clearable
            >
              <template v-slot:no-option>
                <q-item>
                  <q-item-section class="text-grey">Escriba al menos 2 caracteres…</q-item-section>
                </q-item>
              </template>
            </q-select>
          </div>
                    <div class="col-6 col-md-3">
            <q-select
              v-model="producto"
              :options="productos.map(p => ({ label: p.nombre_producto, value: p.id }))"
              label="Producto"
              dense outlined
              emit-value
              map-options
            />
<!--            <pre>{{form.producto_id}}</pre>-->
          </div>
            <div class="col-12 col-md-3">
              <q-input
                v-model="numActa"
                label="Número de Acta"
                type="text"
                dense
                outlined
              />

            </div>
            <div class="col-12 col-md-2 flex flex-center">
              <q-btn
                :loading="loading"
                label="Buscar Cosechas"
                type="submit"
                color="primary"
                no-caps
                icon="search"
              />
              </div>
            <div class="col-12 col-md-2 flex flex-center">
              <q-btn
                :loading="loading"
                label="EXCEL"
                color="green"
                @click="generarExcel"
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
    }
  },
  mounted() {
    this.buscarCosechas();
    this.productoGet();

  },
  methods: {
        filterProductores (val, update, abort) {
      if (!val || val.length < 2) {
        update(() => { this.productorOptions = [] })
        return
      }
      update(async () => {
        this.loadingProductores = true
        try {
          const { data } = await this.$axios.get('/productores', {
            params: {
              search: val,
              per_page: 20
            }
          })
          const items = (data?.data || data || []).map(p => ({
            value: p.id,
            label: `${p.nombre ?? ''} ${p.apellidos ?? ''} — ${p.municipio?.nombre_municipio ?? ''}`.trim(),
            raw: p
          }))
          this.productorOptions = items
        } catch (e) {
          this.$q.notify({ type: 'negative', message: 'No se pudo cargar productores' })
        } finally {
          this.loadingProductores = false
        }
      })
    },

      async productoGet() {
        // agregar un registro vacio

      this.$axios.get('/productos/tipo/1').then(({ data }) => {
        console.log(data);
        this.productos = data?.data || data || []
        // un registro vacio al inicio
        this.productos.unshift({ id: null, nombre_producto: 'Todos' })
        this.producto = null;
      }).catch(() => {
        this.$q.notify({ type: 'negative', message: 'No se pudieron cargar los productos' })
      })
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
          fecha_inicio: this.fechaInicio,
          fecha_fin: this.fechaFin,
          estado: this.estadoSeleccionado,
          productor_id: this.productor,
          num_acta: this.numActa,
          producto_id: this.producto
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
