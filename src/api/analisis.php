<?php

class GPAI_ANALISIS
{
    public static function init()
    {
        add_action('wp_ajax_gpai_analisis_seo', array(self::class, 'analyzeSEO_ajax'));
        add_action('wp_ajax_gpai_analisis_links', array(self::class, 'validateLinks_ajax'));
        add_action('wp_ajax_gpai_analisis_pagespeed', array(self::class, 'pageSpeed_ajax'));
    }

    public static function analyzeSEO_ajax()
    {
        check_ajax_referer('gpai_nonce', 'nonce');

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

        if (!$post_id || !current_user_can('edit_post', $post_id)) {
            wp_send_json_error('Sin permisos.');
        }

        $seoFields = GPAI_SEO::GET($post_id);
        $analysis = array();
        $score = 0;
        $total = 0;

        // Title SEO
        $total++;
        $title = isset($seoFields['gpai_wpseo_title']) ? $seoFields['gpai_wpseo_title'] : '';
        $titleLen = mb_strlen($title);
        if (empty($title)) {
            $analysis[] = array(
                'type' => 'error',
                'field' => 'Titulo SEO',
                'message' => 'No definido.',
                'suggestion' => 'Define un titulo SEO unico e informativo.'
            );
        } elseif ($titleLen < 30) {
            $analysis[] = array(
                'type' => 'warning',
                'field' => 'Titulo SEO',
                'message' => $titleLen . ' caracteres.',
                'suggestion' => 'Muy corto. Se recomienda entre 50-60 caracteres.'
            );
            $score++;
        } elseif ($titleLen > 60) {
            $analysis[] = array(
                'type' => 'warning',
                'field' => 'Titulo SEO',
                'message' => $titleLen . ' caracteres.',
                'suggestion' => 'Muy largo. Se recomienda maximo 60 caracteres.'
            );
            $score++;
        } else {
            $analysis[] = array(
                'type' => 'ok',
                'field' => 'Titulo SEO',
                'message' => $titleLen . ' caracteres.',
                'suggestion' => ''
            );
            $score++;
        }

        // Meta description
        $total++;
        $desc = isset($seoFields['gpai_wpseo_metadesc']) ? $seoFields['gpai_wpseo_metadesc'] : '';
        $descLen = mb_strlen($desc);
        if (empty($desc)) {
            $analysis[] = array(
                'type' => 'error',
                'field' => 'Meta Descripcion',
                'message' => 'No definida.',
                'suggestion' => 'Define una meta descripcion atractiva que incluya la palabra clave.'
            );
        } elseif ($descLen < 120) {
            $analysis[] = array(
                'type' => 'warning',
                'field' => 'Meta Descripcion',
                'message' => $descLen . ' caracteres.',
                'suggestion' => 'Corta. Se recomienda entre 150-160 caracteres.'
            );
            $score++;
        } elseif ($descLen > 160) {
            $analysis[] = array(
                'type' => 'warning',
                'field' => 'Meta Descripcion',
                'message' => $descLen . ' caracteres.',
                'suggestion' => 'Larga. Se recomienda maximo 160 caracteres.'
            );
            $score++;
        } else {
            $analysis[] = array(
                'type' => 'ok',
                'field' => 'Meta Descripcion',
                'message' => $descLen . ' caracteres.',
                'suggestion' => ''
            );
            $score++;
        }

        // Focus keyword
        $total++;
        $kw = isset($seoFields['gpai_wpseo_focuskw']) ? $seoFields['gpai_wpseo_focuskw'] : '';
        if (empty($kw)) {
            $analysis[] = array(
                'type' => 'error',
                'field' => 'Palabra Clave',
                'message' => 'No definida.',
                'suggestion' => 'Define al menos una palabra clave principal para enfoque SEO.'
            );
        } else {
            $analysis[] = array(
                'type' => 'ok',
                'field' => 'Palabra Clave',
                'message' => '"' . esc_html($kw) . '"',
                'suggestion' => ''
            );
            $score++;
        }

        // Open Graph
        $total++;
        $ogTitle = isset($seoFields['gpai_wpseo_opengraph-title']) ? $seoFields['gpai_wpseo_opengraph-title'] : '';
        $ogDesc = isset($seoFields['gpai_wpseo_opengraph-description']) ? $seoFields['gpai_wpseo_opengraph-description'] : '';
        $ogImg = isset($seoFields['gpai_wpseo_opengraph-image']) ? $seoFields['gpai_wpseo_opengraph-image'] : '';
        $missingOG = array();
        if (empty($ogTitle)) { $missingOG[] = 'Titulo'; }
        if (empty($ogDesc)) { $missingOG[] = 'Descripcion'; }
        if (empty($ogImg)) { $missingOG[] = 'Imagen'; }
        if (!empty($missingOG)) {
            $analysis[] = array(
                'type' => 'warning',
                'field' => 'Open Graph',
                'message' => 'Faltan: ' . implode(', ', $missingOG) . '.',
                'suggestion' => 'Completa los campos OG para mejor comparticion en redes sociales.'
            );
        } else {
            $analysis[] = array(
                'type' => 'ok',
                'field' => 'Open Graph',
                'message' => 'Completo.',
                'suggestion' => ''
            );
            $score++;
        }

        // Twitter Cards
        $total++;
        $twTitle = isset($seoFields['gpai_wpseo_twitter-title']) ? $seoFields['gpai_wpseo_twitter-title'] : '';
        $twDesc = isset($seoFields['gpai_wpseo_twitter-description']) ? $seoFields['gpai_wpseo_twitter-description'] : '';
        $twImg = isset($seoFields['gpai_wpseo_twitter-image']) ? $seoFields['gpai_wpseo_twitter-image'] : '';
        $missingTW = array();
        if (empty($twTitle)) { $missingTW[] = 'Titulo'; }
        if (empty($twDesc)) { $missingTW[] = 'Descripcion'; }
        if (empty($twImg)) { $missingTW[] = 'Imagen'; }
        if (!empty($missingTW)) {
            $analysis[] = array(
                'type' => 'warning',
                'field' => 'Twitter Cards',
                'message' => 'Faltan: ' . implode(', ', $missingTW) . '.',
                'suggestion' => 'Completa los campos Twitter para mejor visualizacion en X/Twitter.'
            );
        } else {
            $analysis[] = array(
                'type' => 'ok',
                'field' => 'Twitter Cards',
                'message' => 'Completo.',
                'suggestion' => ''
            );
            $score++;
        }

        // Canonical
        $total++;
        $canonical = isset($seoFields['gpai_wpseo_canonical']) ? $seoFields['gpai_wpseo_canonical'] : '';
        if (empty($canonical)) {
            $analysis[] = array(
                'type' => 'info',
                'field' => 'URL Canonica',
                'message' => 'No definida.',
                'suggestion' => 'Define canonical solo si hay contenido duplicado en otras URLs.'
            );
            $score++;
        } else {
            $analysis[] = array(
                'type' => 'ok',
                'field' => 'URL Canonica',
                'message' => 'Definida.',
                'suggestion' => ''
            );
            $score++;
        }

        // Schema
        $total++;
        $pageType = isset($seoFields['gpai_wpseo_schema_page_type']) ? $seoFields['gpai_wpseo_schema_page_type'] : '';
        $articleType = isset($seoFields['gpai_wpseo_schema_article_type']) ? $seoFields['gpai_wpseo_schema_article_type'] : '';
        if (empty($pageType) && empty($articleType)) {
            $analysis[] = array(
                'type' => 'info',
                'field' => 'Schema',
                'message' => 'Tipos no definidos.',
                'suggestion' => 'Define tipo de pagina/articulo para mejorar datos estructurados.'
            );
            $score++;
        } else {
            $analysis[] = array(
                'type' => 'ok',
                'field' => 'Schema',
                'message' => 'Tipos definidos.',
                'suggestion' => ''
            );
            $score++;
        }

        // Active status
        $total++;
        $active = isset($seoFields['gpai_wpseo_active']) ? $seoFields['gpai_wpseo_active'] : '';
        if ($active !== '1') {
            $analysis[] = array(
                'type' => 'error',
                'field' => 'Estado GPAI SEO',
                'message' => 'Inactivo.',
                'suggestion' => 'Activa GPAI SEO para que los campos tengan efecto en el frontend.'
            );
        } else {
            $analysis[] = array(
                'type' => 'ok',
                'field' => 'Estado GPAI SEO',
                'message' => 'Activo.',
                'suggestion' => ''
            );
            $score++;
        }

        wp_send_json_success(array(
            'items' => $analysis,
            'score' => $score,
            'total' => $total,
            'pct' => $total > 0 ? round(($score / $total) * 100) : 0
        ));
    }

