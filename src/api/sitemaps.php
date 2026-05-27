<?php

use franciscoblancojn\wordpress_utils\FWUSystemLog;

class GPAI_SITEMAPS_API
{
    public static function init()
    {
        add_action('wp_ajax_gpai_sitemap_generate', [self::class, 'generate']);
    }

    public static function getSitemapBasePrompt()
    {
        $config = new GPAI_USE_DATA_CONFIG();
        $data = $config->get();
        $promptsBase = $data['prompts_base'] ?? [];

        return $promptsBase['sitemap'] ?? self::getSitemapBasePromptDefault();
    }

    public static function getSitemapBasePromptDefault()
    {
        $file = GPAI_DIR . 'src/prompts/sitemap-v1.txt';
        if (!file_exists($file)) {
            return '';
        }
        return file_get_contents($file);
    }

    public static function generate()
    {
        $sitemap_name = isset($_POST['sitemap_name'])
            ? sanitize_text_field(wp_unslash($_POST['sitemap_name']))
            : '';
        $custom_prompt = isset($_POST['custom_prompt'])
            ? sanitize_textarea_field(wp_unslash($_POST['custom_prompt']))
            : '';

        if (empty($sitemap_name)) {
            wp_send_json_error('El nombre del Site Map es requerido.');
            return;
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Sin permisos.');
            return;
        }

        $template = self::getSitemapBasePrompt();
        $site_url = untrailingslashit(get_site_url());
        $prompt = str_replace('{{sitemap_name}}', $sitemap_name, $template);
        $prompt = str_replace('{{URL_BASE}}', $site_url, $prompt);
        $prompt = str_replace('{{custom_prompt}}', $custom_prompt, $prompt);

        $result = GPAI_AI::sendPrompt($prompt);

        if ($result['status'] === 'ok') {
            $content = $result['data'];
            $content = preg_replace('/^```xml\s*/i', '', $content);
            $content = preg_replace('/^```\s*/i', '', $content);
            $content = preg_replace('/```$/', '', $content);
            $content = trim($content);

            wp_send_json_success([
                'content' => $content,
                'message' => 'Contenido generado correctamente.',
            ]);
        } else {
            wp_send_json_error($result['message']);
        }
    }
}

GPAI_SITEMAPS_API::init();
