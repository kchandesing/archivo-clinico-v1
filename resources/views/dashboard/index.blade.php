@extends('layouts.dashboard')

@section('title', 'Dashboard Principal - Archivo Clínico')

@section('dashboard_content')
<div class="row mb-4">
    <div class="col-12">
        <h4 class="fw-bold text-dark m-0">Resumen Operativo</h4>
        <p class="text-muted small">Estado del archivo clínico en tiempo real</p>
    </div>
</div>

<!-- Tarjetas Métricas Responsivas (Grid de Bootstrap) -->
<div class="row g-3">
    <!-- Tarjeta 1: Pacientes -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm p-3 bg-white rounded-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small d-block fw-medium mb-1">PACIENTES</span>
                    <h3 class="fw-bold text-dark m-0">0</h3>
                </div>
                <div class="p-2 bg-light rounded-3 text-secondary">
                    <!-- Indicador visual provisional -->
                    <span class="fs-4">👥</span>
                </div>
            </div>
            <small class="text-muted d-block mt-2" style="font-size: 0.72rem;">Expedientes totales en la BD</small>
        </div>
    </div>

    <!-- Tarjeta 2: Ubicaciones -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm p-3 bg-white rounded-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small d-block fw-medium mb-1">EN ARCHIVO</span>
                    <h3 class="fw-bold text-success m-0">0</h3>
                </div>
                <div class="p-2 bg-light rounded-3">
                    <span class="fs-4">🗄️</span>
                </div>
            </div>
            <small class="text-muted d-block mt-2" style="font-size: 0.72rem;">Expedientes físicos disponibles</small>
        </div>
    </div>

    <!-- Tarjeta 3: Préstamos Activos -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm p-3 bg-white rounded-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small d-block fw-medium mb-1">PRESTADOS</span>
                    <h3 class="fw-bold text-warning m-0">0</h3>
                </div>
                <div class="p-2 bg-light rounded-3">
                    <span class="fs-4">📋</span>
                </div>
            </div>
            <small class="text-muted d-block mt-2" style="font-size: 0.72rem;">Expedientes en consulta médica</small>
        </div>
    </div>

    <!-- Tarjeta 4: Auditoría Reciente -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm p-3 bg-white rounded-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small d-block fw-medium mb-1">AUDITORÍA</span>
                    <h3 class="fw-bold text-info m-0">0</h3>
                </div>
                <div class="p-2 bg-light rounded-3">
                    <span class="fs-4">🛡️</span>
                </div>
            </div>
            <small class="text-muted d-block mt-2" style="font-size: 0.72rem;">Eventos registrados hoy en Syslog</small>
        </div>
    </div>
</div>

<!-- Contenedor para el Buscador de Sprints Futuros -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm p-4 bg-white rounded-3">
            <h6 class="fw-bold text-dark mb-2">Buscador Operativo Integrado</h6>
            <p class="text-muted small mb-0">La barra de búsquedas alfabéticas rápidas mediante índices trigram (`pg_trgm`) se acoplará en esta sección durante el desarrollo del Sprint 4.</p>
        </div>
    </div>
</div>
@endsection
