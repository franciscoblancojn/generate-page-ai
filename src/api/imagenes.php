<?php

class GPAI_IMAGENES
{
    public static function init()
    {
        add_action('wp_ajax_gpai_imagenes_get', [self::class, 'getImagesAjax']);
        add_action('wp_ajax_gpai_imagenes_save', [self::class, 'saveImagesAjax']);
    }

    public static function getImagesAjax()
    {
        if (!current_user_can('manage_options')) wp_die(-1);

        $post_id = intval($_POST['post_id'] ?? 0);
        $post = get_post($post_id);
        if (!$post) wp_send_json_error(['message' => 'Post no existe.']);

        $images = self::getPostImages($post_id);
        wp_send_json_success($images);
    }

    public static function saveImagesAjax()
    {
        if (!current_user_can('manage_options')) wp_die(-1);

        $images_data = isset($_POST['images']) ? json_decode(wp_unslash($_POST['images']), true) : [];

        if (empty($images_data)) {
            wp_send_json_error(['message' => 'No hay datos de imágenes.']);
        }

        $saved = 0;
        foreach ($images_data as $data) {
            $attachment_id = intval($data['id'] ?? 0);
            if (!$attachment_id) continue;

            $attachment = get_post($attachment_id);
            if (!$attachment || $attachment->post_type !== 'attachment') continue;

            $update = ['ID' => $attachment_id];
            if (isset($data['title'])) $update['post_title'] = sanitize_text_field($data['title']);
            if (isset($data['caption'])) $update['post_excerpt'] = sanitize_text_field($data['caption']);
            if (isset($data['description'])) $update['post_content'] = wp_kses_post($data['description']);

            if (count($update) > 1) {
                wp_update_post($update);
            }

            if (isset($data['alt'])) {
                update_post_meta($attachment_id, '_wp_attachment_image_alt', sanitize_text_field($data['alt']));
            }

            $saved++;
        }

        wp_send_json_success(['message' => "{$saved} imagen(es) actualizada(s) correctamente."]);
    }

    public static function getPostImages($post_id)
    {
        $image_ids = [];
        $post = get_post($post_id);

        $thumb_id = get_post_thumbnail_id($post_id);
        if ($thumb_id) $image_ids[] = $thumb_id;

        $attached = get_attached_media('image', $post_id);
        foreach ($attached as $attachment) {
            $image_ids[] = $attachment->ID;
        }

        if ($post && !empty($post->post_content)) {
            preg_match_all('/wp-image-(\d+)/', $post->post_content, $matches);
            foreach ($matches[1] as $id) {
                $image_ids[] = (int) $id;
            }
            preg_match_all('/wp:image\s*\{[^}]*"id"\s*:\s*(\d+)/', $post->post_content, $matches);
            foreach ($matches[1] as $id) {
                $image_ids[] = (int) $id;
            }
            preg_match_all('/<img[^>]+data-id=["\'](\d+)/i', $post->post_content, $matches);
            foreach ($matches[1] as $id) {
                $image_ids[] = (int) $id;
            }
        }

        $elementor_data = get_post_meta($post_id, '_elementor_data', true);
        if ($elementor_data) {
            $elementor_data = is_string($elementor_data) ? json_decode($elementor_data, true) : $elementor_data;
            if (is_array($elementor_data)) {
                array_walk_recursive($elementor_data, function ($value, $key) use (&$image_ids) {
                    if (in_array($key, ['id', 'image_id']) && is_numeric($value)) {
                        $image_ids[] = (int) $value;
                    }
                });
            }
        }

        $gallery_ids_str = get_post_meta($post_id, '_product_image_gallery', true);
        if (!empty($gallery_ids_str)) {
            foreach (explode(',', $gallery_ids_str) as $gid) {
                $image_ids[] = (int) $gid;
            }
        }

        $image_ids = array_unique(array_filter($image_ids));

        $images = [];
        foreach ($image_ids as $id) {
            $attachment = get_post($id);
            if (!$attachment || $attachment->post_type !== 'attachment') continue;
            if (strpos($attachment->post_mime_type, 'image/') !== 0) continue;

            $src = wp_get_attachment_image_url($id, 'medium');
            $full_src = wp_get_attachment_image_url($id, 'full');
            $thumbnail = wp_get_attachment_image_url($id, 'thumbnail');
            $metadata = wp_get_attachment_metadata($id);
            $filepath = get_attached_file($id);

            $images[] = [
                'id'          => $id,
                'title'       => $attachment->post_title,
                'caption'     => $attachment->post_excerpt,
                'description' => $attachment->post_content,
                'alt'         => get_post_meta($id, '_wp_attachment_image_alt', true),
                'url'         => $full_src,
                'thumbnail'   => $thumbnail ?: $src,
                'medium'      => $src,
                'filename'    => basename($filepath),
                'mime'        => $attachment->post_mime_type,
                'filesize'    => $metadata && $filepath && file_exists($filepath)
                    ? size_format(filesize($filepath))
                    : '',
                'width'       => $metadata['width'] ?? '',
                'height'      => $metadata['height'] ?? '',
            ];
        }

        return $images;
    }
}

add_action('admin_init', ['GPAI_IMAGENES', 'init']);
