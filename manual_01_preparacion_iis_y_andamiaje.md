# Manual Técnico — Sistema de Archivo Clínico
## Parte 1: Preparación de IIS (local) y Andamiaje Inicial del Proyecto

**Punto de partida asumido:**
- Windows con IIS instalable (local, para desarrollo).
- PHP ya descargado (pendiente de configurar dentro de IIS).
- Composer, Git y cuenta de GitHub ya instalados/creados.
- Carpeta del proyecto: **aún no existe**.

**Objetivo de este documento:** dejar IIS listo para servir PHP/Laravel, y crear la carpeta del proyecto con el andamiaje base (Laravel + Livewire) versionado en Git, listo para subir a GitHub.

---

## SECCIÓN A — Preparar IIS para PHP y Laravel

### A.1 ¿Por qué IIS necesita configuración extra para PHP?

IIS, a diferencia de Apache, no interpreta PHP de forma nativa. Necesita:
1. Un **manejador FastCGI** que le diga "cuando pidan un `.php`, pásaselo al ejecutable de PHP".
2. El **módulo URL Rewrite**, para que las rutas "amigables" de Laravel (`/pacientes/5`, `/login`, etc.) se redirijan todas hacia `index.php`, que es quien realmente decide qué mostrar.

Sin estos dos elementos, IIS solo podrá servir archivos estáticos (HTML, CSS, imágenes) y Laravel no funcionará.

### A.2 Habilitar el rol de IIS y sus características necesarias

1. Abre **Panel de Control → Programas → Activar o desactivar las características de Windows**.
2. Activa (si no están ya):
   - `Internet Information Services`
     - `Herramientas de administración web` → `Consola de administración de IIS`
     - `Servicios World Wide Web`
       - `Características de desarrollo de aplicaciones` → marca **CGI** (esto habilita el soporte FastCGI que usará PHP)
       - `Características HTTP comunes` → Documento predeterminado, Examen de directorios (opcional), Errores HTTP
3. Acepta y espera a que Windows instale los componentes. Puede pedir reinicio.

**Por qué marcar CGI específicamente:** IIS ejecuta PHP como un proceso externo (`php-cgi.exe`) usando el protocolo FastCGI. Si no activas la característica CGI del rol de IIS, la opción de registrar un manejador FastCGI ni siquiera aparece en el panel de administración.

### A.3 Verificar que PHP esté listo para FastCGI

1. Ubica la carpeta donde descomprimiste PHP (ejemplo: `C:\PHP\php-8.3.x`).
2. Dentro de esa carpeta debe existir `php-cgi.exe`. Si solo tienes `php.exe`, verifica que descargaste el paquete **"Non Thread Safe" (NTS)** de PHP para Windows — es el que IIS/FastCGI requiere. La versión "Thread Safe" es para Apache con `mod_php`.
3. Copia (o renombra) `php.ini-production` a `php.ini` dentro de esa misma carpeta, si aún no existe un `php.ini`.

### A.4 Activar las extensiones PHP que Laravel + PostgreSQL necesitan

Abre `php.ini` con un editor de texto y **descomenta** (quita el `;` inicial) estas líneas. Si alguna no aparece, agrégala al final de la sección de extensiones:

```ini
extension=curl
extension=fileinfo
extension=mbstring
extension=openssl
extension=pdo_pgsql
extension=pgsql
extension=tokenizer
extension=xml
extension=zip
extension=intl
```

**Por qué cada una importa para este proyecto en concreto:**
- `pdo_pgsql` y `pgsql` — sin estas dos, Laravel no puede conectarse a tu PostgreSQL 14. Es el extension más importante y el más fácil de olvidar.
- `mbstring`, `tokenizer`, `xml`, `ctype` (viene integrada en PHP 8+) — requisitos duros del núcleo de Laravel 12; sin ellos, `composer install` fallará con errores de "extensión faltante".
- `openssl` — usado para el cifrado de tu `APP_KEY` y el `Crypt::encrypt()` que ya usas en `InstallController`.
- `fileinfo` — lo usan Livewire (subida de archivos) y validaciones de tipo MIME.

También ajusta estas dos líneas (valores razonables para desarrollo, puedes subirlos si trabajas con archivos grandes como tu Excel de 6 MB):

```ini
memory_limit = 256M
upload_max_filesize = 20M
post_max_size = 20M
```

### A.5 Registrar PHP como manejador FastCGI en IIS

1. Abre **Administrador de Internet Information Services (IIS)**.
2. En el panel izquierdo, selecciona el **nombre del servidor** (el nivel raíz, no un sitio específico).
3. Doble clic en **"Configuración de FastCGI"**.
4. Panel derecho → **Agregar aplicación**.
   - **Ruta de acceso completa del ejecutable:** la ruta a `php-cgi.exe` (ej. `C:\PHP\php-8.3.x\php-cgi.exe`).
   - Deja lo demás por defecto y acepta.
