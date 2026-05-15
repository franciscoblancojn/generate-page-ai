<?php

use franciscoblancojn\wordpress_utils\FWUSystemLog;

class GPAI_CONTENT
{
    public static function getBasePromptTemplate($type = 'content')
    {
        $config = new GPAI_USE_DATA_CONFIG();
        $data = $config->get();
        $promptsBase = $data['prompts_base'] ?? [];

        return $promptsBase[$type] ?? self::getBasePromptDefault($type);
    }

    public static function getBasePromptDefault($type = 'content')
    {
        $defaults = [
            'content' => '----TITULO DE LA PAGINA----
{{title}}

----CAMPOS PERSONALIZADOS----
{{customFields}}

----PROMPTS PARA CAMPOS PERSONALIZADOS----
{{customFields_prompt}}

----DATOS DE YOAST SEO----
{{yoastFields}}

----PROMPTS PARA DATOS DE YOAST SEO----
{{yoastFields_prompt}}

----PROMPT BASE----
{{prompt}}

----
INSTRUCCIONES IMPORTANTES:

- Genera una NUEVA versi\u00f3n del contenido.
- NO copies literalmente el contenido actual.
- NO reutilices frases exactas.
- Reescribe completamente cada texto manteniendo el mismo objetivo comercial.
- Usa un tono m\u00e1s persuasivo, moderno y orientado a conversi\u00f3n.
- Los valores actuales solo son contexto de referencia.
- Los ejemplos incluidos en los prompts NO deben copiarse literalmente.
- Cambia estructura, redacci\u00f3n y enfoque manteniendo la intenci\u00f3n original.
- Mant\u00e9n \u00fanicamente URLs, nombres de marca o datos t\u00e9cnicos cuando sea necesario.
- Evita respuestas id\u00e9nticas o muy similares al contenido original.
- Cada campo debe ser significativamente distinto al valor original.
- Usa vocabulario diferente y evita sin\u00f3nimos directos.
- Cada texto debe sentirse como una nueva versi\u00f3n de marketing.
- Evita reemplazos m\u00ednimos de palabras.
- Reestructura completamente frases y titulares.
- Prioriza nuevas propuestas de valor.
- Usa diferentes \u00e1ngulos comerciales y emocionales.
- customFields SOLO puede contener claves presentes en CAMPOS PERSONALIZADOS.
- yoastFields SOLO puede contener claves presentes en DATOS DE YOAST SEO.
- NO mezcles campos entre ambas estructuras.
- NO inventes nuevas claves.

----
FORMATO DE RESPUESTA:

Retorna \u00fanicamente un JSON v\u00e1lido.

Formato:
{
    \"title\":\"title\",
    \"customFields\":{
        \"key\":\"value\"
    },
    \"yoastFields\":{
        \"key\":\"value\"
    }
}

Si se generan m\u00faltiples opciones:

[
    {
        \"title\":\"title\",
        \"customFields\":{},
        \"yoastFields\":{}
    }
]',

            'content_img' => '----TITULO DE LA PAGINA----
{{title}}
----CAMPOS PERSONALIZADOS----
{{customFields}}
----DATOS DE YOAST SEO----
{{yoastFields}}
IMAGEN BASE (URL):
{{imageUrl}}
----INSTRUCCIONES----
Necesito que generes UNA imagen optimizada para SEO basada en:
- El contenido de la p\u00e1gina
- Los datos SEO
- Y tomando como referencia visual la imagen proporcionada (URL)
La imagen debe:
- Ser estilo marketing digital / ecommerce
- Tener apariencia profesional
- Incluir elementos visuales relacionados con el contenido
- NO incluir texto incrustado (importante para SEO din\u00e1mico)
- Ser reutilizable como imagen destacada o banner
----FORMATO DE RESPUESTA----
Devuelve \u00fanicamente un JSON v\u00e1lido con este formato:
{
    \'image_base64\': \'data:image/png;base64,....\',
    \'alt\': \'texto alternativo SEO optimizado\',
    \'title\': \'titulo de la imagen\'
}
----REGLAS----
- NO expliques nada
- NO agregues texto fuera del JSON
- SOLO devuelve el JSON',

