-- =============================================================================
-- ESQUEMA MAESTRO DE BASE DE DATOS: ARCHIVO CLÍNICO HOSPITALARIO (V1.0)
-- DESCRIPCIÓN: Diseñado bajo principios SOLID, normalización estricta (NOM-DGIS)
--              y optimizado para el servidor web Microsoft IIS y PostgreSQL.
-- =============================================================================

-- =============================================================================
-- APARTADO 1: EXTENSIONES Y OPTIMIZACIÓN DE RENDIMIENTO
-- OBJETIVO: Habilitar algoritmos avanzados de indexación en el motor de la BD.
-- =============================================================================

-- Sirve para activar la extensión de coincidencia difusa basada en trigramas.
-- Es indispensable para que el Buscador Predictivo (Sprint 4) jale nombres,
-- apellidos o CURPs al vuelo con aproximaciones matemáticas de texto.
CREATE EXTENSION IF NOT EXISTS pg_trgm;


-- =============================================================================
-- APARTADO 2: SEGURIDAD, ACCESOS Y ROLES DE OPERADORES
-- OBJETIVO: Controlar el acceso al software y segmentar las funciones del personal.
-- =============================================================================

-- Sirve para almacenar el catálogo semilla inalterable de los perfiles del hospital.
-- Controla de forma estricta los niveles de acceso permitidos en el sistema.
CREATE TABLE roles (
    id_rol SERIAL PRIMARY KEY,
    nombre_rol VARCHAR(50) UNIQUE NOT NULL -- Valores fijos: 'Administrador', 'Usuario', 'Gerente'
);

-- Inserción inicial obligatoria de roles core del sistema.
INSERT INTO roles (nombre_rol) VALUES ('Administrador'), ('Usuario'), ('Gerente');

-- Sirve para registrar las credenciales y el estado operativo del personal clínico.
-- La columna 'password' almacena el hash BCRYPT robusto generado desde Laravel.
CREATE TABLE usuarios (
    id_usuario SERIAL PRIMARY KEY,
    id_rol INT NOT NULL,
    nombres VARCHAR(50) NOT NULL,
    apellido_paterno VARCHAR(50) NOT NULL,
    apellido_materno VARCHAR(50), -- Permite NULL si el operador no cuenta con él
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    activo BOOLEAN DEFAULT TRUE NOT NULL, -- Bloquea accesos si el empleado es dado de baja
    fecha_creacion TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL,
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol) ON DELETE RESTRICT
);


-- =============================================================================
-- APARTADO 3: IDENTIFICACIÓN MÉDICA Y DEMOGRÁFICA (NÚCLEO DEL EXPEDIENTE)
-- OBJETIVO: Almacenar los datos de identidad oficial bajo los lineamientos DGIS.
-- =============================================================================

-- Sirve como la tabla maestra del hospital. Almacena la información demográfica
-- extraída automáticamente por el algoritmo de la CURP o capturada a mano.
-- Mantiene la trazabilidad del operador de archivo que abrió el expediente físico.
CREATE TABLE pacientes (
    id_paciente SERIAL PRIMARY KEY,
    num_expediente VARCHAR(25) UNIQUE NOT NULL, -- Generado automáticamente por trigger (Provisionales) o secuencia
    nombres VARCHAR(100) NOT NULL,
    apellido_paterno VARCHAR(100) NOT NULL,
    apellido_materno VARCHAR(100),
    curp VARCHAR(18) UNIQUE,                     -- Es NULL estrictamente si es provisional
    fecha_nacimiento DATE NOT NULL,               -- Auto-llenado desde el JavaScript de la CURP
    edad INT NOT NULL,                            -- Número calculado en pantalla (ej: 5, 24)
    clave_edad_id INT NOT NULL,                   -- Catálogo oficial DGIS: 1-Horas, 2-Meses, 3-Años
    sexo_id INT NOT NULL,                         -- Catálogo oficial numérico de salud (ej: 1)
    sexo_nombre VARCHAR(20) NOT NULL,             -- Texto plano autónomo: 'HOMBRE', 'MUJER', 'OTRO'
    entidad_nacimiento_id INT NOT NULL,           -- Clave INEGI del estado de origen
    entidad_nacimiento VARCHAR(100) NOT NULL,     -- Nombre del estado de nacimiento
    nacio_extranjero BOOLEAN DEFAULT FALSE NOT NULL, -- Checkbox que mitiga el bloqueo por falta de CURP
    es_migrante_retornado_id INT NOT NULL,        -- Catálogo binario oficial de la DGIS (1-Sí, 2-No)
    afiliacion_id INT NOT NULL,                   -- Clave del catálogo de seguros (ej: 2-IMSS, 14-IMSS BIENESTAR)
    afiliacion_nombre VARCHAR(50) NOT NULL,       -- Nombre de la institución médica de soporte
    num_afiliacion VARCHAR(30),                   -- Número de póliza o credencial de seguro social (Permite NULL)
    es_provisional BOOLEAN DEFAULT FALSE NOT NULL, -- TRUE si es recién nacido o ingreso por urgencias sin papeles
    id_usuario_registro INT NOT NULL,             -- Mapea qué usuario de la tabla 'usuarios' hizo el alta
    fecha_registro TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL,
    
    FOREIGN KEY (id_usuario_registro) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT,
    
    -- Sirve para blindar la calidad de los expedientes: si es definitivo la CURP es obligatoria,
    -- si es provisional la CURP debe viajar completamente vacía (NULL).
    CONSTRAINT chk_provisional_curp CHECK (
        (es_provisional = TRUE AND curp IS NULL) OR
        (es_provisional = FALSE AND curp IS NOT NULL)
    )
);


