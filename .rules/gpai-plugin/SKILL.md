---
name: gpai-plugin
description: >-
  Use ONLY when working on Generate Page AI plugin features: GPAI SEO system,
  content generation with Gemini, custom fields management, sitemaps, Elementor
  integration, or Static Page HTML optimization. Contains plugin-specific
  validation rules, class references, and data flow constraints.
---

# GPAI Plugin — Reglas Específicas del Plugin

Actíva esta skill cuando trabajes con funcionalidades propias del plugin Generate Page AI.

---

## Sistema GPAI SEO (27 campos)

### Meta Keys Permitidos
```php
gpai_wpseo_active              // '1'/'0'
gpai_wpseo_title               // string
gpai_wpseo_metadesc            // string
gpai_wpseo_focuskw             // string
gpai_wpseo_focuskeywords       // string (JSON)
gpai_wpseo_canonical           // URL
gpai_wpseo_bctitle             // string
gpai_wpseo_redirect            // URL
gpai_wpseo_is_cornerstone      // '1'/'0'
gpai_wpseo_meta-robots-noindex    // '1'/'0'
gpai_wpseo_meta-robots-nofollow   // '1'/'0'
gpai_wpseo_meta-robots-adv        // string
gpai_wpseo_meta-robots-noarchive  // '1'/'0'
gpai_wpseo_meta-robots-nosnippet  // '1'/'0'
gpai_wpseo_meta-robots-noimageindex // '1'/'0'
gpai_wpseo_opengraph-title        // string
gpai_wpseo_opengraph-description  // string
gpai_wpseo_opengraph-image        // URL
gpai_wpseo_opengraph-image-id     // string (ID)
gpai_wpseo_opengraph-url          // URL
gpai_wpseo_twitter-title          // string
gpai_wpseo_twitter-description    // string
gpai_wpseo_twitter-image          // URL
gpai_wpseo_schema_page_type       // string (ej: "WebPage")
gpai_wpseo_schema_article_type    // string (ej: "Article")
gpai_wpseo_schema_extra_json      // string (JSON con bloques Schema.org)
gpai_wpseo_remove_other_jsonld    // '1'/'0'
```

### Reglas del Sistema SEO
1. Todos los meta keys deben tener prefijo `gpai_wpseo_`.
2. `gpai_wpseo_active` debe ser `'1'` o `'0'` como string.
3. Los campos de tipo flag (`active`, `is_cornerstone`, robots flags, `remove_other_jsonld`) siempre son string `'1'`/`'0'`.
4. `schema_extra_json` debe guardarse como string JSON válido.
5. Los robots flags se guardan como meta keys separados (no como array).
6. `focuskeywords` es JSON array de objetos `{ keyword, score }`.
7. La sanitización usa `wp_kses_post()` salvo para `schema_extra_json` y `focuskeywords` que usan `wp_json_encode()`.

### Flujo de Renderizado SEO
1. `wp_head` priority 20 → `GPAI_SEO_output()`.
2. Si `gpai_wpseo_active !== '1'`, no se renderiza nada.
3. `GPAI_SEO_output_jsonld()` → construye `@graph` con WebPage + WebSite + Organization + bloques extra.
4. Schema extra se filtra por `@type` y se aplica `GPAI_replace_custom_vars()` via `array_walk_recursive()`.
5. Filtro `gpai_seo_schema` disponible para hooks externos.
6. Anulación Yoast via 15+ filtros `wpseo_*`.
7. Eliminación de otros JSON-LD via output buffering si `remove_other_jsonld === '1'`.

---

## Generación de Contenido con IA

### Flujo Correcto
1. `GPAI_CF::GET($post_id)` → escanea `post_content` + `_elementor_data` en busca de `{{key}}`.
2. Admin asigna valores/prompts personalizados.
3. `GPAI_CONTENT::getContent($CONFIG)` → construye prompt, envía a Gemini, parsea JSON.
4. Resultado → `GPAI_CONTENT` (wp_options) como array de variaciones.
5. `GPAI_USE_DATA_DUPLICADOS::generateVariation()` → duplica post via Yoast, escribe campos.