            'template' => '----TITULO DE LA PLANTILLA----
{{title}}

----VARIABLES GLOBALES {g{...}}----
{{globalFields}}

----PROMPTS PARA VARIABLES GLOBALES----
{{globalFields_prompt}}

----PROMPT BASE----
{{prompt}}

----
INSTRUCCIONES IMPORTANTES:

- Genera una NUEVA versi\u00f3n del contenido para cada variable global.
- NO copies literalmente el contenido actual.
- Reescribe completamente cada texto manteniendo el mismo objetivo.
- Usa un tono persuasivo, moderno y orientado a conversi\u00f3n.
- Los valores actuales solo son contexto de referencia.
- Cada variable debe ser significativamente distinta al valor original.
- NO inventes nuevas variables.
- Solo puedes usar las claves listadas en VARIABLES GLOBALES.

----
FORMATO DE RESPUESTA:

Retorna \u00fanicamente un JSON v\u00e1lido.

Formato para una variacion:
{
    \"title\": \"Titulo para la pagina\",
    \"variable_key\": \"valor\",
    \"otra_variable\": \"otro valor\"
}

Si se generan m\u00faltiples opciones, retorna un array:
[
    { ... },
    { ... }
]',
        ];

        return $defaults[$type] ?? $defaults['content'];
    }

    public static function getPromptImg($post_id, $customFields, $yoastFields)
    {
        $title = get_the_title($post_id);
        $imageUrl = get_the_post_thumbnail_url($post_id, 'full') ?? "no tiene";

        $template = self::getBasePromptTemplate('content_img');

        $replacements = [
            '{{title}}' => $title,
            '{{customFields}}' => json_encode($customFields),
            '{{yoastFields}}' => json_encode($yoastFields),
            '{{imageUrl}}' => $imageUrl,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    public static function getPrompt($CONFIG)
    {
        [
            "post_id" => $post_id,
            "prompt" => $prompt,
            "customFields" => $customFields,
            "customFields_prompt" => $customFields_prompt,
            "yoastFields" => $yoastFields,
            "yoastFields_prompt" => $yoastFields_prompt,
        ] = $CONFIG;

        $title = get_the_title($post_id);

        $template = self::getBasePromptTemplate('content');

        $replacements = [
            '{{title}}' => $title,
            '{{customFields}}' => json_encode($customFields, JSON_UNESCAPED_UNICODE),
            '{{customFields_prompt}}' => json_encode($customFields_prompt, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            '{{yoastFields}}' => json_encode($yoastFields, JSON_UNESCAPED_UNICODE),
            '{{yoastFields_prompt}}' => json_encode($yoastFields_prompt, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            '{{prompt}}' => $prompt,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    public static function getContentByPrompt($PROMPT)
    {
        $jsonResponse = [];
        try {
            $result = GPAI_AI::sendPrompt($PROMPT);

            if ($result['status'] == 'error') {
                return $result;
            }
            $result['message'] = "Contenido Generado";
            FWUSystemLog::add(GPAI_KEY, [
                'type' => "IA Content result text",
                'PROMPT' => $PROMPT,
                'result' => $result,
            ]);
            $result['data'] = GPAI_AI::parseJson($result['data']);
            if (!isset($result['data'][0])) {
                $result['data'] = [$result['data']];
            }
            return $result;
        } catch (\Throwable $th) {
            $error = [
                "status" => "error",
                "message" => $th->getMessage(),
                'data' => [
                    'line' => $th->getLine(),
                    'file' => $th->getFile(),
                    'jsonResponse' => $jsonResponse
                ]
            ];
            FWUSystemLog::add(GPAI_KEY, [
                'type' => "IA Error Content result text",
                'PROMPT' => $PROMPT,
                'error' => $error,
            ]);
            return $error;
        }
    }

    public static function getContent($CONFIG)
    {
        try {
            $PROMPT = self::getPrompt($CONFIG);
            $result = self::getContentByPrompt($PROMPT);
            if ($result['status'] == "error") {
                return $result;
            }
            foreach ($result['data'] as $key => $item) {
                $result['data'][$key] = self::normalizeFields(
                    $item,
                    $CONFIG['customFields'],
                    $CONFIG['yoastFields']
                );
            }
            FWUSystemLog::add(GPAI_KEY, [
                'type' => "IA Content result",
                'PROMPT' => $PROMPT,
                'CONFIG' => $CONFIG,
                'result' => $result,
            ]);
            if ($CONFIG['generate_img']) {
                foreach ($result['data'] as $key => $value) {
                    $PROMPTBYIMG = self::getPromptImg($CONFIG['post_id'], $value['customFields'], $value['yoastFields']);
                    $result_img = GPAI_AI::sendPrompt($PROMPTBYIMG);
                    if ($result_img['status'] == 'ok') {
                        $result_img['data'] = GPAI_AI::parseJson($result_img['data']);
                    }
                    $result['data'][$key]['imagen'] = $result_img;
                }
                FWUSystemLog::add(GPAI_KEY, [
                    'type' => "IA Duplicados result with img",
                    'PROMPT' => $PROMPT,
                    'CONFIG' => $CONFIG,
                    'result' => $result,
                ]);
            }
            return $result;
        } catch (\Throwable $th) {
            $error = [
                "status" => "error",
                "message" => $th->getMessage(),
                'data' => [
                    'line' => $th->getLine(),
                    'file' => $th->getFile(),
                ]
            ];
            FWUSystemLog::add(GPAI_KEY, [
                'type' => "IA Error Content result",
                'CONFIG' => $CONFIG,
                'error' => $error,
            ]);
            return $error;
        }
    }

    public static function normalizeFields($item, $customFields, $yoastFields)
    {
        $allowedCustomFields = array_keys($customFields);
        $allowedYoastFields = array_keys($yoastFields);

        $normalizedCustomFields = [];
        $normalizedYoastFields = [];

        if (!empty($item['customFields']) && is_array($item['customFields'])) {
            foreach ($item['customFields'] as $key => $value) {
                if (strpos($key, '_yoast_wpseo_') === 0) {
                    if (in_array($key, $allowedYoastFields)) {
                        $normalizedYoastFields[$key] = $value;
                    }
                    continue;
                }
                if (in_array($key, $allowedCustomFields)) {
                    $normalizedCustomFields[$key] = $value;
                }
            }
        }

        if (!empty($item['yoastFields']) && is_array($item['yoastFields'])) {
            foreach ($item['yoastFields'] as $key => $value) {
                if (strpos($key, '_yoast_wpseo_') !== 0) {
                    if (in_array($key, $allowedCustomFields)) {
                        $normalizedCustomFields[$key] = $value;
                    }
                    continue;
                }
                if (in_array($key, $allowedYoastFields)) {
                    $normalizedYoastFields[$key] = $value;
                }
            }
        }

        $item['customFields'] = $normalizedCustomFields;
        $item['yoastFields'] = $normalizedYoastFields;

        return $item;
    }

    public static function getContentTemplate($CONFIG)
    {
        try {
            [
                "template_id" => $template_id,
                "prompt" => $prompt,
                "globalFields" => $globalFields,
                "globalFields_prompt" => $globalFields_prompt,
            ] = $CONFIG;

            $title = get_the_title($template_id);

            $template = self::getBasePromptTemplate('template');

            $replacements = [
                '{{title}}' => $title,
                '{{globalFields}}' => json_encode($globalFields, JSON_UNESCAPED_UNICODE),
                '{{globalFields_prompt}}' => json_encode($globalFields_prompt ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                '{{prompt}}' => $prompt,
            ];

            $PROMPT = str_replace(array_keys($replacements), array_values($replacements), $template);

            $result = self::getContentByPrompt($PROMPT);

            foreach ($result['data'] as $key => $item) {
                $allowed = array_keys($globalFields);
                $normalized = [];
                if (is_array($item)) {
                    foreach ($item as $k => $v) {
                        if (in_array($k, $allowed) || $k === 'title') {
                            $normalized[$k] = $v;
                        }
                    }
                }
                $result['data'][$key] = $normalized;
            }

            FWUSystemLog::add(GPAI_KEY, [
                'type' => "IA Template Content result",
                'PROMPT' => $PROMPT,
                'config' => $CONFIG,
                'result' => $result,
            ]);

            return $result;
        } catch (\Throwable $th) {
            $error = [
                "status" => "error",
                "message" => $th->getMessage(),
                'data' => [
                    'line' => $th->getLine(),
                    'file' => $th->getFile(),
                ]
            ];
            FWUSystemLog::add(GPAI_KEY, [
                'type' => "IA Error Template Content result",
                'config' => $CONFIG,
                'error' => $error,
            ]);
            return $error;
        }
    }

    public static function cleanPromptText($text)
    {
        if (!is_string($text)) {
            return $text;
        }

        $text = stripslashes($text);

        while (strpos($text, '\\\\') !== false) {
            $text = str_replace('\\\\', '\\', $text);
        }

        $text = str_replace('\\"', '"', $text);

        $text = trim($text);

        return $text;
    }
}