5. Ahora ve a **"Asignaciones de controladores"** (Handler Mappings), a nivel de servidor o del sitio.
   - **Agregar asignación de módulo…**
   - **Ruta de solicitud:** `*.php`
   - **Módulo:** `FastCgiModule`
   - **Ejecutable:** la misma ruta a `php-cgi.exe`
   - **Nombre:** `PHP-FastCGI`
   - Acepta y confirma "Permitir" cuando pregunte por permisos de ejecución.

> **Atajo recomendado:** en vez de los pasos A.5 manuales, puedes instalar la extensión gratuita **"PHP Manager for IIS"** de Microsoft, que hace este registro con un asistente visual. Si prefieres esa ruta dime y documentamos esa variante en su lugar.

### A.6 Instalar el módulo URL Rewrite de IIS

1. Descarga **"URL Rewrite Module"** de la página oficial de IIS (`iis.net` → Downloads → URL Rewrite).
2. Instálalo (requiere cerrar y volver a abrir el Administrador de IIS después).
3. Esto habilita que tu archivo `public/web.config` (que ya tienes en el repo) funcione — es el que traduce URLs amigables a `index.php`, tal como ya está en el snapshot que compartiste.

### A.7 Crear el Sitio en IIS apuntando a `public/`

Esto lo harás **después** de tener la carpeta del proyecto creada (Sección B), porque necesitas que exista la subcarpeta `public/`. Los pasos, para cuando llegues ahí, serán:

1. **Sitios → Agregar sitio web**.
2. **Nombre del sitio:** ej. `archivo-clinico-local`.
3. **Ruta de acceso física:** apunta a `...\tu-proyecto\public` (⚠️ la carpeta `public`, **no** la raíz del proyecto — esto es crítico: si apuntas a la raíz, expones tu `.env` con las credenciales de base de datos a cualquiera que lo pida por URL).
4. **Puerto:** 80 (o el que prefieras si ya tienes otro sitio usando el 80), y sin HTTPS por ahora, consistente con tu configuración de servidor de producción.
5. Confirma y prueba visitando `http://localhost` (o el puerto que hayas elegido).

### A.8 Verificación rápida antes de continuar

Antes de escribir una sola línea de Laravel, confirma que PHP básico funciona en IIS:

1. Crea temporalmente un archivo `info.php` con este contenido dentro de cualquier carpeta servida por IIS:
   ```php
   <?php phpinfo();
   ```
2. Visítalo desde el navegador. Si ves la tabla de información de PHP, FastCGI está funcionando.
3. Busca en esa página la sección de `pdo_pgsql` — confirma que aparece como habilitada.
4. **Borra `info.php`** al terminar la prueba (nunca lo dejes en un entorno accesible, ni siquiera local — es una mala práctica de seguridad que se te puede olvidar quitar antes de subir a producción).

---

## SECCIÓN B — Crear la carpeta del proyecto y el andamiaje inicial (Composer + Git)

### B.1 Crear la carpeta del proyecto

Abre tu terminal (PowerShell o Git Bash) en la ubicación donde vivirá el proyecto y ejecuta:

```powershell
mkdir archivo-clinico
cd archivo-clinico
```

**Nota:** por ahora la carpeta está vacía. No hagas `git init` todavía — Composer va a crear el proyecto completo primero, y es más limpio inicializar Git *después*, cuando ya exista el `.gitignore` que trae Laravel por defecto (así evitas trackear accidentalmente carpetas como `vendor/` o `node_modules/` antes de que exista el `.gitignore`).

### B.2 Generar el proyecto Laravel con Composer

Como ya estás **dentro** de la carpeta `archivo-clinico` (vacía), usa el `.` para instalar aquí mismo en vez de crear una subcarpeta:

```powershell
composer create-project laravel/laravel . "12.*"
```

**Qué hace este comando:**
- Descarga el esqueleto oficial de Laravel en la versión 12.x (la que ya usas según tu `composer.json`).
- Instala automáticamente todas las dependencias de `vendor/`.
- Genera el `.env` a partir de `.env.example` y ejecuta `php artisan key:generate` automáticamente (esto crea tu `APP_KEY`).
- Trae ya un `.gitignore` correcto para proyectos Laravel (ignora `vendor/`, `node_modules/`, `.env`, `storage/*.key`, etc.).

Al terminar, confirma que ves la estructura típica: `app/`, `bootstrap/`, `config/`, `database/`, `public/`, etc.

