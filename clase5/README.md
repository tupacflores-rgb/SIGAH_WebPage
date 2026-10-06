# Clase 5 - Formularios seguros con PHP

Esta carpeta reúne la entrega correspondiente a la quinta clase.

## Objetivo

Implementar la recepción, validación y sanitización de datos enviados desde el navegador usando `$_POST` y `$_GET`, con foco en la prevención de XSS y una mejor experiencia del usuario.

## Contenido

- `index.php`: procesamiento principal de los formularios.
- `style.css`: estilos visuales del ejemplo.
- `screenshots/post-form.svg`: vista del formulario por POST.
- `screenshots/get-form.svg`: vista del formulario por GET.

## Reglas aplicadas

- `trim()` para limpiar espacios.
- `htmlspecialchars()` para proteger la salida en pantalla.
- `filter_var()` y `FILTER_VALIDATE_EMAIL` para comprobar correos válidos.
- `empty()`, `isset()` y validación manual para campos obligatorios.
- Preservación de los valores ingresados cuando hay errores.
