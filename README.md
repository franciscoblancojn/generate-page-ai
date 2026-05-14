# Generate Page AI 🚀

**Version:** 1.2.1 | **License:** GPLv2+

Generate Page AI es un plugin de WordPress que potencia tus páginas con **inteligencia artificial** 🤖. Conéctalo a Google Gemini, gestiona campos personalizados, datos Yoast SEO, y crea variaciones de contenido en masa para posts y plantillas de Elementor.

---

## ✨ Características

- 🤖 **Generación con IA** — Usa Google Gemini para generar contenido único para campos personalizados, Yoast SEO y variables globales de plantillas.
- 📄 **Gestión de Posts** — Selecciona cualquier página, edita sus campos personalizados y metadatos Yoast, escribe prompts y genera variaciones de contenido al instante.
- 🧩 **Soporte para Elementor** — Detecta variables `{g{variable}}` en tus plantillas de Elementor, permite editarlas y generar variaciones.
- 🔄 **Variaciones en Masa** — Genera múltiples variaciones de contenido desde un solo prompt. Revisa, aplica o descarta cada una.
- 📤 **Exportación/Importación JSON** — Exporta campos personalizados, datos Yoast y valores de plantillas a JSON. Impórtalos después con un clic.
- 🔗 **Reemplazo en Frontend** — Las variables `{g{key}}`, `{{key}}` y `__key__` se reemplazan automáticamente con sus valores al mostrar la página.
- 🖌️ **Crear Plantilla desde Variación** — Convierte una variación de contenido en una nueva plantilla de Elementor independiente.
- 🔍 **Vista Previa** — Previsualiza variaciones directamente en el editor de Elementor con los valores inyectados como parámetros.

---

## 📋 Requisitos

- WordPress 5.0+
- PHP 7.4+
- Plugin [Yoast Duplicate Post](https://wordpress.org/plugins/duplicate-post/) (obligatorio)
- Plugin [Elementor](https://wordpress.org/plugins/elementor/) (para funcionalidad de plantillas)
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
│   ├── api/                      # API REST y handlers AJAX
│   ├── css/                      # Estilos CSS inline
│   ├── data/                     # Persistencia de datos (opciones de WP)
│   ├── hook/                     # Hooks de WordPress (filtro para `the_content`)
│   ├── js/                       # JavaScript inline para la interfaz admin
│   ├── page/                     # Páginas del admin (menús, secciones)
│   └── templates/                # Helpers de renderizado HTML
```

---

## 🧠 Clases Principales

| Clase | Archivo | Función |
|-------|---------|---------|
| `GPAI_AI` | `src/ai/ai.php` | 🛰️ Cliente HTTP para la API de Google Gemini |
| `GPAI_CONTENT` | `src/ai/content.php` | 🧬 Orquestador de generación de contenido con IA |
| `GPAI_PROMPT` | `src/ai/prompt.php` | 💡 Mejora de prompts existentes vía IA |
| `GPAI_CF` | `src/api/cf.php` | 📦 API para campos personalizados de posts |
| `GPAI_YOAST` | `src/api/yoast.php` | 🔍 API para metadatos Yoast SEO |
| `GPAI_CF_TEMPLATE` | `src/api/cf_template.php` | 🧩 API para variables globales `{g{...}}` de plantillas |
| `GPAI_EXPORT_IMPORT` | `src/api/export_import.php` | 📤 Exportación/Importación JSON |
| `GPAI_USE_DATA_*` | `src/data/*.php` | 💾 Clases de almacenamiento basadas en `wp_options` |

---

## 🖥️ Páginas del Admin

| Menú | Slug | Descripción |
|------|------|-------------|
| ⚙️ **Configuración** | `GPAI_config` | API Key de Gemini, selección de modelo, toggle de generación de imágenes |
| 📄 **Post** | `GPAI_post` | Gestión de posts: campos personalizados, Yoast, prompts, variaciones |
| 🧩 **Plantillas** | `GPAI_plantilllas` | Gestión de plantillas Elementor: variables globales, prompts, variaciones |

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

### AJAX
- `wp_ajax_gpai_export_post` — Exportar datos de un post.
- `wp_ajax_gpai_import_post` — Importar datos a un post.
- `wp_ajax_gpai_export_template` — Exportar datos de una plantilla.
- `wp_ajax_gpai_import_template` — Importar datos a una plantilla.

---

## 🔐 Seguridad

- ✅ Todos los valores de campos usan `wp_kses_post()` para sanitizar HTML permitido.
- ✅ Las capacidades requeridas son `manage_options`.
- ✅ Los nonces de WordPress se verifican en las peticiones AJAX.
- ✅ Las claves de variación se codifican en base64 para evitar roturas en formularios HTML.

---

## 📄 Licencia

GPLv2+ — Ver [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html) para más detalles.

---

## 👤 Developer

- **Name:** Francisco Blanco
- **Website:** https://franciscoblanco.vercel.app/
- **Email:** blancofrancisco34@gmail.com
