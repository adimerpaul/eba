<template>
  <q-card-section class="q-pa-none">
    <div style="height: 580px; border-radius: 8px; overflow: hidden">
      <l-map ref="map" :zoom="zoom" :center="initialCenter" :options="mapOptions" style="height: 100%; width: 100%">
        <l-control-layers position="topright" />

        <!-- Capas base -->
        <l-tile-layer layer-type="base" name="OpenStreetMap" url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
          :max-zoom="19" :visible="false" />
        <l-tile-layer layer-type="base" name="Google Calle" url="https://mt1.google.com/vt/lyrs=r&x={x}&y={y}&z={z}"
          :max-zoom="21" :visible="false" />
        <l-tile-layer layer-type="base" name="Google Satélite" url="https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}"
          :max-zoom="21" :visible="false" />
        <l-tile-layer layer-type="base" name="Google Híbrido" url="https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}"
          :max-zoom="21" :visible="true" />

        <!-- Marcadores de apiarios -->
        <l-marker v-for="a in apiariosConLatLng" :key="a.id" :lat-lng="[a.lat, a.lng]">
          <l-popup>
            <div style="min-width: 180px">
              <div class="text-bold">{{ a.nombre_cip || ('Apiario #' + a.id) }}</div>
              <div>{{ a.lugar_apiario || '-' }}</div>
              <div class="text-grey-7" style="font-size:12px">
                Lat: {{ a.lat.toFixed(6) }}<br>
                Lng: {{ a.lng.toFixed(6) }}
              </div>
              <div v-if="a.estado" class="q-mt-xs">
                Estado:
                <q-badge :color="a.estado === 'ACTIVO' ? 'green' : 'grey'" text-color="white" rounded>
                  {{ a.estado }}
                </q-badge>
              </div>
            </div>
          </l-popup>
        </l-marker>
      </l-map>
    </div>
  </q-card-section>
</template>

<script>
import { LMap, LTileLayer, LMarker, LPopup, LControlLayers } from '@vue-leaflet/vue-leaflet'
import 'leaflet/dist/leaflet.css'
import L from 'leaflet'

// Fix de iconos (Vite/Webpack)
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png'
import markerIcon from 'leaflet/dist/images/marker-icon.png'
import markerShadow from 'leaflet/dist/images/marker-shadow.png'
delete L.Icon.Default.prototype._getIconUrl
L.Icon.Default.mergeOptions({
  iconRetinaUrl: markerIcon2x,
  iconUrl: markerIcon,
  shadowUrl: markerShadow
})

export default {
  name: 'ProductorMapa',
  components: { LMap, LTileLayer, LMarker, LPopup, LControlLayers },

  props: {
    productor: { type: Object, required: true },
    // Centro y zoom por defecto: Bolivia
    defaultCenter: { type: Array, default: () => [-16.29, -63.59] },
    defaultZoom: { type: Number, default: 6 }
  },

  data() {
    return {
      zoom: this.defaultZoom,
      initialCenter: this.defaultCenter,
      mapOptions: {
        zoomControl: true,
        attributionControl: false,
        zoomAnimation: false,
        fadeAnimation: false,
        markerZoomAnimation: false
      }
    }
  },

  computed: {
    mapObj() { return this.$refs.map?.leafletObject || null },

    apiariosConLatLng() {
      const arr = Array.isArray(this.productor?.apiarios) ? this.productor.apiarios : []
      return arr
        .map(a => {
          const lat = Number(a.latitud)
          const lng = Number(a.longitud)
          return (isFinite(lat) && isFinite(lng))
            ? { ...a, lat, lng }
            : null
        })
        .filter(Boolean)
    }
  },

  watch: {
    productor: {
      deep: true,
      handler() { this.$nextTick(() => this.fitToMarkers()) }
    }
  },

  mounted() {
    this.$nextTick(() => this.fitToMarkers())
  },

  methods: {
    // Llama a esto si el mapa se muestra dentro de un QDialog/Tab
    refresh() {
      const map = this.mapObj
      if (!map) return
      setTimeout(() => { map.invalidateSize(); this.fitToMarkers(true) }, 60)
    },

    fitToMarkers(keepZoom = false) {
      const map = this.mapObj
      if (!map) return

      const pts = this.apiariosConLatLng.map(a => [a.lat, a.lng])

      if (pts.length === 0) {
        map.setView(this.defaultCenter, this.defaultZoom, { animate: false })
        return
      }
      if (pts.length === 1) {
        const zoom = keepZoom ? (map.getZoom() || this.defaultZoom) : Math.max(map.getZoom() || this.defaultZoom, 15)
        map.setView(pts[0], zoom, { animate: false })
        return
      }

      const bounds = L.latLngBounds(pts)
      map.fitBounds(bounds.pad(0.2), { animate: false })
    }
  }
}
</script>

<style scoped>
/* nada extra */
</style>
