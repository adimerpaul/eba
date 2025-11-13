<template>
  <q-form @submit="submit" class="q-gutter-md">
    <q-banner v-if="banner" class="bg-yellow-1 text-yellow-10">
      {{ banner }}
    </q-banner>

    <div class="row q-col-gutter-sm">
      <!-- RUNSA / Sub código -->
      <div class="col-6 col-sm-2">
        <q-input v-model="form.runsa" label="RUNSA" dense outlined />
      </div>
      <div class="col-6 col-sm-2">
        <q-input v-model="form.sub_codigo" label="Sub Código" dense outlined />
      </div>

      <!-- Nombre / Apellidos -->
      <div class="col-12 col-sm-4">
        <q-input v-model="form.nombre" label="Nombre" dense outlined :rules="[v => !!v || 'Requerido']" />
      </div>
      <div class="col-12 col-sm-4">
        <q-input v-model="form.apellidos" label="Apellidos" dense outlined :rules="[v => !!v || 'Requerido']" />
      </div>

      <!-- CI / Expedido / Nacimiento / Sexo -->
      <div class="col-6 col-sm-3">
        <q-input v-model="form.numcarnet" label="N° Carnet" dense outlined :rules="[v => !!v || 'Requerido']" />
      </div>
      <div class="col-6 col-sm-2">
        <q-select v-model="form.expedido" label="Expedido" dense outlined :options="dept" />
      </div>
      <div class="col-12 col-sm-3">
        <q-input v-model="form.fec_nacimiento" type="date" label="Nacimiento" dense outlined />
      </div>
      <div class="col-12 col-sm-4">
        <q-select v-model="form.sexo" :options="sexoOptions" label="Sexo" dense outlined emit-value map-options />
      </div>

      <!-- Dirección -->
      <div class="col-12">
        <q-input v-model="form.direccion" type="textarea" label="Dirección" dense outlined autogrow />
      </div>

      <!-- Comunidad / Proveedor / CIP -->
      <div class="col-12 col-sm-4">
        <q-input v-model="form.comunidad" label="Comunidad" dense outlined />
      </div>
      <!-- <div class="col-12 col-sm-4">
        <q-select v-model="form.proveedor" label="Proveedor" :options="tipos" dense outlined />
      </div>-->
      <div class="col-12 col-sm-4">
        <q-input v-model="form.cip_acopio" label="CIP Acopio" dense outlined />
      </div>

      <!-- Celular / Ocupación / Selección -->
      <div class="col-12 col-sm-4">
        <q-input v-model="form.num_celular" label="Celular" dense outlined />
      </div>
      <!--<div class="col-12 col-sm-4">
        <q-input v-model="form.ocupacion" label="Ocupación" dense outlined />
      </div>-->
      <div class="col-12 col-sm-4">
        <q-input v-model.number="form.seleccion" type="number" label="Selección" dense outlined />
      </div>

      <!-- Organización (buscador remoto simple) -->
      <div class="col-12 col-sm-4">
        <q-select
          v-model="form.organizacion"
          label="Organización"
          dense outlined
          use-input fill-input input-debounce="300"
          :options="orgOptions"
          option-label="nombre_organiza"
          
          emit-value map-options
          clearable
          @filter="filterOrganizaciones"
          :loading="orgLoading"
          hint="Escribe para buscar"
        >
          <template #no-option>
            <q-item><q-item-section class="text-grey">Sin resultados</q-item-section></q-item>
          </template>
        </q-select>
      </div>
      <div class="col-12 col-sm-4"><b>DEPT:</b> {{ form.organizacion?.departamento.nombre_departamento }}</div>
      <div class="col-12 col-sm-4"><b>Mun:</b> {{ form.organizacion?.municipio.nombre_municipio }}</div>
      <div class="col-12 col-sm-4"><b>Prov:</b> {{ form.organizacion?.provincia.nombre_provincia }}</div>
      <!-- Ubicación administrativa 
      <div class="col-12 col-sm-4">
        <q-select
          v-model="form.departamento_id"
          :options="depOptions"
          option-label="label"
          option-value="value"
          emit-value map-options dense outlined
          label="Departamento"
          @update:model-value="onDepChange"
        />
      </div>
      <div class="col-12 col-sm-4">
        <q-select
          v-model="form.provincia_id"
          :options="provOptions"
          option-label="label"
          option-value="value"
          emit-value map-options dense outlined
          label="Provincia"
          :disable="!form.departamento_id"
          @update:model-value="onProvChange"
        />
      </div>
      <div class="col-12 col-sm-4">
        <q-select
          v-model="form.municipio_id"
          :options="munOptions"
          option-label="label"
          option-value="value"
          emit-value map-options dense outlined
          label="Municipio"
          :rules="[v => !!v || 'Requerido']"
          :disable="!form.provincia_id"
        />
      </div>-->

      <!-- Fechas / Estado -->
      <div class="col-12 col-sm-4">
        <q-input v-model="form.fecha_registro" type="date" label="Fecha Registro" dense outlined />
      </div>
      <div class="col-12 col-sm-4">
        <q-input v-model="form.fecha_expiracion" type="date" label="Fecha Expiración" dense outlined />
      </div>
      <div class="col-12 col-sm-4">
        <q-select v-model="form.estado" :options="['VIGENTE','VENCIDO','ACTIVO','INACTIVO']" label="Estado" dense outlined />
      </div>

      <!-- Otros -->
      <div class="col-12">
        <q-input v-model="form.otros" type="textarea" label="Otros" dense outlined autogrow />
      </div>
    </div>

    <div class="text-right q-gutter-sm">
      <q-btn flat color="negative" label="Cancelar" :disable="saving" @click="$emit('cancel')" />
      <q-btn color="primary" :label="form.id ? 'Guardar cambios' : 'Crear productor'" type="submit" :loading="saving" />
    </div>
  </q-form>