### B.3 Configurar `.env` para PostgreSQL 14 (mínimo, sin datos reales)

Edita el `.env` recién creado y cambia el bloque de base de datos:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=archivo_clinico
DB_USERNAME=postgres
DB_PASSWORD=tu_password_local
```

(Esto es temporal para desarrollo local; más adelante retomamos si seguimos usando tu wizard de instalación cifrado o simplificamos ese flujo — lo definimos en un paso posterior.)

### B.4 Instalar Livewire v4

```powershell
composer require livewire/livewire:^4.4
```

Esto agrega Livewire a tu `composer.json` y `composer.lock`, igual que en tu proyecto original.

### B.5 Inicializar Git

Ahora sí, con el `.gitignore` de Laravel ya en su lugar:

```powershell
git init
git add .
git commit -m "Andamiaje inicial: Laravel 12 + Livewire 4"
```

**Verificación importante antes de continuar:** confirma que `.env` **no** aparece en el commit:

```powershell
git status
```

Si por alguna razón `.env` apareciera como archivo nuevo a trackear, **detente** — significa que el `.gitignore` no se copió bien, y hay que corregirlo antes de seguir (nunca se debe commitear ese archivo, contiene o contendrá tus credenciales).

### B.6 Conectar con GitHub

1. Crea el repositorio vacío en GitHub (sin README, sin `.gitignore`, sin licencia — para que no choque con lo que ya tienes local).
2. Conecta tu repo local con el remoto:

```powershell
git remote add origin https://github.com/kchandesing/archivo-clinico-v1.git
git branch -M main
git push -u origin main
```

### B.7 Verificación final de este capítulo

Antes de avanzar al siguiente paso (migraciones), confirma que:

- [ ] `http://localhost` (o el puerto configurado en IIS apuntando a `public/`) muestra la página de bienvenida de Laravel.
- [ ] `php artisan --version` responde correctamente desde la terminal, dentro de la carpeta del proyecto.
- [ ] El repo ya está visible en GitHub con tu primer commit.
- [ ] `.env` **no** está en el historial de Git (revisado en B.5).

---

## Qué sigue

Una vez confirmes que estos puntos funcionan en tu máquina, retomamos el **Paso 2: migraciones** (empezando por `roles` y `usuarios`), ya con la carpeta real del proyecto lista para recibir esos archivos.

El Flujo Lógico y de Ciclo de Vida (El "Cómo funciona")Cuando un usuario monta el proyecto en IIS por primera vez y entra a http://localhost, el flujo debe seguir este orden estricto de validación para evitar errores de infraestructura:[ Petición Web ] ──> ¿Existe el archivo 'storage/installed.lock'?
                          │
                          ├──> SÍ: (Flujo normal) El sistema funciona, va al Login/Dashboard.
                          │
                          └──> NO: (Flujo de Instalación)
                                    │
                                    ├──> ¿La ruta es '/install' o sub-rutas?
                                    │       ├──> SÍ: Permite el paso al formulario o procesamiento.
                                    │       └──> NO: Redirige automáticamente a '/install'.

Arquitectura de Logs y Buenas Prácticas (La Auditoría del Proceso)Siguiendo las mejores prácticas de ingeniería de software, la instalación no puede ocurrir "a ciegas". Necesitamos registrar un rastro detallado en los archivos de log de Laravel (storage/logs/laravel.log). Cada paso del aprovisionamiento debe reportar tres niveles de logs:

Log::info: Para transiciones exitosas (ej. "Conexión exitosa a PostgreSQL", "Base de datos 'archivo_clinico' creada").

Log::warning: Para situaciones anómalas pero controlables (ej. "Intento fallido de instalación con Master Key incorrecta desde la IP: X.X.X.X").

Log::error: Para fallos catastróficos que detengan el flujo (ej. "Fallo al ejecutar las migraciones: la tabla X ya existía" o "Permisos denegados en PostgreSQL").

Componentes de Software Requeridos (La Estructura SOLID)

Para llevar esta lógica al código de forma desacoplada y limpia, dividiremos el instalador en 5 piezas clave que interactúan entre sí:

El Guardián (CheckIfInstalled Middleware): Su única responsabilidad es interceptar las rutas y verificar si el archivo de bloqueo existe. Si no existe, bloquea el resto del software y encapsula al usuario en el entorno de instalación.

El Validador (InstallRequest): Un Form Request que se asegura de que ningún dato llegue vacío o mal formateado (ej. que la contraseña cumpla con longitud mínima, que el correo sea válido y que los apellidos no contengan caracteres extraños).

El Orquestador (InstallController): Un controlador extremadamente delgado. Solo recibe los datos validados del formulario, se los pasa al servicio, captura las excepciones si algo sale mal y decide a qué vista redirigir.

