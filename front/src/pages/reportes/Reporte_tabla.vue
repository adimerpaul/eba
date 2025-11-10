<template>
    <q-page class="q-pa-md">
        <div class="row">
            <div class="col-4"><q-input v-model="gestion" type="number" label="Gestion" dense /></div>
            <div class="col-4"><q-select v-model="producto" :options="productos" option-label="nombre_producto" label="Productos" dense /></div>
            <div class="col-4"><q-btn color="primary" label="ACOPIO GESTION" @click="getReporte" :loading="loading"/> <q-btn color="primary" label="GRAFICA" @click="getReporteTabla" :loading="loading"/></div>
        </div>
      <q-table
        :rows="listado1"
        row-key="id"
        flat bordered dense wrap-cells
        :rows-per-page-options="[0]"
        :title="`REPORTE ACOPIO GESTION PRODUCTO POR DEPARTAMENTO ${producto.nombre_producto} GESTION ${gestion}`"
        :loading="loading"
        v-if="listado1.length>0"
      >

      </q-table>
      <div v-if="listado2.length>0">
          <div class="text-center text-bold text-h6"> Grafica ACOPIO GESTION PRODUCTO POR DEPARTAMENTO {{producto.nombre_producto}} GESTION {{gestion}} </div>
         <canvas ref="grafico"></canvas>
      </div>

      <div class="row">
        <div class="col-md-12 col-xs-12 text-center text-h6 text-bold">PRODUCTORES POR GENERO <q-btn color="info" icon="update"  @click="reportEdad"  dense :loading="loading"/></div>
        <div class="col-md-6 col-xs-12"><q-table
            :rows="list_edad"
            row-key="name"
            dense
            v-if="list_edad.length>0"
        /></div>
        <div class="col-md-6 col-xs-12"><canvas ref="grafico2" height="180"></canvas></div>
      </div>
      <div class="text-center text-bold text-h6">ORGANIZACION ACOPIO <q-btn color="info" icon="update"  @click="reportOrg"  dense :loading="loading"/></div>
      <q-table
        :rows="list_org"
        row-key="name"
        dense
        :loading="loading"
        v-if="list_org.length>0"
      />
      <div class="row">
        <div class="col-12 q-pa-xs"><q-table
            title="LISTADO DE APICULTORES POR DEPARTAMENTO Y GENERO"
            :rows="reporte1"
            row-key="name"
        /></div>


        <div class="col-12 q-pa-xs">
            <q-table
                title="LISTADO DE COLMENAS POR DEPARTAMENTO PORCENTAJE"
                :rows="reporte4"
                row-key="name"
            />
        </div>


      </div>
    </q-page>
