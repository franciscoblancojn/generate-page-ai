<?php

use franciscoblancojn\wordpress_utils\FWUPage;

try {
    echo FWUPage::css();

    $GPAI_USE_DATA_CONFIG = new GPAI_USE_DATA_CONFIG();
    $GPAI_USE_DATA_DUPLICADOS = new GPAI_USE_DATA_DUPLICADOS();

    $CONFIG = $GPAI_USE_DATA_CONFIG->get();

    $TAGS = [
        [
            'key' => 'post',
            'title' => 'Gestionar Post',
        ],
        [
            'key' => 'procesar_contenido',
            'title' => 'Procesar Contenido',
        ],
        [
            'key' => 'imagenes',
            'title' => 'Ajustes de Imágenes',
        ],
    ];

    $defaultTag =  $TAGS[0]['key'];

?>
    <div id="page-<?= GPAI_KEY ?>" class="wrap">
        <h1>Generate Page AI</h1>
        <?php FWUPage::tabs($TAGS, $defaultTag); ?>
        <?php
        foreach ($TAGS  as $key => $value) {
        ?>
            <div class="tab-content <?= $value['key'] == $defaultTag ? "nav-tab-active" : "" ?>" id="<?= $value['key'] ?>">
                <?php
                require_once GPAI_DIR . 'src/page/sections/' . $value['key'] . ".php";
                ?>
            </div>
        <?php
        }
        ?>
    </div>
<?php

    echo FWUPage::js(GPAI_KEY);
} catch (\Throwable $th) {
    $error = [
        "status" => "error",
        "message" => $th->getMessage(),
        'data' => [
            'line' => $th->getLine(),
            'file' => $th->getFile(),
        ]
    ];
    echo json_encode($error);
}
