const routes = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', component: () => import('pages/IndexPage.vue'), meta: { requiresAuth: true, perm: 'Dashboard' } },
      { path: '/usuarios', component: () => import('pages/usuarios/Usuarios.vue'), meta: { requiresAuth: true, perm: 'Usuarios' } },
      { path: '/apicultores', component: () => import('pages/apicultores/Apicultor.vue'), meta: { requiresAuth: true, perm: 'Produccion primaria' } },
      { path: '/geocrud', component: () => import('pages/geocrud/GeoCrud.vue'), meta: { requiresAuth: true, perm: 'GeoCrud' } },
      { path: '/organizaciones', component: () => import('pages/organizaciones/Organizaciones.vue'), meta: { requiresAuth: true, perm: 'Organizaciones' } },
      { path: '/productores', component: () => import('pages/productores/Productores.vue'), meta: { requiresAuth: true, perm: 'Productores' } },
      { path: '/productores/crear', component: () => import('pages/productores/ProductorCrear.vue'), meta: { requiresAuth: true, perm: 'Productores' } },
      { path: '/productores/editar/:id', component: () => import('pages/productores/ProductorShow.vue'), meta: { requiresAuth: true, perm: 'Productores' } },
      { path: '/acopios', component: () => import('pages/acopio/Acopio.vue'), meta: { requiresAuth: true, perm: 'Acopio' } },
      { path: '/recoleccion', component: () => import('pages/acopio/AcopioCrear.vue'), meta: { requiresAuth: true, perm: 'Acopio' } },
      { path: '/acopio/cosechas/:id', component: () => import('pages/acopio/CosechaShow.vue'), meta: { requiresAuth: true, perm: 'Acopio' } },
      { path: '/productos', component: () => import('pages/productos/Productos.vue'), meta: { requiresAuth: true, perm: 'Productos' } },
    ]
  },
  { path: '/qr/:code', component: () => import('pages/qr/QrFront.vue'), meta: { public: true } },
  { path: '/login', component: () => import('layouts/Login.vue') },
  { path: '/:catchAll(.*)*', component: () => import('pages/ErrorNotFound.vue') }
]
export default routes
