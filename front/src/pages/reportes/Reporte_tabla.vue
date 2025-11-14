<template>
    <q-page class="q-pa-xs">
      <q-card flat bordered>
        <q-card-section class="q-pa-none">
          <div class="row">
            <div class="col-4"><q-input v-model="gestion" type="number" label="Gestion" dense outlined /></div>
            <div class="col-4"><q-select v-model="producto" :options="productos" option-label="nombre_producto" label="Productos" dense  outlined/></div>
            <div class="col-4">
<!--              <q-btn color="primary" label="Grafica" @click="getReporteTabla" :loading="loading" no-caps icon="bar_chart"/>-->
            </div>
          </div>
          <q-tabs
            v-model="tab"
            class="text-teal"
          >
            <q-tab name="departamentos" icon="south_america" label="departamentos" />
            <q-tab name="municipios" icon="location_city" label="municipios" />
            <q-tab name="grafica1" icon="bar_chart" label="grafica_edad" />
            <q-tab name="grafica2" icon="bar_chart" label="grafica_genero" />
          </q-tabs>
            <q-separator />
        <q-tab-panels v-model="tab" animated>
          <q-tab-panel name="departamentos">
            <q-btn color="primary" label="Acopios" @click="getRepDept" :loading="loading" no-caps icon="table_chart"/>
              <q-markup-table dense flat bordered>
                <thead>
                  <tr style="font-weight: bold;">
                    <th class="text-center">DEPARTAMENTO</th>
                    <th class="text-center">PRODUCTORES</th>
                    <th class="text-center">ENERO</th>
                    <th class="text-center">FEBRERO</th>
                    <th class="text-center">MARZO</th>
                    <th class="text-center">ABRIL</th>
                    <th class="text-center">MAYO</th>   
                    <th class="text-center">JUNIO</th>
                    <th class="text-center">JULIO</th>
                    <th class="text-center">AGOSTO</th>
                    <th class="text-center">SEPTIEMBRE</th>
                    <th class="text-center">OCTUBRE</th>
                    <th class="text-center">NOVIEMBRE</th>
                    <th class="text-center">DICIEMBRE</th>
                    <th class="text-center">TOTAL</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(row, index) in listado1" :key="index">
                    <td class="text-left">{{ row.departamento }}</td>
                    <td class="text-center">{{ row.productores }}</td>
                    <td class="text-right">{{ row.enero }}</td>
                    <td class="text-right">{{ row.febrero }}</td>
                    <td class="text-right">{{ row.marzo }}</td>
                    <td class="text-right">{{ row.abril }}</td>
                    <td class="text-right">{{ row.mayo }}</td>
                    <td class="text-right">{{ row.junio }}</td>  
                    <td class="text-right">{{ row.julio }}</td>
                    <td class="text-right">{{ row.agosto }}</td>
                    <td class="text-right">{{ row.septiembre }}</td>
                    <td class="text-right">{{ row.octubre }}</td>
                    <td class="text-right">{{ row.noviembre }}</td>
                    <td class="text-right">{{ row.diciembre }}</td>
                    <td class="text-right">{{ row.total }}</td>
                  </tr>
                  <tr>
                    <td class="text-right"><strong>TOTALES</strong></td>
                    <td class="text-right"><strong>{{ sumarCampos(listado1, 'productores') }}</strong></td>
                    <td class="text-right"><strong>{{ sumarCampos(listado1, 'enero') }}</strong></td>
                    <td class="text-right"><strong>{{ sumarCampos(listado1, 'febrero') }}</strong></td>
                    <td class="text-right"><strong>{{ sumarCampos(listado1, 'marzo') }}</strong></td>
                    <td class="text-right"><strong>{{ sumarCampos(listado1, 'abril') }}</strong></td>
                    <td class="text-right"><strong>{{ sumarCampos(listado1, 'mayo') }}</strong></td>
                    <td class="text-right"><strong>{{ sumarCampos(listado1, 'junio') }}</strong></td>
                    <td class="text-right"><strong>{{ sumarCampos(listado1, 'julio') }}</strong></td>
                    <td class="text-right"><strong>{{ sumarCampos(listado1, 'agosto') }}</strong></td>
                    <td class="text-right"><strong>{{ sumarCampos(listado1, 'septiembre') }}</strong></td>
                    <td class="text-right"><strong>{{ sumarCampos(listado1, 'octubre') }}</strong></td>
                    <td class="text-right"><strong>{{ sumarCampos(listado1, 'noviembre') }}</strong></td>
                    <td class="text-right"><strong>{{ sumarCampos(listado1, 'diciembre') }}</strong></td>
                    <td class="text-right"><strong>{{ sumarCampos(listado1, 'total') }}</strong></td>

                  </tr>
                </tbody>
                </q-markup-table>
            <br>
            <div >
          <div class="text-center text-bold text-h6"> Grafica ACOPIO GESTION PRODUCTO POR DEPARTAMENTO {{producto.nombre_producto}} GESTION {{gestion}} </div>
            <canvas ref="grafico"></canvas>
          </div>
                    <q-markup-table dense flat bordered>
            <thead>
              <tr>
                <th class="text-center">MES</th>
                <th class="text-center">PORCENTAJE ACOPIO (%)</th>
                <th class="text-center">TOTAL PRODUCTORES</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, index) in listado2.labels" :key="index">
                <td class="text-left">{{ row }}</td>
                <td class="text-right">{{ listado2.porcentaje[index] ?? 0 }}</td>
                <td class="text-right">{{ listado2.productores[index] ?? 0 }}</td>
              </tr>
            </tbody>
          </q-markup-table>
          </q-tab-panel>
          <q-tab-panel name="municipios">
            <q-btn color="primary" label="Acopios" @click="getReporte" :loading="loading" no-caps icon="table_chart"/>
            <q-markup-table dense flat bordered>
              <thead>
                <tr>
                  <th class="text-center">MUNICIPIO</th>
                  <th class="text-center">PRODUCTORES</th>
                  <th class="text-center">ENERO</th>
                  <th class="text-center">FEBRERO</th>
                  <th class="text-center">MARZO</th>
                  <th class="text-center">ABRIL</th>
                  <th class="text-center">MAYO</th>
                  <th class="text-center">JUNIO</th>
                  <th class="text-center">JULIO</th>
                  <th class="text-center">AGOSTO</th>
                  <th class="text-center">SEPTIEMBRE</th>
                  <th class="text-center">OCTUBRE</th>
                  <th class="text-center">NOVIEMBRE</th>
                    <th class="text-center">DICIEMBRE</th>
                  <th class="text-center">TOTAL</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="row in listMun" :key="row.id">
                  <td class="text-left">{{ row.municipio }}</td>
                  <td class="text-right">{{ row.productores }}</td>
                  <td class="text-right">{{ row.enero }}</td>
                  <td class="text-right">{{ row.febrero }}</td>
                  <td class="text-right">{{ row.marzo }}</td>
                  <td class="text-right">{{ row.abril }}</td>
                  <td class="text-right">{{ row.mayo }}</td>
                  <td class="text-right">{{ row.junio }}</td>
                  <td class="text-right">{{ row.julio }}</td>
                  <td class="text-right">{{ row.agosto }}</td>
                  <td class="text-right">{{ row.septiembre }}</td>
                  <td class="text-right">{{ row.octubre }}</td>
                  <td class="text-right">{{ row.noviembre }}</td>
                  <td class="text-right">{{ row.diciembre }}</td>
                  <td class="text-right">{{ row.total }}</td>
                </tr>
                <tr>
                  <td class="text-right"><strong>TOTALES</strong></td>
                  <td class="text-right"><strong>{{ sumarCampos(listMun, 'productores') }}</strong></td>
                  <td class="text-right"><strong>{{ sumarCampos(listMun, 'enero') }}</strong></td>
                  <td class="text-right"><strong>{{ sumarCampos(listMun, 'febrero') }}</strong></td>
                  <td class="text-right"><strong>{{ sumarCampos(listMun, 'marzo') }}</strong></td>
                  <td class="text-right"><strong>{{ sumarCampos(listMun, 'abril') }}</strong></td>
                  <td class="text-right"><strong>{{ sumarCampos(listMun, 'mayo') }}</strong></td>
                  <td class="text-right"><strong>{{ sumarCampos(listMun, 'junio') }}</strong></td>
                  <td class="text-right"><strong>{{ sumarCampos(listMun, 'julio') }}</strong></td>
                  <td class="text-right"><strong>{{ sumarCampos(listMun, 'agosto') }}</strong></td>
                  <td class="text-right"><strong>{{ sumarCampos(listMun, 'septiembre') }}</strong></td>
                    <td class="text-right"><strong>{{ sumarCampos(listMun, 'octubre') }}</strong></td>
                    <td class="text-right"><strong>{{ sumarCampos(listMun, 'noviembre') }}</strong></td>
                    <td class="text-right"><strong>{{ sumarCampos(listMun, 'diciembre') }}</strong></td>
                    <td class="text-right"><strong>{{ sumarCampos(listMun, 'total') }}</strong></td>
                </tr>

              </tbody>
            </q-markup-table>
            </q-tab-panel>
            <q-tab-panel name="grafica1">
                <div class="text-center text-bold text-h6">GRAFICA PRODUCTORES POR EDAD <q-btn color="info" icon="update"  @click="reportEdad"  dense :loading="loading" /></div>
                <div class="col-md-6 col-xs-12">
                    <q-table
              :rows="list_edad"
              row-key="name"
              dense
              :rows-per-page-options="[0]"
              v-if="list_edad.length>0"
            /></div>
            <div class="col-md-6 col-xs-12"><canvas ref="grafico2" height="180"></canvas></div>
            </q-tab-panel>
            <q-tab-panel name="grafica2">
                <div class="text-center text-bold text-h6">GRAFICA PRODUCTORES POR GENERO <q-btn color="info" icon="update"  @click="getReporte2"  dense :loading="loading" /></div>
                <div class="col-md-6 col-xs-12">
                    <q-table
              :rows="reporte2"
              row-key="name"
              dense
              :rows-per-page-options="[0]"
              v-if="reporte2.length>0"
            /></div>

            <div class="col-md-6 col-xs-12"><canvas ref="grafico3" height="180"></canvas></div>
            </q-tab-panel>
          </q-tab-panels>   
