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
    private function generateDuplicado(
        $post_id,
        $title,
        $custom_fields = [],
        $yoastFields = []
    ) {

        $post = get_post($post_id);

        if (!$post) {
            throw new \RuntimeException(
                'Post no encontrado.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDAR PLUGIN
        |--------------------------------------------------------------------------
        */
        if (!function_exists('duplicate_post_create_duplicate')) {
            throw new \RuntimeException('Yoast Duplicate Post no está instalado.');
        }
        /*
        |--------------------------------------------------------------------------
        | DUPLICAR POST
        |--------------------------------------------------------------------------
        */


        $new_post_id = duplicate_post_create_duplicate(
            $post,
            'draft',
            null
        );

        if (!$new_post_id) {
            throw new \RuntimeException(
                'Error al duplicar el post.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | ACTUALIZAR TÍTULO
        |--------------------------------------------------------------------------
        */

        wp_update_post([
            'ID'         => $new_post_id,
            'post_title' => $title,
            'post_status' => 'draft',
        ]);

        /*
        |--------------------------------------------------------------------------
        | CUSTOM FIELDS
        |--------------------------------------------------------------------------
        */

        foreach ($custom_fields as $key => $value) {

            update_post_meta(
                $new_post_id,
                $key,
                $value
            );
        }

        /*
        |--------------------------------------------------------------------------
        | YOAST FIELDS
        |--------------------------------------------------------------------------
        */

        foreach ($yoastFields as $key => $value) {

            update_post_meta(
                $new_post_id,
                $key,
                $value
            );
        }

        /*
        |--------------------------------------------------------------------------
        | RELACIÓN PADRE
        |--------------------------------------------------------------------------
        */

        update_post_meta(
            $new_post_id,
            GPAI_KEY . '_PARENT',
            $post_id
        );

        /*
        |--------------------------------------------------------------------------
        | LIMPIAR CACHE ELEMENTOR
        |--------------------------------------------------------------------------
        */

        delete_post_meta(
            $new_post_id,
            '_elementor_css'
        );

        if (class_exists('\Elementor\Plugin')) {

            \Elementor\Plugin::instance()
                ->files_manager
                ->clear_cache();
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
