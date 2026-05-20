<?php

$CONFIG ??= [];
$key_post_id = 'post_id_by_html';
$post_id = isset($_POST[$key_post_id]) ? intval($_POST[$key_post_id]) : ($CONFIG[$key_post_id] ?? 0);
$staticInfo = null;
$optimizedFile = null;

if (isset($post_id)) {
    $CONFIG[$key_post_id] = $post_id;
    if (isset($GPAI_USE_DATA_CONFIG)) {
        $GPAI_USE_DATA_CONFIG->set($CONFIG);
    }
    $htmlPath = get_post_meta($post_id, 'STPA_PAGE_STATIC_HTML_FILE', true);
    if ($htmlPath && file_exists($htmlPath)) {
        $uploadDir = wp_upload_dir();
        $htmlUrl = str_replace($uploadDir['basedir'], $uploadDir['baseurl'], $htmlPath);
        $staticInfo = [
            'url' => $htmlUrl,
            'fullPath' => $htmlPath,
            'size' => filesize($htmlPath),
        ];
        $optimizedPath = get_post_meta($post_id, 'STPA_PAGE_STATIC_HTML_FILE_OPTIMIZE', true);
        if ($optimizedPath && file_exists($optimizedPath)) {
            $optimizedFile = [
                'url' => str_replace($uploadDir['basedir'], $uploadDir['baseurl'], $optimizedPath),
                'fullPath' => $optimizedPath,
                'size' => filesize($optimizedPath),
            ];
        }
    }
}

?>
<form method="post">
    <?= GPAI_Respond($respond_content ?? null) ?>
    <input type="hidden" name="save" value="html">
    <table class="form-table">
        <tr>
            <th scope="row">
                <?= GPAI_Tooltip("Página Estática", "Selecciona un post para verificar si tiene página estática generada por Static Page.") ?>
            </th>
            <td>
                <div class="content-btn">
                    <?php
                    ob_start();
                    wp_dropdown_pages([
                        'name'              => $key_post_id,
                        'id'                => $key_post_id,
                        'show_option_none'  => '-- Seleccionar --',
                        'option_none_value' => '',
                        'selected'          => $post_id,
                    ]);
                    $data = ob_get_clean();

                    $data = preg_replace_callback(
                        '/<option([^>]*)value="([^"]*)"([^>]*)>(.*?)<\/option>/si',
                        function ($m) {
                            $before = $m[1];
                            $value  = $m[2];
                            $after  = $m[3];
                            $label  = trim(strip_tags($m[4]));
                            if ($value === '') return $m[0];
                            return sprintf(
                                '<option%svalue="%s"%s>#%s - %s</option>',
                                $before,
                                esc_attr($value),
                                $after,
                                esc_html($value),
                                esc_html($label)
                            );
                        },
                        $data
                    );
                    echo $data;
                    ?>
                    <button type="submit" name="is_load_post" value="1" class="button button-primary">
                        Cargar Post
                    </button>
                </div>
            </td>
        </tr>
    </table>

    <?php if ($post_id) : ?>
        <hr>
        <h3><?= get_the_title($post_id) ?> (#<?= $post_id ?>)</h3>
        <?php if ($staticInfo) : ?>
            <table class="form-table">
                <tr>
                    <th scope="row">Archivo Estático</th>
                    <td>
                        <code><?= esc_html($staticInfo['url']) ?></code>
                        <br><small>Tamaño: <?= size_format($staticInfo['size']) ?></small>
                    </td>
                </tr>
                <?php if ($optimizedFile) : ?>
                    <tr>
                        <th scope="row">Archivo Optimizado</th>
                        <td>
                            <code><?= esc_html($optimizedFile['url']) ?></code>
                            <br><small>Tamaño: <?= size_format($optimizedFile['size']) ?></small>
                            <br><span class="description">Ya existe una versión optimizada. Puedes generar una nueva.</span>
                        </td>
                    </tr>
                <?php endif; ?>
            </table>

            <div class="content-btn" style="margin-top:12px;">
                <a href="<?= get_permalink($post_id) ?>" target="_blank" class="button">Ver Post</a>
                <a href="<?= get_edit_post_link($post_id) ?>" target="_blank" class="button">Editar Post</a>
                <a href="<?= esc_url($staticInfo['url']) ?>" target="_blank" class="button">Ver HTML Estático</a>
                <?php if ($optimizedFile) : ?>
                    <a href="<?= esc_url($optimizedFile['url']) ?>" target="_blank" class="button">Ver HTML Optimizado</a>
                    <?php
                    $isUsingOptimized = $staticInfo['fullPath'] === $optimizedFile['fullPath'];
                    ?>
                    <button type="button" class="button gpai-html-swap-btn"
                        data-post-id="<?= esc_attr($post_id) ?>"
                        data-nonce="<?= esc_attr(wp_create_nonce('gpai_html_swap_' . $post_id)) ?>"
                        data-confirm="<?= $isUsingOptimized ? '¿Cambiar al HTML original?' : '¿Cambiar al HTML optimizado?' ?>">
                        <?= $isUsingOptimized ? 'Usar HTML Normal' : 'Usar HTML Optimizado' ?>
                    </button>
                    <span class="gpai-html-swap-status" style="margin-left:8px;font-style:italic;"></span>
                <?php endif; ?>
                <button type="button" class="button button-primary gpai-html-optimize-btn"
                    data-post-id="<?= esc_attr($post_id) ?>"
                    data-nonce="<?= esc_attr(wp_create_nonce('gpai_html_generate_' . $post_id)) ?>">
                    Mejorar HTML con IA
                </button>
                <span class="gpai-html-optimize-status" style="margin-left:8px;font-style:italic;"></span>
            </div>
        <?php else : ?>
            <div class="notice notice-warning inline" style="margin:12px 0;">
                <p>Esta página no tiene página estática generada por <strong>Static Page</strong>.</p>
                <p>Ve a editar el post y activa la opción de "Página Estática" en el meta-box de Static Page, luego genera el HTML estático.</p>
            </div>
            <div class="content-btn">
                <a href="<?= get_edit_post_link($post_id) ?>" target="_blank" class="button button-primary">Editar Post</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</form>