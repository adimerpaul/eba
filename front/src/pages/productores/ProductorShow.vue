<template>
  <q-page class="q-pa-md">
    <div class="row items-center q-gutter-sm q-mb-md">
      <q-btn flat round icon="arrow_back" @click="$router.push('/productores')" />
      <!-- 2025-11-21: Se agrega el nombre del productor junto al título -->
      <div class="text-h6">
        Detalles Productor
        <span v-if="productor" class="text-primary"> — {{ productor.nombre_completo }}</span>
      </div>
      <q-space />
      <!-- 2025-11-21: Boton de navegacion rapida a modulo de Acopio -->
      <q-btn v-if="productor" flat color="primary" icon="inventory_2" label="Ver Acopios" @click="navegarAcopios" />
      <q-btn flat round icon="refresh" :loading="loading" @click="fetchProductor" />
    </div>

    <!-- 2025-11-21: Dashboard con KPIs y alertas del productor -->
    <q-card v-if="!loading && productor" flat bordered class="q-mb-md">
      <q-card-section>
        <div class="row q-col-gutter-md">
          <!-- Tarjeta: Total Apiarios -->
          <div class="col-12 col-sm-6 col-md-3">
            <q-card flat class="bg-primary text-white">
              <q-card-section class="text-center">
                <div class="text-caption">Total Apiarios</div>
                <div class="text-h4">{{ totalApiarios }}</div>
              </q-card-section>
            </q-card>
          </div>

          <!-- Tarjeta: Total Colmenas -->
          <div class="col-12 col-sm-6 col-md-3">
            <q-card flat class="bg-green text-white">
              <q-card-section class="text-center">
                <div class="text-caption">Total Colmenas</div>
                <div class="text-h4">{{ totalColmenas }}</div>
              </q-card-section>
            </q-card>
          </div>

          <!-- Tarjeta: Total Certificaciones -->
          <div class="col-12 col-sm-6 col-md-3">
            <q-card flat class="bg-orange text-white">
              <q-card-section class="text-center">
                <div class="text-caption">Certificaciones</div>
                <div class="text-h4">{{ totalCertificaciones }}</div>
              </q-card-section>
            </q-card>
          </div>

          <!-- Tarjeta: Estado del Productor -->
          <div class="col-12 col-sm-6 col-md-3">
            <q-card flat :class="estadoClass">
              <q-card-section class="text-center">
                <div class="text-caption">Estado</div>
                <div class="text-h6">{{ productor.estado || 'N/A' }}</div>
              </q-card-section>
            </q-card>
          </div>
        </div>

        <!-- 2025-11-21: Banner de alerta para certificaciones vencidas -->
        <q-banner v-if="certificacionesVencidas > 0" class="bg-red-1 text-red-9 q-mt-md" rounded>
          <template v-slot:avatar>
            <q-icon name="warning" color="red" />
          </template>
          Tiene {{ certificacionesVencidas }} certificacion(es) por vencer en los proximos 30 dias
        </q-banner>

        <!-- 2025-11-21: Banner de alerta para RUNSAs vencidos -->
        <q-banner v-if="runsasVencidos > 0" class="bg-orange-1 text-orange-9 q-mt-sm" rounded>
          <template v-slot:avatar>
            <q-icon name="warning" color="orange" />
          </template>
          Tiene {{ runsasVencidos }} RUNSA(s) por vencer en los proximos 30 dias
        </q-banner>
      </q-card-section>
    </q-card>

    <!-- 2025-11-21: Indicador de completitud del perfil -->
    <q-card v-if="!loading && productor" flat bordered class="q-mb-md">
      <q-card-section>
        <div class="text-subtitle2 q-mb-sm">Completitud del Perfil</div>
        <q-linear-progress :value="completitud / 100" size="12px" :color="completitud === 100 ? 'green' : 'orange'"
          class="q-mb-sm" />
        <div class="text-caption text-grey-7">
          Perfil completo al {{ completitud }}%
          <q-tooltip max-width="300px">
            <div class="text-weight-bold q-mb-xs">Checklist de Completitud:</div>
            <div v-for="item in checklistCompletitud" :key="item.label" class="q-my-xs">
              <q-icon :name="item.completo ? 'check_circle' : 'radio_button_unchecked'"
                :color="item.completo ? 'green' : 'grey'" size="sm" />
              {{ item.label }}
            </div>
          </q-tooltip>
        </div>
      </q-card-section>
    </q-card>

    <q-card flat bordered>
      <q-tabs v-model="tab" class="text-primary" align="left" dense>
        <q-tab name="general" icon="badge" label="1) Datos del Productor" no-caps />
        <!-- 2025-11-21: Badge de alerta en tab RUNSA -->
        <q-tab name="runsa" icon="assured_workload" no-caps>
          <template v-slot:default>
            2) Runsa
            <q-badge v-if="runsasVencidos > 0" color="orange" floating>{{ runsasVencidos }}</q-badge>
          </template>
        </q-tab>
        <!-- 2025-11-21: Badge de alerta en tab Certificaciones -->
        <q-tab name="certs" icon="verified" no-caps>
          <template v-slot:default>
            3) Certificaciones
            <q-badge v-if="certificacionesVencidas > 0" color="red" floating>{{ certificacionesVencidas }}</q-badge>
          </template>
        </q-tab>
        <!--<q-tab name="apiarios" icon="hive" label="4) Apiarios" no-caps />-->
        <q-tab name="apiarios" icon="hive" label="4) Lugares de Acopio" no-caps />
        <q-tab name="mapa" icon="map" label="5) Mapa" no-caps />
        <q-tab name="acopio" icon="send" label="6) Manejo de Apiario" no-caps />
      </q-tabs>
      <q-separator />

      <q-tab-panels v-model="tab" animated>
        <q-tab-panel name="general" class="q-pa-none">
          <q-card-section v-if="!loading && productor">
            <ProductorForm :productor="productor" banner-msg="Actualiza los datos y guarda los cambios" @saved="onSaved"
              @cancel="$router.back()" />
          </q-card-section>
        </q-tab-panel>

        <q-tab-panel name="certs" class="q-pa-none">
          <ProductorCertificaciones :productor="productor" @updated="fetchProductor" />
        </q-tab-panel>

        <q-tab-panel name="runsa" class="q-pa-none">
          <ProductorRunsa :productor="productor" @updated="fetchProductor" />
        </q-tab-panel>

        <q-tab-panel name="apiarios" class="q-pa-none">
          <ProductorApiarios :productor="productor" @updated="fetchProductor" />
        </q-tab-panel>

        <q-tab-panel name="mapa" class="q-pa-none">
          <ProductorMapa :productor="productor" @updated="fetchProductor" />
        </q-tab-panel>

        <!-- NUEVO PANEL: Tab de Acopios por Gestión -->
        <q-tab-panel name="acopios" class="q-pa-none">
          <ProductorAcopiosGestion v-if="!loading && productor" :productor="productor" @updated="fetchProductor" />
        </q-tab-panel>

        <q-tab-panel name="acopio" class="q-pa-none">
          <ProductorAcopios :productor="productor" @updated="fetchProductor" />
        </q-tab-panel>
        <!--      </q-tab-panels>-->
      </q-tab-panels>
    </q-card>
  </q-page>
