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
        foreach ($data as $key => $value) {
            if (!in_array($key, $allowed)) continue;
            if (is_array($value)) {
                $value = wp_json_encode($value);
            } else {
                $value = wp_kses_post($value);
            }
            if ($value !== '') {
                update_post_meta($post_id, $key, $value);
            } else {
                delete_post_meta($post_id, $key);
            }
        }
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
            ],
        ];
    }
}
