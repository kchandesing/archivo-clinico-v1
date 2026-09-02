@extends('layouts.app')

@section('title', 'Asistente de Instalación - Archivo Clínico')

@push('styles')
    <!-- Inyectamos el CSS exclusivo del instalador en la cabecera del layout -->
    <link href="{{ asset('css/installer.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            
            <!-- Encabezado -->
            <div class="text-center mb-4">
                <h2 class="fw-bold text-dark m-0">Archivo Clínico v1</h2>
                <p class="text-muted small">Asistente de inicialización del entorno</p>
            </div>

            <!-- Contenedor Base -->
            <div class="card border-0 border-brand-top shadow-sm p-4 bg-white rounded-3">
                
                @if($errors->has('error'))
                    <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                        <small><strong>Fallo en la instalación:</strong> {{ $errors->first('error') }}</small>
                    </div>
                @endif

                <form action="{{ route('install.store') }}" method="POST" id="installerForm">
                    @csrf

                    <!-- SECCIÓN 1: SEGURIDAD DEL SOFTWARE -->
                    <div class="mb-3">
                        <label for="master_key" class="form-label text-secondary fw-semibold small">Llave Maestra (Master Key)</label>
                        <input type="password" class="form-control form-control-sm @error('master_key') is-invalid @enderror" id="master_key" name="master_key" placeholder="Autorizar instalación" required>
                        @error('master_key')
                            <div class="invalid-feedback small">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- SECCIÓN 2: INFRAESTRUCTURA DE BASE DE DATOS -->
                    <h6 class="text-brand fw-bold mt-4 mb-3 border-bottom pb-2 small">CONFIGURACIÓN POSTGRESQL</h6>
                    
                    <div class="row g-2">
                        <div class="col-8">
                            <label for="db_host" class="form-label text-secondary small">Host Servidor</label>
                            <input type="text" class="form-control form-control-sm @error('db_host') is-invalid @enderror" id="db_host" name="db_host" value="{{ old('db_host', 'localhost') }}" required>
                            @error('db_host') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-4">
                            <label for="db_port" class="form-label text-secondary small">Puerto</label>
                            <input type="text" class="form-control form-control-sm @error('db_port') is-invalid @enderror" id="db_port" name="db_port" value="{{ old('db_port', '5433') }}" required>
                            @error('db_port') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-2 mt-1">
                        <div class="col-12 col-sm-6">
                            <label for="db_username" class="form-label text-secondary small">Usuario Master DB</label>
                            <input type="text" class="form-control form-control-sm @error('db_username') is-invalid @enderror" id="db_username" name="db_username" value="{{ old('db_username', 'postgres') }}" required>
                            @error('db_username') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="db_password" class="form-label text-secondary small">Contraseña DB</label>
                            <input type="password" class="form-control form-control-sm @error('db_password') is-invalid @enderror" id="db_password" name="db_password" placeholder="Contraseña Postgres" required>
                            @error('db_password') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3 mt-2">
                        <label ask for="database_name" class="form-label text-secondary small">Nombre de la Base de Datos a Crear</label>
                        <input type="text" class="form-control form-control-sm @error('database_name') is-invalid @enderror" id="database_name" name="database_name" value="{{ old('database_name', 'archivo_clinico') }}" required>
                        <div class="form-text text-muted" style="font-size: 0.75rem;">Se creará automáticamente inyectando el esquema clínico.</div>
                        @error('database_name') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                    </div>

                    <!-- SECCIÓN 3: CREDENCIALES DEL PRIMER ADMINISTRADOR -->
                    <h6 class="text-brand fw-bold mt-4 mb-3 border-bottom pb-2 small">PRIMER ADMINISTRADOR</h6>

                    <div class="mb-2">
                        <label for="admin_nombres" class="form-label text-secondary small">Nombre(s)</label>
                        <input type="text" class="form-control form-control-sm @error('admin_nombres') is-invalid @enderror" id="admin_nombres" name="admin_nombres" value="{{ old('admin_nombres') }}" required max="50">
                        @error('admin_nombres') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-2">
                        <div class="col-12 col-sm-6">
                            <label for="admin_paterno" class="form-label text-secondary small">Apellido Paterno</label>
                            <input type="text" class="form-control form-control-sm @error('admin_paterno') is-invalid @enderror" id="admin_paterno" name="admin_paterno" value="{{ old('admin_paterno') }}" required max="50">
                            @error('admin_paterno') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="admin_materno" class="form-label text-secondary small">Apellido Materno (Opcional)</label>
                            <input type="text" class="form-control form-control-sm @error('admin_materno') is-invalid @enderror" id="admin_materno" name="admin_materno" value="{{ old('admin_materno') }}" max="50">
                            @error('admin_materno') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-2 mt-2">
                        <label for="admin_email" class="form-label text-secondary small">Correo Electrónico</label>
                        <input type="email" class="form-control form-control-sm @error('admin_email') is-invalid @enderror" id="admin_email" name="admin_email" value="{{ old('admin_email') }}" placeholder="ejemplo@hospital.com" required>
                        @error('admin_email') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-2">
                        <div class="col-12 col-sm-6">
                            <label for="admin_password" class="form-label text-secondary small">Contraseña Admin</label>
                            <input type="password" class="form-control form-control-sm @error('admin_password') is-invalid @enderror" id="admin_password" name="admin_password" required minlength="8">
                            @error('admin_password') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="admin_password_confirmation" class="form-label text-secondary small">Confirmar Contraseña</label>
                            <input type="password" class="form-control form-control-sm" id="admin_password_confirmation" name="admin_password_confirmation" required minlength="8">
                        </div>
                    </div>

                    <!-- BOTÓN ACCIÓN -->
                    <div class="mt-4">
                        <button type="submit" class="btn btn-brand w-100 py-2 shadow-sm rounded-2" id="submitBtn">
                            Iniciar Aprovisionamiento Seguro
                        </button>
                    </div>

                </form>
            </div>
            
            <div class="text-center mt-3">
                <p class="text-muted" style="font-size: 0.75rem;">&copy; {{ date('Y') }} Sistema de Archivo Clínico. Arquitectura Desacoplada SOLID.</p>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
    <!-- Inyectamos el JS exclusivo del instalador al final del layout -->
    <script src="{{ asset('js/installer.js') }}"></script>
@endpush
