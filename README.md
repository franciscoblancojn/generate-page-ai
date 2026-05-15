# Generate Page AI 🚀

**Version:** 1.4.0 | **License:** GPLv2+

Generate Page AI es un plugin de WordPress que potencia tus páginas con **inteligencia artificial** 🤖. Conéctalo a Google Gemini, gestiona campos personalizados, datos Yoast SEO, crea variaciones de contenido en masa para posts y plantillas de Elementor, y ahora también gestiona campos personalizados **directamente desde el editor de Elementor**.

---

## ✨ Características

- 🤖 **Generación con IA** — Usa Google Gemini para generar contenido único para campos personalizados, Yoast SEO y variables globales de plantillas.
- 📄 **Gestión de Posts** — Selecciona cualquier página, edita sus campos personalizados y metadatos Yoast, escribe prompts y genera variaciones de contenido al instante.
- 🧩 **Soporte para Elementor** — Detecta variables `{g{variable}}` en tus plantillas de Elementor, permite editarlas y generar variaciones.
- 🎨 **Campos Personalizados en Elementor** — Botón flotante "Campos" en el editor de Elementor. Abre un panel arrastrable tipo Navigator para **crear, editar y eliminar campos personalizados** en tiempo real mientras diseñas la página.
- 🔄 **Variaciones en Masa** — Genera múltiples variaciones de contenido desde un solo prompt. Revisa, aplica o descarta cada una.
- 📤 **Exportación/Importación JSON** — Exporta campos personalizados, datos Yoast y valores de plantillas a JSON. Impórtalos después con un clic.
- 🔗 **Reemplazo en Frontend** — Las variables `{g{key}}`, `{{key}}` y `__key__` se reemplazan automáticamente con sus valores al mostrar la página (incluyendo widgets de Elementor).
- 🖌️ **Crear Plantilla desde Variación** — Convierte una variación de contenido en una nueva plantilla de Elementor independiente.
- 🔍 **Vista Previa** — Previsualiza variaciones directamente en el editor de Elementor con los valores inyectados como parámetros.
- 🧠 **Prompts Base Editables** — Personaliza los prompts base que usa la IA para generar contenido, imágenes y variables globales. Acceso desde Configuración > Prompts Base.

---

## 📋 Requisitos