    public static function validateLinks_ajax()
    {
        check_ajax_referer('gpai_nonce', 'nonce');

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

        if (!$post_id || !current_user_can('edit_post', $post_id)) {
            wp_send_json_error('Sin permisos.');
        }

        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error('Post no encontrado.');
        }

        $content = $post->post_content;

        $elementor_data = get_post_meta($post_id, '_elementor_data', true);
        if (!empty($elementor_data) && is_string($elementor_data)) {
            $content .= ' ' . $elementor_data;
        }

        $links = self::extractLinks($content);

        if (empty($links)) {
            wp_send_json_success(array(
                'results' => array(),
                'total' => 0,
                'ok' => 0,
                'redirect' => 0,
                'error' => 0
            ));
        }

        $site_url = home_url();
        $parsed_site = parse_url($site_url);
        $site_host = isset($parsed_site['host']) ? $parsed_site['host'] : '';

        $results = array();
        $count_ok = 0;
        $count_redirect = 0;
        $count_error = 0;
        $checked = 0;

        foreach ($links as $link) {
            $parsed = parse_url($link);
            $is_internal = false;

            if (empty($parsed['host'])) {
                $is_internal = true;
            } elseif (isset($parsed['host']) && $parsed['host'] === $site_host) {
                $is_internal = true;
            }

            if (!$is_internal) {
                continue;
            }

            if (empty($parsed['host'])) {
                $url = home_url($link);
            } else {
                $url = $link;
            }

            if ($checked > 0) {
                usleep(150000);
            }
            $checked++;

            $status = self::checkLinkStatus($url);

            $type = 'error';
            if ($status >= 200 && $status < 300) {
                $type = 'ok';
                $count_ok++;
            } elseif ($status >= 300 && $status < 400) {
                $type = 'redirect';
                $count_redirect++;
            } elseif ($status >= 400) {
                $type = 'error';
                $count_error++;
            } elseif ($status === 0) {
                $type = 'error';
                $count_error++;
            }

            $results[] = array(
                'url' => $url,
                'href' => $link,
                'status' => $status,
                'type' => $type,
                'status_text' => $status >= 100 ? self::getStatusText($status) : 'Error de conexion'
            );
        }