</template>

<script>
export default {
  name: 'ProductorForm',
  props: {
    // En editar envías el objeto; en crear no envíes nada
    productor: { type: Object, default: null },
    // Puedes pasar el árbol ya cargado; si no, el form lo carga solo
    tree: { type: Array, default: () => [] },
    autoLoadTree: { type: Boolean, default: true },
    bannerMsg: { type: String, default: '' }
  },
  emits: ['saved', 'cancel', 'error'],
  data () {
    return {
      tipos: ['Pequeño Productor','Productor Individual','Organizacion de Productores','Asociacion'],
      dept:['LPZ','CBB','SCZ','ORU','POT','PND','BEN','TAR','SUC','OTRO'],
      localTree: [],
      saving: false,
      banner: this.bannerMsg,

      orgOptions: [],
      orgLoading: false,

      form: this.emptyForm(),

      sexoOptions: [
        { label: 'Masculino', value: 1 },
        { label: 'Femenino', value: 2 },
      ]
    }
  },
  computed: {
    treeToUse () {
      return (this.tree && this.tree.length) ? this.tree : this.localTree
    },
    depOptions () {
      return (this.treeToUse || []).map(d => ({ label: d.nombre_departamento, value: d.id }))
    },
    provOptions () {
      const depId = this.form.departamento_id
      if (!depId) return []
      const dep = (this.treeToUse || []).find(d => d.id === depId)
      return (dep?.provincias || []).map(p => ({ label: p.nombre_provincia, value: p.id }))
    },
    munOptions () {
      const depId = this.form.departamento_id
      const provId = this.form.provincia_id
      if (!depId || !provId) return []
      const dep = (this.treeToUse || []).find(d => d.id === depId)
      const prov = (dep?.provincias || []).find(p => p.id === provId)
      return (prov?.municipios || []).map(m => ({ label: m.nombre_municipio, value: m.id }))
    }
  },
  watch: {
    productor: {
      handler () { this.initFromProp() },
      immediate: true
    }
  },
  mounted () {
    if (!this.tree?.length && this.autoLoadTree) {
      this.loadTree()
    }
  },
  methods: {
    emptyForm () {
      return {
        id: null,
        municipio_id: null,
        runsa: '0',
        sub_codigo: '',
        nombre: '',
        apellidos: '',
        numcarnet: '',
        expedido: '',
        fec_nacimiento: null,
        sexo: null,
        direccion: '',
        comunidad: '',
        proveedor: '',
        cip_acopio: '',
        num_celular: '',
        ocupacion: '',
        otros: '',
        seleccion: 0,
        organizacion_id: null,
        fecha_registro: new Date().toISOString().slice(0,10),
        fecha_expiracion: null,
        estado: 'VIGENTE',
        // selects dependientes
        departamento_id: null,
        provincia_id: null
      }
    },

    async loadTree () {
      try {
        const { data } = await this.$axios.get('geo/tree')
        this.localTree = data
      } catch (e) {
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo cargar el árbol geográfico')
      }
    },

    initFromProp () {
      const p = this.productor
      console.log(p);
      if (!p) {
        this.form = this.emptyForm()
        return
      }
      this.form = {
        id: p.id ?? null,
        //municipio_id: p.municipio?.id || p.municipio_id || null,
        runsa: p.runsa ?? '0',
        sub_codigo: p.sub_codigo ?? '',
        nombre: p.nombre ?? '',
        apellidos: p.apellidos ?? '',
        numcarnet: p.numcarnet ?? '',
        expedido: p.expedido ?? '',
        fec_nacimiento: p.fec_nacimiento ?? null,
        sexo: p.sexo ?? null,
        direccion: p.direccion ?? '',
        comunidad: p.comunidad ?? '',
        proveedor: p.proveedor ?? '',
        cip_acopio: p.cip_acopio ?? '',
        num_celular: p.num_celular ?? '',
        ocupacion: p.ocupacion ?? '',
        otros: p.otros ?? '',
        seleccion: p.seleccion ?? 0,
        organizacion_id: p.organizacion?.id || p.organizacion_id || null,
        organizacion:  p.organizacion || null,
        fecha_registro: p.fecha_registro ?? new Date().toISOString().slice(0,10),
        fecha_expiracion: p.fecha_expiracion ?? null,
        estado: p.estado ?? 'VIGENTE',
        //departamento_id: p.municipio?.departamento_id || null,
        provincia_id: p.municipio?.provincia_id || null
      }
    },

    onDepChange () {
      this.form.provincia_id = null
      this.form.municipio_id = null
    },
    onProvChange () {
      this.form.municipio_id = null
    },

    async filterOrganizaciones (val, update) {
      update(async () => {
        this.orgLoading = true
        try {
          const { data } = await this.$axios.get('organizaciones', {
            params: { search: val || undefined, paginate: false }
          })
          console.log(data)
          this.orgOptions = Array.isArray(data) ? data : (data?.data || [])
        } catch {
          this.orgOptions = []
        } finally {
          this.orgLoading = false
        }
      })
    },

    async submit () {
      this.saving = true
      try {
        const payload = {
          //municipio_id: this.form.municipio_id,
          runsa: this.form.runsa,
          sub_codigo: this.form.sub_codigo,
          nombre: this.form.nombre,
          apellidos: this.form.apellidos,
          numcarnet: this.form.numcarnet,
          expedido: this.form.expedido,
          fec_nacimiento: this.form.fec_nacimiento,
          sexo: this.form.sexo,
          direccion: this.form.direccion,
          comunidad: this.form.comunidad,
          proveedor: this.form.proveedor,
          cip_acopio: this.form.cip_acopio,
          num_celular: this.form.num_celular,
          ocupacion: this.form.ocupacion,
          otros: this.form.otros,
          seleccion: this.form.seleccion,
          organizacion_id: this.form.organizacion.id,
          fecha_registro: this.form.fecha_registro,
          fecha_expiracion: this.form.fecha_expiracion,
          estado: this.form.estado
        }

        let res
        if (this.form.id) {
          res = await this.$axios.put(`productores/${this.form.id}`, payload)
        } else {
          res = await this.$axios.post('productores', payload)
        }

        const saved = res.data
        if (!this.form.id) {
          this.$router.push('/productores/editar/' + saved.id)
        }
        this.$alert?.success?.(this.form.id ? 'Productor actualizado' : 'Productor creado')
        // this.$emit('saved', saved)
      } catch (e) {
        const msg = e.response?.data?.message || 'No se pudo guardar'
        this.$alert?.error?.(msg)
        this.$emit('error', e)
      } finally {
        this.saving = false
      }
    }
  }
}
</script>

<style scoped>
/* estilos mínimos */
</style>