<!--          <q-table-->
<!--            :rows="listado1"-->
<!--            :columns="columns2"-->
<!--            row-key="id"-->
<!--            flat bordered dense wrap-cells-->
<!--            :rows-per-page-options="[0]"-->
<!--            :title="`REPORTE ACOPIO GESTION PRODUCTO POR DEPARTAMENTO ${producto.nombre_producto} GESTION ${gestion}`"-->
<!--            :loading="loading"-->
<!--            :filter="filter"-->
<!--            v-if="listado1.length>0"-->
<!--          >-->
<!--            <template v-slot:top-right>-->
<!--              <q-btn color="info" icon="print"  @click="impresion"  dense :loading="loading" />-->
<!--              <q-input dense debounce="300" v-model="filter" placeholder="Buscar..." outlined-->
<!--                       clearable-->
<!--                       clear-icon="close"-->
<!--                       append-icon="search"-->
<!--              />-->
<!--            </template>-->
<!--            &lt;!&ndash;Sumatorias de las columnas &ndash;&gt;-->
<!--            <template v-slot:bottom-row>-->
<!--              <q-tr>-->
<!--                <q-td class="text-right"><strong>TOTALES</strong></q-td>-->
<!--                <q-td class="text-right "><strong>{{ listado1.reduce((sum, row) => sum + row.productores, 0) }}</strong></q-td>-->
<!--                <q-td class="text-right "><strong>{{ listado1.reduce((sum, row) => sum + (parseFloat(row.enero) || 0), 0) }}</strong></q-td>-->
<!--                <q-td class="text-right "><strong>{{ listado1.reduce((sum, row) => sum + (parseFloat(row.febrero) || 0), 0) }}</strong></q-td>-->
<!--                <q-td class="text-right "><strong>{{ listado1.reduce((sum, row) => sum + (parseFloat(row.marzo) || 0), 0) }}</strong></q-td>-->
<!--                <q-td class="text-right "><strong>{{ listado1.reduce((sum, row) => sum + (parseFloat( row.abril) || 0), 0) }}</strong></q-td>-->
<!--                <q-td class="text-right "><strong>{{ listado1.reduce((sum, row) => sum + (parseFloat(row.mayo) || 0), 0) }}</strong></q-td>-->
<!--                <q-td class="text-right "><strong>{{ listado1.reduce((sum, row) => sum + (parseFloat(row.junio) || 0), 0) }}</strong></q-td>-->
<!--                <q-td class="text-right "><strong>{{ listado1.reduce((sum, row) => sum + (parseFloat(row.julio) || 0), 0) }}</strong></q-td>-->
<!--                <q-td class="text-right "><strong>{{ listado1.reduce((sum, row) => sum + (parseFloat(row.agosto) || 0), 0) }}</strong></q-td>-->
<!--                <q-td class="text-right "><strong>{{ listado1.reduce((sum, row) => sum + (parseFloat(row.septiembre) || 0), 0) }}</strong></q-td>-->
<!--                <q-td class="text-right "><strong>{{ listado1.reduce((sum, row) => sum + (parseFloat(row.octubre) || 0), 0) }}</strong></q-td>-->
<!--                <q-td class="text-right "><strong>{{ listado1.reduce((sum, row) => sum + (parseFloat(row.noviembre) || 0), 0) }}</strong></q-td>-->
<!--                <q-td class="text-right "><strong>{{ listado1.reduce((sum, row) => sum + (parseFloat(row.diciembre) || 0), 0) }}</strong></q-td>-->
<!--                <q-td class="text-right "><strong>{{ listado1.reduce((sum, row) => sum + (parseFloat(row.total) || 0), 0) }}</strong></q-td>-->
<!--              </q-tr>-->
<!--            </template>-->

