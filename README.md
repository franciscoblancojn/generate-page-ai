# Generate Page AI 🚀

**Version:** 1.7.8 | **License:** GPLv2+

Generate Page AI es un plugin de WordPress que potencia tus páginas con **inteligencia artificial** 🤖. Conéctalo a Google Gemini, gestiona campos personalizados, datos Yoast SEO, datos **GPAI SEO** (con meta box, etiquetas `<head>` y Schema JSON-LD), crea variaciones de contenido en masa para posts y plantillas de Elementor, gestiona campos personalizados **directamente desde el editor de Elementor**, y cuenta con auto-actualizador vía GitHub.

---

## ✨ Características

- 🤖 **Generación con IA** — Usa Google Gemini para generar contenido único para campos personalizados, Yoast SEO, GPAI SEO y variables globales de plantillas.
- 📄 **Gestión de Posts** — Selecciona cualquier página, edita sus campos personalizados y metadatos Yoast, GPAI SEO, escribe prompts y genera variaciones de contenido al instante.
- 🧩 **Soporte para Elementor** — Detecta variables `{g{variable}}` en tus plantillas de Elementor, permite editarlas y generar variaciones.
- 🎨 **Campos Personalizados en Elementor** — Botón flotante "Campos" en el editor de Elementor. Abre un panel arrastrable tipo Navigator para **crear, editar y eliminar campos personalizados** en tiempo real mientras diseñas la página.
- 🔄 **Variaciones en Masa** — Genera múltiples variaciones de contenido desde un solo prompt. Revisa, aplica o descarta cada una.
- 📤 **Exportación/Importación JSON** — Exporta campos personalizados, datos Yoast, GPAI SEO y valores de plantillas a JSON. Impórtalos después con un clic.
- 🔗 **Reemplazo en Frontend** — Las variables `{g{key}}`, `{{key}}` y `__key__` se reemplazan automáticamente con sus valores al mostrar la página.
- 🖌️ **Crear Plantilla desde Variación** — Convierte una variación de contenido en una nueva plantilla de Elementor independiente.
- 🔍 **Vista Previa** — Previsualiza variaciones directamente en el editor de Elementor con los valores inyectados como parámetros.
- 🧠 **Prompts Base Editables** — Personaliza los prompts base que usa la IA para generar contenido, imágenes y variables globales. Acceso desde Configuración > Prompts Base.
- 🏷️ **GPAI SEO** — Sistema completo de SEO con 24 campos en 5 grupos (Principales, Robots, Open Graph, Twitter, Schema). Meta box en el editor de posts, salida de etiquetas `<head>`, Schema JSON-LD y **anulación de Yoast SEO**.
- 🔄 **Auto-Update vía GitHub** — El plugin se actualiza automáticamente desde GitHub Releases cuando hay una nueva versión.
- 📋 **Sistema de Logs** — Registro de actividad del plugin accesible desde la barra de administración.

---

## 📋 Requisitos

