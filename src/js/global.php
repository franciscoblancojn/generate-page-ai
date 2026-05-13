<script>
    document.querySelectorAll('.nav-tab').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.nav-tab, .tab-content')
                .forEach(el => el.classList.remove('nav-tab-active'));

            btn.classList.add('nav-tab-active');
            document.getElementById(btn.dataset.tab)
                .classList.add('nav-tab-active');
        });
    });
    window.addEventListener('DOMContentLoaded', () => {
        const hash = window.location.hash
        if (hash) {
            const btn = document.querySelector(".nav-tab[href='" + hash + "']")
            if (btn) {
                btn?.click()
            }
        }
    });
    const page = document.getElementById("page-<?= GPAI_KEY ?>")
    window.addEventListener('DOMContentLoaded', () => {
        const btns = page.querySelectorAll('[type="submit"]')
        btns.forEach((e, i) => e.addEventListener('click', (ele) => {
            btns[i].classList.add('loader')
        }))
    });
</script>