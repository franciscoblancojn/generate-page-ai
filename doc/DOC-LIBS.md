# DOC-LIBS.md

> Convenciones y uso de `franciscoblancojn/wordpress_utils` en Generate Page AI.

---

## Instalación

La librería vive en `libs/` (Composer vendor renombrado). **Nunca edites `libs/` a mano.**

| Comando | Propósito |
|---|---|
| `npm run install` | `composer install` + elimina `libs/` + renombra `vendor` → `libs` |
| `npm run update` | Borra `libs/` y `composer.lock`, reinstala desde cero |

---

## Clases y API

### `FWUPage` — Layout de páginas admin

Cada submenú del plugin usa el mismo patrón:

```php
// En page/pages/{subpage}/page.php
echo FWUPage::css();

// Dentro del <body> — tabs si hay secciones múltiples
<?php FWUPage::tabs($TAGS, $defaultTag); ?>

echo FWUPage::js(GPAI_KEY);
```

- `FWUPage::css()` → imprime CSS del layout admin.
- `FWUPage::tabs($TAGS, $default)` → renderiza navegación por tabs.
- `FWUPage::js($pluginKey)` → imprime JS + agrega clase `fwue-loader` a todos los `[type="submit"]` en click.

### `FWUTooltip` — Ayuda contextual

```php
<?php FWUTooltip::render("Título", "Descripción que aparece en el tooltip.") ?>
```

Se usa en formularios para explicar campos, endpoints, opciones, etc.

### `FWUModal` — Diálogos modales

```php
echo FWUModal::css() . FWUModal::js();
```

Se incluye en secciones que usan modales (export/import, por ejemplo).

### `FWUCollapse` — Secciones plegables

```php
FWUCollapse::render($title, $content, $startOpen);
// o sin output buffer:
$html = FWUCollapse::html($title, $content, $startOpen);
```

Se usa en páginas con listas extensas (sitemaps, procesar contenido, config sitemaps).

### `FWUExportImport` — Bloque exportar/importar JSON

```php
echo FWUExportImport::css() . FWUExportImport::js();

// Botón exportar (descarga JSON)
echo FWUExportImport::exportButtonHtml($ajaxAction, $extraData, $filename);

// Botón importar (abre modal)
echo FWUExportImport::importButtonHtml($modalId);

// Modal de importación completo
echo FWUExportImport::html($modalId, $title, $ajaxAction, $extraData, $showPostSelector);
```

Se usa en el meta box GPAI SEO (`src/meta-box/gpai-seo.php`) y en la sección Post (`src/page/sections/post.php`).

### `FWURespond` — Mensajes de resultado

```php
FWURespond::render($config);
```

`$config` es un array con `type` (`success`/`error`/`info`), `message`, etc. Se usa en todas las secciones para mostrar resultado de AJAX: config, sitemaps, htaccess, reemplazar, análisis, imágenes, etc.

### `FWUSystemLog` — Logs del plugin

```php
FWUSystemLog::add(GPAI_KEY, [
    'type'    => 'nombre_del_evento',
    'detail1' => $valor,
    'detail2' => $valor,
]);
```

- Se ve desde la barra de admin de WordPress (ícono de log).
- Máximo `GPAI_LOG_COUNT` (100) entradas.
- **Nunca uses `error_log()`, `var_dump()`, `print_r()`** en producción.

### `FWUUpdate` — Auto-update vía GitHub

```php
FWUUpdate::init([
    'repo'      => 'franciscoblancojn/generate-page-ai',
    'version'   => GPAI_VERSION,
    'basename'  => GPAI_BASENAME,
]);
```

Se inicializa en `index.php` (plugin header).

---

## El loader de `fwue-loader` y el bug del `invalid` listener

`FWUPage::js()` registra un listener **solo en `click`** que agrega `fwue-loader` (spinner) a `event.target` si es `[type="submit"]`.

**Problema:** Si la validación HTML5 del navegador bloquea el `submit` (campo `required` vacío, `pattern` inválido, etc.), el botón queda con la clase `fwue-loader` y el spinner nunca se apaga.

**Solución (ya aplicada en el plugin):** Después de cada `FWUPage::js()` o en el JS inline de la sección, se registra un listener en el evento `invalid`:

```javascript
document.addEventListener('invalid', function(e) {
    if (e.target.matches('[type="submit"]')) {
        e.target.classList.remove('fwue-loader');
    }
}, true);
```

Esto se aplica en: `html.php`, `sitemaps.php`, `config-sitemaps.php`, `crear_sitemap.php`, `htaccess.php`, `reemplazar.php`.

---

## Convenciones

1. **Namespace**: `franciscoblancojn\wordpress_utils\` — siempre importar con `use` al inicio del archivo.
2. **Prefix CSS**: La librería usa `fwue-` (ej: `fwue-loader`). El plugin usa `gpai-`.
3. **No hardcodees paths**: Usa `GPAI_DIR` y `GPAI_URL`.
4. **Logs**: Siempre `FWUSystemLog::add(GPAI_KEY, ...)`.
5. **Respuestas AJAX**: Siempre `wp_send_json_success()` / `wp_send_json_error()` con `FWURespond` en la UI.
