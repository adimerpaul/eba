<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cuenta creada</title>
</head>
<body>
<h2>Hola {{ $name }},</h2>
<p>Su usuario ha sido creado exitosamente en el sistema.</p>
<p><strong>Credenciales de acceso:</strong></p>
<ul>
    <li><strong>Usuario:</strong> {{ $username }}</li>
    <li><strong>Contraseña:</strong> {{ $password }}</li>
</ul>
{{--<p>Por favor, inicie sesión y cambie su contraseña lo antes posible.</p>--}}
<p>Saludos,<br>El equipo del sistema</p>
</body>
</html>