- WordPress 5.0+
- PHP 7.4+
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
│   │   ├── cf_template.php       # GPAI_CF_TEMPLATE - API para variables {g{...}}
│   │   └── export_import.php     # GPAI_EXPORT_IMPORT - Exportación/Importación JSON
│   ├── css/                      # Estilos CSS inline
│   │   ├── global.php            # Estilos generales del admin
│   │   └── elementor.php         # Estilos del panel flotante en editor Elementor
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
│   ├── hook/                     # Hooks de WordPress
│   │   ├── _.php
│   │   └── content.php           # GPAI_replace_custom_vars() - filtro the_content
│   ├── js/                       # JavaScript
│   │   ├── global.php            # JS general del admin (tabs, modales, export/import)
│   │   └── elementor.php         # Panel flotante de campos personalizados en Elementor
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
│   └── sections/                 # Secciones de cada página
│       ├── config.php            # API Key, modelo, toggle de imágenes
│       ├── prompts_base.php      # Editor de prompts base (templates editables)
│       ├── test.php              # Pruebas (dev mode)
│       ├── post.php              # Gestión de posts
│       ├── procesar_contenido.php# Variaciones de contenido
│       ├── plantillas.php        # Gestión de plantillas
│       └── procesar_plantillas.php# Variaciones de plantillas
```

---

## 🧠 Clases Principales

| Clase | Archivo | Función |
|-------|---------|---------|
| `GPAI_AI` | `src/ai/ai.php` | 🛰️ Cliente HTTP para la API de Google Gemini |
| `GPAI_CONTENT` | `src/ai/content.php` | 🧬 Orquestador de generación de contenido con IA. Los prompts base ahora son **editables** mediante templates con `{{placeholders}}` |
| `GPAI_PROMPT` | `src/ai/prompt.php` | 💡 Mejora de prompts existentes vía IA |
| `GPAI_CF` | `src/api/cf.php` | 📦 API para campos personalizados de posts. Incluye endpoints AJAX para el editor de Elementor |
| `GPAI_YOAST` | `src/api/yoast.php` | 🔍 API para metadatos Yoast SEO |
| `GPAI_CF_TEMPLATE` | `src/api/cf_template.php` | 🧩 API para variables globales `{g{...}}` de plantillas |
| `GPAI_EXPORT_IMPORT` | `src/api/export_import.php` | 📤 Exportación/Importación JSON |
| `GPAI_USE_DATA_*` | `src/data/*.php` | 💾 Clases de almacenamiento basadas en `wp_options` |

---

## 🖥️ Páginas del Admin

| Menú | Slug | Descripción |
|------|------|-------------|
| ⚙️ **Configuración** | `GPAI_config` | API Key de Gemini, selección de modelo, toggle de generación de imágenes |
| 🧠 **Prompts Base** | `GPAI_config` (tab) | Editor de templates base para generación de contenido, imágenes y variables globales |
| 📄 **Post** | `GPAI_post` | Gestión de posts: campos personalizados, Yoast, prompts, variaciones |
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

Las variables `{{key}}` y `__key__` en widgets de Elementor se reemplazan automáticamente al mostrar la página:
- Hook en `elementor/frontend/the_content`
- Hook en `elementor/widget/render_content`

---

## 🧠 Prompts Base Editables

Los prompts que la IA utiliza para generar contenido ahora son **totalmente editables** desde Configuración > Prompts Base.

| Template | Método | Placeholders |
|----------|--------|--------------|
| **Contenido** | `getPrompt()` | `{{title}}`, `{{customFields}}`, `{{customFields_prompt}}`, `{{yoastFields}}`, `{{yoastFields_prompt}}`, `{{prompt}}` |
| **Imagen** | `getPromptImg()` | `{{title}}`, `{{customFields}}`, `{{yoastFields}}`, `{{imageUrl}}` |
| **Plantillas** | `getContentTemplate()` | `{{title}}`, `{{globalFields}}`, `{{globalFields_prompt}}`, `{{prompt}}` |

Los valores predeterminados son los prompts originales del plugin. Cada template incluye un botón **"Restaurar predeterminado"** para volver al valor de fábrica.

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

### Filtros
- `the_content` → `GPAI_replace_custom_vars()` — Reemplaza `{{key}}`, `__key__` y `{g{key}}` con sus valores.
- `elementor/frontend/the_content` → `GPAI_replace_custom_vars()` — Reemplazo en contenido de Elementor.
- `elementor/widget/render_content` → `GPAI_replace_custom_vars()` — Reemplazo en widgets individuales de Elementor.

### AJAX
- `wp_ajax_gpai_export_post` — Exportar datos de un post.
- `wp_ajax_gpai_import_post` — Importar datos a un post.
- `wp_ajax_gpai_export_template` — Exportar datos de una plantilla.
- `wp_ajax_gpai_import_template` — Importar datos a una plantilla.
- `wp_ajax_gpai_save_custom_field` — Guardar/actualizar campo personalizado (usado desde Elementor).
- `wp_ajax_gpai_list_custom_fields` — Listar campos personalizados de un post.
- `wp_ajax_gpai_delete_custom_field` — Eliminar un campo personalizado.

---

## 🔐 Seguridad

- ✅ Todos los valores de campos usan `wp_kses_post()` para sanitizar HTML permitido.
- ✅ Las capacidades requeridas son `manage_options`.
- ✅ Los nonces de WordPress se verifican en las peticiones AJAX.
- ✅ Las claves de variación se codifican en base64 para evitar roturas en formularios HTML.
- ✅ El panel de Elementor solo se activa si `ELEMENTOR_VERSION` está definido.

---

## 📄 Licencia

GPLv2+ — Ver [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html) para más detalles.

---

## 👤 Developer

- **Name:** Francisco Blanco
- **Website:** https://franciscoblanco.vercel.app/
- **Email:** blancofrancisco34@gmail.com
