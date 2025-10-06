<!-- components/ApiarioMap.vue -->
<template>
  <div class="map-wrapper">
    <div class="row items-center q-col-gutter-sm q-mb-sm">
      <div class="col-6 col-sm-3">
        <q-input v-model.number="local.latitud" dense outlined label="Latitud" />
      </div>
      <div class="col-6 col-sm-3">
        <q-input v-model.number="local.longitud" dense outlined label="Longitud" />
      </div>
      <div class="col-6 col-sm-3">
        <q-btn dense no-caps color="primary" icon="place" label="Ir a lat/lng"
               @click="flyToLatLng" style="width:100%" />
      </div>
      <div class="col-6 col-sm-3">
        <q-btn dense no-caps color="secondary" icon="my_location" label="Mi ubicación"
               @click="locateMe" style="width:100%" />
      </div>
    </div>

    <l-map
      ref="map"
      :zoom="zoom"
      :center="mapCenter"
      :options="mapOptions"
      style="height: 360px; border-radius: 8px; overflow: hidden"
      @click="onMapClick"
    >
      <l-control-layers position="topright" />

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

      <l-marker
        v-if="hasLatLng"
        :lat-lng="[Number(local.latitud), Number(local.longitud)]"
        :draggable="true"
        @dragend="onDragEnd"
      >
        <l-popup>
          <div>Lat: {{ fix(local.latitud) }}<br>Lng: {{ fix(local.longitud) }}</div>
        </l-popup>
      </l-marker>
    </l-map>
  </div>
</template>

<script>
import { LMap, LTileLayer, LMarker, LPopup, LControlLayers } from '@vue-leaflet/vue-leaflet'
import 'leaflet/dist/leaflet.css'
import L from 'leaflet'

// Fix iconos
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
  name: 'ApiarioMap',
  components: { LMap, LTileLayer, LMarker, LPopup, LControlLayers },

  props: {
    modelValue: { type: Object, default: () => ({ latitud: null, longitud: null }) },
    center:     { type: Array,  default: () => [-16.5, -68.15] },
    zoomInit:   { type: Number, default: 14 }
  },
  emits: ['update:modelValue'],

  data () {
    return {
      zoom: this.zoomInit,
      local: { ...this.modelValue },
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
    hasLatLng () {
      const { latitud, longitud } = this.local
      return latitud != null && longitud != null && isFinite(latitud) && isFinite(longitud)
    },
    mapCenter () {
      return this.hasLatLng
        ? [Number(this.local.latitud), Number(this.local.longitud)]
        : this.center
    },
    mapObj () { return this.$refs.map?.leafletObject || null }
  },

  watch: {
    // padre -> hijo
    modelValue: {
      deep: true,
      handler (v) {
        if (!v) return
        if (v.latitud !== this.local.latitud || v.longitud !== this.local.longitud) {
          this.local = { ...v }
          this.$nextTick(() => this.refresh())
        }
      }
    },
    // hijo -> padre  (¡MERGE para no perder id ni otros campos!)
    local: {
      deep: true,
      handler (v) {
        const merged = {
          ...this.modelValue,               // conserva id, estado, etc.
          latitud:  v.latitud ?? null,
          longitud: v.longitud ?? null
        }
        this.$emit('update:modelValue', merged)
      }
    }
  },

  mounted () { this.$nextTick(() => this.refresh()) },

  methods: {
    fix (n) { const x = Number(n); return isFinite(x) ? x.toFixed(6) : '-' },

    refresh () {
      const map = this.mapObj
      if (!map) return
      setTimeout(() => {
        map.invalidateSize()
        if (this.hasLatLng) {
          map.setView(
            [Number(this.local.latitud), Number(this.local.longitud)],
            Math.max(map.getZoom() || this.zoom, 15),
            { animate: false }
          )
        }
      }, 60)
    },

    onMapClick (e) {
      const { lat, lng } = e.latlng || {}
      if (lat == null || lng == null) return
      this.local.latitud  = Number(lat.toFixed(7))
      this.local.longitud = Number(lng.toFixed(7))
      this.flyToLatLng()
    },

    onDragEnd (e) {
      const ll = e?.target?.getLatLng?.()
      if (!ll) return
      this.local.latitud  = Number(ll.lat.toFixed(7))
      this.local.longitud = Number(ll.lng.toFixed(7))
      this.flyToLatLng()
    },

    flyToLatLng () {
      const map = this.mapObj
      if (!map || !this.hasLatLng) return
      map.setView(
        [Number(this.local.latitud), Number(this.local.longitud)],
        Math.max(map.getZoom() || this.zoom, 16),
        { animate: false }
      )
    },

    locateMe () {
      if (!navigator.geolocation) return
      navigator.geolocation.getCurrentPosition(pos => {
        this.local.latitud  = Number(pos.coords.latitude.toFixed(7))
        this.local.longitud = Number(pos.coords.longitude.toFixed(7))
        this.$nextTick(() => this.flyToLatLng())
      })
    }
  }
}
</script>

<style scoped>
.map-wrapper { width: 100%; }
</style>
