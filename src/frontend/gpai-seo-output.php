<?php

function GPAI_SEO_is_active($post_id)
{
    return get_post_meta($post_id, 'gpai_wpseo_active', true) === '1';
}

function GPAI_SEO_clean_text($text)
{
    if (function_exists('GPAI_replace_custom_vars')) {
        $text = GPAI_replace_custom_vars($text);
    }
    return wp_strip_all_tags($text);
}

function GPAI_SEO_output()
{
    if (!is_singular() && !is_front_page()) return;
    $post_id = get_queried_object_id();
    if (is_front_page() && 'page' === get_option('show_on_front')) {
        $post_id = get_option('page_on_front');
    }
    if (!$post_id) return;
    if (!GPAI_SEO_is_active($post_id)) return;

    $values = GPAI_SEO::GET($post_id);
    $hasValues = false;
    foreach ($values as $v) {
        if ($v !== '') {
            $hasValues = true;
            break;
        }
    }
    if (!$hasValues) return;

    $post = get_post($post_id);
    $title = GPAI_SEO_clean_text($values['gpai_wpseo_title']) ?: get_the_title($post_id);
    $desc = GPAI_SEO_clean_text($values['gpai_wpseo_metadesc']) ?: '';
    $canonical = $values['gpai_wpseo_canonical'] ?: get_permalink($post_id);
    $ogTitle = GPAI_SEO_clean_text($values['gpai_wpseo_opengraph-title']) ?: $title;
    $ogDesc = GPAI_SEO_clean_text($values['gpai_wpseo_opengraph-description']) ?: $desc;
    $ogImage = $values['gpai_wpseo_opengraph-image'] ?: '';
    $ogUrl = $values['gpai_wpseo_opengraph-url'] ?: $canonical;
    $twTitle = GPAI_SEO_clean_text($values['gpai_wpseo_twitter-title']) ?: $ogTitle;
    $twDesc = GPAI_SEO_clean_text($values['gpai_wpseo_twitter-description']) ?: $ogDesc;
    $twImage = $values['gpai_wpseo_twitter-image'] ?: $ogImage;

    echo "\n<!-- GPAI SEO Meta Tags -->\n";

    if ($desc) {
        echo '<meta name="description" content="' . esc_attr($desc) . '" class="gpai-seo-meta-tag">' . "\n";
    }
    if ($values['gpai_wpseo_canonical']) {
        echo '<link rel="canonical" href="' . esc_url($canonical) . '" class="gpai-seo-meta-tag">' . "\n";
    }

    echo '<meta property="og:title" content="' . esc_attr($ogTitle) . '" class="gpai-seo-meta-tag">' . "\n";
    if ($ogDesc) {
        echo '<meta property="og:description" content="' . esc_attr($ogDesc) . '" class="gpai-seo-meta-tag">' . "\n";
    }
    echo '<meta property="og:url" content="' . esc_url($ogUrl) . '" class="gpai-seo-meta-tag">' . "\n";
    echo '<meta property="og:type" content="website" class="gpai-seo-meta-tag">' . "\n";
    if ($ogImage) {
        echo '<meta property="og:image" content="' . esc_url($ogImage) . '" class="gpai-seo-meta-tag">' . "\n";
    }

    echo '<meta name="twitter:card" content="summary_large_image" class="gpai-seo-meta-tag">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($twTitle) . '" class="gpai-seo-meta-tag">' . "\n";
    if ($twDesc) {
        echo '<meta name="twitter:description" content="' . esc_attr($twDesc) . '" class="gpai-seo-meta-tag">' . "\n";
    }
    if ($twImage) {
        echo '<meta name="twitter:image" content="' . esc_url($twImage) . '" class="gpai-seo-meta-tag">' . "\n";
    }

    $noindex = $values['gpai_wpseo_meta-robots-noindex'] ?? '0';
    $nofollow = $values['gpai_wpseo_meta-robots-nofollow'] ?? '0';
    $content = ($noindex === '1' ? 'noindex' : 'index') . ',' . ($nofollow === '1' ? 'nofollow' : 'follow');
    echo '<meta name="robots" content="' . esc_attr($content) . '" class="gpai-seo-meta-tag">' . "\n";

    GPAI_SEO_output_jsonld($post_id, $post, $values, $title, $desc, $canonical, $ogImage);

    echo "<!-- /GPAI SEO Meta Tags -->\n\n";
}
add_action('wp_head', 'GPAI_SEO_output', 20);

