# SIGAH — Sistema Integral de Gestión de Asistencias y Horarios

Aplicación web en **PHP** para la EET N°3100 "Rep. de la India" (Salta, Argentina).
Pensada para correr en **XAMPP** (Apache + PHP + MariaDB) sin instalar nada más:
ni Composer, ni frameworks, ni dependencias externas.

Este repositorio también documenta la evolución del sitio a PHP para la **Unidad 4**:
las vistas renderizan HTML desde el servidor (SSR), reutilizan plantillas mediante
`require/include` (SSI), generan el título y el enlace activo del menú dinámicamente,
y cargan la configuración global desde `.env`.

**Prototipo de Figma:** pendiente de completar con el enlace público o de solo lectura del diseño original.

**Tecnologías:** HTML5, CSS3, JavaScript, PHP 8.1+, Apache/XAMPP y MariaDB/MySQL opcional.

---

## Requisitos

| Componente | Versión mínima | De dónde sale |
|---|---|---|
| Apache | 2.4 | XAMPP |
| PHP | **8.1** | XAMPP (8.2 recomendado) |
| MariaDB / MySQL | 10.4 | XAMPP (**opcional**) |

Descarga de XAMPP: <https://www.apachefriends.org/es/download.html>

---

## Instalación en XAMPP (paso a paso)

### Desde cero en Windows

1. Descargá XAMPP para Windows desde el enlace de requisitos e instalalo. Incluí
  **Apache** y **PHP**; MySQL/MariaDB es opcional para el modo predeterminado basado en JSON.
2. Abrí **XAMPP Control Panel**. Si Windows solicita permiso de firewall, permití
  Apache en redes privadas. No hace falta iniciar MySQL para la configuración básica.
3. Ubicá la carpeta `htdocs`, normalmente `C:\xampp\htdocs`.
4. Luego seguí el paso 1 de abajo para copiar el repositorio en `htdocs` y elegir
  el nombre de la carpeta del proyecto.

> Si clonás desde GitHub, cloná el repositorio dentro de `C:\xampp\htdocs`.
> Si descargás un ZIP, extraelo y evitá dejar una carpeta anidada duplicada: `index.php`
> debe quedar directamente dentro de la carpeta del proyecto.

### 1. Copiar el proyecto a `htdocs`

Copiá toda la carpeta del proyecto adentro de `htdocs` y llamala `sigah`:

```
C:\xampp\htdocs\sigah\
├── index.php
├── app\
├── partials\
├── data\
└── assets\
```

> Si la dejás en otra ruta (por ejemplo `C:\xampp\htdocs\SIGAH_WebPage`),
> también funciona: la URL base se detecta sola.

### 2. Crear el archivo `.env`

En la raíz del proyecto, copiá `.env.example` y renombralo a `.env`:

```powershell
cd C:\xampp\htdocs\sigah
Copy-Item .env.example .env
```

Ese archivo ya viene con valores listos para funcionar. Es el único lugar
donde se configura el sistema (nombre, escuela, sesión, base de datos, etc.).
Cada pantalla define `$titulo_pagina` antes de incluir `partials/header.php`, que
compone el título de la pestaña con el nombre de la aplicación.

### 3. Encender Apache

Abrí el **XAMPP Control Panel** y tocá `Start` en **Apache**.
Solo hace falta MySQL si vas a usar la base de datos (paso 6).

### 4. Abrir la web

```
http://localhost/sigah/
```

### 5. Entrar al sistema

| Rol | Usuario | Contraseña |
|---|---|---|
| Administrador | `rolando@sigah.edu.ar` o `rolando_flores` | `Sigah123@` |
| Docente | `lopez@sigah.edu.ar` o `prof_lopez` | `Docente456@` |
| Docente | `torres@sigah.edu.ar` o `prof_torres` | `Docente789@` |

También hay un botón **"Entrar como prueba"** (se apaga con `ALLOW_DEMO_LOGIN=false`).

### 6. (Opcional) Usar MariaDB en lugar de los JSON

