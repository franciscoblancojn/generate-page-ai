<?php

use franciscoblancojn\wordpress_utils\FWUSystemLog;

class GPAI_USE_DATA_DUPLICADOS extends GPAI_USE_DATA_BASE
{
    protected $KEY = GPAI_CONTENT;

    public function deletePost($post_id)
    {
        $DUPLICADOS = $this->get();
        if (isset($DUPLICADOS[$post_id])) {
            unset($DUPLICADOS[$post_id]);
            $this->set($DUPLICADOS);
        }
    }
    public function deletePrompt($post_id, $prompt)
    {
        $DUPLICADOS = $this->get();
        if (isset($DUPLICADOS[$post_id]['variations'][$prompt])) {
            unset($DUPLICADOS[$post_id]['variations'][$prompt]);
            $this->set($DUPLICADOS);
            if (count($DUPLICADOS[$post_id]['variations']) == 0) {
                $this->deletePost($post_id);
            }
        }
    }
    public function deleteVariation($post_id, $prompt, $v)
    {
        $DUPLICADOS = $this->get();
        if (isset($DUPLICADOS[$post_id]['variations'][$prompt][$v])) {
            unset($DUPLICADOS[$post_id]['variations'][$prompt][$v]);
            $DUPLICADOS[$post_id]['variations'][$prompt] = array_values(
                $DUPLICADOS[$post_id]['variations'][$prompt]
            );
            $this->set($DUPLICADOS);
            if (count($DUPLICADOS[$post_id]['variations'][$prompt]) == 0) {
                $this->deletePrompt($post_id, $prompt);
            }
        }
    }
    private function generateDuplicado($post_id, $title, $custom_fields = [],$yoastFields = [])
    {
        $post = get_post($post_id);

        if (!$post) {
            throw new \RuntimeException('Post no encontrado.');
        }

        // 1. Crear nuevo post basado en el original
        $new_post_id = wp_insert_post([
            'post_title'   => $title,
            'post_content' => $post->post_content,
            'post_status'  => 'publish', // o publish si quieres
            'post_type'    => $post->post_type,
            'post_author'  => get_current_user_id(),
        ]);

        if (!$new_post_id) {
            throw new \RuntimeException('Error al crear Post Duplicado.');
        }

        // 2. Copiar TODOS los metadatos del original
        $metas = get_post_meta($post_id);

        foreach ($metas as $key => $values) {
            // Evitar basura interna opcional
            if (in_array($key, ['_edit_lock', '_edit_last'])) {
                continue;
            }

            foreach ($values as $value) {
                update_post_meta(
                    $new_post_id,
                    $key,
                    maybe_unserialize($value)
                );
            }
        }

        // 3. Sobrescribir SOLO los custom fields que envías
        foreach ($custom_fields as $key => $value) {
            update_post_meta($new_post_id, $key, $value);
        }
        // 4. Sobrescribir SOLO los yoast fields que envías
        foreach ($yoastFields as $key => $value) {
            update_post_meta($new_post_id, $key, $value);
        }
        update_post_meta($new_post_id, GPAI_KEY . "_PARENT", $post_id);

        //PENDING: generar img con IA
        $thumbnail_id = get_post_thumbnail_id($post_id);
        if ($thumbnail_id) {
            set_post_thumbnail($new_post_id, $thumbnail_id);
        }
        if (class_exists('\Elementor\Plugin')) {
            \Elementor\Plugin::instance()->files_manager->clear_cache();
        }
        return $new_post_id;
    }
    public function generateVariation($post_id, $prompt, $v)
    {
        try {
            $DUPLICADOS = $this->get();
            if (isset($DUPLICADOS[$post_id]['variations'][$prompt][$v])) {
                $DATA = $DUPLICADOS[$post_id]['variations'][$prompt][$v];
                $new_post_id = $this->generateDuplicado(
                    $post_id,
                    $DATA['title'],
                    $DATA['customFields'],
                    $DATA['yoastFields'],
                );
                $this->deleteVariation($post_id, $prompt, $v);
                return [
                    "status" => "ok",
                    "message" => "Duplicacion Exitosa.",
                    'data' => [
                        "post_id"       => $post_id,
                        "new_post_id"   => $new_post_id,
                        "title"         => $DATA['title'],
                        'url'           => get_permalink($new_post_id),
                    ],
                ];
            }
            throw new \RuntimeException('Variacion no existe.');
        } catch (\Throwable $th) {
            $error = [
                "status" => "error",
                "message" => $th->getMessage(),
                'data' => [
                    "post_id"   => $post_id,
                    'line' => $th->getLine(),
                    'file' => $th->getFile(),
                ]
            ];
            FWUSystemLog::add(GPAI_KEY, [
                'type' => "Duplicados error",
                'data' => $error
            ]);
            return $error;
        }
    }
    public function generateVariationWithData($post_id, $prompt, $DATA)
    {
        try {
            $new_post_id = $this->generateDuplicado(
                $post_id,
                $DATA['title'],
                $DATA['customFields'],
                $DATA['yoastFields'],
            );
            return [
                "status" => "ok",
                "message" => "Duplicacion Exitosa.",
                'data' => [
                    "post_id"       => $post_id,
                    "new_post_id"   => $new_post_id,
                    "title"         => $DATA['title'],
                    'url'           => get_permalink($new_post_id),
                ],
            ];
        } catch (\Throwable $th) {
            $error = [
                "status" => "error",
                "message" => $th->getMessage(),
                'data' => [
                    "post_id"   => $post_id,
                    'line' => $th->getLine(),
                    'file' => $th->getFile(),
                ]
            ];
            FWUSystemLog::add(GPAI_KEY, [
                'type' => "Duplicados error",
                'data' => $error
            ]);
            return $error;
        }
    }
    public function generateAllVariations()
    {
        try {
            $DUPLICADOS = $this->get();
            $respond = [];
            foreach ($DUPLICADOS as $post_id => $duplication) {
                $variations = $duplication['variations'];
                foreach ($variations as $prompt => $variation) {
                    foreach ($variation as $v => $DATA) {
                        $respond[] = $this->generateVariationWithData($post_id, $prompt, $DATA);
                        // $this->deleteVariation($post_id, $prompt, $v);
                    }
                }
            }
            $this->set([]);
            return [
                "status" => "ok",
                "message" => "Duplicaciones Exitosas.",
                'data' => $respond,
            ];
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
                'type' => "Duplicados error",
                'data' => $error
            ]);
            return $error;
        }
    }
}
