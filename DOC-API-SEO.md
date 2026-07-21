# Generate Page AI — API SEO

Endpoint REST para enviar configuraciones SEO a posts de WordPress desde servicios externos.

---

## Configuración previa

1. Ir a **Generate Page AI → API** en el admin de WordPress.
2. Activar el checkbox **"Activar API SEO"**.
3. Generar o ingresar una **API Key**.
4. Copiar la **Endpoint URL** (formato: `https://tudominio.com/wp-json/GPAI/seo`).

---

## Endpoint

```
POST /wp-json/GPAI/seo
```

---

## Headers

| Header | Requerido | Descripción |
|---|---|---|
| `Content-Type` | Sí | Debe ser `application/json` |
| `X-GPAI-SEO-Key` | Sí | API Key configurada en el admin |

---

## Body (JSON)

El body debe ser un objeto JSON con `post_id` obligatorio y uno o más campos SEO.

### Campo obligatorio

| Campo | Tipo | Descripción |
|---|---|---|
| `post_id` | `integer` | ID del post/página en WordPress |

### Campos SEO opcionales

Puedes enviar solo los campos que necesites actualizar. Los campos no enviados no se modifican.

#### Principales

| Campo | Tipo | Descripción |
|---|---|---|
| `gpai_wpseo_active` | `string` | `'1'` para activar SEO, `'0'` para desactivar |
| `gpai_wpseo_title` | `string` | Título SEO (meta title) |
| `gpai_wpseo_metadesc` | `string` | Meta descripción |
| `gpai_wpseo_focuskw` | `string` | Palabra clave principal |
| `gpai_wpseo_focuskeywords` | `string` | Palabras clave separadas por coma |
| `gpai_wpseo_canonical` | `string` | URL canónica |
| `gpai_wpseo_bctitle` | `string` | Título del breadcrumb |
| `gpai_wpseo_redirect` | `string` | URL de redirección 301 (vacío = sin redirect) |
| `gpai_wpseo_post_name` | `string` | Slug del post |
| `gpai_wpseo_is_cornerstone` | `string` | `'1'` si es contenido cornerstone, `'0'` si no |

#### Robots

| Campo | Tipo | Descripción |
|---|---|---|
| `gpai_wpseo_meta-robots-noindex` | `string` | `'1'` para noindex, `'0'` para index |
| `gpai_wpseo_meta-robots-nofollow` | `string` | `'1'` para nofollow, `'0'` para follow |
| `gpai_wpseo_meta-robots-adv` | `string` | Robots avanzado (ej: `max-snippet:-1,max-image-preview:large,max-video-preview:-1`) |
| `gpai_wpseo_meta-robots-noarchive` | `string` | `'1'` para noarchive |
| `gpai_wpseo_meta-robots-nosnippet` | `string` | `'1'` para nosnippet |
| `gpai_wpseo_meta-robots-noimageindex` | `string` | `'1'` para noimageindex |

#### Open Graph

| Campo | Tipo | Descripción |
|---|---|---|
| `gpai_wpseo_opengraph-title` | `string` | Título para Facebook/OG |
| `gpai_wpseo_opengraph-description` | `string` | Descripción para Facebook/OG |
| `gpai_wpseo_opengraph-image` | `string` | URL de imagen para Facebook/OG |
| `gpai_wpseo_opengraph-image-id` | `string` | ID del adjunto de imagen OG |
| `gpai_wpseo_opengraph-url` | `string` | URL para Facebook/OG |

#### Twitter Cards

| Campo | Tipo | Descripción |
|---|---|---|
| `gpai_wpseo_twitter-title` | `string` | Título para Twitter Card |
| `gpai_wpseo_twitter-description` | `string` | Descripción para Twitter Card |
| `gpai_wpseo_twitter-image` | `string` | URL de imagen para Twitter Card |

#### Schema JSON-LD

