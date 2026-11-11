<?php

function GPAI_Imagenes_Post($post_id)
{
    ob_start();
?>
    <div id="gpai-imagenes-wrap">
        <div id="gpai-imagenes-loading" style="padding:2rem;text-align:center;">
            <span class="spinner is-active" style="float:none;"></span>
            Cargando imágenes...
        </div>
        <div id="gpai-imagenes-error" style="display:none;padding:1rem;color:#d63638;"></div>
        <div id="gpai-imagenes-content" style="display:none;"></div>
    </div>
    <div class="content-btn" id="gpai-imagenes-actions" style="display:none;margin-top:1rem;">
        <button type="button" class="button button-primary" id="gpai-imagenes-save-btn">
            Guardar Cambios
        </button>
        <span id="gpai-imagenes-save-status" style="margin-left:8px;font-style:italic;"></span>
    </div>

    <style>
        #gpai-imagenes-list {
            width: 100%;
            border-collapse: collapse;
        }
        #gpai-imagenes-list th {
            text-align: left;
            padding: 8px 10px;
            background: #f0f0f1;
            border-bottom: 1px solid #c3c4c7;
            font-weight: 600;
        }
        #gpai-imagenes-list td {
            padding: 12px 10px;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: top;
        }
        .gpai-img-preview-cell {
            width: 100px;
        }
        .gpai-img-preview {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #ddd;
            display: block;
        }
        .gpai-img-info {
            font-size: 11px;
            color: #666;
            margin-top: 4px;
            word-break: break-all;
        }
        .gpai-img-fields-cell label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: #444;
            margin: 6px 0 2px;
        }
        .gpai-img-fields-cell label:first-child {
            margin-top: 0;
        }
        .gpai-img-fields-cell input,
        .gpai-img-fields-cell textarea {
            width: 100%;
            box-sizing: border-box;
        }
        .gpai-img-fields-cell textarea {
            min-height: 48px;
            resize: vertical;
        }
        .gpai-img-actions-cell {
            width: 60px;
            text-align: center;
            vertical-align: middle;
        }
        .gpai-img-download-btn {
            text-decoration: none;
            font-size: 20px;
            color: #2271b1;
            cursor: pointer;
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            transition: background 0.2s;
        }
        .gpai-img-download-btn:hover {
            background: #f0f0f1;
        }
        .gpai-img-download-btn .dashicons {
            font-size: 24px;
            width: 24px;
            height: 24px;
        }
    </style>

    <script>
        (function() {
            const postId = <?= json_encode($post_id) ?>;
            const wrap = document.getElementById('gpai-imagenes-wrap');
            const loading = document.getElementById('gpai-imagenes-loading');
            const errorEl = document.getElementById('gpai-imagenes-error');
            const contentEl = document.getElementById('gpai-imagenes-content');
            const actionsEl = document.getElementById('gpai-imagenes-actions');
            const saveBtn = document.getElementById('gpai-imagenes-save-btn');
            const saveStatus = document.getElementById('gpai-imagenes-save-status');

            if (!postId) return;

            function loadImages() {
                loading.style.display = 'block';
                errorEl.style.display = 'none';
                contentEl.style.display = 'none';
                actionsEl.style.display = 'none';

                const fd = new FormData();
                fd.append('action', 'gpai_imagenes_get');
                fd.append('post_id', postId);

                fetch(ajaxurl, { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(res => {
                        loading.style.display = 'none';
                        if (!res.success) {
                            errorEl.textContent = res.data?.message || 'Error al cargar imágenes.';
                            errorEl.style.display = 'block';
                            return;
                        }
                        renderImages(res.data);
                    })
                    .catch(() => {
                        loading.style.display = 'none';
                        errorEl.textContent = 'Error de conexión.';
                        errorEl.style.display = 'block';
                    });
            }

            function escAttr(str) {
                return ('' + (str || '')).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            }

            function escHtml(str) {
                const div = document.createElement('div');
                div.textContent = str;
                return div.innerHTML;
            }

            function renderImages(images) {
                if (!images || images.length === 0) {
                    contentEl.innerHTML = '<p style="padding:1rem;">No se encontraron imágenes en este post.</p>';
                    contentEl.style.display = 'block';
                    return;
                }

                let html = '<table id="gpai-imagenes-list">';
                html += '<thead><tr>' +
                    '<th>Vista Previa</th>' +
                    '<th>Texto Alternativo</th>' +
                    '<th>Título</th>' +
                    '<th>Leyenda</th>' +
                    '<th>Descripción</th>' +
                    '<th></th>' +
                    '</tr></thead><tbody>';

                images.forEach(img => {
                    var infoParts = [img.filename];
                    if (img.width && img.height) infoParts.push(img.width + 'x' + img.height);
                    if (img.filesize) infoParts.push(img.filesize);

                    html += '<tr data-id="' + img.id + '">';
                    html += '<td class="gpai-img-preview-cell">';
                    html += '<img class="gpai-img-preview" src="' + escAttr(img.thumbnail || img.medium) + '" alt="">';
                    html += '<div class="gpai-img-info">#' + img.id + '<br>' + infoParts.join(' &middot; ') + '</div>';
                    html += '</td>';
                    html += '<td class="gpai-img-fields-cell">';
                    html += '<input type="text" class="large-text gpai-img-alt" value="' + escAttr(img.alt || '') + '" placeholder="Texto alternativo">';
                    html += '</td>';
                    html += '<td class="gpai-img-fields-cell">';
                    html += '<input type="text" class="large-text gpai-img-title" value="' + escAttr(img.title || '') + '" placeholder="Título">';
                    html += '</td>';
                    html += '<td class="gpai-img-fields-cell">';
                    html += '<textarea class="large-text code gpai-img-caption" rows="2" placeholder="Leyenda">' + escHtml(img.caption || '') + '</textarea>';
                    html += '</td>';
                    html += '<td class="gpai-img-fields-cell">';
                    html += '<textarea class="large-text code gpai-img-desc" rows="2" placeholder="Descripción">' + escHtml(img.description || '') + '</textarea>';
                    html += '</td>';
                    html += '<td class="gpai-img-actions-cell">';
                    if (img.url) {
                        html += '<a href="' + escAttr(img.url) + '" download class="gpai-img-download-btn dashicons dashicons-download" title="Descargar"></a>';
                    }
                    html += '</td>';
                    html += '</tr>';
                });

                html += '</tbody></table>';
                contentEl.innerHTML = html;
                contentEl.style.display = 'block';
                actionsEl.style.display = 'flex';
            }

            saveBtn.addEventListener('click', function() {
                const rows = contentEl.querySelectorAll('#gpai-imagenes-list tbody tr');
                const images = [];
                rows.forEach(tr => {
                    const id = parseInt(tr.dataset.id);
                    if (!id) return;
                    images.push({
                        id: id,
                        alt: tr.querySelector('.gpai-img-alt')?.value || '',
                        title: tr.querySelector('.gpai-img-title')?.value || '',
                        caption: tr.querySelector('.gpai-img-caption')?.value || '',
                        description: tr.querySelector('.gpai-img-desc')?.value || '',
                    });
                });

                if (images.length === 0) return;

                saveBtn.disabled = true;
                saveStatus.textContent = 'Guardando...';

                const fd = new FormData();
                fd.append('action', 'gpai_imagenes_save');
                fd.append('images', JSON.stringify(images));
                fd.append('post_id', postId);

                fetch(ajaxurl, { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(res => {
                        saveBtn.disabled = false;
                        if (res.success) {
                            saveStatus.textContent = res.data.message + ' ✓';
                            setTimeout(() => { saveStatus.textContent = ''; }, 3000);
                        } else {
                            saveStatus.textContent = '✗ ' + (res.data?.message || 'Error');
                        }
                    })
                    .catch(() => {
                        saveBtn.disabled = false;
                        saveStatus.textContent = '✗ Error de conexión';
                    });
            });

            if (postId) loadImages();
        })();
    </script>
<?php
    return ob_get_clean();
}