<!--          </q-table>-->

<!--          <div >-->
<!--            <div class="text-center text-bold text-h6"> Grafica ACOPIO GESTION PRODUCTO POR DEPARTAMENTO {{producto.nombre_producto}} GESTION {{gestion}} </div>-->
<!--            <canvas ref="grafico"></canvas>-->
<!--          </div>-->
<!--          <div class="row">-->
<!--            <div class="col-md-12 col-xs-12 text-center text-h6 text-bold">PRODUCTORES POR EDAD <q-btn color="info" icon="update"  @click="reportEdad"  dense :loading="loading"/></div>-->
<!--            <div class="col-md-6 col-xs-12"><q-table-->
<!--              :rows="list_edad"-->
<!--              row-key="name"-->
<!--              dense-->
<!--              v-if="list_edad.length>0"-->
<!--            /></div>-->
<!--            <div class="col-md-6 col-xs-12"><canvas ref="grafico2" height="180"></canvas></div>-->
<!--          </div>-->
<!--          <div class="text-center text-bold text-h6">ORGANIZACION ACOPIO <q-btn color="info" icon="update"  @click="reportOrg"  dense :loading="loading"/></div>-->
<!--          <q-table-->
<!--            :rows="list_org"-->
<!--            row-key="name"-->
<!--            dense-->
<!--            :loading="loading"-->
<!--            v-if="list_org.length>0"-->
<!--          />-->
<!--          <div class="row">-->
<!--            <div class="col-12 q-pa-xs"><q-table-->
<!--              title="CANTIDAD      DE APICULTORES POR DEPARTAMENTO Y GENERO"-->
<!--              :rows="reporte1"-->
<!--              row-key="name"-->
<!--            /></div>-->