        wp_send_json_success(array(
            'results' => $results,
            'total' => count($results),
            'ok' => $count_ok,
            'redirect' => $count_redirect,
            'error' => $count_error
        ));
    }

    public static function pageSpeed_ajax()
    {
        check_ajax_referer('gpai_nonce', 'nonce');

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

        if (!$post_id || !current_user_can('edit_post', $post_id)) {
            wp_send_json_error('Sin permisos.');
        }

        $url = get_permalink($post_id);
        if (!$url) {
            wp_send_json_error('URL no disponible.');
        }

        $ps_url = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';
        $ps_url .= '?url=' . urlencode($url);
        $ps_url .= '&strategy=mobile';
        $ps_url .= '&category=performance';
        $ps_url .= '&category=seo';
        $ps_url .= '&category=accessibility';
        $ps_url .= '&category=best-practices';

        $response = wp_remote_get($ps_url, array(
            'timeout' => 30,
            'user-agent' => 'GPAI-PageSpeed/1.0'
        ));

        if (is_wp_error($response)) {
            wp_send_json_success(array(
                'fallback' => true,
                'url' => 'https://pagespeed.web.dev/analysis?url=' . urlencode($url),
                'error' => $response->get_error_message()
            ));
            return;
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($code !== 200) {
            wp_send_json_success(array(
                'fallback' => true,
                'url' => 'https://pagespeed.web.dev/analysis?url=' . urlencode($url),
                'error' => 'API respondio con codigo ' . $code
            ));
            return;
        }

        $data = json_decode($body, true);

        if (!$data || !isset($data['lighthouseResult'])) {
            wp_send_json_error('No se pudo analizar la respuesta de PageSpeed.');
            return;
        }

        $lighthouse = $data['lighthouseResult'];
        $categories = isset($lighthouse['categories']) ? $lighthouse['categories'] : array();

        $result = array(
            'fallback' => false,
            'url' => 'https://pagespeed.web.dev/analysis?url=' . urlencode($url),
            'performance' => isset($categories['performance']['score']) ? round($categories['performance']['score'] * 100) : null,
            'seo' => isset($categories['seo']['score']) ? round($categories['seo']['score'] * 100) : null,
            'accessibility' => isset($categories['accessibility']['score']) ? round($categories['accessibility']['score'] * 100) : null,
            'best_practices' => isset($categories['best-practices']['score']) ? round($categories['best-practices']['score'] * 100) : null,
        );

        wp_send_json_success($result);
    }

    private static function extractLinks($content)
    {
        $links = array();
        preg_match_all('/href=["\']([^"\']+)["\']/si', $content, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $link) {
                $link = trim($link);
                if (empty($link) || $link === '#') { continue; }
                if (strpos($link, 'javascript:') === 0) { continue; }
                if (strpos($link, 'mailto:') === 0) { continue; }
                if (strpos($link, 'tel:') === 0) { continue; }
                $links[] = $link;
            }
        }

        return array_unique($links);
    }

    private static function checkLinkStatus($url)
    {
        $response = wp_remote_get($url, array(
            'timeout' => 8,
            'redirection' => 0,
            'user-agent' => 'Mozilla/5.0 (compatible; GPAI Link Checker/1.0)',
            'sslverify' => false,
            'blocking' => true,
        ));

        if (is_wp_error($response)) {
            return 0;
        }

        return wp_remote_retrieve_response_code($response);
    }

    private static function getStatusText($code)
    {
        $codes = array(
            200 => 'OK',
            201 => 'Created',
            204 => 'No Content',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            307 => 'Temporary Redirect',
            308 => 'Permanent Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            410 => 'Gone',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
        );

        return isset($codes[$code]) ? $codes[$code] : 'Unknown';
    }
}

add_action('admin_init', array('GPAI_ANALISIS', 'init'));
