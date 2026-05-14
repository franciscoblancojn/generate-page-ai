<?php

use franciscoblancojn\wordpress_utils\FWUSystemLog;

class GPAI_CONTENT
{
    public static function getPromptImg($post_id, $customFields, $yoastFields)
    {
        $title = get_the_title($post_id);
        $imageUrl = get_the_post_thumbnail_url($post_id, 'full') ?? "no tiene";

        $PROMPT = "
            ----TITULO DE LA PAGINA----
            " . $title . "
            ----CAMPOS PERSONALIZADOS----
            " . json_encode($customFields) . "
            ----DATOS DE YOAST SEO----
            " . json_encode($yoastFields) . "
            IMAGEN BASE (URL):
            {$imageUrl}
            ----INSTRUCCIONES----
            Necesito que generes UNA imagen optimizada para SEO basada en:
            - El contenido de la página
            - Los datos SEO
            - Y tomando como referencia visual la imagen proporcionada (URL)
            La imagen debe:
            - Ser estilo marketing digital / ecommerce
            - Tener apariencia profesional
            - Incluir elementos visuales relacionados con el contenido
            - NO incluir texto incrustado (importante para SEO dinámico)
            - Ser reutilizable como imagen destacada o banner
            ----FORMATO DE RESPUESTA----
            Devuelve únicamente un JSON válido con este formato:
            {
                'image_base64': 'data:image/png;base64,....',
                'alt': 'texto alternativo SEO optimizado',
                'title': 'titulo de la imagen'
            }
            ----REGLAS----
            - NO expliques nada
            - NO agregues texto fuera del JSON
            - SOLO devuelve el JSON
        ";

        return $PROMPT;
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
        $PROMPT = "
        ----TITULO DE LA PAGINA----
        " . $title . "

        ----CAMPOS PERSONALIZADOS----
        " . json_encode($customFields, JSON_UNESCAPED_UNICODE) . "

        ----PROMPTS PARA CAMPOS PERSONALIZADOS----
        " . json_encode($customFields_prompt, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "

        ----DATOS DE YOAST SEO----
        " . json_encode($yoastFields, JSON_UNESCAPED_UNICODE) . "

        ----PROMPTS PARA DATOS DE YOAST SEO----
        " . json_encode($yoastFields_prompt, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "

        ----PROMPT BASE----
        " . $prompt . "

        ----
        INSTRUCCIONES IMPORTANTES:

        - Genera una NUEVA versión del contenido.
        - NO copies literalmente el contenido actual.
        - NO reutilices frases exactas.
        - Reescribe completamente cada texto manteniendo el mismo objetivo comercial.
        - Usa un tono más persuasivo, moderno y orientado a conversión.
        - Los valores actuales solo son contexto de referencia.
        - Los ejemplos incluidos en los prompts NO deben copiarse literalmente.
        - Cambia estructura, redacción y enfoque manteniendo la intención original.
        - Mantén únicamente URLs, nombres de marca o datos técnicos cuando sea necesario.
        - Evita respuestas idénticas o muy similares al contenido original.
        - Cada campo debe ser significativamente distinto al valor original.
        - Usa vocabulario diferente y evita sinónimos directos.
        - Cada texto debe sentirse como una nueva versión de marketing.
        - Evita reemplazos mínimos de palabras.
        - Reestructura completamente frases y titulares.
        - Prioriza nuevas propuestas de valor.
        - Usa diferentes ángulos comerciales y emocionales.
        - customFields SOLO puede contener claves presentes en CAMPOS PERSONALIZADOS.
        - yoastFields SOLO puede contener claves presentes en DATOS DE YOAST SEO.
        - NO mezcles campos entre ambas estructuras.
        - NO inventes nuevas claves.

        ----
        FORMATO DE RESPUESTA:

        Retorna únicamente un JSON válido.

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

        Si se generan múltiples opciones:

        [
            {
                \"title\":\"title\",
                \"customFields\":{},
                \"yoastFields\":{}
            }
        ]
        ";
        return $PROMPT;
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

        // =========================
        // CUSTOM FIELDS
        // =========================
        if (!empty($item['customFields']) && is_array($item['customFields'])) {

            foreach ($item['customFields'] as $key => $value) {

                // mover yoast mal ubicado
                if (strpos($key, '_yoast_wpseo_') === 0) {

                    if (in_array($key, $allowedYoastFields)) {
                        $normalizedYoastFields[$key] = $value;
                    }

                    continue;
                }

                // permitir solo custom válidos
                if (in_array($key, $allowedCustomFields)) {
                    $normalizedCustomFields[$key] = $value;
                }
            }
        }

        // =========================
        // YOAST FIELDS
        // =========================
        if (!empty($item['yoastFields']) && is_array($item['yoastFields'])) {

            foreach ($item['yoastFields'] as $key => $value) {

                // mover custom mal ubicado
                if (strpos($key, '_yoast_wpseo_') !== 0) {

                    if (in_array($key, $allowedCustomFields)) {
                        $normalizedCustomFields[$key] = $value;
                    }

                    continue;
                }

                // permitir solo yoast válidos
                if (in_array($key, $allowedYoastFields)) {
                    $normalizedYoastFields[$key] = $value;
                }
            }
        }

        // =========================
        // REEMPLAZAR
        // =========================
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

            $PROMPT = "
            ----TITULO DE LA PLANTILLA----
            " . $title . "

            ----VARIABLES GLOBALES {g{...}}----
            " . json_encode($globalFields, JSON_UNESCAPED_UNICODE) . "

            ----PROMPTS PARA VARIABLES GLOBALES----
            " . json_encode($globalFields_prompt ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "

            ----PROMPT BASE----
            " . $prompt . "

            ----
            INSTRUCCIONES IMPORTANTES:

            - Genera una NUEVA versión del contenido para cada variable global.
            - NO copies literalmente el contenido actual.
            - Reescribe completamente cada texto manteniendo el mismo objetivo.
            - Usa un tono persuasivo, moderno y orientado a conversión.
            - Los valores actuales solo son contexto de referencia.
            - Cada variable debe ser significativamente distinta al valor original.
            - NO inventes nuevas variables.
            - Solo puedes usar las claves listadas en VARIABLES GLOBALES.

            ----
            FORMATO DE RESPUESTA:

            Retorna únicamente un JSON válido.

            Formato para una variacion:
            {
                \"title\": \"Titulo para la pagina\",
                \"variable_key\": \"valor\",
                \"otra_variable\": \"otro valor\"
            }

            Si se generan múltiples opciones, retorna un array:
            [
                { ... },
                { ... }
            ]
            ";

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

        // quitar slashes acumulados
        $text = stripslashes($text);

        // reemplazar escapes repetidos
        while (strpos($text, '\\\\') !== false) {
            $text = str_replace('\\\\', '\\', $text);
        }

        // quitar escapes de comillas
        $text = str_replace('\\"', '"', $text);

        // limpiar espacios
        $text = trim($text);

        return $text;
    }
}
