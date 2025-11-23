<template>
  <!-- 2025-11-23: Modal de registro rápido de productores -->
  <!-- Permite crear productores con campos esenciales sin salir de la vista principal -->
  <q-dialog v-model="showDialog" persistent>
    <q-card style="width: 700px; max-width: 90vw">
      <!-- Header del modal -->
      <q-card-section class="row items-center bg-primary text-white">
        <q-icon name="person_add" size="sm" class="q-mr-sm" />
        <div class="text-h6">Registro Rápido de Productor</div>
        <q-space />
        <q-btn icon="close" flat round dense @click="cerrar" />
      </q-card-section>

      <q-separator />

      <!-- Formulario de campos esenciales -->
      <q-card-section class="q-pt-md">
        <q-form @submit.prevent="guardar" ref="formRef">
          <div class="row q-col-gutter-md">
            <!-- Nombre (requerido) -->
            <div class="col-12 col-sm-6">
              <q-input 
                v-model="form.nombre" 
                label="Nombre *" 
                outlined 
                dense
                :rules="[v => !!v || 'El nombre es requerido']"
                maxlength="200"
              >
                <template #prepend>
                  <q-icon name="person" />
                </template>
              </q-input>
            </div>

            <!-- Apellidos (requerido) -->
            <div class="col-12 col-sm-6">
              <q-input 
                v-model="form.apellidos" 
                label="Apellidos *" 
                outlined 
                dense
                :rules="[v => !!v || 'Los apellidos son requeridos']"
                maxlength="200"
              >
                <template #prepend>
                  <q-icon name="badge" />
                </template>
              </q-input>
            </div>

            <!-- N° Carnet/CI (requerido) -->
            <div class="col-12 col-sm-6">
              <q-input 
                v-model="form.numcarnet" 
                label="N° Carnet/CI *" 
                outlined 
                dense
                :rules="[v => !!v || 'El CI es requerido']"
                maxlength="20"
                @blur="verificarDuplicados"
              >
                <template #prepend>
                  <q-icon name="credit_card" />
                </template>
              </q-input>
            </div>

            <!-- Expedido -->
            <div class="col-12 col-sm-6">
              <q-select 
                v-model="form.expedido" 
                label="Expedido" 
                outlined 
                dense
                :options="deptOptions"
              >
                <template #prepend>
                  <q-icon name="location_on" />
                </template>
              </q-select>
            </div>

            <!-- Sexo (2025-11-23: agregado por solicitud del usuario) -->
            <div class="col-12 col-sm-6">
              <q-select 
                v-model="form.sexo" 
                label="Sexo" 
                outlined 
                dense
                :options="sexoOptions"
                option-label="label"
                option-value="value"
                emit-value
                map-options
              >
                <template #prepend>
                  <q-icon name="wc" />
                </template>
              </q-select>
            </div>

            <!-- Organización (requerido - con búsqueda remota) -->
            <div class="col-12">
              <q-select 
                v-model="form.organizacion" 
                label="Organización *" 
                outlined 
                dense
                use-input 
                fill-input
                input-debounce="300"
                :options="orgOptions"
                option-label="nombre_organiza"
                :loading="orgLoading"
                :rules="[v => !!v || 'La organización es requerida']"
                @filter="filterOrganizaciones"
                hint="Escribe para buscar la organización"
              >
                <template #prepend>
                  <q-icon name="business" />
                </template>
                <template #no-option>
                  <q-item>
                    <q-item-section class="text-grey">
                      Sin resultados. Escribe para buscar...
                    </q-item-section>
                  </q-item>
                </template>
              </q-select>
            </div>

            <!-- Información de ubicación de la organización seleccionada -->
            <div v-if="form.organizacion" class="col-12">
              <q-banner dense class="bg-blue-1 text-blue-9">
                <template #avatar>
                  <q-icon name="info" color="blue" />
                </template>
                <div class="text-caption">
                  <strong>Ubicación:</strong> 
                  {{ form.organizacion.departamento?.nombre_departamento }} → 
                  {{ form.organizacion.provincia?.nombre_provincia }} → 
                  {{ form.organizacion.municipio?.nombre_municipio }}
                </div>
                <div class="text-caption q-mt-xs" v-if="form.organizacion.comunidad">
                  <strong>Comunidad:</strong> {{ form.organizacion.comunidad }}
                </div>
              </q-banner>
            </div>

            <!-- Comunidad (2025-11-23: se autocompleta desde organización) -->
            <div class="col-12 col-sm-6">
              <q-input 
                v-model="form.comunidad" 
                label="Comunidad" 
                outlined 
                dense
                maxlength="100"
                hint="Se autocompleta desde la organización"
              >
                <template #prepend>
                  <q-icon name="terrain" />
                </template>
              </q-input>
            </div>

            <!-- Celular (opcional pero recomendado) -->
            <div class="col-12 col-sm-6">
              <q-input 
                v-model="form.num_celular" 
                label="Celular" 
                outlined 
                dense
                maxlength="15"
                hint="Recomendado para contacto"
              >
                <template #prepend>
                  <q-icon name="phone" />
                </template>
              </q-input>
            </div>

            <!-- Estado (por defecto VIGENTE) -->
            <div class="col-12 col-sm-6">
              <q-select 
                v-model="form.estado" 
                label="Estado" 
                outlined 
                dense
                :options="['VIGENTE', 'VENCIDO', 'ACTIVO', 'INACTIVO']"
              >
                <template #prepend>
                  <q-icon name="check_circle" />
                </template>
              </q-select>
            </div>
          </div>

          <!-- Nota informativa -->
          <div class="q-mt-md">
            <q-banner dense class="bg-grey-2">
              <template #avatar>
                <q-icon name="lightbulb" color="orange" />
              </template>
              <div class="text-caption">
                <strong>Nota:</strong> Estás creando un registro rápido con los datos esenciales. 
                Puedes completar información adicional (dirección, fechas, RUNSA, etc.) después de guardar.
              </div>
            </q-banner>
          </div>
        </q-form>
      </q-card-section>

      <q-separator />

      <!-- Acciones del modal -->
      <q-card-actions align="right" class="q-pa-md">
        <q-btn 
          flat 
          label="Cancelar" 
          color="negative" 
          @click="cerrar" 
          :disable="saving"
        />
        <q-btn 
          unelevated 
          label="Guardar" 
          color="primary" 
          @click="guardar(false)" 
          :loading="saving"
          icon="save"
        />
        <q-btn 
          unelevated 
          label="Guardar y completar datos" 
          color="secondary" 
          @click="guardar(true)" 
          :loading="saving"
          icon="edit"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script>
