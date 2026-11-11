# Generate Page AI — HOOKS.md

> Referencia completa de hooks, AJAX y REST del plugin (v2.11.0).
> Para contexto de arquitectura ver `CONTEXT.md`.

---

## 1. Acciones de WordPress

| Hook | Callback | Priority | Archivo | Propósito |
|---|---|---|---|---|
| `admin_menu` | closure | 10 | `src/page/add.php` | Registra menú principal |
| `admin_menu` | closure | 10 | `src/page/pages/{x}/add.php` | Registra submenús (config, post, html, sitemaps, htaccess, campos_globales, reemplazar, api) |
| `add_meta_boxes` | `GPAI_SEO_MetaBox_register` | 10 | `src/meta-box/gpai-seo.php` | Meta box GPAI SEO |
| `add_meta_boxes` | `GPAI_Parent_MetaBox_register` | 10 | `src/meta-box/gpai-parent.php` | Meta box GPAI Parent |
| `add_meta_boxes` | `GPAI_Box_MetaBox_register` | 10 | `src/meta-box/gpai-box.php` | Meta box GPAI Box |
| `save_post` | `GPAI_SEO_MetaBox_save` | 10 | `src/meta-box/gpai-seo.php` | Guardado tradicional campos SEO |
| `save_post` | `GPAI_Parent_MetaBox_save` | 10 | `src/meta-box/gpai-parent.php` | Guardado contenido independiente |
| `wp_head` | `GPAI_SEO_output` | 20 | `src/frontend/gpai-seo-output.php` | Meta tags + Schema JSON-LD |
| `wp_head` | `GPAI_SEO_maybe_start_canonical_buffer` | 0 | `src/frontend/gpai-seo-output.php` | Buffer para canonical duplicado |
| `template_redirect` | `GPAI_SEO_handle_redirect` | 1 | `src/frontend/gpai-seo-output.php` | Redirección 301 |
| `template_redirect` | `GPAI_SEO_remove_other_jsonld` | 0 | `src/frontend/gpai-seo-output.php` | Elimina otros JSON-LD |
| `template_redirect` | closure | 10 | `src/hook/content.php` | Reemplazo frontend de páginas hijas |
| `wp_enqueue_scripts` | `GPAI_Edit_Assets` | 10 | `src/frontend/gpai-edit.php` | Assets panel edición frontend |
| `elementor/editor/after_enqueue_scripts` | `GPAI_Elementor_Editor_Assets` | 10 | `src/elementor/editor.php` | JS panel Elementor |
| `elementor/editor/after_enqueue_styles` | `GPAI_Elementor_Editor_Assets` | 10 | `src/elementor/editor.php` | CSS panel Elementor |
| `admin_head` | closure | 10 | `src/meta-box/gpai-seo.php` | Estilos meta box SEO |
| `admin_footer` | `GPAI_SEO_MetaBox_script` | 10 | `src/meta-box/gpai-seo.php` | JS meta box SEO |
| `admin_footer` | `GPAI_Parent_admin_js` | 10 | `src/meta-box/gpai-parent.php` | JS meta box Parent |
| `admin_enqueue_scripts` | `GPAI_Parent_localize_script` | 10 | `src/meta-box/gpai-parent.php` | Localize JS parent |
| `admin_init` | `GPAI_EXPORT_IMPORT::init` | 10 | `src/api/export_import.php` | Registra AJAX export/import |
| `admin_init` | `GPAI_IMAGENES::init` | 10 | `src/api/imagenes.php` | Registra AJAX imágenes |
| `admin_init` | `GPAI_ANALISIS::init` | 10 | `src/api/analisis.php` | Registra AJAX análisis |
| `admin_init` | `GPAI_REEMPLAZAR::init` | 10 | `src/api/reemplazar.php` | Registra AJAX reemplazar |
| `rest_api_init` | `GPAI_API_SEO::registerRoutes` | 10 | `src/api/seo_api.php` | REST SEO |
| `rest_api_init` | `GPAI_API_CF::registerRoutes` | 10 | `src/api/cf_api.php` | REST custom fields |
| `rest_api_init` | `GPAI_API_GF::registerRoutes` | 10 | `src/api/gf_api.php` | REST global fields |

---

## 2. Filtros de WordPress