El Motor de Infraestructura (InstallationService): Aquí vive la lógica pesada. Este servicio se conecta temporalmente al servidor de base de datos usando las credenciales maestras del sistema, crea físicamente la nueva base de datos, reescribe el archivo .env en caliente, purga la caché de Laravel para que reconozca los nuevos datos, corre las migraciones, inserta al administrador y genera el archivo de bloqueo.

La Interfaz (install/form.blade.php): Una vista limpia basada en componentes (similar al formulario de Odoo de tu imagen) con validaciones visuales en tiempo real para el usuario.


# Módulo de Instalación y Aprovisionamiento Seguro (Wizard)
## Sistema de Archivo Clínico v1 (Arquitectura SOLID & Clean Code)

Este documento detalla la arquitectura, el flujo lógico y los componentes del asistente de instalación automatizado. Este módulo se diseñó bajo los principios SOLID con el objetivo de desacoplar la infraestructura, asegurar las credenciales de la base de datos y registrar de forma auditable cada paso en el sistema de logs.

---

## 1. Arquitectura y Flujo Lógico General

El propósito del instalador es inicializar el entorno cuando el software se monta por primera vez (por ejemplo, en un servidor IIS). Evita que la aplicación falle por falta de conectividad interceptando al usuario y encapsulándolo en un flujo seguro.

### Diagrama del Ciclo de Vida de una Petición
1. **Petición Web (`GET /`)** -> Entra al Middleware Global `CheckIfInstalled`.
2. **Evaluación de Estado** -> El sistema busca físicamente el archivo testigo `storage/installed.lock`.
   - **Caso A (Instalado):** El archivo existe. La petición sigue su curso normal hacia el Login o Dashboard.
   - **Caso B (No Instalado):** El archivo no existe. El middleware bloquea el software y redirige de forma obligatoria a `/install`.
3. **Formulario Web** -> El usuario ingresa las credenciales del servidor, los datos de la nueva BD y los datos del Administrador.
4. **Procesamiento Seguro** -> El controlador delega la carga al Servicio de Infraestructura, el cual inyecta el esquema `.sql`, cifra las credenciales en el `.env` y genera el archivo de bloqueo para cerrar el ciclo de instalación.

---

## 2. Desglose de Componentes y Conexiones

### A. Capa de Negocio e Infraestructura: `InstallationService.php`
* **Ubicación:** `app/Services/InstallationService.php`
* **Para qué sirve:** Es el motor pesado del módulo (Service Layer). Aplica el principio de Responsabilidad Única (SRP) al remover la lógica de base de datos fuera del controlador.
* **Conexiones e Interacciones:**
  - **PostgreSQL (Conexión Maestra):** Se conecta inicialmente a la base de datos global nativa `postgres` utilizando un driver `PDO` puro para validar si el nombre de la BD clínica ya existe. Si no existe, ejecuta el comando nativo `CREATE DATABASE`.
  - **PostgreSQL (Conexión Clínica):** Abre una segunda conexión `PDO` apuntando a la nueva base de datos y ejecuta la función `injectSqlSchema()`. Esta lee y ejecuta en bloque el script físico localizado en `database/sql/database_schema.sql` (creando tablas, índices trigram e inyectando los triggers JSONB de auditoría nativos).
  - **Cifrado de Datos (`Crypt`):** Recibe las credenciales de conexión en texto plano, las empaqueta en un array y genera una cadena altamente segura cifrada con el algoritmo AES-256 utilizando la llave maestra `APP_KEY` del proyecto.
  - **Manipulación de Entorno (`.env`):** Escribe físicamente en el archivo `.env` del servidor las variables `APP_INSTALLED=true` y `DB_ENCRYPTED_DATA="...cadena_cifrada..."`. Cambia además `DB_CONNECTION=pgsql` para asegurar que el sistema sepa qué driver usar, y posteriormente purga la caché con `Artisan::call('config:clear')`.
  - **Eloquent (`Models`):** Utiliza los modelos `Role` y `User` reconfigurados para insertar los primeros registros semilla (Administrador, Usuario, Gerente) y dar de alta al primer usuario utilizando la fachada `Hash::make()` para la contraseña.

### B. Capa de Validación: `InstallRequest.php`
* **Ubicación:** `app/Http/Requests/InstallRequest.php`
* **Para qué sirve:** Valida de forma estricta todos los datos del formulario antes de que puedan ser procesados por el servidor.
* **Conexiones e Interacciones:**
  - Se conecta directamente con el ciclo de vida de la petición HTTP (`FormRequest` de Laravel).
  - Protege la base de datos limitando los campos de nombres y apellidos a un máximo de 50 caracteres (`max:50`), previniendo errores de desbordamiento de cadena (`String data, right truncation`) en PostgreSQL debido al cambio estructural del Sprint 1.
  - Fuerza a que la contraseña administrativa tenga una longitud mínima de 8 caracteres y pase por una confirmación estricta (`confirmed`).

