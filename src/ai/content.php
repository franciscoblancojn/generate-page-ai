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
            'content' => GPAI_DIR . 'src/prompts/content-v2.txt',
            'content_img' => GPAI_DIR . 'src/prompts/content_img-v1.txt',
            'template' => GPAI_DIR . 'src/prompts/template-v1.txt',
        ];

        $file = $defaults[$type] ?? $defaults['content'];

        if (!file_exists($file)) {
            return '';
        }

        return file_get_contents($file);
    }

    public static function getPromptImg($post_id, $customFields, $gpaiSeoFields = [])
    {
        $title = get_the_title($post_id);
        $imageUrl = get_the_post_thumbnail_url($post_id, 'full') ?? "no tiene";

        $template = self::getBasePromptTemplate('content_img');

        $replacements = [
            '{{title}}' => $title,
            '{{customFields}}' => json_encode($customFields, JSON_UNESCAPED_UNICODE),
            '{{gpaiSeoFields}}' => json_encode($gpaiSeoFields, JSON_UNESCAPED_UNICODE),
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
        ] = $CONFIG;

        $gpaiSeoFields = $CONFIG['gpaiSeoFields'] ?? [];
        $gpaiSeoFields_prompt = $CONFIG['gpaiSeoFields_prompt'] ?? [];
        $globalFields = $CONFIG['globalFields'] ?? [];
        $templateFields = $CONFIG['templateFields'] ?? [];

        $title = get_the_title($post_id);

        $template = self::getBasePromptTemplate('content');

        $replacements = [
            '{{title}}' => $title,
            '{{customFields}}' => json_encode($customFields, JSON_UNESCAPED_UNICODE),
            '{{customFields_prompt}}' => json_encode(array_filter($customFields_prompt, 'strlen'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
             '{{gpaiSeoFields}}' => json_encode($gpaiSeoFields, JSON_UNESCAPED_UNICODE),
            '{{gpaiSeoFields_prompt}}' => json_encode(array_filter($gpaiSeoFields_prompt, 'strlen'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            '{{globalFields}}' => json_encode($globalFields, JSON_UNESCAPED_UNICODE),
            '{{templateFields}}' => json_encode($templateFields, JSON_UNESCAPED_UNICODE),
            '{{prompt}}' => $prompt,
        ];

        $sections = [
            '----CAMPOS PERSONALIZADOS----' => '{{customFields}}',
            '----PROMPTS PARA CAMPOS PERSONALIZADOS----' => '{{customFields_prompt}}',
            '----DATOS DE GPAI SEO----' => '{{gpaiSeoFields}}',
            '----PROMPTS PARA DATOS DE GPAI SEO----' => '{{gpaiSeoFields_prompt}}',
            '----CAMPOS GLOBALES----' => '{{globalFields}}',
            '----CAMPOS DE PLANTILLAS----' => '{{templateFields}}',
        ];

        $isEmpty = function ($v) {
            return in_array($v, ['{}', '[]', '""', 'null', ''], true);
        };

        foreach ($sections as $heading => $placeholder) {
            if ($isEmpty($replacements[$placeholder])) {
                $template = preg_replace(
                    '/' . preg_quote($heading, '/') . '\n' . preg_quote($placeholder, '/') . '\n*/',
                    '',
                    $template
                );
                unset($replacements[$placeholder]);
            }
        }

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
                    $PROMPTBYIMG = self::getPromptImg($CONFIG['post_id'], $value['customFields'], $value['gpaiSeoFields'] ?? []);
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

    public static function normalizeFields($item, $customFields)
    {
        $allowedCustomFields = array_keys($customFields);
        $allowedGpaiSeoFields = array_keys(GPAI_SEO::getFields());

        $normalizedCustomFields = [];
        $normalizedGpaiSeoFields = [];

        if (!empty($item['customFields']) && is_array($item['customFields'])) {
            foreach ($item['customFields'] as $key => $value) {
                if (strpos($key, 'gpai_wpseo_') === 0) {
                    if (in_array($key, $allowedGpaiSeoFields)) {
                        $normalizedGpaiSeoFields[$key] = $value;
                    }
                    continue;
                }
                if (in_array($key, $allowedCustomFields)) {
                    $normalizedCustomFields[$key] = $value;
                }
            }
        }

        if (!empty($item['gpaiSeoFields']) && is_array($item['gpaiSeoFields'])) {
            foreach ($item['gpaiSeoFields'] as $key => $value) {
                if (in_array($key, $allowedGpaiSeoFields)) {
                    $normalizedGpaiSeoFields[$key] = $value;
                }
            }
        }

        $item['customFields'] = $normalizedCustomFields;
        $item['gpaiSeoFields'] = $normalizedGpaiSeoFields;

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