- WordPress 5.0+
- PHP 5.6+
- Plugin [Yoast Duplicate Post](https://wordpress.org/plugins/duplicate-post/) (obligatorio)
- Plugin [Elementor](https://wordpress.org/plugins/elementor/) (para funcionalidad de plantillas y editor visual)
- Clave de API de [Google Gemini](https://aistudio.google.com/)

---

## ⚙️ Instalación

1. Descarga el plugin y súbelo a `/wp-content/plugins/generate-page-ai/`.
2. Actívalo desde el menú **Plugins** de WordPress.
3. Ve a **Generate Page AI → Configuración** e ingresa tu **API Key de Gemini**.
4. ¡Listo! Comienza a gestionar posts y plantillas. 🎉

---

## 🗂️ Estructura del Plugin

```
generate-page-ai/
├── index.php                     # Archivo principal (plugin header, constantes, updater)
├── update.php                    # Auto-actualizador vía GitHub
├── composer.json                 # Dependencias Composer
├── package.json                  # Scripts de release/versionado
├── libs/                         # Dependencias (Composer vendor renombrado)
├── src/
│   ├── _.php                     # Cargador maestro
│   ├── ai/                       # Capa de IA (cliente Gemini, generación de contenido)
│   │   ├── _.php
│   │   ├── ai.php                # GPAI_AI - Cliente HTTP para Gemini
│   │   ├── content.php           # GPAI_CONTENT - Orquestador de generación con templates editables
│   │   └── prompt.php            # GPAI_PROMPT - Mejora de prompts vía IA
│   ├── api/                      # API REST y handlers AJAX
│   │   ├── _.php
│   │   ├── cf.php                # GPAI_CF - CRUD de campos personalizados (incl. endpoints Elementor)
│   │   ├── yoast.php             # GPAI_YOAST - API para metadatos Yoast SEO
│   │   ├── gpai_seo.php          # GPAI_SEO - API para campos SEO personalizados
│   │   ├── cf_template.php       # GPAI_CF_TEMPLATE - API para variables {g{...}}
│   │   └── export_import.php     # GPAI_EXPORT_IMPORT - Exportación/Importación JSON
│   ├── css/                      # Estilos CSS inline
│   │   ├── global.php            # Estilos generales del admin
│   │   └── elementor-editor.css  # Estilos del panel flotante en editor Elementor
│   ├── data/                     # Persistencia de datos (opciones de WP)
│   │   ├── _.php
│   │   ├── base.php              # GPAI_USE_DATA_BASE - CRUD genérico con wp_options
│   │   ├── config.php            # GPAI_USE_DATA_CONFIG - Configuración del plugin
│   │   ├── duplicados.php        # GPAI_USE_DATA_DUPLICADOS - Variaciones de posts
│   │   └── templates_data.php    # GPAI_USE_DATA_TEMPLATES - Variaciones de plantillas
│   ├── elementor/                # Integración con Elementor
│   │   ├── _.php                 # Cargador condicional
│   │   ├── editor.php            # Encola assets en el editor de Elementor
│   │   └── frontend.php          # Filtros de reemplazo {{key}} en frontend de Elementor
│   ├── frontend/                 # Salida en frontend
│   │   └── gpai-seo-output.php   # GPAI_SEO_output - Etiquetas <head>, JSON-LD, anulación Yoast
│   ├── hook/                     # Hooks de WordPress
│   │   ├── _.php
│   │   └── content.php           # GPAI_replace_custom_vars() - filtro the_content
│   ├── js/                       # JavaScript
│   │   ├── global.php            # JS general del admin (tabs, modales, export/import)
│   │   └── elementor-editor.js   # Panel flotante de campos personalizados en Elementor
│   ├── meta-box/                 # Meta boxes en el editor de posts
│   │   └── gpai-seo.php          # GPAI SEO meta box (5 grupos, 24 campos, guardado AJAX)
│   ├── page/                     # Páginas del admin
│   │   ├── _.php
│   │   ├── add.php               # Registro del menú principal
│   │   ├── page.php              # (no usado)
│   │   └── pages/
│   │       ├── _.php
│   │       ├── config/           # Página de configuración
│   │       │   ├── add.php       # Submenú "Configuración"
│   │       │   └── page.php      # Layout con tabs: IA, Prompts Base, Pruebas
│   │       ├── post/             # Página de posts
│   │       └── plantillas/       # Página de plantillas
│   ├── prompts/                  # Archivos de texto con templates de prompts por defecto
│   │   ├── content-v1.txt        # Prompt original para contenido
│   │   ├── content-v2.txt        # Prompt actualizado para contenido (incluye GPAI SEO)
│   │   ├── content_img-v1.txt    # Prompt para generación de imágenes
│   │   └── template-v1.txt       # Prompt para variables globales de plantillas
│   ├── sections/                 # Secciones de cada página
│   │   ├── config.php            # API Key, modelo, toggle de imágenes
│   │   ├── prompts_base.php      # Editor de prompts base (templates editables)
│   │   ├── test.php              # Pruebas (dev mode)
│   │   ├── post.php              # Gestión de posts
│   │   ├── procesar_contenido.php# Variaciones de contenido
│   │   ├── plantillas.php        # Gestión de plantillas
│   │   └── procesar_plantillas.php# Variaciones de plantillas
│   └── templates/                # Helpers de renderizado
│       ├── _.php
│       ├── respond.php           # GPAI_Respond() - Mensajes de estado
│       ├── tooltip.php           # GPAI_Tooltip() - Tooltips
│       ├── collapse.php          # GPAI_Collapse() - Acordeones
│       ├── table_fields.php      # GPAI_Table_Fields() - Tabla genérica clave/valor
│       ├── custom_fields.php     # GPAI_Custom_Fields() - Campos personalizados
│       ├── custom_yoast.php      # GPAI_Custom_Yoast() - Campos Yoast
│       ├── custom_gpai_seo.php   # GPAI_Custom_Gpai_Seo() - Campos GPAI SEO
│       └── global_fields.php     # GPAI_Global_Fields() - Variables globales {g{...}}
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
| `GPAI_SEO` | `src/api/gpai_seo.php` | 🏷️ API para 24 campos SEO personalizados en 5 grupos |
| `GPAI_CF_TEMPLATE` | `src/api/cf_template.php` | 🧩 API para variables globales `{g{...}}` de plantillas |
| `GPAI_EXPORT_IMPORT` | `src/api/export_import.php` | 📤 Exportación/Importación JSON |
| `GPAI_USE_DATA_BASE` | `src/data/base.php` | 💾 CRUD genérico basado en `wp_options` |
| `GPAI_USE_DATA_CONFIG` | `src/data/config.php` | ⚙️ Configuración del plugin |
| `GPAI_USE_DATA_DUPLICADOS` | `src/data/duplicados.php` | 📝 Variaciones de posts pendientes |
| `GPAI_USE_DATA_TEMPLATES` | `src/data/templates_data.php` | 📐 Configuración y variaciones de plantillas |

---

## 🖥️ Páginas del Admin

| Menú | Slug | Descripción |
|------|------|-------------|
| ⚙️ **Configuración** | `GPAI_config` | API Key de Gemini, selección de modelo, toggle de generación de imágenes |
| 🧠 **Prompts Base** | `GPAI_config` (tab) | Editor de templates base para generación de contenido, imágenes y variables globales |
| 🧪 **Pruebas** | `GPAI_config` (tab, solo dev) | Pruebas de parseo JSON (solo visible en modo desarrollo) |
| 📄 **Post** | `GPAI_post` | Gestión de posts: campos personalizados, Yoast, GPAI SEO, prompts, variaciones |
| 🧩 **Plantillas** | `GPAI_plantilllas` | Gestión de plantillas Elementor: variables globales, prompts, variaciones |

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

Las variables `{{key}}` y `__key__` en widgets de Elementor se reemplazan automáticamente al mostrar la página mediante el filtro global `the_content`.

> ℹ️ Los hooks específicos de Elementor (`elementor/frontend/the_content`, `elementor/widget/render_content`) están actualmente **desactivados** en el código. El reemplazo funciona a través del filtro `the_content` estándar.

---

## 🏷️ GPAI SEO

El plugin incluye un **sistema completo de SEO** propio que puede funcionar junto con Yoast SEO o reemplazar sus valores.

### Meta Box en el Editor

Se agrega una meta box **"Gpai SEO"** en todos los post types públicos con 24 campos organizados en 5 grupos:

| Grupo | Campos |
|-------|--------|
| **Principales** | `title`, `description`, `canonical`, `redirect` |
| **Robots** | `noindex`, `nofollow`, `noarchive`, `nosnippet` |
| **Open Graph** | `og_title`, `og_description`, `og_image`, `og_url`, `og_type`, `og_site_name` |
| **Twitter** | `twitter_title`, `twitter_description`, `twitter_image`, `twitter_card` |
| **Schema** | `schema_type`, `schema_name`, `schema_description`, `schema_image`, `schema_url` |

Los campos se guardan vía **AJAX** (sin recargar la página) o mediante `save_post`.

### Salida en Frontend

El hook `wp_head` genera automáticamente:
- `<meta name="description">`
- `<link rel="canonical">`
- Open Graph tags (`<meta property="og:...">`)
- Twitter Card tags (`<meta name="twitter:...">`)
- Robots meta (`<meta name="robots">`)
- **JSON-LD Schema** (`WebPage`, `WebSite`, `Organization` con `SearchAction`)
- **Redirección 301** si `gpai_wpseo_redirect` está configurado

### Anulación de Yoast SEO

Cuando los campos GPAI SEO tienen valor, el plugin **anula automáticamente** los valores equivalentes de Yoast SEO mediante filtros (`wpseo_title`, `wpseo_metadesc`, `wpseo_canonical`, `wpseo_opengraph_*`, `wpseo_twitter_*`, `wpseo_robots`, `document_title_parts`).

---

## 🧠 Prompts Base Editables

Los prompts que la IA utiliza para generar contenido ahora son **totalmente editables** desde Configuración > Prompts Base.

| Template | Método | Archivo por Defecto | Placeholders |
|----------|--------|---------------------|--------------|
| **Contenido (v2)** | `getPrompt()` | `prompts/content-v2.txt` | `{{title}}`, `{{customFields}}`, `{{customFields_prompt}}`, `{{yoastFields}}`, `{{yoastFields_prompt}}`, `{{gpaiSeoFields}}`, `{{gpaiSeoFields_prompt}}`, `{{prompt}}` |
| **Imagen** | `getPromptImg()` | `prompts/content_img-v1.txt` | `{{title}}`, `{{customFields}}`, `{{yoastFields}}`, `{{gpaiSeoFields}}`, `{{imageUrl}}` |
| **Plantillas** | `getContentTemplate()` | `prompts/template-v1.txt` | `{{title}}`, `{{globalFields}}`, `{{globalFields_prompt}}`, `{{prompt}}` |

Los valores predeterminados se leen de archivos `.txt` en `src/prompts/`. Cada template incluye un botón **"Restaurar predeterminado"** para volver al valor de fábrica. Actualmente hay 4 archivos de prompt (incluyendo `content-v1.txt` como respaldo legacy).

> ⚠️ **Aviso:** Esta sección es de alto nivel. Usuarios no experimentados no deben modificar estos templates.

---

## 🧩 Variables Globales `{g{variable}}`

Las plantillas de Elementor pueden contener marcadores `{g{nombre_variable}}`. El plugin:

1. 🕵️ **Detección automática** — Escanea `_elementor_data` y encuentra todas las variables.
2. ✏️ **Edición centralizada** — Muestra todas las variables en una tabla para asignar valores y prompts personalizados.
3. 🔄 **Herencia de valores** — Los posts que usan la plantilla pueden sobrescribir valores individuales.
4. 🔗 **Reemplazo en Frontend** — `{g{key}}` se reemplaza por `_g_{key}` (valor por defecto) o `global_{key}` (valor del post).

---

## 📤 Exportación / Importación

Cada sección (Post y Plantillas) tiene botones para:
- **Exportar** — Descarga un archivo JSON con los valores actuales.
- **Importar** — Sube un JSON o pégalo en el área de texto para restaurar valores.

---

## 🔌 Hooks

### Filtros de Contenido
- `the_content` → `GPAI_replace_custom_vars()` — Reemplaza `{{key}}`, `__key__` y `{g{key}}` con sus valores.

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
- `gpai_seo_schema` — Filtro para modificar la salida del Schema JSON-LD.
- `site_transient_update_plugins` — Integración con el auto-actualizador de GitHub.

### Acciones de WordPress
- `admin_menu` — Registro de menús y submenús.
- `add_meta_boxes` — Registro de la meta box GPAI SEO.
- `save_post` — Guardado tradicional de campos GPAI SEO.
- `elementor/editor/after_enqueue_scripts` — Carga de JS en el editor de Elementor.
- `elementor/editor/after_enqueue_styles` — Carga de CSS en el editor de Elementor.
- `wp_head` — Salida de etiquetas SEO y Schema JSON-LD.
- `template_redirect` — Manejo de redirección 301.

### AJAX
- `wp_ajax_gpai_export_post` — Exportar datos de un post.
- `wp_ajax_gpai_import_post` — Importar datos a un post.
- `wp_ajax_gpai_export_template` — Exportar datos de una plantilla.
- `wp_ajax_gpai_import_template` — Importar datos a una plantilla.
- `wp_ajax_gpai_save_custom_field` — Guardar/actualizar campo personalizado (usado desde Elementor).
- `wp_ajax_gpai_list_custom_fields` — Listar campos personalizados de un post.
- `wp_ajax_gpai_delete_custom_field` — Eliminar un campo personalizado.
- `wp_ajax_gpai_list_template_fields` — Listar variables `{g{...}}` de plantilla para un post.
- `wp_ajax_gpai_save_global_field` — Guardar un valor `global_` de plantilla para un post.
- `wp_ajax_gpai_seo_save` — Guardar campos GPAI SEO desde la meta box (AJAX).

---

## 🔐 Seguridad

- ✅ Todos los valores de campos usan `wp_kses_post()` para sanitizar HTML permitido.
- ✅ Las capacidades requeridas son `manage_options` y/o `edit_post`.
- ✅ Los nonces de WordPress se verifican en todas las peticiones AJAX (incluyendo GPAI SEO y Elementor).
- ✅ Las claves de variación se codifican en base64 para evitar roturas en formularios HTML.
- ✅ El panel de Elementor solo se activa si `ELEMENTOR_VERSION` está definido.
- ✅ Validación de JSON en importaciones antes de procesar.
- ✅ Sanitización específica por tipo de dato (`sanitize_text_field`, `sanitize_key`, `intval`, `esc_attr`, `esc_url`).

---

## 📄 Licencia

GPLv2+ — Ver [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html) para más detalles.

---

## 👤 Developer

- **Name:** Francisco Blanco
- **Website:** https://franciscoblanco.vercel.app/
- **Email:** blancofrancisco34@gmail.com
