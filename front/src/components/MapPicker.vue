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
      @click="onMapClick"
      ref="mapRef"
    >
      <l-tile-layer :url="tileUrl" :attribution="attribution" />
      <l-marker v-if="hasLatLng" :lat-lng="[localValue.lat, localValue.lng]" :draggable="true" @moveend="onDragEnd">
        <l-popup>
          <div>Lat: {{ localValue.lat?.toFixed(6) }}<br/>Lng: {{ localValue.lng?.toFixed(6) }}</div>
        </l-popup>
      </l-marker>
    </l-map>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { LMap, LTileLayer, LMarker, LPopup } from '@vue-leaflet/vue-leaflet'
import 'leaflet/dist/leaflet.css'
import L from 'leaflet'

// fix de íconos (Vite)
import markerIcon2xUrl from 'leaflet/dist/images/marker-icon-2x.png'
import markerIconUrl   from 'leaflet/dist/images/marker-icon.png'
import markerShadowUrl from 'leaflet/dist/images/marker-shadow.png'
L.Icon.Default.mergeOptions({
  iconRetinaUrl: markerIcon2xUrl,
  iconUrl: markerIconUrl,
  shadowUrl: markerShadowUrl
})

// props + v-model
const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({ lat: null, lng: null })
  },
  // centro inicial del mapa
  center: {
    type: Array,
    default: () => [-16.5, -68.15] // La Paz
  },
  zoomInit: {
    type: Number,
    default: 13
  }
})
const emit = defineEmits(['update:modelValue'])

const localValue = ref({ ...props.modelValue })
watch(() => props.modelValue, (v) => { localValue.value = { ...v } }, { deep: true })
watch(localValue, v => emit('update:modelValue', v), { deep: true })

const mapRef = ref(null)
const zoom = ref(props.zoomInit)
const mapCenter = computed(() => {
  // si tenemos lat/lng, centra ahí; sino usa props.center
  if (localValue.value.lat != null && localValue.value.lng != null) {
    return [localValue.value.lat, localValue.value.lng]
  }
  return props.center
})

const hasLatLng = computed(() => localValue.value.lat != null && localValue.value.lng != null)

const tileUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'
const attribution = '&copy; OpenStreetMap contributors'

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
.map-wrapper {
  width: 100%;
}
</style>
