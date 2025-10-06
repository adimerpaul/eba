<template>
  <q-card-section>
    <div class="row items-center q-gutter-sm q-mb-sm">
      <div class="text-subtitle1">Apiarios</div>
      <q-space/>
      <q-btn dense color="primary" icon="add_circle" label="Nuevo" no-caps
             @click="startCreate" :disable="saving"/>
    </div>

    <q-markup-table dense>
      <thead>
      <tr class="bg-grey-3">
        <th class="text-left">#</th>
        <th class="text-left">Opciones</th>
        <th class="text-left">Nombre/CIP</th>
        <th class="text-left">Lugar</th>
        <th class="text-left">Ubicación</th>
        <th class="text-left">Colmenas</th>
        <th class="text-left">Estado</th>
        <th class="text-left">Instalación</th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="(a,i) in list" :key="a.id">
        <td>{{ i+1 }}</td>
        <td>
          <q-btn-dropdown dense label="Opciones" no-caps color="primary" size="10px">
            <q-list>
              <q-item clickable v-close-popup @click="startEdit(a)">
                <q-item-section avatar><q-icon name="edit"/></q-item-section>
                <q-item-section>Editar</q-item-section>
              </q-item>
              <q-item clickable v-close-popup @click="openColmenas(a)">
                <q-item-section avatar><q-icon name="hive"/></q-item-section>
                <q-item-section>Colmenas</q-item-section>
              </q-item>
              <q-item clickable v-close-popup @click="removeApiario(a)">
                <q-item-section avatar><q-icon class="text-negative" name="delete"/></q-item-section>
                <q-item-section class="text-negative">Eliminar</q-item-section>
              </q-item>
            </q-list>
          </q-btn-dropdown>
        </td>
        <td>{{ a.nombre_cip || '-' }}</td>
        <td>{{ a.lugar_apiario || '-' }}</td>
        <td>
          <span v-if="a.latitud && a.longitud">
            {{ a.latitud.toFixed ? a.latitud.toFixed(6) : a.latitud }},
            {{ a.longitud.toFixed ? a.longitud.toFixed(6) : a.longitud }}
          </span>
          <span v-else>-</span>
        </td>
        <td>{{ (a.colmenas ? a.colmenas.length : a.colmenas_count) ?? 0 }}</td>
        <td>
          <q-chip :color="a.estado==='ACTIVO'?'green':'grey'" text-color="white" size="xs" class="text-bold">
            {{ a.estado }}
          </q-chip>
        </td>
        <td>{{ a.fecha_instalacion || '-' }}</td>
      </tr>
      <tr v-if="!loading && list.length===0">
        <td colspan="8" class="text-center text-grey">Sin registros</td>
      </tr>
      </tbody>
    </q-markup-table>

    <!-- DIALOG: Crear/Editar Apiario -->
    <q-dialog v-model="dlgApiario.open" persistent transition-show="none" transition-hide="none"
              @show="onApiarioDialogShow">
      <q-card style="min-width: 900px; max-width: 95vw;">
        <q-card-section class="row items-center q-gutter-sm">
          <div class="text-h6">{{ formApiario.id ? 'Editar apiario' : 'Nuevo apiario' }}</div>
          <q-space/><q-btn flat round dense icon="close" v-close-popup/>
        </q-card-section>
        <q-separator/>

        <q-card-section>
          <q-form @submit="saveApiario" class="row q-col-gutter-sm">

            <div class="col-12 col-sm-4">
              <q-input v-model="formApiario.nombre_cip" label="Nombre / CIP" dense outlined/>
            </div>
            <div class="col-12 col-sm-8">
              <q-input v-model="formApiario.lugar_apiario" label="Lugar del apiario" dense outlined/>
            </div>

            <div class="col-6 col-sm-2">
              <q-input v-model.number="formApiario.altitud" label="Altitud (msnm)" dense outlined/>
            </div>
            <div class="col-6 col-sm-2">
              <q-input v-model="formApiario.fecha_instalacion" type="date" label="Instalación" dense outlined/>
            </div>
            <div class="col-12 col-sm-2">
              <q-select v-model="formApiario.estado" :options="['ACTIVO','INACTIVO']" label="Estado" dense outlined/>
            </div>

            <!-- MAPA -->
            <div class="col-12">
              <ApiarioMap
                ref="apiarioMap"
                v-model="formApiario"
                :center="[-16.5, -68.15]"
                :zoom-init="14"
              />
            </div>

            <div class="col-6 col-sm-3">
              <q-input v-model.number="formApiario.numero_colmenas_runsa" type="number" label="Colmenas RUNSA" dense outlined/>
            </div>
            <div class="col-6 col-sm-3">
              <q-input v-model.number="formApiario.numero_colmenas_prod" type="number" label="Colmenas PROD." dense outlined/>
            </div>
            <div class="col-6 col-sm-3">
              <q-input v-model.number="formApiario.rend_programa_nal" type="number" step="0.01" label="Rend. prog. nal" dense outlined/>
            </div>
            <div class="col-6 col-sm-3">
              <q-input v-model.number="formApiario.seleccion" type="number" label="Selección" dense outlined/>
            </div>

            <div class="col-12 text-right q-gutter-sm q-mt-sm">
              <q-btn flat color="grey" label="Cancelar" v-close-popup :disable="saving"/>
              <q-btn color="primary" :label="formApiario.id?'Guardar cambios':'Crear'" type="submit" :loading="saving"/>
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- DIALOG: Colmenas del apiario -->
    <q-dialog v-model="dlgColmena.open" persistent transition-show="none" transition-hide="none">
      <q-card style="min-width: 900px; max-width: 95vw;">
        <q-card-section class="row items-center q-gutter-sm">
          <div class="text-h6">Colmenas — {{ currentApiario?.nombre_cip || ('Apiario #'+currentApiario?.id) }}</div>
          <q-space/>
          <q-btn dense color="primary" icon="add_circle" label="Nueva colmena" no-caps @click="startCreateColmena"/>
          <q-btn flat round dense icon="close" v-close-popup/>
        </q-card-section>
        <q-separator/>

        <q-card-section>
          <q-markup-table dense>
            <thead>
            <tr class="bg-grey-3">
              <th>#</th><th>Opc.</th><th>Código</th><th>Tipo</th><th>Tipo miel</th>
              <th>Instalación</th><th>Reina nacimiento</th><th>Estado</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(c,i) in colmenas" :key="c.id || i">
              <td>{{ i+1 }}</td>
              <td>
                <q-btn-dropdown dense no-caps size="10px" label="Opciones" color="primary">
                  <q-list>
                    <q-item clickable v-close-popup @click="startEditColmena(c)">
                      <q-item-section avatar><q-icon name="edit"/></q-item-section>
                      <q-item-section>Editar</q-item-section>
                    </q-item>
                    <q-item clickable v-close-popup @click="removeColmena(c)">
                      <q-item-section avatar><q-icon class="text-negative" name="delete"/></q-item-section>
                      <q-item-section class="text-negative">Eliminar</q-item-section>
                    </q-item>
                  </q-list>
                </q-btn-dropdown>
              </td>
              <td>{{ c.codigo_colmena || '-' }}</td>
              <td>{{ c.tipo_colmena || '-' }}</td>
              <td>{{ c.tipo_miel?.tipo_miel || c.tipoMiel?.tipo_miel || '-' }}</td>
              <td>{{ c.fecha_instalacion || '-' }}</td>
              <td>{{ c.reina_fecha_nacimiento || '-' }}</td>
              <td><q-chip :color="c.estado==='ACTIVA'?'green':'grey'" text-color="white" size="xs">{{ c.estado }}</q-chip></td>
            </tr>
            <tr v-if="!colLoading && colmenas.length===0">
              <td colspan="8" class="text-center text-grey">Sin colmenas</td>
            </tr>
            </tbody>
          </q-markup-table>

          <!-- Form Colmena -->
          <q-separator class="q-my-md"/>
          <div class="text-subtitle2 q-mb-sm">{{ formColmena.id?'Editar colmena':'Nueva colmena' }}</div>
          <q-form @submit="saveColmena" class="row q-col-gutter-sm">
            <div class="col-12 col-sm-3"><q-input v-model="formColmena.codigo_colmena" dense outlined label="Código"/></div>
            <div class="col-12 col-sm-3"><q-input v-model="formColmena.tipo_colmena" dense outlined label="Tipo colmena"/></div>
            <div class="col-12 col-sm-3">
              <q-select v-model="formColmena.tipo_miel_id" :options="tipoMielOptions"
                        option-value="id" option-label="tipo_miel" emit-value map-options dense outlined label="Tipo de miel"/>
            </div>
            <div class="col-12 col-sm-3"><q-input v-model="formColmena.fecha_instalacion" type="date" dense outlined label="Instalación"/></div>
            <div class="col-12 col-sm-4"><q-input v-model="formColmena.reina_fecha_nacimiento" type="date" dense outlined label="Reina: nacimiento"/></div>
            <div class="col-12 col-sm-4"><q-input v-model="formColmena.reina_procedencia" dense outlined label="Reina: procedencia"/></div>
            <div class="col-12 col-sm-4"><q-select v-model="formColmena.estado" :options="['ACTIVA','INACTIVA']" dense outlined label="Estado"/></div>
            <div class="col-12 text-right q-gutter-sm">
              <q-btn flat color="grey" label="Limpiar" @click="resetColmena" :disable="saving"/>
              <q-btn color="primary" :label="formColmena.id?'Guardar cambios':'Agregar colmena'" type="submit" :loading="saving"/>
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-card-section>
</template>

