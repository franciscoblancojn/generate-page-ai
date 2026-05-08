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
        $content = get_post_field('post_content', $post_id);
        $PROMPT = "
            ----TITULO DE LA PAGINA----
            " . $title . "
            ----CONTENIDO DE LA PAGINA----
            " . $content . "
            ----CAMPOS PERSONALIZADOS----
            " . json_encode($customFields) . "
            ----PROMPTS PARA CAMPOS PERSONALIZADOS----
            " . json_encode($customFields_prompt) . "
            ----DATOS DE YOAST SEO----
            " . json_encode($yoastFields) . "
            ----PROMPTS PARA DATOS DE YOAST SEO----
            " . json_encode($yoastFields_prompt) . "
            ----PROMP BASE----
            " . $prompt . "
            ----
            Necesito que generes un json basandote en el contenido, campos personalizados y datos de yoast seo como referencia.
            Formato de json : {title:'title',customFields:{key:'value',...},yoastFields:{key:'value',...}}
            En caso que se pidan varias respuesta este es el formato a usar:
            Formato de array : [{title:'title',customFields:{key:'value',...},yoastFields:{key:'value',...}},{title:'title2',customFields:{key:'value',...},yoastFields:{key:'value',...}}]
            Importante, ten en cuenta el prompt base y prompts personalizados por campos o datos de yoast, en caso de que exista prompts personalizados usa tanto el prompt personalizado como el prompt base para generar contenido.
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
            return $error;
        }
    }
    public static function getContent($CONFIG)
    {
        try {
            $PROMPT = self::getPrompt($CONFIG);
            $result = self::getContentByPrompt($PROMPT);
            FWUSystemLog::add(GPAI_KEY, [
                'type' => "IA Content result",
                'PROMPT' => $PROMPT,
                ...$CONFIG,
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
                    ...$CONFIG,
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
                 ...$CONFIG,
                'error' => $error,
            ]);
            return $error;
        }
    }
}
