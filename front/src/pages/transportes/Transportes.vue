<template>
  <q-page class="q-pa-md">
    <!-- Encabezado con título y estadísticas rápidas -->
    <div class="row q-mb-md items-center">
      <div class="col-12 col-md-6">
        <div class="text-h5 text-weight-bold">Gestión de Transportes</div>
        <div class="text-caption text-grey-7">Control de vehículos y estadísticas de uso</div>
      </div>
      <div class="col-12 col-md-6">
        <div class="row q-gutter-sm justify-end">
          <q-btn color="positive" icon="add_circle_outline" label="Nuevo Transporte" no-caps @click="openCreate" />
          <q-btn color="primary" icon="refresh" label="Actualizar" no-caps :loading="loading" @click="fetch" />
        </div>
      </div>
    </div>

    <!-- Tarjetas de estadísticas -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-sm-6 col-md-2">
        <q-card class="bg-primary text-white">
          <q-card-section>
            <div class="text-h6">{{ rows.length }}</div>
            <div class="text-caption">Total Transportes</div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Estadísticas de ENTRADA (Acopios) -->
      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="bg-positive text-white">
          <q-card-section>
            <div class="row items-center">
              <q-icon name="input" size="sm" class="q-mr-sm" />
              <div>
                <div class="text-h6">{{ estadisticas.entrada?.total_viajes || 0 }}</div>
                <div class="text-caption"> Viajes Entrada (Acopios)</div>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-2">
        <q-card class="bg-teal text-white">
          <q-card-section>
            <div class="text-h6">{{ formatNumber(estadisticas.entrada?.kg_transportados || 0) }} kg</div>
            <div class="text-caption">Kg Transportados</div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Estadísticas de SALIDA (Ventas) -->
      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="bg-info text-white">
          <q-card-section>
            <div class="row items-center">
              <q-icon name="output" size="sm" class="q-mr-sm" />
              <div>
                <div class="text-h6">{{ estadisticas.salida?.total_entregas || 0 }}</div>
                <div class="text-caption"> Entregas (Ventas)</div>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-2">
        <q-card class="bg-orange text-white">
          <q-card-section>
            <div class="text-h6">{{ estadisticas.entrada?.alertas_temperatura + estadisticas.entrada?.alertas_tiempo ||
              0 }}</div>
            <div class="text-caption">Alertas</div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Tabs para diferentes vistas -->
    <q-tabs v-model="tab" class="text-primary" align="left" dense>
      <q-tab name="lista" icon="list" label="Lista de Transportes" />
      <q-tab name="estadisticas" icon="bar_chart" label="Estadísticas" />
      <q-tab name="alertas" icon="warning" label="Alertas" />
    </q-tabs>
    <q-separator />

    <q-tab-panels v-model="tab" animated class="q-mt-md">
      <!-- Tab: Lista de transportes -->
      <q-tab-panel name="lista">
        <q-table :rows="rows" :columns="columns" row-key="id" flat bordered dense wrap-cells
          :rows-per-page-options="[10, 25, 50, 0]" :loading="loading" :filter="filter" pagination.sync="pagination">
          <template #top-right>
            <q-input v-model="filter" dense outlined placeholder="Buscar por empresa, placa o responsable"
              @update:model-value="fetchDebounced" style="width: 300px">
              <template #append><q-icon name="search" /></template>
            </q-input>
          </template>

          <template #body-cell-estadisticas="props">
            <q-td :props="props">
              <div class="text-caption">
                <div><strong>Acopios:</strong> {{ props.row.estadisticas_uso?.total_acopios || 0 }}</div>
                <div><strong>Ventas:</strong> {{ props.row.estadisticas_uso?.total_ventas || 0 }}</div>
              </div>
            </q-td>
          </template>

          <template #body-cell-alertas="props">
            <q-td :props="props">
              <q-badge v-if="props.row.estadisticas_uso?.alertas_temperatura > 0" color="red" class="q-mr-xs">
                🌡️ {{ props.row.estadisticas_uso.alertas_temperatura }}
              </q-badge>
              <q-badge v-if="props.row.estadisticas_uso?.alertas_tiempo > 0" color="orange">
                ⏱️ {{ props.row.estadisticas_uso.alertas_tiempo }}
              </q-badge>
              <span
                v-if="!props.row.estadisticas_uso?.alertas_temperatura && !props.row.estadisticas_uso?.alertas_tiempo"
                class="text-grey-6">
                Sin alertas
              </span>
            </q-td>
          </template>

          <template #body-cell-actions="props">
            <q-td :props="props" class="text-right">
              <q-btn-group flat>
                <q-btn flat dense color="info" icon="info" size="sm" @click="verDetalle(props.row)">
                  <q-tooltip>Ver detalle</q-tooltip>
                </q-btn>
                <q-btn flat dense color="primary" icon="edit" size="sm" @click="openEdit(props.row)">
                  <q-tooltip>Editar</q-tooltip>
                </q-btn>
                <q-btn flat dense color="negative" icon="delete" size="sm" @click="onDelete(props.row)">
                  <q-tooltip>Eliminar</q-tooltip>
                </q-btn>
              </q-btn-group>
            </q-td>
          </template>
        </q-table>
      </q-tab-panel>

      <!-- Tab: Estadísticas -->
      <q-tab-panel name="estadisticas">
        <div class="row q-col-gutter-md">
          <!-- Estadísticas de ENTRADA -->
          <div class="col-12 col-md-6">
            <q-card>
              <q-card-section class="bg-positive text-white">
                <div class="row items-center">
                  <q-icon name="input" size="md" class="q-mr-sm" />
                  <div class="text-h6">Transportes de Entrada (Acopios)</div>
                </div>
              </q-card-section>
              <q-separator />
              <q-card-section>
                <q-list separator>
                  <q-item>
                    <q-item-section avatar><q-icon name="local_shipping" color="positive" /></q-item-section>
                    <q-item-section>
                      <q-item-label>Total de viajes</q-item-label>
                      <q-item-label caption>{{ formatNumber(estadisticas.entrada?.total_viajes || 0) }}</q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-item>
                    <q-item-section avatar><q-icon name="scale" color="teal" /></q-item-section>
                    <q-item-section>
                      <q-item-label>Kg transportados</q-item-label>
                      <q-item-label caption>{{ formatNumber(estadisticas.entrada?.kg_transportados || 0) }}
                        kg</q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-item>
                    <q-item-section avatar><q-icon name="place" color="info" /></q-item-section>
                    <q-item-section>
                      <q-item-label>Distancia total recorrida</q-item-label>
                      <q-item-label caption>{{ formatNumber(estadisticas.entrada?.distancia_total_km || 0) }}
                        km</q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-item>
                    <q-item-section avatar><q-icon name="people" color="primary" /></q-item-section>
                    <q-item-section>
                      <q-item-label>Productores atendidos</q-item-label>
                      <q-item-label caption>{{ estadisticas.entrada?.productores_atendidos || 0 }}</q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-item>
                    <q-item-section avatar><q-icon name="warning" color="orange" /></q-item-section>
                    <q-item-section>
                      <q-item-label>Alertas temperatura / tiempo</q-item-label>
                      <q-item-label caption>
                        🌡️ {{ estadisticas.entrada?.alertas_temperatura || 0 }} /
                        ⏱️ {{ estadisticas.entrada?.alertas_tiempo || 0 }}
                      </q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-item>
                    <q-item-section avatar><q-icon name="verified" color="positive" /></q-item-section>
                    <q-item-section>
                      <q-item-label>Cumplimiento </q-item-label>
                      <q-item-label caption>{{ (estadisticas.entrada?.porcentaje_cumplimiento || 100).toFixed(1)
                        }}%</q-item-label>
                    </q-item-section>
                  </q-item>
                </q-list>
              </q-card-section>
            </q-card>
          </div>

          <!-- Estadísticas de SALIDA -->
          <div class="col-12 col-md-6">
            <q-card>
              <q-card-section class="bg-info text-white">
                <div class="row items-center">
                  <q-icon name="output" size="md" class="q-mr-sm" />
                  <div class="text-h6"> Transportes de Salida (Ventas)</div>
                </div>
              </q-card-section>
              <q-separator />
              <q-card-section>
                <q-list separator>
                  <q-item>
                    <q-item-section avatar><q-icon name="local_shipping" color="info" /></q-item-section>
                    <q-item-section>
                      <q-item-label>Total de entregas</q-item-label>
                      <q-item-label caption>{{ formatNumber(estadisticas.salida?.total_entregas || 0) }}</q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-item>
                    <q-item-section avatar><q-icon name="scale" color="teal" /></q-item-section>
                    <q-item-section>
                      <q-item-label>Kg entregados</q-item-label>
                      <q-item-label caption>{{ formatNumber(estadisticas.salida?.kg_entregados || 0) }}
                        kg</q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-item>
                    <q-item-section avatar><q-icon name="payments" color="positive" /></q-item-section>
                    <q-item-section>
                      <q-item-label>Valor total</q-item-label>
                      <q-item-label caption>Bs. {{ formatNumber(estadisticas.salida?.valor_total || 0) }}</q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-item>
                    <q-item-section avatar><q-icon name="business" color="primary" /></q-item-section>
                    <q-item-section>
                      <q-item-label>Clientes atendidos</q-item-label>
                      <q-item-label caption>{{ estadisticas.salida?.clientes_atendidos || 0 }}</q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-item>
                    <q-item-section avatar><q-icon name="check_circle" color="positive" /></q-item-section>
                    <q-item-section>
                      <q-item-label>Promedio por entrega</q-item-label>
                      <q-item-label caption>{{ formatNumber(estadisticas.salida?.kg_promedio || 0) }} kg</q-item-label>
                    </q-item-section>
                  </q-item>
                  <q-item>
                    <q-item-section avatar><q-icon name="attach_money" color="orange" /></q-item-section>
                    <q-item-section>
                      <q-item-label>Valor promedio</q-item-label>
                      <q-item-label caption>Bs. {{ formatNumber(estadisticas.salida?.valor_promedio || 0)
                        }}</q-item-label>
                    </q-item-section>
                  </q-item>
                </q-list>
              </q-card-section>
            </q-card>
          </div>

          <!-- Totales Generales -->
          <div class="col-12">
            <q-card>
              <q-card-section>
                <div class="text-h6">Totales Generales</div>
              </q-card-section>
              <q-separator />
              <q-card-section>
                <div class="row q-col-gutter-md">
                  <div class="col-12 col-md-4">
                    <q-item>
                      <q-item-section avatar><q-icon name="local_shipping" color="primary" size="lg" /></q-item-section>
                      <q-item-section>
                        <q-item-label>Transportes Registrados</q-item-label>
                        <q-item-label caption class="text-h6">{{ rows.length }}</q-item-label>
                      </q-item-section>
                    </q-item>
                  </div>
                  <div class="col-12 col-md-4">
                    <q-item>
                      <q-item-section avatar><q-icon name="route" color="positive" size="lg" /></q-item-section>
                      <q-item-section>
                        <q-item-label>Total Viajes (Entrada + Salida)</q-item-label>
                        <q-item-label caption class="text-h6">{{ formatNumber(estadisticas.totales?.total_viajes || 0)
                          }}</q-item-label>
                      </q-item-section>
                    </q-item>
                  </div>
                  <div class="col-12 col-md-4">
                    <q-item>
                      <q-item-section avatar><q-icon name="scale" color="teal" size="lg" /></q-item-section>
                      <q-item-section>
                        <q-item-label>Total Kg Movilizados</q-item-label>
                        <q-item-label caption class="text-h6">{{ formatNumber(estadisticas.totales?.total_kg || 0) }}
                          kg</q-item-label>
                      </q-item-section>
                    </q-item>
                  </div>
                </div>
              </q-card-section>
            </q-card>
          </div>

          <!-- Top 5 transportes más utilizados -->
          <div class="col-12">
            <q-card>
              <q-card-section>
                <div class="text-h6">Transportes Más Utilizados</div>
              </q-card-section>
              <q-separator />
              <q-card-section>
                <q-list separator>
                  <q-item v-for="(t, idx) in topTransportes" :key="t.id">
                    <q-item-section avatar>
                      <q-avatar :color="idx === 0 ? 'amber' : idx === 1 ? 'grey-5' : idx === 2 ? 'orange-8' : 'primary'"
                        text-color="white">
                        {{ idx + 1 }}
                      </q-avatar>
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>{{ t.empresa }} - {{ t.placa }}</q-item-label>
                      <q-item-label caption>{{ t.responsable }}</q-item-label>
                    </q-item-section>
                    <q-item-section side>
                      <q-item-label>{{ t.estadisticas_uso?.total_viajes || 0 }} viajes</q-item-label>
                    </q-item-section>
                  </q-item>
                </q-list>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </q-tab-panel>

      <!-- Tab: Alertas -->
      <q-tab-panel name="alertas">
        <q-card>
          <q-card-section>
            <div class="text-h6">Transportes con Alertas</div>
            <div class="text-caption text-grey-7">Vehículos con incidencias en temperatura o tiempo</div>
          </q-card-section>
          <q-separator />
          <q-card-section>
            <q-list separator>
              <q-item v-for="t in transportesConAlertas" :key="t.id">
                <q-item-section avatar>
                  <q-avatar color="red" text-color="white" icon="warning" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>{{ t.empresa }} - {{ t.placa }}</q-item-label>
                  <q-item-label caption>Responsable: {{ t.responsable }}</q-item-label>
                </q-item-section>
                <q-item-section side>
                  <div>
                    <q-badge v-if="t.estadisticas_uso?.alertas_temperatura > 0" color="red" class="q-mr-xs">
                      {{ t.estadisticas_uso.alertas_temperatura }}
                    </q-badge>
                    <q-badge v-if="t.estadisticas_uso?.alertas_tiempo > 0" color="orange">
                      {{ t.estadisticas_uso.alertas_tiempo }}
                    </q-badge>
                  </div>
                </q-item-section>
                <q-item-section side>
                  <q-btn flat dense color="info" icon="visibility" @click="verDetalle(t)">
                    <q-tooltip>Ver detalle</q-tooltip>
                  </q-btn>
                </q-item-section>
              </q-item>
              <q-item v-if="transportesConAlertas.length === 0">
                <q-item-section>
                  <q-item-label class="text-center text-grey-6">
                    <q-icon name="check_circle" size="lg" color="positive" />
                    <div>No hay transportes con alertas</div>
                  </q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </q-card-section>
        </q-card>
      </q-tab-panel>
    </q-tab-panels>

    <!-- Diálogo crear/editar -->
    <q-dialog v-model="dlg.open" persistent>
      <q-card style="min-width: 600px; max-width: 900px">
        <q-card-section class="bg-primary text-white">
          <div class="text-h6">
            <q-icon name="local_shipping" class="q-mr-sm" />
            {{ dlg.mode === 'create' ? 'Nuevo Transporte' : 'Editar Transporte' }}
          </div>
        </q-card-section>
        <q-separator />
        <q-card-section>
          <q-form @submit.prevent="onSubmit" ref="formRef">
            <div class="row q-col-gutter-md">
              <div class="col-12">
                <div class="text-subtitle2 text-weight-bold q-mb-sm">Información del Vehículo</div>
              </div>
              <div class="col-12 col-md-6">
                <q-input v-model="form.empresa" dense outlined label="Empresa / Razón social *"
                  :rules="[val => !!val || 'Campo requerido']">
                  <template #prepend>
                    <q-icon name="business" />
                  </template>
                </q-input>
              </div>
              <div class="col-12 col-md-6">
                <q-input v-model="form.placa" dense outlined label="Placa *"
                  :rules="[val => !!val || 'Campo requerido']"
                  @update:model-value="val => form.placa = val.toUpperCase()">
                  <template #prepend>
                    <q-icon name="pin" />
                  </template>
                </q-input>
              </div>
              <div class="col-12 col-md-6">
                <q-input v-model="form.responsable" dense outlined label="Responsable / Conductor *"
                  :rules="[val => !!val || 'Campo requerido']">
                  <template #prepend>
                    <q-icon name="person" />
                  </template>
                </q-input>
              </div>
              <div class="col-12 col-md-6">
                <q-input v-model="form.telefono" dense outlined label="Teléfono de Contacto" mask="####-####">
                  <template #prepend>
                    <q-icon name="phone" />
                  </template>
                </q-input>
              </div>
              <div class="col-12">
                <q-input v-model="form.observaciones" dense outlined type="textarea" rows="3" label="Observaciones">
                  <template #prepend>
                    <q-icon name="notes" />
                  </template>
                </q-input>
              </div>
            </div>
          </q-form>
        </q-card-section>
        <q-separator />
        <q-card-actions align="right" class="q-pa-md">
          <q-btn flat label="Cancelar" color="grey-7" v-close-popup />
          <q-btn unelevated color="primary" :loading="saving" :disable="!canSubmit" label="Guardar" icon="save"
            @click="onSubmit" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Diálogo de detalle -->
    <q-dialog v-model="dlgDetalle.open">
      <q-card style="min-width: 800px; max-width: 1200px">
        <q-card-section class="bg-info text-white">
          <div class="text-h6">
            <q-icon name="info" class="q-mr-sm" />
            Detalle del Transporte
          </div>
        </q-card-section>
        <q-separator />
        <q-card-section v-if="dlgDetalle.transporte">
          <!-- Información básica -->
          <div class="row q-col-gutter-md q-mb-md">
            <div class="col-12 col-md-4">
              <q-item>
                <q-item-section avatar><q-icon name="business" color="primary" size="md" /></q-item-section>
                <q-item-section>
                  <q-item-label caption>Empresa</q-item-label>
                  <q-item-label class="text-weight-bold">{{ dlgDetalle.transporte.empresa }}</q-item-label>
                </q-item-section>
              </q-item>
            </div>
            <div class="col-12 col-md-4">
              <q-item>
                <q-item-section avatar><q-icon name="pin" color="primary" size="md" /></q-item-section>
                <q-item-section>
                  <q-item-label caption>Placa</q-item-label>
                  <q-item-label class="text-weight-bold">{{ dlgDetalle.transporte.placa }}</q-item-label>
                </q-item-section>
              </q-item>
            </div>
            <div class="col-12 col-md-4">
              <q-item>
                <q-item-section avatar><q-icon name="person" color="primary" size="md" /></q-item-section>
                <q-item-section>
                  <q-item-label caption>Responsable</q-item-label>
                  <q-item-label class="text-weight-bold">{{ dlgDetalle.transporte.responsable }}</q-item-label>
                </q-item-section>
              </q-item>
            </div>
          </div>

          <!-- Tabs para Entrada y Salida -->
          <q-tabs v-model="dlgDetalle.tab" class="text-primary" dense>
            <q-tab name="entrada" icon="input" label="Entrada (Acopios)" />
            <q-tab name="salida" icon="output" label="Salida (Ventas)" />
            <q-tab name="alertas" icon="warning" label="Alertas" />
          </q-tabs>
          <q-separator />

          <q-tab-panels v-model="dlgDetalle.tab" animated>
            <!-- Tab Entrada -->
            <q-tab-panel name="entrada">
              <div class="row q-col-gutter-md" v-if="dlgDetalle.estadisticas">
                <div class="col-12 col-sm-6 col-md-4">
                  <q-card flat bordered>
                    <q-card-section>
                      <div class="text-center">
                        <q-icon name="local_shipping" color="positive" size="lg" />
                        <div class="text-h6">{{ formatNumber(dlgDetalle.estadisticas.entrada?.total_viajes || 0) }}
                        </div>
                        <div class="text-caption">Viajes Realizados</div>
                      </div>
                    </q-card-section>
                  </q-card>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                  <q-card flat bordered>
                    <q-card-section>
                      <div class="text-center">
                        <q-icon name="scale" color="teal" size="lg" />
                        <div class="text-h6">{{ formatNumber(dlgDetalle.estadisticas.entrada?.kg_transportados || 0) }}
                          kg
                        </div>
                        <div class="text-caption">Kg Transportados</div>
                      </div>
                    </q-card-section>
                  </q-card>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                  <q-card flat bordered>
                    <q-card-section>
                      <div class="text-center">
                        <q-icon name="place" color="info" size="lg" />
                        <div class="text-h6">{{ formatNumber(dlgDetalle.estadisticas.entrada?.distancia_total_km || 0)
                          }} km
                        </div>
                        <div class="text-caption">Distancia Total</div>
                      </div>
                    </q-card-section>
                  </q-card>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                  <q-card flat bordered>
                    <q-card-section>
                      <div class="text-center">
                        <q-icon name="people" color="primary" size="lg" />
                        <div class="text-h6">{{ dlgDetalle.estadisticas.entrada?.productores_atendidos || 0 }}</div>
                        <div class="text-caption">Productores Atendidos</div>
                      </div>
                    </q-card-section>
                  </q-card>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                  <q-card flat bordered>
                    <q-card-section>
                      <div class="text-center">
                        <q-icon name="verified" color="positive" size="lg" />
                        <div class="text-h6">{{ (dlgDetalle.estadisticas.entrada?.porcentaje_cumplimiento ||
                          100).toFixed(1)
                          }}%</div>
                        <div class="text-caption">Cumplimiento</div>
                      </div>
                    </q-card-section>
                  </q-card>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                  <q-card flat bordered>
                    <q-card-section>
                      <div class="text-center">
                        <q-icon name="speed" color="orange" size="lg" />
                        <div class="text-h6">{{ formatNumber(dlgDetalle.estadisticas.entrada?.kg_promedio || 0) }} kg
                        </div>
                        <div class="text-caption">Promedio por Viaje</div>
                      </div>
                    </q-card-section>
                  </q-card>
                </div>
              </div>
            </q-tab-panel>

            <!-- Tab Salida -->
            <q-tab-panel name="salida">
              <div class="row q-col-gutter-md" v-if="dlgDetalle.estadisticas">
                <div class="col-12 col-sm-6 col-md-4">
                  <q-card flat bordered>
                    <q-card-section>
                      <div class="text-center">
                        <q-icon name="local_shipping" color="info" size="lg" />
                        <div class="text-h6">{{ formatNumber(dlgDetalle.estadisticas.salida?.total_entregas || 0) }}
                        </div>
                        <div class="text-caption">Entregas Realizadas</div>
                      </div>
                    </q-card-section>
                  </q-card>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                  <q-card flat bordered>
                    <q-card-section>
                      <div class="text-center">
                        <q-icon name="scale" color="teal" size="lg" />
                        <div class="text-h6">{{ formatNumber(dlgDetalle.estadisticas.salida?.kg_entregados || 0) }} kg
                        </div>
                        <div class="text-caption">Kg Entregados</div>
                      </div>
                    </q-card-section>
                  </q-card>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                  <q-card flat bordered>
                    <q-card-section>
                      <div class="text-center">
                        <q-icon name="payments" color="positive" size="lg" />
                        <div class="text-h6">Bs. {{ formatNumber(dlgDetalle.estadisticas.salida?.valor_total || 0) }}
                        </div>
                        <div class="text-caption">Valor Total</div>
                      </div>
                    </q-card-section>
                  </q-card>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                  <q-card flat bordered>
                    <q-card-section>
                      <div class="text-center">
                        <q-icon name="business" color="primary" size="lg" />
                        <div class="text-h6">{{ dlgDetalle.estadisticas.salida?.clientes_atendidos || 0 }}</div>
                        <div class="text-caption">Clientes Atendidos</div>
                      </div>
                    </q-card-section>
                  </q-card>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                  <q-card flat bordered>
                    <q-card-section>
                      <div class="text-center">
                        <q-icon name="check_circle" color="positive" size="lg" />
                        <div class="text-h6">{{ formatNumber(dlgDetalle.estadisticas.salida?.kg_promedio || 0) }} kg
                        </div>
                        <div class="text-caption">Promedio por Entrega</div>
                      </div>
                    </q-card-section>
                  </q-card>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                  <q-card flat bordered>
                    <q-card-section>
                      <div class="text-center">
                        <q-icon name="attach_money" color="orange" size="lg" />
                        <div class="text-h6">Bs. {{ formatNumber(dlgDetalle.estadisticas.salida?.valor_promedio || 0) }}
                        </div>
                        <div class="text-caption">Valor Promedio</div>
                      </div>
                    </q-card-section>
                  </q-card>
                </div>
              </div>
            </q-tab-panel>

            <!-- Tab Alertas -->
            <q-tab-panel name="alertas">
              <div class="row q-col-gutter-md" v-if="dlgDetalle.estadisticas">
                <div class="col-12 col-md-6">
                  <q-card flat bordered>
                    <q-card-section>
                      <div class="text-center">
                        <q-icon name="warning"
                          :color="(dlgDetalle.estadisticas.entrada?.alertas_temperatura || 0) > 0 ? 'red' : 'positive'"
                          size="xl" />
                        <div class="text-h4 q-mt-sm">{{ dlgDetalle.estadisticas.entrada?.alertas_temperatura || 0 }}
                        </div>
                        <div class="text-subtitle1">Alertas de Temperatura</div>
                        <div class="text-caption text-grey-7 q-mt-sm">
                          Transportes que excedieron los límites de temperatura.
                        </div>
                      </div>
                    </q-card-section>
                  </q-card>
                </div>
                <div class="col-12 col-md-6">
                  <q-card flat bordered>
                    <q-card-section>
                      <div class="text-center">
                        <q-icon name="schedule"
                          :color="(dlgDetalle.estadisticas.entrada?.alertas_tiempo || 0) > 0 ? 'orange' : 'positive'"
                          size="xl" />
                        <div class="text-h4 q-mt-sm">{{ dlgDetalle.estadisticas.entrada?.alertas_tiempo || 0 }}</div>
                        <div class="text-subtitle1">Alertas de Tiempo</div>
                        <div class="text-caption text-grey-7 q-mt-sm">
                          Transportes que excedieron el tiempo máximo de transporte
                        </div>
                      </div>
                    </q-card-section>
                  </q-card>
                </div>
                <div class="col-12">
                  <q-banner class="bg-positive text-white"
                    v-if="(dlgDetalle.estadisticas.entrada?.alertas_temperatura || 0) === 0 && (dlgDetalle.estadisticas.entrada?.alertas_tiempo || 0) === 0">
                    <template #avatar>
                      <q-icon name="check_circle" size="lg" />
                    </template>
                    <div class="text-h6">Cumplimiento Total</div>
                    <div>Este transporte ha cumplido con todos los requisitos de transporte de materia prima.</div>
                  </q-banner>
                  <q-banner class="bg-orange text-white" v-else>
                    <template #avatar>
                      <q-icon name="warning" size="lg" />
                    </template>
                    <div class="text-h6">Atención Requerida</div>
                    <div>Se han detectado alertas en el transporte. Revisar los registros para tomar acciones
                      correctivas.
                    </div>
                  </q-banner>
                </div>
              </div>
            </q-tab-panel>
          </q-tab-panels>
        </q-card-section>
        <q-separator />
        <q-card-actions align="right">
          <q-btn flat label="Cerrar" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
