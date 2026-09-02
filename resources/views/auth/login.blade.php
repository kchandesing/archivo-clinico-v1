@extends('layouts.app')

@section('title', 'Inicio de Sesión - Archivo Clínico')

@push('styles')
    <link href="{{ asset('css/installer.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container py-5">
    <div class="row justify-content-center my-5">
        <div class="col-12 col-md-6 col-lg-4">
            
            <div class="text-center mb-4">
                <h3 class="fw-bold text-dark m-0">Archivo Clínico 1.0</h3>
                <p class="text-muted small">Ingresa tus credenciales de acceso</p>
            </div>

            <div class="card border-0 border-brand-top shadow-sm p-4 bg-white rounded-3">
                
                <form action="{{ route('login.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label text-secondary small fw-medium">Correo Electrónico</label>
                        <input type="email" class="form-control form-control-sm @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="nombre@hospital.com" required autofocus>
                        @error('email')
                            <div class="invalid-feedback small d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label text-secondary small fw-medium">Contraseña</label>
                        <input type="password" class="form-control form-control-sm" id="password" name="password" placeholder="••••••••" required>
                    </div>

                    <div class="mb-3 form-check d-flex justify-content-between align-items-center">
                        <div>
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label text-secondary small" for="remember">Recordarme</label>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-brand w-100 py-2 shadow-sm rounded-2 small">
                            Iniciar Sesión
                        </button>
                    </div>

                </form>
            </div>

            @if(session('success'))
                <div class="alert alert-success text-center mt-3 p-2 small" role="alert">
                    {{ session('success') }}
                </div>
            @endif

        </div>
    </div>
</div>
@endsection
