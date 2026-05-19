<?php

$CONFIG ??= [];
$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : ($CONFIG['post_id'] ?? 0);
$staticInfo = null;
$optimizedFile = null;

if (isset($_POST['save']) && $_POST['save'] === 'html') {
    $post_id = intval($_POST['post_id'] ?? 0);
    if ($post_id) {
        $htmlPath = get_post_meta($post_id, 'STPA_PAGE_STATIC_HTML_FILE', true);
        if ($htmlPath) {
            $uploadDir = wp_upload_dir();
            $fullPath = $uploadDir['basedir'] . str_replace($uploadDir['baseurl'], '', $htmlPath);
            if (file_exists($fullPath)) {
                $staticInfo = [
                    'path' => $htmlPath,
                    'fullPath' => $fullPath,
                    'size' => filesize($fullPath),
                ];
                $optimizedCandidate = str_replace('.html', '-2.html', $fullPath);
                if (file_exists($optimizedCandidate)) {
                    $optimizedFile = [
                        'path' => str_replace('.html', '-2.html', $htmlPath),
                        'fullPath' => $optimizedCandidate,
                        'size' => filesize($optimizedCandidate),
                    ];
                }
            }
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
                        'name'              => 'post_id',
                        'id'                => 'post_id',
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
                        <code><?= esc_html($staticInfo['path']) ?></code>
                        <br><small>Tamaño: <?= size_format($staticInfo['size']) ?></small>
                    </td>
                </tr>
                <?php if ($optimizedFile) : ?>
                <tr>
                    <th scope="row">Archivo Optimizado</th>
                    <td>
                        <code><?= esc_html($optimizedFile['path']) ?></code>
                        <br><small>Tamaño: <?= size_format($optimizedFile['size']) ?></small>
                        <br><span class="description">Ya existe una versión optimizada. Puedes generar una nueva.</span>
                    </td>
                </tr>
                <?php endif; ?>
            </table>

            <div class="content-btn" style="margin-top:12px;">
                <a href="<?= get_permalink($post_id) ?>" target="_blank" class="button">Ver Post</a>
                <a href="<?= esc_url($staticInfo['path']) ?>" target="_blank" class="button">Ver HTML Estático</a>
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
