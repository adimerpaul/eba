<template>
  <q-page class="q-pa-md">
    <div class="row items-center q-gutter-sm q-mb-sm">
      <q-avatar icon="groups" color="primary" text-color="white" />
      <div class="text-h6 text-weight-bold">Organizaciones</div>
      <q-space />
      <q-btn color="primary" icon="refresh" label="Actualizar árbol" @click="loadTree" :loading="loadingTree" no-caps />
    </div>

    <q-card flat bordered class="q-mb-md">
      <q-card-section class="row items-center q-col-gutter-sm">
        <div class="col-12 col-md-3">
          <q-input v-model="filters.search" dense outlined placeholder="Buscar (nombre, asociación, presidente...)"
                   @keyup.enter="fetchRows">
            <template #append><q-icon name="search" /></template>
          </q-input>
        </div>

        <div class="col-12 col-md-3">
          <q-select v-model="filters.departamento_id" :options="depOptions" option-label="label" option-value="value"
                    emit-value map-options dense outlined label="Departamento" @update:model-value="onDepChange" clearable />
        </div>
        <div class="col-12 col-md-3">
          <q-select v-model="filters.provincia_id" :options="provOptions" option-label="label" option-value="value"
                    emit-value map-options dense outlined label="Provincia" @update:model-value="onProvChange"
                    clearable
                    :disable="!filters.departamento_id" />
        </div>
        <div class="col-12 col-md-3">
          <q-select v-model="filters.municipio_id" :options="munOptions" option-label="label" option-value="value" clearable
                    emit-value map-options dense outlined label="Municipio" :disable="!filters.provincia_id" />
        </div>

        <div class="col-12 col-md-2">
          <q-select v-model="filters.estado" :options="['ACTIVO','INACTIVO']" dense outlined label="Estado" clearable />
        </div>

        <div class="col-12 col-md-6 q-gutter-sm">
          <q-btn color="secondary" icon="refresh" label="Actualizar" :loading="loading" @click="fetchRows" no-caps />
          <q-btn color="positive" icon="add_circle" label="Nueva" :loading="saving" @click="openNew" no-caps />
          <q-btn-dropdown label="Reportes" color="primary" no-caps>
            <q-list>
<!--              repote de activos inactivos-->
              <q-item clickable v-close-popup @click="reporte('ACTIVO')">
                <q-item-section avatar><q-icon name="description" /></q-item-section>
                <q-item-section>Reporte Activos</q-item-section>
              </q-item>
              <q-item clickable v-close-popup @click="reporte('INACTIVO')">
                <q-item-section avatar><q-icon name="description" /></q-item-section>
                <q-item-section>Reporte Inactivos</q-item-section>
              </q-item>
            </q-list>
          </q-btn-dropdown>
        </div>
      </q-card-section>
    </q-card>

    <q-card flat bordered>
      <q-markup-table dense wrap-cells>
        <thead>
        <tr class="bg-primary text-white">
          <th style="width: 140px;">Opciones</th>
          <th>Organización</th>
          <th>Archivo</th>
          <th>Asociación</th>
          <th>Presidente</th>
          <th>Departamento</th>
          <th>Provincia</th>
          <th>Municipio</th>
          <th class="text-right"># Apicultores</th>
          <th class="text-right"># Colmenas</th>
          <th>PJ</th>
          <th>Convenio</th>
          <th>Estado</th>
          <th>Registro</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="(row,i) in rows" :key="row.id">
          <td>
            <q-btn-dropdown dense color="primary" :label="`Opciones (${i+1})`" no-caps size="10px">
              <q-list>
                <q-item clickable v-close-popup @click="openEdit(row)">
                  <q-item-section avatar><q-icon name="edit" /></q-item-section>
                  <q-item-section>Editar</q-item-section>
                </q-item>
                <q-item clickable v-close-popup @click="remove(row)">
                  <q-item-section avatar><q-icon name="delete" color="negative" /></q-item-section>
                  <q-item-section>Eliminar</q-item-section>
                </q-item>