### C. Capa de Control: `InstallController.php`
* **Ubicación:** `app/Http/Controllers/InstallController.php`
* **Para qué sirve:** Actúa como el orquestador o intermediario del flujo (Controller Layer). No sabe cómo conectarse a una base de datos ni cómo modificar archivos; solo sabe recibir peticiones y retornar respuestas.
* **Conexiones e Interacciones:**
  - **Inyección de Dependencias (SOLID):** Inyecta el `InstallationService` a través de su constructor, permitiendo un desacoplamiento total de la lógica.
  - **Manejo de Excepciones:** Captura los errores de base de datos (`PDOException`) o lógicos (`Exception`) generados en el servicio. Si algo falla, escribe en el log, evita que la aplicación muestre una pantalla rota (error 500) y regresa limpiamente al usuario al formulario inyectando el mensaje de error exacto.

### D. Capa de Datos: Modelos Eloquent Modificados (`User.php` y `Role.php`)
* **Ubicación:** `app/Models/User.php` y `app/Models/Role.php`
* **Para qué sirve:** Mapean y adaptan el ORM de Laravel a la estructura personalizada en español definida en tu script SQL.
* **Conexiones e Interacciones:**
  - **`Role`:** Sobreescribe las propiedades de Laravel apuntando a la tabla `roles` y definiendo como clave primaria `id_rol`. Establece una relación de uno a muchos (`hasMany`) con el modelo `User`.
  - **`User`:** Sobreescribe el modelo de autenticación nativo apuntando a la tabla `usuarios` y definiendo la clave primaria `id_usuario`. Mapea la columna de auditoría de creación usando `const CREATED_AT = 'fecha_creacion'`.
  - **Configuración de Autenticación (`config/auth.php`):** Se modificó el archivo de configuración core para indicarle al ecosistema de Laravel que el proveedor de autenticación por defecto (`users`) debe resolver las sesiones de los usuarios consumiendo el nuevo modelo `App\Models\User::class` (tabla `usuarios`).

---

## 3. Estrategia de Auditoría de Instalación (Syslog / Logs)

Para cumplir con las normativas de alta disponibilidad y auditoría, el proceso de instalación escribe logs detallados en `storage/logs/laravel.log` divididos en tres niveles de criticidad:

1. **`Log::info`:** Registra el inicio de la instalación, la creación exitosa de la BD, la inyección del esquema `.sql`, el registro del administrador y la culminación del wizard.
2. **`Log::warning`:** Registra anomalías operativas que no rompen el sistema, específicamente intentos de instalación donde el usuario proporciona una **Master Key incorrecta**, guardando además la dirección IP del atacante.
3. **`Log::error`:** Registra fallos catastróficos de infraestructura, tales como credenciales incorrectas de PostgreSQL proporcionadas por el usuario, fallos de sintaxis en el archivo SQL o problemas de permisos de escritura en el archivo `.env`.

---

## 4. Guía para Futuras Actualizaciones

Si en el futuro deseas expandir o modificar el instalador (por ejemplo, añadir soporte para el Sprint 2 o modificar variables), toma en cuenta lo siguiente:

1. **Si cambia el esquema SQL global:** Debes actualizar directamente el archivo físico en `database/sql/database_schema.sql`. El servicio lo leerá e inyectará automáticamente en la próxima instalación limpia.
2. **Si se añaden nuevos roles semilla:** Debes declararlos dentro del método `seedInitialAdmin` en el archivo `InstallationService.php` utilizando el método estático de Eloquent `Role::firstOrCreate()`.
3. **Si deseas cambiar la palabra clave de instalación (Master Key):** Abre el archivo `InstallationService.php`, localiza la propiedad protegida `$masterKeyHash` y reemplaza el string por un nuevo hash Bcrypt válido. Puedes generar este hash de forma rápida en tu terminal ejecutando `php artisan tinker` seguido de `Illuminate\Support\Facades\Hash::make('NuevaPalabra');`.

¿Cómo funciona ahora el sistema?
Al entrar a la web, Laravel carga DatabaseConfigurationServiceProvider.

El proveedor revisa el archivo .env. Si DB_ENCRYPTED_DATA está vacío (porque el sistema no se ha instalado), ignora el paso y deja que el middleware CheckIfInstalled redirija al usuario al /install.

