<template>
  <div class="map-full">
    <l-map
      :center="center"
      v-model:zoom="zoom"
      :use-global-leaflet="false"
      :options="{ attributionControl: false }"
      ref="mapRef"
      style="height: 100%; width: 100%;"
    >
      <l-control-layers position="topright" />

      <!-- Capas base -->
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
      <template v-for="it in withCoords" :key="it.id">
        <l-circle-marker
          :lat-lng="[it.lat, it.lng]"
          :radius="7"
          :color="strokeColor(it.estado)"
          :fill-color="fillColor(it.estado)"
          :fill-opacity="0.9"
          :weight="2"
        >
          <l-popup>
            <div class="q-pa-xs" style="min-width: 180px">
              <div class="text-bold">{{ it.nombre }}</div>
              <div class="text-caption text-grey-8">{{ it.codigo }}</div>
              <q-separator spaced />
              <div><b>Estado:</b> {{ it.estado }}</div>
              <div><b>Municipio:</b> {{ it.municipio || '-' }}</div>
              <div><b>Apiarios:</b> {{ it.apiarios ?? 0 }}</div>
              <div class="text-caption q-mt-xs">
                Lat: {{ Number(it.lat).toFixed(5) }} / Lng: {{ Number(it.lng).toFixed(5) }}
              </div>
            </div>
          </l-popup>
        </l-circle-marker>
      </template>
    </l-map>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { LMap, LTileLayer, LControlLayers, LCircleMarker, LPopup } from '@vue-leaflet/vue-leaflet'
import 'leaflet/dist/leaflet.css'

const props = defineProps({
  items: { type: Array, default: () => [] },
  // cambiar este prop para forzar "fit bounds" (lo mando desde el dialog)
  refitKey: { type: [Number, String], default: 0 }
})

const mapRef = ref(null)
const center = ref([-16.5, -64.5]) // Bolivia aprox centro
const zoom = ref(5)

const withCoords = computed(() =>
  (props.items || []).filter(i => i.lat != null && i.lng != null)
)

function fillColor (estado) {
  if (estado === 'Activo') return '#21ba45'       // green
  if (estado === 'Mantenimiento') return '#FFC107' // amber
  return '#E53935'                                 // red
}
function strokeColor (estado) {
  if (estado === 'Activo') return '#0e8a2f'
  if (estado === 'Mantenimiento') return '#b58900'
  return '#ab1f1c'
}

function fitToMarkers () {
  const map = mapRef.value?.leafletObject
  if (!map) return

  if (withCoords.value.length) {
    const latlngs = withCoords.value.map(i => [i.lat, i.lng])
    const bounds = window.L.latLngBounds(latlngs)
    map.fitBounds(bounds, { padding: [20, 20] })
  } else {
    // bounds de Bolivia (aprox)
    const bolivia = window.L.latLngBounds(
      window.L.latLng(-22.9, -69.6), // SW
      window.L.latLng(-9.7, -57.5)   // NE
    )
    map.fitBounds(bolivia, { padding: [20, 20] })
  }
}

onMounted(() => fitToMarkers())
watch(() => props.refitKey, () => fitToMarkers())
watch(withCoords, () => fitToMarkers())
</script>

<style scoped>
.map-full { width: 100%; height: 100%; }
</style>
