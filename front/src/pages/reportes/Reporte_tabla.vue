<template>
    <q-page class="q-pa-md">
        <div class="row">
            <div class="col-4"><q-input v-model="gestion" type="number" label="Gestion" dense /></div>
            <div class="col-4"><q-select v-model="producto" :options="productos" option-label="nombre_producto" label="Productos" dense /></div>
            <div class="col-4"><q-btn color="primary" label="Generar" @click="getReporte" :loading="loading"/> <q-btn color="primary" label="Generar2" @click="getReporteTabla" :loading="loading"/></div>
        </div>
      <q-table
        :rows="listado1"
        row-key="id"
        flat bordered dense wrap-cells
        :rows-per-page-options="[0]"
        title="Reporte"
        :loading="loading"
      >

      </q-table>
      <div>
         <canvas ref="grafico"></canvas>
      </div>
    </q-page>
</template>
<script>
import Chart from 'chart.js/auto';
import moment from 'moment';
import { list } from 'postcss';
export default {
  name: 'ReporteTabla',
data    () {
    return {
        grafico: null,
        listado1: [],
        listado2: {labels: [], porcentaje: [], productores: []},
        loading: false,
      gestion: moment().format('YYYY'),
      productos:[],
      chartInstance: null,
      producto:{nombre_producto:'', id: null},
        columns: [
            { name: 'id', label: 'ID', align: 'left', field: 'id' },
            { name: 'nombre', label: 'Producto', align: 'left', field: 'nombre' },
            { name: 'acciones', label: 'Acciones', align: 'center', field: 'acciones' },
        ],
    }
},
mounted() {
    this.getProductos();
},
  methods: {
    getProductos() {
      this.$axios.get('/productos/tipo/1').then(({ data }) => {
        this.productos = data?.data || data || []
      });
    },
    getReporte() {
        if(!this.producto.id) return;
        if(!this.gestion) return;
        this.loading = true;
        this.$axios.post('/reporteAcopioProveedorDep', {
            producto_id: this.producto.id,
            inicio: this.gestion + '-01-01',
            fin: this.gestion + '-12-31',
            }).then(({ data }) => {
                console.log(data);
            this.listado2 = data.data || data || []
        }).finally(() => {
            this.loading = false;
        });
    },
    getReporteTabla() {

        if(!this.producto.id) return;
        if(!this.gestion) return;
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