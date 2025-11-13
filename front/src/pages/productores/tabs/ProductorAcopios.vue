<template>
  <q-card-section>
    <div class="row items-center q-gutter-sm q-mb-sm">
      <div class="text-subtitle1">Acopios Proveedor</div>
      <q-space />
    </div>

    <!-- Tabla -->
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

    <!-- Dialog Crear/Editar -->
    <q-dialog v-model="dlg.open" persistent>
      <q-card style="min-width: 720px; max-width: 95vw">
        <q-card-section class="row items-center q-gutter-sm">
          <div class="text-h6">
            {{ form.id ? 'Editar runsa' : 'Nueva runsa' }}
          </div>
          <q-space />
          <q-btn flat round dense icon="close" v-close-popup />
        </q-card-section>
        <q-separator />

        <q-card-section>
          <q-form @submit="submit" class="row q-col-gutter-sm">
            <div class="col-12 col-sm-4">
              <q-input
                v-model="form.codigo"
                dense outlined label="Codigo"
                :rules="[v=>!!v || 'Requerido']"
              />
            </div>
            <div class="col-12 col-sm-8">
              <q-input
                v-model="form.subcodigo"
                dense outlined label="Subcodigo"
              />
            </div>

            <div class="col-6 col-sm-3">
              <q-input v-model="form.fecha_registro" type="date" dense outlined label="Fec Registro" />
            </div>
            <div class="col-6 col-sm-3">
              <q-input v-model="form.fecha_vencimiento" type="date" dense outlined label="Fec Vencimiento" />
            </div>

            <div class="col-12 col-sm-3">
              <q-select
                v-model="form.estado"
                :options="['VIGENTE','VENCIDO','SUSPENDIDO']"
                dense outlined label="Estado"
              />
            </div>

            <div class="col-12 text-right q-gutter-sm q-mt-sm">
              <q-btn flat color="grey" label="Cancelar" v-close-popup :disable="saving" />
              <q-btn color="primary" :label="form.id ? 'Guardar cambios' : 'Crear'"
                     type="submit" :loading="saving" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-card-section>
</template>

<script>
import moment from 'moment';


export default {
  name: 'ProductorAcopios',
  props: {
    productor: { type: Object, required: true }
  },
  emits: ['updated'],
  data () {
    return {
      loading: false,
      saving: false,
      list: [],
      dlg: { open: false },
      form: this.emptyForm(),
      acopioCosechas: []
    }
  },
    mounted () { this.hydrateFromProductor() },
  watch: { productor () { this.hydrateFromProductor() } },
  methods: {
        async fetchAcopio(){
      this.loading = true;
      console.log(this.productor);
      await this.$axios.post('/productorAcopios', 
         {
          productor_id: this.productor.id,
        
      })
      .then((response) => {
        //console.log(params)
        console.log(response);
        //return
        this.acopioCosechas = response.data;
      })
      .catch((error) => {
        console.log(error)
        this.$alert?.error?.(error.response?.data?.message || 'No se pudo cargar las cosechas');
      })
      .finally(() => {
        this.loading = false;
      });
    },
    hydrateFromProductor () {
       this.fetchAcopio()
    },
    async fetchRunsas () {
        console.log(this.productor)
      this.loading = true
      try {
        const { data } = await this.$axios.get('runsas', {
          params: { productor_id: this.productor.id, paginate: false }
        })
        console.log(data)   
        this.list = Array.isArray(data) ? data : (data?.data || [])
      } catch (e) {
        console.log(e)
        this.list = []
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo cargar runsas')
      } finally { this.loading = false }
    },
    emptyForm () {
      return {
        id: null,
        productor_id: this.productor?.id || null,
        codigo: null,
        subcodigo: '',
        fecha_registro: null,
        fecha_vencimiento: null,
        estado: 'VIGENTE'
      }
    },
    chip (estado) {
      if (estado === 'VIGENTE') return 'green'
      if (estado === 'VENCIDO') return 'red'
      if (estado === 'SUSPENDIDO') return 'orange'
      return 'grey'
    },

    startCreate () {
      this.form = this.emptyForm()
      this.form.productor_id = this.productor?.id || null
      this.dlg.open = true
    },
    startEdit (row) {
      this.form = {
        id: row.id,
        productor_id: this.productor?.id || row.productor_id,
        codigo: row.codigo || null,
        subcodigo: row.subcodigo || '',
        fecha_registro: row.fecha_registro || null,
        fecha_vencimiento: row.fecha_vencimiento || null,
        estado: row.estado || 'VIGENTE'
      }
      this.dlg.open = true
    },

    async submit () {
      if (!this.productor?.id) return
      this.saving = true
      try {
        const payload = { ...this.form, productor_id: this.productor.id }
        if (payload.id) {
          await this.$axios.put(`runsas/${payload.id}`, payload)
        } else {
          await this.$axios.post('runsas', payload)
        }
        this.$alert?.success?.(payload.id ? 'Runsa actualizada' : 'Runsa creada')
        this.dlg.open = false
        this.$emit('updated') // el padre vuelve a llamar GET /productores/{id}
      } catch (e) {
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo guardar')
      } finally {
        this.saving = false
      }
    },

    remove (row) {
      this.$alert?.dialog?.('¿Eliminar runsa?')?.onOk(async () => {
        try {
          await this.$axios.delete(`runsas/${row.id}`)
          this.$alert?.success?.('Eliminado')
          this.$emit('updated')
        } catch (e) {
          this.$alert?.error?.(e.response?.data?.message || 'No se pudo eliminar')
        }
      })
    },

  }
}
</script>