<!--                subir fecha archio -->
                <q-item clickable v-close-popup @click="subirArchivo(row)">
                  <q-item-section avatar><q-icon name="file_upload" /></q-item-section>
                  <q-item-section>Subir archivo</q-item-section>
                </q-item>
              </q-list>
            </q-btn-dropdown>
          </td>
          <td class="text-weight-medium">{{ row.nombre_organiza }}</td>
          <td>
            <div v-if="row.url">
              <a :href="openUrl(row)" target="_blank" rel="noopener noreferrer">Ver archivo</a>
            </div>
            <div v-else>
              -
            </div>
          </td>
          <td>{{ row.asociacion }}</td>
          <td>{{ row.nombre_presidente || '-' }}</td>
          <td>{{ row.departamento?.nombre_departamento || '-' }}</td>
          <td>{{ row.provincia?.nombre_provincia || '-' }}</td>
          <td>{{ row.municipio?.nombre_municipio || '-' }}</td>
          <td class="text-right">{{ row.num_apicultor ?? 0 }}</td>
          <td class="text-right">{{ row.num_colmena ?? 0 }}</td>
          <td>{{ row.pj_actual ?? 0 }}</td>
          <td>{{ row.convenio ?? 0 }}</td>
          <td>
            <q-chip :color="row.estado === 'ACTIVO' ? 'green' : 'red'" text-color="white" size="xs" class="text-bold">
              {{ row.estado }}
            </q-chip>
          </td>
          <td>{{ row.fecha_registro }}</td>
        </tr>
        </tbody>
      </q-markup-table>
    </q-card>

    <!-- Diálogo crear/editar -->
    <q-dialog v-model="dialog" persistent>
      <q-card style="min-width: 520px; max-width: 95vw">
        <q-card-section class="row items-center q-gutter-sm">
          <div class="text-h6">{{ form.id ? 'Editar Organización' : 'Nueva Organización' }}</div>
          <q-space /><q-btn flat round dense icon="close" v-close-popup />
        </q-card-section>
        <q-separator />
        <q-card-section>
          <q-form @submit="submit">
            <div class="row q-col-gutter-sm">
              <div class="col-12">
                <q-input v-model="form.nombre_organiza" label="Nombre de la organización" dense outlined
                         :rules="[v=>!!v || 'Requerido']" />
              </div>
              <div class="col-12 col-sm-6">
                <q-input v-model="form.asociacion" label="Asociación" dense outlined :rules="[v=>!!v || 'Requerido']" />
              </div>
              <div class="col-12 col-sm-6">
                <q-input v-model="form.programa" label="Programa" dense outlined />
              </div>

              <div class="col-12 col-sm-6">
                <q-input v-model="form.nombre_presidente" label="Nombre Presidente" dense outlined />
              </div>
              <div class="col-12 col-sm-6">
                <q-input v-model="form.celular" label="Celular" dense outlined />
              </div>

              <div class="col-12">
                <q-input type="textarea" v-model="form.descripcion" label="Descripción" dense outlined autogrow />
              </div>

              <div class="col-6 col-sm-3">
                <q-input v-model.number="form.num_apicultor" type="number" label="# Apicultores" dense outlined />
              </div>
              <div class="col-6 col-sm-3">
                <q-input v-model.number="form.num_colmena" type="number" label="# Colmenas" dense outlined />
              </div>
              <div class="col-6 col-sm-3">
                <q-input v-model.number="form.pj_actual" type="number" label="PJ actual" dense outlined />
              </div>
              <div class="col-6 col-sm-3">
                <q-input v-model.number="form.convenio" type="number" label="Convenio" dense outlined />
              </div>

              <!-- Ubicación administrativa -->
              <div class="col-12 col-sm-4">
                <q-select v-model="form.departamento_id" :options="depOptions" option-label="label" option-value="value"
                          emit-value map-options dense outlined label="Departamento"
                          :rules="[v=>!!v || 'Requerido']"
                          @update:model-value="onFormDepChange" />
              </div>
              <div class="col-12 col-sm-4">
                <q-select v-model="form.provincia_id" :options="formProvOptions" option-label="label" option-value="value"
                          emit-value map-options dense outlined label="Provincia"
                          :rules="[v=>!!v || 'Requerido']"
                          @update:model-value="onFormProvChange" :disable="!form.departamento_id" />
              </div>
              <div class="col-12 col-sm-4">
                <q-select v-model="form.municipio_id" :options="formMunOptions" option-label="label" option-value="value"
                          emit-value map-options dense outlined label="Municipio"
                          :rules="[v=>!!v || 'Requerido']"
                          :disable="!form.provincia_id" />
              </div>

              <div class="col-12 col-sm-6">
                <q-select v-model="form.estado" :options="['ACTIVO','INACTIVO']" label="Estado" dense outlined />
              </div>
              <div class="col-12 col-sm-6">
                <q-input v-model="form.fecha_registro" type="date" label="Fecha registro" dense outlined />
              </div>
            </div>

            <div class="text-right q-mt-md">
              <q-btn flat label="Cancelar" color="negative" v-close-popup :loading="saving" />
              <q-btn label="Guardar" color="primary" type="submit" :loading="saving" class="q-ml-sm" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
    <q-dialog v-model="dialogSubirArchivo" persistent>
      <q-card style="min-width: 400px; max-width: 90vw">
        <q-card-section class="row items-center q-gutter-sm">
          <div class="text-h6">Subir archivo para {{ form.nombre_organiza }}</div>
          <q-space /><q-btn flat round dense icon="close" v-close-popup />
        </q-card-section>
        <q-separator />
        <q-card-section>
          <q-form>
            <q-file
              filled
              v-model="file"
              label="Seleccionar archivo"
              accept=".pdf,.doc,.docx,.xls,.xlsx"
              dense
              outlined
              style="width: 100%;"
            />
            <div class="text-right q-mt-md">
              <q-btn flat label="Cancelar" color="negative" v-close-popup />
              <q-btn label="Subir" color="primary" class="q-ml-sm" @click="actualizarArchivo" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