| Hook | Callback | Priority | Archivo | Propósito |
|---|---|---|---|---|
| `the_content` | `GPAI_replace_custom_vars` | 20 | `src/hook/content.php` | Reemplaza `{{key}}`/`__key__` |
| `wpseo_title` | `GPAI_SEO_override_yoast_title` | 20 | `src/frontend/gpai-seo-output.php` | Anula título Yoast |
| `wpseo_metadesc` | `GPAI_SEO_override_yoast_metadesc` | 20 | ídem | Anula meta desc Yoast |
| `wpseo_canonical` | `GPAI_SEO_override_yoast_canonical` | 20 | ídem | Anula canonical Yoast |
| `wpseo_opengraph_title` | `GPAI_SEO_override_yoast_og_title` | 20 | ídem | Anula OG title Yoast |
| `wpseo_opengraph_desc` | `GPAI_SEO_override_yoast_og_desc` | 20 | ídem | Anula OG desc Yoast |
| `wpseo_opengraph_image` | `GPAI_SEO_override_yoast_og_image` | 20 | ídem | Anula OG image Yoast |
| `wpseo_opengraph_url` | `GPAI_SEO_override_yoast_og_url` | 20 | ídem | Anula OG url Yoast |
| `wpseo_twitter_title` | `GPAI_SEO_override_yoast_twitter_title` | 20 | ídem | Anula Twitter title Yoast |
| `wpseo_twitter_description` | `GPAI_SEO_override_yoast_twitter_desc` | 20 | ídem | Anula Twitter desc Yoast |
| `wpseo_twitter_image` | `GPAI_SEO_override_yoast_twitter_image` | 20 | ídem | Anula Twitter image Yoast |
| `wpseo_robots` | `GPAI_SEO_override_yoast_robots` | 20 | ídem | Anula robots Yoast |
| `wpseo_robots_array` | `GPAI_SEO_override_yoast_robots_array` | 20 | ídem | Anula robots array Yoast |
| `wpseo_schema_graph` | `GPAI_SEO_clean_yoast_schema` | 100 | ídem | Limpia schema Yoast (`description_schema_fallback`) |
| `document_title_parts` | `GPAI_SEO_override_document_title` | 20 | ídem | Anula título document |
| `wp_robots` | `GPAI_SEO_remove_default_robots` | 15 | ídem | Remueve robots default |
| `site_transient_update_plugins` | closure (FWUUpdate) | 10 | `index.php` | Auto-updater GitHub |

> **Nota:** El filtro `elementor/frontend/the_content` y `elementor/widget/render_content` están desactivados (comentados) en `src/elementor/frontend.php`.

---

## 3. Filtro propio de Schema

| Hook | Firma | Utilidad |
|---|---|---|
| `gpai_seo_schema` | `apply_filters('gpai_seo_schema', $schema)` | Modifica el array `$schema` (con su `@graph`) antes de `wp_json_encode`. Aplicado en `src/frontend/gpai-seo-output.php`. |

---

## 4. Hooks de IA (harness)

Disparados por `GPAI_AI::request()` (`src/ai/ai.php`).

| Hook | Tipo | Args | Descripción |
|---|---|---|---|
| `gpai_ai_before_request` | acción | `($PROMPT, $data, $url)` | Antes de cada request a Gemini |
| `gpai_ai_after_request` | acción | `($PROMPT, $data, $url, $result)` | Después de cada request |
| `gpai_ai_mock_response` | filtro | `($default, $PROMPT)` | Si un callback retorna array, reemplaza la llamada real |

Usados por `GPAI_AI_HARNESS` (`src/ai/harness.php`):
- `startRecording()` / `stopRecording()` → captura request/response
- `setMockResponse($content, $status, $message)` → mock estático
- `setMockPipeline($responses)` → mock rotativo
- `clearMocks()` → elimina filtros mock
- `enableDetailedLogging()` → logs detallados vía `FWUSystemLog`

---

## 5. AJAX Endpoints

> Todos validan nonce `check_ajax_referer('gpai_nonce', 'nonce')` y capability. Responden con `wp_send_json_success`/`wp_send_json_error`.

