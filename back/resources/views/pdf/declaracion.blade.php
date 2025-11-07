<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .titulo { text-align: center; font-weight: bold; margin-bottom: 10px; }
        .campo { border: 1px solid #000; padding: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        td { padding: 5px; vertical-align: top; }
        .firma { margin-top: 40px; text-align: center; }
    </style>
</head>
<body>

    <div class="titulo">
        EMPRESA BOLIVIANA DE ALIMENTOS Y DERIVADOS <br>
        <strong>DECLARACIÓN JURADA DEL PRODUCTOR - 2022</strong>
    </div>

    <table>
        <tr>
            <td><strong>NOMBRE(S):</strong> {{ $nombre }}</td>
            <td><strong>APELLIDO PATERNO:</strong> {{ $apellido_paterno }}</td>
            <td><strong>APELLIDO MATERNO:</strong> {{ $apellido_materno }}</td>
        </tr>
        <tr>
            <td><strong>EDAD:</strong> {{ $edad }}</td>
            <td><strong>NACIONALIDAD:</strong> {{ $nacionalidad }}</td>
            <td><strong>CI:</strong> {{ $ci }} {{ $expedido }}</td>
        </tr>
        <tr>
            <td colspan="3"><strong>OCUPACIÓN:</strong> {{ $ocupacion }}</td>
        </tr>
        <tr>
            <td colspan="3"><strong>DOMICILIO:</strong> {{ $domicilio }}</td>
        </tr>
        <tr>
            <td colspan="3"><strong>COMUNIDAD:</strong> {{ $comunidad }}</td>
        </tr>
        <tr>
            <td><strong>MUNICIPIO:</strong> {{ $municipio }}</td>
            <td colspan="2"><strong>DEPARTAMENTO:</strong> {{ $departamento }}</td>
        </tr>
    </table>

    <p style="margin-top: 20px; text-align: justify;">
        En calidad de declaración jurada, declaro ser productor/a y poseer un total de 
        <strong>{{ $colmenas }}</strong> colmenas en producción, ubicadas en la comunidad mencionada, 
        con una proyección de producción de hasta <strong>{{ $proyeccion }}</strong> kilogramos de miel por año;
        Asi tambien, declaro conocer los parámetros de calidad exigidos por la Empresa Boliviana de Alimentos y Derivados (EBA) y cumplir con dichos requisitos. 
        Autorizo al personal de EBA a realizar las visitas técnicas necesarias para corroborar la información de la presente declaración jurada.
    </p>
    <div class="firma">
        _______________________________________<br>
        <strong>FIRMA DEL(A) PRODUCTOR(A)</strong><br>
        Fecha: {{ $fecha }}
    </div>

</body>
</html>
