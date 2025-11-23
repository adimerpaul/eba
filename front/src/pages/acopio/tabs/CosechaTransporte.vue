<template>
  <div class="q-pa-md">
    <q-card>
      <q-card-section>
        <div class="row items-center">
          <div class="col">
            <div class="text-h6">Control de Transporte</div>
            <div class="text-caption text-grey-7">
              Registro de transporte desde apiario - Requisito SENASAG
            </div>
          </div>
          <div class="col-auto">
            <q-btn
              color="primary"
              icon="add"
              label="Registrar Transporte"
              @click="abrirFormulario(null)"
              :disable="!cosecha?.id"
            />
          </div>
        </div>
      </q-card-section>

      <q-separator />

      <q-card-section v-if="cargando" class="text-center">
        <q-spinner color="primary" size="50px" />
        <div class="text-caption q-mt-md">Cargando registros...</div>
      </q-card-section>

      <q-card-section v-else-if="registros.length === 0" class="text-center text-grey-7">
        <q-icon name="local_shipping" size="64px" class="q-mb-md" />
        <div>No hay registros de transporte para este acopio</div>
        <div class="text-caption">Registre la información del transporte desde el apiario</div>
      </q-card-section>

      <q-list v-else separator>
        <q-item v-for="registro in registros" :key="registro.id" clickable @click="abrirFormulario(registro)">
          <q-item-section avatar>
            <q-avatar :color="getColorEstado(registro.estado_cumplimiento)" text-color="white" icon="local_shipping" />
          </q-item-section>

          <q-item-section>
            <q-item-label>
              {{ registro.transporte?.empresa || 'Sin empresa' }} - {{ registro.transporte?.placa || 'Sin placa' }}
            </q-item-label>
            <q-item-label caption>
              {{ registro.lugar_origen }} → {{ registro.lugar_destino }}
            </q-item-label>
            <q-item-label caption class="q-mt-xs">
              <q-chip size="sm" dense :color="registro.alerta_temperatura ? 'red' : 'green'" text-color="white">
                Temp: {{ formatTemperatura(registro.temperatura_llegada) }}
              </q-chip>
              <q-chip size="sm" dense :color="registro.alerta_tiempo ? 'red' : 'green'" text-color="white" class="q-ml-xs">
                {{ registro.tiempo_transporte_horas || 0 }} hrs
              </q-chip>
            </q-item-label>
          </q-item-section>

          <q-item-section side>
            <div class="text-caption text-grey-7">
              {{ formatFecha(registro.fecha_hora_salida) }}
            </div>
            <q-btn
              flat
              round
              dense
              color="negative"
              icon="delete"
              size="sm"
              @click.stop="confirmarEliminar(registro)"
            />
          </q-item-section>
        </q-item>
      </q-list>
    </q-card>

    <q-dialog v-model="dialogo" persistent>
      <q-card style="min-width: 600px">
        <q-card-section class="row items-center">
          <div class="text-h6">{{ modoEdicion ? 'Editar' : 'Registrar' }} Transporte</div>
          <q-space />
          <q-btn icon="close" flat round dense @click="cerrarFormulario" />
        </q-card-section>

        <q-separator />

        <q-card-section class="q-pt-none" style="max-height: 70vh; overflow-y: auto">
          <q-form @submit.prevent="guardar" ref="formRef">
            <div class="row q-col-gutter-md q-mt-sm">
              <div class="col-12">
                <q-select
                  v-model="form.transporte_id"
                  :options="transportes"
                  option-value="id"
                  :option-label="opt => `${opt.empresa || 'Sin empresa'} - ${opt.placa || 'Sin placa'}`"
                  emit-value
                  map-options
                  label="Transporte *"
                  filled
                  dense
                  :rules="[val => val > 0 || 'Seleccione un transporte']"
                />
              </div>

              <div class="col-12 col-md-6">
                <q-input
                  v-model="form.lugar_origen"
                  label="Lugar de Origen *"
                  filled
                  dense
                  :rules="[val => !!val || 'Campo requerido']"
                />
              </div>

              <div class="col-12 col-md-6">
                <q-input
                  v-model="form.lugar_destino"
                  label="Lugar de Destino"
                  filled
                  dense
                />
              </div>

              <div class="col-12 col-md-4">
                <q-input
                  v-model.number="form.distancia_km"
                  type="number"
                  step="0.01"
                  label="Distancia (km)"
                  filled
                  dense
                />
              </div>

              <div class="col-12 col-md-4">
                <q-input
                  v-model="form.fecha_hora_salida"
                  type="datetime-local"
                  label="Fecha/Hora Salida"
                  filled
                  dense
                />
              </div>

              <div class="col-12 col-md-4">
                <q-input
                  v-model="form.fecha_hora_llegada"
                  type="datetime-local"
                  label="Fecha/Hora Llegada"
                  filled
                  dense
                />
              </div>

              <div class="col-12">
                <div class="text-subtitle2 q-mb-sm">Control de Temperatura (°C)</div>
              </div>

              <div class="col-6 col-md-3">
                <q-input
                  v-model.number="form.temperatura_salida"
                  type="number"
                  step="0.1"
                  label="Salida"
                  filled
                  dense
                  hint="Temperatura al inicio"
                />
              </div>

              <div class="col-6 col-md-3">
                <q-input
                  v-model.number="form.temperatura_llegada"
                  type="number"
                  step="0.1"
                  label="Llegada"
                  filled
                  dense
                  hint="Temperatura al final"
                />
              </div>

              <div class="col-6 col-md-3">
                <q-input
                  v-model.number="form.temperatura_maxima"
                  type="number"
                  step="0.1"
                  label="Máxima"
                  filled
                  dense
                  hint="Máx registrada"
                />
              </div>

              <div class="col-6 col-md-3">
                <q-input
                  v-model.number="form.temperatura_minima"
                  type="number"
                  step="0.1"
                  label="Mínima"
                  filled
                  dense
                  hint="Mín registrada"
                />
              </div>

              <div class="col-12 col-md-6">
                <q-select
                  v-model="form.condiciones_envase"
                  :options="opcionesCondicionEnvase"
                  label="Condiciones del Envase"
                  filled
                  dense
                />
              </div>

              <div class="col-12 col-md-6">
                <q-select
                  v-model="form.condiciones_vehiculo"
                  :options="opcionesCondicionVehiculo"
                  label="Condiciones del Vehículo"
                  filled
                  dense
                />
              </div>

              <div class="col-12">
                <q-input
                  v-model="form.observaciones"
                  type="textarea"
                  label="Observaciones"
                  filled
                  dense
                  rows="3"
                />
              </div>
            </div>
          </q-form>
        </q-card-section>

        <q-separator />

        <q-card-actions align="right">
          <q-btn flat label="Cancelar" color="grey-7" @click="cerrarFormulario" />
          <q-btn
            label="Guardar"
            color="primary"
            @click="guardar"
            :loading="guardando"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue'
