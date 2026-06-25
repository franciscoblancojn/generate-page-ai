# Generate Page AI — Contexto para IAs

> Plug-in WordPress v2.9.2 — Generado automáticamente para que IAs entren en contexto rápido.

---

## ¿Qué hace este plugin?

Genera páginas y contenido usando Google Gemini. Permite:
- Crear variaciones de posts/páginas con IA
- Gestionar campos personalizados (`{{key}}`)
- SEO completo con meta tags, Open Graph, Twitter Cards, Schema JSON-LD (sistema propio `gpai_wpseo_*`)
- Sitemaps XML generados por IA
- Optimización de HTML estático con IA
- Panel de campos personalizados dentro del editor Elementor
- Export/Import de configuraciones en JSON
- Auto-update vía GitHub
- Ajustes de imágenes de posts (alt, título, leyenda, descripción, descarga)

---

## Constantes globales

| Constante | Valor | Dónde se usa |
|---|---|---|
| `GPAI_KEY` | `'GPAI'` | Prefijo de opciones, meta keys, slugs |
| `GPAI_CONFIG` | `'GPAI_CONFIG'` | `wp_options` → configuración del plugin |
| `GPAI_CONTENT` | `'GPAI_CONTENT'` | `wp_options` → variaciones de posts generadas |
| `GPAI_DIR` | `plugin_dir_path(__FILE__)` | Base del plugin |
| `GPAI_URL` | `plugin_dir_url(__FILE__)` | URL base del plugin |
| `GPAI_KEY_SEPARETE` | `'____GPAI____'` | Separador en valores de formularios |
| `GPAI_CONTENT_INDEPENDIENTE_META` | `'GPAI_CONTENT_INDEPENDIENTE'` | `post_meta` → flag contenido independiente |
| `GPAI_GENERACION_PAGINAS_CON_CONTENT_INDEPENDIENTE` | `'GPAI_GENERACION_PAGINAS_CON_CONTENT_INDEPENDIENTE'` | `wp_options` → flag generar páginas con contenido independiente |
| `GPAI_LOG` | `true` | Habilita logs del plugin |
| `GPAI_LOG_KEY` | `'GPAI_LOG'` | Clave para opción de logs |
| `GPAI_LOG_COUNT` | `100` | Máximo de entradas de log |
| `GPAI_BASENAME` | `plugin_basename(__FILE__)` | Base name del plugin |

---

## Estructura de archivos