-- =============================================================================
-- APARTADO 4: GEOGRAFÍA OFICIAL NOM Y DIRECCIONES EN CASCADA (INEGI)
-- OBJETIVO: Almacenar la residencia legal del paciente de forma dividida e indexable.
-- =============================================================================

-- Sirve para almacenar el domicilio completo del paciente. Implementa el enfoque
-- híbrido: guarda las claves geográficas del INEGI para estadísticas de salud pública
-- y el texto descriptivo plano para garantizar la total autonomía de los reportes.
CREATE TABLE direcciones_pacientes (
    id_direccion SERIAL PRIMARY KEY,
    id_paciente INT UNIQUE NOT NULL,             
    reside_extranjero BOOLEAN DEFAULT FALSE NOT NULL, -- Deshabilita catálogos si el origen no es nacional
    pais_nombre VARCHAR(50) DEFAULT 'MÉXICO' NOT NULL,
    cve_estado VARCHAR(2) NOT NULL,              -- Clave oficial INEGI (ej: '31')
    estado VARCHAR(100) NOT NULL,                -- Texto plano (ej: 'YUCATÁN')
    cve_municipio VARCHAR(3) NOT NULL,           -- Clave oficial INEGI (ej: '059')
    municipio VARCHAR(100) NOT NULL,             -- Texto plano (ej: 'PETO')
    cve_localidad VARCHAR(4) NOT NULL,           -- Clave oficial INEGI (ej: '0001')
    localidad VARCHAR(100) NOT NULL,             -- Texto plano (ej: 'PETO')
    localidad_especifica VARCHAR(150),           -- Texto libre para rancherías o sectores sin mapear
    tipo_vialidad_id INT NOT NULL,               -- Clave de tu catálogo HTML (ej: 5)
    tipo_vialidad_nombre VARCHAR(50) NOT NULL,   -- Texto plano (ej: 'CALLE')
    nombre_vialidad VARCHAR(150) NOT NULL,       -- Nombre o número de la calle (ej: '30')
    numero_exterior VARCHAR(10) NOT NULL,
    numero_interior VARCHAR(10),                 -- Permite NULL si el domicilio no cuenta con él
    tipo_asentamiento_id INT NOT NULL,           -- Clave del catálogo de asentamientos (Colonia, Ejido, etc.)
    tipo_asentamiento_nombre VARCHAR(50) NOT NULL, -- Texto descriptivo
    nombre_asentamiento VARCHAR(150) NOT NULL,   -- Nombre de la colonia (ej: 'Centro')
    codigo_postal VARCHAR(5) NOT NULL,          
    se_ignora_cp BOOLEAN DEFAULT FALSE NOT NULL, -- Flag para omitir validación en zonas irregulares
    telefono VARCHAR(15),                        -- Teléfono de contacto o familiar responsable (Permite NULL)
    
    FOREIGN KEY (id_paciente) REFERENCES pacientes(id_paciente) ON DELETE CASCADE
);


-- =============================================================================
-- APARTADO 5: LOGÍSTICA DE ANAQUELES Y CONTROL DE EXPEDIENTES FÍSICOS
-- OBJETIVO: Administrar las coordenadas de almacenamiento en el archivo en papel.
-- =============================================================================

-- Sirve para ubicar físicamente el folder de papel dentro del archivo del hospital.
-- Sus columnas de estantería permiten valores NULL para que los ingresos de emergencia
-- registren al paciente de inmediato y los lockers se asignen después con calma.
CREATE TABLE localizacion_fisica (
    id_localizacion SERIAL PRIMARY KEY,
    id_paciente INT UNIQUE NOT NULL,
    pasillo VARCHAR(50),      -- Coordenada libre (ej: 'PASILLO A')
    estante VARCHAR(50),      -- Coordenada libre (ej: 'ESTANTE 4')
    caja_nivel VARCHAR(50),   -- Coordenada libre (ej: 'NIVEL SUPERIOR')
    estado_expediente VARCHAR(30) DEFAULT 'En Archivo' NOT NULL, -- Control core para los préstamos
    ultima_modificacion TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL,
    
    FOREIGN KEY (id_paciente) REFERENCES pacientes(id_paciente) ON DELETE CASCADE,
    
    -- Sirve para garantizar que el expediente físico solo pueda estar en uno de los dos estados operativos legales.
    CONSTRAINT chk_estado_expediente CHECK (estado_expediente IN ('En Archivo', 'Prestado'))
);


