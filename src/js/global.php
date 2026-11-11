<script>
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.gpai-seo-generate-btn')
        if (!btn) return
        const postId = btn.dataset.postId
        const nonce = btn.dataset.nonce
        const statusEl = btn.parentElement.querySelector('.gpai-seo-generate-status')
        if (!postId || !nonce) return

        if (!confirm('¿Generar nuevos datos SEO con IA? Se sobrescribirán los valores actuales.')) return

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

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.gpai-html-swap-btn')
        if (!btn) return
        const postId = btn.dataset.postId
        const nonce = btn.dataset.nonce
        const statusEl = btn.parentElement.querySelector('.gpai-html-swap-status')
        if (!postId || !nonce) return

        if (!confirm(btn.dataset.confirm || '¿Cambiar la versión HTML activa?')) return

        btn.disabled = true
        if (statusEl) statusEl.textContent = 'Cambiando...'

        const formData = new FormData()
        formData.append('action', 'gpai_html_swap')
        formData.append('post_id', postId)
        formData.append('nonce', nonce)

        fetch(ajaxurl, { method: 'POST', body: formData })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    if (statusEl) statusEl.textContent = '✓ ' + (res.data.message || 'Cambiado. Recargando...')
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
