<script>
    const onLoad = () => {
        try {
            document.querySelectorAll('.nav-tab').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.querySelectorAll('.nav-tab, .tab-content')
                        .forEach(el => el.classList.remove('nav-tab-active'));

                    btn.classList.add('nav-tab-active');
                    const tabContent = document.getElementById(btn.dataset.tab);
                    if (tabContent) tabContent.classList.add('nav-tab-active');
                });
            });

            const hash = window.location.hash
            if (hash) {
                const btn = document.querySelector(".nav-tab[href='" + hash + "']")
                if (btn) btn.click()
            }

            const page = document.getElementById("page-<?= GPAI_KEY ?>")
            if (page) {
                const btns = page.querySelectorAll('[type="submit"]')
                btns.forEach((e, i) => e.addEventListener('click', (ele) => {
                    btns[i].classList.add('loader')
                }))
            }
        } catch (e) {
            console.error('GPAI init error:', e)
        }
    }
    window.addEventListener('DOMContentLoaded', onLoad);

    function gpaiExport(action, payload, filename) {
        const formData = new FormData()
        formData.append('action', action)
        Object.entries(payload).forEach(([k, v]) => formData.append(k, v))

        fetch(ajaxurl, { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' })
                const a = document.createElement('a')
                a.href = URL.createObjectURL(blob)
                a.download = filename
                a.click()
                URL.revokeObjectURL(a.href)
            })
    }

    function gpaiOpenModal(modalId) {
        document.getElementById(modalId).classList.add('open')
    }

    function gpaiCloseModal(modalId) {
        document.getElementById(modalId).classList.remove('open')
    }

    function gpaiImport(action, payload, modalId, reload) {
        const textarea = document.querySelector('#' + modalId + ' .gpai-import-data')
        const btn = document.querySelector('#' + modalId + ' .gpai-import-btn')
        let raw = textarea.value.trim()
        if (!raw) { alert('Pega o carga un JSON primero.'); return }
        try { JSON.parse(raw) } catch (e) { alert('JSON inválido.'); return }

        btn.disabled = true
        btn.textContent = 'Importando...'

        const formData = new FormData()
        formData.append('action', action)
        Object.entries(payload).forEach(([k, v]) => formData.append(k, v))
        formData.append('data', raw)

        fetch(ajaxurl, { method: 'POST', body: formData })
            .then(r => r.json())
            .then(res => {
                btn.disabled = false
                btn.textContent = 'Importar'
                if (res.success) {
                    alert(res.data.message)
                    gpaiCloseModal(modalId)
                    if (reload) location.reload()
                } else {
                    alert(res.data.message || 'Error al importar.')
                }
            })
    }

    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('gpai-import-file')) {
            const file = e.target.files[0]
            if (!file) return
            const reader = new FileReader()
            const textarea = e.target.closest('.gpai-modal-content').querySelector('.gpai-import-data')
            reader.onload = function (ev) { textarea.value = ev.target.result }
            reader.readAsText(file)
        }
    })

    document.addEventListener('click', function (e) {
        const modal = e.target.closest('.gpai-modal')
        if (modal && e.target === modal) gpaiCloseModal(modal.id)
    })

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.gpai-seo-generate-btn')
        if (!btn) return
        const postId = btn.dataset.postId
        const nonce = btn.dataset.nonce
        const statusEl = btn.parentElement.querySelector('.gpai-seo-generate-status')
        if (!postId || !nonce) return

        btn.disabled = true
        if (statusEl) statusEl.textContent = 'Generando...'

        const formData = new FormData()
        formData.append('action', 'gpai_seo_generate')
        formData.append('post_id', postId)
        formData.append('nonce', nonce)

        fetch(ajaxurl, { method: 'POST', body: formData })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    if (statusEl) statusEl.textContent = '✓ SEO generado. Recargando...'
                    setTimeout(() => location.reload(), 800)
                } else {
                    btn.disabled = false
                    if (statusEl) statusEl.textContent = '✗ ' + (res.data || 'Error')
                }
            })
            .catch(() => {
                btn.disabled = false
                if (statusEl) statusEl.textContent = '✗ Error de conexión'
            })
    })

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.gpai-html-optimize-btn')
        if (!btn) return
        const postId = btn.dataset.postId
        const nonce = btn.dataset.nonce
        const statusEl = btn.parentElement.querySelector('.gpai-html-optimize-status')
        if (!postId || !nonce) return

        btn.disabled = true
        if (statusEl) statusEl.textContent = 'Optimizando HTML con IA...'

        const formData = new FormData()
        formData.append('action', 'gpai_html_generate')
        formData.append('post_id', postId)
        formData.append('nonce', nonce)

        fetch(ajaxurl, { method: 'POST', body: formData })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    if (statusEl) statusEl.textContent = '✓ HTML optimizado. Recargando...'
                    setTimeout(() => location.reload(), 800)
                } else {
                    btn.disabled = false
                    if (statusEl) statusEl.textContent = '✗ ' + (res.data || 'Error')
                }
            })
            .catch(() => {
                btn.disabled = false
                if (statusEl) statusEl.textContent = '✗ Error de conexión'
            })
    })
</script>