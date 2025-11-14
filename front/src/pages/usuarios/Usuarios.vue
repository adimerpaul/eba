<template>
  <q-page class="q-pa-md">
    <!-- TABS -->
    <q-tabs
      v-model="tab"
      dense
      class="text-primary q-mb-md"
      align="left"
      active-color="primary"
      indicator-color="primary"
    >
      <q-tab name="internos" label="Usuarios internos" icon="badge"  no-caps />
      <q-tab name="externos" label="Usuarios externos" icon="person" no-caps />
    </q-tabs>

    <q-separator class="q-mb-md" />

    <q-tab-panels v-model="tab" animated>

      <!-- ========== TAB 1: USUARIOS EXTERNOS (TU CÓDIGO ACTUAL) ========== -->
      <q-tab-panel name="externos">
        <q-table
          :rows="users"
          :columns="columns"
          dense
          wrap-cells
          flat
          bordered
          :rows-per-page-options="[0]"
          title="Usuarios externos"
          :filter="filter"
        >
          <template v-slot:top-right>
            <q-btn
              color="positive"
              label="Nuevo"
              @click="userNew"
              no-caps
              icon="add_circle_outline"
              :loading="loading"
              class="q-mr-sm"
            />
            <q-btn
              color="primary"
              label="Actualizar"
              @click="usersGet"
              no-caps
              icon="refresh"
              :loading="loading"
              class="q-mr-sm"
            />
            <q-input v-model="filter" label="Buscar" dense outlined>
              <template v-slot:append>
                <q-icon name="search" />
              </template>
            </q-input>
          </template>

          <template v-slot:body-cell-actions="props">
            <q-td :props="props">
              <q-btn-dropdown label="Opciones" no-caps size="10px" dense color="primary">
                <q-list>
                  <q-item clickable @click="userEdit(props.row)" v-close-popup>
                    <q-item-section avatar>
                      <q-icon name="edit" />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>Editar</q-item-label>
                    </q-item-section>
                  </q-item>

                  <q-item clickable @click="userDelete(props.row.id)" v-close-popup>
                    <q-item-section avatar>
                      <q-icon name="delete" />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>Eliminar</q-item-label>
                    </q-item-section>
                  </q-item>

                  <q-item clickable @click="userEditPassword(props.row)" v-close-popup>
                    <q-item-section avatar>
                      <q-icon name="lock_reset" />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>Cambiar contraseña</q-item-label>
                    </q-item-section>
                  </q-item>

                  <q-item clickable @click="cambiarAvatar(props.row)" v-close-popup>
                    <q-item-section avatar>
                      <q-icon name="image" />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>Cambiar avatar</q-item-label>
                    </q-item-section>
                  </q-item>

                  <q-item clickable @click="permisosShow(props.row)" v-close-popup>
                    <q-item-section avatar>
                      <q-icon name="lock" />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>Permisos</q-item-label>
                    </q-item-section>
                  </q-item>
                </q-list>
              </q-btn-dropdown>
            </q-td>
          </template>

          <template v-slot:body-cell-role="props">
            <q-td :props="props">
              <q-chip
                :label="props.row.role"
                :color="$filters.color(props.row.role)"
                text-color="white"
                dense
                size="14px"
              />
            </q-td>
          </template>

          <template v-slot:body-cell-avatar="props">
            <q-td :props="props">
              <q-avatar rounded>
                <q-img
                  :src="`${$url}/../images/${props.row.avatar}`"
                  width="40px"
                  height="40px"
                  v-if="props.row.avatar"
                />
                <q-icon name="person" size="40px" v-else />
              </q-avatar>
            </q-td>
          </template>

          <template v-slot:body-cell-permissions="props">
            <q-td :props="props">
              <div class="row items-center q-col-gutter-xs">
                <!-- hasta 3 chips visibles -->
                <q-chip
                  v-for="(perm, idx) in (props.row.permissions || []).slice(0, 3)"
                  :key="perm.id"
                  dense
                  color="grey-3"
                  text-color="black"
                  size="12px"
                  class="q-mr-xs q-mb-xs"
                >
                  {{ perm.name }}
                </q-chip>

                <!-- si hay más, badge + tooltip con el listado completo -->
                <template v-if="(props.row.permissions || []).length > 3">
                  <q-badge outline color="primary" class="q-ml-xs">
                    +{{ (props.row.permissions || []).length - 3 }}
                    <q-tooltip anchor="top middle" self="bottom middle" :offset="[0,8]">
                      <div class="text-left">
                        <div
                          v-for="perm in props.row.permissions"
                          :key="perm.id"
                        >• {{ perm.name }}</div>
                      </div>
                    </q-tooltip>
                  </q-badge>
                </template>

                <!-- sin permisos -->
                <q-badge
                  v-if="!(props.row.permissions || []).length"
                  color="grey-5"
                  text-color="white"
                  outline
                >
                  Sin permisos
                </q-badge>
              </div>
            </q-td>
          </template>
        </q-table>

        <!-- Diálogo crear/editar usuario externo -->
        <!-- (igual que ya lo tienes) -->
        <!-- ... tus q-dialog de userDialog, cambioAvatarDialogo, dialogPermisos ... -->

        <!-- DIALOG CREAR/EDITAR USUARIO -->
        <!-- (copio lo esencial) -->
        <q-dialog v-model="userDialog" persistent>
          <q-card style="width: 400px">
            <q-card-section class="q-pb-none row items-center">
              <div>
                {{ actionUser }} user
              </div>
              <q-space />
              <q-btn icon="close" flat round dense @click="userDialog = false" />
            </q-card-section>
            <q-card-section class="q-pt-none">
              <q-form @submit="user.id ? userPut() : userPost()">
                <q-input
                  v-model="user.name"
                  label="Nombre"
                  dense
                  outlined
                  :rules="[val => !!val || 'Campo requerido']"
                />
                <q-input
                  v-model="user.username"
                  label="Usuario"
                  dense
                  outlined
                  :rules="[val => !!val || 'Campo requerido']"
                />
                <q-input v-model="user.email" label="Email" dense outlined hint=""/>
                <q-input
                  v-model="user.password"
                  label="Contraseña"
                  dense
                  outlined
                  :rules="[val => !!val || 'Campo requerido']"
                  v-if="!user.id"
                />
                <q-select
                  v-model="user.role"
                  label="Rol"
                  dense
                  outlined
                  :options="roles"
                  :rules="[val => !!val || 'Campo requerido']"
                />
                <div class="text-right q-mt-md">
                  <q-btn
                    color="negative"
                    label="Cancelar"
                    @click="userDialog = false"
                    no-caps
                    :loading="loading"
                  />
                  <q-btn
                    color="primary"
                    label="Guardar"
                    type="submit"
                    no-caps
                    :loading="loading"
                    class="q-ml-sm"
                  />
                </div>
              </q-form>
            </q-card-section>
          </q-card>
        </q-dialog>

        <!-- DIALOG CAMBIAR AVATAR -->
        <q-dialog v-model="cambioAvatarDialogo" persistent>
          <q-card>
            <q-card-section class="q-pb-none row items-center text-bold">
              Cambiar avatar
              <q-space />
              <q-btn icon="close" flat round dense @click="cambioAvatarDialogo = false" />
            </q-card-section>
            <q-card-section class="q-pt-none">
              <q-form @submit="userPut()">
                <div>
                  <div style="position: relative; top: 0; left: 0;">
                    <q-btn
                      icon="edit"
                      size="10px"
                      class="absolute q-mt-sm q-ml-sm"
                      @click="$refs.fileInput.click()"
                      dense
                      outline
                      label="Cambiar foto"
                      no-caps
                    />
                  </div>
                  <img
                    :src="`${$url}/../images/${user.avatar}`"
                    width="300px"
                    height="300px"
                    v-if="user.avatar"
                  />
                  <q-icon name="person" size="100px" v-else />
                  <input
                    ref="fileInput"
                    type="file"
                    style="display: none;"
                    @change="onFileChange"
                    accept="image/*"
                  />
                </div>
                <div class="text-right q-mt-md">
                  <q-btn
                    color="negative"
                    label="Cancelar"
                    @click="cambioAvatarDialogo = false"
                    no-caps
                    :loading="loading"
                  />
                  <q-btn
                    color="primary"
                    label="Guardar"
                    type="submit"
                    no-caps
                    :loading="loading"
                    class="q-ml-sm"
                  />
                </div>
              </q-form>
            </q-card-section>
          </q-card>
        </q-dialog>
      </q-tab-panel>

      <!-- ========== TAB 2: USUARIOS INTERNOS (BpUsuarios) ========== -->
      <q-tab-panel name="internos">
        <q-card flat bordered>
          <q-card-section class="row items-center q-col-gutter-sm">
            <div class="text-h6">
              Usuarios internos
            </div>
            <q-space />
            <q-btn
              color="primary"
              label="Actualizar"
              icon="refresh"
              no-caps
              :loading="loadingInternos"
              @click="bpUsersGet"
            />
            <q-input
              v-model="filterInterno"
              dense
              outlined
              placeholder="Buscar usuario interno..."
              class="q-ml-sm"
            >
              <template #append><q-icon name="search" /></template>
            </q-input>
          </q-card-section>

          <q-separator />

          <q-table
            :rows="bpUsersFiltered"
            :columns="bpColumns"
            row-key="usr_id"
            dense
            flat
            :rows-per-page-options="[0]"
          >