import { useQuasar } from 'quasar'
import axios from 'axios'
import moment from 'moment'

export default {
  name: 'CosechaTransporte',
  props: {
    cosecha: {
      type: Object,
      required: true
    }
  },
  setup(props) {
    const $q = useQuasar()
    const registros = ref([])
    const transportes = ref([])
    const cargando = ref(false)
    const guardando = ref(false)
    const dialogo = ref(false)
    const modoEdicion = ref(false)
    const formRef = ref(null)

    const opcionesCondicionEnvase = [
      'Limpio',
      'Sellado',
      'Adecuado',
      'Requiere limpieza',
      'Dañado'
    ]

    const opcionesCondicionVehiculo = [
      'Limpio',
      'Adecuado',
      'Necesita limpieza',
      'No cumple requisitos'
    ]

    const formInicial = {
      transporte_id: null,
      lugar_origen: '',
      lugar_destino: 'Planta de procesamiento',
      distancia_km: null,
      temperatura_salida: null,
      temperatura_llegada: null,
      temperatura_maxima: null,
      temperatura_minima: null,
      fecha_hora_salida: null,
      fecha_hora_llegada: null,
      condiciones_envase: null,
      condiciones_vehiculo: null,
      observaciones: null
    }

    const form = ref({ ...formInicial })

    const cargarRegistros = async () => {
      if (!props.cosecha?.id) return

      cargando.value = true
      try {
        const { data } = await axios.get(`/acopio-cosechas/${props.cosecha.id}/transporte-logs`)
        registros.value = data
      } catch (error) {
        $q.notify({
          type: 'negative',
          message: 'Error al cargar registros de transporte'
        })
      } finally {
        cargando.value = false
      }
    }

    const cargarTransportes = async () => {
      try {
        const { data } = await axios.get('/transportes')
        transportes.value = data.data || data
      } catch (error) {
        $q.notify({
          type: 'warning',
          message: 'No se pudieron cargar los transportes'
        })
      }
    }

    const abrirFormulario = (registro) => {
      if (registro) {
        modoEdicion.value = true
        form.value = {
          id: registro.id,
          transporte_id: registro.transporte_id,
          lugar_origen: registro.lugar_origen,
          lugar_destino: registro.lugar_destino,
          distancia_km: registro.distancia_km,
          temperatura_salida: registro.temperatura_salida,
          temperatura_llegada: registro.temperatura_llegada,
          temperatura_maxima: registro.temperatura_maxima,
          temperatura_minima: registro.temperatura_minima,
          fecha_hora_salida: registro.fecha_hora_salida ? moment(registro.fecha_hora_salida).format('YYYY-MM-DDTHH:mm') : null,
          fecha_hora_llegada: registro.fecha_hora_llegada ? moment(registro.fecha_hora_llegada).format('YYYY-MM-DDTHH:mm') : null,
          condiciones_envase: registro.condiciones_envase,
          condiciones_vehiculo: registro.condiciones_vehiculo,
          observaciones: registro.observaciones
        }
      } else {
        modoEdicion.value = false
        form.value = { ...formInicial }
      }
      dialogo.value = true
    }

    const cerrarFormulario = () => {
      dialogo.value = false
      form.value = { ...formInicial }
    }

    const guardar = async () => {
      const valido = await formRef.value.validate()
      if (!valido) return

      guardando.value = true
      try {
        if (modoEdicion.value) {
          await axios.put(`/transporte-logs/${form.value.id}`, form.value)
          $q.notify({
            type: 'positive',
            message: 'Registro actualizado exitosamente'
          })
        } else {
          await axios.post(`/acopio-cosechas/${props.cosecha.id}/transporte-logs`, form.value)
          $q.notify({
            type: 'positive',
            message: 'Registro creado exitosamente'
          })
        }
        cerrarFormulario()
        cargarRegistros()
      } catch (error) {
        $q.notify({
          type: 'negative',
          message: error.response?.data?.message || 'Error al guardar'
        })
      } finally {
        guardando.value = false
      }
    }

    const confirmarEliminar = (registro) => {
      $q.dialog({
        title: 'Confirmar eliminación',
        message: '¿Está seguro de eliminar este registro de transporte?',
        cancel: true,
        persistent: true
      }).onOk(async () => {
        try {
          await axios.delete(`/transporte-logs/${registro.id}`)
          $q.notify({
            type: 'positive',
            message: 'Registro eliminado exitosamente'
          })
          cargarRegistros()
        } catch (error) {
          $q.notify({
            type: 'negative',
            message: 'Error al eliminar'
          })
        }
      })
    }

    const getColorEstado = (estado) => {
      switch (estado) {
        case 'CONFORME':
          return 'green'
        case 'NO_CONFORME':
          return 'red'
        default:
          return 'grey'
      }
    }

    const formatTemperatura = (temp) => {
      return temp !== null && temp !== undefined ? `${temp}°C` : 'N/A'
    }

    const formatFecha = (fecha) => {
      return fecha ? moment(fecha).format('DD/MM/YYYY HH:mm') : 'N/A'
    }

    watch(() => props.cosecha?.id, (newId) => {
      if (newId) {
        cargarRegistros()
      }
    })

    onMounted(() => {
      cargarTransportes()
      if (props.cosecha?.id) {
        cargarRegistros()
      }
    })

    return {
      registros,
      transportes,
      cargando,
      guardando,
      dialogo,
      modoEdicion,
      form,
      formRef,
      opcionesCondicionEnvase,
      opcionesCondicionVehiculo,
      abrirFormulario,
      cerrarFormulario,
      guardar,
      confirmarEliminar,
      getColorEstado,
      formatTemperatura,
      formatFecha
    }
  }
}
</script>