function GPAI_SEO_maybe_start_canonical_buffer()
{
    if (!is_singular() && !is_front_page()) return;
    $post_id = get_queried_object_id();
    if (is_front_page() && 'page' === get_option('show_on_front')) {
        $post_id = get_option('page_on_front');
    }
    if (!$post_id) return;
    if (!GPAI_SEO_is_active($post_id)) return;
    $canonical = get_post_meta($post_id, 'gpai_wpseo_canonical', true);
    if ($canonical) {
        ob_start('GPAI_SEO_remove_other_canonical');
    }
}
add_action('wp_head', 'GPAI_SEO_maybe_start_canonical_buffer', 0);

function GPAI_SEO_output_jsonld($post_id, $post, $values, $title, $desc, $canonical, $ogImage)
{
    $post_type = get_post_type($post_id);
    $pageType = 'WebPage';
    if (!empty($values['gpai_wpseo_schema_page_type'])) {
        $pageType = $values['gpai_wpseo_schema_page_type'];
    }
    if (!empty($values['gpai_wpseo_schema_article_type']) && $post_type === 'post') {
        $pageType = $values['gpai_wpseo_schema_article_type'];
    }

    $siteName  = get_bloginfo('name');
    $siteUrl   = home_url('/');
    $siteDesc  = get_bloginfo('description');
    $lang      = get_bloginfo('language');
    $logoId    = get_option('site_icon');
    $logoUrl   = $logoId ? wp_get_attachment_url($logoId) : '';

    $graph = [];

    // --- WebPage ---
    $webPage = [
        '@type'        => $pageType,
        '@id'          => $canonical . '#webpage',
        'url'          => $canonical,
        'name'         => GPAI_SEO_clean_text($title),
        'inLanguage'   => $lang,
        'isPartOf'     => ['@id' => $siteUrl . '#website'],
        'publisher'    => ['@id' => $siteUrl . '#organization'],
    ];
    if ($desc) {
        $webPage['description'] = GPAI_SEO_clean_text($desc);
    }
    if ($ogImage) {
        $webPage['image'] = $ogImage;
    }
    if (!empty($values['gpai_wpseo_focuskw'])) {
        $webPage['keywords'] = GPAI_SEO_clean_text($values['gpai_wpseo_focuskw']);
    }
    if ($post) {
        $webPage['datePublished'] = get_the_date('c', $post_id);
        $webPage['dateModified']  = get_the_modified_date('c', $post_id);
    }
    $graph[] = $webPage;

    // --- WebSite ---
    $webSite = [
        '@type'    => 'WebSite',
        '@id'      => $siteUrl . '#website',
        'url'      => $siteUrl,
        'name'     => $siteName,
        'publisher' => ['@id' => $siteUrl . '#organization'],
    ];
    if ($siteDesc) {
        $webSite['description'] = $siteDesc;
    }
    $webSite['potentialAction'] = [[
        '@type'         => 'SearchAction',
        'target'        => [
            '@type'       => 'EntryPoint',
            'urlTemplate' => $siteUrl . '?s={search_term_string}',
        ],
        'query-input'   => 'required name=search_term_string',
    ]];
    $graph[] = $webSite;

    // --- Organization ---
    $org = [
        '@type' => 'Organization',
        '@id'   => $siteUrl . '#organization',
        'name'  => $siteName,
        'url'   => $siteUrl,
    ];
    if ($logoUrl) {
        $org['logo'] = [
            '@type'      => 'ImageObject',
            '@id'        => $siteUrl . '#/schema/logo/image/',
            'url'        => $logoUrl,
            'contentUrl' => $logoUrl,
            'caption'    => $siteName,
        ];
        $org['image'] = ['@id' => $siteUrl . '#/schema/logo/image/'];
    }
    $graph[] = $org;

    // --- Additional Schema Blocks from Extra JSON ---
    if (!empty($values['gpai_wpseo_schema_extra_json'])) {
        $extra = json_decode($values['gpai_wpseo_schema_extra_json'], true);
        if (is_array($extra)) {
            array_walk_recursive($extra, function (&$value) {
                if (is_string($value)) {
                    if (function_exists('GPAI_replace_custom_vars')) {
                        $value = GPAI_replace_custom_vars($value);
                    }
                    $value = wp_strip_all_tags($value);
                }
            });
            foreach ($extra as $block) {
                if (isset($block['@type'])) {
                    $graph[] = $block;
                }
            }
        }
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@graph'   => $graph,
    ];

    $schema = apply_filters('gpai_seo_schema', $schema, $post_id);

    echo '<script type="application/ld+json" class="gpai-seo-schema">' . "\n";
    echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    echo "\n" . '</script>' . "\n";
}

function GPAI_SEO_get_post_id()
{
    if (is_front_page() && 'page' === get_option('show_on_front')) {
        return get_option('page_on_front');
    }
    return get_queried_object_id();
}

