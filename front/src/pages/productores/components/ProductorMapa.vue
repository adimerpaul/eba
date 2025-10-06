<template>
  <q-card-section class="q-pa-none">
    <div style="height: 420px; border-radius: 8px; overflow: hidden">
      <l-map
        ref="map"
        :zoom="zoom"
        :center="initialCenter"
        :options="mapOptions"
        style="height: 100%; width: 100%"
      >
        <l-control-layers position="topright" />

        <!-- Capas base -->
        <l-tile-layer
          layer-type="base"
          name="OpenStreetMap"
          url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
          :max-zoom="19"
          :visible="true"
        />
        <l-tile-layer
          layer-type="base"
          name="Google Calle"
          url="https://mt1.google.com/vt/lyrs=r&x={x}&y={y}&z={z}"
          :max-zoom="21"
          :visible="false"
        />
        <l-tile-layer
          layer-type="base"
          name="Google Satélite"
          url="https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}"
          :max-zoom="21"
          :visible="false"
        />
        <l-tile-layer
          layer-type="base"
          name="Google Híbrido"
          url="https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}"
          :max-zoom="21"
          :visible="false"
        />

        <!-- Marcadores de apiarios -->
        <l-marker
          v-for="a in apiariosConLatLng"
          :key="a.id"
          :lat-lng="[a.lat, a.lng]"
        >
          <l-popup>
            <div style="min-width: 180px">
              <div class="text-bold">{{ a.nombre_cip || ('Apiario #' + a.id) }}</div>
              <div>{{ a.lugar_apiario || '-' }}</div>
              <div class="text-grey-7" style="font-size:12px">
                Lat: {{ a.lat.toFixed(6) }}<br>
                Lng: {{ a.lng.toFixed(6) }}
              </div>
              <div v-if="a.estado" class="q-mt-xs">
                Estado: <q-badge :color="a.estado==='ACTIVO' ? 'green' : 'grey'" text-color="white" rounded>{{ a.estado }}</q-badge>
              </div>
            </div>
          </l-popup>
        </l-marker>
      </l-map>
    </div>
  </q-card-section>
</template>

<script>
// Usa @vue-leaflet/vue-leaflet (wrapper Vue 3)
import { LMap, LTileLayer, LMarker, LPopup, LControlLayers } from '@vue-leaflet/vue-leaflet'
import 'leaflet/dist/leaflet.css'
import L from 'leaflet'

// Fix de iconos (Vite/Webpack)
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png'
import markerIcon   from 'leaflet/dist/images/marker-icon.png'
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
    // centro por defecto (si no hay apiarios válidos)
    defaultCenter: { type: Array, default: () => [-16.5, -68.15] },
    defaultZoom: { type: Number, default: 13 }
  },

  data () {
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
    mapObj () { return this.$refs.map?.leafletObject || null },

    // Apiarios con lat/lng válidos (strings -> number)
    apiariosConLatLng () {
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
    // Cuando cambien los datos del productor, reencuadra el mapa
    productor: {
      deep: true,
      handler () { this.$nextTick(() => this.fitToMarkers()) }
    }
  },

  mounted () {
    // Ajusta al montar (útil si está dentro de un QDialog/Tab)
    this.$nextTick(() => this.fitToMarkers())
  },

  methods: {
    refresh () {
      // Método público (llámalo al abrir un QDialog): this.$refs.productorMapa.refresh()
      const map = this.mapObj
      if (!map) return
      setTimeout(() => { map.invalidateSize(); this.fitToMarkers(true) }, 60)
    },

    fitToMarkers (keepZoom = false) {
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
      // padding para que no queden pegados al borde
      map.fitBounds(bounds.pad(0.2), { animate: false })
    }
  }
}
</script>

<style scoped>
/* nada extra */
</style>