export default {
  name: 'TransportesPage',
  data() {
    return {
      loading: false,
      rows: [],
      columns: [
        { name: 'actions', label: 'Acciones', align: 'center', style: 'width: 150px' },
        { name: 'empresa', label: 'Empresa', align: 'left', field: 'empresa', sortable: true },
        { name: 'placa', label: 'Placa', align: 'left', field: 'placa', sortable: true },
        { name: 'responsable', label: 'Responsable', align: 'left', field: 'responsable', sortable: true },
        { name: 'estadisticas', label: 'Uso', align: 'center', style: 'width: 120px' },
        { name: 'alertas', label: 'Alertas', align: 'center', style: 'width: 150px' },
      ],
      filter: '',
      pagination: {
        rowsPerPage: 10
      },

      // Tabs
      tab: 'lista',

      // Estadísticas
      estadisticas: {
        entrada: {
          total_viajes: 0,
          kg_transportados: 0,
          distancia_total_km: 0,
          alertas_temperatura: 0,
          alertas_tiempo: 0,
          productores_atendidos: 0,
          porcentaje_cumplimiento: 100
        },
        salida: {
          total_entregas: 0,
          kg_entregados: 0,
          valor_total: 0,
          clientes_atendidos: 0
        },
        totales: {
          total_viajes: 0,
          total_kg: 0
        }
      },

      // CRUD
      dlg: { open: false, mode: 'create', row: null },
      form: {
        empresa: '',
        placa: '',
        responsable: '',
        telefono: '',
        observaciones: ''
      },
      saving: false,

      // Detalle
      dlgDetalle: { open: false, transporte: null, estadisticas: null, tab: 'entrada' },
    }
  },
  computed: {
    canSubmit() {
      const f = this.form
      return !!(f.empresa && f.placa && f.responsable)
    },
    topTransportes() {
      return [...this.rows]
        .sort((a, b) => {
          const aViajes = a.estadisticas_uso?.total_viajes || 0
          const bViajes = b.estadisticas_uso?.total_viajes || 0
          return bViajes - aViajes
        })
        .slice(0, 5)
    },
    transportesConAlertas() {
      return this.rows.filter(t => {
        const alertasTemp = t.estadisticas_uso?.alertas_temperatura || 0
        const alertasTiempo = t.estadisticas_uso?.alertas_tiempo || 0
        return alertasTemp > 0 || alertasTiempo > 0
      })
    }
  },
  mounted() {
    this.fetch()
    this.cargarEstadisticas()
  },
  methods: {
    async fetch() {
      this.loading = true
      try {
        const params = {}
        if (this.filter) params.q = this.filter
        const { data } = await this.$axios.get('/transportes', { params })
        this.rows = Array.isArray(data) ? data : (data.data || [])
      } catch (e) {
        this.$q.notify({ type: 'negative', message: e.response?.data?.message || 'Error al listar transportes' })
      } finally { this.loading = false }
    },

    async cargarEstadisticas() {
      try {
        // Cargar estadísticas agregadas de todos los transportes
        const promises = this.rows.map(t =>
          this.$axios.get(`/transportes/${t.id}/estadisticas-completas`)
            .then(({ data }) => data.estadisticas)
            .catch(() => null)
        )

        const results = await Promise.all(promises)
        const validas = results.filter(r => r !== null)

        if (validas.length > 0) {
          this.estadisticas = {
            entrada: {
              total_viajes: validas.reduce((sum, r) => sum + (r.entrada?.total_viajes || 0), 0),
              kg_transportados: validas.reduce((sum, r) => sum + (r.entrada?.kg_transportados || 0), 0),
              distancia_total_km: validas.reduce((sum, r) => sum + (r.entrada?.distancia_total_km || 0), 0),
              alertas_temperatura: validas.reduce((sum, r) => sum + (r.entrada?.alertas_temperatura || 0), 0),
              alertas_tiempo: validas.reduce((sum, r) => sum + (r.entrada?.alertas_tiempo || 0), 0),
              productores_atendidos: validas.reduce((sum, r) => sum + (r.entrada?.productores_atendidos || 0), 0),
              porcentaje_cumplimiento: validas.reduce((sum, r) => sum + (r.entrada?.porcentaje_cumplimiento || 100), 0) / validas.length
            },
            salida: {
              total_entregas: validas.reduce((sum, r) => sum + (r.salida?.total_entregas || 0), 0),
              kg_entregados: validas.reduce((sum, r) => sum + (r.salida?.kg_entregados || 0), 0),
              valor_total: validas.reduce((sum, r) => sum + (r.salida?.valor_total || 0), 0),
              clientes_atendidos: validas.reduce((sum, r) => sum + (r.salida?.clientes_atendidos || 0), 0)
            },
            totales: {
              total_viajes: validas.reduce((sum, r) => sum + (r.totales?.total_viajes || 0), 0),
              total_kg: validas.reduce((sum, r) => sum + (r.totales?.total_kg || 0), 0)
            }
          }
        }
      } catch (e) {
        console.error('Error al cargar estadísticas:', e)
      }
    },

    fetchDebounced: (() => {
      let t
      return function () {
        clearTimeout(t)
        t = setTimeout(() => this.fetch(), 350)
      }
    })(),

    openCreate() {
      this.dlg = { open: true, mode: 'create', row: null }
      this.form = { empresa: '', placa: '', responsable: '', telefono: '', observaciones: '' }
    },

    openEdit(row) {
      this.dlg = { open: true, mode: 'edit', row }
      this.form = {
        empresa: row.empresa || '',
        placa: row.placa || '',
        responsable: row.responsable || '',
        telefono: row.telefono || '',
        observaciones: row.observaciones || ''
      }
    },

    async verDetalle(transporte) {
      this.dlgDetalle = { open: true, transporte, estadisticas: null, tab: 'entrada' }
      try {
        const { data } = await this.$axios.get(`/transportes/${transporte.id}/estadisticas-completas`)
        this.dlgDetalle.estadisticas = data.estadisticas
      } catch (e) {
        console.error('Error al cargar estadísticas:', e)
        this.$q.notify({ type: 'warning', message: 'No se pudieron cargar las estadísticas completas' })
      }
    },

    async onSubmit() {
      if (!this.canSubmit) return
      this.saving = true
      try {
        if (this.dlg.mode === 'create') {
          await this.$axios.post('/transportes', this.form)
        } else {
          await this.$axios.put(`/transportes/${this.dlg.row.id}`, this.form)
        }
        this.$q.notify({ type: 'positive', message: 'Guardado exitosamente', icon: 'check_circle' })
        this.dlg.open = false
        await this.fetch()
        await this.cargarEstadisticas()
      } catch (e) {
        this.$q.notify({ type: 'negative', message: e.response?.data?.message || 'No se pudo guardar' })
      } finally { this.saving = false }
    },

    async onDelete(row) {
      this.$q.dialog({
        title: 'Confirmar Eliminación',
        message: `¿Está seguro de eliminar el transporte ${row.empresa || row.placa}?<br><br><strong>Nota:</strong> Esta acción no eliminará los registros de transporte asociados.`,
        html: true,
        cancel: { label: 'Cancelar', flat: true, color: 'grey-7' },
        ok: { label: 'Eliminar', color: 'negative' },
        persistent: true
      }).onOk(async () => {
        try {
          await this.$axios.delete(`/transportes/${row.id}`)
          this.$q.notify({ type: 'positive', message: 'Eliminado exitosamente', icon: 'check_circle' })
          await this.fetch()
          await this.cargarEstadisticas()
        } catch (e) {
          this.$q.notify({ type: 'negative', message: e.response?.data?.message || 'No se pudo eliminar' })
        }
      })
    },

    formatNumber(num) {
      return new Intl.NumberFormat('es-BO').format(num || 0)
    }
  }
}
</script>

<style scoped>
.q-card {
  border-radius: 8px;
}
</style>