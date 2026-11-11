# Generate Page AI — Reglas para IAs

Este archivo contiene las reglas, validaciones y convenciones que toda IA debe seguir al programar en este proyecto.

---

## 1. Estándares de Código

### PHP
- **WordPress Coding Standards**: Sigue los estándares de codificación de WordPress para PHP.
- **PHP 7.0+**: No uses sintaxis moderna de PHP (nullsafe `?->`, named arguments, match, readonly properties, etc). El operador `??` (null coalescing) está permitido.
- **Nombrado**: Las clases usan prefijo `GPAI_` (ej: `GPAI_AI`, `GPAI_CONTENT`). Métodos y propiedades en `camelCase` o `UPPER_SNAKE` para constantes.
- **Sanitización**: Toda salida de datos debe escaparse. Usa `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()` según contexto.
- **Nonces**: Todo formulario y AJAX debe verificar nonce con `wp_verify_nonce()` o `check_ajax_referer()`.
- **Capabilities**: Toda operación admin debe verificar `current_user_can()`.

### JavaScript
- **ES5**: El plugin soporta WordPress 5.0+, usa ES5 (no arrow functions, no let/const, no template literals).
- **jQuery**: Usa `jQuery(function($){ ... })` para DOM ready.
- **AJAX**: Toda llamada AJAX debe usar `jQuery.ajax()` con action registrada via `wp_ajax_*` y nonce.
- **Nombrado**: Funciones en `snake_case` con prefijo `gpai_` (ej: `gpai_save_custom_field`).

### CSS
- **Prefijo**: Todas las clases CSS deben llevar prefijo `gpai-`.
- **Especificidad**: Evita `!important`. Usa clases con la especificidad adecuada.

---

## 2. Arquitectura del Plugin

### Sistema de Archivos
- `index.php` → Plugin header y constantes globales. No agregues lógica aquí.
- `src/_.php` → Cargador maestro. Todo nuevo módulo debe ser require desde aquí.
- `src/ai/` → Clientes IA (Google Gemini, harness de pruebas).
- `src/api/` → APIs AJAX, CRUD y REST.
- `src/data/` → Capa de datos (wp_options CRUD).
- `src/frontend/` → Salida frontend (meta tags, JSON-LD, panel de edición).
- `src/meta-box/` → Meta boxes (GPAI SEO, Parent, Box).
- `src/page/` → Páginas admin.
- `src/templates/` → Templates reutilizables.
- `src/prompts/` → Archivos de prompt default (.txt).
- `src/css/` → Estilos (admin, Elementor, frontend edit, XSL sitemap).
- `src/js/` → JavaScript admin, Elementor, frontend edit.
- `src/hook/` → Hooks de WordPress.
- `src/elementor/` → Integración con Elementor.

### Constantes
Usa las constantes definidas en `index.php`:
- `GPAI_KEY` para prefijos de opciones y meta keys
- `GPAI_CONFIG` para la configuración global
- `GPAI_CONTENT` para variaciones de posts
- Nunca hardcodees strings como `'GPAI'` o `'GPAI_CONFIG'`

### Post Meta
Todos los meta keys del sistema GPAI SEO usan prefijo `gpai_wpseo_`. No agregues meta keys sin este prefijo a menos que sea estrictamente necesario.

### wp_options
Toda opción global debe usar `GPAI_USE_DATA_BASE` o una subclase. No uses `add_option()`/`update_option()` directamente fuera de la capa data.

---

## 3. Validaciones de Seguridad

1. **Nunca** hardcodees API keys en el código. La API key de Gemini se guarda en `GPAI_CONFIG['apiKey']`.
2. **Siempre** sanitiza input: `$_POST`, `$_GET`, `$_REQUEST` deben pasar por `sanitize_text_field()`, `intval()`, etc.
3. **Siempre** valida nonces en handlers AJAX (`check_ajax_referer('gpai_nonce', 'nonce')`).
4. **Siempre** valida capabilities: `current_user_can('edit_posts')` antes de cualquier operación.
5. **wp_kses_post()** se aplica en casi todos los saves — ten cuidado porque puede corromper JSON si contiene `<` o `>`.
6. **Nunca** confíes en el output de la IA. Siempre parsea con `GPAI_AI::parseJson()` y normaliza campos con `GPAI_CONTENT::normalizeFields()`.

---

## 4. Convenciones del Proyecto

### AJAX Endpoints
- Action registrada: `wp_ajax_{action}` donde action usa prefijo `gpai_`.
- Nonce action: `gpai_nonce`.
- Los handlers deben estar en `src/api/` o en el archivo correspondiente.
- Respuesta siempre en JSON: `wp_send_json_success($data)` o `wp_send_json_error($message)`.

### REST API
- Namespace: `GPAI_KEY` (`'GPAI'`).
- Endpoints existentes:
  - `POST /GPAI/seo` → Guarda campos GPAI SEO, clave vía header `X-GPAI-SEO-Key`.
  - `GET /GPAI/cf/get` → Obtiene custom fields de un post, clave vía header `X-GPAI-CF-Key`.
  - `POST /GPAI/cf/set` → Guarda custom fields de un post, clave vía header `X-GPAI-CF-Key`.
  - `GET /GPAI/gf/get` → Obtiene campos globales, clave vía header `X-GPAI-GF-Key`.
  - `POST /GPAI/gf/set` → Guarda campos globales, clave vía header `X-GPAI-GF-Key`.
- Las API keys se configuran en la página admin "API".

### Hooks
- Acciones: `add_action('hook', 'callback', priority)`.
- Filtros: `add_filter('hook', 'callback', priority, args)`.
- No registres hooks en el scope global. Deben estar dentro de funciones o métodos.

### Prompts de IA
- Los prompts base se guardan en `GPAI_CONFIG['prompts_base']`.
- Los archivos default están en `src/prompts/*.txt`.
- Nunca edites prompts directamente en el código SQL — usa la UI admin o los archivos .txt.

### Logging
- Usa siempre `FWUSystemLog::add(GPAI_KEY, $message)` para errores de IA.
- No uses `error_log()`, `var_dump()`, `print_r()` en producción.

---

## 5. Git Workflow

1. **Commits**: No hacer commits actumaticamente, solo dar sugerencias de commits.

---

## 6. Lo que NO debes hacer

- ✗ NO modifiques `index.php` (plugin header).
- ✗ NO elimines el prefijo `GPAI_` de ninguna clase/función.
- ✗ NO agregues dependencias npm/composer sin autorización explícita.
- ✗ NO edites archivos en `libs/` (vendor de Composer).
- ✗ NO uses sintaxis moderna de PHP (>=7.0) — el plugin requiere PHP 7.0+ (usa `??` pero no `?->`, named arguments, match, readonly properties).
- ✗ NO hardcodees URLs o paths — usa `GPAI_URL`, `GPAI_DIR`.
- ✗ NO añadas archivos nuevos sin require desde `src/_.php` o desde subcarpetas `src/*/_.php`.
