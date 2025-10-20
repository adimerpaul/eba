<template>
  <q-card flat bordered>
    <q-card-section class="row items-center justify-between">
      <div class="text-h6">Registrar Acopio</div>
      <q-badge color="primary" outline v-if="estado === 'crear'">Nuevo</q-badge>
    </q-card-section>

    <q-separator />

    <q-card-section>
      <q-form @submit.prevent="onSubmit" ref="formRef">
        <div class="row q-col-gutter-md">

          <!-- Fecha de cosecha -->
          <div class="col-12 col-md-3">
            <q-input
              v-model="form.fecha_cosecha"
              type="date"
              label="Fecha de Cosecha"
              dense outlined
              :rules="[val => !!val || 'Requerido']"
            />
          </div>

          <!-- Apiario (productor) -->
          <div class="col-12 col-md-6">
            <q-select
              v-model="form.apiario"
              :options="apiarioOptions"
              option-label="label"
              option-value="value"
              emit-value
              map-options
              use-input
              input-debounce="400"
              @filter="filterApiarios"
              label="Apiario / Productor"
              dense outlined
              :loading="loadingApiarios"
              clearable
              :rules="[val => !!val || 'Seleccione un apiario']"
            >
              <template #no-option>
                <q-item><q-item-section class="text-grey">Sin resultados</q-item-section></q-item>
              </template>
            </q-select>
            <div class="text-caption text-grey-7 q-mt-xs" v-if="form.apiario_detail">
              <q-icon name="place" size="16px" class="q-mr-xs" />
              {{ form.apiario_detail?.municipio?.nombre_municipio || '—' }}
            </div>
          </div>

          <!-- Cantidad KG -->
          <div class="col-6 col-md-3">
            <q-input
              v-model.number="form.cantidad_kg"
              type="number"
              min="0"
              step="0.01"
              label="Cantidad (kg)"
              dense outlined
              :rules="[v => v > 0 || 'Mayor a 0']"
              suffix="kg"
            />
          </div>

          <!-- Humedad -->
          <div class="col-6 col-md-3">
            <q-input
              v-model.number="form.humedad"
              type="number"
              min="0"
              step="0.01"
              label="Humedad (%)"
              dense outlined
              suffix="%"
            />
          </div>

          <!-- Temperatura -->
          <div class="col-6 col-md-3">
            <q-input
              v-model.number="form.temperatura_almacenaje"
              type="number"
              min="-50"
              step="0.01"
              label="Temperatura Almacenaje (°C)"
              dense outlined
              suffix="°C"
            />
          </div>

          <!-- Número acta -->
<!--          <div class="col-6 col-md-3">-->
<!--            <q-input-->
<!--              v-model="form.num_acta"-->
<!--              label="Número de Acta"-->
<!--              dense outlined-->
<!--            />-->
<!--          </div>-->

          <!-- Condiciones -->
          <div class="col-12 col-md-6">
            <q-input
              v-model="form.condiciones_almacenaje"
              label="Condiciones de Almacenaje"
              dense outlined
              autogrow
            />
          </div>

          <!-- Estado -->
          <div class="col-12 col-md-3">
            <q-select
              v-model="form.estado"
              :options="estados"
              label="Estado"
              dense outlined
              emit-value map-options
              :rules="[v => !!v || 'Requerido']"
            />
          </div>
        </div>

        <div class="row q-gutter-sm q-mt-md">
          <q-btn type="submit" color="primary" icon="save" :loading="loading" no-caps label="Guardar" />
          <q-btn type="reset" color="grey-7" flat icon="history" no-caps label="Limpiar" />
        </div>
      </q-form>
    </q-card-section>
  </q-card>
</template>

<script>
export default {
  name: 'AcopioFormulario',
  props: {
    estado: {
      type: String,
      required: true,
      validator: val => ['crear', 'editar'].includes(val)
    }
  },
  data() {
    return {
      form: {
        fecha_cosecha: '',
        apiario: null,
        apiario_detail: null,
        cantidad_kg: null,
        humedad: null,
        temperatura_almacenaje: null,
        num_acta: '',
        condiciones_almacenaje: '',
        estado: ''
      },
      apiarioOptions: [],
      loadingApiarios: false,
      estados: ['BUENO', 'REGULAR', 'MALO'],
      loading: false
    };
  },
  methods: {
    filterApiarios(val, update) {
      if (val.length < 2) {
        this.apiarioOptions = [];
        return;
      }
      this.loadingApiarios = true;
      // Simulate API call
      setTimeout(() => {
        this.apiarioOptions = [
          { label: 'Apiario 1', value: 1 },
          { label: 'Apiario 2', value: 2 }
        ].filter(item => item.label.toLowerCase().includes(val.toLowerCase()));
        this.loadingApiarios = false;
      }, 500);
    },
    onSubmit() {
      this.$refs.formRef.validate().then(valid => {
        if (valid) {
          this.loading = true;
          // Simulate form submission
          setTimeout(() => {
            this.loading = false;
            this.$q.notify({ type: 'positive', message: 'Acopio guardado exitosamente' });
          }, 1000);
        }
      });
    }
  }
}
</script>
