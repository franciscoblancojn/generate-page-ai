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

    public static function getPostImages($post_id)
    {
        $images = [];

        $thumb_id = get_post_thumbnail_id($post_id);
        if ($thumb_id) {
            $thumb_url = wp_get_attachment_url($thumb_id);
            if ($thumb_url) $images[] = $thumb_url;
        }

        $post = get_post($post_id);
        if ($post && !empty($post->post_content)) {
            preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $post->post_content, $matches);
            if (!empty($matches[1])) {
                foreach ($matches[1] as $url) {
                    $url = strtok($url, '?');
                    if (!in_array($url, $images)) {
                        $images[] = $url;
                    }
                }
            }
        }

        $gallery_ids = get_post_meta($post_id, '_product_image_gallery', true);
        if (!empty($gallery_ids)) {
            foreach (explode(',', $gallery_ids) as $gid) {
                $gurl = wp_get_attachment_url((int) $gid);
                if ($gurl && !in_array($gurl, $images)) {
                    $images[] = $gurl;
                }
            }
        }

        return $images;
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

        $enabled_posts = get_option('GPAI_SITEMAP_URLS', []);
        $paginas_lines = [];
        $posts_lines = [];
        $paginas_images = [];
        $posts_images = [];
        $skipped = [];
        foreach ($enabled_posts as $post_id) {
            $post = get_post($post_id);
            if (!$post) continue;
            $permalink = get_permalink($post_id);
            if (!$permalink) continue;

            if (strpos($permalink, '?page_id=') !== false) {
                $skipped[] = "{$permalink} (?page_id)";
                continue;
            }

            $response = wp_remote_head($permalink, [
                'timeout' => 5,
                'blocking' => true,
            ]);
            if (is_wp_error($response) || wp_remote_retrieve_response_code($response) === 404) {
                $skipped[] = "{$permalink} (404)";
                continue;
            }

            $lastmod = get_the_modified_date('Y-m-d', $post_id);
            $line = "{$permalink} (lastmod: {$lastmod})";
            if ($post->post_type === 'page') {
                $paginas_lines[] = $line;
            } else {
                $posts_lines[] = $line;
            }
            $imgs = self::getPostImages($post_id);
            if (!empty($imgs)) {
                $img_block = "URL: {$permalink}\n" . implode("\n", array_map(function ($u) {
                    return "  - {$u}";
                }, $imgs));
                if ($post->post_type === 'page') {
                    $paginas_images[] = $img_block;
                } else {
                    $posts_images[] = $img_block;
                }
            }
        }
        $paginas_list = !empty($paginas_lines) ? implode("\n", $paginas_lines) : 'No hay paginas configuradas.';
        $posts_list = !empty($posts_lines) ? implode("\n", $posts_lines) : 'No hay posts configurados.';
        $prompt = str_replace('{{URL_PAGINAS_LIST}}', $paginas_list, $prompt);
        $prompt = str_replace('{{URL_POSTS_LIST}}', $posts_list, $prompt);

        $paginas_images_block = !empty($paginas_images) ? implode("\n\n", $paginas_images) : 'No se encontraron imagenes en paginas.';
        $posts_images_block = !empty($posts_images) ? implode("\n\n", $posts_images) : 'No se encontraron imagenes en posts.';
        $prompt = str_replace('{{PAGINAS_IMAGES}}', $paginas_images_block, $prompt);
        $prompt = str_replace('{{POSTS_IMAGES}}', $posts_images_block, $prompt);
        $prompt = str_replace('{{custom_prompt}}', $custom_prompt, $prompt);
            FWUSystemLog::add(GPAI_KEY, [
                'type' => "prompt",
                'data' => $prompt
            ]);

        $result = GPAI_AI::sendPrompt($prompt);

        if ($result['status'] === 'ok') {
            $content = $result['data'];
            $content = preg_replace('/^```xml\s*/i', '', $content);
            $content = preg_replace('/^```\s*/i', '', $content);
            $content = preg_replace('/```$/', '', $content);
            $content = trim($content);

            $message = 'Contenido generado correctamente.';
            if (!empty($skipped)) {
                $message .= ' URLs omitidas: ' . implode(', ', $skipped);
            }
            wp_send_json_success([
                'content' => $content,
                'message' => $message,
            ]);
        } else {
            wp_send_json_error($result['message']);
        }
    }
}

GPAI_SITEMAPS_API::init();