<!--            acciones colcaor permisos-->
            <template #body-cell-actions="props">
              <q-td :props="props">
                <q-btn
                  color="primary"
                  label="Permisos"
                  no-caps
                  size="10px"
                  @click="permisosShow(props.row)"
                />
              </q-td>
            </template>
            <template #body-cell-traza="props">
              <q-td :props="props">
                <q-toggle
                  v-model="props.row.traza_activado"
                  color="primary"
                  label="TRAZA"
                  @update:model-value="val => toggleTraza(props.row, val)"
                />
              </q-td>
            </template>

            <template #body-cell-sistemas="props">
              <q-td :props="props">
                <q-chip
                  v-for="(s,i) in (props.row.usr_access_sistem || [])"
                  :key="i"
                  dense
                  outline
                  :color="s.sistema === 'TRAZA' ? (s.activado ? 'green-5' : 'red-5') : 'grey-4'"
                  text-color="black"
                  class="q-mr-xs q-mb-xs"
                  size="11px"
                >
                  {{ s.sistema }} ({{ s.activado ? 'ON' : 'OFF' }})
                </q-chip>
              </q-td>
            </template>
          </q-table>
        </q-card>
      </q-tab-panel>
    </q-tab-panels>
    <!-- DIALOG PERMISOS EXTERNOS -->
    <q-dialog v-model="dialogPermisos" persistent>
      <q-card style="min-width: 420px">
        <q-card-section class="q-pb-none row items-center text-bold">
          Permisos de {{ user.username }}
          <q-space />
          <q-btn icon="close" flat round dense @click="dialogPermisos = false" />
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-input
            v-model="permFilter"
            dense
            outlined
            placeholder="Filtrar permisos..."
            class="q-mb-sm"
          >
            <template v-slot:append><q-icon name="search" /></template>
          </q-input>

          <q-list dense bordered>
            <q-item v-for="perm in filteredPermissions" :key="perm.id">
              <q-item-section>{{ perm.name }}</q-item-section>
              <q-item-section side>
                <q-toggle v-model="perm.checked" />
              </q-item-section>
            </q-item>
          </q-list>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn
            color="negative"
            label="Cancelar"
            @click="dialogPermisos = false"
            no-caps
            :loading="loading"
          />
          <q-btn
            color="primary"
            label="Guardar"
            @click="permisosPost"
            no-caps
            :loading="loading"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>