<!--            <div class="col-12 q-pa-xs">-->
<!--              <q-table-->
<!--                title="CANITDAD DE COLMENAS POR DEPARTAMENTO PORCENTAJE"-->
<!--                :rows="reporte4"-->
<!--                row-key="name"-->
<!--              />-->
<!--            </div>-->
<!--          </div>-->
        </q-card-section>
      </q-card>
    </q-page>
</template>
<script>
import Chart from 'chart.js/auto';
import moment from 'moment';
export default {
  name: 'ReporteTabla',
data    () {
    return {
        tab: 'departamentos',
        grafico: null,
        grafico2: null,
        grafico3: null,
        listado1: [],
        listado2: {labels: [], porcentaje: [], productores: []},
        list_edad: [],
        list_org: [],
        listMun: [],
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
        columns2: [
            { name: 'municipio', label: 'MUNICIPIO', align: 'left', field: 'municipio' },
            { name: 'productores', label: 'PRODUCTORES', align: 'right', field: 'productores' },
            { name: 'enero', label: 'ENERO', align: 'right', field: 'enero' },
            { name: 'febrero', label: 'FEBRERO', align: 'right', field: 'febrero' },
            { name: 'marzo', label: 'MARZO', align: 'right', field: 'marzo' },
            { name: 'abril', label: 'ABRIL', align: 'right', field: 'abril' },
            { name: 'mayo', label: 'MAYO', align: 'right', field: 'mayo' },
            { name: 'junio', label: 'JUNIO', align: 'right', field: 'junio' },
            { name: 'julio', label: 'JULIO', align: 'right', field: 'julio' },
            { name: 'agosto', label: 'AGOSTO', align: 'right', field: 'agosto' },
            { name: 'septiembre', label: 'SEPTIEMBRE', align: 'right', field: 'septiembre' },
            { name: 'octubre', label: 'OCTUBRE', align: 'right', field: 'octubre' },
            { name: 'noviembre', label: 'NOVIEMBRE', align: 'right', field: 'noviembre' },
            { name: 'diciembre', label: 'DICIEMBRE', align: 'right', field: 'diciembre' },
            { name: 'total', label: 'TOTAL', align: 'right', field: 'total' },
        ],
        reporte1: [],
        reporte2: [],
        reporte3: [],
        reporte4: [],
        reporte5: [],
        reporte6: [],
        filter: '',
    }
},
mounted() {
    this.getProductos();
    // this.reportEdad();
    // this.reportOrg();
    // this.graficoEdad()
    // this.getReporte1();
    // this.getReporte2();
    // this.getReporte3();
    // this.getReporte4();
    // this.getReporte5();
    // this.getReporte6();
},
  methods: {
    sumarCampos(listado,campo){
        let total = 0;
        listado.forEach(element => {
            total += parseFloat(element[campo]??0);
        });
        return total.toFixed(2);
    },
    impresion() {
        let fecha = moment().format('DD/MM/YYYY');
        let lugar = '__________';
        let cadena=`
        <style>
  .table1 { width:100%; border-collapse: collapse; font-size:8px; }
  .table1 td, .table1 th { border:1px solid #444; padding:2px; }

  .header { width:100%; border-collapse: collapse; margin-bottom:8px; }
  .header td { padding:4px; font-size:11px; }
  .titulo { text-align:center; font-size:16px; font-weight:bold; margin:8px 0; }
  .subtitulo { text-align:center; font-size:13px; margin-bottom:8px; }
  .table { width:100%; border-collapse: collapse; font-size:12px; }
  .table2 { width:100%; border-collapse: collapse; font-size:8px; }
  .table td, .table th { border:1px solid #444; padding:5px; }
  .label { background:#f0f0f0; font-weight:bold; }
  .right { text-align:right; }
  .center { text-align:center; }
  .firma { text-align:center; font-size:11px; padding-top:25px; }
  .firma .linea { border-top:1px dotted #444; width:80%; margin:0 auto 4px; }
  .nota { font-size:10px; margin-top:5px; }
  .dark { background:#5f6c78; color:#fff; font-weight:bold; text-align:center; }
  .no-border td { border:none !important; }
  </style>

  <table class="header">
    <tr>
      <td rowspan="2" style="width:150px;"><img src="logoOld.png" width="150"></td>
      <td colspan="3" style="text-align:center; font-weight:bold; font-size:22px;">EMPRESA BOLIVIANA DE ALIMENTOS Y DERIVADOS - EBA</td>
      <td style="border:1px solid #000; font-size:10px;" rowspan="2">
        <b>Fecha de emisión:</b>${fecha}<br><br>
      </td>
    </tr>
  </table>
        <table class="table1">
        <tr>
            <th>MUNICIPIO</th>
            <th>PRODUCTOR</th>
            <th>ENERO</th>
            <th>FEBRERO</th>
            <th>MARZO</th>
            <th>ABRIL</th>
            <th>MAYO</th>
            <th>JUNIO</th>
            <th>JULIO</th>
            <th>AGOSTO</th>
            <th>SEPTIEMBRE</th>
            <th>OCTUBRE</th>
            <th>NOVIEMBRE</th>
            <th>DICIEMBRE</th>
            <th>TOTAL</th>
        </tr>`;
        this.listado1.forEach(row => {
            cadena+=`<tr>
            <td>${row.municipio}</td>
            <td style="text-align: right;">${row.productores}</td>
            <td style="text-align: right;">${row.enero??''}</td>
            <td style="text-align: right;">${row.febrero??''}</td>
            <td style="text-align: right;">${row.marzo??''}</td>
            <td style="text-align: right;">${row.abril??''}</td>
            <td style="text-align: right;">${row.mayo??''}</td>
            <td style="text-align: right;">${row.junio??''}</td>
            <td style="text-align: right;">${row.julio??''}</td>
            <td style="text-align: right;">${row.agosto??''}</td>
            <td style="text-align: right;">${row.septiembre??''}</td>
            <td style="text-align: right;">${row.octubre??''}</td>
            <td style="text-align: right;">${row.noviembre??''}</td>
            <td style="text-align: right;">${row.diciembre??''}</td>
            <td style="text-align: right;">${row.total}</td>
        </tr>`;
        });
        cadena+=`<tr>
            <td><strong>TOTALES</strong></td>
            <td style="text-align: right;"><strong>${ this.listado1.reduce((sum, row) => sum + row.productores, 0) }</strong></td>
            <td style="text-align: right;"><strong>${ this.listado1.reduce((sum, row) => sum + (parseFloat(row.enero) || 0), 0) }</strong></td>
            <td style="text-align: right;"><strong>${ this.listado1.reduce((sum, row) => sum + (parseFloat(row.febrero) || 0), 0) }</strong></td>
            <td style="text-align: right;"><strong>${ this.listado1.reduce((sum, row) => sum + (parseFloat(row.marzo) || 0), 0) }</strong></td>
            <td style="text-align: right;"><strong>${ this.listado1.reduce((sum, row) => sum + (parseFloat( row.abril) || 0), 0) }</strong></td>
            <td style="text-align: right;"><strong>${ this.listado1.reduce((sum, row) => sum + (parseFloat(row.mayo) || 0), 0) }</strong></td>
            <td style="text-align: right;"><strong>${ this.listado1.reduce((sum, row) => sum + (parseFloat(row.junio) || 0), 0) }</strong></td>
            <td style="text-align: right;"><strong>${ this.listado1.reduce((sum, row) => sum + (parseFloat(row.julio) || 0), 0) }</strong></td>
            <td style="text-align: right;"><strong>${ this.listado1.reduce((sum, row) => sum + (parseFloat(row.agosto) || 0), 0) }</strong></td>
            <td style="text-align: right;"><strong>${ this.listado1.reduce((sum, row) => sum + (parseFloat(row.septiembre) || 0), 0) }</strong></td>
            <td style="text-align: right;"><strong>${ this.listado1.reduce((sum, row) => sum + (parseFloat(row.octubre) || 0), 0) }</strong></td>
            <td style="text-align: right;"><strong>${ this.listado1.reduce((sum, row) => sum + (parseFloat(row.noviembre) || 0), 0) }</strong></td>
            <td style="text-align: right;"><strong>${ this.listado1.reduce((sum, row) => sum + (parseFloat(row.diciembre) || 0), 0) }</strong></td>
            <td style="text-align: right;"><strong>${ this.listado1.reduce((sum, row) => sum + (parseFloat(row.total) || 0), 0) }</strong></td>
        </tr>`;
        cadena+=`</table>
        `
        // impresion
        let ventana = window.open('', '_blank');
        ventana.document.write(cadena);
        ventana.document.close();
        ventana.print();

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
        },
            getReporte() {
        if(!this.producto.id) {
            this.$alert?.error?.('Seleccione un producto');
            return;}
        if(!this.gestion){ this.$alert?.error?.('Seleccione una gestion'); return;}
        this.loading = true;
        this.$axios.post('/reporteAcopioProveedorMun', {
            producto_id: this.producto.id,
            inicio: this.gestion + '-01-01',
            fin: this.gestion + '-12-31',
            }).then(({ data }) => {
                console.log(data);
            this.listMun = data.data || data || []
        }).finally(() => {
            this.loading = false;
        });
    },
    getRepDept() {
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
            this.getReporteTabla()
        }).finally(() => {
            this.loading = false;
        });
    },
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
            // si ay datos varon mujer agrupar y sumar para obtenr labels y total
            let varon=0
            let mujer=0
            this.reporte2.forEach(element => {
                varon+=element.varon;
                mujer+=element.mujer;
            })
            let labels = ['MASCULINO', 'FEMENINO'];
            let totales = [varon, mujer];
            console.log(labels, totales);
            if (!this.$refs.grafico3) {
                console.error('Canvas aún no está montado')
                return
            }
            if (this.chartInstance3) this.chartInstance3.destroy()

            this.chartInstance3 = new Chart(this.$refs.grafico3, {
                type: 'bar',
                data: {
                labels: labels,
                datasets: [
                    {
                    label: 'Cantidad de apicultores',
                    data: totales,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    yAxisID: 'y'
                    }
                ]
                },
                options: {
                responsive: true,
                scales: {
                    y: {
                    type: 'linear',
                    position: 'left',
                    title: { display: true, text: 'Cantidad de apicultores' }
                    }
                }
                }
            })
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
            })    },
    async getProductos() {
       await this.$axios.get('/productos/tipo/1').then(({ data }) => {
        this.productos = data?.data || data || []
        if(this.productos.length>0) this.producto = this.productos[0];
      });
    },

}
}
</script>
