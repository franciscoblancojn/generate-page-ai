<?php

function GPAI_SEO_output()
{
    if (!is_singular()) return;
    $post_id = get_queried_object_id();
    if (!$post_id) return;

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
    $title = $values['gpai_wpseo_title'] ?: get_the_title($post_id);
    $desc = $values['gpai_wpseo_metadesc'] ?: '';
    $canonical = $values['gpai_wpseo_canonical'] ?: get_permalink($post_id);
    $ogTitle = $values['gpai_wpseo_opengraph-title'] ?: $title;
    $ogDesc = $values['gpai_wpseo_opengraph-description'] ?: $desc;
    $ogImage = $values['gpai_wpseo_opengraph-image'] ?: '';
    $ogUrl = $values['gpai_wpseo_opengraph-url'] ?: $canonical;
    $twTitle = $values['gpai_wpseo_twitter-title'] ?: $ogTitle;
    $twDesc = $values['gpai_wpseo_twitter-description'] ?: $ogDesc;
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

    $noindex = $values['gpai_wpseo_meta-robots-noindex'] ?? '';
    $nofollow = $values['gpai_wpseo_meta-robots-nofollow'] ?? '';
    if ($noindex || $nofollow) {
        $content = ($noindex === '1' ? 'noindex' : 'index') . ',' . ($nofollow === '1' ? 'nofollow' : 'follow');
        echo '<meta name="robots" content="' . esc_attr($content) . '" class="gpai-seo-meta-tag">' . "\n";
    }

    GPAI_SEO_output_jsonld($post_id, $post, $values, $title, $desc, $canonical, $ogImage);

    echo "<!-- /GPAI SEO Meta Tags -->\n\n";
}
add_action('wp_head', 'GPAI_SEO_output', 20);

function GPAI_SEO_output_jsonld($post_id, $post, $values, $title, $desc, $canonical, $ogImage)
{
    $schemaType = 'WebPage';
    if (!empty($values['gpai_wpseo_schema_page_type'])) {
        $schemaType = $values['gpai_wpseo_schema_page_type'];
    }
    if (!empty($values['gpai_wpseo_schema_article_type'])) {
        $schemaType = $values['gpai_wpseo_schema_article_type'];
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => $schemaType,
        'name' => $title,
        'url' => $canonical,
    ];

    if ($desc) {
        $schema['description'] = $desc;
    }

    if (!empty($values['gpai_wpseo_focuskw'])) {
        $schema['keywords'] = $values['gpai_wpseo_focuskw'];
    }

    if ($ogImage) {
        $schema['image'] = $ogImage;
    }

    if ($post) {
        $schema['datePublished'] = get_the_date('c', $post_id);
        $schema['dateModified'] = get_the_modified_date('c', $post_id);
    }

    $schema = apply_filters('gpai_seo_schema', $schema, $post_id);

    echo '<script type="application/ld+json" class="gpai-seo-schema">' . "\n";
    echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    echo "\n" . '</script>' . "\n";
}

function GPAI_SEO_override_yoast_title($title)
{
    $post_id = get_queried_object_id();
    if (!$post_id) return $title;
    $gpai = get_post_meta($post_id, 'gpai_wpseo_title', true);
    return $gpai ?: $title;
}
add_filter('wpseo_title', 'GPAI_SEO_override_yoast_title', 20);

function GPAI_SEO_override_yoast_metadesc($desc)
{
    $post_id = get_queried_object_id();
    if (!$post_id) return $desc;
    $gpai = get_post_meta($post_id, 'gpai_wpseo_metadesc', true);
    return $gpai ?: $desc;
}
add_filter('wpseo_metadesc', 'GPAI_SEO_override_yoast_metadesc', 20);

function GPAI_SEO_override_yoast_canonical($canonical)
{
    $post_id = get_queried_object_id();
    if (!$post_id) return $canonical;
    $gpai = get_post_meta($post_id, 'gpai_wpseo_canonical', true);
    return $gpai ?: $canonical;
}
add_filter('wpseo_canonical', 'GPAI_SEO_override_yoast_canonical', 20);