function GPAI_SEO_override_yoast_title($title)
{
    $post_id = GPAI_SEO_get_post_id();
    if (!$post_id) return $title;
    if (!GPAI_SEO_is_active($post_id)) return $title;
    $gpai = get_post_meta($post_id, 'gpai_wpseo_title', true);
    return $gpai ? GPAI_SEO_clean_text($gpai) : $title;
}
add_filter('wpseo_title', 'GPAI_SEO_override_yoast_title', 20);

function GPAI_SEO_override_yoast_metadesc($desc)
{
    $post_id = GPAI_SEO_get_post_id();
    if (!$post_id) return $desc;
    if (!GPAI_SEO_is_active($post_id)) return $desc;
    $gpai = get_post_meta($post_id, 'gpai_wpseo_metadesc', true);
    return $gpai ? GPAI_SEO_clean_text($gpai) : $desc;
}
add_filter('wpseo_metadesc', 'GPAI_SEO_override_yoast_metadesc', 20);

function GPAI_SEO_override_yoast_canonical($canonical)
{
    $post_id = GPAI_SEO_get_post_id();
    if (!$post_id) return $canonical;
    if (!GPAI_SEO_is_active($post_id)) return $canonical;
    $gpai = get_post_meta($post_id, 'gpai_wpseo_canonical', true);
    return $gpai ?: $canonical;
}
add_filter('wpseo_canonical', 'GPAI_SEO_override_yoast_canonical', 20);

function GPAI_SEO_override_yoast_og_title($title)
{
    $post_id = GPAI_SEO_get_post_id();
    if (!$post_id) return $title;
    if (!GPAI_SEO_is_active($post_id)) return $title;
    $gpai = get_post_meta($post_id, 'gpai_wpseo_opengraph-title', true);
    return $gpai ? GPAI_SEO_clean_text($gpai) : $title;
}
add_filter('wpseo_opengraph_title', 'GPAI_SEO_override_yoast_og_title', 20);

function GPAI_SEO_override_yoast_og_desc($desc)
{
    $post_id = GPAI_SEO_get_post_id();
    if (!$post_id) return $desc;
    if (!GPAI_SEO_is_active($post_id)) return $desc;
    $gpai = get_post_meta($post_id, 'gpai_wpseo_opengraph-description', true);
    return $gpai ? GPAI_SEO_clean_text($gpai) : $desc;
}
add_filter('wpseo_opengraph_desc', 'GPAI_SEO_override_yoast_og_desc', 20);

function GPAI_SEO_override_yoast_og_image($image)
{
    $post_id = GPAI_SEO_get_post_id();
    if (!$post_id) return $image;
    if (!GPAI_SEO_is_active($post_id)) return $image;
    $gpai = get_post_meta($post_id, 'gpai_wpseo_opengraph-image', true);
    return $gpai ?: $image;
}
add_filter('wpseo_opengraph_image', 'GPAI_SEO_override_yoast_og_image', 20);

function GPAI_SEO_override_yoast_og_url($url)
{
    $post_id = GPAI_SEO_get_post_id();
    if (!$post_id) return $url;
    if (!GPAI_SEO_is_active($post_id)) return $url;
    $gpai = get_post_meta($post_id, 'gpai_wpseo_opengraph-url', true);
    return $gpai ?: $url;
}
add_filter('wpseo_opengraph_url', 'GPAI_SEO_override_yoast_og_url', 20);

function GPAI_SEO_override_yoast_twitter_title($title)
{
    $post_id = GPAI_SEO_get_post_id();
    if (!$post_id) return $title;
    if (!GPAI_SEO_is_active($post_id)) return $title;
    $gpai = get_post_meta($post_id, 'gpai_wpseo_twitter-title', true);
    return $gpai ? GPAI_SEO_clean_text($gpai) : $title;
}
add_filter('wpseo_twitter_title', 'GPAI_SEO_override_yoast_twitter_title', 20);

function GPAI_SEO_override_yoast_twitter_desc($desc)
{
    $post_id = GPAI_SEO_get_post_id();
    if (!$post_id) return $desc;
    if (!GPAI_SEO_is_active($post_id)) return $desc;
    $gpai = get_post_meta($post_id, 'gpai_wpseo_twitter-description', true);
    return $gpai ? GPAI_SEO_clean_text($gpai) : $desc;
}
add_filter('wpseo_twitter_description', 'GPAI_SEO_override_yoast_twitter_desc', 20);

function GPAI_SEO_override_yoast_twitter_image($image)
{
    $post_id = GPAI_SEO_get_post_id();
    if (!$post_id) return $image;
    if (!GPAI_SEO_is_active($post_id)) return $image;
    $gpai = get_post_meta($post_id, 'gpai_wpseo_twitter-image', true);
    return $gpai ?: $image;
}
add_filter('wpseo_twitter_image', 'GPAI_SEO_override_yoast_twitter_image', 20);

