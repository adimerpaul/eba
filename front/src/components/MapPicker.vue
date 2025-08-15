<template>
  <div class="map-wrapper">
    <div class="row items-center q-col-gutter-sm q-mb-sm">
      <div class="col-6 col-md-3">
        <q-input v-model.number="localValue.latitud" dense outlined label="Latitud" style="max-width: 160px" />
      </div>
      <div class="col-6 col-md-3">
        <q-input v-model.number="localValue.longitud" dense outlined label="Longitud" style="max-width: 160px" />
      </div>
      <div class="col-6 col-md-3">
        <q-btn dense no-caps color="primary" label="Ir" @click="flyToLatLng" icon="place" style="width: 100%" />
      </div>
      <div class="col-6 col-md-3">
        <q-btn dense no-caps color="secondary" label="Mi ubicación" @click="locateMe" icon="my_location" style="width: 100%" />
      </div>
    </div>

    <l-map
      style="height: 350px"
      v-model:zoom="zoom"
      :center="mapCenter"
      :use-global-leaflet="false"
      :options="{ attributionControl: false }"
      @click="onMapClick"
      ref="mapRef"
    >
      <!-- Control de capas -->
      <l-control-layers position="topright" />

      <!-- OpenStreetMap -->
      <l-tile-layer
        layer-type="base"
        name="OpenStreetMap"
        url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        attribution="&copy; OpenStreetMap contributors"
        :subdomains="['a','b','c']"
        :max-zoom="19"
        :visible="false"
      />

      <!-- Google Calle (por defecto) -->
      <l-tile-layer
        layer-type="base"
        name="Google Calle"
        url="https://mt1.google.com/vt/lyrs=r&x={x}&y={y}&z={z}"
        attribution="Map data &copy; Google"
        :max-zoom="21"
        :visible="true"
      />

      <!-- Google Satélite -->
      <l-tile-layer
        layer-type="base"
        name="Google Satélite"
        url="https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}"
        attribution="Map data &copy; Google"
        :max-zoom="21"
        :visible="false"
      />

      <!-- Google Híbrido -->
      <l-tile-layer
        layer-type="base"
        name="Google Híbrido"
        url="https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}"
        attribution="Map data &copy; Google"
        :max-zoom="21"
        :visible="false"
      />

      <!-- Marcador -->
      <l-marker
        v-if="hasLatLng"
        :lat-lng="[Number(localValue.latitud), Number(localValue.longitud)]"
        :draggable="true"
        @moveend="onDragEnd"
      >
        <l-popup>
          <div>Lat: {{ toFix(localValue.latitud) }}<br/>Lng: {{ toFix(localValue.longitud) }}</div>
        </l-popup>
      </l-marker>
    </l-map>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { LMap, LTileLayer, LMarker, LPopup, LControlLayers } from '@vue-leaflet/vue-leaflet'
import 'leaflet/dist/leaflet.css'
import L from 'leaflet'

// Fix iconos Vite
import markerIcon2xUrl from 'leaflet/dist/images/marker-icon-2x.png'
import markerIconUrl   from 'leaflet/dist/images/marker-icon.png'
import markerShadowUrl from 'leaflet/dist/images/marker-shadow.png'
L.Icon.Default.mergeOptions({
  iconRetinaUrl: markerIcon2xUrl,
  iconUrl: markerIconUrl,
  shadowUrl: markerShadowUrl
})

const props = defineProps({
  // Retrocompatible: si te pasan {lat, lng} también lo toma
  modelValue: { type: Object, default: () => ({ latitud: null, longitud: null }) },
  center: { type: Array, default: () => [-16.5, -68.15] },
  zoomInit: { type: Number, default: 13 }
})
const emit = defineEmits(['update:modelValue'])

/**
 * Estado interno siempre en {latitud, longitud}.
 * Si el padre manda {lat, lng}, lo mapeamos.
 */
const normalizeIn = (mv) => ({
  latitud: mv?.latitud ?? mv?.lat ?? null,
  longitud: mv?.longitud ?? mv?.lng ?? null
})
const normalizeOut = (v) => ({
  // Emitimos ambos pares de keys para no romper nada
  latitud: v.latitud ?? null,
  longitud: v.longitud ?? null,
  lat: v.latitud ?? null,
  lng: v.longitud ?? null
})

const localValue = ref(normalizeIn(props.modelValue))

// Observa cambios del padre (en cualquiera de las dos formas)
watch(
  () => props.modelValue,
  (mv) => {
    const next = normalizeIn(mv)
    if (next.latitud !== localValue.value.latitud || next.longitud !== localValue.value.longitud) {
      localValue.value = next
    }
  },
  { deep: true }
)

// Emitimos al padre cuando cambian latitud/longitud locales
watch(localValue, v => emit('update:modelValue', normalizeOut(v)), { deep: true })

const mapRef = ref(null)
const zoom = ref(props.zoomInit)

const mapCenter = computed(() =>
  (localValue.value.latitud != null && localValue.value.longitud != null)
    ? [Number(localValue.value.latitud), Number(localValue.value.longitud)]
    : props.center
)

const hasLatLng = computed(() =>
  localValue.value.latitud != null && localValue.value.longitud != null &&
  Number.isFinite(Number(localValue.value.latitud)) &&
  Number.isFinite(Number(localValue.value.longitud))
)

function toFix (v, n = 6) {
  const num = Number(v)
  return Number.isFinite(num) ? num.toFixed(n) : '-'
}

function onMapClick(e) {
  const { lat, lng } = e.latlng
  localValue.value.latitud = Number(lat.toFixed(7))
  localValue.value.longitud = Number(lng.toFixed(7))
}
function onDragEnd(e) {
  const { lat, lng } = e.target.getLatLng()
  localValue.value.latitud = Number(lat.toFixed(7))
  localValue.value.longitud = Number(lng.toFixed(7))
}
function flyToLatLng() {
  if (!hasLatLng.value) return
  const leaflet = mapRef.value?.leafletObject
  leaflet && leaflet.flyTo([Number(localValue.value.latitud), Number(localValue.value.longitud)], Math.max(zoom.value, 15))
}
function locateMe() {
  if (!('geolocation' in navigator)) return
  navigator.geolocation.getCurrentPosition(pos => {
    const { latitude, longitude } = pos.coords
    localValue.value.latitud = Number(latitude.toFixed(7))
    localValue.value.longitud = Number(longitude.toFixed(7))
    const leaflet = mapRef.value?.leafletObject
    leaflet && leaflet.flyTo([Number(localValue.value.latitud), Number(localValue.value.longitud)], 16)
  })
}
</script>

<style scoped>
.map-wrapper { width: 100%; }
</style>
