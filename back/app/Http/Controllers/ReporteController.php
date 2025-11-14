<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    //
    public function reporteAcopioProveedorDep(Request $request){
        // Lógica para generar el reporte de productos
        $res = DB::SELECT("SELECT 
            d.nombre_departamento,
            COUNT(DISTINCT p.id) AS productores,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 1) AS enero,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 2) AS febrero,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 3) AS marzo,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 4) AS abril,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 5) AS mayo,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 6) AS junio,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 7) AS julio,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 8) AS agosto,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 9) AS septiembre,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 10) AS octubre,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 11) AS noviembre,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 12) AS diciembre,
            SUM(ac.cantidad_kg) AS total_anual
            FROM departamentos d 
            INNER JOIN municipios m ON d.id = m.departamento_id
            INNER JOIN productores p ON p.municipio_id = m.id 
            INNER JOIN apiarios a ON a.productor_id = p.id
            INNER JOIN acopio_cosechas ac ON ac.apiario_id = a.id
            WHERE ac.producto_id = $request->producto_id
            AND ac.fecha_cosecha BETWEEN '$request->inicio' AND '$request->fin'
            GROUP BY d.nombre_departamento
            ORDER BY d.nombre_departamento;");
        return $res;
    }

        public function reporteAcopioProveedorMun(Request $request){
        // Lógica para generar el reporte de productos
        $res = DB::SELECT("SELECT 
            m.nombre_municipio municipio,
            COUNT(DISTINCT p.id) AS productores,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 1) AS enero,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 2) AS febrero,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 3) AS marzo,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 4) AS abril,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 5) AS mayo,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 6) AS junio,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 7) AS julio,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 8) AS agosto,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 9) AS septiembre,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 10) AS octubre,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 11) AS noviembre,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 12) AS diciembre,
            SUM(ac.cantidad_kg) AS total
            FROM departamentos d 
            INNER JOIN municipios m ON d.id = m.departamento_id
            INNER JOIN productores p ON p.municipio_id = m.id 
            INNER JOIN apiarios a ON a.productor_id = p.id
            INNER JOIN acopio_cosechas ac ON ac.apiario_id = a.id
            WHERE ac.producto_id = $request->producto_id
            AND ac.fecha_cosecha BETWEEN '$request->inicio' AND '$request->fin'
            GROUP BY m.nombre_municipio
            ORDER BY m.nombre_municipio;");
        return $res;
    }



    public function reportePorcentual(Request $request){
        // Lógica para generar el reporte de productos
        $request->validate([
            'producto_id' => 'required|integer',
            'inicio' => 'required|date',
            'fin' => 'required|date',
        ]);

        // Ejecutamos la consulta
        $res = DB::select("
            WITH meses AS (
                SELECT generate_series(1, 12) AS mes
            ),
            acopio_mes AS (
                SELECT 
                    EXTRACT(MONTH FROM ac.fecha_cosecha)::int AS mes,
                    SUM(ac.cantidad_kg) AS total_mes,
                    COUNT(DISTINCT p.id) AS productores_mes
                FROM departamentos d
                INNER JOIN municipios m ON d.id = m.departamento_id
                INNER JOIN productores p ON p.municipio_id = m.id 
                INNER JOIN apiarios a ON a.productor_id = p.id
                INNER JOIN acopio_cosechas ac ON ac.apiario_id = a.id
                WHERE ac.producto_id = :producto_id
                    AND ac.fecha_cosecha BETWEEN :inicio AND :fin
                GROUP BY EXTRACT(MONTH FROM ac.fecha_cosecha)
            ),
            total_anual AS (
                SELECT COALESCE(SUM(total_mes), 0) AS total_kg_anual FROM acopio_mes
            )
            SELECT 
                m.mes,
                INITCAP(TO_CHAR(TO_DATE(m.mes::text, 'MM'), 'TMMonth')) AS nombre_mes,
                COALESCE(a.productores_mes, 0) AS productores_mes,
                COALESCE(a.total_mes, 0) AS acopio_kg,
                CASE 
                    WHEN t.total_kg_anual > 0 
                    THEN ROUND((COALESCE(a.total_mes, 0) / t.total_kg_anual) * 100, 2)
                    ELSE 0
                END AS porcentaje
            FROM meses m
            LEFT JOIN acopio_mes a ON m.mes = a.mes
            CROSS JOIN total_anual t
            ORDER BY m.mes;
        ", [
            'producto_id' => $request->producto_id,
            'inicio' => $request->inicio,
            'fin' => $request->fin,
        ]);

        // Estructuramos los datos para el front
        $labels = [];
        $porcentajes = [];
        $productores = [];

        foreach ($res as $fila) {
            $labels[] = $fila->nombre_mes;
            $porcentajes[] = (float)$fila->porcentaje;
            $productores[] = (int)$fila->productores_mes;
        }

        return response()->json([
            'labels' => $labels,
            'porcentaje' => $porcentajes,
            'productores' => $productores,
        ]);
    }

    public function reportEdad(Request $request){
        $res = DB::SELECT("SELECT rango_edad, COUNT(*) AS total, ROUND((COUNT(*) * 100.0 / SUM(COUNT(*)) OVER ()), 1) AS porcentaje																														
FROM ( SELECT CASE																														
WHEN EXTRACT(YEAR FROM AGE(now()::DATE, fec_nacimiento)) BETWEEN 18 AND 19 THEN '18 a 19'																														
WHEN EXTRACT(YEAR FROM AGE(now()::DATE, fec_nacimiento)) BETWEEN 20 AND 24 THEN '20 a 24'																														
WHEN EXTRACT(YEAR FROM AGE(now()::DATE, fec_nacimiento)) BETWEEN 25 AND 29 THEN '25 a 29'																														
WHEN EXTRACT(YEAR FROM AGE(now()::DATE, fec_nacimiento)) BETWEEN 30 AND 34 THEN '30 a 34'																														
WHEN EXTRACT(YEAR FROM AGE(now()::DATE, fec_nacimiento)) BETWEEN 35 AND 39 THEN '35 a 39'																														
WHEN EXTRACT(YEAR FROM AGE(now()::DATE, fec_nacimiento)) BETWEEN 40 AND 44 THEN '40 a 44'																														
WHEN EXTRACT(YEAR FROM AGE(now()::DATE, fec_nacimiento)) BETWEEN 45 AND 49 THEN '45 a 49'																														
WHEN EXTRACT(YEAR FROM AGE(now()::DATE, fec_nacimiento)) BETWEEN 50 AND 54 THEN '50 a 54'																														
WHEN EXTRACT(YEAR FROM AGE(now()::DATE, fec_nacimiento)) BETWEEN 55 AND 59 THEN '55 a 59'																														
WHEN EXTRACT(YEAR FROM AGE(now()::DATE, fec_nacimiento)) BETWEEN 60 AND 64 THEN '60 a 64'																														
WHEN EXTRACT(YEAR FROM AGE(now()::DATE, fec_nacimiento)) BETWEEN 65 AND 69 THEN '65 a 69'																														
WHEN EXTRACT(YEAR FROM AGE(now()::DATE, fec_nacimiento)) BETWEEN 70 AND 74 THEN '70 a 74'																														
WHEN EXTRACT(YEAR FROM AGE(now()::DATE, fec_nacimiento)) BETWEEN 75 AND 79 THEN '75 a 79'																														
WHEN EXTRACT(YEAR FROM AGE(now()::DATE, fec_nacimiento)) BETWEEN 80 AND 84 THEN '80 a 84'																														
WHEN EXTRACT(YEAR FROM AGE(now()::DATE, fec_nacimiento)) BETWEEN 85 AND 89 THEN '85 a 89'																														
WHEN EXTRACT(YEAR FROM AGE(now()::DATE, fec_nacimiento)) BETWEEN 90 AND 94 THEN '90 a 94'																														
ELSE 'Fuera de rango' END AS rango_edad																														
FROM public.productores																														
WHERE fec_nacimiento IS NOT NULL																														
AND EXTRACT(YEAR FROM AGE(now()::DATE, fec_nacimiento)) BETWEEN 18 AND 94																														
) AS age_groups																														
GROUP BY rango_edad																														
ORDER BY																														
CASE																														
WHEN rango_edad = '18 a 19' THEN 1																														
WHEN rango_edad = '20 a 24' THEN 2																														
WHEN rango_edad = '25 a 29' THEN 3																														
WHEN rango_edad = '30 a 34' THEN 4																														
WHEN rango_edad = '35 a 39' THEN 5																														
WHEN rango_edad = '40 a 44' THEN 6																														
WHEN rango_edad = '45 a 49' THEN 7																														
WHEN rango_edad = '50 a 54' THEN 8																														
WHEN rango_edad = '55 a 59' THEN 9																														
WHEN rango_edad = '60 a 64' THEN 10																														
WHEN rango_edad = '65 a 69' THEN 11																														
WHEN rango_edad = '70 a 74' THEN 12																														
WHEN rango_edad = '75 a 79' THEN 13																														
WHEN rango_edad = '80 a 84' THEN 14																														
WHEN rango_edad = '85 a 89' THEN 15																														
WHEN rango_edad = '90 a 94' THEN 16																														
ELSE 17																														
END ;" );
        return $res;
    }

    // reoprte acopio por organizacion
    public function reportAcopioOrg(Request $request){
        $res= DB::SELECT("SELECT o.asociacion,
    SUM(CASE WHEN ac.mes = 'ene' THEN ac.cantidad_kg ELSE 0 END) AS ene,
    SUM(CASE WHEN ac.mes = 'feb' THEN ac.cantidad_kg ELSE 0 END) AS feb,
    SUM(CASE WHEN ac.mes = 'mar' THEN ac.cantidad_kg ELSE 0 END) AS mar,
    SUM(CASE WHEN ac.mes = 'abr' THEN ac.cantidad_kg ELSE 0 END) AS abr,
    SUM(CASE WHEN ac.mes = 'may' THEN ac.cantidad_kg ELSE 0 END) AS may,
    SUM(CASE WHEN ac.mes = 'jun' THEN ac.cantidad_kg ELSE 0 END) AS jun,
    SUM(CASE WHEN ac.mes = 'jul' THEN ac.cantidad_kg ELSE 0 END) AS jul,
    SUM(CASE WHEN ac.mes = 'ago' THEN ac.cantidad_kg ELSE 0 END) AS ago,
    SUM(CASE WHEN ac.mes = 'sep' THEN ac.cantidad_kg ELSE 0 END) AS sep,
    SUM(CASE WHEN ac.mes = 'oct' THEN ac.cantidad_kg ELSE 0 END) AS oct,
    SUM(CASE WHEN ac.mes = 'nov' THEN ac.cantidad_kg ELSE 0 END) AS nov,
    SUM(CASE WHEN ac.mes = 'dic' THEN ac.cantidad_kg ELSE 0 END) AS dic
FROM public.organizaciones o
left JOIN public.productores p ON o.id = p.organizacion_id
left JOIN public.apiarios a ON a.productor_id = p.id
left JOIN public.v_acopio_cosechas_gestion_mes ac ON ac.apiario_id = a.id
WHERE ac.gestion = $request->gestion
and ac.producto_id = $request->producto_id
GROUP BY o.asociacion" );
        return $res;
    }

    // apivultores por departamento
    public function reportApicultorDep(){
        $res = DB::SELECT("SELECT d.nombre_departamento, count(*) num_apicultor,			
        COUNT(CASE WHEN runsa IS NOT NULL AND runsa <> '0' THEN 1 END) AS con_rumsa,
        SUM(a.numero_colmenas_prod) AS num_col_prod, 
        SUM(a.numero_colmenas_runsa) AS prod_promedio,			
        SUM(CASE WHEN sexo = 1 THEN 1 ELSE 0 END) varon, 
        SUM(CASE WHEN sexo = 2 THEN 1 ELSE 0 END) mujer			
        from public.productores p			
        left join public.municipios m ON p.municipio_id = m.id	
        left join public.departamentos d ON m.departamento_id = d.id	
        left join public.apiarios a ON a.productor_id  = p.id
        where p.id != 0 and a.id != 0
        group by d.nombre_departamento;");
        return $res;
    }

    // porcentaje apicultores por departamento y producicion por genero
    public function reportApicultorDepGenero(){
        $res = DB::SELECT("SELECT d.nombre_departamento, count(*) num_apicultor,			
            COUNT(runsa) AS con_rumsa,SUM(a.numero_colmenas_prod) AS num_col_prod,
            SUM(a.numero_colmenas_runsa) AS prod_promedio,			
            SUM(CASE WHEN sexo = 1 THEN 1 ELSE 0 END) varon, 
            SUM(CASE WHEN sexo = 2 THEN 1 ELSE 0 END) mujer			
            from public.productores p			
            left join public.municipios m ON p.municipio_id = m.id	
            left join public.departamentos d ON m.departamento_id = d.id	
            left join public.apiarios a ON a.productor_id  = p.id
            where p.id != 0 and a.id != 0
            group by d.nombre_departamento;");
        return $res;
    }
    
    // porcentaje apicultores por departamento
    public function reportePorcentualApicultorDep(){
        $res = DB::SELECT("SELECT  d.nombre_departamento, COUNT(*) AS num_apicultor,			
            SUM(COUNT(*)) OVER() AS total_general, ROUND((COUNT(*) * 100.0 / SUM(COUNT(*)) OVER()), 2) AS porcentaje			
            from public.productores p			
            left join public.municipios m ON p.municipio_id = m.id	
            left join public.departamentos d ON m.departamento_id = d.id	
            left join public.apiarios a ON a.productor_id  = p.id
            where p.id != 0 and a.id != 0		
            group by d.nombre_departamento	;");
        return $res;
    }

    // porcentaje colmenas por departamento
    public function reportePorcentualColmenasDep(){
        $res = DB::SELECT("SELECT  d.nombre_departamento, SUM(a.numero_colmenas_prod) AS num_colmenas,			
            SUM(SUM(a.numero_colmenas_prod)) OVER() AS total_general, ROUND((SUM(a.numero_colmenas_prod) * 100.0 / SUM(SUM(a.numero_colmenas_prod)) OVER()), 2) AS porcentaje			
            from public.productores p			
            left join public.municipios m ON p.municipio_id = m.id	
            left join public.departamentos d ON m.departamento_id = d.id	
            left join public.apiarios a ON a.productor_id  = p.id
            where p.id != 0 and a.id != 0				
            group by  d.nombre_departamento	;");
        return $res;
    }

    // porcentaje apicultores por departamento con acopio
    public function reportePorcentualApicultorDepAcopio(){
        $res = DB::SELECT("SELECT  d.nombre_departamento, COUNT(*) AS num_apicultor, COUNT(runsa) AS con_runsa,	
            SUM(COUNT(*)) OVER() AS total_general, ROUND((COUNT(*) * 100.0 / SUM(COUNT(*)) OVER()), 2) AS porcentaje	
            from public.productores p			
            left join public.municipios m ON p.municipio_id = m.id	
            left join public.departamentos d ON m.departamento_id = d.id	
            left join public.apiarios a ON a.productor_id  = p.id
            where p.id != 0 and a.id != 0				
            group by  d.nombre_departamento	;");
        return $res;
    }

    // porcentaje apicultores por departamento con acopio
    public function reportePorcentualApicultorDepAcopio2(){
        return DB::SELECT("SELECT d.nombre_departamento, COUNT(*) AS num_apicultor, COUNT(runsa) AS con_runsa,	
            SUM(COUNT(*)) OVER() AS total_general, ROUND((COUNT(*) * 100.0 / SUM(COUNT(*)) OVER()), 2) AS porcentaje	
            from public.productores p			
            left join public.municipios m ON p.municipio_id = m.id	
            left join public.departamentos d ON m.departamento_id = d.id	
            JOIN public.apiarios a ON a.productor_id = p.id
            JOIN public.v_acopio_cosechas_gestion_mes ac ON ac.apiario_id = a.id
            where p.id != 0 and a.id != 0				
            group by  d.nombre_departamento
            ;");
    }
}

