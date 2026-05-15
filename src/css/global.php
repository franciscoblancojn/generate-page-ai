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

    .gpai-modal {
        display: none;
        position: fixed;
        z-index: 9999;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        align-items: center;
        justify-content: center;
    }

    .gpai-modal.open {
        display: flex;
    }

    .gpai-modal-content {
        background: #fff;
        border-radius: 8px;
        padding: 24px;
        max-width: 600px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
        position: relative;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.2);
    }

    .gpai-modal-content h3 {
        margin-top: 0;
    }

    .gpai-modal-close {
        position: absolute;
        top: 12px;
        right: 16px;
        font-size: 24px;
        cursor: pointer;
        color: #666;
    }

    .gpai-modal-close:hover {
        color: #000;
    }

    #gpai_seo_box .inside {
        margin: 0;
        padding: 0;
    }

    #gpai_seo_box details > div {
        padding: 12px;
    }

    #gpai_seo_box .form-table th {
        font-weight: 500;
    }

    #gpai_seo_box .form-table input.regular-text,
    #gpai_seo_box .form-table select.regular-text {
        width: 100%;
    }

    .gpai-modal-content textarea {
        width: 100%;
        min-height: 200px;
        font-family: monospace;
    }

    .gpai-modal-content .gpai-modal-actions {
        margin-top: 16px;
        display: flex;
        gap: 8px;
    }
</style>