</template>
<script>
import Chart from 'chart.js/auto';
import moment from 'moment';
export default {
  name: 'ReporteTabla',
data    () {
    return {
        grafico: null,
        grafico2: null,
        grafico3: null,
        listado1: [],
        listado2: {labels: [], porcentaje: [], productores: []},
        list_edad: [],
        list_org: [],
        loading: false,
        gestion: moment().format('YYYY'),
        productos:[],
        chartInstance: null,
        chartInstance2: null,
        chartInstance3: null,
        producto:{nombre_producto:'', id: null},
        columns: [
            { name: 'id', label: 'ID', align: 'left', field: 'id' },
            { name: 'nombre', label: 'Producto', align: 'left', field: 'nombre' },
            { name: 'acciones', label: 'Acciones', align: 'center', field: 'acciones' },
        ],
        reporte1: [],
        reporte2: [],
        reporte3: [],
        reporte4: [],
        reporte5: [],
        reporte6: [],
    }
},
mounted() {
    this.getProductos();
    this.reportEdad();
    this.reportOrg();
    this.graficoEdad()
    this.getReporte()
    this.getReporteTabla()
    this.getReporte1();
    this.getReporte2();
    this.getReporte3();
    this.getReporte4();
    this.getReporte5();
    this.getReporte6();
},
  methods: {
    getReporte1() {
        this.loading = true;
        this.$axios.post('/reportApicultorDep').then(({ data }) => {
            this.reporte1 = data.data || data || []
        })  .finally(() => {
            this.loading = false;
        });
    },
    getReporte2() {
        this.loading = true;
        this.$axios.post('/reportApicultorDepGenero').then(({ data }) => {
            this.reporte2 = data.data || data || []
        })  .finally(() => {
            this.loading = false;
        });
    },
    getReporte3() {
        this.loading = true;
        this.$axios.post('/reportePorcentualApicultorDep').then(({ data }) => {
            this.reporte3 = data.data || data || []
        })  .finally(() => {
            this.loading = false;
        });
    },
    getReporte4() {
        this.loading = true;
        this.$axios.post('/reportePorcentualColmenasDep').then(({ data }) => {
            this.reporte4 = data.data || data || []
        })  .finally(() => {
            this.loading = false;
        });
    },
    getReporte5() {
        this.loading = true;
        this.$axios.post('/reportePorcentualApicultorDepAcopio').then(({ data }) => {
            this.reporte5 = data.data || data || []
        })  .finally(() => {
            this.loading = false;
        });
    },
    getReporte6() {
        this.loading = true;
        this.$axios.post('/reportePorcentualApicultorDepAcopio2').then(({ data }) => {
            this.reporte6 = data.data || data || []
        })  .finally(() => {
            this.loading = false;
        });
    },
    reportOrg() {
        if(!this.producto.id) {
            this.$alert?.error?.('Seleccione un producto');
            return;}
        if(!this.gestion){ this.$alert?.error?.('Seleccione una gestion'); return;}
        this.loading = true;
        this.$axios.post('/reportAcopioOrg',{gestion: this.gestion,producto_id: this.producto.id}).then(({ data }) => {
            this.list_org = data.data || data || []
        })  .finally(() => {
            this.loading = false;
        });
    },
    reportEdad() {
        this.loading = true;
        this.$axios.post('/reportEdad').then(({ data }) => {
            this.list_edad = data.data || data || []
            this.graficoEdad() 
        })  .finally(() => {
            this.loading = false;
        });
    },
        graficoEdad() {
        if (!this.$refs.grafico2) {
                console.error('Canvas aún no está montado')
                return
            }
            if (this.chartInstance2) this.chartInstance2.destroy()
                const etiquetas = this.list_edad.map(d => d.rango_edad)
                const totales = this.list_edad.map(d => d.total)
                const porcentajes = this.list_edad.map(d => d.porcentaje)
            this.chartInstance2 = new Chart(this.$refs.grafico2, {
                type: 'doughnut',
                data: {
                labels: etiquetas,
                datasets: [
                    {
                    label: 'Distribucion por rango de edad',
                    data: porcentajes,
                    backgroundColor: [// 16 colores diferentes
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 206, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(153, 102, 255)',
                        'rgb(255, 159, 64)',
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 206, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(153, 102, 255)',
                        'rgb(255, 159, 64)',
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 206, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(153, 102, 255)',
                    ],
                      borderWidth: 1
                    }
                ]
                },
                options: {
                responsive: true,
                plugins: {
                    legend: {
                    position: 'right'
                    },
                    tooltip: {
                    callbacks: {
                        label: ctx => `${ctx.label}: ${ctx.parsed}%`
                    }
                    },
                    title: {
                    display: true,
                    text: 'Distribución porcentual por rango de edad'
                    }
                
                }
            }
            })
    },
    async getProductos() {
       await this.$axios.get('/productos/tipo/1').then(({ data }) => {
        this.productos = data?.data || data || []
        if(this.productos.length>0) this.producto = this.productos[0];
      });
    },
    getReporte() {
        if(!this.producto.id) {
            this.$alert?.error?.('Seleccione un producto');
            return;}
        if(!this.gestion){ this.$alert?.error?.('Seleccione una gestion'); return;}
        this.loading = true;
        this.$axios.post('/reporteAcopioProveedorDep', {
            producto_id: this.producto.id,
            inicio: this.gestion + '-01-01',
            fin: this.gestion + '-12-31',
            }).then(({ data }) => {
                console.log(data);
            this.listado1 = data.data || data || []
        }).finally(() => {
            this.loading = false;
        });
    },
    getReporteTabla() {

        if(!this.producto.id) {
            this.$alert?.error?.('Seleccione un producto');
            return;}
        if(!this.gestion){ this.$alert?.error?.('Seleccione una gestion'); return;}
        this.loading = true;
        this.$axios.post('/reportePorcentual', {
            producto_id: this.producto.id,
            inicio: this.gestion + '-01-01',
            fin: this.gestion + '-12-31',
            }).then(({ data }) => {
            console.log(data);
             this.listado2 = data.data || data || []
             this.crearGrafico()
        }).finally(() => {
            this.loading = false;
        });
    },
     crearGrafico() {
        if (!this.$refs.grafico) {
                console.error('Canvas aún no está montado')
                return
            }
            if (this.chartInstance) this.chartInstance.destroy()

            this.chartInstance = new Chart(this.$refs.grafico, {
                type: 'bar',
                data: {
                labels: this.listado2.labels,
                datasets: [
                    {
                    label: 'Porcentaje de acopio (%)',
                    data: this.listado2.porcentaje,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    yAxisID: 'y'
                    },
                    {
                    label: 'Total de productores',
                    data: this.listado2.productores,
                    backgroundColor: 'rgba(255, 159, 64, 0.6)',
                    yAxisID: 'y1'
                    }
                ]
                },
                options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                stacked: false,
                scales: {
                    y: {
                    type: 'linear',
                    position: 'left',
                    title: { display: true, text: 'Porcentaje (%)' }
                    },
                    y1: {
                    type: 'linear',
                    position: 'right',
                    title: { display: true, text: 'Productores (total)' },
                    grid: { drawOnChartArea: false }
                    }
                }
                }
            })
        }}
}
</script>