| Campo | Tipo | Descripción |
|---|---|---|
| `gpai_wpseo_schema_page_type` | `string` | Tipo de página Schema.org (ej: `WebPage`, `AboutPage`, `ContactPage`) |
| `gpai_wpseo_schema_article_type` | `string` | Tipo de artículo Schema.org (ej: `Article`, `BlogPosting`, `NewsArticle`) |
| `gpai_wpseo_schema_extra_json` | `string` | JSON string con bloques Schema.org adicionales (ver ejemplo abajo) |
| `gpai_wpseo_remove_other_jsonld` | `string` | `'1'` para eliminar otros JSON-LD de la página, `'0'` para mantener |

#### Campo adicional (no SEO)

| Campo | Tipo | Descripción |
|---|---|---|
| `post_name` | `string` | Slug del post (actualiza directamente el `post_name` de WordPress) |

---

## Ejemplo de request completo

```bash
curl -X POST "https://tudominio.com/wp-json/GPAI/seo" \
  -H "Content-Type: application/json" \
  -H "X-GPAI-SEO-Key: gpai_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -d '{
    "post_id": 123,
    "post_name": "mi-pagina",
    "gpai_wpseo_active": "1",
    "gpai_wpseo_title": "Mi Titulo SEO | Mi Sitio",
    "gpai_wpseo_metadesc": "Descripcion optimizada para motores de busqueda.",
    "gpai_wpseo_focuskw": "palabra clave principal",
    "gpai_wpseo_focuskeywords": "palabra clave 1, palabra clave 2, palabra clave 3",
    "gpai_wpseo_canonical": "https://tudominio.com/mi-pagina/",
    "gpai_wpseo_bctitle": "Mi Pagina",
    "gpai_wpseo_is_cornerstone": "0",
    "gpai_wpseo_meta-robots-noindex": "0",
    "gpai_wpseo_meta-robots-nofollow": "0",
    "gpai_wpseo_meta-robots-adv": "max-snippet:-1,max-image-preview:large,max-video-preview:-1",
    "gpai_wpseo_meta-robots-noarchive": "0",
    "gpai_wpseo_meta-robots-nosnippet": "0",
    "gpai_wpseo_meta-robots-noimageindex": "0",
    "gpai_wpseo_opengraph-title": "Mi Titulo para Redes Sociales",
    "gpai_wpseo_opengraph-description": "Descripcion que aparece al compartir en Facebook.",
    "gpai_wpseo_opengraph-image": "https://tudominio.com/wp-content/uploads/og-image.jpg",
    "gpai_wpseo_opengraph-url": "https://tudominio.com/mi-pagina/",
    "gpai_wpseo_twitter-title": "Mi Titulo para Twitter",
    "gpai_wpseo_twitter-description": "Descripcion que aparece al compartir en Twitter.",
    "gpai_wpseo_twitter-image": "https://tudominio.com/wp-content/uploads/twitter-image.jpg",
    "gpai_wpseo_schema_page_type": "WebPage",
    "gpai_wpseo_schema_article_type": "Article",
    "gpai_wpseo_schema_extra_json": "[{\"@type\":\"Service\",\"name\":\"Mi Servicio\",\"description\":\"Descripcion del servicio.\"}]",
    "gpai_wpseo_remove_other_jsonld": "0"
  }'
```

## Ejemplo mínimo (solo campos esenciales)

```bash
curl -X POST "https://tudominio.com/wp-json/GPAI/seo" \
  -H "Content-Type: application/json" \
  -H "X-GPAI-SEO-Key: gpai_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -d '{
    "post_id": 123,
    "gpai_wpseo_active": "1",
    "gpai_wpseo_title": "Titulo SEO",
    "gpai_wpseo_metadesc": "Meta descripcion de la pagina."
  }'
```

---

## Ejemplo: Schema Extra JSON

El campo `gpai_wpseo_schema_extra_json` acepta un JSON string con un array de bloques Schema.org. Cada bloque debe tener un `@type` válido.