1. `Start` en **MySQL** desde el panel de XAMPP.
2. Entrá a <http://localhost/phpmyadmin> → pestaña **Importar** → elegí `sql/sigah.sql`.
3. Generá los hashes de contraseña:
   ```powershell
   C:\xampp\php\php.exe sql\generar-hashes.php
   ```
   Copiá los `UPDATE` que imprime y ejecutalos en phpMyAdmin.
4. En el `.env` poné `DB_ENABLED=true`.

---

## Estructura del proyecto

```
sigah/
├── .env                  ← configuración GLOBAL (no se versiona)
├── .env.example          ← plantilla del .env
├── .env.local            ← configuración LOCAL de tu máquina (opcional)
├── .htaccess             ← reglas de Apache y bloqueo de archivos sensibles
│
├── index.php             ← login
├── home.php              ← dashboard
├── asist.php             ← toma de asistencia
├── registry-al.php       ← alta / edición / baja de alumnos
├── report.php            ← reportes y gráficos
├── clock.php             ← horario semanal
├── notif.php             ← notificaciones (SANE)
├── config.php            ← configuración de la institución
├── perf-adm.php          ← perfil del usuario
├── add-user.php          ← registro de usuarios
├── logout.php            ← cierre de sesión
├── faq.php · blog.php · curso.php · cv.php   ← páginas públicas
│
├── app/                  ← núcleo (no accesible por URL)
│   ├── bootstrap.php     ← arranque: .env, sesión, helpers
│   ├── Env.php           ← lector de archivos .env
│   ├── helpers.php       ← url(), asset(), e(), read_json(), csrf...
│   ├── auth.php          ← login, roles, contraseñas
│   └── database.php      ← conexión PDO opcional a MariaDB
│
├── partials/             ← plantillas compartidas
│   ├── header.php        ← <head>, metadatos, CSS y apertura del <body>
│   ├── head.php          ← alias de compatibilidad que carga header.php
│   ├── site-header.php   ← encabezado visual y acciones
│   ├── nav.php           ← menú SSR con enlace activo
│   ├── page-header.php   ← franja con título y migas de pan
│   └── footer.php        ← pie + scripts + cierre del documento
│
├── data/                 ← "base de datos" en JSON (bloqueada por .htaccess)
│   ├── credentials.json  ← usuarios
│   ├── students.json     ← alumnos
│   ├── institution.json  ← institución, ciclo lectivo, cursos
│   ├── attendance.json   ← se crea al guardar asistencia
│   └── comments.json     ← se crea al comentar en el blog
│
├── assets/
│   ├── css/style.css
│   └── js/components.js
│
└── sql/
    ├── sigah.sql             ← esquema + datos de ejemplo para MariaDB
    └── generar-hashes.php    ← genera los hashes de contraseña
```

---

## Cómo funcionan las variables de entorno

Hay **dos niveles**, y el segundo pisa al primero:

| Archivo | Para qué | ¿Se versiona? |
|---|---|---|
| `.env` | Variables **globales** del proyecto: nombre del sistema, datos de la escuela, funcionalidades, umbrales. | No (se versiona `.env.example`) |
| `.env.local` | Variables **locales** de tu computadora: ruta base, contraseña de MySQL, modo debug. | Nunca |

En el código se leen siempre igual:

```php
$nombre  = env('APP_NAME', 'SIGAH');
$umbral  = (int) env('ATTENDANCE_RISK_THRESHOLD', 85);
```

Las variables globales seguras también llegan al navegador como `window.SIGAH`
(nombre, versión, ruta base). **Nunca** pongas contraseñas ahí.

### Variables más usadas

