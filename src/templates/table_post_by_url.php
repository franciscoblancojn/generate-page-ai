<?php

function GPAI_Table_Post_By_Url($posts,$enabled_posts)
{
    ob_start();
?>
    <table class="wp-list-table widefat fixed striped" style="border:none;">
        <colgroup>
            <col style="width: 5%;">
            <col style="width: 5%;">
            <col style="width: 40%;">
            <col style="width: 45%;">
            <col style="width: 5%;">
        </colgroup>
        <thead>
            <tr>
                <th style="width:40px;"><input type="checkbox" class="gpai-toggle-type" style="margin:0;"></th>
                <th>ID</th>
                <th>Titulo</th>
                <th>URL</th>
                <th>Ver</th>
            </tr>
        </thead>
        <tbody>
            <?php

            foreach ($posts as $post) {
                $checked = in_array($post->ID, $enabled_posts) ? 'checked' : '';
                $permalink = get_permalink($post->ID);
                echo '<tr>
                            <td><input type="checkbox" name="enabled_posts[]" value="' . esc_attr($post->ID) . '" ' . $checked . '></td>
                            <td><code style="font-size:10px;">ID:' . $post->ID . '</code></td>
                            <td>' . esc_html($post->post_title) . '</td>
                            <td><code style="font-size:11px;word-break:break-all;">' . esc_url($permalink) . '</code></td>
                            <td><a target="_blank" href="' . esc_url($permalink) . '" class="button button-primary">Ver</a></td>
                        </tr>';
            }
            ?>
        </tbody>
    </table>
<?php
    return ob_get_clean();
}
