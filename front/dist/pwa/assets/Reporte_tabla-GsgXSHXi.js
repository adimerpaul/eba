import{_ as f,c as m,o as g,w as n,a as l,Q as x,e as y,b as a,ag as $,f as O,aa as c,ab as E,ac as I,t as h}from"./index-7y1xTPo0.js";import{Q as R}from"./QSelect-DyaU-PEd.js";import{Q as _}from"./QMarkupTable-DKld9FxK.js";import{Q as A}from"./QPage-BsPoimEa.js";import{C as p}from"./auto-DHENdVy-.js";import{h as u}from"./moment-C5S46NFB.js";import"./format-DML4119o.js";import"./selection-DMQW0dhV.js";const C={name:"ReporteTabla",data(){return{grafico:null,grafico2:null,grafico3:null,listado1:[],listado2:{labels:[],porcentaje:[],productores:[]},list_edad:[],list_org:[],loading:!1,gestion:u().format("YYYY"),productos:[],chartInstance:null,chartInstance2:null,chartInstance3:null,producto:{nombre_producto:"",id:null},columns:[{name:"id",label:"ID",align:"left",field:"id"},{name:"nombre",label:"Producto",align:"left",field:"nombre"},{name:"acciones",label:"Acciones",align:"center",field:"acciones"}],columns2:[{name:"municipio",label:"MUNICIPIO",align:"left",field:"municipio"},{name:"productores",label:"PRODUCTORES",align:"right",field:"productores"},{name:"enero",label:"ENERO",align:"right",field:"enero"},{name:"febrero",label:"FEBRERO",align:"right",field:"febrero"},{name:"marzo",label:"MARZO",align:"right",field:"marzo"},{name:"abril",label:"ABRIL",align:"right",field:"abril"},{name:"mayo",label:"MAYO",align:"right",field:"mayo"},{name:"junio",label:"JUNIO",align:"right",field:"junio"},{name:"julio",label:"JULIO",align:"right",field:"julio"},{name:"agosto",label:"AGOSTO",align:"right",field:"agosto"},{name:"septiembre",label:"SEPTIEMBRE",align:"right",field:"septiembre"},{name:"octubre",label:"OCTUBRE",align:"right",field:"octubre"},{name:"noviembre",label:"NOVIEMBRE",align:"right",field:"noviembre"},{name:"diciembre",label:"DICIEMBRE",align:"right",field:"diciembre"},{name:"total",label:"TOTAL",align:"right",field:"total"}],reporte1:[],reporte2:[],reporte3:[],reporte4:[],reporte5:[],reporte6:[],filter:""}},mounted(){this.getProductos()},methods:{impresion(){let i=`
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
        <b>Fecha de emisión:</b>${u().format("DD/MM/YYYY")}<br><br>
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
        </tr>`;this.listado1.forEach(t=>{i+=`<tr>
            <td>${t.municipio}</td>
            <td style="text-align: right;">${t.productores}</td>
            <td style="text-align: right;">${t.enero??""}</td>
            <td style="text-align: right;">${t.febrero??""}</td>
            <td style="text-align: right;">${t.marzo??""}</td>
            <td style="text-align: right;">${t.abril??""}</td>
            <td style="text-align: right;">${t.mayo??""}</td>
            <td style="text-align: right;">${t.junio??""}</td>
            <td style="text-align: right;">${t.julio??""}</td>
            <td style="text-align: right;">${t.agosto??""}</td>
            <td style="text-align: right;">${t.septiembre??""}</td>
            <td style="text-align: right;">${t.octubre??""}</td>
            <td style="text-align: right;">${t.noviembre??""}</td>
            <td style="text-align: right;">${t.diciembre??""}</td>
            <td style="text-align: right;">${t.total}</td>
        </tr>`}),i+=`<tr>
            <td><strong>TOTALES</strong></td>
            <td style="text-align: right;"><strong>${this.listado1.reduce((t,o)=>t+o.productores,0)}</strong></td>
            <td style="text-align: right;"><strong>${this.listado1.reduce((t,o)=>t+(parseFloat(o.enero)||0),0)}</strong></td>
            <td style="text-align: right;"><strong>${this.listado1.reduce((t,o)=>t+(parseFloat(o.febrero)||0),0)}</strong></td>
            <td style="text-align: right;"><strong>${this.listado1.reduce((t,o)=>t+(parseFloat(o.marzo)||0),0)}</strong></td>
            <td style="text-align: right;"><strong>${this.listado1.reduce((t,o)=>t+(parseFloat(o.abril)||0),0)}</strong></td>
            <td style="text-align: right;"><strong>${this.listado1.reduce((t,o)=>t+(parseFloat(o.mayo)||0),0)}</strong></td>
            <td style="text-align: right;"><strong>${this.listado1.reduce((t,o)=>t+(parseFloat(o.junio)||0),0)}</strong></td>
            <td style="text-align: right;"><strong>${this.listado1.reduce((t,o)=>t+(parseFloat(o.julio)||0),0)}</strong></td>
            <td style="text-align: right;"><strong>${this.listado1.reduce((t,o)=>t+(parseFloat(o.agosto)||0),0)}</strong></td>
            <td style="text-align: right;"><strong>${this.listado1.reduce((t,o)=>t+(parseFloat(o.septiembre)||0),0)}</strong></td>
            <td style="text-align: right;"><strong>${this.listado1.reduce((t,o)=>t+(parseFloat(o.octubre)||0),0)}</strong></td>
            <td style="text-align: right;"><strong>${this.listado1.reduce((t,o)=>t+(parseFloat(o.noviembre)||0),0)}</strong></td>
            <td style="text-align: right;"><strong>${this.listado1.reduce((t,o)=>t+(parseFloat(o.diciembre)||0),0)}</strong></td>
            <td style="text-align: right;"><strong>${this.listado1.reduce((t,o)=>t+(parseFloat(o.total)||0),0)}</strong></td>
        </tr>`,i+=`</table>
        `;let r=window.open("","_blank");r.document.write(i),r.document.close(),r.print()},getReporteTabla(){if(!this.producto.id){this.$alert?.error?.("Seleccione un producto");return}if(!this.gestion){this.$alert?.error?.("Seleccione una gestion");return}this.loading=!0,this.$axios.post("/reportePorcentual",{producto_id:this.producto.id,inicio:this.gestion+"-01-01",fin:this.gestion+"-12-31"}).then(({data:e})=>{console.log(e),this.listado2=e.data||e||[],this.crearGrafico()}).finally(()=>{this.loading=!1})},crearGrafico(){if(!this.$refs.grafico){console.error("Canvas aún no está montado");return}this.chartInstance&&this.chartInstance.destroy(),this.chartInstance=new p(this.$refs.grafico,{type:"bar",data:{labels:this.listado2.labels,datasets:[{label:"Porcentaje de acopio (%)",data:this.listado2.porcentaje,backgroundColor:"rgba(54, 162, 235, 0.6)",yAxisID:"y"},{label:"Total de productores",data:this.listado2.productores,backgroundColor:"rgba(255, 159, 64, 0.6)",yAxisID:"y1"}]},options:{responsive:!0,interaction:{mode:"index",intersect:!1},stacked:!1,scales:{y:{type:"linear",position:"left",title:{display:!0,text:"Porcentaje (%)"}},y1:{type:"linear",position:"right",title:{display:!0,text:"Productores (total)"},grid:{drawOnChartArea:!1}}}}})},getReporte(){if(!this.producto.id){this.$alert?.error?.("Seleccione un producto");return}if(!this.gestion){this.$alert?.error?.("Seleccione una gestion");return}this.loading=!0,this.$axios.post("/reporteAcopioProveedorMun",{producto_id:this.producto.id,inicio:this.gestion+"-01-01",fin:this.gestion+"-12-31"}).then(({data:e})=>{console.log(e),this.listado1=e.data||e||[]}).finally(()=>{this.loading=!1})},getReporte1(){this.loading=!0,this.$axios.post("/reportApicultorDep").then(({data:e})=>{this.reporte1=e.data||e||[]}).finally(()=>{this.loading=!1})},getReporte2(){this.loading=!0,this.$axios.post("/reportApicultorDepGenero").then(({data:e})=>{this.reporte2=e.data||e||[]}).finally(()=>{this.loading=!1})},getReporte3(){this.loading=!0,this.$axios.post("/reportePorcentualApicultorDep").then(({data:e})=>{this.reporte3=e.data||e||[]}).finally(()=>{this.loading=!1})},getReporte4(){this.loading=!0,this.$axios.post("/reportePorcentualColmenasDep").then(({data:e})=>{this.reporte4=e.data||e||[]}).finally(()=>{this.loading=!1})},getReporte5(){this.loading=!0,this.$axios.post("/reportePorcentualApicultorDepAcopio").then(({data:e})=>{this.reporte5=e.data||e||[]}).finally(()=>{this.loading=!1})},getReporte6(){this.loading=!0,this.$axios.post("/reportePorcentualApicultorDepAcopio2").then(({data:e})=>{this.reporte6=e.data||e||[]}).finally(()=>{this.loading=!1})},reportOrg(){if(!this.producto.id){this.$alert?.error?.("Seleccione un producto");return}if(!this.gestion){this.$alert?.error?.("Seleccione una gestion");return}this.loading=!0,this.$axios.post("/reportAcopioOrg",{gestion:this.gestion,producto_id:this.producto.id}).then(({data:e})=>{this.list_org=e.data||e||[]}).finally(()=>{this.loading=!1})},reportEdad(){this.loading=!0,this.$axios.post("/reportEdad").then(({data:e})=>{this.list_edad=e.data||e||[],this.graficoEdad()}).finally(()=>{this.loading=!1})},graficoEdad(){if(!this.$refs.grafico2){console.error("Canvas aún no está montado");return}this.chartInstance2&&this.chartInstance2.destroy();const e=this.list_edad.map(r=>r.rango_edad);this.list_edad.map(r=>r.total);const i=this.list_edad.map(r=>r.porcentaje);this.chartInstance2=new p(this.$refs.grafico2,{type:"doughnut",data:{labels:e,datasets:[{label:"Distribucion por rango de edad",data:i,backgroundColor:["rgb(255, 99, 132)","rgb(54, 162, 235)","rgb(255, 206, 86)","rgb(75, 192, 192)","rgb(153, 102, 255)","rgb(255, 159, 64)","rgb(255, 99, 132)","rgb(54, 162, 235)","rgb(255, 206, 86)","rgb(75, 192, 192)","rgb(153, 102, 255)","rgb(255, 159, 64)","rgb(255, 99, 132)","rgb(54, 162, 235)","rgb(255, 206, 86)","rgb(75, 192, 192)","rgb(153, 102, 255)"],borderWidth:1}]},options:{responsive:!0,plugins:{legend:{position:"right"},tooltip:{callbacks:{label:r=>`${r.label}: ${r.parsed}%`}},title:{display:!0,text:"Distribución porcentual por rango de edad"}}}})},async getProductos(){await this.$axios.get("/productos/tipo/1").then(({data:e})=>{this.productos=e?.data||e||[],this.productos.length>0&&(this.producto=this.productos[0])})}}},P={class:"row"},T={class:"col-4"},D={class:"col-4"},B={class:"col-4"},M={class:"text-left"},S={class:"text-right"},k={class:"text-right"};function F(e,i,r,t,o,b){return g(),m(A,{class:"q-pa-xs"},{default:n(()=>[l(x,{flat:"",bordered:""},{default:n(()=>[l(y,{class:"q-pa-none"},{default:n(()=>[a("div",P,[a("div",T,[l($,{modelValue:o.gestion,"onUpdate:modelValue":i[0]||(i[0]=s=>o.gestion=s),type:"number",label:"Gestion",dense:"",outlined:""},null,8,["modelValue"])]),a("div",D,[l(R,{modelValue:o.producto,"onUpdate:modelValue":i[1]||(i[1]=s=>o.producto=s),options:o.productos,"option-label":"nombre_producto",label:"Productos",dense:"",outlined:""},null,8,["modelValue","options"])]),a("div",B,[l(O,{color:"primary",label:"Acopios",onClick:b.getReporte,loading:o.loading,"no-caps":"",icon:"table_chart"},null,8,["onClick","loading"])])]),l(_,{dense:"",flat:"",bordered:""},{default:n(()=>[i[2]||(i[2]=a("thead",null,[a("tr",null,[a("th",{class:"text-center"},"MUNICIPIO"),a("th",{class:"text-center"},"PORCENTAJE ACOPIO (%)"),a("th",{class:"text-center"},"TOTAL PRODUCTORES")])],-1)),a("tbody",null,[(g(!0),c(E,null,I(o.listado2.labels,(s,d)=>(g(),c("tr",{key:d},[a("td",M,h(s),1),a("td",S,h(o.listado2.porcentaje[d]??0),1),a("td",k,h(o.listado2.productores[d]??0),1)]))),128))])]),_:1})]),_:1})]),_:1})]),_:1})}const Y=f(C,[["render",F]]);export{Y as default};
