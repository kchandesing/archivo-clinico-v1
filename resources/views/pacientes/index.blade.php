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
        <!-- <a href="{{ route('pacientes.create') }}" class="btn btn-sm btn-success px-3 py-2 rounded-2 fw-medium shadow-sm">
            + Registrar Nuevo Paciente
        </a> -->
        <!-- Botón para ir al formulario de captura (Sprint 2 - Siguiente paso) -->
        <button type="button" class="btn btn-sm btn-success px-3 py-2 rounded-2 fw-medium shadow-sm" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
            Registrar Nuevo Paciente
        </button>


        <!-- Modal -->
        <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <i class="bi bi-person-fill"></i>
                        <h5 class="modal-title" id="staticBackdropLabel">Modal title</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        ...
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary">Understood</button>
                    </div>
                </div>
            </div>
        </div>
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
                <!-- Fila principal de inputs utilizando el espaciado responsivo g-3 de Bootstrap 5 -->
                <div class="row g-3 mb-3">
                    
                    <!-- Campo CURP: 12 columnas en móvil, 6 en tablet/laptop, 4 en pantallas de escritorio grandes -->
                    <div class="col-12 col-md-6 col-lg-3">
                        <label for="curp" class="form-label fw-bold text-secondary small mb-1">CURP:</label>
                        <input type="text" 
                            id="curp"
                            name="curp" 
                            class="form-control form-control-sm text-uppercase border-secondary-subtle" 
                            placeholder=""
                            value="{{ request('curp') }}"
                            autocomplete="off">
                    </div>

                    <!-- Campo Nombre(s) -->
                    <div class="col-12 col-md-6 col-lg-3">
                        <label for="nombres" class="form-label fw-bold text-secondary small mb-1">Nombre(s):</label>
                        <input type="text" 
                            id="nombres"
                            name="nombres" 
                            class="form-control form-control-sm border-secondary-subtle" 
                            placeholder=""
                            value="{{ request('nombres') }}"
                            autocomplete="off">
                    </div>

                    <!-- Campo Primer Apellido -->
                    <div class="col-12 col-md-6 col-lg-3">
                        <label for="apellido_paterno" class="form-label fw-bold text-secondary small mb-1">Primer Apellido:</label>
                        <input type="text" 
                            id="apellido_paterno"
                            name="apellido_paterno" 
                            class="form-control form-control-sm border-secondary-subtle" 
                            placeholder=""
                            value="{{ request('apellido_paterno') }}"
                            autocomplete="off">
                    </div>

                    <!-- Campo Segundo Apellido: Toma un bloque más ancho para equilibrar la rejilla visual de tu tarjeta -->
                    <div class="col-12 col-md-6 col-lg-3">
                        <label for="apellido_materno" class="form-label fw-bold text-secondary small mb-1">Segundo Apellido:</label>
                        <input type="text" 
                            id="apellido_materno"
                            name="apellido_materno" 
                            class="form-control form-control-sm border-secondary-subtle" 
                            placeholder=""
                            value="{{ request('apellido_materno') }}"
                            autocomplete="off">
                    </div>
                    
                </div>

                <!-- Bloque de control inferior para botones: Línea divisoria y alineación flexbox a la derecha -->
                <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 pt-2 border-top border-light">
                    
                    <!-- El botón Limpiar solo aparece si hay un filtro de búsqueda activo en la URL -->
                    @if(request()->filled('curp') || request()->filled('nombres') || request()->filled('apellido_paterno') || request()->filled('apellido_materno'))
                        <a href="{{ route('pacientes.index') }}" class="btn btn-sm btn-outline-danger order-2 order-sm-1 px-4 py-1.5 rounded-2 fw-medium">
                            Limpiar Filtros
                        </a>
                    @endif

                    <button type="submit" class="btn btn-sm btn-warning order-1 order-sm-2 px-4 py-1.5 rounded-2 fw-medium text-white shadow-sm" style="">
                        Buscar Expediente
                    </button>
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
                                    <span class="fs-2 d-block mb-2"><i class="bi bi-journal-arrow-up"></span>
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