<script>
import ApiarioMap from 'pages/productores/components/ApiarioMap.vue' // ajusta la ruta

export default {
  name: 'ProductorApiarios',
  components: { ApiarioMap },
  props: { productor: { type: Object, required: true } },
  emits: ['updated'],

  data () {
    return {
      loading: false,
      saving: false,
      list: [],
      dlgApiario: { open: false },
      formApiario: this.emptyApiario(),
      dlgColmena: { open: false },
      currentApiario: null,
      colmenas: [],
      colLoading: false,
      formColmena: this.emptyColmena(),
      tipoMielOptions: []
    }
  },

  mounted () { this.hydrateFromProductor() },
  watch: { productor () { this.hydrateFromProductor() } },

  methods: {
    hydrateFromProductor () {
      if (this.productor?.apiarios) this.list = this.productor.apiarios
      else this.fetchApiarios()
    },
    async fetchApiarios () {
      this.loading = true
      try {
        const { data } = await this.$axios.get('apiarios', {
          params: { productor_id: this.productor.id, paginate: false }
        })
        this.list = Array.isArray(data) ? data : (data?.data || [])
      } catch (e) {
        this.list = []
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo cargar apiarios')
      } finally { this.loading = false }
    },

    emptyApiario () {
      return {
        id: null,
        productor_id: this.productor?.id || null,
        municipio_id: null,
        nombre_cip: '',
        latitud: null,
        longitud: null,
        altitud: null,
        lugar_apiario: '',
        numero_colmenas_runsa: null,
        numero_colmenas_prod: 0,
        seleccion: 0,
        rend_programa_nal: 0,
        organizacion_id: 0,
        fecha_instalacion: null,
        estado: 'ACTIVO',
        fase: '',
        coordenada: ''
      }
    },
    startCreate () {
      this.formApiario = this.emptyApiario()
      this.dlgApiario.open = true
    },
    startEdit (row) {
      this.formApiario = { ...this.emptyApiario(), ...row, productor_id: this.productor?.id || row.productor_id }
      this.dlgApiario.open = true
    },
    onApiarioDialogShow () {
      this.$nextTick(() => this.$refs.apiarioMap?.refresh())
    },
    async saveApiario () {
      this.saving = true
      try {
        const payload = { ...this.formApiario, productor_id: this.productor.id }
        if (payload.id) await this.$axios.put(`apiarios/${payload.id}`, payload)
        else await this.$axios.post('apiarios', payload)
        this.$alert?.success?.(payload.id ? 'Apiario actualizado' : 'Apiario creado')
        this.dlgApiario.open = false
        this.$emit('updated')
        await this.fetchApiarios()
      } catch (e) {
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo guardar apiario')
      } finally { this.saving = false }
    },

    // --- Colmenas (igual que tenías) ---
    emptyColmena () { return { id:null, apiario_id:null, tipo_miel_id:null, codigo_colmena:'', tipo_colmena:'', fecha_instalacion:null, reina_fecha_nacimiento:null, reina_procedencia:'', estado:'ACTIVA' } },
    async openColmenas (apiario) { this.currentApiario = apiario; this.dlgColmena.open = true; await this.fetchTipoMiel(); await this.fetchColmenas() },
    async fetchTipoMiel () { try { const { data } = await this.$axios.get('tipo-miel'); this.tipoMielOptions = Array.isArray(data)?data:(data?.data||[]) } catch { this.tipoMielOptions = [] } },
    async fetchColmenas () {
      if (!this.currentApiario?.id) return
      this.colLoading = true
      try {
        const { data } = await this.$axios.get('colmenas', { params: { apiario_id: this.currentApiario.id, paginate: false } })
        this.colmenas = Array.isArray(data) ? data : (data?.data || [])
      } catch (e) {
        this.colmenas = []
        this.$alert?.error?.(e.response?.data?.message || 'No se pudieron cargar colmenas')
      } finally { this.colLoading = false }
    },
    startCreateColmena () { this.formColmena = this.emptyColmena(); this.formColmena.apiario_id = this.currentApiario?.id || null },
    startEditColmena (row) { this.formColmena = { ...this.emptyColmena(), ...row, apiario_id: this.currentApiario?.id || row.apiario_id } },
    resetColmena () { this.startCreateColmena() },
    async saveColmena () {
      this.saving = true
      try {
        const payload = { ...this.formColmena, apiario_id: this.currentApiario.id }
        if (payload.id) await this.$axios.put(`colmenas/${payload.id}`, payload)
        else await this.$axios.post('colmenas', payload)
        this.$alert?.success?.(payload.id ? 'Colmena actualizada' : 'Colmena creada')
        await this.fetchColmenas()
        this.$emit('updated')
      } catch (e) {
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo guardar colmena')
      } finally { this.saving = false }
    },
    removeColmena (row) {
      this.$alert?.dialog?.('¿Eliminar colmena?')?.onOk(async () => {
        try {
          await this.$axios.delete(`colmenas/${row.id}`)
          this.$alert?.success?.('Eliminado')
          await this.fetchColmenas()
          this.$emit('updated')
        } catch (e) {
          this.$alert?.error?.(e.response?.data?.message || 'No se pudo eliminar')
        }
      })
    }
  }
}
</script>

<style scoped>
/* nada especial */
</style>
