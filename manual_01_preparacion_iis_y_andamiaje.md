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

