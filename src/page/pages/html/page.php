<?php
try {
    require_once GPAI_DIR . 'src/css/global.php';

    $GPAI_USE_DATA_CONFIG = new GPAI_USE_DATA_CONFIG();
    $CONFIG = $GPAI_USE_DATA_CONFIG->get();

    $TAGS = [
        [
            'key' => 'html',
            'title' => 'Optimización HTML',
        ],
    ];

    $defaultTag = $TAGS[0]['key'];
?>
    <div id="page-<?= GPAI_KEY ?>" class="wrap">
        <h1>Optimización HTML</h1>
        <div class="nav-tab-wrapper woo-nav-tab-wrapper">
            <?php
            foreach ($TAGS as $value) {
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
        foreach ($TAGS as $value) {
        ?>
            <div class="tab-content <?= $value['key'] == $defaultTag ? "nav-tab-active" : "" ?>" id="<?= $value['key'] ?>">
                <?php
                require_once GPAI_DIR . 'src/page/sections/html.php';
                ?>
            </div>
        <?php
        }
        ?>
    </div>
<?php

    require_once GPAI_DIR . 'src/js/global.php';
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