```
index.php               → Plugin header, constantes, auto-updater GitHub (vía Composer)
libs/                   → Composer vendor (franciscoblancojn/wordpress_utils)
src/
  _.php                 → Cargador maestro (require de todos los módulos)
  ai/
    ai.php              → GPAI_AI: Cliente HTTP Google Gemini
    content.php         → GPAI_CONTENT: Orquestador generación contenido
    prompt.php          → GPAI_PROMPT: Mejora de prompts vía IA
    harness.php         → GPAI_AI_HARNESS: Harness de pruebas para respuestas IA
  api/
    cf.php              → GPAI_CF: CRUD custom fields + AJAX Elementor
    yoast.php           → GPAI_YOAST: API metadatos Yoast
    gpai_seo.php        → GPAI_SEO: API campos SEO personalizados
    export_import.php   → GPAI_EXPORT_IMPORT: Export/Import JSON (posts + global fields)
    sitemaps.php        → GPAI_SITEMAPS_API: Generación sitemaps con IA
    imagenes.php        → GPAI_IMAGENES: AJAX para obtener/guardar metadatos de imágenes del post
    analisis.php        → GPAI_ANALISIS: Análisis SEO, validación de enlaces, PageSpeed
    seo_api.php         → GPAI_API_SEO: REST API para campos SEO
    cf_api.php          → GPAI_API_CF: REST API para custom fields
    gf_api.php          → GPAI_API_GF: REST API para campos globales
  css/
    global.php          → Estilos admin inline
    elementor-editor.css → Estilos panel flotante Elementor
    gpai-edit.css       → Estilos panel edición frontend
    sitemap.xsl         → Hoja XSL para sitemaps XML
  data/
    base.php            → GPAI_USE_DATA_BASE: CRUD genérico wp_options
    config.php          → GPAI_USE_DATA_CONFIG: Config plugin
    duplicados.php      → GPAI_USE_DATA_DUPLICADOS: Variaciones posts
    sitemaps_data.php   → GPAI_USE_DATA_SITEMAPS: CRUD archivos XML
    htaccess_data.php   → GPAI_USE_DATA_HTACCESS: CRUD .htaccess
    global_fields_data.php → GPAI_USE_DATA_GLOBAL_FIELDS: Campos globales (opciones)
  elementor/
    editor.php          → Encola CSS/JS en editor Elementor
    frontend.php        → Filtros de reemplazo {{key}} (hoy desactivados)
  frontend/
    gpai-seo-output.php → Salida <head>: meta tags, JSON-LD, anulación Yoast
    gpai-edit.php       → GPAI_Edit_Assets(): assets panel edición frontend
  hook/
    content.php         → GPAI_replace_custom_vars(): reemplazo en frontend
  js/
    global.php          → JS admin: tabs, modales, export/import, SEO generate, HTML optimize/swap
    elementor-editor.js → Panel flotante de campos personalizados en Elementor
    gpai-edit.js        → JS panel edición frontend
  meta-box/
    gpai-seo.php        → Meta box GPAI SEO (5 grupos, 27 campos, guardado AJAX)
    gpai-parent.php     → Meta box GPAI Parent (contenido independiente)
    gpai-box.php        → Meta box GPAI Box (enlace a edición frontend)
  page/
    add.php             → add_menu_page('Generate Page AI')
    page.php            → Layout con tabs (Config, Post, Prompts Base, etc.)
    pages/
      config/           → Submenú "Configuración"
      post/             → Submenú "Post"
      html/             → Submenú "Optimización HTML"
      sitemaps/         → Submenú "Site Maps"
      htaccess/         → Submenú ".htaccess"
      campos_globales/  → Submenú "Campos Globales"
      api/              → Submenú "API"
    sections/
      config.php        → API Key, modelo Gemini, toggle contenido independiente
      post.php          → Gestión de posts (campos, prompts, generar)
      procesar_contenido.php → Revisar/generar variaciones de posts
      imagenes.php       → Ajustes de imágenes del post (alt, título, leyenda, descripción, descarga)
      html.php          → Optimización HTML estático
      sitemaps.php      → Lista/edita sitemaps XML
      config-sitemaps.php → Configurar URLs para sitemaps
      crear_sitemap.php → Crear nuevo sitemap XML
      prompts_base.php  → Editor de prompts base
      campos_globales.php → CRUD campos globales (opciones)
      test.php          → Pruebas (solo dev mode)
      htaccess.php      → Editor .htaccess
      analisis.php      → Análisis SEO, validación enlaces, PageSpeed
      api_seo.php       → Config API key para REST SEO
      api_cf.php        → Config API key para REST Custom Fields
      api_gf.php        → Config API key para REST Global Fields
  prompts/
    content-v1.txt      → Prompt legacy para contenido
    content-v2.txt      → Prompt para generar contenido (incluye GPAI SEO)
    content_img-v1.txt  → Prompt para generar imágenes
    seo-v1.txt          → Prompt para datos SEO
    html-v1.txt         → Prompt para optimizar HTML
    sitemap-v1.txt      → Prompt para sitemaps XML
  templates/
    table_fields.php    → GPAI_Table_Fields(): tabla genérica
    custom_fields.php   → GPAI_Custom_Fields(): campos personalizados
    custom_yoast.php    → GPAI_Custom_Yoast(): campos Yoast
    custom_gpai_seo.php → GPAI_Custom_Gpai_Seo(): campos GPAI SEO (+ grouped)
    table_post_by_url.php → GPAI_Table_Post_By_Url(): posts con checkbox
    imagenes_post.php   → GPAI_Imagenes_Post(): tabla de imágenes con preview, campos editables y descarga
    analisis.php        → GPAI_Analisis_Post(): análisis SEO de un post
```

