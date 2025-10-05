const routes = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', component: () => import('pages/IndexPage.vue'), meta: { requiresAuth: true, perm: 'Dashboard' } },
      { path: '/usuarios', component: () => import('pages/usuarios/Usuarios.vue'), meta: { requiresAuth: true, perm: 'Usuarios' } },
      { path: '/apicultores', component: () => import('pages/apicultores/Apicultor.vue'), meta: { requiresAuth: true, perm: 'Produccion primaria' } },
      { path: '/geocrud', component: () => import('pages/geocrud/GeoCrud.vue'), meta: { requiresAuth: true, perm: 'GeoCrud' } },
    ]
  },
  { path: '/login', component: () => import('layouts/Login.vue') },
  { path: '/:catchAll(.*)*', component: () => import('pages/ErrorNotFound.vue') }
]
export default routes
