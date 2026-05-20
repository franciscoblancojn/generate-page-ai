<?php

use franciscoblancojn\wordpress_utils\FWUSystemLog;

class GPAI_SEO
{
    public static function getFields()
    {
        return [
            'gpai_wpseo_title' => 'Título SEO',
            'gpai_wpseo_metadesc' => 'Meta Descripción',
            'gpai_wpseo_focuskw' => 'Palabra Clave',
            'gpai_wpseo_focuskeywords' => 'Palabras Clave (JSON)',
            'gpai_wpseo_canonical' => 'URL Canónica',
            'gpai_wpseo_bctitle' => 'Título de Breadcrumb',
            'gpai_wpseo_redirect' => 'Redirección',
            'gpai_wpseo_is_cornerstone' => 'Contenido Cornerstone',
            'gpai_wpseo_meta-robots-noindex' => 'No Index',
            'gpai_wpseo_meta-robots-nofollow' => 'No Follow',
            'gpai_wpseo_meta-robots-adv' => 'Robots Avanzado',
            'gpai_wpseo_meta-robots-noarchive' => 'No Archive',
            'gpai_wpseo_meta-robots-nosnippet' => 'No Snippet',
            'gpai_wpseo_meta-robots-noimageindex' => 'No Image Index',
            'gpai_wpseo_opengraph-title' => 'OG Título',
            'gpai_wpseo_opengraph-description' => 'OG Descripción',
            'gpai_wpseo_opengraph-image' => 'OG Imagen',
            'gpai_wpseo_opengraph-image-id' => 'OG Imagen ID',
            'gpai_wpseo_opengraph-url' => 'OG URL',
            'gpai_wpseo_twitter-title' => 'Twitter Título',
            'gpai_wpseo_twitter-description' => 'Twitter Descripción',
            'gpai_wpseo_twitter-image' => 'Twitter Imagen',
            'gpai_wpseo_schema_page_type' => 'Schema Tipo de Página',
            'gpai_wpseo_schema_article_type' => 'Schema Tipo de Artículo',
            'gpai_wpseo_schema_extra_json' => 'Schema Bloques Adicionales (JSON)',
        ];
    }

    public static function GET($post_id)
    {
        $fields = self::getFields();
        $values = [];
        foreach ($fields as $key => $label) {
            $value = get_post_meta($post_id, $key, true);
            if ($value !== '') {
                $values[$key] = $value;
            } else {
                $values[$key] = '';
            }
        }
        return $values;
    }

    public static function SET($post_id, $data)
    {
        $allowed = array_keys(self::getFields());
        $saved = [];
        $skipped = [];
        foreach ($data as $key => $value) {
            if (!in_array($key, $allowed)) {
                $skipped[] = $key;
                continue;
            }
            $original = $value;
            if (is_array($value)) {
                $value = wp_json_encode($value);
            } else {
                $value = wp_kses_post($value);
            }
            if ($value !== '') {
                update_post_meta($post_id, $key, $value);
                $saved[$key] = $value;
            } else {
                delete_post_meta($post_id, $key);
                $saved[$key] = '(deleted)';
            }
        }
        FWUSystemLog::add(GPAI_KEY, [
            'type' => 'GPAI_SEO_SET',
            'post_id' => $post_id,
            'saved' => $saved,
            'skipped' => $skipped,
        ]);
    }