<script>
import moment from 'moment'

export default {
  name: 'UsuariosPage',
  data () {
    return {
      // TAB
      tab: 'internos',

      // EXTERNOS
      users: [],
      user: {},
      userDialog: false,
      loading: false,
      actionUser: '',
      gestiones: [],
      filter: '',
      roles: ['Usuario', 'Gerente', 'Productor', 'Produccion', 'Administrativo'],
      columns: [
        { name: 'actions', label: 'Acciones', align: 'center' },
        { name: 'name', label: 'Nombre', align: 'left', field: 'name' },
        { name: 'username', label: 'Usuario', align: 'left', field: 'username' },
        { name: 'avatar', label: 'Avatar', align: 'left', field: row => row.avatar },
        { name: 'role', label: 'Rol', align: 'left', field: 'role' },
        {
          name: 'permissions',
          label: 'Permisos',
          align: 'left',
          field: row => (row.permissions || []).map(p => p.name).join(', ')
        }
      ],
      permissions: [],
      dialogPermisos: false,
      permFilter: '',
      cambioAvatarDialogo: false,
      docentes: [],

      // Target para el diálogo de permisos (externo o interno)
      permisosTargetType: null, // 'external' | 'internal'
      permisosTargetId: null,

      // INTERNOS
      bpUsers: [],
      loadingInternos: false,
      filterInterno: '',
      bpColumns: [
        { name: 'actions', label: 'Acciones', align: 'center' },
        { name: 'usr_id', label: 'ID', field: 'usr_id', align: 'left' },
        { name: 'usr_usuario', label: 'Usuario', field: 'usr_usuario', align: 'left' },
        { name: 'traza', label: 'TRAZA', field: 'traza_activado', align: 'center' },
        { name: 'sistemas', label: 'Sistemas', field: 'usr_access_sistem', align: 'left' }
      ]
    }
  },
  async mounted () {
    this.usersGet()
    // Opcional: cargar internos de una vez
    this.bpUsersGet()
  },
  methods: {
    // ================== AVATAR EXTERNOS ==================
    onFileChange (event) {
      const file = event.target.files[0]
      const formData = new FormData()
      formData.append('avatar', file)
      this.loading = true
      this.$axios.post(this.user.id + '/avatar', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }).then(res => {
        this.cambioAvatarDialogo = false
        this.$alert.success('Avatar actualizado')
        this.usersGet()
      }).catch(error => {
        this.$alert.error(error.response.data.message)
      }).finally(() => {
        this.loading = false
      })
    },
    cambiarAvatar (user) {
      this.cambioAvatarDialogo = true
      this.user = { ...user }
    },

    // ================== EXTERNOS ==================
    userNew () {
      this.user = {
        name: '',
        email: '',
        password: '',
        area_id: 1,
        username: '',
        cargo: '',
        role: 'Usuario'
      }
      this.actionUser = 'Nuevo'
      this.userDialog = true
    },
    usersGet () {
      this.loading = true
      this.users = []
      this.$axios.get('users').then(res => {
        this.users = res.data
      }).catch(error => {
        this.$alert.error(error.response.data.message)
      }).finally(() => {
        this.loading = false
      })
    },
    userPost () {
      this.loading = true
      this.$axios.post('users', this.user).then(res => {
        this.userDialog = false
        this.$alert.success('User creado')
        this.usersGet()
      }).catch(error => {
        this.$alert.error(error.response.data.message)
      }).finally(() => {
        this.loading = false
      })
    },
    userPut () {
      this.loading = true
      this.$axios.put('users/' + this.user.id, this.user).then(res => {
        this.usersGet()
        this.userDialog = false
        this.$alert.success('User actualizado')
      }).catch(error => {
        this.$alert.error(error.response.data.message)
      }).finally(() => {
        this.loading = false
      })
    },

    // 🔐 DIÁLOGO DE PERMISOS (EXTERNOS e INTERNOS)
    async permisosShow (row) {
      // row puede ser:
      // - user externo: { id, username, ... }
      // - user interno: { usr_id, usr_usuario, ... }

      this.dialogPermisos = true
      this.loading = true

      // Configuramos el target
      if (row.id) {
        // EXTERNO
        this.permisosTargetType = 'external'
        this.permisosTargetId = row.id
        this.user = { ...row } // para mostrar username en el título
      } else {
        // INTERNO
        this.permisosTargetType = 'internal'
        this.permisosTargetId = row.usr_id
        this.user = {
          username: row.usr_usuario
        }
      }

      try {
        const all = await this.$axios.get('permissions').then(r => r.data)

        const endpoint =
          this.permisosTargetType === 'external'
            ? `users/${this.permisosTargetId}/permissions`
            : `bp-usuarios/${this.permisosTargetId}/permissions`

        const userPermIds = await this.$axios.get(endpoint).then(r => r.data)

        this.permissions = all.map(p => ({
          ...p,
          checked: userPermIds.includes(p.id)
        }))
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'Error cargando permisos')
      } finally {
        this.loading = false
      }
    },
    async permisosPost () {
      this.loading = true
      try {
        const ids = this.permissions.filter(p => p.checked).map(p => p.id)

        const endpoint =
          this.permisosTargetType === 'external'
            ? `users/${this.permisosTargetId}/permissions`
            : `bp-usuarios/${this.permisosTargetId}/permissions`

        await this.$axios.put(endpoint, { permissions: ids })

        this.dialogPermisos = false
        this.$alert.success('Permisos actualizados')
        // Refrescamos según el tipo
        if (this.permisosTargetType === 'external') {
          this.usersGet()
        } else {
          this.bpUsersGet()
        }
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'No se pudo guardar')
      } finally {
        this.loading = false
      }
    },

    userEditPassword (user) {
      this.user = { ...user }
      this.$alert
        .dialogPrompt('Nueva contraseña', 'Ingrese la nueva contraseña', 'password')
        .onOk(password => {
          this.$axios.put('updatePassword/' + user.id, {
            password
          }).then(res => {
            this.usersGet()
            this.$alert.success('Contraseña actualizada de ' + user.username)
          }).catch(error => {
            this.$alert.error(error.response.data.message)
          })
        })
    },
    userEdit (user) {
      this.user = { ...user }
      this.actionUser = 'Editar'
      this.userDialog = true
    },
    userDelete (id) {
      this.$alert
        .dialog('¿Desea eliminar el user?')
        .onOk(() => {
          this.loading = true
          this.$axios.delete('users/' + id).then(res => {
            this.usersGet()
            this.$alert.success('User eliminado')
          }).catch(error => {
            this.$alert.error(error.response.data.message)
          }).finally(() => {
            this.loading = false
          })
        })
    },

    // ================== INTERNOS (BpUsuarios) ==================
    bpUsersGet () {
      this.loadingInternos = true
      this.$axios.get('bp-usuarios')
        .then(res => {
          this.bpUsers = res.data
        })
        .catch(err => {
          this.$alert.error(err.response?.data?.message || 'Error cargando usuarios internos')
        })
        .finally(() => {
          this.loadingInternos = false
        })
    },
    toggleTraza (row, val) {
      // Llamar al backend para actualizar TRAZA
      this.$axios.put(`bp-usuarios/${row.usr_id}/traza`, { activado: val })
        .then(() => {
          this.$alert.success(`TRAZA ${val ? 'activado' : 'desactivado'} para ${row.usr_usuario}`)
        })
        .catch(err => {
          this.$alert.error(err.response?.data?.message || 'No se pudo actualizar TRAZA')
          // revertir el cambio en UI
          row.traza_activado = !val
        })
    }
  },
  computed: {
    filteredPermissions () {
      if (!this.permFilter) return this.permissions
      const t = this.permFilter.toLowerCase()
      return this.permissions.filter(p => p.name.toLowerCase().includes(t))
    },
    bpUsersFiltered () {
      if (!this.filterInterno) return this.bpUsers
      const t = this.filterInterno.toLowerCase()
      return this.bpUsers.filter(u =>
        String(u.usr_usuario || '').toLowerCase().includes(t) ||
        String(u.usr_id || '').includes(t)
      )
    }
  }
}
</script>
