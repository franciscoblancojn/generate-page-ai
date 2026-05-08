<?php


$DPAI_USE_DATA_CONFIG = new DPAI_USE_DATA_CONFIG();
$DPAI_USE_DATA_DUPLICADOS = new DPAI_USE_DATA_DUPLICADOS();

$CONFIG = $DPAI_USE_DATA_CONFIG->get();

$TAGS = [
    [
        'key' => 'config',
        'title' => 'Configuracion IA',
    ],
    [
        'key' => 'post_fileds_prompts',
        'title' => 'Post, Campos y Prompts',
    ],
    [
        'key' => 'procesar_contenido',
        'title' => 'Procesar Contenido',
    ],
];

$defaultTag =  $TAGS[0]['key'];

?>
<div id="page-<?= DPAI_KEY ?>" class="wrap">
    <h1>Duplicate Page AI</h1>
    <div class="nav-tab-wrapper woo-nav-tab-wrapper">
        <?php
        foreach ($TAGS  as $key => $value) {
        ?>
            <a
                class="nav-tab <?= $value['key'] == $defaultTag ? "nav-tab-active" : "" ?>"
                data-tab="<?= $value['key'] ?>"
                href="#tag-<?= $value['key'] ?>">
                <?= $value['title'] ?>
            </a>
        <?php
        }
        ?>
    </div>
    <?php
    foreach ($TAGS  as $key => $value) {
    ?>
        <div class="tab-content <?= $value['key'] == $defaultTag ? "nav-tab-active" : "" ?>" id="<?= $value['key'] ?>">
            <?php
            require_once DPAI_DIR . 'src/page/sections/' . $value['key'] . ".php";
            ?>
        </div>
    <?php
    }
    ?>
    <style>
        .tab-content:not(.nav-tab-active) {
            display: none;
        }

        .tab-content {
            padding-top: 1rem;
        }

        .nav-tab {
            cursor: pointer;
        }

        .message {
            font-weight: 900;
            position: sticky;
            left: 0;
            top: 2.5rem;
            padding: 1rem;
            border-radius: .5rem;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            z-index: 10;
        }

        .error {
            color: #ffffffff;
            background: #d63638;
        }

        .ok {
            color: #ffffffff;
            background: #25992fff;
        }

        [type="submit"].loader {
            position: relative;
            color: transparent !important;
        }

        [type="submit"].loader::after {
            content: '';
            display: block;
            position: absolute;
            inset: 0;
            margin: auto;
            width: 1rem;
            height: 1rem;
            aspect-ratio: 1/1;
            border-radius: 100%;
            border: 2px solid #1d2327;
            border-top-color: transparent;
            animation: rotate 1s infinite;
        }

        [type="submit"].button-primary.loader::after {
            border: 2px solid #fff;
            border-top-color: transparent;
        }

        @keyframes rotate {
            to {
                transform: rotateZ(360deg);
            }
        }

        .content-btn {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
        }

        .content-title-btn {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
        }

        .content-btn .submit {
            margin: 0;
            padding: 0;
        }

        .goshap-tooltip {
            position: relative;
            cursor: pointer;
            margin-left: 6px;
            display: inline-block;
        }

        .goshap-tooltip-text::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 10px;
            border-width: 5px;
            border-style: solid;
            border-color: #1d2327 transparent transparent transparent;
        }

        .goshap-tooltip-text {
            visibility: hidden;
            opacity: 0;
            width: 360px;
            background: #1d2327;
            color: #fff;
            text-align: left;
            padding: 8px;
            border-radius: 6px;
            position: absolute;
            z-index: 9999;
            bottom: 125%;
            left: 0;
            transition: opacity 0.2s ease;
            font-size: 12px;
            line-height: 1.4;
        }

        .goshap-tooltip:hover .goshap-tooltip-text {
            visibility: visible;
            opacity: 1;
        }




        /* Contenedor general */
        details {
            margin-bottom: 1rem;
            border: 1px solid #dcdcde;
            border-radius: 8px;
            background: #fff;
            overflow: hidden;
        }

        /* Header tipo collapse */
        details summary {
            cursor: pointer;
            padding: 12px 16px;
            font-weight: 600;
            font-size: 14px;
            background: #f6f7f7;
            list-style: none;
            position: relative;
            transition: background 0.2s ease;
        }

        /* Hover */
        details summary:hover {
            background: #e5e5e5;
        }

        /* Quitar flecha default */
        details summary::-webkit-details-marker {
            display: none;
        }

        /* Flecha custom */
        details summary::after {
            content: "▸";
            position: absolute;
            right: 16px;
            font-size: 14px;
            transition: transform 0.2s ease;
        }

        /* Rotar cuando está abierto */
        details[open] summary::after {
            transform: rotate(90deg);
        }

        /* Contenido interno */
        details>div {
            padding: 16px;
            background: #ffffff;
            border-top: 1px solid #dcdcde;
            max-height: 75dvh;
            overflow: auto;
        }

        .btn-to-right.btn-to-right {
            margin-left: auto;
        }
    </style>

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
        const page = document.getElementById("page-<?= DPAI_KEY ?>")
        window.addEventListener('DOMContentLoaded', () => {
            const btns = page.querySelectorAll('[type="submit"]')
            btns.forEach((e, i) => e.addEventListener('click', (ele) => {
                btns[i].classList.add('loader')
            }))
        });
    </script>
</div>
<?php
