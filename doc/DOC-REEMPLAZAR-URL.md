# DOC-REEMPLAZAR-URL.md

> Documentación del módulo **Reemplazar URL** del plugin Generate Page AI v2.11.0.

---

## Descripción

Reemplaza una URL o fragmento de texto en **toda la base de datos** de WordPress, con soporte para datos serializados de PHP/Elementor y opcionalmente agrega una regla de redirección 301 al `.htaccess`.

Equivalente a ejecutar:

```bash
wp search-replace 'url_vieja' 'url_nueva' --all-tables
```

pero desde el admin de WordPress, vía AJAX, sin WP-CLI.

---

## Archivos

| Archivo | Responsabilidad |
|---|---|
| `src/api/reemplazar.php` | `GPAI_REEMPLAZAR` — handler AJAX, reemplazo en DB, `.htaccess` |
| `src/page/pages/reemplazar/add.php` | Registro del submenú |
| `src/page/pages/reemplazar/page.php` | Layout (tab) |
| `src/page/sections/reemplazar.php` | Formulario, botones, JS inline y resultados |

---

## Seguridad

El handler `reemplazarAjax()` valida 4 capas antes de ejecutar:

1. **Nonce**: `check_ajax_referer('gpai_nonce', 'nonce')` — previene CSRF.
2. **Capability**: `current_user_can('manage_options')` — solo administradores.
3. **Clave interna**: `hash_equals(GPAI_API_KEY_INTERNA, $_POST['api_key'])` — la clave viaja en el body del AJAX y se compara con la constante PHP `GPAI_API_KEY_INTERNA` (que a su vez se carga de `wp_options` `GPAI_API_KEY_INTERNA`, generada al activar el plugin).
4. **Sanitización de input**: `sanitizeUrlField()` aplica `wp_unslash()`, `wp_check_invalid_utf8()`, `wp_strip_all_tags()`, `wp_kses_no_null()` y `trim()`.

---

## Flujo completo

### 1. Reemplazo en base de datos (`replaceUrl()`)

1. Se amplían los límites de ejecución (`set_time_limit(600)`, `memory_limit 512M`).
2. Se obtienen todas las tablas con `SHOW TABLES`.
3. Por cada tabla, `processTable()`:
   - Obtiene columnas (`SHOW FULL COLUMNS FROM`) y primary keys (`SHOW KEYS ... WHERE Key_name = 'PRIMARY'`).
   - Lee filas en **chunks de 500** (`LIMIT 500 OFFSET N`).
   - Por cada fila, recorre todas las columnas (excepto `guid`).
   - `recursiveUnserializeReplace()` deserializa recursivamente, reemplaza el texto, y re-serializa si estaba serializado.
   - Genera un `UPDATE` manual con `sqlValue()` (escapa strings con `esc_sql()`).
4. **Segunda pasada**: si la URL original contiene `/`, se repite con `str_replace('/', '\\/', ...)` para cubrir URLs escapadas en formato JSON/Elementor.
5. Se retorna un reporte: tablas procesadas, tablas con cambios, filas actualizadas, celdas actualizadas, errores, tiempo transcurrido.

### 2. Redirección 301 (`.htaccess`)

1. `buildRedirectRule()` construye: `RewriteRule ^old/?$ /new [R=301,L]`.
   - Escapa caracteres especiales de PCRE con `preg_quote()`.
   - Reemplaza espacios por `%20`.
2. `addRedirectToHtaccess()`:
   - Verifica permisos de escritura del `.htaccess` via `GPAI_USE_DATA_HTACCESS::get()`.
   - Verifica si la regla ya existe con `ruleExists()` (busca string exacto + versión normalizada con `normalizeForCompare()`).
   - Si existe bloque `# GPAI Redirect URL`: inserta la nueva regla antes de `</IfModule>`.
   - Si no existe: crea el bloque completo y lo inserta antes de `# BEGIN WordPress`.
   - Si el `.htaccess` no existe, hace backup previo.

### 3. Registro en log

Cada ejecución se registra con `FWUSystemLog::add(GPAI_KEY, ...)` con: tipo, URLs, si se agregó redirect, reporte y resultado de redirect.

---

## `recursiveUnserializeReplace()` — Preservación de serialización

El algoritmo recursivo:

```
Si es string serializado → deserializar → recursión → re-serializar
Si es array → iterar valores → recursión sobre cada valor
Si es objeto (no __PHP_Incomplete_Class) → clonar → recursión sobre propiedades
Si es string normal → str_replace directo
Si hay excepción → conservar valor original
```

Esto evita romper datos serializados de WordPress, Elementor (`_elementor_data`), y cualquier otro valor que use `serialize()`.

---

## Normalización para dedup (`normalizeForCompare()`)

Para evitar duplicar reglas ya escritas (con slashes escapados distintos), se normaliza:

| Original | Normalizado |
|---|---|
| `\-` | `-` |
| `\.` | `.` |
| `\/` | `/` |
| `\~` | `~` |
| `\^` | `^` |
| `\$` | `$` |

---

## Clave interna `GPAI_API_KEY_INTERNA`

- Se define como constante en `index.php`:
  ```php
  if (!defined('GPAI_API_KEY_INTERNA')) {
      define('GPAI_API_KEY_INTERNA', get_option('GPAI_API_KEY_INTERNA'));
  }
  ```
- El valor se genera y persiste en `wp_options` al activar el plugin:
  - Intento 1: `bin2hex(random_bytes(32))` (64 chars hex aleatorios).
  - Fallback: `md5(uniqid('', true))` (32 chars hex).
- El formulario admin carga la clave via AJAX (`loadGpaiApiKey`) y la envía como `api_key` en el body del POST de reemplazo.

---

## AJAX

| Campo | Descripción |
|---|---|
| Action | `gpai_reemplazar_url` |
| Nonce | `gpai_nonce` (campo `nonce`) |
| `api_key` | Clave interna `GPAI_API_KEY_INTERNA` |
| `url_vieja` | URL o fragmento a buscar (sanitizado) |
| `url_nueva` | URL o fragmento de reemplazo (sanitizado) |
| `agregar_redirect` | `'1'` si se debe agregar redirect 301, `'0'` si no |

### Respuesta exitosa (`wp_send_json_success`)

```json
{
  "data": {
    "message": "Reemplazo completado.",
    "report": {
      "tables": 12,
      "tables_with_changes": 3,
      "rows_updated": 5,
      "cells_updated": 10,
      "tables_skipped": [],
      "errors": [],
      "elapsed": 1.23
    },
    "rule": "RewriteRule ^old/?$ /new [R=301,L]",
    "redirect": {
      "status": "ok",
      "message": "Redirección agregada a .htaccess."
    }
  }
}
```

---

## Errores comunes

| Mensaje | Causa |
|---|---|
| `La clave de seguridad es inválida.` | `api_key` no coincide con `GPAI_API_KEY_INTERNA` |
| `No tienes permisos para realizar esta acción.` | El usuario no tiene `manage_options` |
| `Debes ingresar la URL vieja y la URL nueva.` | Campos vacíos |
| `La URL vieja y la URL nueva deben ser diferentes.` | Ambos valores son idénticos |
| `El archivo .htaccess no tiene permisos de escritura.` | `chmod` del `.htaccess` no permite escritura |
