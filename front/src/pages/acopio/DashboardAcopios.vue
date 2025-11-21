<template>
  <!-- 2025-11-21: Dashboard multidimensional de acopios con análisis geográfico, temporal y organizacional -->
  <q-page class="q-pa-md">
    <!-- HEADER CON TITULO Y SELECTORES GLOBALES -->
    <div class="row items-center q-mb-md">
      <div class="col-12 col-md-4">
        <div class="text-h5">Dashboard de Acopios</div>
        <div class="text-caption text-grey-7">Análisis multidimensional de producción</div>
      </div>
      
      <q-space />
      
      <!-- 2025-11-21: Selectores globales de gestión y producto -->
      <div class="col-12 col-md-4 q-gutter-sm">
        <div class="row q-gutter-sm">
          <div class="col">
            <q-select
              v-model="gestionSeleccionada"
              :options="gestiones"
              label="Gestión"
              dense
              outlined
              @update:model-value="cambioFiltroGlobal"
            />
          </div>
          <div class="col">
            <q-select
              v-model="productoSeleccionado"
              :options="productos"
              option-label="nombre_producto"
              option-value="id"
              label="Producto"
              dense
              outlined
              emit-value
              map-options
              @update:model-value="cambioFiltroGlobal"
            />
          </div>
        </div>
      </div>
      
      <!-- 2025-11-21: Botón de actualizar datos -->
      <div class="col-auto">
        <q-btn
          flat
          round
          icon="refresh"
          color="primary"
          :loading="loadingActualizar"
          @click="actualizarDatos"
        >
          <q-tooltip>Actualizar datos</q-tooltip>
        </q-btn>
      </div>
    </div>

    <!-- 2025-11-21: INDICADORES GLOBALES PERMANENTES -->
    <q-banner v-if="indicadoresGlobales" class="bg-blue-grey-1 q-mb-md" rounded>
      <div class="row q-gutter-sm">
        <q-chip color="primary" text-color="white" icon="inbox" dense>
          Total Kg: {{ indicadoresGlobales.totalKg?.toFixed(2) || '0.00' }}
        </q-chip>
        <q-chip color="green" text-color="white" icon="people" dense>
          Productores: {{ indicadoresGlobales.totalProductores || 0 }}
        </q-chip>
        <q-chip color="orange" text-color="white" icon="analytics" dense>
          Promedio: {{ indicadoresGlobales.promedioKg?.toFixed(2) || '0.00' }} Kg/Productor
        </q-chip>
        <q-chip
          :color="indicadoresGlobales.crecimiento >= 0 ? 'positive' : 'negative'"
          text-color="white"
          :icon="indicadoresGlobales.crecimiento >= 0 ? 'trending_up' : 'trending_down'"
          dense
        >
          Crecimiento: {{ indicadoresGlobales.crecimiento?.toFixed(2) || '0.00' }}%
        </q-chip>
      </div>
    </q-banner>

    <!-- TABS PRINCIPALES -->
    <q-card flat bordered>
      <q-tabs
        v-model="tabActiva"
        dense
        align="left"
        active-color="primary"
        indicator-color="primary"
        class="text-grey"
      >
        <q-tab name="resumen" icon="dashboard" label="Resumen General" no-caps />
        <q-tab name="geografico" icon="location_on" label="Análisis Geográfico" no-caps />
        <q-tab name="productos" icon="inventory" label="Análisis por Producto" no-caps />
        <q-tab name="temporal" icon="schedule" label="Análisis Temporal" no-caps />
        <q-tab name="organizacional" icon="apartment" label="Análisis Organizacional" no-caps />
      </q-tabs>

      <q-separator />

      <q-tab-panels v-model="tabActiva" animated>
        <!-- ============================================ -->
        <!-- TAB 1: RESUMEN GENERAL -->
        <!-- ============================================ -->
        <q-tab-panel name="resumen">
          <!-- 2025-11-21: KPIs principales con loading states -->
          <div class="row q-col-gutter-md q-mb-md">
            <div class="col-12 col-md-6 col-lg-3" v-for="kpi in kpisResumen" :key="kpi.label">
              <q-card flat bordered>
                <q-card-section>
                  <q-inner-loading :showing="loadingKpis" />
                  <div class="row items-center">
                    <q-icon :name="kpi.icon" :color="kpi.color" size="md" class="q-mr-sm" />
                    <div>
                      <div class="text-caption text-grey-7">{{ kpi.label }}</div>
                      <div class="text-h5 text-weight-bold">{{ kpi.value }}</div>
                    </div>
                  </div>
                </q-card-section>
              </q-card>
            </div>
          </div>

          <!-- 2025-11-21: Gráfico de evolución anual -->
          <q-card flat bordered class="q-mb-md">
            <q-card-section>
              <q-inner-loading :showing="loadingGraficos" />
              <div class="text-subtitle1 q-mb-md">
                Evolución Mensual de Acopios
                <q-icon name="info" size="xs" color="grey-6">
                  <q-tooltip>Muestra la tendencia de acopio a lo largo del año</q-tooltip>
                </q-icon>
              </div>
              <div v-if="!loadingGraficos && datosEvolucionAnual.length === 0">
                <q-banner class="bg-info text-white" rounded dense>
                  <template v-slot:avatar>
                    <q-icon name="info" />
                  </template>
                  No hay datos disponibles para la gestión seleccionada
                </q-banner>
              </div>
              <canvas v-else ref="chartEvolucionAnual" height="80"></canvas>
            </q-card-section>
          </q-card>

          <!-- 2025-11-21: Top 3 Rankings -->
          <div class="row q-col-gutter-md">
            <div class="col-12 col-md-4">
              <q-card flat bordered>
                <q-card-section>
                  <q-inner-loading :showing="loadingTablas" />
                  <div class="text-subtitle1 q-mb-md">Top 3 Departamentos</div>
                  <q-list dense v-if="topDepartamentos.length > 0">
                    <q-item v-for="(item, index) in topDepartamentos" :key="index">
                      <q-item-section avatar>
                        <q-avatar :color="['primary', 'secondary', 'accent'][index]" text-color="white">
                          {{ index + 1 }}
                        </q-avatar>
                      </q-item-section>
                      <q-item-section>
                        <q-item-label>{{ item.nombre }}</q-item-label>
                        <q-item-label caption>{{ item.kg }} Kg</q-item-label>
                      </q-item-section>
                    </q-item>
                  </q-list>
                  <div v-else class="text-center text-grey-6 q-pa-md">Sin datos</div>
                </q-card-section>
              </q-card>
            </div>

            <div class="col-12 col-md-4">
              <q-card flat bordered>
                <q-card-section>
                  <q-inner-loading :showing="loadingTablas" />
                  <div class="text-subtitle1 q-mb-md">Top 3 Municipios</div>
                  <q-list dense v-if="topMunicipios.length > 0">
                    <q-item v-for="(item, index) in topMunicipios" :key="index">
                      <q-item-section avatar>
                        <q-avatar :color="['primary', 'secondary', 'accent'][index]" text-color="white">
                          {{ index + 1 }}
                        </q-avatar>
                      </q-item-section>
                      <q-item-section>
                        <q-item-label>{{ item.nombre }}</q-item-label>
                        <q-item-label caption>{{ item.kg }} Kg</q-item-label>
                      </q-item-section>
                    </q-item>
                  </q-list>
                  <div v-else class="text-center text-grey-6 q-pa-md">Sin datos</div>
                </q-card-section>
              </q-card>
            </div>

            <div class="col-12 col-md-4">
              <q-card flat bordered>
                <q-card-section>
                  <q-inner-loading :showing="loadingTablas" />
                  <div class="text-subtitle1 q-mb-md">Top 3 Organizaciones</div>
                  <q-list dense v-if="topOrganizaciones.length > 0">
                    <q-item v-for="(item, index) in topOrganizaciones" :key="index">
                      <q-item-section avatar>
                        <q-avatar :color="['primary', 'secondary', 'accent'][index]" text-color="white">
                          {{ index + 1 }}
                        </q-avatar>
                      </q-item-section>
                      <q-item-section>
                        <q-item-label>{{ item.nombre }}</q-item-label>
                        <q-item-label caption>{{ item.kg }} Kg</q-item-label>
                      </q-item-section>
                    </q-item>
                  </q-list>
                  <div v-else class="text-center text-grey-6 q-pa-md">Sin datos</div>
                </q-card-section>
              </q-card>
            </div>
          </div>
        </q-tab-panel>

        <!-- ============================================ -->
        <!-- TAB 2: ANALISIS GEOGRAFICO -->
        <!-- ============================================ -->
        <q-tab-panel name="geografico">
          <!-- 2025-11-21: Filtros geográficos en cascada -->
          <div class="row q-col-gutter-md q-mb-md">
            <div class="col-12 col-md-4">
              <q-select
                v-model="filtroDepartamento"
                :options="departamentos"
                option-label="nombre_departamento"
                option-value="id"
                label="Departamento"
                dense
                outlined
                clearable
                emit-value
                map-options
                @update:model-value="onDepartamentoChange"
              />
            </div>
            <div class="col-12 col-md-4">
              <q-select
                v-model="filtroMunicipio"
                :options="municipios"
                option-label="nombre_municipio"
                option-value="id"
                label="Municipio"
                dense
                outlined
                clearable
                emit-value
                map-options
                :disable="!filtroDepartamento"
                @update:model-value="aplicarFiltroGeografico"
              />
            </div>
            <div class="col-12 col-md-4">
              <q-btn
                color="primary"
                label="Aplicar Filtros"
                icon="filter_alt"
                @click="aplicarFiltroGeografico"
                :disable="!filtroDepartamento"
                no-caps
              />
            </div>
          </div>

          <!-- 2025-11-21: Gráfico de barras comparativo -->
          <q-card flat bordered class="q-mb-md">
            <q-card-section>
              <q-inner-loading :showing="loadingGraficos" />
              <div class="text-subtitle1 q-mb-md">
                Distribución Geográfica de Acopios
                <q-icon name="info" size="xs" color="grey-6">
                  <q-tooltip>Comparativa de acopio por ubicación geográfica</q-tooltip>
                </q-icon>
              </div>
              <div v-if="!loadingGraficos && datosGeograficos.length === 0">
                <q-banner class="bg-info text-white" rounded dense>
                  <template v-slot:avatar>
                    <q-icon name="info" />
                  </template>
                  Seleccione un departamento para visualizar datos
                </q-banner>
              </div>
              <canvas v-else ref="chartGeografico" height="100"></canvas>
            </q-card-section>
          </q-card>

          <!-- 2025-11-21: Tabla detallada con exportación -->
          <q-card flat bordered>
            <q-card-section>
              <div class="row items-center q-mb-md">
                <div class="text-subtitle1">Detalle por Ubicación</div>
                <q-space />
                <q-btn
                  flat
                  dense
                  icon="download"
                  color="primary"
                  label="Exportar"
                  @click="exportarDashboard('geografico')"
                  :disable="tablaGeografica.length === 0"
                  no-caps
                />
              </div>
              <q-table
                :rows="tablaGeografica"
                :columns="columnasGeograficas"
                row-key="ubicacion"
                dense
                flat
                :loading="loadingTablas"
                :pagination="paginacionGeo"
                binary-state-sort
              >
                <template v-slot:body-cell-porcentaje="props">
                  <q-td :props="props">
                    <q-linear-progress
                      :value="props.row.porcentaje / 100"
                      color="primary"
                      size="md"
                      class="q-mb-xs"
                    />
                    <div class="text-caption">{{ props.row.porcentaje }}%</div>
                  </q-td>
                </template>
              </q-table>
            </q-card-section>
          </q-card>
        </q-tab-panel>

        <!-- ============================================ -->
        <!-- TAB 3: ANALISIS POR PRODUCTO -->
        <!-- ============================================ -->
        <q-tab-panel name="productos">
          <!-- 2025-11-21: Selector de producto y gráfico circular -->
          <div class="row q-col-gutter-md">
            <div class="col-12 col-md-6">
              <q-card flat bordered>
                <q-card-section>
                  <q-inner-loading :showing="loadingGraficos" />
                  <div class="text-subtitle1 q-mb-md">
                    Distribución por Tipo de Producto
                    <q-icon name="info" size="xs" color="grey-6">
                      <q-tooltip>Proporción de acopio por cada tipo de producto</q-tooltip>
                    </q-icon>
                  </div>
                  <div v-if="!loadingGraficos && datosProductos.length === 0">
                    <q-banner class="bg-info text-white" rounded dense>
                      <template v-slot:avatar>
                        <q-icon name="info" />
                      </template>
                      No hay datos disponibles para los filtros seleccionados
                    </q-banner>
                  </div>
                  <canvas v-else ref="chartProductos" height="200"></canvas>
                </q-card-section>
              </q-card>
            </div>

            <!-- 2025-11-21: Tabla de distribución geográfica del producto -->
            <div class="col-12 col-md-6">
              <q-card flat bordered>
                <q-card-section>
                  <q-inner-loading :showing="loadingTablas" />
                  <div class="text-subtitle1 q-mb-md">Distribución Geográfica</div>
                  <q-table
                    :rows="tablaDistribucionProducto"
                    :columns="columnasDistribucionProducto"
                    row-key="ubicacion"
                    dense
                    flat
                    :pagination="{ rowsPerPage: 5 }"
                  />
                </q-card-section>
              </q-card>
            </div>
          </div>
        </q-tab-panel>

        <!-- ============================================ -->
        <!-- TAB 4: ANALISIS TEMPORAL -->
        <!-- ============================================ -->
        <q-tab-panel name="temporal">
          <!-- 2025-11-21: Selector de gestiones múltiples para comparar -->
          <div class="row q-col-gutter-md q-mb-md">
            <div class="col-12 col-md-6">
              <q-select
                v-model="gestionesComparar"
                :options="gestiones"
                label="Gestiones a Comparar (máx. 3)"
                dense
                outlined
                multiple
                use-chips
                max-values="3"
                @update:model-value="generarGraficoTemporal"
              />
            </div>
          </div>

          <!-- 2025-11-21: Gráfico de evolución mensual comparativa -->
          <q-card flat bordered class="q-mb-md">
            <q-card-section>
              <q-inner-loading :showing="loadingGraficos" />
              <div class="text-subtitle1 q-mb-md">
                Evolución Mensual Comparativa
                <q-icon name="info" size="xs" color="grey-6">
                  <q-tooltip>Comparación de acopio mensual entre gestiones</q-tooltip>
                </q-icon>
              </div>
              <canvas ref="chartTemporal" height="80"></canvas>
            </q-card-section>
          </q-card>

          <div class="row q-col-gutter-md">
            <!-- 2025-11-21: Tabla de estacionalidad -->
            <div class="col-12 col-md-6">
              <q-card flat bordered>
                <q-card-section>
                  <q-inner-loading :showing="loadingTablas" />
                  <div class="text-subtitle1 q-mb-md">Análisis de Estacionalidad</div>
                  <q-table
                    :rows="tablaEstacionalidad"
                    :columns="columnasEstacionalidad"
                    row-key="mes"
                    dense
                    flat
                    :pagination="{ rowsPerPage: 12 }"
                  >
                    <template v-slot:body-cell-tendencia="props">
                      <q-td :props="props">
                        <q-badge
                          :color="props.row.ranking <= 3 ? 'positive' : (props.row.ranking >= 10 ? 'negative' : 'grey')"
                          :label="props.row.ranking <= 3 ? 'Alto' : (props.row.ranking >= 10 ? 'Bajo' : 'Medio')"
                        />
                      </q-td>
                    </template>
                  </q-table>
                </q-card-section>
              </q-card>
            </div>

            <!-- 2025-11-21: Tabla comparativa año vs año -->
            <div class="col-12 col-md-6">
              <q-card flat bordered>
                <q-card-section>
                  <q-inner-loading :showing="loadingTablas" />
                  <div class="text-subtitle1 q-mb-md">Comparativa Año vs Año</div>
                  <q-table
                    :rows="tablaComparativa"
                    :columns="columnasComparativa"
                    row-key="mes"
                    dense
                    flat
                    :pagination="{ rowsPerPage: 12 }"
                  >
                    <template v-slot:body-cell-variacion="props">
                      <q-td :props="props">
                        <q-badge
                          :color="props.row.variacion >= 0 ? 'positive' : 'negative'"
                          :icon="props.row.variacion >= 0 ? 'trending_up' : 'trending_down'"
                          :label="props.row.variacion.toFixed(2) + '%'"
                        />
                      </q-td>
                    </template>
                  </q-table>
                </q-card-section>
              </q-card>
            </div>
          </div>
        </q-tab-panel>

        <!-- ============================================ -->
        <!-- TAB 5: ANALISIS ORGANIZACIONAL -->
        <!-- ============================================ -->
        <q-tab-panel name="organizacional">
          <!-- 2025-11-21: Ranking de organizaciones -->
          <q-card flat bordered class="q-mb-md">
            <q-card-section>
              <q-inner-loading :showing="loadingTablas" />
              <div class="row items-center q-mb-md">
                <div class="text-subtitle1">Ranking de Organizaciones</div>
                <q-space />
                <q-btn
                  flat
                  dense
                  icon="download"
                  color="primary"
                  label="Exportar"
                  @click="exportarDashboard('organizacional')"
                  :disable="tablaOrganizaciones.length === 0"
                  no-caps
                />
              </div>
              <q-table
                :rows="tablaOrganizaciones"
                :columns="columnasOrganizaciones"
                row-key="asociacion"
                dense
                flat
                :pagination="{ rowsPerPage: 10 }"
              >
                <template v-slot:body-cell-posicion="props">
                  <q-td :props="props">
                    <q-avatar
                      :color="props.row.posicion <= 3 ? 'primary' : 'grey'"
                      text-color="white"
                      size="sm"
                    >
                      {{ props.row.posicion }}
                    </q-avatar>
                  </q-td>
                </template>
              </q-table>
            </q-card-section>
          </q-card>

          <div class="row q-col-gutter-md">
            <!-- 2025-11-21: Gráfico de performance por organización -->
            <div class="col-12 col-md-6">
              <q-card flat bordered>
                <q-card-section>
                  <q-inner-loading :showing="loadingGraficos" />
                  <div class="text-subtitle1 q-mb-md">
                    Performance por Organización
                    <q-icon name="info" size="xs" color="grey-6">
                      <q-tooltip>Acopio mensual de organización seleccionada</q-tooltip>
                    </q-icon>
                  </div>
                  <q-select
                    v-model="orgSeleccionada"
                    :options="listaOrganizaciones"
                    option-label="asociacion"
                    label="Seleccionar Organización"
                    dense
                    outlined
                    @update:model-value="generarGraficoPerformanceOrg"
                    class="q-mb-md"
                  />
                  <canvas ref="chartPerformance" height="150"></canvas>
                </q-card-section>
              </q-card>
            </div>

            <!-- 2025-11-21: Mapa de contribución visual -->
            <div class="col-12 col-md-6">
              <q-card flat bordered>
                <q-card-section>
                  <q-inner-loading :showing="loadingTablas" />
                  <div class="text-subtitle1 q-mb-md">Mapa de Contribución Nacional</div>
                  <q-list dense>
                    <q-item v-for="item in tablaContribucion" :key="item.asociacion">
                      <q-item-section>
                        <q-item-label>{{ item.asociacion }}</q-item-label>
                        <q-linear-progress
                          :value="item.porcentaje / 100"
                          color="primary"
                          size="lg"
                          class="q-mt-xs"
                        />
                      </q-item-section>
                      <q-item-section side>
                        <q-item-label caption>{{ item.porcentaje }}%</q-item-label>
                      </q-item-section>
                    </q-item>
                  </q-list>
                </q-card-section>
              </q-card>
            </div>
          </div>
        </q-tab-panel>
      </q-tab-panels>
    </q-card>
  </q-page>