export default {
  name: 'ProductorModalCrear',
  emits: ['created', 'close'],
  data() {
    return {
      showDialog: false,
      saving: false,
      
      // Opciones para selects
      deptOptions: ['LPZ', 'CBB', 'SCZ', 'ORU', 'POT', 'PND', 'BEN', 'TAR', 'SUC', 'OTRO'],
      sexoOptions: [
        { label: 'Masculino', value: 1 },
        { label: 'Femenino', value: 2 }
      ],
      
      // Búsqueda de organizaciones
      orgOptions: [],
      orgLoading: false,
      
      // Formulario con campos esenciales
      form: {
        nombre: '',
        apellidos: '',
        numcarnet: '',
        expedido: '',
        sexo: null,
        organizacion: null,
        comunidad: '',
        num_celular: '',
        estado: 'VIGENTE'
      }
    }
  },
  // 2025-11-23: Watcher para autocompletar comunidad desde organización
  watch: {
    'form.organizacion'(newOrg) {
      if (newOrg && newOrg.comunidad) {
        this.form.comunidad = newOrg.comunidad
      }
    }
  },
  methods: {
    // 2025-11-23: Abrir modal y resetear formulario
    abrir() {
      this.showDialog = true
      this.resetForm()
    },
    
    // 2025-11-23: Cerrar modal
    cerrar() {
      this.showDialog = false
      this.$emit('close')
    },
    
    // 2025-11-23: Resetear formulario a valores iniciales
    resetForm() {
      this.form = {
        nombre: '',
        apellidos: '',
        numcarnet: '',
        expedido: '',
        sexo: null,
        organizacion: null,
        comunidad: '',
        num_celular: '',
        estado: 'VIGENTE'
      }
      this.$refs.formRef?.resetValidation()
    },
    
    // 2025-11-23: Búsqueda remota de organizaciones con debounce
    async filterOrganizaciones(val, update) {
      update(async () => {
        this.orgLoading = true
        try {
          const { data } = await this.$axios.get('organizaciones', {
            params: { 
              search: val || undefined, 
              paginate: false 
            }
          })
          this.orgOptions = Array.isArray(data) ? data : (data?.data || [])
        } catch (e) {
          this.orgOptions = []
          this.$alert?.error?.('Error al buscar organizaciones')
        } finally {
          this.orgLoading = false
        }
      })
    },
    
    // 2025-11-23: Verificar duplicados por CI antes de guardar
    async verificarDuplicados() {
      if (!this.form.numcarnet) return false
      
      try {
        const { data } = await this.$axios.get('productores-verificar-duplicado', {
          params: { numcarnet: this.form.numcarnet }
        })
        
        if (data.existe) {
          this.$q.dialog({
            title: 'Productor ya registrado',
            message: `Ya existe un productor con este CI:
              
              Nombre: ${data.datos.nombre_completo}
              CI: ${data.datos.numcarnet}
              Estado: ${data.datos.estado}
              
              ¿Desea ver el registro existente?`,
            cancel: {
              label: 'Cancelar',
              flat: true,
              color: 'negative'
            },
            ok: {
              label: 'Ver registro',
              color: 'primary'
            },
            persistent: true
          }).onOk(() => {
            this.$router.push(`/productores/editar/${data.productor_id}`)
            this.cerrar()
          })
          return true
        }
        
        return false
      } catch (e) {
        return false
      }
    },
    
    // 2025-11-23: Guardar productor con datos esenciales
    async guardar(completarDatos = false) {
      // Validar formulario
      const valid = await this.$refs.formRef.validate()
      if (!valid) {
        this.$alert?.warning?.('Por favor completa todos los campos requeridos')
        return
      }
      
      // Verificar duplicados antes de crear
      const existeDuplicado = await this.verificarDuplicados()
      if (existeDuplicado) return
      
      this.saving = true
      try {
        // Preparar payload con campos esenciales
        // Los campos faltantes se llenan con valores por defecto en el backend
        // 2025-11-23: RUNSA como string vacío, no como '0'
        const payload = {
          nombre: this.form.nombre.trim(),
          apellidos: this.form.apellidos.trim(),
          numcarnet: this.form.numcarnet.trim(),
          expedido: this.form.expedido || '',
          sexo: this.form.sexo || null,
          organizacion_id: this.form.organizacion.id,
          comunidad: this.form.comunidad?.trim() || '',
          num_celular: this.form.num_celular?.trim() || '',
          estado: this.form.estado,
          // Valores por defecto (2025-11-23: RUNSA como string vacío)
          runsa: '',
          sub_codigo: '',
          ocupacion: 'APICULTOR',
          fecha_registro: new Date().toISOString().slice(0, 10),
          seleccion: 0
        }
        
        const { data } = await this.$axios.post('productores', payload)
        
        this.$alert?.success?.('Productor creado exitosamente')
        
        // Emitir evento con el productor creado
        this.$emit('created', data)
        
        // Si el usuario quiere completar datos, redirigir al formulario completo
        if (completarDatos) {
          this.$router.push(`/productores/editar/${data.id}`)
        }
        
        // Cerrar modal
        this.cerrar()
        
      } catch (e) {
        const msg = e.response?.data?.message || 'No se pudo crear el productor'
        this.$alert?.error?.(msg)
      } finally {
        this.saving = false
      }
    }
  }
}
</script>

<style scoped>
/* Estilos mínimos para el modal */
</style>
