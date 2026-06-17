# Generate Page AI 🚀

**Version:** 2.7.0 | **License:** GPLv2+

Generate Page AI es un plugin de WordPress que potencia tus páginas con **inteligencia artificial** 🤖. Conéctalo a Google Gemini, gestiona campos personalizados, datos Yoast SEO, datos **GPAI SEO** (con meta box, etiquetas `<head>` y Schema JSON-LD), crea variaciones de contenido en masa para posts, gestiona campos personalizados **directamente desde el editor de Elementor**, optimiza HTML estático generado por **Static Page**, y cuenta con auto-actualizador vía GitHub.

---

## ✨ Características

- 🤖 **Generación con IA** — Usa Google Gemini para generar contenido único para campos personalizados, Yoast SEO y GPAI SEO.
- 📄 **Gestión de Posts** — Selecciona cualquier página, edita sus campos personalizados y metadatos Yoast, GPAI SEO, escribe prompts y genera variaciones de contenido al instante.
- 🎨 **Campos Personalizados en Elementor** — Botón flotante "Campos" en el editor de Elementor. Abre un panel arrastrable tipo Navigator para **crear, editar y eliminar campos personalizados** en tiempo real mientras diseñas la página.
- 🔄 **Variaciones en Masa** — Genera múltiples variaciones de contenido desde un solo prompt. Revisa, aplica o descarta cada una.
- 📤 **Exportación/Importación JSON** — Exporta campos personalizados, datos Yoast y GPAI SEO a JSON. Impórtalos después con un clic.
- 🔗 **Reemplazo en Frontend** — Las variables `{{key}}` y `__key__` se reemplazan automáticamente con sus valores al mostrar la página.
- 🔍 **Vista Previa** — Previsualiza variaciones directamente en el editor de Elementor con los valores inyectados como parámetros.
- 🧠 **Prompts Base Editables** — Personaliza los prompts base que usa la IA para generar contenido, SEO y optimización HTML. Acceso desde Configuración > Prompts Base.
- 🏷️ **GPAI SEO** — Sistema completo de SEO con 27 campos en 5 grupos (Principales, Robots, Open Graph, Twitter, Schema). Meta box en el editor de posts, salida de etiquetas `<head>`, Schema JSON-LD con soporte para **bloques adicionales** (`gpai_wpseo_schema_extra_json` generado por IA), **anulación de Yoast SEO**, y botón **Validar SEO** que abre Schema.org validator con la URL del post.
- 🔄 **Auto-Update vía GitHub** — El plugin se actualiza automáticamente desde GitHub Releases cuando hay una nueva versión.
- 📋 **Sistema de Logs** — Registro de actividad del plugin accesible desde la barra de administración.
- 🧹 **Optimización HTML** — Subpágina "Optimización HTML" (visible solo si **Static Page** está activo). Selecciona un post (con selección persistente entre cargas), verifica si tiene HTML estático generado por Static Page, y permite **mejorar el HTML con IA** usando Gemini para optimizarlo (más liviano, misma apariencia). Guarda el resultado como `page-{id}-optimize.html` y registra la ruta en `STPA_PAGE_STATIC_HTML_FILE_OPTIMIZE`. Incluye un botón para **alternar entre HTML normal y optimizado** intercambiando la ruta activa.
- 🗺️ **Site Maps** — Subpágina completa para gestionar archivos XML de sitemaps en la raíz de WordPress. Lectura, edición, creación y eliminación de archivos. Generación de contenido XML con IA usando el prompt base de sitemaps más las URLs reales del sitio.
- 🔗 **URLs para Sitemaps** — Pestaña dentro de Site Maps que lista todos los posts y páginas publicadas con checkboxes para activar/desactivar su inclusión. Genera XML con `<url><loc><lastmod><changefreq><priority>`. Configuración persistente de frecuencia y prioridad por tipo de contenido.
- 🖼️ **Imágenes en Sitemaps** — Escaneo automático de imágenes destacadas, contenido y galerías de productos para cada URL. Se inyectan en el prompt de IA para generar `<image:image>` tags en el sitemap.
- 🖼️ **Ajustes de Imágenes** — Nueva pestaña en la sección Post que carga todas las imágenes del post seleccionado (destacada, contenido, Elementor, galerías) y permite editar **Texto Alternativo, Título, Leyenda y Descripción** con vista previa y botón de descarga.
- ✅ **Confirmación en Generar SEO** — El botón "Generar SEO con IA" ahora pide confirmación antes de sobrescribir los valores actuales.
- 🧼 **Limpieza de Schema Yoast** — Filtro automático que remueve propiedades internas no estándar (`description_schema_fallback`) del schema de Yoast antes de renderizar.

