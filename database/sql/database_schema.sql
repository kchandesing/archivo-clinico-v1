-- =============================================================================
-- SPRINT 1: SCRIPT DE BASE DE DATOS PARA ARCHIVO CLÍNICO (POSTGRESQL 14)
-- =============================================================================

-- 1. HABILITAR EXTENSIÓN PARA BÚSQUEDAS ALFABÉTICAS ULTRA RÁPIDAS
-- (Requerimiento para el Buscador Predictivo del Sprint 4)
CREATE EXTENSION IF NOT EXISTS pg_trgm;

-- 2. TABLA DE ROLES Y PRIVILEGIOS
CREATE TABLE roles (
    id_rol SERIAL PRIMARY KEY,
    nombre_rol VARCHAR(50) UNIQUE NOT NULL -- 'Administrador', 'Usuario', 'Gerente'
);

-- Inserción inicial de los roles requeridos
INSERT INTO roles (nombre_rol) VALUES ('Administrador'), ('Usuario'), ('Gerente');

-- 3. TABLA DE USUARIOS DEL SISTEMA (Para el Módulo de Configuración y Accesos)
CREATE TABLE usuarios (
    id_usuario SERIAL PRIMARY KEY,
    id_rol INT NOT NULL,
    nombres VARCHAR(50) NOT NULL,              -- Nombre(s) separados
    apellido_paterno VARCHAR(50) NOT NULL,     -- Apellido Paterno
    apellido_materno VARCHAR(50),              -- Apellido Materno (Permite NULL)
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL, -- Almacenará el hash BCRYPT de Laravel
    activo BOOLEAN DEFAULT TRUE NOT NULL,
    fecha_creacion TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol) ON DELETE RESTRICT
);

-- 4. TABLA MAESTRA DE PACIENTES (Campos del Excel con Nombre Dividido)
CREATE TABLE pacientes (
    id_paciente SERIAL PRIMARY KEY,
    num_expediente VARCHAR(25) UNIQUE NOT NULL, -- Número físico o Folio Provisional
    seguro_social VARCHAR(20) UNIQUE,           -- Campo "Seguro soc" del Excel
    nombres VARCHAR(100) NOT NULL,              -- Nombre(s) separados
    apellido_paterno VARCHAR(100) NOT NULL,     -- Apellido Paterno
    apellido_materno VARCHAR(100),              -- Apellido Materno (Permite NULL)
    curp VARCHAR(18) UNIQUE,                     -- Campo "CURP" del Excel
    es_provisional BOOLEAN DEFAULT FALSE NOT NULL, -- TRUE si es recién nacido sin CURP
    fecha_registro TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    
    -- REGLA DE CALIDAD SANITARIA: Si es provisional no lleva CURP, si no es provisional el CURP es obligatorio
    CONSTRAINT chk_provisional_curp CHECK (
        (es_provisional = TRUE AND curp IS NULL) OR
        (es_provisional = FALSE AND curp IS NOT NULL)
    )
);

-- 5. TABLA DE DIRECCIONES (Datos Geográficos del Excel)
CREATE TABLE direcciones_pacientes (
    id_direccion SERIAL PRIMARY KEY,
    id_paciente INT UNIQUE NOT NULL,             
    direccion_calle VARCHAR(255) NOT NULL,       -- Campo "DIRECCION"
    colonia_localidad VARCHAR(150) NOT NULL,    -- Campo "LOC. Ó COL."
    municipio VARCHAR(100) NOT NULL,            -- Campo "MUNICIPIO"
    codigo_postal VARCHAR(5) NOT NULL,          -- Código Postal
    estado VARCHAR(100) NOT NULL,               -- Campo "ESTADO"
    FOREIGN KEY (id_paciente) REFERENCES pacientes(id_paciente) ON DELETE CASCADE
);

-- 6. TABLA DE LOCALIZACIÓN FÍSICA EN EL HOSPITAL
CREATE TABLE localizacion_fisica (
    id_localizacion SERIAL PRIMARY KEY,
    id_paciente INT UNIQUE NOT NULL,
    pasillo VARCHAR(30) NOT NULL,
    estante VARCHAR(30) NOT NULL,
    caja_nivel VARCHAR(30) NOT NULL,
    estado_expediente VARCHAR(30) DEFAULT 'En Archivo' NOT NULL, -- 'En Archivo', 'Prestado'
    ultima_modificacion TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_paciente) REFERENCES pacientes(id_paciente) ON DELETE CASCADE,
    CONSTRAINT chk_estado_expediente CHECK (estado_expediente IN ('En Archivo', 'Prestado'))
);

-- 7. TABLA DE HISTORIAL DE CONSULTAS IMPRESAS
-- (Reemplaza el llenado manual de "FECHA DE ULTIMA CONSU" del Excel)
CREATE TABLE historial_impresiones_consultas (
    id_impresion SERIAL PRIMARY KEY,
    id_paciente INT NOT NULL,
    id_usuario_archivo INT NOT NULL,           -- Qué usuario del personal imprimió el formato
    tipo_formato VARCHAR(50) NOT NULL,         -- 'Consulta Masc', 'Ingreso Fem', 'Carátula', etc.
    fecha_impresion TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL,
    FOREIGN KEY (id_paciente) REFERENCES pacientes(id_paciente) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario_archivo) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT
);

