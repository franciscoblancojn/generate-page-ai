<?php

use franciscoblancojn\wordpress_utils\FWUSystemLog;

class GPAI_SITEMAPS_API
{
    public static function init()
    {
        add_action('wp_ajax_gpai_sitemap_generate', [self::class, 'generate']);
        add_action('wp_ajax_gpai_sitemap_save_generate', [self::class, 'saveAndGenerate']);
        add_action('wp_ajax_gpai_sitemap_save_xml', [self::class, 'saveXml']);
    }

    public static function getEnabledPosts($sitemap_name)
    {
        $sitemap_file = $sitemap_name . '.xml';
        $all_configs = get_option('GPAI_SITEMAP_CONFIGS', []);
        if (isset($all_configs[$sitemap_file]) && !empty($all_configs[$sitemap_file]['enabled_posts'])) {
            return $all_configs[$sitemap_file]['enabled_posts'];
        }
        return get_option('GPAI_SITEMAP_URLS', []);
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

    public static function buildSitemapXml($sitemap_name, $enabled_posts, $config = [])
    {
        $defaults = [
            'changefreq_page' => 'monthly',
            'priority_page' => '0.8',
            'changefreq_post' => 'weekly',
            'priority_post' => '0.9',
            'changefreq_default' => 'monthly',
            'priority_default' => '0.5',
            'include_images' => '1',
        ];
        $config = array_merge($defaults, $config);

        $home_url = home_url('/');

        $xsl_url = GPAI_URL . 'src/css/sitemap.xsl';
        $xsl_decl = '<?xml-stylesheet type="text/xsl" href="' . esc_url($xsl_url) . '"?>';

        $include_images = !empty($config['include_images']) && $config['include_images'] === '1';
        $image_ns = $include_images
            ? ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"'
            : '';

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        if (!empty($xsl_decl)) {
            $xml .= $xsl_decl . "\n";
        }
        $xml .= '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . $image_ns . ' xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd' . ($include_images ? ' http://www.google.com/schemas/sitemap-image/1.1 http://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd' : '') . '" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

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

            if ($post->post_type === 'page') {
                $changefreq = $config['changefreq_page'];
                $priority  = $config['priority_page'];
            } elseif ($post->post_type === 'post') {
                $changefreq = $config['changefreq_post'];
                $priority  = $config['priority_post'];
            } else {
                $changefreq = $config['changefreq_default'];
                $priority  = $config['priority_default'];
            }

            if (untrailingslashit($permalink) === untrailingslashit($home_url)) {
                $priority = '1.0';
            }

            $lastmod = get_the_modified_time('c', $post_id);

            $xml .= "\t<url>\n";
            $xml .= "\t\t<loc>" . esc_url($permalink) . "</loc>\n";
            $xml .= "\t\t<lastmod>" . esc_html($lastmod) . "</lastmod>\n";
            $xml .= "\t\t<changefreq>" . esc_html($changefreq) . "</changefreq>\n";
            $xml .= "\t\t<priority>" . esc_html($priority) . "</priority>\n";

            if ($include_images) {
                $images = self::getPostImages($post_id);
                foreach ($images as $img_url) {
                    $xml .= "\t\t<image:image>\n";
                    $xml .= "\t\t\t<image:loc>" . esc_url($img_url) . "</image:loc>\n";
                    $xml .= "\t\t</image:image>\n";
                }
            }

            $xml .= "\t</url>\n";
        }

        $xml .= '</urlset>';

        return [
            'xml' => $xml,
            'skipped' => $skipped,
        ];
    }

    public static function generate()
    {
        $sitemap_name = isset($_POST['sitemap_name'])
            ? sanitize_text_field(wp_unslash($_POST['sitemap_name']))
            : '';

        if (empty($sitemap_name)) {
            wp_send_json_error('El nombre del Site Map es requerido.');
            return;
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Sin permisos.');
            return;
        }

        $enabled_posts = self::getEnabledPosts($sitemap_name);
        $sitemap_file  = $sitemap_name . '.xml';
        $all_configs   = get_option('GPAI_SITEMAP_CONFIGS', []);
        $config        = $all_configs[$sitemap_file] ?? [];

        $result = self::buildSitemapXml($sitemap_name, $enabled_posts, $config);

        $message = 'XML generado correctamente.';
        if (!empty($result['skipped'])) {
            $message .= ' URLs omitidas: ' . implode(', ', $result['skipped']);
        }

        wp_send_json_success([
            'content' => $result['xml'],
            'message' => $message,
        ]);
    }

    public static function saveXml()
    {
        $sitemap_name = isset($_POST['sitemap_name'])
            ? sanitize_text_field(wp_unslash($_POST['sitemap_name'])) : '';
        $xml_content = isset($_POST['xml_content'])
            ? wp_unslash($_POST['xml_content']) : '';

        if (empty($sitemap_name) || empty($xml_content)) {
            wp_send_json_error('Faltan parametros.');
            return;
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Sin permisos.');
            return;
        }

        $sitemap_file = $sitemap_name . '.xml';
        $dataSitemaps = new GPAI_USE_DATA_SITEMAPS();
        $saved = $dataSitemaps->saveSitemap($sitemap_file, $xml_content);

        if ($saved) {
            wp_send_json_success([
                'message' => 'XML guardado correctamente en ' . esc_html($sitemap_file),
            ]);
        } else {
            wp_send_json_error('Error al guardar el archivo XML.');
        }
    }

    public static function saveAndGenerate()
    {
        $sitemap_name = isset($_POST['sitemap_name'])
            ? sanitize_text_field(wp_unslash($_POST['sitemap_name'])) : '';

        if (empty($sitemap_name)) {
            wp_send_json_error('El nombre del Site Map es requerido.');
            return;
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Sin permisos.');
            return;
        }

        $sitemap_file = $sitemap_name . '.xml';

        $enabled_posts = isset($_POST['enabled_posts']) && is_array($_POST['enabled_posts'])
            ? array_map('intval', $_POST['enabled_posts']) : [];

        $config = [
            'enabled_posts' => $enabled_posts,
            'changefreq_page' => sanitize_text_field($_POST['changefreq_page'] ?? 'monthly'),
            'priority_page' => sanitize_text_field($_POST['priority_page'] ?? '0.8'),
            'changefreq_post' => sanitize_text_field($_POST['changefreq_post'] ?? 'weekly'),
            'priority_post' => sanitize_text_field($_POST['priority_post'] ?? '0.9'),
            'changefreq_default' => sanitize_text_field($_POST['changefreq_default'] ?? 'monthly'),
            'priority_default' => sanitize_text_field($_POST['priority_default'] ?? '0.5'),
            'include_images' => !empty($_POST['include_images']) ? '1' : '0',
        ];

        $all_configs = get_option('GPAI_SITEMAP_CONFIGS', []);
        $all_configs[$sitemap_file] = $config;
        update_option('GPAI_SITEMAP_CONFIGS', $all_configs);

        $result = self::buildSitemapXml($sitemap_name, $enabled_posts, $config);

        $message = 'Configuracion guardada y XML generado correctamente. Revisa el XML y usa "Guardar XML" para escribirlo al archivo.';
        if (!empty($result['skipped'])) {
            $message .= ' URLs omitidas: ' . implode(', ', $result['skipped']);
        }

        wp_send_json_success([
            'content' => $result['xml'],
            'message' => $message,
        ]);
    }
}

GPAI_SITEMAPS_API::init();
