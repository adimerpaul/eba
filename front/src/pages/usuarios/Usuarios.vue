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
      <q-tab name="internos" label="Usuarios internos" icon="badge" no-caps />
      <q-tab name="externos" label="Usuarios externos" icon="person" no-caps />
    </q-tabs>

    <q-separator class="q-mb-md" />

    <q-tab-panels v-model="tab" animated>

      <!-- ========== TAB: USUARIOS EXTERNOS ========== -->
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
              color="secondary"
              label="Roles & permisos"
              no-caps
              icon="admin_panel_settings"
              class="q-mr-sm"
              :loading="rolesLoading"
              @click="openRolesAdmin"
            />
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

                  <q-item clickable @click="userRolesShow(props.row)" v-close-popup>
                    <q-item-section avatar>
                      <q-icon name="admin_panel_settings" />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>Roles del usuario</q-item-label>
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

        <!-- DIALOG CREAR/EDITAR USUARIO -->
        <q-dialog v-model="userDialog" persistent>
          <q-card style="width: 400px">
            <q-card-section class="q-pb-none row items-center">
              <div>{{ actionUser }} user</div>
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
                <q-input v-model="user.email" label="Email" dense outlined />

                <q-input
                  v-model="user.password"
                  label="Contraseña"
                  dense
                  outlined
                  :rules="[val => !!val || 'Campo requerido']"
                  v-if="!user.id"
                  type="password"
                />

                <!-- Rol de Spatie -->
                <q-select
                  v-model="user.role_id"
                  label="Rol"
                  dense
                  outlined
                  :options="rolesSelect"
                  option-label="name"
                  option-value="id"
                  emit-value
                  map-options
                  :loading="rolesLoading"
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

      <!-- ========== TAB: USUARIOS INTERNOS (BpUsuarios) ========== -->
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
            <template #body-cell-actions="props">
              <q-td :props="props">
                <q-btn
                  color="primary"
                  label="Roles"
                  no-caps
                  size="10px"
                  @click="userRolesShow(props.row)"
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

    <!-- ========== DIALOG ROLES DE UN USUARIO (EXTERNO / INTERNO) ========== -->
    <q-dialog v-model="dialogUserRoles" persistent>
      <q-card style="min-width: 420px">
        <q-card-section class="q-pb-none row items-center text-bold">
          Roles de {{ user.username }}
          <q-space />
          <q-btn icon="close" flat round dense @click="dialogUserRoles = false" />
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-input
            v-model="rolesUserFilter"
            dense
            outlined
            placeholder="Filtrar roles..."
            class="q-mb-sm"
          >
            <template #append><q-icon name="search" /></template>
          </q-input>

          <q-list dense bordered>
            <q-item
              v-for="r in filteredRolesUser"
              :key="r.id"
            >
              <q-item-section>{{ r.name }}</q-item-section>
              <q-item-section side>
                <q-toggle v-model="r.checked" />
              </q-item-section>
            </q-item>
          </q-list>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn
            color="negative"
            label="Cancelar"
            @click="dialogUserRoles = false"
            no-caps
            :loading="loading"
          />
          <q-btn
            color="primary"
            label="Guardar"
            @click="userRolesSave"
            no-caps
            :loading="loading"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- ========== DIALOG ADMIN ROLES & PERMISOS ========== -->
    <q-dialog v-model="rolesAdminDialog" persistent>
      <q-card style="min-width: 700px; max-width: 900px;">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Roles & permisos</div>
          <q-space />
          <q-btn icon="close" flat round dense @click="rolesAdminDialog = false" />
        </q-card-section>

        <q-separator />

        <q-card-section class="q-pt-sm">
          <!-- Form nuevo/editar rol -->
          <div class="row q-col-gutter-sm q-mb-sm">
            <div class="col">
              <q-input
                v-model="roleForm.name"
                label="Nombre del rol"
                dense
                outlined
              />
            </div>
            <div class="col-auto">
              <q-btn
                :label="roleForm.id ? 'Actualizar rol' : 'Crear rol'"
                color="primary"
                no-caps
                icon="save"
                :loading="rolesLoading"
                @click="saveRole"
                class="q-mr-sm"
              />
              <q-btn
                v-if="roleForm.id"
                label="Nuevo"
                color="grey-7"
                no-caps
                flat
                @click="resetRoleForm"
              />
            </div>
          </div>

          <div class="row q-col-gutter-md">
            <!-- Tabla de roles -->
            <div class="col-12 col-md-5">
              <q-table
                title="Roles"
                :rows="rolesList"
                :columns="rolesColumns"
                row-key="id"
                dense
                flat
                bordered
                :rows-per-page-options="[0]"
                @row-click="(_, row) => selectRole(row)"
              >
                <template #body-cell-actions="props">
                  <q-td :props="props">
                    <q-btn
                      icon="delete"
                      size="sm"
                      color="negative"
                      flat
                      round
                      @click.stop="deleteRole(props.row)"
                    />
                  </q-td>
                </template>
              </q-table>
            </div>

            <!-- Permisos del rol seleccionado -->
            <div class="col-12 col-md-7">
              <div v-if="selectedRole">
                <div class="row items-center q-mb-sm">
                  <div class="text-subtitle2">
                    Permisos de: <strong>{{ selectedRole.name }}</strong>
                  </div>
                  <q-space />
                  <q-input
                    v-model="permFilterRole"
                    dense
                    outlined
                    placeholder="Filtrar permisos..."
                    style="max-width: 220px;"
                  >
                    <template #append><q-icon name="search" /></template>
                  </q-input>
                </div>

                <q-list dense bordered style="max-height: 350px; overflow-y: auto;">
                  <q-item
                    v-for="perm in filteredRolePermissions"
                    :key="perm.id"
                  >
                    <q-item-section>{{ perm.name }}</q-item-section>
                    <q-item-section side>
                      <q-toggle v-model="perm.checked" />
                    </q-item-section>
                  </q-item>
                </q-list>

                <div class="text-right q-mt-sm">
                  <q-btn
                    color="primary"
                    label="Guardar permisos del rol"
                    no-caps
                    :loading="rolesLoading"
                    @click="saveRolePermissions"
                  />
                </div>
              </div>
              <div v-else class="text-grey text-italic">
                Selecciona un rol de la tabla para editar sus permisos.
              </div>
            </div>
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>
<script>
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
      filter: '',

      // roles Spatie para selects y dialogs
      rolesSelect: [],        // para <q-select> de usuario
      rolesList: [],          // para tabla de roles en admin
      rolesLoading: false,

      // columnas tabla usuarios externos
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

      cambioAvatarDialogo: false,

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
      ],

      // DIALOG ROLES POR USUARIO
      dialogUserRoles: false,
      rolesTargetType: null, // 'external' | 'internal'
      rolesTargetId: null,
      rolesUserList: [],
      rolesUserFilter: '',

      // ADMIN DE ROLES
      rolesAdminDialog: false,
      allPermissions: [],
      roleForm: { id: null, name: '' },
      selectedRole: null,
      rolePermissions: [],
      permFilterRole: '',

      rolesColumns: [
        { name: 'name', label: 'Nombre', field: 'name', align: 'left' },
        {
          name: 'permissions_count',
          label: 'N° permisos',
          field: row => (row.permissions || []).length,
          align: 'center'
        },
        { name: 'actions', label: '', align: 'center' }
      ]
    }
  },
  async mounted () {
    this.usersGet()
    this.bpUsersGet()
    this.loadRoles() // roles y permisos para admin
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
      }).then(() => {
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
        username: '',
        role_id: null
      }
      this.actionUser = 'Nuevo'
      this.userDialog = true
    },
    usersGet () {
      this.loading = true
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
      this.$axios.post('users', this.user).then(() => {
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
      this.$axios.put('users/' + this.user.id, this.user).then(() => {
        this.usersGet()
        this.userDialog = false
        this.$alert.success('User actualizado')
      }).catch(error => {
        this.$alert.error(error.response.data.message)
      }).finally(() => {
        this.loading = false
      })
    },
    userEditPassword (user) {
      this.user = { ...user }
      this.$alert
        .dialogPrompt('Nueva contraseña', 'Ingrese la nueva contraseña', 'password')
        .onOk(password => {
          this.$axios.put('updatePassword/' + user.id, {
            password
          }).then(() => {
            this.usersGet()
            this.$alert.success('Contraseña actualizada de ' + user.username)
          }).catch(error => {
            this.$alert.error(error.response.data.message)
          })
        })
    },
    userEdit (user) {
      this.user = { ...user }
      // mapear primer rol a role_id si existe
      if (user.roles && user.roles.length) {
        this.user.role_id = user.roles[0].id
      }
      this.actionUser = 'Editar'
      this.userDialog = true
    },
    userDelete (id) {
      this.$alert
        .dialog('¿Desea eliminar el user?')
        .onOk(() => {
          this.loading = true
          this.$axios.delete('users/' + id).then(() => {
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
      this.$axios.put(`bp-usuarios/${row.usr_id}/traza`, { activado: val })
        .then(() => {
          this.$alert.success(`TRAZA ${val ? 'activado' : 'desactivado'} para ${row.usr_usuario}`)
        })
        .catch(err => {
          this.$alert.error(err.response?.data?.message || 'No se pudo actualizar TRAZA')
          row.traza_activado = !val
        })
    },

    // ================== ROLES: CARGA GLOBAL ==================
    async loadRoles () {
      this.rolesLoading = true
      try {
        const [rolesRes, permsRes] = await Promise.all([
          this.$axios.get('roles'),
          this.$axios.get('permissions')
        ])

        this.rolesSelect = rolesRes.data // para select de usuario
        this.rolesList = rolesRes.data   // para tabla admin
        this.allPermissions = permsRes.data
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'Error cargando roles/permisos')
      } finally {
        this.rolesLoading = false
      }
    },

    // ================== ROLES POR USUARIO ==================
    async userRolesShow (row) {
      this.dialogUserRoles = true
      this.loading = true

      if (row.id) {
        // EXTERNO
        this.rolesTargetType = 'external'
        this.rolesTargetId = row.id
        this.user = { ...row }
      } else {
        // INTERNO
        this.rolesTargetType = 'internal'
        this.rolesTargetId = row.usr_id
        this.user = { username: row.usr_usuario }
      }

      try {
        if (!this.rolesSelect.length) {
          await this.loadRoles()
        }

        const endpoint =
          this.rolesTargetType === 'external'
            ? `users/${this.rolesTargetId}/roles`
            : `bp-usuarios/${this.rolesTargetId}/roles`

        const userRoleIds = await this.$axios.get(endpoint).then(r => r.data)

        this.rolesUserList = this.rolesSelect.map(r => ({
          ...r,
          checked: userRoleIds.includes(r.id)
        }))
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'Error cargando roles del usuario')
      } finally {
        this.loading = false
      }
    },

    async userRolesSave () {
      this.loading = true
      try {
        const roleIds = this.rolesUserList
          .filter(r => r.checked)
          .map(r => r.id)

        const endpoint =
          this.rolesTargetType === 'external'
            ? `users/${this.rolesTargetId}/roles`
            : `bp-usuarios/${this.rolesTargetId}/roles`

        await this.$axios.put(endpoint, { roles: roleIds })

        this.dialogUserRoles = false
        this.$alert.success('Roles actualizados')

        if (this.rolesTargetType === 'external') {
          this.usersGet()
        } else {
          this.bpUsersGet()
        }
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'No se pudo guardar roles')
      } finally {
        this.loading = false
      }
    },

    // ================== ADMIN DE ROLES (CRUD + PERMISOS) ==================
    openRolesAdmin () {
      this.rolesAdminDialog = true
      this.loadRoles()
      this.resetRoleForm()
      this.selectedRole = null
      this.rolePermissions = []
      this.permFilterRole = ''
    },
    resetRoleForm () {
      this.roleForm = { id: null, name: '' }
    },
    async saveRole () {
      if (!this.roleForm.name) {
        this.$alert.error('El nombre del rol es obligatorio')
        return
      }
      this.rolesLoading = true
      try {
        if (this.roleForm.id) {
          await this.$axios.put(`roles/${this.roleForm.id}`, {
            name: this.roleForm.name
          })
          this.$alert.success('Rol actualizado')
        } else {
          await this.$axios.post('roles', {
            name: this.roleForm.name
          })
          this.$alert.success('Rol creado')
        }
        await this.loadRoles()
        if (!this.roleForm.id) {
          this.roleForm.name = ''
        }
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'No se pudo guardar el rol')
      } finally {
        this.rolesLoading = false
      }
    },
    async deleteRole (role) {
      this.$alert
        .dialog(`¿Eliminar el rol "${role.name}"?`)
        .onOk(async () => {
          this.rolesLoading = true
          try {
            await this.$axios.delete(`roles/${role.id}`)
            this.$alert.success('Rol eliminado')
            await this.loadRoles()
            if (this.selectedRole && this.selectedRole.id === role.id) {
              this.selectedRole = null
              this.rolePermissions = []
            }
          } catch (e) {
            this.$alert.error(e.response?.data?.message || 'No se pudo eliminar el rol')
          } finally {
            this.rolesLoading = false
          }
        })
    },
    async selectRole (role) {
      this.selectedRole = role
      this.roleForm = { id: role.id, name: role.name }
      this.rolesLoading = true
      try {
        const ids = await this.$axios.get(`roles/${role.id}/permissions`).then(r => r.data)
        this.rolePermissions = this.allPermissions.map(p => ({
          ...p,
          checked: ids.includes(p.id)
        }))
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'Error cargando permisos del rol')
      } finally {
        this.rolesLoading = false
      }
    },
    async saveRolePermissions () {
      if (!this.selectedRole) return
      this.rolesLoading = true
      try {
        const ids = this.rolePermissions
          .filter(p => p.checked)
          .map(p => p.id)

        await this.$axios.put(`roles/${this.selectedRole.id}/permissions`, {
          permissions: ids
        })

        this.$alert.success('Permisos del rol actualizados')
        await this.loadRoles()
      } catch (e) {
        this.$alert.error(e.response?.data?.message || 'No se pudo guardar los permisos del rol')
      } finally {
        this.rolesLoading = false
      }
    }
  },
  computed: {
    bpUsersFiltered () {
      if (!this.filterInterno) return this.bpUsers
      const t = this.filterInterno.toLowerCase()
      return this.bpUsers.filter(u =>
        String(u.usr_usuario || '').toLowerCase().includes(t) ||
        String(u.usr_id || '').includes(t)
      )
    },
    filteredRolesUser () {
      if (!this.rolesUserFilter) return this.rolesUserList
      const t = this.rolesUserFilter.toLowerCase()
      return this.rolesUserList.filter(r => r.name.toLowerCase().includes(t))
    },
    filteredRolePermissions () {
      if (!this.permFilterRole) return this.rolePermissions
      const t = this.permFilterRole.toLowerCase()
      return this.rolePermissions.filter(p => p.name.toLowerCase().includes(t))
    }
  }
}
</script>
