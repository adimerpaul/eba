<template>
  <div class="map-wrapper">
    <div class="row items-center q-col-gutter-sm q-mb-sm">
      <div class="col-6 col-md-3">
        <q-input v-model.number="localValue.lat" dense outlined label="Lat" style="max-width: 160px" />
      </div>
      <div class="col-6 col-md-3">
        <q-input v-model.number="localValue.lng" dense outlined label="Lng" style="max-width: 160px" />
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
        :lat-lng="[localValue.lat, localValue.lng]"
        :draggable="true"
        @moveend="onDragEnd"
      >
        <l-popup>
          <div>Lat: {{ localValue.lat?.toFixed(6) }}<br/>Lng: {{ localValue.lng?.toFixed(6) }}</div>
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
  modelValue: { type: Object, default: () => ({ lat: null, lng: null }) },
  center: { type: Array, default: () => [-16.5, -68.15] },
  zoomInit: { type: Number, default: 13 }
})
const emit = defineEmits(['update:modelValue'])

const localValue = ref({ ...props.modelValue })

// <- solo observamos cambios en lat/lng del padre; si no cambian, no tocamos el estado interno
watch(
  () => [props.modelValue.lat, props.modelValue.lng],
  ([lat, lng]) => {
    if (lat !== localValue.value.lat || lng !== localValue.value.lng) {
      localValue.value = { lat, lng }
    }
  }
)

// Emitimos al padre cuando cambian lat/lng locales
watch(localValue, v => emit('update:modelValue', v), { deep: true })

const mapRef = ref(null)
const zoom = ref(props.zoomInit)

const mapCenter = computed(() =>
  localValue.value.lat != null && localValue.value.lng != null
    ? [localValue.value.lat, localValue.value.lng]
    : props.center
)
const hasLatLng = computed(() => localValue.value.lat != null && localValue.value.lng != null)

function onMapClick(e) {
  const { lat, lng } = e.latlng
  localValue.value.lat = Number(lat.toFixed(7))
  localValue.value.lng = Number(lng.toFixed(7))
}
function onDragEnd(e) {
  const { lat, lng } = e.target.getLatLng()
  localValue.value.lat = Number(lat.toFixed(7))
  localValue.value.lng = Number(lng.toFixed(7))
}
function flyToLatLng() {
  if (!hasLatLng.value) return
  const leaflet = mapRef.value?.leafletObject
  leaflet && leaflet.flyTo([localValue.value.lat, localValue.value.lng], Math.max(zoom.value, 15))
}
function locateMe() {
  if (!('geolocation' in navigator)) return
  navigator.geolocation.getCurrentPosition(pos => {
    const { latitude, longitude } = pos.coords
    localValue.value.lat = Number(latitude.toFixed(7))
    localValue.value.lng = Number(longitude.toFixed(7))
    const leaflet = mapRef.value?.leafletObject
    leaflet && leaflet.flyTo([localValue.value.lat, localValue.value.lng], 16)
  })
}
</script>

<style scoped>
.map-wrapper { width: 100%; }
</style>
