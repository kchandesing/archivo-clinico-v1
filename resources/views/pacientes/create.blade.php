@extends('layouts.dashboard')

@section('title', 'Registrar Paciente - Archivo Clínico')

@section('dashboard_content')
<div class="row mb-4">
    <div class="col-12">
        <h4 class="fw-bold text-dark m-0">Apertura de Expediente Nuevo</h4>
        <p class="text-muted small m-0">Introduce los datos demográficos y de archivo para dar de alta al paciente</p>
    </div>
</div>

@if($errors->has('error'))
    <div class="alert alert-danger shadow-sm p-3 small mb-4" role="alert">
        <strong>Error de Infraestructura:</strong> {{ $errors->first('error') }}
    </div>
@endif

<div class="row">
    <div class="col-12 col-xl-9">
        <div class="card border-0 shadow-sm p-4 bg-white rounded-3">
            
            <form action="{{ route('pacientes.store') }}" method="POST" id="pacienteForm">
                @csrf

                <!-- SECCIÓN 1: IDENTIFICACIÓN -->
                <div class="fw-bold mb-3 border-bottom pb-2 small text-uppercase" style="color: #714B67;">
                    1. Identificación del Paciente
                </div>

                <div class="mb-4 p-3 bg-light rounded-2 border">
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" role="switch" id="es_provisional" name="es_provisional" value="1" {{ old('es_provisional') ? 'checked' : '' }}>
                        <label class="form-check-label fw-medium text-dark small" for="es_provisional">
                            ¿Es un expediente provisional? (Sin CURP / Recién Nacido / Emergencia)
                        </label>
                    </div>
                    <div class="form-text text-muted m-0" style="font-size: 0.72rem;">
                        Al activarse, el sistema omitirá el CURP y le asignará un folio automático con prefijo PROV en la base de datos.
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12" id="curpContainer">
                        <label for="curp" class="form-label text-secondary small fw-medium">Clave Única de Registro de Población (CURP)</label>
                        <input type="text" class="form-control form-control-sm text-uppercase @error('curp') is-invalid @enderror" id="curp" name="curp" value="{{ old('curp') }}" placeholder="18 caracteres alfanuméricos">
                        @error('curp') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="nombres" class="form-label text-secondary small fw-medium">Nombre(s)</label>
                        <input type="text" class="form-control form-control-sm @error('nombres') is-invalid @enderror" id="nombres" name="nombres" value="{{ old('nombres') }}" required max="50">
                        @error('nombres') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-sm-6 col-md-4">
                        <label for="apellido_paterno" class="form-label text-secondary small fw-medium">Apellido Paterno</label>
                        <input type="text" class="form-control form-control-sm @error('apellido_paterno') is-invalid @enderror" id="apellido_paterno" name="apellido_paterno" value="{{ old('apellido_paterno') }}" required max="50">
                        @error('apellido_paterno') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-sm-6 col-md-4">
                        <label for="apellido_materno" class="form-label text-secondary small fw-medium">Apellido Materno (Opcional)</label>
                        <input type="text" class="form-control form-control-sm @error('apellido_materno') is-invalid @enderror" id="apellido_materno" name="apellido_materno" value="{{ old('apellido_materno') }}" max="50">
                        @error('apellido_materno') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-sm-6">
                        <label for="fecha_nacimiento" class="form-label text-secondary small fw-medium">Fecha de Nacimiento</label>
                        <input type="date" class="form-control form-control-sm @error('fecha_nacimiento') is-invalid @enderror" id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" required>
                        @error('fecha_nacimiento') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-sm-6">
                        <label for="sexo" class="form-label text-secondary small fw-medium">Sexo Biológico</label>
                        <select class="form-select form-select-sm @error('sexo') is-invalid @enderror" id="sexo" name="sexo" required>
                            <option value="" disabled selected>Selecciona una opción</option>
                            <option value="H" {{ old('sexo') == 'H' ? 'selected' : '' }}>Hombre</option>
                            <option value="M" {{ old('sexo') == 'M' ? 'selected' : '' }}>Mujer</option>
                            <option value="O" {{ old('sexo') == 'O' ? 'selected' : '' }}>Otro</option>
                        </select>
                        @error('sexo') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- SECCIÓN 2: DOMICILIO -->
                <div class="fw-bold mt-4 mb-3 border-bottom pb-2 small text-uppercase" style="color: #714B67;">
                    2. Información Domiciliaria
                </div>

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="calle" class="form-label text-secondary small fw-medium">Calle / Avenida</label>
                        <input type="text" class="form-control form-control-sm @error('calle') is-invalid @enderror" id="calle" name="calle" value="{{ old('calle') }}" required max="100">
                        @error('calle') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-6 col-md-3">
                        <label for="numero_exterior" class="form-label text-secondary small fw-medium">No. Exterior</label>
                        <input type="text" class="form-control form-control-sm @error('numero_exterior') is-invalid @enderror" id="numero_exterior" name="numero_exterior" value="{{ old('numero_exterior') }}" required max="10">
                        @error('numero_exterior') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-6 col-md-3">
                        <label for="numero_interior" class="form-label text-secondary small fw-medium">No. Interior</label>
                        <input type="text" class="form-control form-control-sm" id="numero_interior" name="numero_interior" value="{{ old('numero_interior') }}" placeholder="Opcional" max="10">
                    </div>

                    <div class="col-12 col-sm-8 col-md-6">
                        <label for="colonia" class="form-label text-secondary small fw-medium">Colonia / Fraccionamiento</label>
                        <input type="text" class="form-control form-control-sm @error('colonia') is-invalid @enderror" id="colonia" name="colonia" value="{{ old('colonia') }}" required max="100">
                        @error('colonia') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-sm-4 col-md-6">
                        <label for="codigo_postal" class="form-label text-secondary small fw-medium">Código Postal</label>
                        <input type="text" class="form-control form-control-sm @error('codigo_postal') is-invalid @enderror" id="codigo_postal" name="codigo_postal" value="{{ old('codigo_postal') }}" placeholder="5 dígitos" required max="5">
                        @error('codigo_postal') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="localidad" class="form-label text-secondary small fw-medium">Localidad / Población</label>
                        <input type="text" class="form-control form-control-sm @error('localidad') is-invalid @enderror" id="localidad" name="localidad" value="{{ old('localidad', 'Peto') }}" required max="100">
                        @error('localidad') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-sm-6 col-md-4">
                        <label for="municipio" class="form-label text-secondary small fw-medium">Municipio</label>
                        <input type="text" class="form-control form-control-sm @error('municipio') is-invalid @enderror" id="municipio" name="municipio" value="{{ old('municipio', 'Peto') }}" required max="100">
                        @error('municipio') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-sm-6 col-md-4">
                        <label for="estado" class="form-label text-secondary small fw-medium">Estado</label>
                        <input type="text" class="form-control form-control-sm @error('estado') is-invalid @enderror" id="estado" name="estado" value="{{ old('estado', 'Yucatán') }}" required max="50">
                        @error('estado') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                    </div>
                </div>
                <!-- SECCIÓN 3: UBICACIÓN FÍSICA EN EL ARCHIVO (OPCIONAL/TEXTO LIBRE) -->
                <div class="text-brand fw-bold mt-4 mb-3 border-bottom pb-2 small text-uppercase" style="color: #714B67;">
                    3. Localización Física del Expediente en Papel
                </div>
                <div class="row g-3">
                    <div class="col-12 col-sm-4">
                        <label for="pasillo" class="form-label text-secondary small fw-medium">Pasillo / Pasaje</label>
                        <input type="text" class="form-control form-control-sm" id="pasillo" name="pasillo" value="{{ old('pasillo') }}" placeholder="ej: Pasillo A" max="50">
                    </div>
                    <div class="col-12 col-sm-4">
                        <label for="estante" class="form-label text-secondary small fw-medium">Estante / Anaquel</label>
                        <input type="text" class="form-control form-control-sm" id="estante" name="estante" value="{{ old('estante') }}" placeholder="ej: Locker 4" max="50">
                    </div>
                    <div class="col-12 col-sm-4">
                        <label for="caja" class="form-label text-secondary small fw-medium">Caja / Nivel</label>
                        <input type="text" class="form-control form-control-sm" id="caja" name="caja" value="{{ old('caja') }}" placeholder="ej: Nivel 2" max="50">
                    </div>
                </div>

                <!-- BOTONES DE ACCIÓN COMPLETAMENTE RESPONSIVOS -->
                <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('pacientes.index') }}" class="btn btn-sm btn-outline-secondary order-2 order-sm-1 px-4 py-2 rounded-2 fw-medium">
                        Cancelar y Volver
                    </a>
                    <button type="submit" class="btn btn-sm order-1 order-sm-2 px-4 py-2 rounded-2 fw-medium" style="background-color: #714B67; color: white;" id="saveBtn">
                        Archivar y Generar Expediente
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- CORRECCIÓN: Agregar la etiqueta de apertura de script -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const esProvisionalCheckbox = document.querySelector('#es_provisional');
        const curpContainer = document.querySelector('#curpContainer');
        const curpInput = document.querySelector('#curp');
        const pacienteForm = document.querySelector('#pacienteForm');
        const saveBtn = document.querySelector('#saveBtn');

        function toggleCurpField() {
            if (esProvisionalCheckbox.checked) {
                curpContainer.style.display = 'none';
                curpInput.disabled = true;
                curpInput.value = '';
            } else {
                curpContainer.style.display = 'block';
                curpInput.disabled = false;
            }
        }

        toggleCurpField();
        esProvisionalCheckbox.addEventListener('change', toggleCurpField);

        pacienteForm.addEventListener('submit', function () {
            if (pacienteForm.checkValidity()) {
                saveBtn.disabled = true;
                saveBtn.innerHTML = 'Guardando expediente clínico...';
            }
        });
    });
</script>
<!-- CORRECCIÓN: Agregar la etiqueta de cierre de script -->
@endpush