---

## Clases y métodos clave

### GPAI_AI (`src/ai/ai.php`)
| Método | Descripción |
|---|---|
| `sendPrompt($PROMPT)` | Envía prompt a Gemini `generateContent`, devuelve texto |
| `getModels()` | Lista modelos Gemini que soportan `generateContent` |
| `parseJson($dataString)` | Parsea JSON de respuesta de IA (limpia ```json, extrae JSON válido) |
| `request($url, $method, $data)` | Llamada HTTP cURL a Gemini API |

### GPAI_CONTENT (`src/ai/content.php`)
| Método | Descripción |
|---|---|
| `getPrompt($CONFIG)` | Construye prompt reemplazando `{{title}}`, `{{customFields}}`, `{{yoastFields}}`, `{{gpaiSeoFields}}`, `{{prompt}}` |
| `getContent($CONFIG)` | Genera contenido: envía prompt, parsea JSON, normaliza campos |
| `getPromptImg($post_id, ...)` | Genera prompt para imágenes |
| `getBasePromptTemplate($type)` | Obtiene prompt base (guardado en opciones o archivo default) |
| `normalizeFields($item, $customFields, $yoastFields)` | Filtra campos generados por IA contra los permitidos |
| `getContentByPrompt($PROMPT)` | Envía prompt y parsea JSON de respuesta |

### GPAI_SEO (`src/api/gpai_seo.php`)
| Método | Descripción |
|---|---|
| `getFields()` | Retorna array `[key => label]` de 27 campos SEO |
| `GET($post_id)` | Lee todos los campos SEO del post como `[key => value]` |
| `SET($post_id, $data)` | Guarda campos SEO (sanitiza con `wp_kses_post` o `wp_json_encode`) |
| `getGroups()` | Retorna grupos: Principales, Robots, Open Graph, Twitter, Schema |
| `generateSEO($post_id, $promptText)` | Genera SEO completo con IA y guarda |
| `normalizeSchemaExtraJson($extra)` | Normaliza `schema_extra_json` (extrae de `@graph` si aplica) |
| `getSEOPrompt($post_id, $customPrompt)` | Construye prompt SEO con valores actuales |

### GPAI_SEO_output functions (`src/frontend/gpai-seo-output.php`)
| Función | Descripción |
|---|---|
| `GPAI_SEO_output()` | Hook `wp_head` priority 20: meta tags, JSON-LD, redirección |
| `GPAI_SEO_output_jsonld()` | Genera `<script class="gpai-seo-schema">`: WebPage, WebSite, Organization + extra JSON |
| `GPAI_SEO_override_yoast_*()` | 15+ filtros que anulan Yoast cuando GPAI SEO está activo |
| `GPAI_SEO_remove_other_jsonld()` | Hook `template_redirect` priority 0: output buffer para eliminar otros JSON-LD |

### GPAI_CF (`src/api/cf.php`)
| Método | Descripción |
|---|---|
| `GET($post_id)` | Escanea `post_content` + `_elementor_data`, extrae `{{key}}`, devuelve valores |
| `SET($post_id, $data)` | Guarda múltiples custom fields |
| `save_from_elementor_ajax()` | Guarda un campo desde Elementor |
| `list_custom_fields_ajax()` | Lista todos los custom fields del post |
### GPAI_USE_DATA_DUPLICADOS (`src/data/duplicados.php`)
| Método | Descripción |
|---|---|
| `generateVariation($post_id, $prompt, $v)` | Crea un post duplicado (usa `duplicate_post_create_duplicate()` de Yoast) |
| `generateAllVariations()` | Procesa todas las variaciones pendientes |
| `generateDuplicado(...)` | Lógica interna: duplica post + escribe custom fields + Yoast + GPAI SEO |

### GPAI_EXPORT_IMPORT (`src/api/export_import.php`)
| Método | Descripción |
|---|---|
| `exportPost()` | Exporta custom fields + Yoast + GPAI SEO a JSON |
| `importPost()` | Importa JSON a un post |

### GPAI_SITEMAPS_API (`src/api/sitemaps.php`)
| Método | Descripción |
|---|---|
| `generate()` | Genera XML de sitemap con IA: construye prompt con URLs reales, envía a Gemini |
| `saveAndGenerate()` | Guarda configuración + genera XML |
| `saveXml()` | Escribe archivo XML en la raíz de WordPress |
| `getPostImages($post_id)` | Extrae imágenes (destacada, contenido, galería producto) |

### GPAI_IMAGENES (`src/api/imagenes.php`)
| Método | Descripción |
|---|---|
| `getImagesAjax()` | AJAX: retorna todas las imágenes del post con metadatos (alt, título, leyenda, descripción, URLs) |
| `saveImagesAjax()` | AJAX: guarda metadatos de múltiples imágenes (alt, título, leyenda, descripción) |
| `getPostImages($post_id)` | Escanea post en busca de imágenes (destacada, adjuntas, contenido, Elementor, galería) y retorna array con datos completos |

---

## Post Meta Keys usados

### Sistema GPAI SEO (27 campos)
```
gpai_wpseo_active              → '1'/'0'
gpai_wpseo_title               → string
gpai_wpseo_metadesc            → string
gpai_wpseo_focuskw             → string
gpai_wpseo_focuskeywords       → string (JSON)
gpai_wpseo_canonical           → string (URL)
gpai_wpseo_bctitle             → string
gpai_wpseo_redirect            → string (URL)
gpai_wpseo_is_cornerstone      → '1'/'0'
gpai_wpseo_meta-robots-noindex → '1'/'0'
gpai_wpseo_meta-robots-nofollow → '1'/'0'
gpai_wpseo_meta-robots-adv     → string
gpai_wpseo_meta-robots-noarchive  → '1'/'0'
gpai_wpseo_meta-robots-nosnippet  → '1'/'0'
gpai_wpseo_meta-robots-noimageindex → '1'/'0'
gpai_wpseo_opengraph-title     → string
gpai_wpseo_opengraph-description → string
gpai_wpseo_opengraph-image     → URL
gpai_wpseo_opengraph-image-id  → string (ID)
gpai_wpseo_opengraph-url       → URL
gpai_wpseo_twitter-title       → string
gpai_wpseo_twitter-description → string
gpai_wpseo_twitter-image       → URL
gpai_wpseo_schema_page_type    → string (ej: "WebPage")
gpai_wpseo_schema_article_type → string (ej: "Article")
gpai_wpseo_schema_extra_json   → string (JSON con bloques Schema.org adicionales)
gpai_wpseo_remove_other_jsonld → '1'/'0'
```

### Static Page Integration
```
STPA_PAGE_STATIC_HTML_FILE          → Ruta al HTML estático activo
STPA_PAGE_STATIC_HTML_FILE_OPTIMIZE → Ruta al HTML optimizado por IA
STPA_KEY_CONFIG                     → Config de Static Page
```

---

## wp_options Keys

| Option Key | Clase | Propósito |
|---|---|---|
| `GPAI_CONFIG` | `GPAI_USE_DATA_CONFIG` | Config global: apikey, modelo, flags, prompts_base |
| `GPAI_CONTENT` | `GPAI_USE_DATA_DUPLICADOS` | Variaciones de posts pendientes de generar |
| `GPAI_SITEMAP_CONFIGS` | `GPAI_SITEMAPS_API` | Config de sitemaps: enabled_posts, changefreq, priority |
| `GPAI_SITEMAP_URLS` | (legacy) | URLs habilitadas para sitemap (reemplazado por GPAI_SITEMAP_CONFIGS) |
| `GPAI_GLOBAL_FIELDS_INDEX` | `GPAI_USE_DATA_GLOBAL_FIELDS` | Índice de campos globales |
| `GPAI_GLOBAL_FIELDS_{key}` | `GPAI_USE_DATA_GLOBAL_FIELDS` | Valor de campo global individual |

---

## AJAX Endpoints

| Action | Clase/Método | Propósito |
|---|---|---|
| `gpai_export_post` | `GPAI_EXPORT_IMPORT::exportPost()` | Exporta post completo a JSON |
| `gpai_import_post` | `GPAI_EXPORT_IMPORT::importPost()` | Importa JSON a post |
| `gpai_export_global_fields` | `GPAI_EXPORT_IMPORT::exportGlobalFields()` | Exporta campos globales a JSON |
| `gpai_import_global_fields` | `GPAI_EXPORT_IMPORT::importGlobalFields()` | Importa JSON a campos globales |
| `gpai_seo_save` | `GPAI_SEO_save_ajax()` | Guarda campos SEO desde meta box |
| `gpai_seo_generate` | `GPAI_SEO::generateSEO_ajax()` | Genera SEO con IA |
| `gpai_seo_export` | `GPAI_SEO_export_ajax()` | Exporta campos SEO a JSON |
| `gpai_seo_import` | `GPAI_SEO_import_ajax()` | Importa campos SEO desde JSON |
| `gpai_save_custom_field` | `GPAI_CF::save_from_elementor_ajax()` | Guarda custom field (desde Elementor) |
| `gpai_list_custom_fields` | `GPAI_CF::list_custom_fields_ajax()` | Lista custom fields del post |
| `gpai_delete_custom_field` | `GPAI_CF::delete_custom_field_ajax()` | Elimina custom field |
| `gpai_html_generate` | `GPAI_SEO::generateHTML_ajax()` | Optimiza HTML estático con IA |
| `gpai_html_swap` | `GPAI_SEO::swapHTML_ajax()` | Alterna entre HTML normal/optimizado |
| `gpai_sitemap_generate` | `GPAI_SITEMAPS_API::generate()` | Genera XML de sitemap con IA |
| `gpai_sitemap_save_generate` | `GPAI_SITEMAPS_API::saveAndGenerate()` | Guarda config + genera XML |
| `gpai_sitemap_save_xml` | `GPAI_SITEMAPS_API::saveXml()` | Escribe archivo XML |
| `gpai_imagenes_get` | `GPAI_IMAGENES::getImagesAjax()` | Obtiene imágenes del post con metadatos |
| `gpai_imagenes_save` | `GPAI_IMAGENES::saveImagesAjax()` | Guarda metadatos de imágenes (alt, título, leyenda, descripción) |
| `gpai_analisis_seo` | `GPAI_ANALISIS::analyzeSEO_ajax()` | Analiza SEO del post (títulos, desc, OG, keywords) |
| `gpai_analisis_links` | `GPAI_ANALISIS::validateLinks_ajax()` | Valida enlaces internos del post |
| `gpai_analisis_pagespeed` | `GPAI_ANALISIS::pageSpeed_ajax()` | Consulta PageSpeed Insights de la URL del post |

---

## Hooks de WordPress

### Acciones
```php
add_action('admin_menu', ...)                        → Registra menú principal y submenús
add_action('add_meta_boxes', 'GPAI_SEO_MetaBox_register') → Meta box GPAI SEO
add_action('add_meta_boxes', 'GPAI_Parent_MetaBox_register') → Meta box GPAI Parent
add_action('add_meta_boxes', 'GPAI_Box_MetaBox_register') → Meta box GPAI Box
add_action('save_post', 'GPAI_SEO_MetaBox_save')     → Guardado tradicional SEO
add_action('save_post', 'GPAI_Parent_MetaBox_save')  → Guardado contenido independiente
add_action('wp_head', 'GPAI_SEO_output', 20)         → Meta tags + Schema JSON-LD
add_action('template_redirect', 'GPAI_SEO_handle_redirect', 1) → Redirección 301
add_action('template_redirect', 'GPAI_SEO_remove_other_jsonld', 0) → Elimina otros JSON-LD
add_action('template_redirect', [closure], ...)      → Reemplazo frontend páginas hijas
add_action('elementor/editor/after_enqueue_scripts', 'GPAI_Elementor_Editor_Assets')
add_action('elementor/editor/after_enqueue_styles', 'GPAI_Elementor_Editor_Assets')
add_action('wp_enqueue_scripts', 'GPAI_Edit_Assets') → Assets panel edición frontend
add_action('wp_ajax_*', ...)                         → Todos los AJAX (ver tabla arriba)
add_action('admin_init', ['GPAI_EXPORT_IMPORT', 'init'])
add_action('admin_init', ['GPAI_IMAGENES', 'init'])
add_action('admin_init', ['GPAI_ANALISIS', 'init'])
add_action('rest_api_init', ['GPAI_API_SEO', 'registerRoutes'])
add_action('rest_api_init', ['GPAI_API_CF', 'registerRoutes'])
add_action('rest_api_init', ['GPAI_API_GF', 'registerRoutes'])
```

### Filtros
```php
add_filter('the_content', 'GPAI_replace_custom_vars', 20)  → Reemplaza {{key}}/__key__
add_filter('wpseo_title', 'GPAI_SEO_override_yoast_title', 20)
add_filter('wpseo_metadesc', 'GPAI_SEO_override_yoast_metadesc', 20)
add_filter('wpseo_canonical', 'GPAI_SEO_override_yoast_canonical', 20)
add_filter('wpseo_opengraph_title', 'GPAI_SEO_override_yoast_og_title', 20)
add_filter('wpseo_opengraph_desc', 'GPAI_SEO_override_yoast_og_desc', 20)
add_filter('wpseo_opengraph_image', 'GPAI_SEO_override_yoast_og_image', 20)
add_filter('wpseo_opengraph_url', 'GPAI_SEO_override_yoast_og_url', 20)
add_filter('wpseo_twitter_title', 'GPAI_SEO_override_yoast_twitter_title', 20)
add_filter('wpseo_twitter_description', 'GPAI_SEO_override_yoast_twitter_desc', 20)
add_filter('wpseo_twitter_image', 'GPAI_SEO_override_yoast_twitter_image', 20)
add_filter('wpseo_robots', 'GPAI_SEO_override_yoast_robots', 20)
add_filter('wpseo_robots_array', 'GPAI_SEO_override_yoast_robots_array', 20)
add_filter('wpseo_schema_graph', 'GPAI_SEO_clean_yoast_schema', 100)
add_filter('document_title_parts', 'GPAI_SEO_override_document_title', 20)
add_filter('wp_robots', 'GPAI_SEO_remove_default_robots', 15)
add_filter('gpai_seo_schema', ...)   → Filtro para modificar Schema JSON-LD antes de renderizar
```

### Filtro `gpai_seo_schema`
Disponible para hooks externos. Se aplica en `gpai-seo-output.php:185` sobre el array `$schema` completo antes de `wp_json_encode`. Permite modificar/añadir/eliminar cualquier nodo del `@graph`.

---

## IA: Google Gemini

- **API**: `https://generativelanguage.googleapis.com/v1/models/{model}:generateContent?key={apiKey}`
- **Modelo**: Configurable (default: primero que soporte `generateContent`)
- **Config**: `maxOutputTokens: 65536`, `temperature: 0.2`
- **Timeout**: 300 segundos
- La IA devuelve JSON que el plugin parsea con `GPAI_AI::parseJson()`
- Los prompts son templates editables desde admin (guardados en `GPAI_CONFIG['prompts_base']`)
- Fallback a archivos `src/prompts/*.txt` si no hay prompts guardados

---

## Flujo de datos: Generación de contenido

1. **Admin** → Selecciona post en "Post, Campos y Prompts"
2. **Carga** → `GPAI_CF::GET()` escanea post en busca de `{{key}}` (en `post_content` y `_elementor_data`)
3. **Edición** → Usuario asigna valores y prompts personalizados para cada campo
4. **Generación** → `GPAI_CONTENT::getContent()` construye prompt con template, envía a Gemini, parsea JSON
5. **Variaciones** → Resultado se guarda en `GPAI_CONTENT` (opción de WP) como array de variaciones
6. **Procesar** → En "Procesar Contenido", usuario revisa, previsualiza, y genera posts duplicados
7. **Duplicación** → `GPAI_USE_DATA_DUPLICADOS::generateVariation()` usa `duplicate_post_create_duplicate()` de Yoast, escribe custom fields + Yoast + GPAI SEO

---

## Flujo: Renderizado SEO en frontend

1. `wp_head` → `GPAI_SEO_output(20)`:
   - Lee `gpai_wpseo_*` de `post_meta`
   - Si `active !== '1'`, no hace nada
   - Output: `<meta name="description">`, `<link canonical>`, OG tags, Twitter tags, robots
   - Llama a `GPAI_SEO_output_jsonld()`
2. `GPAI_SEO_output_jsonld()`:
   - Construye `@graph`: WebPage + WebSite + Organization (built-in)
   - Lee `gpai_wpseo_schema_extra_json`, decodifica, aplica `GPAI_replace_custom_vars` sobre el array, y agrega bloques con `@type` al `@graph`
   - Aplica filtro `gpai_seo_schema`
   - Renderiza `<script type="application/ld+json" class="gpai-seo-schema">`
3. Anulación Yoast:
   - Filtros `wpseo_title`, `wpseo_metadesc`, `wpseo_canonical`, etc. → si el campo GPAI tiene valor, retorna ese valor
4. Eliminación otros JSON-LD (opcional):
   - Si `gpai_wpseo_remove_other_jsonld === '1'`, se inicia output buffering en `template_redirect` priority 0
   - El callback elimina todo `<script type="application/ld+json">` que no contenga `gpai-seo-schema`

---

## Schema JSON-LD: Estructura de salida

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "WebPage",                 // o gpai_wpseo_schema_page_type
      "@id": "{canonical}#webpage",
      "url": "{canonical}",
      "name": "{title}",
      "description": "{metadesc}",
      "inLanguage": "{site_language}",
      "isPartOf": {"@id": "{home}#website"},
      "publisher": {"@id": "{home}#organization"},
      "image": "{og_image}",
      "keywords": "{focuskw}",
      "datePublished": "...",
      "dateModified": "..."
    },
    {
      "@type": "WebSite",                 // Built-in
      "@id": "{home}#website",
      "url": "{home}",
      "name": "{site_name}",
      "description": "{site_description}",
      "publisher": {"@id": "{home}#organization"},
      "potentialAction": [{"@type":"SearchAction", ...}]
    },
    {
      "@type": "Organization",            // Built-in
      "@id": "{home}#organization",
      "name": "{site_name}",
      "url": "{home}",
      "logo": {...},
      "image": {...}
    },
    // ... bloques de gpai_wpseo_schema_extra_json (Service, FAQPage, etc.)
  ]
}
```

---

## Dependencias

- **WordPress** 5.0+
- **PHP** 5.6+
- **Yoast Duplicate Post** (obligatorio para duplicación)
- **Elementor** (opcional, para plantillas y panel editor)
- **Static Page** (opcional, para optimización HTML)
- **Google Gemini API Key** (obligatorio para generación IA)
- **Composer**: `franciscoblancojn/wordpress_utils` (FWUSystemLog)

---

## Notas importantes para debugging

- Todos los errores de IA se registran via `FWUSystemLog::add(GPAI_KEY, ...)` y son visibles desde la barra de admin de WP
- `wp_kses_post()` se aplica en casi todos los saves (puede corromper JSON si contiene `<` `>`)
- `GPAI_replace_custom_vars()` usa `get_the_ID()` — asegurar que el global `$post` esté correcto
- El schema extra JSON (`gpai_wpseo_schema_extra_json`) se procesa así: `json_decode` → `array_walk_recursive` con `GPAI_replace_custom_vars` → filtrar por `@type`
- Modo desarrollo: `GPAI_MODE_DEV` se activa si `$_SERVER['HTTP_HOST']` es `wordpress.local`, `localhost` o `127.0.0.1`