function GPAI_SEO_override_yoast_og_title($title)
{
    $post_id = get_queried_object_id();
    if (!$post_id) return $title;
    $gpai = get_post_meta($post_id, 'gpai_wpseo_opengraph-title', true);
    return $gpai ?: $title;
}
add_filter('wpseo_opengraph_title', 'GPAI_SEO_override_yoast_og_title', 20);

function GPAI_SEO_override_yoast_og_desc($desc)
{
    $post_id = get_queried_object_id();
    if (!$post_id) return $desc;
    $gpai = get_post_meta($post_id, 'gpai_wpseo_opengraph-description', true);
    return $gpai ?: $desc;
}
add_filter('wpseo_opengraph_desc', 'GPAI_SEO_override_yoast_og_desc', 20);

function GPAI_SEO_override_yoast_og_image($image)
{
    $post_id = get_queried_object_id();
    if (!$post_id) return $image;
    $gpai = get_post_meta($post_id, 'gpai_wpseo_opengraph-image', true);
    return $gpai ?: $image;
}
add_filter('wpseo_opengraph_image', 'GPAI_SEO_override_yoast_og_image', 20);

function GPAI_SEO_override_yoast_og_url($url)
{
    $post_id = get_queried_object_id();
    if (!$post_id) return $url;
    $gpai = get_post_meta($post_id, 'gpai_wpseo_opengraph-url', true);
    return $gpai ?: $url;
}
add_filter('wpseo_opengraph_url', 'GPAI_SEO_override_yoast_og_url', 20);

function GPAI_SEO_override_yoast_twitter_title($title)
{
    $post_id = get_queried_object_id();
    if (!$post_id) return $title;
    $gpai = get_post_meta($post_id, 'gpai_wpseo_twitter-title', true);
    return $gpai ?: $title;
}
add_filter('wpseo_twitter_title', 'GPAI_SEO_override_yoast_twitter_title', 20);

function GPAI_SEO_override_yoast_twitter_desc($desc)
{
    $post_id = get_queried_object_id();
    if (!$post_id) return $desc;
    $gpai = get_post_meta($post_id, 'gpai_wpseo_twitter-description', true);
    return $gpai ?: $desc;
}
add_filter('wpseo_twitter_description', 'GPAI_SEO_override_yoast_twitter_desc', 20);

function GPAI_SEO_override_yoast_twitter_image($image)
{
    $post_id = get_queried_object_id();
    if (!$post_id) return $image;
    $gpai = get_post_meta($post_id, 'gpai_wpseo_twitter-image', true);
    return $gpai ?: $image;
}
add_filter('wpseo_twitter_image', 'GPAI_SEO_override_yoast_twitter_image', 20);

function GPAI_SEO_override_yoast_robots($robots)
{
    $post_id = get_queried_object_id();
    if (!$post_id) return $robots;

    $noindex = get_post_meta($post_id, 'gpai_wpseo_meta-robots-noindex', true);
    $nofollow = get_post_meta($post_id, 'gpai_wpseo_meta-robots-nofollow', true);

    if (is_array($robots)) {
        if ($noindex === '1') $robots['index'] = 'noindex';
        if ($nofollow === '1') $robots['follow'] = 'nofollow';
    }

    return $robots;
}
add_filter('wpseo_robots', 'GPAI_SEO_override_yoast_robots', 20);

function GPAI_SEO_override_document_title($title_parts)
{
    if (!is_singular()) return $title_parts;
    $post_id = get_queried_object_id();
    if (!$post_id) return $title_parts;
    $gpai = get_post_meta($post_id, 'gpai_wpseo_title', true);
    if ($gpai) {
        $title_parts['title'] = $gpai;
    }
    return $title_parts;
}
add_filter('document_title_parts', 'GPAI_SEO_override_document_title', 20);

function GPAI_SEO_handle_redirect()
{
    if (!is_singular()) return;
    $post_id = get_queried_object_id();
    if (!$post_id) return;
    $redirect = get_post_meta($post_id, 'gpai_wpseo_redirect', true);
    if ($redirect) {
        wp_redirect(esc_url($redirect), 301);
        exit;
    }
}
add_action('template_redirect', 'GPAI_SEO_handle_redirect', 1);
