<?php

function GPAI_Analisis_Post($post_id)
{
    ob_start();
?>
    <div id="gpai-analisis-wrap">
        <style>
            .gpai-analisis-section {
                margin-bottom: 20px;
            }
            .gpai-analisis-section h4 {
                margin: 0 0 12px;
                padding: 0;
                font-size: 14px;
                font-weight: 600;
                color: #1d2327;
            }
            .gpai-analisis-section hr {
                margin: 16px 0;
                border: none;
                border-top: 1px solid #e0e0e0;
            }
            .gpai-analisis-score {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 4px 12px;
                border-radius: 12px;
                font-weight: 600;
                font-size: 13px;
                margin-bottom: 12px;
            }
            .gpai-analisis-score.good {
                background: #edfaef;
                color: #1e8a3e;
            }
            .gpai-analisis-score.warning {
                background: #fef8ee;
                color: #b8860b;
            }
            .gpai-analisis-score.bad {
                background: #fce8e8;
                color: #b32d2e;
            }
            .gpai-analisis-item {
                display: flex;
                align-items: flex-start;
                gap: 8px;
                padding: 8px 10px;
                border-bottom: 1px solid #f0f0f1;
                font-size: 13px;
                line-height: 1.4;
            }
            .gpai-analisis-item:last-child {
                border-bottom: none;
            }
            .gpai-analisis-icon {
                flex: 0 0 20px;
                font-size: 16px;
                line-height: 1.4;
            }
            .gpai-analisis-icon.ok {
                color: #1e8a3e;
            }
            .gpai-analisis-icon.warning {
                color: #b8860b;
            }
            .gpai-analisis-icon.error {
                color: #b32d2e;
            }
            .gpai-analisis-icon.info {
                color: #2271b1;
            }
            .gpai-analisis-field {
                font-weight: 600;
                min-width: 140px;
                flex-shrink: 0;
            }
            .gpai-analisis-msg {
                flex: 1;
            }
            .gpai-analisis-suggestion {
                color: #666;
                font-style: italic;
                font-size: 12px;
            }
            .gpai-analisis-links-summary {
                display: flex;
                gap: 16px;
                margin-bottom: 12px;
                flex-wrap: wrap;
            }
            .gpai-analisis-links-stat {
                padding: 6px 14px;
                border-radius: 4px;
                font-weight: 600;
                font-size: 13px;
            }
            .gpai-analisis-links-stat.ok {
                background: #edfaef;
                color: #1e8a3e;
            }
            .gpai-analisis-links-stat.redirect {
                background: #fef8ee;
                color: #b8860b;
            }
            .gpai-analisis-links-stat.error {
                background: #fce8e8;
                color: #b32d2e;
            }
            .gpai-analisis-link-row {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 6px 8px;
                border-bottom: 1px solid #f0f0f1;
                font-size: 12px;
                font-family: monospace;
            }
            .gpai-analisis-link-row:last-child {
                border-bottom: none;
            }
            .gpai-analisis-link-status {
                flex: 0 0 60px;
                text-align: center;
                padding: 2px 8px;
                border-radius: 3px;
                font-weight: 700;
                font-size: 12px;
            }
            .gpai-analisis-link-status.ok {
                background: #edfaef;
                color: #1e8a3e;
            }
            .gpai-analisis-link-status.redirect {
                background: #fef8ee;
                color: #b8860b;
            }
            .gpai-analisis-link-status.error {
                background: #fce8e8;
                color: #b32d2e;
            }
            .gpai-analisis-link-url {
                flex: 1;
                word-break: break-all;
                color: #2271b1;
            }
            .gpai-analisis-link-text {
                color: #666;
                font-size: 11px;
            }
            .gpai-pagespeed-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
                gap: 12px;
                margin: 12px 0;
            }
            .gpai-pagespeed-card {
                text-align: center;
                padding: 16px 12px;
                border-radius: 8px;
                border: 1px solid #e0e0e0;
                background: #fafafa;
            }
            .gpai-pagespeed-card .gpai-ps-score {
                font-size: 32px;
                font-weight: 700;
                line-height: 1.2;
            }
            .gpai-pagespeed-card .gpai-ps-label {
                font-size: 11px;
                color: #666;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-top: 4px;
            }
            .gpai-pagespeed-card .gpai-ps-score.good {
                color: #1e8a3e;
            }
            .gpai-pagespeed-card .gpai-ps-score.warning {
                color: #b8860b;
            }
            .gpai-pagespeed-card .gpai-ps-score.bad {
                color: #b32d2e;
            }
            .gpai-analisis-loading {
                padding: 20px;
                text-align: center;
                color: #666;
                font-style: italic;
            }
            .gpai-analisis-error {
                padding: 12px;
                color: #b32d2e;
                background: #fce8e8;
                border-radius: 4px;
                margin: 8px 0;
            }
            .gpai-analisis-empty {
                padding: 16px;
                text-align: center;
                color: #666;
            }
        </style>

        <!-- SEO Analysis -->
        <div class="gpai-analisis-section" id="gpai-analisis-seo">
            <h4>Analisis SEO <span class="gpai-analisis-seo-score"></span></h4>
            <div id="gpai-analisis-seo-results">
                <div class="gpai-analisis-loading">Analizando campos SEO...</div>
            </div>
        </div>

        <hr>

        <!-- Link Validator -->
        <div class="gpai-analisis-section" id="gpai-analisis-links">
            <h4>Validador de Enlaces Internos</h4>
            <div class="content-btn">
                <button type="button" class="button" id="gpai-analisis-links-btn">Analizar Enlaces</button>
                <span id="gpai-analisis-links-status" style="margin-left:8px;font-style:italic;"></span>
            </div>
            <div id="gpai-analisis-links-results" style="margin-top:12px;"></div>
        </div>

        <hr>

        <!-- PageSpeed -->
        <div class="gpai-analisis-section" id="gpai-analisis-pagespeed">
            <h4>PageSpeed</h4>
            <div class="content-btn">
                <button type="button" class="button" id="gpai-analisis-pagespeed-btn">Analizar PageSpeed</button>
                <span id="gpai-analisis-pagespeed-status" style="margin-left:8px;font-style:italic;"></span>
            </div>
            <div id="gpai-analisis-pagespeed-results" style="margin-top:12px;"></div>
        </div>

        <hr>

        <!-- Schema Validator -->
        <div class="gpai-analisis-section" id="gpai-analisis-schema">
            <h4>Validador de Schema</h4>
            <div class="content-btn">
                <a href="https://validator.schema.org/#url=<?php echo urlencode(get_permalink($post_id)); ?>" target="_blank" class="button">
                    Abrir Schema.org Validator
                </a>
                <a href="https://search.google.com/test/rich-results?url=<?php echo urlencode(get_permalink($post_id)); ?>" target="_blank" class="button">
                    Prueba de Resultados Enriquecidos
                </a>
            </div>
        </div>
    </div>

    <script>
        (function() {
            var postId = <?php echo json_encode($post_id); ?>;
            if (!postId) return;

            var nonce = '<?php echo wp_create_nonce('gpai_nonce'); ?>';

            // ─── SEO Analysis ───────────────────────────────────────────
            function loadSeoAnalysis() {
                var resultsEl = document.getElementById('gpai-analisis-seo-results');
                if (!resultsEl) return;

                resultsEl.innerHTML = '<div class="gpai-analisis-loading">Analizando campos SEO...</div>';

                var fd = new FormData();
                fd.append('action', 'gpai_analisis_seo');
                fd.append('post_id', postId);
                fd.append('nonce', nonce);

                fetch(ajaxurl, { method: 'POST', body: fd })
                    .then(function(r) { return r.json(); })
                    .then(function(res) {
                        if (!res.success) {
                            resultsEl.innerHTML = '<div class="gpai-analisis-error">' + escHtml(res.data || 'Error al analizar SEO.') + '</div>';
                            return;
                        }
                        renderSeoResults(res.data);
                    })
                    .catch(function() {
                        resultsEl.innerHTML = '<div class="gpai-analisis-error">Error de conexion.</div>';
                    });
            }

            function renderSeoResults(data) {
                var resultsEl = document.getElementById('gpai-analisis-seo-results');
                if (!resultsEl) return;

                var scoreEl = document.querySelector('.gpai-analisis-seo-score');
                if (scoreEl && data.total > 0) {
                    var pct = data.pct;
                    var cls = 'good';
                    if (pct < 50) cls = 'bad';
                    else if (pct < 80) cls = 'warning';
                    scoreEl.innerHTML = '<span class="gpai-analisis-score ' + cls + '">' + pct + '% (' + data.score + '/' + data.total + ')</span>';
                }

                var html = '';
                for (var i = 0; i < data.items.length; i++) {
                    var item = data.items[i];
                    var icon = '';
                    if (item.type === 'ok') icon = '✓';
                    else if (item.type === 'warning') icon = '⚠';
                    else if (item.type === 'error') icon = '✗';
                    else icon = 'ℹ';

                    html += '<div class="gpai-analisis-item">';
                    html += '<span class="gpai-analisis-icon ' + item.type + '">' + icon + '</span>';
                    html += '<span class="gpai-analisis-field">' + escHtml(item.field) + ':</span>';
                    html += '<span class="gpai-analisis-msg">' + escHtml(item.message);
                    if (item.suggestion) {
                        html += ' <span class="gpai-analisis-suggestion">' + escHtml(item.suggestion) + '</span>';
                    }
                    html += '</span></div>';
                }

                resultsEl.innerHTML = html;
            }

            // ─── Link Validator ─────────────────────────────────────────
            document.addEventListener('click', function(e) {
                var btn = e.target.closest('#gpai-analisis-links-btn');
                if (!btn) return;

                var resultsEl = document.getElementById('gpai-analisis-links-results');
                var statusEl = document.getElementById('gpai-analisis-links-status');
                if (!resultsEl) return;

                btn.disabled = true;
                if (statusEl) statusEl.textContent = 'Analizando enlaces...';
                resultsEl.innerHTML = '<div class="gpai-analisis-loading">Escaneando enlaces internos...</div>';

                var fd = new FormData();
                fd.append('action', 'gpai_analisis_links');
                fd.append('post_id', postId);
                fd.append('nonce', nonce);

                fetch(ajaxurl, { method: 'POST', body: fd })
                    .then(function(r) { return r.json(); })
                    .then(function(res) {
                        btn.disabled = false;
                        if (statusEl) statusEl.textContent = '';
                        if (!res.success) {
                            resultsEl.innerHTML = '<div class="gpai-analisis-error">' + escHtml(res.data || 'Error al validar enlaces.') + '</div>';
                            return;
                        }
                        renderLinkResults(res.data);
                    })
                    .catch(function() {
                        btn.disabled = false;
                        if (statusEl) statusEl.textContent = '';
                        resultsEl.innerHTML = '<div class="gpai-analisis-error">Error de conexion.</div>';
                    });
            });

            function renderLinkResults(data) {
                var resultsEl = document.getElementById('gpai-analisis-links-results');
                if (!resultsEl) return;

                if (!data.results || data.results.length === 0) {
                    resultsEl.innerHTML = '<div class="gpai-analisis-empty">No se encontraron enlaces internos en este post.</div>';
                    return;
                }

                var html = '<div class="gpai-analisis-links-summary">';
                html += '<span class="gpai-analisis-links-stat ok">✓ ' + data.ok + ' OK</span>';
                html += '<span class="gpai-analisis-links-stat redirect">↻ ' + data.redirect + ' Redirecciones</span>';
                html += '<span class="gpai-analisis-links-stat error">✗ ' + data.error + ' Errores</span>';
                html += '<span style="font-size:12px;color:#666;line-height:28px;">Total: ' + data.total + ' enlaces</span>';
                html += '</div>';

                for (var i = 0; i < data.results.length; i++) {
                    var link = data.results[i];
                    var statusText = link.status > 0 ? link.status + ' ' + link.status_text : link.status_text;
                    html += '<div class="gpai-analisis-link-row">';
                    html += '<span class="gpai-analisis-link-status ' + link.type + '">' + statusText + '</span>';
                    html += '<span class="gpai-analisis-link-url"><a href="' + escAttr(link.url) + '" target="_blank" rel="noopener">' + escHtml(link.href) + '</a></span>';
                    html += '</div>';
                }

                resultsEl.innerHTML = html;
            }

            // ─── PageSpeed ──────────────────────────────────────────────
            document.addEventListener('click', function(e) {
                var btn = e.target.closest('#gpai-analisis-pagespeed-btn');
                if (!btn) return;

                var resultsEl = document.getElementById('gpai-analisis-pagespeed-results');
                var statusEl = document.getElementById('gpai-analisis-pagespeed-status');
                if (!resultsEl) return;

                btn.disabled = true;
                if (statusEl) statusEl.textContent = 'Consultando Google PageSpeed Insights...';
                resultsEl.innerHTML = '<div class="gpai-analisis-loading">Analizando rendimiento...</div>';

                var fd = new FormData();
                fd.append('action', 'gpai_analisis_pagespeed');
                fd.append('post_id', postId);
                fd.append('nonce', nonce);

                fetch(ajaxurl, { method: 'POST', body: fd })
                    .then(function(r) { return r.json(); })
                    .then(function(res) {
                        btn.disabled = false;
                        if (statusEl) statusEl.textContent = '';
                        if (!res.success) {
                            resultsEl.innerHTML = '<div class="gpai-analisis-error">' + escHtml(res.data || 'Error al obtener PageSpeed.') + '</div>';
                            return;
                        }
                        renderPageSpeedResults(res.data);
                    })
                    .catch(function() {
                        btn.disabled = false;
                        if (statusEl) statusEl.textContent = '';
                        resultsEl.innerHTML = '<div class="gpai-analisis-error">Error de conexion.</div>';
                    });
            });

            function renderPageSpeedResults(data) {
                var resultsEl = document.getElementById('gpai-analisis-pagespeed-results');
                if (!resultsEl) return;

                if (data.fallback) {
                    resultsEl.innerHTML = '<div class="gpai-analisis-error">No se pudo conectar con la API de PageSpeed. <a href="' + escAttr(data.url) + '" target="_blank" rel="noopener">Abrir PageSpeed Web.dev</a></div>';
                    return;
                }

                var html = '<div class="gpai-pagespeed-grid">';

                var categories = [
                    { key: 'performance', label: 'Rendimiento' },
                    { key: 'seo', label: 'SEO' },
                    { key: 'accessibility', label: 'Accesibilidad' },
                    { key: 'best_practices', label: 'Buenas Practicas' },
                ];

                for (var i = 0; i < categories.length; i++) {
                    var cat = categories[i];
                    var score = data[cat.key];
                    var cls = 'bad';
                    if (score >= 90) cls = 'good';
                    else if (score >= 50) cls = 'warning';

                    html += '<div class="gpai-pagespeed-card">';
                    html += '<div class="gpai-ps-score ' + cls + '">' + (score !== null ? score : '--') + '</div>';
                    html += '<div class="gpai-ps-label">' + cat.label + '</div>';
                    html += '</div>';
                }

                html += '</div>';
                html += '<div class="content-btn"><a href="' + escAttr(data.url) + '" target="_blank" rel="noopener" class="button">Ver informe completo en PageSpeed</a></div>';

                resultsEl.innerHTML = html;
            }

            // ─── Helpers ────────────────────────────────────────────────
            function escAttr(str) {
                return ('' + (str || '')).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            }

            function escHtml(str) {
                var d = document.createElement('div');
                d.textContent = str;
                return d.innerHTML;
            }

            // ─── Init ───────────────────────────────────────────────────
            if (document.getElementById('gpai-analisis-seo-results')) {
                loadSeoAnalysis();
            }
        })();
    </script>
<?php
    return ob_get_clean();
}