function GPAI_SEO_override_yoast_robots($robots)
{
    $post_id = GPAI_SEO_get_post_id();
    if (!$post_id) return $robots;
    if (!GPAI_SEO_is_active($post_id)) return $robots;

    if (
        metadata_exists('post', $post_id, 'gpai_wpseo_meta-robots-noindex') ||
        metadata_exists('post', $post_id, 'gpai_wpseo_meta-robots-nofollow')
    ) {
        return '';
    }

    return $robots;
}
add_filter('wpseo_robots', 'GPAI_SEO_override_yoast_robots', 20);

function GPAI_SEO_override_yoast_robots_array($robots)
{
    $post_id = GPAI_SEO_get_post_id();
    if (!$post_id) return $robots;
    if (!GPAI_SEO_is_active($post_id)) return $robots;

    if (
        metadata_exists('post', $post_id, 'gpai_wpseo_meta-robots-noindex') ||
        metadata_exists('post', $post_id, 'gpai_wpseo_meta-robots-nofollow')
    ) {
        return [];
    }

    return $robots;
}
add_filter('wpseo_robots_array', 'GPAI_SEO_override_yoast_robots_array', 20);

function GPAI_SEO_override_document_title($title_parts)
{
    if (!is_singular() && !is_front_page()) return $title_parts;
    $post_id = GPAI_SEO_get_post_id();
    if (!$post_id) return $title_parts;
    if (!GPAI_SEO_is_active($post_id)) return $title_parts;
    $gpai = get_post_meta($post_id, 'gpai_wpseo_title', true);
    if ($gpai) {
        $title_parts['title'] = GPAI_SEO_clean_text($gpai);
        unset($title_parts['tagline']);
        unset($title_parts['site']);
    }
    return $title_parts;
}
add_filter('document_title_parts', 'GPAI_SEO_override_document_title', 20);

function GPAI_SEO_clean_yoast_schema($graph)
{
    if (!is_array($graph)) return $graph;
    if (!GPAI_SEO_is_active(get_the_ID())) return $graph;
    foreach ($graph as $key => $value) {
        if ($key === 'description_schema_fallback') {
            unset($graph[$key]);
        } elseif (is_array($value)) {
            $graph[$key] = GPAI_SEO_clean_yoast_schema($value);
        }
    }
    return $graph;
}
add_filter('wpseo_schema_graph', 'GPAI_SEO_clean_yoast_schema', 100);

function GPAI_SEO_handle_redirect()
{
    if (!is_singular() && !is_front_page()) return;
    $post_id = GPAI_SEO_get_post_id();
    if (!$post_id) return;
    if (!GPAI_SEO_is_active($post_id)) return;
    $redirect = get_post_meta($post_id, 'gpai_wpseo_redirect', true);
    if ($redirect) {
        wp_redirect(esc_url($redirect), 301);
        exit;
    }
}
add_action('template_redirect', 'GPAI_SEO_handle_redirect', 1);

function GPAI_SEO_remove_other_jsonld()
{
    if (!is_singular() && !is_front_page()) return;
    $post_id = get_queried_object_id();
    if (is_front_page() && 'page' === get_option('show_on_front')) {
        $post_id = get_option('page_on_front');
    }
    if (!$post_id) return;
    if (!GPAI_SEO_is_active($post_id)) return;

    $remove = get_post_meta($post_id, 'gpai_wpseo_remove_other_jsonld', true);
    if ($remove !== '1') return;

    ob_start('GPAI_SEO_remove_other_jsonld_callback');
}
add_action('template_redirect', 'GPAI_SEO_remove_other_jsonld', 0);

function GPAI_SEO_remove_default_robots($robots)
{
    if (!is_singular() && !is_front_page()) return $robots;
    $post_id = GPAI_SEO_get_post_id();
    if (!$post_id) return $robots;
    if (!GPAI_SEO_is_active($post_id)) return $robots;

    return [];
}
add_filter('wp_robots', 'GPAI_SEO_remove_default_robots', 15);

function GPAI_SEO_remove_other_jsonld_callback($buffer)
{
    return preg_replace_callback(
        '/<script\b[^>]*type="application\/ld\+json"[^>]*>.*?<\/script>/is',
        function ($matches) {
            if (strpos($matches[0], 'gpai-seo-schema') !== false) {
                return $matches[0];
            }
            return '';
        },
        $buffer
    );
}

function GPAI_SEO_remove_other_canonical($buffer)
{
    return preg_replace_callback(
        '/<link\b[^>]*\brel=["\']canonical["\'][^>]*>/is',
        function ($matches) {
            if (strpos($matches[0], 'gpai-seo-meta-tag') !== false) {
                return $matches[0];
            }
            return '';
        },
        $buffer
    );
}
