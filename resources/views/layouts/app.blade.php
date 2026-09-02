<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Archivo Clínico v1')</title>
    
    <!-- Bootstrap 5 Nativo (Carga Limpia con Asset de Laravel) -->
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    
    <!-- Espacio por si alguna vista requiere estilos CSS adicionales -->
    @stack('styles')
</head>
<body class="bg-light">

    <!-- El contenido de cada sección del software se inyectará aquí -->
    <main>
        @yield('content')
    </main>

    <!-- Bootstrap 5 JS Global -->
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    
    <!-- Espacio por si alguna vista requiere scripts JS adicionales -->
    @stack('scripts')
</body>
</html>

