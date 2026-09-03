@extends('layouts.dashboard')

@section('title', 'Control de Pacientes - Archivo Clínico')

@section('dashboard_content')
<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-6">
        <h4 class="fw-bold text-dark m-0">Control de Expedientes</h4>
        <p class="text-muted small m-0">Búsqueda alfabética y clínica de pacientes</p>
    </div>
    <div class="col-12 col-md-6 text-md-end mt-3 mt-md-0">
        <!-- Botón para ir al formulario de captura (Sprint 2 - Siguiente paso) -->
        <a href="{{ route('pacientes.create') }}" class="btn btn-sm btn-success px-3 py-2 rounded-2 fw-medium shadow-sm">
            + Registrar Nuevo Paciente
        </a>
    </div>
</div>

<!-- Alertas de Éxito o Error Operativo -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm p-3 small mb-4" role="alert">
        <strong>Éxito:</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->has('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm p-3 small mb-4" role="alert">
        <strong>Error:</strong> {{ $errors->first('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- BARRA DE BÚSQUEDA GLOBAL (ÍNDICES TRIGRAM PG_TRGM) -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm p-3 bg-white rounded-3">
            <!-- Formulario síncrono que envía por GET para sobreponer resultados en la misma tabla -->
            <form action="{{ route('pacientes.index') }}" method="GET" id="searchForm">
                <div class="input-group">
                    <input type="text" 
                           name="query" 
                           class="form-control form-control-sm bg-light border-secondary-subtle" 
                           placeholder="Escribe el Número de Expediente, CURP o Nombre completo del paciente..." 
                           value="{{ $searchTerm }}"
                           autocomplete="off">
                    <button type="submit" class="btn btn-sm btn-secondary px-4 fw-medium">
                        Buscar Paciente
                    </button>
                    @if(!empty($searchTerm))
                        <!-- Botón para limpiar filtro rápidamente si hay una búsqueda activa -->
                        <a href="{{ route('pacientes.index') }}" class="btn btn-sm btn-outline-danger d-flex align-items-center px-3">
                            Limpiar
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

<!-- TABLA DE RESULTADOS DE EXPEDIENTES (RESPONSIVA) -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm bg-white rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap" style="font-size: 0.88rem;">
                    <thead class="table-light text-secondary fw-semibold">
                        <tr>
                            <th class="ps-4">No. Expediente</th>
                            <th>Nombre Completo</th>
                            <th>CURP</th>
                            <th>Sexo</th>
                            <th>Fecha Nac.</th>
                            <th>Ubicación Física</th>
                            <th>Estado</th>
                            <th class="text-center pe-4" style="width: 100px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-dark">
                        @forelse($pacientes as $paciente)
                            <tr>
                                <!-- Folio o Folio Provisional -->
                                <td class="ps-4 fw-bold text-secondary">
                                    {{ $paciente->numero_expediente }}
                                </td>
                                <!-- Nombre Combinado -->
                                <td class="fw-semibold">
                                    {{ $paciente->nombres }} {{ $paciente->apellido_paterno }} {{ $paciente->apellido_materno }}
                                </td>
                                <!-- Identificador de Identidad -->
                                <td>
                                    @if($paciente->es_provisional)
                                        <span class="text-muted small italic">S/C (Provisional)</span>
                                    @else
                                        <code class="text-dark select-all">{{ $paciente->curp }}</code>
                                    @endif
                                </td>
                                <!-- Demográficos genéricos -->
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $paciente->sexo }}
                                    </span>
                                </td>
                                <td>
                                    {{ $paciente->fecha_nacimiento->format('d/m/Y') }}
                                </td>
                                <!-- Mapeo opcional de Localización Física -->
                                <td>
                                    @if($paciente->ubicacionFisica && ($paciente->ubicacionFisica->pasillo || $paciente->ubicacionFisica->estante))
                                        <span class="text-secondary small fw-medium">
                                            P:{{ $paciente->ubicacionFisica->pasillo ?? '-' }} | E:{{ $paciente->ubicacionFisica->estante ?? '-' }}
                                        </span>
                                    @else
                                        <span class="text-muted small">No Asignada</span>
                                    @endif
                                </td>
                                <!-- Estatus de Préstamos -->
                                <td>
                                    @php
                                        $estado = $paciente->ubicacionFisica->estado_expediente ?? 'En Archivo';
                                    @php
                                    <span class="badge {{ $estado === 'En Archivo' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' }} px-2 py-1 rounded-2 fw-semibold" style="font-size: 0.72rem;">
                                        {{ $estado }}
                                    </span>
                                </td>
                                <!-- Acciones Ficha Médica -->
                                <td class="text-center pe-4">
                                    <a href="{{ route('pacientes.show', $paciente->id_paciente) }}" class="btn btn-xs btn-outline-secondary py-1 px-2 rounded-2 fw-medium" style="font-size: 0.75rem;">
                                        Ver Ficha
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <!-- Estado Vacío Informativo -->
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <span class="fs-2 d-block mb-2">📋</span>
                                    <h6 class="fw-semibold m-0">No se encontraron expedientes clínicos</h6>
                                    <small class="text-muted">Intenta cambiar los términos de búsqueda o registra un nuevo paciente en el sistema.</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