### Validaciones en Generación
- Siempre parsea respuesta con `GPAI_AI::parseJson()`.
- Siempre normaliza campos con `GPAI_CONTENT::normalizeFields()`.
- Nunca confíes en que la IA devuelva la estructura exacta.
- Los prompts base se almacenan en `GPAI_CONFIG['prompts_base']` con fallback a archivos `src/prompts/*.txt`.

---

## AJAX Endpoints del Plugin

| Action | Handler | Ubicación |
|---|---|---|
| `gpai_export_post` | `GPAI_EXPORT_IMPORT::exportPost()` | `src/api/export_import.php` |
| `gpai_import_post` | `GPAI_EXPORT_IMPORT::importPost()` | `src/api/export_import.php` |
| `gpai_export_global_fields` | `GPAI_EXPORT_IMPORT::exportGlobalFields()` | `src/api/export_import.php` |
| `gpai_import_global_fields` | `GPAI_EXPORT_IMPORT::importGlobalFields()` | `src/api/export_import.php` |
| `gpai_seo_save` | `GPAI_SEO_save_ajax()` | `src/meta-box/gpai-seo.php` |
| `gpai_seo_generate` | `GPAI_SEO::generateSEO_ajax()` | `src/api/gpai_seo.php` |
| `gpai_seo_export` | `GPAI_SEO_export_ajax()` | `src/meta-box/gpai-seo.php` |
| `gpai_seo_import` | `GPAI_SEO_import_ajax()` | `src/meta-box/gpai-seo.php` |
| `gpai_save_custom_field` | `GPAI_CF::save_from_elementor_ajax()` | `src/api/cf.php` |
| `gpai_list_custom_fields` | `GPAI_CF::list_custom_fields_ajax()` | `src/api/cf.php` |
| `gpai_delete_custom_field` | `GPAI_CF::delete_custom_field_ajax()` | `src/api/cf.php` |
| `gpai_html_generate` | `GPAI_SEO::generateHTML_ajax()` | `src/api/gpai_seo.php` |
| `gpai_html_swap` | `GPAI_SEO::swapHTML_ajax()` | `src/api/gpai_seo.php` |
| `gpai_sitemap_generate` | `GPAI_SITEMAPS_API::generate()` | `src/api/sitemaps.php` |
| `gpai_sitemap_save_generate` | `GPAI_SITEMAPS_API::saveAndGenerate()` | `src/api/sitemaps.php` |
| `gpai_sitemap_save_xml` | `GPAI_SITEMAPS_API::saveXml()` | `src/api/sitemaps.php` |
| `gpai_imagenes_get` | `GPAI_IMAGENES::getImagesAjax()` | `src/api/imagenes.php` |
| `gpai_imagenes_save` | `GPAI_IMAGENES::saveImagesAjax()` | `src/api/imagenes.php` |
| `gpai_analisis_seo` | `GPAI_ANALISIS::analyzeSEO_ajax()` | `src/api/analisis.php` |
| `gpai_analisis_links` | `GPAI_ANALISIS::validateLinks_ajax()` | `src/api/analisis.php` |
| `gpai_analisis_pagespeed` | `GPAI_ANALISIS::pageSpeed_ajax()` | `src/api/analisis.php` |

Todos los AJAX deben:
- Verificar nonce con `check_ajax_referer('gpai_nonce', 'nonce')`.
- Verificar `current_user_can('edit_posts')`.
- Responder con `wp_send_json_success()` / `wp_send_json_error()`.

---

## Capa de Datos (wp_options CRUD)

- `GPAI_USE_DATA_BASE` es la clase base para CRUD de opciones.
- Subclases: `GPAI_USE_DATA_CONFIG`, `GPAI_USE_DATA_DUPLICADOS`, `GPAI_USE_DATA_SITEMAPS`, `GPAI_USE_DATA_HTACCESS`, `GPAI_USE_DATA_GLOBAL_FIELDS`.
- No uses `get_option()`/`update_option()` directamente fuera de estas clases.

---

## wp_options Keys del Plugin