Si DB_ENCRYPTED_DATA ya contiene la cadena larga de credenciales, el proveedor la descifra en milisegundos en la memoria RAM del servidor e inyecta los datos de conexión nativos. Tus credenciales reales nunca quedan expuestas en texto plano dentro del .env.


# Módulo de Autenticación, Rutas Virtuales y Desencriptación Dinámica
## Sistema de Archivo Clínico v1 (Arquitectura SOLID & Clean Code)

Este documento detalla la arquitectura, la integración del ciclo de vida y los componentes del módulo de inicio de sesión real (Login), así como la reconfiguración de proveedores de servicios de arranque y los ajustes necesarios en el servidor web IIS para procesar el flujo una vez completada la instalación.

---

## 1. Arquitectura y Flujo Lógico Posterior a la Instalación

Una vez que el archivo testigo `storage/installed.lock` ha sido generado físicamente por el asistente de instalación, el sistema transiciona a su estado operativo real (Modo Producción).

### Diagrama del Ciclo de Vida de Autenticación
1. **Petición Web (`GET /login`)** -> El middleware `CheckIfInstalled` valida el archivo de bloqueo, aprueba el paso y delega la ruta al grupo protegido `guest`.
2. **Carga Estática en IIS** -> El servidor web procesa la regla del archivo central `public/web.config` y mapea los estilos del framework mediante la función absoluta `{{ asset() }}` para renderizar la interfaz responsiva.
3. **Validación Temprana** -> Al enviar el formulario, el software intercepta los datos en la capa `LoginRequest` antes de que toquen los recursos de cómputo del servidor.
4. **Validación de Identidad** -> El controlador utiliza el ecosistema de Laravel adaptado a la tabla en español `usuarios`. Autentica mediante Bcrypt, valida de forma condicional que el flag `activo = TRUE` se cumpla en PostgreSQL y regenera los identificadores de sesión para mitigar vulnerabilidades de fijación de sesión.

---

## 2. Desglose de Componentes y Conexiones

### A. Capa de Seguridad y Arranque: `DatabaseConfigurationServiceProvider.php`
* **Ubicación:** `app/Providers/DatabaseConfigurationServiceProvider.php`
* **Para qué sirve:** Intercepta el arranque del framework (`bootstrapping`) en cada petición web entrante para inyectar las credenciales del motor de datos de forma dinámica en la memoria RAM del servidor.
* **Conexiones e Interacciones:**
  - **Inyección en Caliente:** Lee el valor alfanumérico alojado en la variable de entorno `DB_ENCRYPTED_DATA` dentro del `.env`. Si contiene datos, utiliza la fachada `Crypt::decrypt` para descifrarlos al vuelo e inyectarlos de forma segura en las llaves de configuración `database.connections.pgsql`.
  - **Aislamiento de Entorno:** Permite que las credenciales maestras de PostgreSQL permanezcan 100% ocultas y protegidas en texto plano dentro del servidor, mitigando filtraciones de credenciales si el código es compartido en repositorios públicos.

### B. Capa de Control de Accesos: `LoginController.php`
* **Ubicación:** `app/Http/Controllers/Auth/LoginController.php`
* **Para qué sirve:** Centraliza y procesa las tres acciones core del estado de sesión: renderizar la tarjeta visual, autenticar credenciales y destruir las variables de sesión (Logout).
* **Conexiones e Interacciones:**
  - **Auth Facade:** Conecta directamente con las directivas de seguridad nativas de Laravel, las cuales fueron redirigidas hacia el modelo personalizado `User` y la tabla `usuarios` en español dentro del archivo `config/auth.php`.
  - **Intended Redirects:** Al autenticar con éxito, redirige al usuario a la ruta protegida que intentaba visitar originalmente (`redirect()->intended()`), mejorando la fluidez operativa del personal clínico.

### C. Capa de Validación de Credenciales: `LoginRequest.php`
* **Ubicación:** `app/Http/Requests/Auth/LoginRequest.php`
* **Para qué sirve:** Sanitiza y valida las entradas del formulario web antes de que el controlador intente interactuar con el motor de hashing de Laravel o con PostgreSQL.
* **Conexiones e Interacciones:**
  - Aplica restricciones estrictas de formato de correo electrónico (`email`) y define límites de longitud (`max:100`) para mitigar intentos de inyección de código o saturación de peticiones por desbordamiento de búfer.