| Variable | Qué hace |
|---|---|
| `APP_DEBUG` | `true` muestra los errores de PHP en pantalla |
| `BASE_PATH` | Fuerza la ruta base. Vacío = detección automática |
| `SCHOOL_NAME` / `SCHOOL_SHORT` | Nombre de la escuela en header, footer y páginas |
| `ALLOW_DEMO_LOGIN` | Muestra u oculta el botón "Entrar como prueba" |
| `ALLOW_SELF_REGISTER` | Permite crear usuarios desde `add-user.php` |
| `SHOW_WELCOME_ALERT` | Los `alert()` de bienvenida del proyecto original |
| `ATTENDANCE_RISK_THRESHOLD` | % de asistencia por debajo del cual un alumno queda "en riesgo" |
| `DB_ENABLED` | `true` usa MariaDB; `false` usa los JSON de `data/` |

La plantilla versionada `.env.example` contiene, entre otras, `APP_NAME`,
`SUPPORT_EMAIL` y `APP_ENV=local`. Copiala como `.env` para ejecutar el proyecto;
`.env` y `.env.local` están excluidos en `.gitignore` y no deben subirse al repositorio.

---

## Entrega del TP4: documentación y evidencias

### Figma

Agregar aquí el enlace público o de solo lectura al prototipo original: **pendiente de completar**.
No se incluye una URL porque el enlace no fue proporcionado en el proyecto.

### Capturas requeridas

Las capturas deben ser imágenes reales tomadas después de iniciar el proyecto en XAMPP;
no se deben reemplazar por imágenes ilustrativas. Guardalas, por ejemplo, en una carpeta
`docs/evidencias/` y enlazalas aquí antes de entregar:

- [ ] Sitio abierto con una URL `http://localhost/...`.
- [ ] Código de `partials/header.php`, `partials/nav.php` y `partials/footer.php`.
- [ ] Pestaña del navegador mostrando un título dinámico y navegación con el elemento activo.
- [ ] Archivo `.env.example` visible en el repositorio.

Estas evidencias y el enlace de Figma requieren datos/capturas del autor y quedan pendientes
hasta agregarlos al repositorio.

### Flujo Git sugerido

Creá una rama de trabajo, guardá los cambios en commits pequeños y descriptivos, subila a
GitHub y abrí un Pull Request hacia `main`. Ejemplo de nombre de rama:
`feature/migracion-php-ssi`. El enlace del PR y el repositorio público se entregan en Classroom.

---

## Problemas frecuentes

| Síntoma | Causa y solución |
|---|---|
| Se ve el código PHP como texto | Abriste el archivo con doble clic (`file:///...`). Tiene que ser `http://localhost/sigah/` |
| `No se encontró el archivo .env` | Falta copiar `.env.example` a `.env` |
| Error 403 al entrar | Apache no está leyendo el `.htaccess`. Verificá `AllowOverride All` en `httpd.conf` |
| Apache no arranca | El puerto 80 está ocupado (Skype, IIS, VMware). Cambialo por 8080 en `Config → httpd.conf` y entrá a `http://localhost:8080/sigah/` |
| "No se pudo guardar" al crear un alumno | La carpeta `data/` no tiene permisos de escritura para el usuario de Apache |
| Las páginas viejas `.html` dan 404 | El `.htaccess` las redirige a `.php`; si falla, activá `mod_rewrite` en `httpd.conf` |

---

## Notas de seguridad

Este proyecto es **didáctico** y está pensado para un servidor local:

- Los usuarios de ejemplo de `data/credentials.json` tienen la contraseña en texto plano
  (venían así del proyecto original). Los usuarios **nuevos** se guardan con
  `password_hash()`. Si lo vas a usar en serio, regenerá todas las contraseñas.
- Todos los formularios que escriben datos usan token **CSRF**.
- Las carpetas `app/`, `partials/`, `data/` y `sql/` están bloqueadas por `.htaccess`.
  Ese bloqueo depende de Apache: si algún día lo movés a otro servidor, verificá que
  siga vigente.
- No expongas este proyecto a internet tal como está.

---

## Autor

Rolando Maximo Tupac Flores Arce — EET N°3100 "Rep. de la India", Salta.
Proyecto de la materia Diseño UI/UX. Soporte técnico: Pixel Fix.
