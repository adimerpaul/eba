<template>
  <q-page class="q-pa-md">
    <div class="row items-center q-gutter-sm q-mb-sm">
      <q-btn flat round dense icon="arrow_back" @click="$router.back()" />
      <div class="text-h6 text-weight-bold">
        Apiarios — {{ depName || ('Departamento '+(depId || '—')) }}
      </div>
      <q-space />
      <q-input v-model="filters.search" dense outlined placeholder="Buscar productor/municipio..." debounce="300" style="min-width:260px">
        <template #append><q-icon name="search" /></template>
      </q-input>
      <q-btn color="primary" icon="refresh" label="Actualizar" :loading="loading" no-caps @click="fetchApiarios"/>
    </div>

    <q-card flat bordered>
      <q-card-section>
        <div class="text-caption q-mb-xs">Total apiarios: {{ apiarios.length }}</div>

        <l-map
          style="height: 70vh"
          v-model:zoom="zoom"
          :center="mapCenter"
          :use-global-leaflet="false"
          :options="{ attributionControl: false }"
          ref="mapRef"
        >
          <l-control-layers position="topright" />

          <l-tile-layer
            layer-type="base"
            name="OpenStreetMap"
            url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
            attribution="&copy; OpenStreetMap contributors"
            :subdomains="['a','b','c']"
            :max-zoom="19"
            :visible="false"
          />
          <l-tile-layer
            layer-type="base"
            name="Google Calle"
            url="https://mt1.google.com/vt/lyrs=r&x={x}&y={y}&z={z}"
            attribution="Map data &copy; Google"
            :max-zoom="21"
            :visible="true"
          />
          <l-tile-layer
            layer-type="base"
            name="Google Satélite"
            url="https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}"
            attribution="Map data &copy; Google"
            :max-zoom="21"
            :visible="false"
          />
          <l-tile-layer
            layer-type="base"
            name="Google Híbrido"
            url="https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}"
            attribution="Map data &copy; Google"
            :max-zoom="21"
            :visible="false"
          />

          <!-- Marcadores -->
          <l-marker
            v-for="m in filteredApiarios"
            :key="m.id"
            :lat-lng="[m.lat, m.lng]"
          >
            <l-popup>
              <div style="min-width:220px">
                <div class="text-subtitle2">{{ m.productor || '—' }}</div>
                <div class="text-caption">Municipio: {{ m.municipio || '—' }}</div>
                <div class="text-caption">Lugar: {{ m.lugar || '—' }}</div>
                <div class="text-caption">CIP: {{ m.cip || '—' }}</div>
                <div class="text-caption">Estado: {{ m.estado || '—' }}</div>
                <q-separator spaced />
                <div class="text-caption">Lat: {{ m.lat.toFixed(6) }} | Lng: {{ m.lng.toFixed(6) }}</div>
              </div>
            </l-popup>
          </l-marker>
        </l-map>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script setup>
import {ref, computed, onMounted, watch, inject, getCurrentInstance} from 'vue'
import { nextTick } from 'vue'
import { useRoute } from 'vue-router'
import { useQuasar } from 'quasar'
import { LMap, LTileLayer, LMarker, LPopup, LControlLayers } from '@vue-leaflet/vue-leaflet'
import 'leaflet/dist/leaflet.css'
import L from 'leaflet'
const { proxy } = getCurrentInstance()
// Fix iconos Vite
import markerIcon2xUrl from 'leaflet/dist/images/marker-icon-2x.png'
import markerIconUrl   from 'leaflet/dist/images/marker-icon.png'
import markerShadowUrl from 'leaflet/dist/images/marker-shadow.png'
L.Icon.Default.mergeOptions({
  iconRetinaUrl: markerIcon2xUrl,
  iconUrl: markerIconUrl,
  shadowUrl: markerShadowUrl
})

const route = useRoute()
const $q = useQuasar()
const api = proxy.$axios // inyectado en main.js

const depId   = computed(() => Number(route.query?.departamento_id || 0))
const depName = computed(() => route.query?.nombre || '')

const zoom = ref(7)
const mapRef = ref(null)

// Centro inicial aproximado a Bolivia
const mapCenter = ref([-16.290154, -63.588653])

const loading  = ref(false)
const apiarios = ref([])
const filters  = ref({ search: '' })

const filteredApiarios = computed(() => {
  const s = (filters.value.search || '').trim().toLowerCase()
  if (!s) return apiarios.value
  return apiarios.value.filter(m =>
    (m.productor || '').toLowerCase().includes(s) ||
    (m.municipio || '').toLowerCase().includes(s) ||
    (m.lugar || '').toLowerCase().includes(s) ||
    (m.cip || '').toLowerCase().includes(s)
  )
})

function isFiniteInRange (lat, lng) {
  const la = Number(lat), ln = Number(lng)
  return Number.isFinite(la) && Number.isFinite(ln) &&
    la >= -90 && la <= 90 && ln >= -180 && ln <= 180
}

async function fetchApiarios () {
  if (!depId.value) return
  loading.value = true
  try {
    const { data } = await api.get(`geo/departamentos/${depId.value}/apiarios`)
    // 1) Casteo + sanitizado
    const raw = Array.isArray(data?.apiarios) ? data.apiarios : []
    const cleaned = raw
      .map(m => ({
        ...m,
        lat: Number(m.lat),
        lng: Number(m.lng)
      }))
      .filter(m => isFiniteInRange(m.lat, m.lng))

    apiarios.value = cleaned

    // 2) Centrado seguro
    await nextTick()
    const leaflet = mapRef.value?.leafletObject
    if (!leaflet) return

    if (apiarios.value.length === 0) {
      // Sin puntos válidos: céntrate al país por defecto
      leaflet.setView(mapCenter.value, 6)
      return
    }
    if (apiarios.value.length === 1) {
      // Un solo punto: setView con zoom agradable
      const p = apiarios.value[0]
      leaflet.setView([p.lat, p.lng], Math.max(zoom.value, 12))
      return
    }
    // 2+ puntos: fitBounds solo con válidos
    // const latlngs = apiarios.value.map(m => [m.lat, m.lng])
    // const bounds = L.latLngBounds(latlngs)
    // if (bounds.isValid()) {
    //   leaflet.fitBounds(bounds.pad(0.15))
    // } else {
    //   // fallback por si acaso
    //   leaflet.setView(mapCenter.value, 6)
    // }
  } catch (e) {
    $q.notify({ type: 'negative', message: e?.response?.data?.message || 'No se pudieron cargar apiarios' })
  } finally {
    loading.value = false
  }
}


onMounted(fetchApiarios)

// Opción A: observar fullPath (simple)
watch(() => route.fullPath, fetchApiarios)

// Opción B (alternativa): observar query con deep
// watch(() => route.query, fetchApiarios, { deep: true })
</script>