### D. Interfaz Gráfica Unificada: `login.blade.php` y `app.blade.php`
* **Ubicación:** `resources/views/auth/login.blade.php` y `resources/views/layouts/app.blade.php`
* **Para qué sirve:** El Layout Madre (`app.blade.php`) provee la cabecera y el pie de página unificados para todo el sistema, mientras que la vista hija (`login.blade.php`) inyecta la tarjeta responsiva con la paleta de colores institucional del proyecto.
* **Conexiones e Interacciones:**
  - **Laravel Assets:** Reemplaza el uso fallido de rutas relativas con puntos (`../public/`) por la función de ayuda `{{ asset() }}`. Esto garantiza que el servidor IIS resuelva los archivos estáticos físicos locales de Bootstrap (`public/css/bootstrap.min.css` y `public/js/bootstrap.bundle.min.js`) de forma absoluta y correcta sin importar el puerto o la URL virtual asignada.

---

## 3. Ajustes Críticos en Servidores Microsoft IIS

Durante el despliegue del flujo real en IIS, se identificaron y resolvieron dos configuraciones de infraestructura esenciales para que Laravel pueda operar correctamente:

1. **Reescritura de URL Virtuales (`public/web.config`):** Por defecto, IIS busca directorios físicos en el disco duro al recibir peticiones web (provocando errores 404). La creación del archivo XML `web.config` dentro de la carpeta `public/` inyecta las reglas nativas para que IIS delegue de forma obligatoria el procesamiento de las rutas virtuales (`/install`, `/login`, `/`) al archivo centralizado `index.php` de Laravel.
2. **Carga de Archivos Locales (Static Content):** Se requiere que el rol de **Contenido Estático** esté activo en las características de Windows del servidor. De lo contrario, IIS bloqueará por razones de seguridad la lectura de los archivos CSS y JavaScript locales (`public/css/installer.css`, `public/js/installer.js`), rompiendo la maquetación visual responsiva del software.

---

## 4. Estrategia de Auditoría de Accesos (Syslog / Logs)

El ciclo de vida de autenticación reporta de forma detallada los eventos críticos del sistema en `storage/logs/laravel.log`:

* **`Log::info` (Inicios Exitosos):** Registra con precisión qué cuenta del personal clínico ha accedido de forma correcta al sistema (ej. *"Syslog_Acceso: Inicio de sesión exitoso para el usuario: informatica@hcpy.blog"*). Reporta también el cierre formal y destrucción de variables de sesión durante el Logout.
* **`Log::warning` (Alertas de Seguridad):** Registra cada intento fallido de inicio de sesión, guardando el correo utilizado y la **dirección IP de origen** (`request()->ip()`) para la auditoría oportuna ante posibles ataques de fuerza bruta.

---

## 5. Resumen de Archivos Guardados y Respaldados en Git
Todos los componentes resultantes de esta sesión de desarrollo limpio han sido confirmados, fusionados y subidos con éxito a tu repositorio remoto de GitHub:
* `app/Providers/DatabaseConfigurationServiceProvider.php` (Inyección de datos descifrados)
* `app/Http/Controllers/Auth/LoginController.php` (Lógica de acceso/salida)
* `app/Http/Requests/Auth/LoginRequest.php` (Validación de credenciales)
* `public/web.config` (Reglas de reescritura para IIS)
* `resources/views/layouts/app.blade.php` (Layout global con carga absoluta asset)
* `resources/views/auth/login.blade.php` (Vista del Login responsivo estilo Odoo)
* `routes/web.php` (Estructura de rutas unificada y limpia con middlewares `guest` y `auth`)

# Módulo de Interfaz Visual Principal (Dashboard & Sidebar Layout)
## Sistema de Archivo Clínico v1 (Arquitectura SOLID & Clean Code)

Este documento detalla la estructura, las dinámicas de adaptabilidad responsiva y los componentes de la interfaz de usuario del Panel de Control Principal (Dashboard). Este módulo se diseñó bajo los estándares visuales corporativos de sistemas empresariales (estilo Odoo) utilizando utilidades nativas de Bootstrap 5 y Flexbox para garantizar un renderizado fluido y desacoplado.

---

## 1. Arquitectura y Ciclo de Vida del Layout

El Dashboard actúa como la vista centralizada y protegida del sistema. Únicamente es accesible si el motor de autenticación valida la petición web a través del filtro de seguridad `auth`.

### Flujo Operativo de la Interfaz
1. **Acceso Autenticado (`GET /`)** -> El enrutador intercepta la petición, verifica que el hilo de sesión esté activo en la memoria RAM y extrae el objeto del usuario logueado.
2. **Inyección Dinámica de Atributos** -> El Layout `dashboard.blade.php` consume en tiempo real las propiedades del modelo `User` para pintar en la barra superior el nombre del operador y su rol correspondiente (`Administrador`, `Usuario` o `Gerente`) extraído desde la relación de PostgreSQL.
3. **Estructura Flexbox Unificada** -> La maquetación divide la pantalla en dos secciones principales mediante un contenedor envolvente (`.wrapper`): una barra de comandos e histórico fija a la izquierda (Sidebar) y un lienzo dinámico y métrico a la derecha (Content Canvas).