export default {
  name: 'OrganizacionesPage',
  data () {
    return {
      rows: [],
      dialogSubirArchivo: false,
      file: null,
      loading: false,
      saving: false,
      dialog: false,

      loadingTree: false,
      tree: [],

      filters: {
        search: '',
        departamento_id: null,
        provincia_id: null,
        municipio_id: null,
        estado: null,
      },

      form: {
        id: null,
        nombre_organiza: '',
        asociacion: '',
        programa: '',
        nombre_presidente: '',
        descripcion: '',
        celular: '',
        num_apicultor: 0,
        num_colmena: 0,
        pj_actual: 0,
        convenio: 0,
        estado: 'ACTIVO',
        fecha_registro: null,
        departamento_id: null,
        provincia_id: null,
        municipio_id: null,
      },
    }
  },
  computed: {
    depOptions () {
      return (this.tree || []).map(d => ({ label: d.nombre_departamento, value: d.id }))
    },
    provOptions () {
      const depId = this.filters.departamento_id
      if (!depId) return []
      const dep = (this.tree || []).find(d => d.id === depId)
      return (dep?.provincias || []).map(p => ({ label: p.nombre_provincia, value: p.id }))
    },
    munOptions () {
      const depId = this.filters.departamento_id
      const provId = this.filters.provincia_id
      if (!depId || !provId) return []
      const dep = (this.tree || []).find(d => d.id === depId)
      const prov = (dep?.provincias || []).find(p => p.id === provId)
      return (prov?.municipios || []).map(m => ({ label: m.nombre_municipio, value: m.id }))
    },
    formProvOptions () {
      const depId = this.form.departamento_id
      if (!depId) return []
      const dep = (this.tree || []).find(d => d.id === depId)
      return (dep?.provincias || []).map(p => ({ label: p.nombre_provincia, value: p.id }))
    },
    formMunOptions () {
      const depId = this.form.departamento_id
      const provId = this.form.provincia_id
      if (!depId || !provId) return []
      const dep = (this.tree || []).find(d => d.id === depId)
      const prov = (dep?.provincias || []).find(p => p.id === provId)
      return (prov?.municipios || []).map(m => ({ label: m.nombre_municipio, value: m.id }))
    },
  },
  mounted () {
    this.loadTree()
    this.fetchRows()
  },
  methods: {
    reporte(estado) {
      this.$axios.get('organizaciones/reportActivos/'+estado, {
        responseType: 'blob'
      }).then((response) => {
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'reporte_organizaciones_activas.pdf');
        document.body.appendChild(link);
        link.click();
      }).catch((e) => {
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo generar el reporte.');
      });
    },
    openUrl(row) {
      return this.$axios.defaults.baseURL.replace(/\/+$/,'') + '/../' + row.url.replace(/^\/+/,'');
    },
    actualizarArchivo() {
      if (!this.file) {
        this.$alert?.error?.('Por favor, seleccione un archivo para subir.');
        return;
      }

      const formData = new FormData();
      formData.append('file', this.file);

      this.saving = true;
      // Route::post('/uploadFileUrl/{organizacion}', [OrganizacionController::class, 'uploadFileUrl']);
      this.$axios.post(`uploadFileUrl/${this.form.id}`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(() => {
        this.$alert?.success?.('Archivo subido correctamente.');
        this.dialogSubirArchivo = false;
        this.file = null;
        this.fetchRows();
      })
      .catch((e) => {
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo subir el archivo.');
      })
      .finally(() => {
        this.saving = false;
      });
    },
    async loadTree () {
      this.loadingTree = true
      try {
        const { data } = await this.$axios.get('geo/tree')
        this.tree = data
      } catch (e) {
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo cargar el árbol geográfico')
      } finally {
        this.loadingTree = false
      }
    },
    async fetchRows () {
      this.loading = true
      try {
        const { data } = await this.$axios.get('organizaciones', {
          params: {
            search: this.filters.search || undefined,
            departamento_id: this.filters.departamento_id || undefined,
            provincia_id: this.filters.provincia_id || undefined,
            municipio_id: this.filters.municipio_id || undefined,
            estado: this.filters.estado || undefined,
          }
        })
        this.rows = data
      } catch (e) {
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo cargar organizaciones')
      } finally {
        this.loading = false
      }
    },
    onDepChange () {
      this.filters.provincia_id = null
      this.filters.municipio_id = null
      this.fetchRows()
    },
    onProvChange () {
      this.filters.municipio_id = null
      this.fetchRows()
    },
    openNew () {
      this.form = {
        id: null,
        nombre_organiza: '',
        asociacion: '',
        programa: '',
        nombre_presidente: '',
        descripcion: '',
        celular: '',
        num_apicultor: 0,
        num_colmena: 0,
        pj_actual: 0,
        convenio: 0,
        estado: 'ACTIVO',
        fecha_registro: new Date().toISOString().slice(0,10),
        departamento_id: this.filters.departamento_id || null,
        provincia_id: this.filters.provincia_id || null,
        municipio_id: this.filters.municipio_id || null,
      }
      this.dialog = true
    },
    openEdit (row) {
      this.form = {
        id: row.id,
        nombre_organiza: row.nombre_organiza,
        asociacion: row.asociacion,
        programa: row.programa,
        nombre_presidente: row.nombre_presidente,
        descripcion: row.descripcion,
        celular: row.celular,
        num_apicultor: row.num_apicultor ?? 0,
        num_colmena: row.num_colmena ?? 0,
        pj_actual: row.pj_actual ?? 0,
        convenio: row.convenio ?? 0,
        estado: row.estado || 'ACTIVO',
        fecha_registro: row.fecha_registro || new Date().toISOString().slice(0,10),
        departamento_id: row.departamento?.id || row.municipio?.departamento_id || null,
        provincia_id: row.provincia?.id || row.municipio?.provincia_id || null,
        municipio_id: row.municipio?.id || row.municipio_id || null,
      }
      this.dialog = true
    },
    async submit () {
      this.saving = true
      try {
        const payload = { ...this.form }
        const send = {
          municipio_id: payload.municipio_id,
          nombre_organiza: payload.nombre_organiza,
          asociacion: payload.asociacion,
          programa: payload.programa,
          nombre_presidente: payload.nombre_presidente,
          descripcion: payload.descripcion,
          celular: payload.celular,
          num_apicultor: payload.num_apicultor,
          num_colmena: payload.num_colmena,
          pj_actual: payload.pj_actual,
          convenio: payload.convenio,
          estado: payload.estado,
          fecha_registro: payload.fecha_registro,
        }

        if (payload.id) {
          await this.$axios.put(`organizaciones/${payload.id}`, send)
          this.$alert?.success?.('Actualizado')
        } else {
          await this.$axios.post('organizaciones', send)
          this.$alert?.success?.('Creado')
        }
        this.dialog = false
        this.fetchRows()
      } catch (e) {
        this.$alert?.error?.(e.response?.data?.message || 'No se pudo guardar')
      } finally {
        this.saving = false
      }
    },
    subirArchivo(row) {
      this.dialogSubirArchivo = true;
      this.form = {
        id: row.id,
        nombre_organiza: row.nombre_organiza,
      };
    },
    remove (row) {
      this.$alert?.dialog?.(`¿Eliminar la organización "${row.nombre_organiza}"?`)?.onOk(async () => {
        this.loading = true
        try {
          await this.$axios.delete(`organizaciones/${row.id}`)
          this.$alert?.success?.('Eliminado')
          this.fetchRows()
        } catch (e) {
          this.$alert?.error?.(e.response?.data?.message || 'No se pudo eliminar')
        } finally {
          this.loading = false
        }
      })
    }
  }
}
</script>