| Key | Propósito |
|---|---|
| `GPAI_CONFIG` | Config global: apikey, modelo, flags, prompts_base |
| `GPAI_CONTENT` | Variaciones de posts pendientes de generar |
| `GPAI_SITEMAP_CONFIGS` | Config de sitemaps: enabled_posts, changefreq, priority |
| `GPAI_GLOBAL_FIELDS_INDEX` | Índice de campos globales |
| `GPAI_GLOBAL_FIELDS_{key}` | Valor de campo global individual |

---

## REST API

- Namespace: `GPAI_KEY` (`'GPAI'`).
- Endpoints:
  - `POST /GPAI/seo` → `GPAI_API_SEO::handleRequest()` — Guarda campos GPAI SEO.
  - `GET /GPAI/cf/get` → `GPAI_API_CF::handleGet()` — Obtiene custom fields.
  - `POST /GPAI/cf/set` → `GPAI_API_CF::handleSet()` — Guarda custom fields.
  - `GET /GPAI/gf/get` → `GPAI_API_GF::handleGet()` — Obtiene campos globales.
  - `POST /GPAI/gf/set` → `GPAI_API_GF::handleSet()` — Guarda campos globales.
- Autenticación vía header `X-GPAI-{type}-Key`, configurable en admin "API".

## GPAI_ANALISIS (`src/api/analisis.php`)

- `analyzeSEO_ajax()` — Analiza título, descripción, focus keyword, OG, Twitter, Schema del post.
- `validateLinks_ajax()` — Extrae enlaces internos del contenido + Elementor y verifica HTTP status.
- `pageSpeed_ajax()` — Consulta Google PageSpeed Insights API públicamente.

## Meta Boxes Adicionales

### GPAI Parent (`src/meta-box/gpai-parent.php`)
- Meta box "GPAI Parent" en todos los post types públicos.
- Muestra el ID del post padre y enlace de edición.
- Checkbox "Cargar contenido del padre" guarda `GPAI_CONTENT_INDEPENDIENTE_META` como `'1'`/`'0'`.

### GPAI Box (`src/meta-box/gpai-box.php`)
- Meta box "GPAI" con botón que abre el panel de edición frontend en una nueva pestaña.

## Frontend Edit Panel

- Activo via `wp_enqueue_scripts` → `GPAI_Edit_Assets()`.
- Se activa con parámetro URL `?GPAI_EDIT`.
- Assets: `src/js/gpai-edit.js`, `src/css/gpai-edit.css`.
- Permite editar campos personalizados directamente desde el frontend.

## AI Harness (`src/ai/harness.php`)

- `GPAI_AI_HARNESS` — Harness de pruebas para capturar y mockear respuestas de IA.
- Dispara hooks `gpai_ai_before_request` y `gpai_ai_after_request` durante las llamadas a Gemini.
- Usa filtro `gpai_ai_mock_response` para simular respuestas sin llamar a la API real.

## Templates

| Template | Archivo | Propósito |
|---|---|---|
| `GPAI_Table_Fields()` | `src/templates/table_fields.php` | Tabla genérica clave/valor |
| `GPAI_Custom_Fields()` | `src/templates/custom_fields.php` | Campos personalizados |
| `GPAI_Custom_Yoast()` | `src/templates/custom_yoast.php` | Campos Yoast |
| `GPAI_Custom_Gpai_Seo()` | `src/templates/custom_gpai_seo.php` | Campos GPAI SEO |
| `GPAI_Custom_Gpai_Seo_Grouped()` | `src/templates/custom_gpai_seo.php` | Campos GPAI SEO agrupados |
| `GPAI_Table_Post_By_Url()` | `src/templates/table_post_by_url.php` | Posts con checkbox |
| `GPAI_Imagenes_Post()` | `src/templates/imagenes_post.php` | Tabla de imágenes con preview |
| `GPAI_Analisis_Post()` | `src/templates/analisis.php` | Análisis SEO de un post |

## Elementor Integration

- Panel flotante de custom fields en editor Elementor.
- Assets: `src/js/elementor-editor.js` y `src/css/elementor-editor.css`.
- Hooks: `elementor/editor/after_enqueue_scripts`, `elementor/editor/after_enqueue_styles`.
- Los valores `{{key}}` se escanean de `_elementor_data` (serializado).
