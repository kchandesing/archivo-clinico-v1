<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Archivo Clínico v1')</title>
    
    <!-- Bootstrap 5 Nativo (Para todo el sistema) -->
    <link href="https://jsdelivr.net" rel="stylesheet">
    
    <!-- Espacio por si alguna vista requiere estilos CSS adicionales -->
    @stack('styles')
</head>
<body class="bg-light">

    <!-- El contenido de cada sección del software se inyectará aquí -->
    <main>
        @yield('content')
    </main>

    <!-- Bootstrap 5 JS Global -->
    <script src="https://jsdelivr.net"></script>
    
    <!-- Espacio por si alguna vista requiere scripts JS adicionales -->
    @stack('scripts')
</body>
</html>
