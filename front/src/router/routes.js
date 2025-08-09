const routes = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', component: () => import('pages/IndexPage.vue'), meta: { requiresAuth: true, perm: 'Dashboard' } },
      { path: '/usuarios', component: () => import('pages/usuarios/Usuarios.vue'), meta: { requiresAuth: true, perm: 'Usuarios' } },
      { path: '/clientes', component: () => import('pages/clientes/Cliente.vue'), meta: { requiresAuth: true, perm: 'Clientes' } },
      { path: '/ordenes', component: () => import('pages/ordenes/Ordenes.vue'), meta: { requiresAuth: true, perm: 'Ordenes' } },
      { path: '/ordenes/crear', name: 'crearOrden', component: () => import('pages/ordenes/OrdenCrear.vue'), meta: { requiresAuth: true, perm: 'Ordenes' } },
      { path: '/ordenes/editar/:id', component: () => import('pages/ordenes/OrderEditar.vue'), meta: { requiresAuth: true, perm: 'Ordenes' } },
      { path: '/configuraciones', component: () => import('pages/configuraciones/Configuraciones.vue'), meta: { requiresAuth: true, perm: 'Configuracion' } },
    ]
  },
  { path: '/login', component: () => import('layouts/Login.vue') },
  { path: '/:catchAll(.*)*', component: () => import('pages/ErrorNotFound.vue') }
]
export default routes