```json
[
  {
    "@type": "Service",
    "name": "Servicio de ejemplo",
    "description": "Descripcion del servicio.",
    "provider": {
      "@type": "Organization",
      "name": "Mi Empresa"
    }
  },
  {
    "@type": "FAQPage",
    "mainEntity": [
      {
        "@type": "Question",
        "name": "¿Que incluye el servicio?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "El servicio incluye asesoria, implementacion y soporte."
        }
      },
      {
        "@type": "Question",
        "name": "¿Cuanto tarda?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "El plazo es de 5 dias habiles."
        }
      }
    ]
  }
]
```

---

## Respuestas

### Éxito (200)

```json
{
  "success": true,
  "message": "Datos SEO guardados correctamente.",
  "data": {
    "post_id": 123,
    "saved": {
      "gpai_wpseo_active": "1",
      "gpai_wpseo_title": "Titulo SEO",
      "gpai_wpseo_metadesc": "Meta descripcion.",
      "gpai_wpseo_title": "(deleted)"
    }
  }
}
```

> **Nota:** Si un campo se envía como string vacío `""`, se elimina del `post_meta` (el valor en `saved` aparecerá como `"(deleted)"`).

### Errores

| HTTP | Código | Mensaje |
|---|---|---|
| 400 | `missing_post_id` | `post_id es requerido.` |
| 400 | `invalid_fields` | `No se enviaron campos SEO validos.` |
| 401 | `missing_key` | `API Key requerida en header X-GPAI-SEO-Key.` |
| 401 | `invalid_key` | `API Key invalida.` |
| 403 | `api_disabled` | `API SEO deshabilitada.` |
| 404 | `not_found` | `Post no encontrado.` |

---

## Ejemplos en lenguajes de programación

### PHP

```php
$ch = curl_init('https://tudominio.com/wp-json/GPAI/seo');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'X-GPAI-SEO-Key: gpai_tu_api_key_aqui',
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'post_id' => 123,
        'gpai_wpseo_active' => '1',
        'gpai_wpseo_title' => 'Titulo SEO desde PHP',
        'gpai_wpseo_metadesc' => 'Meta descripcion generada externamente.',
    ]),
    CURLOPT_RETURNTRANSFER => true,
]);

$response = curl_exec($ch);
curl_close($ch);
$data = json_decode($response, true);
```

### JavaScript (fetch)

```javascript
fetch('https://tudominio.com/wp-json/GPAI/seo', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-GPAI-SEO-Key': 'gpai_tu_api_key_aqui',
    },
    body: JSON.stringify({
        post_id: 123,
        gpai_wpseo_active: '1',
        gpai_wpseo_title: 'Titulo SEO desde JS',
        gpai_wpseo_metadesc: 'Meta descripcion generada externamente.',
    }),
})
.then(function(res) { return res.json(); })
.then(function(data) { console.log(data); });
```

### Python

```python
import requests

response = requests.post(
    'https://tudominio.com/wp-json/GPAI/seo',
    headers={
        'Content-Type': 'application/json',
        'X-GPAI-SEO-Key': 'gpai_tu_api_key_aqui',
    },
    json={
        'post_id': 123,
        'gpai_wpseo_active': '1',
        'gpai_wpseo_title': 'Titulo SEO desde Python',
        'gpai_wpseo_metadesc': 'Meta descripcion generada externamente.',
    },
)

print(response.json())
```

---

## Notas

- Los campos enviados como string vacío `""` eliminan el valor del `post_meta`.
- El campo `gpai_wpseo_schema_extra_json` debe ser un JSON string (no un objeto anidado).
- El campo `gpai_wpseo_post_name` actualiza el slug del post (también se puede usar `post_name`).
- La API key se compara con `hash_equals()` (resistente a timing attacks).
- Todas las operaciones se registran en el log del plugin.