| Action | Handler | Propósito |
|---|---|---|
| `gpai_export_post` | `GPAI_EXPORT_IMPORT::exportPost()` | Exporta post a JSON |
| `gpai_import_post` | `GPAI_EXPORT_IMPORT::importPost()` | Importa JSON a post |
| `gpai_export_global_fields` | `GPAI_EXPORT_IMPORT::exportGlobalFields()` | Exporta campos globales |
| `gpai_import_global_fields` | `GPAI_EXPORT_IMPORT::importGlobalFields()` | Importa campos globales |
| `gpai_seo_save` | `GPAI_SEO_save_ajax()` | Guarda SEO desde meta box |
| `gpai_seo_generate` | `GPAI_SEO::generateSEO_ajax()` | Genera SEO con IA |
| `gpai_seo_export` | `GPAI_SEO_export_ajax()` | Exporta SEO a JSON |
| `gpai_seo_import` | `GPAI_SEO_import_ajax()` | Importa SEO desde JSON |
| `gpai_save_custom_field` | `GPAI_CF::save_from_elementor_ajax()` | Guarda custom field |
| `gpai_list_custom_fields` | `GPAI_CF::list_custom_fields_ajax()` | Lista custom fields |
| `gpai_delete_custom_field` | `GPAI_CF::delete_custom_field_ajax()` | Elimina custom field |
| `gpai_search_parent_posts` | `GPAI_Parent_search_ajax()` | Busca posts padre |
| `gpai_html_generate` | `GPAI_SEO::generateHTML_ajax()` | Optimiza HTML con IA |
| `gpai_html_swap` | `GPAI_SEO::swapHTML_ajax()` | Alterna HTML normal/optimizado |
| `gpai_sitemap_generate` | `GPAI_SITEMAPS_API::generate()` | Genera XML con IA |
| `gpai_sitemap_save_generate` | `GPAI_SITEMAPS_API::saveAndGenerate()` | Guarda config + genera XML |
| `gpai_sitemap_save_xml` | `GPAI_SITEMAPS_API::saveXml()` | Escribe archivo XML |
| `gpai_imagenes_get` | `GPAI_IMAGENES::getImagesAjax()` | Imágenes del post con metadatos |
| `gpai_imagenes_save` | `GPAI_IMAGENES::saveImagesAjax()` | Guarda metadatos de imágenes |
| `gpai_analisis_seo` | `GPAI_ANALISIS::analyzeSEO_ajax()` | Análisis SEO del post |
| `gpai_analisis_links` | `GPAI_ANALISIS::validateLinks_ajax()` | Valida enlaces internos |
| `gpai_analisis_pagespeed` | `GPAI_ANALISIS::pageSpeed_ajax()` | PageSpeed Insights |
| `gpai_reemplazar_url` | `GPAI_REEMPLAZAR::reemplazarAjax()` | Search-replace en DB + redirect |
| `gpai_htaccess_generate` | `GPAI_REEMPLAZAR::generateHtaccessAjax()` | Genera contenido .htaccess para descarga manual (sin escribir) |

---

## 6. REST API

Namespace: `GPAI_KEY` (`'GPAI'`).

| Método | Ruta | Handler | Header API Key |
|---|---|---|---|
| `POST` | `/wp-json/GPAI/seo` | `GPAI_API_SEO::handleRequest()` | `X-GPAI-SEO-Key` |
| `GET` | `/wp-json/GPAI/cf/get` | `GPAI_API_CF::handleGet()` | `X-GPAI-CF-Key` |
| `POST` | `/wp-json/GPAI/cf/set` | `GPAI_API_CF::handleSet()` | `X-GPAI-CF-Key` |
| `GET` | `/wp-json/GPAI/gf/get` | `GPAI_API_GF::handleGet()` | `X-GPAI-GF-Key` |
| `POST` | `/wp-json/GPAI/gf/set` | `GPAI_API_GF::handleSet()` | `X-GPAI-GF-Key` |

Detalle: `doc/DOC-API-SEO.md` (SEO), secciones REST de `README.md`.

---

## 7. Filtros desactivados (código comentado)

```php
// src/api/yoast.php:155
// add_action('rest_api_init', ['GPAI_YOAST', 'init']);

// src/elementor/frontend.php:8-9
// add_filter('elementor/frontend/the_content', 'GPAI_replace_custom_vars');
// add_filter('elementor/widget/render_content', 'GPAI_replace_custom_vars_elementor_widget', 10, 2);
```