    public static function getGroups()
    {
        return [
            'Principales' => [
                'gpai_wpseo_title',
                'gpai_wpseo_metadesc',
                'gpai_wpseo_focuskw',
                'gpai_wpseo_focuskeywords',
                'gpai_wpseo_canonical',
                'gpai_wpseo_bctitle',
                'gpai_wpseo_redirect',
                'gpai_wpseo_is_cornerstone',
            ],
            'Robots' => [
                'gpai_wpseo_meta-robots-noindex',
                'gpai_wpseo_meta-robots-nofollow',
                'gpai_wpseo_meta-robots-adv',
                'gpai_wpseo_meta-robots-noarchive',
                'gpai_wpseo_meta-robots-nosnippet',
                'gpai_wpseo_meta-robots-noimageindex',
            ],
            'Open Graph' => [
                'gpai_wpseo_opengraph-title',
                'gpai_wpseo_opengraph-description',
                'gpai_wpseo_opengraph-image',
                'gpai_wpseo_opengraph-image-id',
                'gpai_wpseo_opengraph-url',
            ],
            'Twitter' => [
                'gpai_wpseo_twitter-title',
                'gpai_wpseo_twitter-description',
                'gpai_wpseo_twitter-image',
            ],
            'Schema' => [
                'gpai_wpseo_schema_page_type',
                'gpai_wpseo_schema_article_type',
                'gpai_wpseo_schema_extra_json',
            ],
        ];
    }

    public static function getSEOBasePromptDefault()
    {
        $file = GPAI_DIR . 'src/prompts/seo-v1.txt';
        if (!file_exists($file)) return '';
        return file_get_contents($file);
    }

