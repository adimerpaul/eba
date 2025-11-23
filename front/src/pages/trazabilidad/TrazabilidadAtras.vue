<template>
  <div class="q-gutter-md">
    <q-card flat bordered>
      <q-card-section>
        <div class="text-h6">Capacidad Productiva vs Producción Real</div>
        <div class="text-caption text-grey-7">
          Análisis de capacidad teórica y producción efectiva
        </div>
      </q-card-section>

      <q-separator />

      <q-card-section v-if="loading">
        <div class="text-center">
          <q-spinner color="primary" size="3em" />
        </div>
      </q-card-section>

      <q-card-section v-else-if="capacidad">
        <div class="row q-col-gutter-md">
          <div class="col-12 col-sm-6 col-md-3">
            <q-card flat bordered>
              <q-card-section class="text-center">
                <q-icon name="people" size="md" color="primary" />
                <div class="text-h4 text-primary q-mt-sm">{{ capacidad.total_productores }}</div>
                <div class="text-caption text-grey-7">Productores Activos</div>
                <div class="text-body2 q-mt-xs">{{ capacidad.total_colmenas }} colmenas</div>
              </q-card-section>
            </q-card>
          </div>

          <div class="col-12 col-sm-6 col-md-3">
            <q-card flat bordered>
              <q-card-section class="text-center">
                <q-icon name="trending_up" size="md" color="orange" />
                <div class="text-h4 text-orange q-mt-sm">{{ formatNumber(capacidad.capacidad_teorica) }}</div>
                <div class="text-caption text-grey-7">Capacidad Teórica (kg)</div>
                <div class="text-body2 q-mt-xs">25 kg/colmena</div>
              </q-card-section>
            </q-card>
          </div>

          <div class="col-12 col-sm-6 col-md-3">
            <q-card flat bordered>
              <q-card-section class="text-center">
                <q-icon name="check_circle" size="md" color="positive" />
                <div class="text-h4 text-positive q-mt-sm">{{ formatNumber(capacidad.produccion_real) }}</div>
                <div class="text-caption text-grey-7">Producción Real (kg)</div>
                <div class="text-body2 q-mt-xs">{{ capacidad.eficiencia }}% eficiencia</div>
              </q-card-section>
            </q-card>
          </div>

          <div class="col-12 col-sm-6 col-md-3">
            <q-card flat bordered>
              <q-card-section class="text-center">
                <q-icon name="hourglass_empty" size="md" color="info" />
                <div class="text-h4 text-info q-mt-sm">{{ formatNumber(capacidad.produccion_esperada_restante) }}</div>
                <div class="text-caption text-grey-7">Restante Esperado (kg)</div>
                <div class="text-body2 q-mt-xs">{{ capacidad.meta_mensual }} kg/mes</div>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </q-card-section>
    </q-card>

    <q-card flat bordered v-if="alertas && alertas.length > 0">
      <q-card-section>
        <div class="row items-center">
          <div class="col">
            <div class="text-h6">Alertas de Sobreproducción</div>
            <div class="text-caption text-grey-7">
              Productores con producción superior a su capacidad teórica
            </div>
          </div>
          <div class="col-auto">
            <q-badge color="warning" outline>
              {{ alertas.length }} alertas
            </q-badge>
          </div>
        </div>
      </q-card-section>

      <q-separator />

      <q-card-section>
        <q-markup-table dense flat>
          <thead>
            <tr>
              <th class="text-left">Productor</th>
              <th class="text-left">RUNSA</th>
              <th class="text-right">Capacidad (kg)</th>
              <th class="text-right">Producción (kg)</th>
              <th class="text-right">Diferencia (kg)</th>
              <th class="text-right">Exceso (%)</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="alerta in alertas" :key="alerta.productor_id">
              <td class="text-left">{{ alerta.productor }}</td>
              <td class="text-left">{{ alerta.runsa || 'N/A' }}</td>
              <td class="text-right">{{ formatNumber(alerta.capacidad_teorica) }}</td>
              <td class="text-right">{{ formatNumber(alerta.produccion_real) }}</td>
              <td class="text-right text-warning text-weight-bold">+{{ formatNumber(alerta.diferencia) }}</td>
              <td class="text-right">
                <q-badge color="warning">{{ alerta.porcentaje_sobre_capacidad }}%</q-badge>
              </td>
            </tr>
          </tbody>
        </q-markup-table>
      </q-card-section>
    </q-card>

    <div class="row q-col-gutter-md">
      <div class="col-12 col-md-6">
        <q-card flat bordered>
          <q-card-section>
            <div class="text-h6">Control de Plagas</div>
            <div class="text-caption text-grey-7">
              Registros SENASAG de control fitosanitario
            </div>
          </q-card-section>

          <q-separator />

          <q-card-section v-if="senasag && senasag.plagas">
            <div class="q-mb-md">
              <div class="row q-col-gutter-sm">
                <div class="col-6">
                  <div class="text-caption text-grey-7">Total Registros</div>
                  <div class="text-h5">{{ senasag.plagas.total_registros }}</div>
                </div>
                <div class="col-6">
                  <div class="text-caption text-grey-7">Colmenas Afectadas</div>
                  <div class="text-h5 text-warning">
                    {{ senasag.plagas.colmenas_afectadas }}
                    <span class="text-caption">({{ senasag.plagas.porcentaje_afectacion }}%)</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="text-subtitle2 q-mb-sm">Plagas Más Comunes</div>
            <q-list bordered separator dense>
              <q-item v-for="plaga in senasag.plagas.mas_comunes" :key="plaga.nombre">
                <q-item-section>
                  <q-item-label>{{ plaga.nombre }}</q-item-label>
                  <q-item-label caption>{{ plaga.casos }} casos</q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-badge color="orange">{{ plaga.colmenas_afectadas }} colmenas</q-badge>
                </q-item-section>
              </q-item>
            </q-list>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-md-6">
        <q-card flat bordered>
          <q-card-section>
            <div class="text-h6">Aplicación de Medicamentos</div>
            <div class="text-caption text-grey-7">
              Tratamientos farmacológicos aplicados
            </div>
          </q-card-section>

          <q-separator />

          <q-card-section v-if="senasag && senasag.medicamentos">
            <div class="text-h5 q-mb-md">{{ senasag.medicamentos.total_registros }} aplicaciones</div>

            <div class="text-subtitle2 q-mb-sm">Productos Más Usados</div>
            <q-list bordered separator dense>
              <q-item v-for="med in senasag.medicamentos.mas_usados" :key="med.producto">
                <q-item-section>
                  <q-item-label>{{ med.producto }}</q-item-label>
                  <q-item-label caption>{{ med.principio_activo }}</q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-badge color="primary">{{ med.aplicaciones }} veces</q-badge>
                </q-item-section>
              </q-item>
            </q-list>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <q-card flat bordered v-if="transporte">
      <q-card-section>
        <div class="text-h6">Transporte desde Apiarios</div>
        <div class="text-caption text-grey-7">
          Control de condiciones de transporte de materia prima
        </div>
      </q-card-section>

      <q-separator />

      <q-card-section>
        <div class="row q-col-gutter-md q-mb-md">
          <div class="col-4">
            <div class="text-caption text-grey-7">Total Transportes</div>
            <div class="text-h5">{{ transporte.total_transportes }}</div>
          </div>
          <div class="col-4">
            <div class="text-caption text-grey-7">Temperatura Promedio</div>
            <div class="text-h5">{{ transporte.temperatura_promedio }}°C</div>
          </div>
          <div class="col-4">
            <div class="text-caption text-grey-7">Fuera de Rango</div>
            <div class="text-h5 text-warning">
              {{ transporte.transportes_fuera_rango }}
              <span class="text-caption">({{ transporte.porcentaje_fuera_rango }}%)</span>
            </div>
          </div>
        </div>

        <div class="text-subtitle2 q-mb-sm">Últimos Transportes</div>
        <q-markup-table dense flat>
          <thead>
            <tr>
              <th class="text-left">Fecha</th>
              <th class="text-left">Apiario</th>
              <th class="text-right">Cantidad (kg)</th>
              <th class="text-center">Temperatura</th>
              <th class="text-center">Estado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(t, index) in transporte.ultimos_transportes.slice(0, 10)" :key="index">
              <td class="text-left">{{ formatFecha(t.fecha) }}</td>
              <td class="text-left">{{ t.apiario }}</td>
              <td class="text-right">{{ formatNumber(t.cantidad_kg) }}</td>
              <td class="text-center">{{ t.temperatura }}°C</td>
              <td class="text-center">
                <q-badge 
                  :color="t.estado_temperatura === 'CONFORME' ? 'positive' : 'warning'"
                >
                  {{ t.estado_temperatura === 'CONFORME' ? 'Conforme' : 'Alerta' }}
                </q-badge>
              </td>
            </tr>
          </tbody>
        </q-markup-table>
      </q-card-section>
    </q-card>

    <q-card flat bordered>
      <q-card-section>
        <div class="row items-center">
          <div class="col">
            <div class="text-h6">Mortandad de Colmenas</div>
            <div class="text-caption text-grey-7">
              Registro de pérdidas y causas
            </div>
          </div>
          <div class="col-auto">
            <q-badge color="grey" outline>Funcionalidad Pendiente</q-badge>
          </div>
        </div>
      </q-card-section>

      <q-separator />

      <q-card-section>
        <q-banner class="bg-grey-2">
          <template v-slot:avatar>
            <q-icon name="info" color="info" />
          </template>
          Esta sección estará disponible una vez implementado el formulario de captura de mortandad de colmenas.
          Se registrarán datos como: fecha, número de colmenas muertas, causa, pérdida estimada y acciones correctivas.
        </q-banner>
      </q-card-section>
    </q-card>
  </div>