-- =============================================================================
-- APARTADO 6: HISTORIAL MÉDICO COMPLEMENTARIO (IMPRESIONES)
-- OBJETIVO: Registrar el uso y emisión de papelería física de los expedientes.
-- =============================================================================

-- Sirve para auditar qué usuario del personal imprimió carátulas o formatos médicos,
-- sustituyendo el llenado manual de fechas de consultas del Excel anterior.
CREATE TABLE historial_impresiones_consultas (
    id_impresion SERIAL PRIMARY KEY,
    id_paciente INT NOT NULL,
    id_usuario_archivo INT NOT NULL,
    tipo_formato VARCHAR(50) NOT NULL, -- Ej: 'Consulta Masculina', 'Ingreso Femenino'
    fecha_impresion TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL,
    FOREIGN KEY (id_paciente) REFERENCES pacientes(id_paciente) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario_archivo) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT
);

-- =============================================================================
-- APARTADO 7: INFRAESTRUCTURA DE AUDITORÍA AVANZADA NATIVA (SYSLOG)
-- OBJETIVO: Registrar de forma inalterable las operaciones de bases de datos.
-- =============================================================================

-- Sirve como la bitácora de seguridad del hospital. Almacena todo el rastro de inserciones,
-- cambios y eliminaciones en formato JSONB binario para auditorías legales sin tocar PHP.
CREATE TABLE syslog_auditoria (
    id_log BIGSERIAL PRIMARY KEY,
    nombre_tabla VARCHAR(100) NOT NULL,          -- Tabla afectada ('pacientes', etc.)
    operacion VARCHAR(20) NOT NULL,              -- Acción ejecutada: 'INSERT', 'UPDATE' o 'DELETE'
    id_registro_afectado INT NOT NULL,           -- Llave primaria del registro manipulado
    datos_anteriores JSONB,                      -- Copia de los datos antes del cambio
    datos_nuevos JSONB,                          -- Copia de los datos después del cambio
    usuario_db VARCHAR(100) DEFAULT CURRENT_USER, -- Cuenta de Postgres que ordenó el cambio
    fecha_evento TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL
);

-- =============================================================================
-- APARTADO 8: PROGRAMACIÓN DE DISPARADORES (TRIGGERS NATIVOS PL/PGSQL)
-- OBJETIVO: Automatizar la lógica compleja directamente en los hilos de PostgreSQL.
-- =============================================================================

-- FUNCIÓN A: Procesamiento de Auditoría Dinámica en Bloque JSONB.
CREATE OR REPLACE FUNCTION procesar_auditoria_automatica()
RETURNS TRIGGER AS $$
DECLARE
    id_afectado INT;
BEGIN
    -- Identificar la llave primaria real según la tabla que disparó el evento
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

    -- Inserción estructurada según la operación ejecutada
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

-- Vinculación inalterable de los disparadores de auditoría a las tablas core
CREATE TRIGGER trg_auditoria_pacientes AFTER INSERT OR UPDATE OR DELETE ON pacientes FOR EACH ROW EXECUTE FUNCTION procesar_auditoria_automatica();
CREATE TRIGGER trg_auditoria_direcciones AFTER INSERT OR UPDATE OR DELETE ON direcciones_pacientes FOR EACH ROW EXECUTE FUNCTION procesar_auditoria_automatica();
CREATE TRIGGER trg_auditoria_localizacion AFTER INSERT OR UPDATE OR DELETE ON localizacion_fisica FOR EACH ROW EXECUTE FUNCTION procesar_auditoria_automatica();

-- FUNCIÓN B: Generación Autónoma de Folios Provisionales.
CREATE OR REPLACE FUNCTION generar_folio_provisional()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.es_provisional = TRUE AND NEW.num_expediente = 'PROVISIONAL' THEN
        NEW.num_expediente := 'PROV-' || TO_CHAR(CURRENT_DATE, 'YYYY') || '-' || LPAD(nextval('pacientes_id_paciente_seq')::text, 5, '0');
        -- Ajuste de secuencia para mitigar saltos por el doble procesamiento de Eloquent
        PERFORM setval('pacientes_id_paciente_seq', nextval('pacientes_id_paciente_seq') - 2);
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Vinculación final del inyector de folios antes de insertar en pacientes
CREATE TRIGGER trg_antes_insertar_paciente BEFORE INSERT ON pacientes FOR EACH ROW EXECUTE FUNCTION generar_folio_provisional();

-- =============================================================================
-- APARTADO 9: ÍNDICES GIN PARA BUSCADOR PREDICTIVO (TRIGRAMS)
-- =============================================================================
CREATE INDEX idx_pacientes_expediente ON pacientes(num_expediente);
CREATE INDEX idx_pacientes_curp ON pacientes(curp);
CREATE INDEX idx_pacientes_ape_paterno ON pacientes USING gin (apellido_paterno gin_trgm_ops);
CREATE INDEX idx_pacientes_ape_materno ON pacientes USING gin (apellido_materno gin_trgm_ops);
CREATE INDEX idx_pacientes_nombres ON pacientes USING gin (nombres gin_trgm_ops);