    public static function getSEOPrompt($post_id, $customPrompt = '')
    {
        $title = get_the_title($post_id);
        $post = get_post($post_id);
        $postContent = $post ? wp_trim_words($post->post_content, 200, '...') : '';
        $currentFields = self::GET($post_id);

        $template = self::getSEOBasePromptDefault();

        $replacements = [
            '{{title}}' => $title,
            '{{postContent}}' => $postContent,
            '{{currentSeoFields}}' => wp_json_encode($currentFields, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            '{{prompt}}' => $customPrompt ?: 'Genera datos SEO optimizados para esta página.',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    public static function generateSEO($post_id, $promptText = '')
    {
        try {
            $PROMPT = self::getSEOPrompt($post_id, $promptText);
            $result = GPAI_AI::sendPrompt($PROMPT);

            if ($result['status'] === 'error') {
                return $result;
            }

            $data = GPAI_AI::parseJson($result['data']);

            $allowed = array_keys(self::getFields());
            $toSave = [];
            foreach ($data as $key => $value) {
                if (in_array($key, $allowed)) {
                    $toSave[$key] = $value;
                }
            }

            if (!empty($toSave)) {
                self::SET($post_id, $toSave);
            }

            FWUSystemLog::add(GPAI_KEY, [
                'type' => 'GPAI_SEO_AI_GENERATE',
                'post_id' => $post_id,
                'generated' => $toSave,
            ]);

            return [
                'status' => 'ok',
                'message' => 'SEO generado correctamente.',
                'data' => $toSave,
            ];
        } catch (\Throwable $th) {
            $error = [
                'status' => 'error',
                'message' => $th->getMessage(),
                'data' => [
                    'line' => $th->getLine(),
                    'file' => $th->getFile(),
                ],
            ];
            FWUSystemLog::add(GPAI_KEY, [
                'type' => 'GPAI_SEO_AI_ERROR',
                'post_id' => $post_id,
                'error' => $error,
            ]);
            return $error;
        }
    }

    public static function generateSEO_ajax()
    {
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $prompt = isset($_POST['prompt']) ? sanitize_text_field(wp_unslash($_POST['prompt'])) : '';
        $nonce = $_POST['nonce'] ?? '';

        if (!$post_id || !wp_verify_nonce($nonce, 'gpai_seo_generate_' . $post_id)) {
            wp_send_json_error('Nonce inválido.');
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            wp_send_json_error('Sin permisos.');
            return;
        }

        $result = self::generateSEO($post_id, $prompt);
        if ($result['status'] === 'ok') {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    public static function getHTMLBasePromptDefault()
    {
        $file = GPAI_DIR . 'src/prompts/html-v1.txt';
        if (!file_exists($file)) return '';
        return file_get_contents($file);
    }

    public static function getHTMLPrompt($post_id)
    {
        $fullPath = get_post_meta($post_id, 'STPA_PAGE_STATIC_HTML_FILE', true);
        if (!$fullPath) {
            return ['status' => 'error', 'message' => 'Este post no tiene archivo HTML estático.'];
        }

        if (!file_exists($fullPath)) {
            return ['status' => 'error', 'message' => 'El archivo HTML estático no existe en el servidor.'];
        }

        $htmlContent = file_get_contents($fullPath);
        if ($htmlContent === false || trim($htmlContent) === '') {
            return ['status' => 'error', 'message' => 'El archivo HTML estático está vacío.'];
        }

        $template = self::getHTMLBasePromptDefault();
        if (empty($template)) {
            return ['status' => 'error', 'message' => 'No se encontró el prompt base para HTML.'];
        }

        $prompt = str_replace('{{htmlContent}}', $htmlContent, $template);

        return [
            'status' => 'ok',
            'data' => $prompt,
            'originalPath' => $fullPath,
        ];
    }

    public static function generateHTML($post_id)
    {
        try {
            $promptResult = self::getHTMLPrompt($post_id);
            if ($promptResult['status'] === 'error') {
                return $promptResult;
            }

            $PROMPT = $promptResult['data'];
            $originalPath = $promptResult['originalPath'];

            $result = GPAI_AI::sendPrompt($PROMPT);

            if ($result['status'] === 'error') {
                return $result;
            }

            $optimizedHtml = $result['data'];

            $optimizedPath = str_replace('.html', '-optimize.html', $originalPath);
            $bytesWritten = file_put_contents($optimizedPath, $optimizedHtml);

            if ($bytesWritten === false) {
                return [
                    'status' => 'error',
                    'message' => 'No se pudo escribir el archivo HTML optimizado.',
                ];
            }

            update_post_meta($post_id, 'STPA_PAGE_STATIC_HTML_FILE_OPTIMIZE', $optimizedPath);

            $uploadDir = wp_upload_dir();
            $originalUrl = str_replace(
                $uploadDir['basedir'],
                $uploadDir['baseurl'],
                $originalPath
            );
            $optimizedUrl = str_replace(
                $uploadDir['basedir'],
                $uploadDir['baseurl'],
                $optimizedPath
            );

            FWUSystemLog::add(GPAI_KEY, [
                'type' => 'GPAI_HTML_AI_GENERATE',
                'post_id' => $post_id,
                'original' => $originalUrl,
                'optimized' => $optimizedUrl,
                'original_size' => filesize($originalPath),
                'optimized_size' => $bytesWritten,
            ]);

            return [
                'status' => 'ok',
                'message' => 'HTML optimizado correctamente.',
                'data' => [
                    'original' => $originalUrl,
                    'optimized' => $optimizedUrl,
                    'original_size' => filesize($originalPath),
                    'optimized_size' => $bytesWritten,
                ],
            ];
        } catch (\Throwable $th) {
            $error = [
                'status' => 'error',
                'message' => $th->getMessage(),
                'data' => [
                    'line' => $th->getLine(),
                    'file' => $th->getFile(),
                ],
            ];
            FWUSystemLog::add(GPAI_KEY, [
                'type' => 'GPAI_HTML_AI_ERROR',
                'post_id' => $post_id,
                'error' => $error,
            ]);
            return $error;
        }
    }

    public static function generateHTML_ajax()
    {
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $nonce = $_POST['nonce'] ?? '';

        if (!$post_id || !wp_verify_nonce($nonce, 'gpai_html_generate_' . $post_id)) {
            wp_send_json_error('Nonce inválido.');
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            wp_send_json_error('Sin permisos.');
            return;
        }

        $result = self::generateHTML($post_id);
        if ($result['status'] === 'ok') {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['message']);
        }
    }
}

add_action('wp_ajax_gpai_seo_generate', ['GPAI_SEO', 'generateSEO_ajax']);
add_action('wp_ajax_gpai_html_generate', ['GPAI_SEO', 'generateHTML_ajax']);