</template>

<script>
import moment from 'moment'

export default {
  name: 'TrazabilidadAtras',
  props: {
    gestion: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      loading: false,
      capacidad: null,
      alertas: [],
      senasag: null,
      transporte: null
    }
  },
  mounted() {
    this.cargarDatos()
  },
  methods: {
    formatNumber(value) {
      return Number(value || 0).toLocaleString('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      })
    },

    formatFecha(fecha) {
      return moment(fecha).format('DD/MM/YYYY HH:mm')
    },

    async cargarDatos() {
      this.loading = true
      try {
        await Promise.all([
          this.cargarCapacidad(),
          this.cargarAlertas(),
          this.cargarSenasag(),
          this.cargarTransporte()
        ])
      } finally {
        this.loading = false
      }
    },

    async cargarCapacidad() {
      try {
        const { data } = await this.$axios.get('/dashboard-trazabilidad/capacidad-productiva', {
          params: { gestion: this.gestion }
        })
        this.capacidad = data.resumen
      } catch (error) {
        this.$q.notify({
          type: 'negative',
          message: 'Error al cargar capacidad productiva'
        })
      }
    },

    async cargarAlertas() {
      try {
        const { data } = await this.$axios.get('/dashboard-trazabilidad/alertas-sobreproduccion', {
          params: { gestion: this.gestion, limit: 10 }
        })
        this.alertas = data.alertas
      } catch (error) {
        this.$q.notify({
          type: 'negative',
          message: 'Error al cargar alertas'
        })
      }
    },

    async cargarSenasag() {
      try {
        const { data } = await this.$axios.get('/dashboard-trazabilidad/senasag', {
          params: { gestion: this.gestion }
        })
        this.senasag = data
      } catch (error) {
        this.$q.notify({
          type: 'negative',
          message: 'Error al cargar datos SENASAG'
        })
      }
    },

    async cargarTransporte() {
      try {
        const { data } = await this.$axios.get('/dashboard-trazabilidad/transporte-apiarios', {
          params: { gestion: this.gestion, limit: 20 }
        })
        this.transporte = data.resumen
        this.transporte.ultimos_transportes = data.ultimos_transportes
      } catch (error) {
        this.$q.notify({
          type: 'negative',
          message: 'Error al cargar transporte'
        })
      }
    }
  },
  watch: {
    gestion() {
      this.cargarDatos()
    }
  }
}
</script>