</template>

<script>
import Chart from 'chart.js/auto'
import moment from 'moment'

export default {
  name: 'DashboardAcopios',
  
  data() {
    return {
      // 2025-11-21: Control de tabs y filtros globales
      tabActiva: 'resumen',
      gestionSeleccionada: moment().year(),
      productoSeleccionado: null,
      
      // 2025-11-21: Catálogos
      gestiones: [],
      productos: [],
      departamentos: [],
      municipios: [],
      listaOrganizaciones: [],
      
      // 2025-11-21: Estados de loading
      loadingActualizar: false,
      loadingKpis: false,
      loadingGraficos: false,
      loadingTablas: false,
      
      // 2025-11-21: Indicadores globales permanentes
      indicadoresGlobales: null,
      
      // 2025-11-21: TAB RESUMEN GENERAL - Datos
      kpisResumen: [],
      datosEvolucionAnual: [],
      topDepartamentos: [],
      topMunicipios: [],
      topOrganizaciones: [],
      
      // 2025-11-21: TAB ANALISIS GEOGRAFICO - Datos
      filtroDepartamento: null,
      filtroMunicipio: null,
      datosGeograficos: [],
      tablaGeografica: [],
      paginacionGeo: { rowsPerPage: 10, sortBy: 'totalKg', descending: true },
      columnasGeograficas: [
        { name: 'ubicacion', label: 'Ubicación', field: 'ubicacion', align: 'left', sortable: true },
        { name: 'productores', label: 'Productores', field: 'productores', align: 'center', sortable: true },
        { name: 'totalKg', label: 'Total Kg', field: 'totalKg', align: 'right', sortable: true, format: val => val?.toFixed(2) },
        { name: 'promedio', label: 'Promedio Kg/Productor', field: 'promedio', align: 'right', sortable: true, format: val => val?.toFixed(2) },
        { name: 'porcentaje', label: '% del Total', field: 'porcentaje', align: 'center', sortable: true }
      ],
      
      // 2025-11-21: TAB ANALISIS POR PRODUCTO - Datos
      datosProductos: [],
      tablaDistribucionProducto: [],
      columnasDistribucionProducto: [
        { name: 'ubicacion', label: 'Ubicación', field: 'ubicacion', align: 'left' },
        { name: 'kg', label: 'Kg', field: 'kg', align: 'right', format: val => val?.toFixed(2) },
        { name: 'porcentaje', label: '% del Producto', field: 'porcentaje', align: 'center', format: val => val?.toFixed(2) + '%' },
        { name: 'productores', label: 'Productores', field: 'productores', align: 'center' }
      ],
      
      // 2025-11-21: TAB ANALISIS TEMPORAL - Datos
      gestionesComparar: [],
      tablaEstacionalidad: [],
      tablaComparativa: [],
      columnasEstacionalidad: [
        { name: 'mes', label: 'Mes', field: 'mes', align: 'left' },
        { name: 'totalKg', label: 'Total Kg', field: 'totalKg', align: 'right', format: val => val?.toFixed(2) },
        { name: 'porcentaje', label: '% del Año', field: 'porcentaje', align: 'center', format: val => val?.toFixed(2) + '%' },
        { name: 'ranking', label: 'Ranking', field: 'ranking', align: 'center' },
        { name: 'tendencia', label: 'Tendencia', field: 'tendencia', align: 'center' }
      ],
      columnasComparativa: [
        { name: 'mes', label: 'Mes', field: 'mes', align: 'left' },
        { name: 'anioActual', label: 'Año Actual (Kg)', field: 'anioActual', align: 'right', format: val => val?.toFixed(2) },
        { name: 'anioAnterior', label: 'Año Anterior (Kg)', field: 'anioAnterior', align: 'right', format: val => val?.toFixed(2) },
        { name: 'diferencia', label: 'Diferencia (Kg)', field: 'diferencia', align: 'right', format: val => val?.toFixed(2) },
        { name: 'variacion', label: 'Variación %', field: 'variacion', align: 'center' }
      ],
      
      // 2025-11-21: TAB ANALISIS ORGANIZACIONAL - Datos
      tablaOrganizaciones: [],
      orgSeleccionada: null,
      tablaContribucion: [],
      columnasOrganizaciones: [
        { name: 'posicion', label: '#', field: 'posicion', align: 'center' },
        { name: 'asociacion', label: 'Asociación', field: 'asociacion', align: 'left', sortable: true },
        { name: 'totalKg', label: 'Total Kg', field: 'totalKg', align: 'right', sortable: true, format: val => val?.toFixed(2) },
        { name: 'productores', label: 'Productores', field: 'productores', align: 'center', sortable: true },
        { name: 'promedio', label: 'Promedio Kg/Productor', field: 'promedio', align: 'right', sortable: true, format: val => val?.toFixed(2) },
        { name: 'porcentaje', label: '% del Total', field: 'porcentaje', align: 'center', sortable: true, format: val => val?.toFixed(2) + '%' }
      ],
      
      // 2025-11-21: Instancias de Chart.js
      chartInstances: []
    }
  },
  
  watch: {
    // 2025-11-21: Lazy loading - cargar datos solo cuando se accede a la tab
    tabActiva(nuevaTab) {
      this.cargarDatosTab(nuevaTab)
    }
  },
  
  mounted() {
    this.cargarDatosIniciales()
  },
  
  beforeUnmount() {
    // 2025-11-21: Destruir todas las instancias de Chart.js para evitar memory leaks
    this.destruirCharts()
  },
  
  methods: {
    // ============================================
    // 2025-11-21: METODOS DE INICIALIZACION
    // ============================================
    
    cargarDatosIniciales() {
      this.generarGestiones()
      this.cargarProductos()
      this.cargarDepartamentos()
      this.gestionesComparar = [this.gestionSeleccionada]
      this.cargarDatosDashboard()
    },
    
    generarGestiones() {
      const anioActual = moment().year()
      this.gestiones = []
      for (let i = 2020; i <= anioActual + 1; i++) {
        this.gestiones.push(i)
      }
    },
    
    async cargarProductos() {
      try {
        const { data } = await this.$axios.get('/productos/tipo/1')
        this.productos = data?.data || data || []
        if (this.productos.length > 0) {
          this.productoSeleccionado = this.productos[0].id
        }
      } catch (error) {
        this.$alert?.error?.('Error al cargar productos')
      }
    },
    
    async cargarDepartamentos() {
      try {
        const { data } = await this.$axios.get('/departamentos')
        this.departamentos = data?.data || data || []
      } catch (error) {
        this.$alert?.error?.('Error al cargar departamentos')
      }
    },
    
    // ============================================
    // 2025-11-21: METODOS DE CONTROL GENERAL
    // ============================================
    
    cambioFiltroGlobal() {
      this.actualizarIndicadoresGlobales()
      this.cargarDatosTab(this.tabActiva)
    },
    
    actualizarDatos() {
      this.loadingActualizar = true
      this.cargarDatosTab(this.tabActiva)
      setTimeout(() => {
        this.loadingActualizar = false
        this.$q.notify({ type: 'positive', message: 'Datos actualizados' })
      }, 1000)
    },
    
    cargarDatosDashboard() {
      this.actualizarIndicadoresGlobales()
      this.cargarDatosTab(this.tabActiva)
    },
    
    cargarDatosTab(nombreTab) {
      switch (nombreTab) {
        case 'resumen':
          this.cargarKpisResumen()
          this.generarGraficoEvolucionAnual()
          this.cargarTop3()
          break
        case 'geografico':
          if (this.filtroDepartamento) {
            this.aplicarFiltroGeografico()
          }
          break
        case 'productos':
          this.generarGraficoProductos()
          this.cargarDistribucionProducto()
          break
        case 'temporal':
          this.generarGraficoTemporal()
          this.cargarEstacionalidad()
          this.cargarComparativaAnual()
          break
        case 'organizacional':
          this.cargarRankingOrganizaciones()
          this.cargarMapaContribucion()
          break
      }
    },
    
    // ============================================
    // 2025-11-21: TAB RESUMEN GENERAL - Métodos
    // ============================================
    
    async actualizarIndicadoresGlobales() {
      try {
        const { data } = await this.$axios.get('/acopiore2', {
          params: { year: this.gestionSeleccionada }
        })
        
        const totalKg = data?.data?.reduce((sum, val) => sum + parseFloat(val || 0), 0) || 0
        
        const anioAnterior = await this.$axios.get('/acopiore2', {
          params: { year: this.gestionSeleccionada - 1 }
        })
        const totalKgAnterior = anioAnterior?.data?.data?.reduce((sum, val) => sum + parseFloat(val || 0), 0) || 0
        
        const crecimiento = totalKgAnterior > 0 
          ? ((totalKg - totalKgAnterior) / totalKgAnterior) * 100 
          : 0
        
        this.indicadoresGlobales = {
          totalKg: totalKg,
          totalProductores: 0,
          promedioKg: 0,
          crecimiento: crecimiento
        }
      } catch (error) {
        this.indicadoresGlobales = {
          totalKg: 0,
          totalProductores: 0,
          promedioKg: 0,
          crecimiento: 0
        }
      }
    },
    
    async cargarKpisResumen() {
      this.loadingKpis = true
      try {
        const { data } = await this.$axios.get('/acopiore2', {
          params: { year: this.gestionSeleccionada }
        })
        
        const totalKg = data?.data?.reduce((sum, val) => sum + parseFloat(val || 0), 0) || 0
        
        this.kpisResumen = [
          {
            label: 'Total Kg Acopiados',
            value: totalKg.toFixed(2),
            icon: 'inbox',
            color: 'primary'
          },
          {
            label: 'Productores Activos',
            value: '0',
            icon: 'people',
            color: 'green'
          },
          {
            label: 'Organizaciones',
            value: '0',
            icon: 'apartment',
            color: 'orange'
          },
          {
            label: 'Crecimiento',
            value: this.indicadoresGlobales?.crecimiento?.toFixed(2) + '%',
            icon: this.indicadoresGlobales?.crecimiento >= 0 ? 'trending_up' : 'trending_down',
            color: this.indicadoresGlobales?.crecimiento >= 0 ? 'positive' : 'negative'
          }
        ]
      } catch (error) {
        this.$alert?.error?.('Error al cargar KPIs')
      } finally {
        this.loadingKpis = false
      }
    },
    
    async generarGraficoEvolucionAnual() {
      this.loadingGraficos = true
      try {
        const { data } = await this.$axios.get('/acopiore2', {
          params: { year: this.gestionSeleccionada }
        })
        
        this.datosEvolucionAnual = data?.data || []
        
        await this.$nextTick()
        
        const canvas = this.$refs.chartEvolucionAnual
        if (!canvas) return
        
        const existingChart = this.chartInstances.find(c => c.canvas === canvas)
        if (existingChart) {
          existingChart.destroy()
          this.chartInstances = this.chartInstances.filter(c => c.canvas !== canvas)
        }
        
        const ctx = canvas.getContext('2d')
        const chart = new Chart(ctx, {
          type: 'line',
          data: {
            labels: data?.labels || [],
            datasets: [{
              label: `Acopio ${this.gestionSeleccionada} (Kg)`,
              data: this.datosEvolucionAnual,
              borderColor: 'rgb(25, 118, 210)',
              backgroundColor: 'rgba(25, 118, 210, 0.1)',
              tension: 0.4,
              fill: true
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
              legend: { display: true, position: 'top' }
            },
            scales: {
              y: { beginAtZero: true }
            }
          }
        })
        
        this.chartInstances.push(chart)
      } catch (error) {
        this.$alert?.error?.('Error al generar gráfico de evolución')
      } finally {
        this.loadingGraficos = false
      }
    },
    
    async cargarTop3() {
      this.loadingTablas = true
      try {
        const { data } = await this.$axios.post('/reporteAcopioProveedorDep', {
          producto_id: this.productoSeleccionado,
          inicio: `${this.gestionSeleccionada}-01-01`,
          fin: `${this.gestionSeleccionada}-12-31`
        })
        
        const departamentos = (data?.data || data || [])
          .map(d => ({
            nombre: d.departamento,
            kg: parseFloat(d.total || 0)
          }))
          .sort((a, b) => b.kg - a.kg)
        
        this.topDepartamentos = departamentos.slice(0, 3)
        this.topMunicipios = []
        this.topOrganizaciones = []
      } catch (error) {
        this.topDepartamentos = []
        this.topMunicipios = []
        this.topOrganizaciones = []
      } finally {
        this.loadingTablas = false
      }
    },
    
    // ============================================
    // 2025-11-21: TAB ANALISIS GEOGRAFICO - Métodos
    // ============================================
    
    async onDepartamentoChange() {
      this.filtroMunicipio = null
      if (this.filtroDepartamento) {
        await this.cargarMunicipios()
      }
    },
    
    async cargarMunicipios() {
      if (!this.filtroDepartamento) return
      try {
        const { data } = await this.$axios.get('/municipios', {
          params: { departamento_id: this.filtroDepartamento }
        })
        this.municipios = data?.data || data || []
      } catch (error) {
        this.$alert?.error?.('Error al cargar municipios')
      }
    },
    
    async aplicarFiltroGeografico() {
      this.loadingGraficos = true
      this.loadingTablas = true
      
      try {
        const endpoint = this.filtroMunicipio 
          ? '/reporteAcopioProveedorMun' 
          : '/reporteAcopioProveedorDep'
        
        const { data } = await this.$axios.post(endpoint, {
          producto_id: this.productoSeleccionado,
          inicio: `${this.gestionSeleccionada}-01-01`,
          fin: `${this.gestionSeleccionada}-12-31`
        })
        
        const datos = data?.data || data || []
        this.datosGeograficos = datos
        
        const totalGeneral = datos.reduce((sum, item) => sum + parseFloat(item.total || 0), 0)
        
        this.tablaGeografica = datos.map(item => {
          const totalKg = parseFloat(item.total || 0)
          const productores = parseInt(item.productores || 0)
          return {
            ubicacion: item.departamento || item.municipio,
            productores: productores,
            totalKg: totalKg,
            promedio: productores > 0 ? totalKg / productores : 0,
            porcentaje: totalGeneral > 0 ? ((totalKg / totalGeneral) * 100).toFixed(2) : 0
          }
        })
        
        await this.generarGraficoGeografico()
      } catch (error) {
        this.$alert?.error?.('Error al cargar datos geográficos')
      } finally {
        this.loadingGraficos = false
        this.loadingTablas = false
      }
    },
    
    async generarGraficoGeografico() {
      await this.$nextTick()
      
      const canvas = this.$refs.chartGeografico
      if (!canvas || this.datosGeograficos.length === 0) return
      
      const existingChart = this.chartInstances.find(c => c.canvas === canvas)
      if (existingChart) {
        existingChart.destroy()
        this.chartInstances = this.chartInstances.filter(c => c.canvas !== canvas)
      }
      
      const ctx = canvas.getContext('2d')
      const chart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: this.datosGeograficos.map(d => d.departamento || d.municipio),
          datasets: [{
            label: 'Acopio (Kg)',
            data: this.datosGeograficos.map(d => parseFloat(d.total || 0)),
            backgroundColor: 'rgba(25, 118, 210, 0.7)',
            borderColor: 'rgb(25, 118, 210)',
            borderWidth: 1
          }]
        },
        options: {
          indexAxis: 'y',
          responsive: true,
          maintainAspectRatio: true,
          plugins: {
            legend: { display: false }
          },
          scales: {
            x: { beginAtZero: true }
          }
        }
      })
      
      this.chartInstances.push(chart)
    },
    
    // ============================================
    // 2025-11-21: TAB ANALISIS POR PRODUCTO - Métodos
    // ============================================
    
    async generarGraficoProductos() {
      this.loadingGraficos = true
      try {
        const { data } = await this.$axios.get('/acopiore1', {
          params: { year: this.gestionSeleccionada }
        })
        
        this.datosProductos = data?.data || []
        
        await this.$nextTick()
        
        const canvas = this.$refs.chartProductos
        if (!canvas) return
        
        const existingChart = this.chartInstances.find(c => c.canvas === canvas)
        if (existingChart) {
          existingChart.destroy()
          this.chartInstances = this.chartInstances.filter(c => c.canvas !== canvas)
        }
        
        const ctx = canvas.getContext('2d')
        const chart = new Chart(ctx, {
          type: 'doughnut',
          data: {
            labels: this.datosProductos.map(d => d.producto),
            datasets: [{
              data: this.datosProductos.map(d => parseFloat(d.total_kg || 0)),
              backgroundColor: [
                'rgba(255, 99, 132, 0.7)',
                'rgba(54, 162, 235, 0.7)',
                'rgba(255, 206, 86, 0.7)',
                'rgba(75, 192, 192, 0.7)',
                'rgba(153, 102, 255, 0.7)'
              ],
              borderWidth: 2
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
              legend: { position: 'bottom' }
            }
          }
        })
        
        this.chartInstances.push(chart)
      } catch (error) {
        this.$alert?.error?.('Error al generar gráfico de productos')
      } finally {
        this.loadingGraficos = false
      }
    },
    
    async cargarDistribucionProducto() {
      this.loadingTablas = true
      try {
        const { data } = await this.$axios.post('/reporteAcopioProveedorDep', {
          producto_id: this.productoSeleccionado,
          inicio: `${this.gestionSeleccionada}-01-01`,
          fin: `${this.gestionSeleccionada}-12-31`
        })
        
        const datos = data?.data || data || []
        const totalProducto = datos.reduce((sum, item) => sum + parseFloat(item.total || 0), 0)
        
        this.tablaDistribucionProducto = datos.slice(0, 5).map(item => ({
          ubicacion: item.departamento,
          kg: parseFloat(item.total || 0),
          porcentaje: totalProducto > 0 ? ((parseFloat(item.total || 0) / totalProducto) * 100) : 0,
          productores: parseInt(item.productores || 0)
        }))
      } catch (error) {
        this.tablaDistribucionProducto = []
      } finally {
        this.loadingTablas = false
      }
    },
    
    // ============================================
    // 2025-11-21: TAB ANALISIS TEMPORAL - Métodos
    // ============================================
    
    async generarGraficoTemporal() {
      this.loadingGraficos = true
      try {
        const datasets = []
        const colores = [
          'rgb(25, 118, 210)',
          'rgb(76, 175, 80)',
          'rgb(255, 152, 0)'
        ]
        
        for (let i = 0; i < this.gestionesComparar.length && i < 3; i++) {
          const gestion = this.gestionesComparar[i]
          const { data } = await this.$axios.get('/acopiore2', {
            params: { year: gestion }
          })
          
          datasets.push({
            label: `Gestión ${gestion}`,
            data: data?.data || [],
            borderColor: colores[i],
            backgroundColor: colores[i].replace('rgb', 'rgba').replace(')', ', 0.1)'),
            tension: 0.4
          })
        }
        
        await this.$nextTick()
        
        const canvas = this.$refs.chartTemporal
        if (!canvas) return
        
        const existingChart = this.chartInstances.find(c => c.canvas === canvas)
        if (existingChart) {
          existingChart.destroy()
          this.chartInstances = this.chartInstances.filter(c => c.canvas !== canvas)
        }
        
        const ctx = canvas.getContext('2d')
        const chart = new Chart(ctx, {
          type: 'line',
          data: {
            labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                     'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            datasets: datasets
          },
          options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
              legend: { display: true, position: 'top' }
            },
            scales: {
              y: { beginAtZero: true }
            }
          }
        })
        
        this.chartInstances.push(chart)
      } catch (error) {
        this.$alert?.error?.('Error al generar gráfico temporal')
      } finally {
        this.loadingGraficos = false
      }
    },
    
    async cargarEstacionalidad() {
      this.loadingTablas = true
      try {
        const { data } = await this.$axios.post('/reportePorcentual', {
          producto_id: this.productoSeleccionado,
          inicio: `${this.gestionSeleccionada}-01-01`,
          fin: `${this.gestionSeleccionada}-12-31`
        })
        
        const datos = data?.data || data || []
        const mesesOrdenados = datos
          .map((item, index) => ({
            mes: item.nombre_mes || `Mes ${index + 1}`,
            totalKg: parseFloat(item.acopio_kg || 0),
            porcentaje: parseFloat(item.porcentaje || 0),
            ranking: 0
          }))
          .sort((a, b) => b.totalKg - a.totalKg)
        
        mesesOrdenados.forEach((item, index) => {
          item.ranking = index + 1
        })
        
        this.tablaEstacionalidad = mesesOrdenados.sort((a, b) => a.mes.localeCompare(b.mes))
      } catch (error) {
        this.tablaEstacionalidad = []
      } finally {
        this.loadingTablas = false
      }
    },
    
    async cargarComparativaAnual() {
      this.loadingTablas = true
      try {
        const [actualData, anteriorData] = await Promise.all([
          this.$axios.get('/acopiore2', { params: { year: this.gestionSeleccionada } }),
          this.$axios.get('/acopiore2', { params: { year: this.gestionSeleccionada - 1 } })
        ])
        
        const actual = actualData?.data?.data || []
        const anterior = anteriorData?.data?.data || []
        const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                       'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
        
        this.tablaComparativa = meses.map((mes, index) => {
          const anioActual = parseFloat(actual[index] || 0)
          const anioAnterior = parseFloat(anterior[index] || 0)
          const diferencia = anioActual - anioAnterior
          const variacion = anioAnterior > 0 ? ((diferencia / anioAnterior) * 100) : 0
          
          return {
            mes: mes,
            anioActual: anioActual,
            anioAnterior: anioAnterior,
            diferencia: diferencia,
            variacion: variacion
          }
        })
      } catch (error) {
        this.tablaComparativa = []
      } finally {
        this.loadingTablas = false
      }
    },
    
    // ============================================
    // 2025-11-21: TAB ANALISIS ORGANIZACIONAL - Métodos
    // ============================================
    
    async cargarRankingOrganizaciones() {
      this.loadingTablas = true
      try {
        const { data } = await this.$axios.post('/reportAcopioOrg', {
          gestion: this.gestionSeleccionada,
          producto_id: this.productoSeleccionado
        })
        
        const datos = data?.data || data || []
        const totalGeneral = datos.reduce((sum, item) => {
          const total = Object.keys(item)
            .filter(key => key !== 'asociacion')
            .reduce((s, key) => s + parseFloat(item[key] || 0), 0)
          return sum + total
        }, 0)
        
        this.listaOrganizaciones = datos
        this.tablaOrganizaciones = datos
          .map(item => {
            const totalKg = Object.keys(item)
              .filter(key => key !== 'asociacion')
              .reduce((sum, key) => sum + parseFloat(item[key] || 0), 0)
            
            return {
              asociacion: item.asociacion,
              totalKg: totalKg,
              productores: 0,
              promedio: 0,
              porcentaje: totalGeneral > 0 ? ((totalKg / totalGeneral) * 100) : 0
            }
          })
          .sort((a, b) => b.totalKg - a.totalKg)
          .map((item, index) => ({
            ...item,
            posicion: index + 1
          }))
      } catch (error) {
        this.tablaOrganizaciones = []
        this.listaOrganizaciones = []
      } finally {
        this.loadingTablas = false
      }
    },
    
    async generarGraficoPerformanceOrg() {
      if (!this.orgSeleccionada) return
      
      this.loadingGraficos = true
      try {
        await this.$nextTick()
        
        const canvas = this.$refs.chartPerformance
        if (!canvas) return
        
        const existingChart = this.chartInstances.find(c => c.canvas === canvas)
        if (existingChart) {
          existingChart.destroy()
          this.chartInstances = this.chartInstances.filter(c => c.canvas !== canvas)
        }
        
        const meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic']
        const datosOrg = meses.map(mes => parseFloat(this.orgSeleccionada[mes] || 0))
        
        const ctx = canvas.getContext('2d')
        const chart = new Chart(ctx, {
          type: 'bar',
          data: {
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            datasets: [{
              label: this.orgSeleccionada.asociacion,
              data: datosOrg,
              backgroundColor: 'rgba(25, 118, 210, 0.7)',
              borderColor: 'rgb(25, 118, 210)',
              borderWidth: 1
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
              legend: { display: true }
            },
            scales: {
              y: { beginAtZero: true }
            }
          }
        })
        
        this.chartInstances.push(chart)
      } catch (error) {
        this.$alert?.error?.('Error al generar gráfico de performance')
      } finally {
        this.loadingGraficos = false
      }
    },
    
    cargarMapaContribucion() {
      this.tablaContribucion = this.tablaOrganizaciones.map(item => ({
        asociacion: item.asociacion,
        porcentaje: item.porcentaje
      }))
    },
    
    // ============================================
    // 2025-11-21: METODOS AUXILIARES
    // ============================================
    
    exportarDashboard(tipo) {
      this.$q.notify({
        type: 'info',
        message: 'Funcionalidad de exportación en desarrollo'
      })
    },
    
    destruirCharts() {
      this.chartInstances.forEach(chart => {
        if (chart && typeof chart.destroy === 'function') {
          chart.destroy()
        }
      })
      this.chartInstances = []
    }
  }
}
</script>

<style scoped>
.chart-container {
  position: relative;
  height: 300px;
}
</style>