---

## 📋 Requisitos

- WordPress 5.0+
- PHP 5.6+
- Plugin [Yoast Duplicate Post](https://wordpress.org/plugins/duplicate-post/) (obligatorio)
- Plugin [Elementor](https://wordpress.org/plugins/elementor/) (para funcionalidad de plantillas y editor visual)
- Clave de API de [Google Gemini](https://aistudio.google.com/)

---

## ⚙️ Instalación

1. Descarga el plugin desde [Aqui](https://github.com/franciscoblancojn/generate-page-ai/archive/refs/heads/master.zip).
2. Subelo y Actívalo desde el menú **Plugins** de WordPress.
3. Ve a **Generate Page AI → Configuración** e ingresa tu **API Key de Gemini**.
4. ¡Listo! Comienza a gestionar posts. 🎉

---

## 🗂️ Estructura del Plugin

```
generate-page-ai/
├── index.php                     # Archivo principal (plugin header, constantes, updater vía Composer)
├── composer.json                 # Dependencias Composer
├── package.json                  # Scripts de release/versionado
├── libs/                         # Dependencias (Composer vendor renombrado)
├── src/
│   ├── _.php                     # Cargador maestro
│   ├── ai/                       # Capa de IA (cliente Gemini, generación de contenido)
│   │   ├── ai.php                # GPAI_AI - Cliente HTTP para Gemini
│   │   ├── content.php           # GPAI_CONTENT - Orquestador de generación con templates editables
│   │   └── prompt.php            # GPAI_PROMPT - Mejora de prompts vía IA
│   ├── api/                      # API REST y handlers AJAX
│   │   ├── cf.php                # GPAI_CF - CRUD de campos personalizados (incl. endpoints Elementor)
│   │   ├── yoast.php             # GPAI_YOAST - API para metadatos Yoast SEO
│   │   ├── gpai_seo.php          # GPAI_SEO - API para campos SEO personalizados
│   │   ├── export_import.php     # GPAI_EXPORT_IMPORT - Exportación/Importación JSON
│   │   ├── sitemaps.php          # GPAI_SITEMAPS_API - AJAX para generar XML de sitemaps con IA
│   │   └── imagenes.php          # GPAI_IMAGENES - AJAX para gestionar metadatos de imágenes del post
│   ├── css/                      # Estilos CSS inline
│   │   ├── global.php            # Estilos generales del admin
│   │   └── elementor-editor.css  # Estilos del panel flotante en editor Elementor
│   ├── data/                     # Persistencia de datos (opciones de WP)
│   │   ├── base.php              # GPAI_USE_DATA_BASE - CRUD genérico con wp_options
│   │   ├── config.php            # GPAI_USE_DATA_CONFIG - Configuración del plugin
│   │   ├── duplicados.php        # GPAI_USE_DATA_DUPLICADOS - Variaciones de posts
│   │   ├── sitemaps_data.php     # GPAI_USE_DATA_SITEMAPS - CRUD de archivos XML de sitemaps
│   │   ├── global_fields_data.php# GPAI_USE_DATA_GLOBAL_FIELDS - Campos globales
│   │   └── htaccess_data.php     # GPAI_USE_DATA_HTACCESS - CRUD de archivos .htaccess
│   ├── elementor/                # Integración con Elementor
│   │   ├── editor.php            # Encola assets en el editor de Elementor
│   │   └── frontend.php          # Filtros de reemplazo {{key}} en frontend de Elementor
│   ├── frontend/                 # Salida en frontend
│   │   └── gpai-seo-output.php   # GPAI_SEO_output - Etiquetas <head>, JSON-LD, anulación Yoast
│   ├── hook/                     # Hooks de WordPress
│   │   └── content.php           # GPAI_replace_custom_vars() - filtro the_content
│   ├── js/                       # JavaScript
│   │   ├── global.php            # JS general del admin (tabs, modales, export/import)
│   │   └── elementor-editor.js   # Panel flotante de campos personalizados en Elementor
│   ├── meta-box/                 # Meta boxes en el editor de posts
│   │   └── gpai-seo.php          # GPAI SEO meta box (5 grupos, 27 campos, guardado AJAX)
│   ├── page/                     # Páginas del admin
│   │   ├── add.php               # Registro del menú principal
│   │   ├── page.php              # (no usado)
│   │   ├── pages/
│   │   │   ├── config/           # Página de configuración
│   │   │   │   ├── add.php       # Submenú "Configuración"
│   │   │   │   └── page.php      # Layout con tabs: IA, Prompts Base, Pruebas
│   │   │   ├── post/             # Página de posts
│   │   │   │   ├── add.php       # Submenú "Post"
│   │   │   │   └── page.php      # Layout con tabs
│   │   │   ├── html/             # Página de optimización HTML
│   │   │   │   ├── add.php       # Submenú "Optimización HTML" (solo si Static Page activo)
│   │   │   │   └── page.php      # Layout con tabs
│   │   │   ├── sitemaps/         # Página de Site Maps
│   │   │   │   ├── add.php       # Submenú "Site Maps"
│   │   │   │   └── page.php      # Layout con tabs
│   │   │   ├── htaccess/         # Página de .htaccess
│   │   │   │   ├── add.php       # Submenú ".htaccess"
│   │   │   │   └── page.php      # Layout
│   │   │   └── campos_globales/  # Página de Campos Globales
│   │   │       ├── add.php       # Submenú "Campos Globales"
│   │   │       └── page.php      # Layout
│   │   └── sections/             # Secciones de cada página
│   │       ├── config.php        # API Key, modelo, toggle de imágenes
│   │       ├── prompts_base.php  # Editor de prompts base (templates editables)
│   │       ├── test.php          # Pruebas (dev mode)
│   │       ├── post.php          # Gestión de posts
│   │       ├── imagenes.php      # Ajustes de imágenes del post (alt, título, leyenda, descripción, descarga)
│   │       ├── procesar_contenido.php# Variaciones de contenido
│   │       ├── html.php          # Optimización HTML (selector de post, estado static, mejora con IA)
│   │       ├── sitemaps.php      # Site Maps: listado de archivos XML con edición y generación IA
│   │       ├── config-sitemaps.php# Site Maps: configuración de URLs por tipo de contenido
│   │       ├── crear_sitemap.php # Site Maps: formulario para crear nuevos archivos XML
│   │       ├── campos_globales.php# CRUD de campos globales
│   │       └── htaccess.php      # Editor de archivos .htaccess
│   └── templates/                # Helpers de renderizado
│       ├── table_fields.php      # GPAI_Table_Fields() - Tabla genérica clave/valor
│       ├── custom_fields.php     # GPAI_Custom_Fields() - Campos personalizados
│       ├── custom_yoast.php      # GPAI_Custom_Yoast() - Campos Yoast
│       ├── custom_gpai_seo.php   # GPAI_Custom_Gpai_Seo() - Campos GPAI SEO
│       ├── table_post_by_url.php # GPAI_Table_Post_By_Url() - Tabla de posts con checkboxes para sitemaps
│       └── imagenes_post.php     # GPAI_Imagenes_Post() - Tabla de imágenes con preview, campos editables y descarga
```

---

## 🧠 Clases Principales

| Clase | Archivo | Función |
|-------|---------|---------|
| `GPAI_AI` | `src/ai/ai.php` | 🛰️ Cliente HTTP para la API de Google Gemini |
| `GPAI_CONTENT` | `src/ai/content.php` | 🧬 Orquestador de generación de contenido con IA. Los prompts base son **editables** mediante templates con `{{placeholders}}` |
| `GPAI_PROMPT` | `src/ai/prompt.php` | 💡 Mejora de prompts existentes vía IA |
| `GPAI_CF` | `src/api/cf.php` | 📦 API para campos personalizados de posts. Incluye endpoints AJAX para el editor de Elementor |
| `GPAI_YOAST` | `src/api/yoast.php` | 🔍 API para metadatos Yoast SEO |
| `GPAI_SEO` | `src/api/gpai_seo.php` | 🏷️ API para 27 campos SEO personalizados en 5 grupos |
| `GPAI_USE_DATA_GLOBAL_FIELDS` | `src/data/global_fields_data.php` | 🌐 Campos globales almacenados en opciones |
| `GPAI_USE_DATA_HTACCESS` | `src/data/htaccess_data.php` | 🔒 CRUD de archivos .htaccess |
| `GPAI_EXPORT_IMPORT` | `src/api/export_import.php` | 📤 Exportación/Importación JSON |
| `GPAI_USE_DATA_BASE` | `src/data/base.php` | 💾 CRUD genérico basado en `wp_options` |
| `GPAI_USE_DATA_CONFIG` | `src/data/config.php` | ⚙️ Configuración del plugin |
| `GPAI_USE_DATA_DUPLICADOS` | `src/data/duplicados.php` | 📝 Variaciones de posts pendientes |
| `GPAI_USE_DATA_SITEMAPS` | `src/data/sitemaps_data.php` | 🗺️ CRUD de archivos XML de sitemaps en la raíz de WordPress |
| `GPAI_SITEMAPS_API` | `src/api/sitemaps.php` | 🤖 AJAX para generar XML de sitemaps con Gemini, reemplaza `{{URL_BASE}}`, `{{URL_PAGINAS_LIST}}`, `{{URL_POSTS_LIST}}`, `{{PAGINAS_IMAGES}}`, `{{POSTS_IMAGES}}` |
| `GPAI_IMAGENES` | `src/api/imagenes.php` | 🖼️ AJAX para obtener y guardar metadatos de imágenes del post (alt, título, leyenda, descripción) |

---

## 🖥️ Páginas del Admin

| Menú | Slug | Descripción |
|------|------|-------------|
| ⚙️ **Configuración** | `GPAI_config` | API Key de Gemini, selección de modelo, toggle de generación de imágenes |
| 🧠 **Prompts Base** | `GPAI_config` (tab) | Editor de templates base para generación de contenido, SEO y optimización HTML |
| 🧪 **Pruebas** | `GPAI_config` (tab, solo dev) | Pruebas de parseo JSON (solo visible en modo desarrollo) |
| 📄 **Post** | `GPAI_post` | Gestión de posts: campos personalizados, Yoast, GPAI SEO, prompts, variaciones, ajustes de imágenes |
| 🧹 **Optimización HTML** | `GPAI_html` | Optimización de HTML estático con IA (solo visible si **Static Page** está activo) |
| 🗺️ **Site Maps** | `GPAI_sitemaps` | Gestión de archivos XML de sitemaps. Tres pestañas: **Site Maps** (lista, editar, generar con IA, descargar), **Crear Site Map** (nuevo archivo XML), **URLs** (seleccionar posts/páginas, configurar frecuencia/prioridad, generar XML). |
| 🌐 **Campos Globales** | `GPAI_campos_globales` | CRUD de campos globales reutilizables (text, textarea, number, email, url, wysiwyg) |
| 🔒 **.htaccess** | `GPAI_htaccess` | Listar, editar, crear y eliminar archivos .htaccess |

---

## 🎨 Integración con Elementor

### Panel de Campos Personalizados en el Editor

El plugin agrega un botón flotante **"Campos"** en la esquina inferior del editor de Elementor (funciona en Elementor Free y Pro). Al hacer clic:

1. 📋 Se abre un **panel arrastrable** (similar al Navigator de Elementor)
2. 👁️ Muestra la **lista de campos personalizados** del post actual (excluye campos internos `_`)
3. ✏️ **Edita** cualquier campo: cambia clave o valor
4. ❌ **Elimina** campos con confirmación
5. ➕ **Crea nuevos campos** con clave (auto-envuelta en `{{ }}`) y valor

Los campos se guardan como `post_meta` inmediatamente vía AJAX.

### Reemplazo en Frontend

Las variables `{{key}}` y `__key__` se reemplazan automáticamente al mostrar la página mediante el filtro global `the_content`.

> ℹ️ Los hooks específicos de Elementor (`elementor/frontend/the_content`, `elementor/widget/render_content`) están actualmente **desactivados** en el código. El reemplazo funciona a través del filtro `the_content` estándar.

---

## 🏷️ GPAI SEO

El plugin incluye un **sistema completo de SEO** propio que puede funcionar junto con Yoast SEO o reemplazar sus valores.

### Meta Box en el Editor

Se agrega una meta box **"Gpai SEO"** en todos los post types públicos con 27 campos organizados en 5 grupos:

| Grupo | Campos |
|-------|--------|
| **Principales** | `title`, `metadesc`, `focuskw`, `focuskeywords`, `canonical`, `bctitle`, `redirect`, `cornerstone` |
| **Robots** | `noindex`, `nofollow`, `robots_adv`, `noarchive`, `nosnippet`, `noimageindex` |
| **Open Graph** | `og_title`, `og_description`, `og_image`, `og_image_id`, `og_url` |
| **Twitter** | `twitter_title`, `twitter_description`, `twitter_image` |
| **Schema** | `schema_page_type`, `schema_article_type`, `schema_extra_json` |

Los campos se guardan vía **AJAX** (sin recargar la página) o mediante `save_post`.

### Generación de SEO con IA

El meta box y la página de Post incluyen un botón **"Generar SEO con IA"** que envía el título, contenido y valores actuales a Gemini para generar datos SEO optimizados. Incluye **confirmación** antes de sobrescribir.

### Validación de Schema

El botón **"Validar SEO"** abre [Schema.org Validator](https://validator.schema.org/) con la URL del post actual para verificar que el JSON-LD generado sea válido.

### Limpieza de Schema de Yoast

El plugin filtra automáticamente el schema de Yoast (`wpseo_schema_graph`) para remover propiedades internas no estándar como `description_schema_fallback` que pueden causar errores de validación.

### Salida en Frontend

El hook `wp_head` genera automáticamente:
- `<meta name="description">`
- `<link rel="canonical">`
- Open Graph tags (`<meta property="og:...">`)
- Twitter Card tags (`<meta name="twitter:...">`)
- Robots meta (`<meta name="robots">`)
- **JSON-LD Schema** (`WebPage`, `WebSite`, `Organization` con `SearchAction`)
- **Redirección 301** si `gpai_wpseo_redirect` está configurado

### Schema JSON-LD — Estructura de Salida

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "WebPage",
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
      "@type": "WebSite",
      "@id": "{home}#website",
      "url": "{home}",
      "name": "{site_name}",
      "description": "{site_description}",
      "publisher": {"@id": "{home}#organization"},
      "potentialAction": [{"@type":"SearchAction", ...}]
    },
    {
      "@type": "Organization",
      "@id": "{home}#organization",
      "name": "{site_name}",
      "url": "{home}",
      "logo": {...},
      "image": {...}
    }
  ]
}
```

Los bloques adicionales definidos en `gpai_wpseo_schema_extra_json` se agregan al `@graph`. Cada objeto debe tener `@type` para ser incluido.

### Eliminación de Otros JSON-LD

Cuando `gpai_wpseo_remove_other_jsonld` está activo, el plugin elimina todos los `<script type="application/ld+json">` que no contengan la clase `gpai-seo-schema`, mediante output buffering en `template_redirect`.

### Anulación de Yoast SEO

Cuando los campos GPAI SEO tienen valor, el plugin **anula automáticamente** los valores equivalentes de Yoast SEO mediante filtros (`wpseo_title`, `wpseo_metadesc`, `wpseo_canonical`, `wpseo_opengraph_*`, `wpseo_twitter_*`, `wpseo_robots`, `document_title_parts`).

---

## 🧠 Prompts Base Editables

Los prompts que la IA utiliza para generar contenido ahora son **totalmente editables** desde Configuración > Prompts Base.

| Template | Método | Archivo por Defecto | Placeholders |
|----------|--------|---------------------|--------------|
| **Contenido (v2)** | `getPrompt()` | `prompts/content-v2.txt` | `{{title}}`, `{{customFields}}`, `{{customFields_prompt}}`, `{{yoastFields}}`, `{{yoastFields_prompt}}`, `{{gpaiSeoFields}}`, `{{gpaiSeoFields_prompt}}`, `{{prompt}}` |
| **SEO** | `getSEOBasePromptDefault()` | `prompts/seo-v1.txt` | `{{title}}`, `{{postContent}}`, `{{currentSeoFields}}`, `{{prompt}}` |
| **HTML** | `getHTMLBasePromptDefault()` | `prompts/html-v1.txt` | `{{htmlContent}}` |
| **Site Maps** | `getSitemapBasePrompt()` | `prompts/sitemap-v1.txt` | `{{sitemap_name}}`, `{{URL_BASE}}`, `{{URL_PAGINAS_LIST}}`, `{{URL_POSTS_LIST}}`, `{{PAGINAS_IMAGES}}`, `{{POSTS_IMAGES}}`, `{{custom_prompt}}` |

Los valores predeterminados se leen de archivos `.txt` en `src/prompts/`. Cada template incluye un botón **"Restaurar predeterminado"** para volver al valor de fábrica. Actualmente hay 5 archivos de prompt:

| Archivo | Propósito |
|---------|-----------|
| `content-v1.txt` | Prompt legacy para contenido |
| `content-v2.txt` | Prompt actualizado para contenido (incluye GPAI SEO) |
| `seo-v1.txt` | Prompt para generación de datos GPAI SEO con IA |
| `html-v1.txt` | Prompt para optimización de HTML estático con IA |
| `sitemap-v1.txt` | Prompt para generación de sitemaps XML con IA. Incluye placeholders para URLs reales e imágenes del sitio |

> ℹ️ Todos los prompts (incluyendo SEO y HTML) se pueden editar desde la UI de Prompts Base. Los valores guardados reemplazan a los archivos por defecto.

> ⚠️ **Aviso:** Esta sección es de alto nivel. Usuarios no experimentados no deben modificar estos templates.

---

## 📤 Exportación / Importación

Cada sección (Post) tiene botones para:
- **Exportar** — Descarga un archivo JSON con los valores actuales.
- **Importar** — Sube un JSON o pégalo en el área de texto para restaurar valores.

---

## 🧹 Optimización HTML

El plugin incluye una subpágina **"Optimización HTML"** (visible solo si el plugin [Static Page](https://github.com/franciscoblancojn/static-page/) está instalado y activo) que permite optimizar el HTML estático generado por Static Page usando inteligencia artificial.

### Funcionamiento

1. **Selecciona un post** del desplegable y haz clic en "Cargar Post". La selección se mantiene entre cargas.
2. Si el post tiene HTML estático (almacenado en `STPA_PAGE_STATIC_HTML_FILE`), se muestra:
   - Ruta y tamaño del archivo estático activo
   - Ruta y tamaño del archivo optimizado (si ya existe)
   - Botones: **Ver Post**, **Editar Post**, **Ver HTML Estático**, **Ver HTML Optimizado**, **Usar HTML Normal/Usar HTML Optimizado**
3. El botón **"Usar HTML Normal"** o **"Usar HTML Optimizado"** permite alternar entre ambas versiones intercambiando la ruta activa en `STPA_PAGE_STATIC_HTML_FILE`. Al recargar se refleja el cambio.
4. Si no tiene HTML estático, se muestra un aviso con un enlace para editar el post.
5. Haz clic en **"Mejorar HTML con IA"** para enviar el HTML a Gemini. El resultado se guarda como `page-{id}-optimize.html` y la ruta se almacena en `STPA_PAGE_STATIC_HTML_FILE_OPTIMIZE`.

### Prompt de Optimización

El archivo `src/prompts/html-v1.txt` contiene el prompt especializado que instruye a Gemini para:
- Reducir el peso del HTML sin alterar la apariencia visual
- Eliminar comentarios, espacios redundantes y markup muerto
- Optimizar CSS/JS inline (combinar reglas, acortar propiedades)
- Preservar URLs, scripts, estilos y meta tags necesarios

---

## 🌐 Campos Globales

El plugin incluye un sistema de **Campos Globales** accesible desde el submenú del mismo nombre. Permite crear variables reutilizables con los siguientes tipos:

| Tipo | Descripción |
|------|-------------|
| `text` | Texto plano |
| `textarea` | Texto multilínea |
| `number` | Valor numérico |
| `email` | Correo electrónico |
| `url` | URL |
| `wysiwyg` | Editor visual (TinyMCE) |

Los campos se almacenan como opciones de WordPress con prefijo `GPAI_GLOBAL_FIELDS_{key}` y un índice en `GPAI_GLOBAL_FIELDS_INDEX`. Se usan como valores de respaldo para `{{key}}` en el reemplazo de frontend.

---

## 🔒 Editor .htaccess

El submenú **.htaccess** permite gestionar archivos `.htaccess` desde el admin:
- Listar archivos `.htaccess` existentes en la raíz de WordPress
- Editar el contenido de cualquier archivo
- Crear nuevos archivos `.htaccess`
- Eliminar archivos existentes

---

## 🔌 Hooks

### Filtros de Contenido
- `the_content` → `GPAI_replace_custom_vars()` — Reemplaza `{{key}}` y `__key__` con sus valores.

### Filtros de Anulación Yoast (GPAI SEO)
- `wpseo_title` → `GPAI_SEO_override_yoast_title()` — Anula el título Yoast con el valor GPAI SEO.
- `wpseo_metadesc` → `GPAI_SEO_override_yoast_metadesc()` — Anula la meta descripción Yoast.
- `wpseo_canonical` → `GPAI_SEO_override_yoast_canonical()` — Anula el canonical Yoast.
- `wpseo_opengraph_title` → `GPAI_SEO_override_yoast_og_title()` — Anula OG title Yoast.
- `wpseo_opengraph_desc` → `GPAI_SEO_override_yoast_og_desc()` — Anula OG description Yoast.
- `wpseo_opengraph_image` → `GPAI_SEO_override_yoast_og_image()` — Anula OG image Yoast.
- `wpseo_opengraph_url` → `GPAI_SEO_override_yoast_og_url()` — Anula OG url Yoast.
- `wpseo_twitter_title` → `GPAI_SEO_override_yoast_twitter_title()` — Anula Twitter title Yoast.
- `wpseo_twitter_description` → `GPAI_SEO_override_yoast_twitter_desc()` — Anula Twitter description Yoast.
- `wpseo_twitter_image` → `GPAI_SEO_override_yoast_twitter_image()` — Anula Twitter image Yoast.
- `wpseo_robots` → `GPAI_SEO_override_yoast_robots()` — Anula robots Yoast.
- `document_title_parts` → `GPAI_SEO_override_document_title()` — Anula el título del documento.

### Otros Filtros
- `gpai_seo_schema` — Filtro para modificar la salida del Schema JSON-LD (aplica sobre el array `@graph` antes de `wp_json_encode`).
- `wpseo_robots_array` — `GPAI_SEO_override_yoast_robots_array()` — Anula robots array de Yoast.
- `wpseo_schema_graph` — `GPAI_SEO_clean_yoast_schema()` — Limpia propiedades internas no estándar (`description_schema_fallback`) del schema de Yoast.
- `site_transient_update_plugins` — Integración con el auto-actualizador de GitHub.

### Acciones de WordPress
- `admin_menu` — Registro de menús y submenús.
- `add_meta_boxes` — Registro de la meta box GPAI SEO.
- `save_post` — Guardado tradicional de campos GPAI SEO.
- `elementor/editor/after_enqueue_scripts` — Carga de JS en el editor de Elementor.
- `elementor/editor/after_enqueue_styles` — Carga de CSS en el editor de Elementor.
- `wp_head` — Salida de etiquetas SEO y Schema JSON-LD.
- `template_redirect` — Manejo de redirección 301 y eliminación de otros JSON-LD (output buffer).
- `wp_robots` — Eliminación de robots meta por defecto (priority 15).

### AJAX
- `wp_ajax_gpai_export_post` — Exportar datos de un post.
- `wp_ajax_gpai_import_post` — Importar datos a un post.
- `wp_ajax_gpai_save_custom_field` — Guardar/actualizar campo personalizado (usado desde Elementor).
- `wp_ajax_gpai_list_custom_fields` — Listar campos personalizados de un post.
- `wp_ajax_gpai_delete_custom_field` — Eliminar un campo personalizado.
- `wp_ajax_gpai_seo_save` — Guardar campos GPAI SEO desde la meta box (AJAX).
- `wp_ajax_gpai_seo_generate` — Generar datos SEO con IA (Gemini) para un post.
- `wp_ajax_gpai_seo_export` — Exportar campos GPAI SEO a JSON.
- `wp_ajax_gpai_seo_import` — Importar campos GPAI SEO desde JSON.
- `wp_ajax_gpai_html_generate` — Optimizar HTML estático con IA para un post.
- `wp_ajax_gpai_html_swap` — Alternar entre HTML normal y optimizado en un post.
- `wp_ajax_gpai_sitemap_generate` — Generar contenido XML de sitemap con IA usando Gemini. Reemplaza `{{URL_BASE}}`, `{{URL_PAGINAS_LIST}}`, `{{URL_POSTS_LIST}}`, `{{PAGINAS_IMAGES}}` y `{{POSTS_IMAGES}}` con datos reales del sitio.
- `wp_ajax_gpai_sitemap_save_generate` — Guardar configuración + generar XML de sitemap con IA.
- `wp_ajax_gpai_sitemap_save_xml` — Escribir archivo XML de sitemap en la raíz de WordPress.
- `wp_ajax_gpai_imagenes_get` — Obtener todas las imágenes de un post (destacada, adjuntas, contenido, Elementor, galerías) con sus metadatos actuales.
- `wp_ajax_gpai_imagenes_save` — Guardar metadatos de imágenes (alt, título, leyenda, descripción) de un post.

---

## 🔐 Seguridad

- ✅ Todos los valores de campos usan `wp_kses_post()` para sanitizar HTML permitido (excepto `gpai_wpseo_schema_extra_json` que usa `sanitize_textarea_field` para preservar JSON).
- ✅ Las capacidades requeridas son `manage_options` y/o `edit_post`.
- ✅ Los nonces de WordPress se verifican en todas las peticiones AJAX (incluyendo GPAI SEO y Elementor).
- ✅ Las claves de variación se codifican en base64 para evitar roturas en formularios HTML.
- ✅ El panel de Elementor solo se activa si `ELEMENTOR_VERSION` está definido.
- ✅ Validación de JSON en importaciones antes de procesar.
- ✅ Sanitización específica por tipo de dato (`sanitize_text_field`, `sanitize_key`, `intval`, `esc_attr`, `esc_url`).

---

## 📦 Constantes Globales

| Constante | Valor | Propósito |
|-----------|-------|-----------|
| `GPAI_KEY` | `'GPAI'` | Prefijo de opciones, meta keys y slugs |
| `GPAI_CONFIG` | `'GPAI_CONFIG'` | Opción de configuración del plugin |
| `GPAI_CONTENT` | `'GPAI_CONTENT'` | Variaciones de posts generadas |
| `GPAI_DIR` | `plugin_dir_path(__FILE__)` | Ruta absoluta del plugin |
| `GPAI_URL` | `plugin_dir_url(__FILE__)` | URL base del plugin |
| `GPAI_KEY_SEPARETE` | `'____GPAI____'` | Separador en valores de formularios |
| `GPAI_CONTENT_INDEPENDIENTE_META` | `'GPAI_CONTENT_INDEPENDIENTE'` | Post meta flag de contenido independiente |



---

## 🗄️ Post Meta Keys

### Sistema GPAI SEO (27 campos)
```
gpai_wpseo_active                 → '1'/'0'
gpai_wpseo_title                  → string
gpai_wpseo_metadesc               → string
gpai_wpseo_focuskw                → string
gpai_wpseo_focuskeywords          → string (JSON)
gpai_wpseo_canonical              → string (URL)
gpai_wpseo_bctitle                → string
gpai_wpseo_redirect               → string (URL)
gpai_wpseo_is_cornerstone         → '1'/'0'
gpai_wpseo_meta-robots-noindex    → '1'/'0'
gpai_wpseo_meta-robots-nofollow   → '1'/'0'
gpai_wpseo_meta-robots-adv        → string
gpai_wpseo_meta-robots-noarchive  → '1'/'0'
gpai_wpseo_meta-robots-nosnippet  → '1'/'0'
gpai_wpseo_meta-robots-noimageindex → '1'/'0'
gpai_wpseo_opengraph-title        → string
gpai_wpseo_opengraph-description  → string
gpai_wpseo_opengraph-image        → URL
gpai_wpseo_opengraph-image-id     → string (ID)
gpai_wpseo_opengraph-url          → URL
gpai_wpseo_twitter-title          → string
gpai_wpseo_twitter-description    → string
gpai_wpseo_twitter-image          → URL
gpai_wpseo_schema_page_type       → string
gpai_wpseo_schema_article_type    → string
gpai_wpseo_schema_extra_json      → string (JSON)
gpai_wpseo_remove_other_jsonld    → '1'/'0'
```

### Contenido independiente
| Meta Key | Propósito |
|----------|-----------|
| `GPAI_PARENT` | ID del post padre (contenido independiente) |
| `GPAI_CONTENT_INDEPENDIENTE` | `'1'` = tiene contenido propio, `'0'` = hereda |

---

## ⚙️ Opciones de WordPress (wp_options)

| Option Key | Propósito |
|------------|-----------|
| `GPAI_CONFIG` | Config global: API key, modelo, flags, prompts base |
| `GPAI_CONTENT` | Variaciones de posts pendientes de generar |
| `GPAI_SITEMAP_CONFIGS` | Config de sitemaps: URLs habilitadas, frecuencia, prioridad |
| `GPAI_GLOBAL_FIELDS_INDEX` | Índice de campos globales |
| `GPAI_GLOBAL_FIELDS_{key}` | Valor de campo global individual |

---

## 📄 Licencia

GPLv2+ — Ver [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html) para más detalles.

---

## 👤 Developer

- **Name:** Francisco Blanco
- **Website:** https://franciscoblanco.vercel.app/
- **Email:** blancofrancisco34@gmail.com
