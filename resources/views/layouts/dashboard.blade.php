<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel - Archivo Clínico')</title>
    
    <!-- Bootstrap 5 Nativo -->
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Estilos específicos del Dashboard -->
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
    
    @stack('styles')
</head>
<body>

<div class="wrapper">
    <!-- 1. BARRA LATERAL (SIDEBAR) -->
    <nav id="sidebar" class="shadow-sm">
        <div class="sidebar-header text-center">
            <h5 class="fw-bold text-white mb-0">HOSPITAL BASE</h5>
            <small class="text-muted" style="font-size: 0.7rem;">Archivo Clínico v1</small>
        </div>

        <ul class="list-unstyled components mt-3">
            <li class="{{ request()->is('/') ? 'active' : '' }}">
                <a href="{{ route('home') }}">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard Principal
                </a>
            </li>
            
            <!-- Secciones que se habilitarán en los siguientes Sprints -->
            <li class="{{ request()->is('pacientes*') ? 'active' : '' }}">
                <a href="{{ route('pacientes.index') }}">
                    <i class="bi bi-people me-2"></i> Control de Pacientes
                </a>
            </li>
            </li>
            <li>
                <a href="#submenuPrestamos" class="text-muted" style="cursor: not-allowed;">
                    <i class="bi bi-journal-arrow-up me-2"></i> Préstamos (Sprint 3)
                </a>
            </li>
            <li>
                <a href="#submenuConfig" class="text-muted" style="cursor: not-allowed;">
                    <i class="bi bi-gear me-2"></i> Configuración
                </a>
            </li>
        </ul>
    </nav>

    <!-- 2. ÁREA DE CONTENIDO (DERECHA) -->
    <div id="content">
        <!-- Navbar Superior Interna -->
        <nav class="navbar navbar-expand-lg navbar-custom py-2 px-3">
            <div class="container-fluid">
                <!-- Botón para colapsar Sidebar en móviles -->
                <button type="button" id="sidebarCollapse" class="btn btn-sm btn-outline-secondary me-3">
                    <span class="navbar-toggler-icon" style="width: 1.2rem; height: 1.2rem;"></span>
                </button>

                <span class="navbar-text fw-medium text-dark d-none d-sm-inline">
                    Bienvenido, <span class="text-brand fw-bold">{{ Auth::user()->nombres }}</span>
                </span>

                <div class="ms-auto d-flex align-items-center">
                    <!-- Información del Rol actual del usuario logueado -->
                    <span class="badge bg-secondary me-3 d-none d-md-inline">
                        {{ Auth::user()->rol->nombre_rol ?? 'Personal' }}
                    </span>

                    <!-- Formulario Seguro de Cierre de Sesión (Logout) -->
                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-3 rounded-2 fw-medium">
                            Salir
                        </button>
                    </form>
                </div>
            </div>
        </nav>

        <!-- Sección Dinámica donde se inyectarán los Sprints -->
        <div class="container-fluid p-4">
            @yield('dashboard_content')
        </div>
    </div>
</div>

<!-- Scripts Base -->
<script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('js/dashboard.js') }}"></script>
@stack('scripts')
</body>
</html>