---

## 2. Desglose de Componentes y Conexiones Visuales

### A. El Cascarón Madre: `layouts/dashboard.blade.php`
* **Ubicación:** `resources/views/layouts/dashboard.blade.php`
* **Para qué sirve:** Define la plantilla estructural compartida para todas las pantallas del flujo operativo interno (Pacientes, Préstamos, Configuración). Asegura que el menú de navegación y la cabecera superior no tengan que duplicarse en código en cada pantalla nueva.
* **Conexiones e Interacciones:**
  - **Auth Integration:** Se conecta con la fachada `Auth::user()` para personalizar el entorno del operador. Incluye además el formulario seguro con la directiva `@csrf` para enviar la petición `POST /logout` al controlador de forma aislada.
  - **Blade Directives:** Utiliza la directiva `@yield('dashboard_content')` para servir de contenedor a los diferentes entregables técnicos del mapa de ruta (Sprints).

### B. El Lienzo de Operaciones: `dashboard/index.blade.php`
* **Ubicación:** `resources/views/dashboard/index.blade.php`
* **Para qué sirve:** Actúa como la pantalla de inicio por defecto (`home`). Presenta de forma gráfica las tarjetas de control métrico y reserva el espacio arquitectónico para la inyección de los componentes pesados.
* **Conexiones e Interacciones:**
  - **Grid Responsivo:** Utiliza las clases de rejilla de Bootstrap 5 (`col-12 col-sm-6 col-lg-3`) para reacomodar de forma automática el tamaño de las tarjetas métricas dependiendo del dispositivo de visualización.
  - **Sprint Anchors:** Deja listos los contenedores para el desarrollo del **Sprint 4** (donde se inyectará el Buscador Predictivo mediante Livewire) y los accesos para la captura y control de expedientes de los Sprints intermedios.

### C. Estilos de Adaptabilidad: `dashboard.css`
* **Ubicación:** `public/css/dashboard.css`
* **Para qué sirve:** Controla de forma exclusiva las dimensiones físicas, los efectos de transición elástica de los menús y la inyección de la paleta de colores corporativa (Púrpura institucional Odoo y Azul pizarra de control).
* **Conexiones e Interacciones:**
  - Utiliza Media Queries (`@media (max-width: 768px)`) para detectar pantallas móviles o de tablets de uso hospitalario, aplicando un desplazamiento negativo (`margin-left: -250px`) que oculta la barra lateral de forma nativa para maximizar el área de trabajo clínico.

### D. Dinámicas del Frontend: `dashboard.js`
* **Ubicación:** `public/js/dashboard.js`
* **Para qué sirve:** Agrega interactividad ligera al Layout controlando los estados de apertura y cierre del menú.
* **Conexiones e Interacciones:**
  - Se vincula al evento `click` del disparador `#sidebarCollapse` de la Navbar superior. Utiliza el método `classList.toggle('active')` para alternar la visibilidad de la barra lateral de manera fluida y responsiva sin necesidad de recargar la página.

---

## 3. Estrategia de Auditoría de Salida (Syslog / Logs)

El cierre de sesión (Logout) integrado en la Navbar superior del layout no es solo una acción cosmética; ejecuta un protocolo de seguridad estricto que escribe en `storage/logs/laravel.log`:

* **`Log::info` (Cierre Formal):** Registra exactamente qué cuenta de usuario clínico ha abandonado el entorno de trabajo (ej. *"Syslog_Acceso: Sesión cerrada formalmente por el usuario: informatica@hcpy.blog"*). 
* **Destrucción de Token:** El controlador invalida el identificador de sesión actual y regenera por completo el token CSRF para asegurar que la sesión quede totalmente inaccesible y blindada en el navegador web del cliente.

---

## 4. Guía para Futuras Extensiones del Panel

Si en el futuro deseas añadir nuevas pantallas operativas dentro de este entorno, sigue estos lineamientos:

1. **Añadir un enlace al menú lateral:** Abre `layouts/dashboard.blade.php`, localiza la lista `<ul>` del Sidebar y añade una etiqueta `<li>`. Utiliza el helper ternario `{{ request()->is('tu-ruta*') ? 'active' : '' }}` en la clase del elemento para que se pinte de color púrpura automáticamente cuando el usuario esté dentro de esa sección.
2. **Crear una vista hija del panel:** Genera tu nuevo archivo Blade y asegúrate de iniciar el código heredando la plantilla mediante `@extends('layouts.dashboard')`. Todo el desarrollo gráfico de la sección debe quedar encapsulado dentro de las directivas `@section('dashboard_content')` y `@endsection`.
