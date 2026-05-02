<?php
function DPAI_Collapse($title, $content)
{
    ob_start();
?>
    <style>
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
    </style>
    <details>
        <summary style="display: flex;">
            <span><?= $title ?> </span>
        </summary>
        <div>
            <?= $content ?>
        </div>
    </details>
<?php
    return ob_get_clean();
}