-- 8. TABLA GLOBAL DE AUDITORÍA AVANZADA (Syslog Automatizado)
CREATE TABLE syslog_auditoria (
    id_log BIGSERIAL PRIMARY KEY,
    nombre_tabla VARCHAR(100) NOT NULL,          
    operacion VARCHAR(20) NOT NULL,              -- 'INSERT', 'UPDATE' o 'DELETE'
    id_registro_afectado INT NOT NULL,           
    datos_anteriores JSONB,                      -- Almacena el estado viejo antes del cambio
    datos_nuevos JSONB,                          -- Almacena el estado nuevo después del cambio
    usuario_db VARCHAR(100) DEFAULT CURRENT_USER,
    fecha_evento TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL
);

-- =============================================================================
-- PROGRAMACIÓN DE TRIGGERS NATIVOS (PROCESAMIENTO INTERNO EN POSTGRESQL 14)
-- =============================================================================

-- TRIGGER A: AUDITORÍA AUTOMÁTICA EN FORMATO JSONB (Cero código en PHP)
CREATE OR REPLACE FUNCTION procesar_auditoria_automatica()
RETURNS TRIGGER AS $$
DECLARE
    id_afectado INT;
BEGIN
    -- Detectar dinámicamente la llave primaria según la tabla afectada
    IF (TG_OP = 'DELETE') THEN
        CASE TG_TABLE_NAME
            WHEN 'pacientes' THEN id_afectado := OLD.id_paciente;
            WHEN 'direcciones_pacientes' THEN id_afectado := OLD.id_direccion;
            WHEN 'localizacion_fisica' THEN id_afectado := OLD.id_localizacion;
            ELSE id_afectado := 0;
        END CASE;
    ELSE
        CASE TG_TABLE_NAME
            WHEN 'pacientes' THEN id_afectado := NEW.id_paciente;
            WHEN 'direcciones_pacientes' THEN id_afectado := NEW.id_direccion;
            WHEN 'localizacion_fisica' THEN id_afectado := NEW.id_localizacion;
            ELSE id_afectado := 0;
        END CASE;
    END IF;

    -- Inserción limpia en el Syslog en formato JSONB
    IF (TG_OP = 'INSERT') THEN
        INSERT INTO syslog_auditoria (nombre_tabla, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
        VALUES (TG_TABLE_NAME, TG_OP, id_afectado, NULL, to_jsonb(NEW));
        RETURN NEW;
    ELSIF (TG_OP = 'UPDATE') THEN
        INSERT INTO syslog_auditoria (nombre_tabla, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
        VALUES (TG_TABLE_NAME, TG_OP, id_afectado, to_jsonb(OLD), to_jsonb(NEW));
        RETURN NEW;
    ELSIF (TG_OP = 'DELETE') THEN
        INSERT INTO syslog_auditoria (nombre_tabla, operacion, id_registro_afectado, datos_anteriores, datos_nuevos)
        VALUES (TG_TABLE_NAME, TG_OP, id_afectado, to_jsonb(OLD), NULL);
        RETURN OLD;
    END IF;
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

-- Vinculación de auditoría inalterable a las tablas core
CREATE TRIGGER trg_auditoria_pacientes
AFTER INSERT OR UPDATE OR DELETE ON pacientes
FOR EACH ROW EXECUTE FUNCTION procesar_auditoria_automatica();

CREATE TRIGGER trg_auditoria_direcciones
AFTER INSERT OR UPDATE OR DELETE ON direcciones_pacientes
FOR EACH ROW EXECUTE FUNCTION procesar_auditoria_automatica();

CREATE TRIGGER trg_auditoria_localizacion
AFTER INSERT OR UPDATE OR DELETE ON localizacion_fisica
FOR EACH ROW EXECUTE FUNCTION procesar_auditoria_automatica();


-- TRIGGER B: GENERACIÓN AUTOMÁTICA DE EXPEDIENTES PROVISIONALES ANTE FALTA DE CURP
CREATE OR REPLACE FUNCTION generar_folio_provisional()
RETURNS TRIGGER AS $$
BEGIN
    -- CORRECCIÓN: Estructura condicional IF válida que evalúa el flag e intercepta el marcador de la web
    IF NEW.es_provisional = TRUE AND NEW.num_expediente = 'PROVISIONAL' THEN
        NEW.num_expediente := 'PROV-' || TO_CHAR(CURRENT_DATE, 'YYYY') || '-' || LPAD(nextval('pacientes_id_paciente_seq')::text, 5, '0');
        -- Ajustamos la secuencia para evitar saltos en la inserción final de Laravel
        PERFORM setval('pacientes_id_paciente_seq', nextval('pacientes_id_paciente_seq') - 2);
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Vinculación final del Trigger de folios provisionales antes del INSERT
CREATE TRIGGER trg_antes_insertar_paciente
BEFORE INSERT ON pacientes
FOR EACH ROW
EXECUTE FUNCTION generar_folio_provisional();

-- =============================================================================
-- CREACIÓN DE ÍNDICES DE RENDIMIENTO (Para el Buscador Predictivo del Sprint 4)
-- =============================================================================
CREATE INDEX idx_pacientes_expediente ON pacientes(num_expediente);
CREATE INDEX idx_pacientes_curp ON pacientes(curp);
CREATE INDEX idx_pacientes_ape_paterno ON pacientes USING gin (apellido_paterno gin_trgm_ops);
CREATE INDEX idx_pacientes_ape_materno ON pacientes USING gin (apellido_materno gin_trgm_ops);
CREATE INDEX idx_pacientes_nombres ON pacientes USING gin (nombres gin_trgm_ops);