</template>

<script>
import ProductorForm from 'pages/productores/ProductorForm.vue'
import ProductorCertificaciones from 'pages/productores/tabs/ProductorCertificaciones.vue'
import ProductorApiarios from 'pages/productores/tabs/ProductorApiarios.vue'
import ProductorMapa from 'pages/productores/tabs/ProductorMapa.vue'
import ProductorRunsa from 'pages/productores/tabs/ProductorRunsa.vue'
// NUEVO: Importar componente de Acopios por Gestión
import ProductorAcopiosGestion from 'pages/productores/tabs/ProductorAcopiosGestion.vue'
import ProductorAcopios from "pages/productores/tabs/ProductorAcopios.vue";

export default {
  name: 'ProductorShow',
  components: {
    ProductorAcopios,
    ProductorMapa,
    ProductorApiarios,
    ProductorCertificaciones,
    ProductorForm,
    ProductorRunsa,
    ProductorAcopiosGestion // Registrar nuevo componente
  },
  data() {
    return {
      tab: 'general',
      loading: false,
      productor: null,
      // 2025-11-21: Contadores para alertas de vencimiento
      certificacionesVencidas: 0,
      runsasVencidos: 0
    }
  },
  computed: {
    // 2025-11-21: Computed properties para KPIs del dashboard
    totalApiarios() {
      return this.productor?.apiarios?.length || 0
    },
    totalColmenas() {
      if (!this.productor?.apiarios) return 0
      return this.productor.apiarios.reduce((sum, apiario) => {
        return sum + (parseInt(apiario.numero_colmenas_prod) || 0)
      }, 0)
    },
    totalCertificaciones() {
      return this.productor?.certificaciones?.length || 0
    },
    // 2025-11-21: Clase dinamica para tarjeta de estado
    estadoClass() {
      if (!this.productor?.estado) return 'bg-grey text-white'
      const estado = this.productor.estado.toUpperCase()
      if (estado === 'VIGENTE' || estado === 'ACTIVO') return 'bg-green text-white'
      if (estado === 'VENCIDO' || estado === 'INACTIVO') return 'bg-red text-white'
      return 'bg-grey text-white'
    },
    // 2025-11-21: Checklist de completitud del perfil
    checklistCompletitud() {
      if (!this.productor) return []

      const tieneDatosBasicos = !!(
        this.productor.nombre &&
        this.productor.apellidos &&
        this.productor.numcarnet &&
        this.productor.organizacion_id
      )

      const tieneRunsaVigente = this.productor.runsas?.some(r => r.estado === 'VIGENTE') || false

      const tieneCertificacionVigente = this.productor.certificaciones?.some(c => c.estado === 'VIGENTE') || false

      const tieneApiario = (this.productor.apiarios?.length || 0) > 0

      const tieneColmenas = this.totalColmenas > 0

      return [
        { label: 'Datos basicos completos', completo: tieneDatosBasicos },
        { label: 'Tiene RUNSA vigente', completo: tieneRunsaVigente },
        { label: 'Tiene certificacion vigente', completo: tieneCertificacionVigente },
        { label: 'Tiene al menos un apiario', completo: tieneApiario },
        { label: 'Tiene colmenas registradas', completo: tieneColmenas }
      ]
    },
    // 2025-11-21: Porcentaje de completitud (0-100)
    completitud() {
      const checklist = this.checklistCompletitud
      if (checklist.length === 0) return 0

      const completos = checklist.filter(item => item.completo).length
      return Math.round((completos / checklist.length) * 100)
    }
  },
  mounted() {
    this.fetchProductor()
    // 2025-11-21: Cargar alertas de vencimientos al montar componente
    this.fetchVencimientos()
  },
  methods: {
    async fetchProductor() {
      this.loading = true
      try {
        const id = this.$route.params.id
        const { data } = await this.$axios.get(`productores/${id}`)
        this.productor = data
      } catch (e) {
        this.productor = null
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo cargar el productor')
      } finally {
        this.loading = false
      }
    },
    /**
     * Obtener contadores de vencimientos proximos (30 dias)
     * Creado: 2025-11-21
     */
    async fetchVencimientos() {
      try {
        const id = this.$route.params.id
        const { data } = await this.$axios.get(`productores/${id}/verificar-vencimientos`)
        this.certificacionesVencidas = data.certificaciones_por_vencer || 0
        this.runsasVencidos = data.runsas_por_vencer || 0
      } catch (e) {
        this.certificacionesVencidas = 0
        this.runsasVencidos = 0
      }
    },
    /**
     * Navegar al modulo de Acopio con filtro por productor
     * Creado: 2025-11-21
     */
    navegarAcopios() {
      this.$router.push({
        path: '/acopios',
        query: { productor_id: this.productor.id }
      })
    },
    onSaved(saved) {
      this.productor = saved
      this.$alert?.success?.('Cambios guardados')
    }
  }
}
</script>
