<?php

use franciscoblancojn\wordpress_utils\FWURespond;
use franciscoblancojn\wordpress_utils\FWUTooltip;
use franciscoblancojn\wordpress_utils\FWUCollapse;
// var_dump([
//     "is_user_admin" => current_user_can( 'manage_options' )
// ]);
$DUPLICADOS = $GPAI_USE_DATA_DUPLICADOS->get();
$respond_duplicates_pendding = null;

if (isset($_POST['save']) && $_POST['save'] == "duplicates_pendding") {
    //PRUEBAS:
    if (GPAI_MODE_DEV && isset($_POST['submit_test']) && $_POST['submit_test'] == 'submit_test') {
        $GPAI_USE_DATA_DUPLICADOS->set(json_decode('{"73":{"post_id":"73","customFields":{"titulo_principal":"Automatiza tu log\u00edstica.","titulo_secundario":"Protege tu flujo de caja. Escala sin fricciones","descripccion_principal":"Aveonline pone al servicio de tu comercio electr\u00f3nico m\u00e1s de 12 a\u00f1os de experiencia, tecnolog\u00eda con inteligencia artificial y automatizaciones listas para usar. Olv\u00eddate de desarrollos costosos: desde la validaci\u00f3n de direcciones hasta el recaudo, todo funciona para ti desde el primer clic.","btn_register_text":"Activa tu cuenta \u2013 Gratis y sin riesgos","btn_register_url":"https:\/\/guias.aveonline.co\/registrarse"},"yoastFields":{"_yoast_wpseo_metadesc":"Aveonline pone al servicio de tu comercio electr\u00f3nico m\u00e1s de 12 a\u00f1os de experiencia, tecnolog\u00eda con inteligencia artificial y automatizaciones listas para usar. Olv\u00eddate de desarrollos costosos: desde la validaci\u00f3n de direcciones hasta el recaudo, todo funciona para ti desde el primer clic.","_yoast_wpseo_content_score":"90","_yoast_wpseo_estimated-reading-time-minutes":"1"},"variations":{"Genera una lista de variaciones para Cucuta, Medellin y Bogota en Colombia. Has que la descripccion_principal tenga como minimo 500 letras.":[{"title":"Automatiza tu log\u00edstica en C\u00facuta con Aveonline","customFields":{"titulo_principal":"Automatiza tu log\u00edstica en C\u00facuta.","titulo_secundario":"Protege tu flujo de caja. Escala sin fricciones en C\u00facuta.","descripccion_principal":"Aveonline se consolida como el aliado estrat\u00e9gico indispensable para potenciar tu comercio electr\u00f3nico en la vibrante ciudad de C\u00facuta. Con m\u00e1s de 12 a\u00f1os de experiencia ininterrumpida en el sector de la log\u00edstica y el comercio electr\u00f3nico, ponemos a tu disposici\u00f3n una tecnolog\u00eda de vanguardia, impulsada por inteligencia artificial y una suite de automatizaciones listas para implementar de manera inmediata. Olv\u00eddate de las costosas y prolongadas inversiones en desarrollos a medida. Desde la validaci\u00f3n precisa de las direcciones de entrega en cada rinc\u00f3n de C\u00facuta, asegurando que tus paquetes lleguen a su destino sin contratiempos, hasta la gesti\u00f3n eficiente del recaudo de tus ventas, nuestro sistema trabaja incansablemente para ti desde el primer clic, optimizando cada etapa de tu cadena de suministro. Entendemos los desaf\u00edos log\u00edsticos espec\u00edficos de C\u00facuta, desde la conectividad hasta las particularidades geogr\u00e1ficas, y hemos dise\u00f1ado nuestras soluciones para superar cualquier obst\u00e1culo. Nuestra plataforma no solo agiliza tus env\u00edos, sino que tambi\u00e9n fortalece tu flujo de caja al minimizar los errores en las entregas y acelerar los procesos de cobro. Permite que Aveonline se encargue de la complejidad operativa para que t\u00fa puedas concentrarte en lo que realmente importa: hacer crecer tu negocio en C\u00facuta y m\u00e1s all\u00e1.","btn_register_text":"Activa tu cuenta en C\u00facuta \u2013 Gratis y sin riesgos","btn_register_url":"https:\/\/guias.aveonline.co\/registrarse"},"yoastFields":{"_yoast_wpseo_metadesc":"Potencia tu e-commerce en C\u00facuta con Aveonline. Automatizaci\u00f3n log\u00edstica, IA y 12 a\u00f1os de experiencia para proteger tu flujo de caja y escalar sin fricciones. \u00a1Activa tu cuenta gratis!","_yoast_wpseo_content_score":"90","_yoast_wpseo_estimated-reading-time-minutes":"1"}},{"title":"Automatiza tu log\u00edstica en Medell\u00edn con Aveonline","customFields":{"titulo_principal":"Automatiza tu log\u00edstica en Medell\u00edn.","titulo_secundario":"Protege tu flujo de caja. Escala sin fricciones en Medell\u00edn.","descripccion_principal":"Aveonline se posiciona como el socio tecnol\u00f3gico esencial para el \u00e9xito de tu comercio electr\u00f3nico en la innovadora ciudad de Medell\u00edn. Con una trayectoria probada de m\u00e1s de 12 a\u00f1os en la optimizaci\u00f3n de procesos log\u00edsticos y de e-commerce, te ofrecemos una plataforma robusta que integra inteligencia artificial y automatizaciones avanzadas, listas para ser implementadas de forma \u00e1gil y eficiente. Desp\u00eddete de las costosas y demoradas inversiones en desarrollo de software a medida. Desde la verificaci\u00f3n rigurosa de direcciones en todas las comunas y barrios de Medell\u00edn, garantizando entregas precisas y puntuales, hasta la gesti\u00f3n fluida del recaudo de tus transacciones, nuestro sistema opera de manera aut\u00f3noma para ti desde el primer momento, simplificando cada eslab\u00f3n de tu cadena log\u00edstica. Comprendemos las din\u00e1micas comerciales y los retos log\u00edsticos particulares de Medell\u00edn, y hemos adaptado nuestras soluciones para asegurar la m\u00e1xima eficiencia operativa. Nuestra tecnolog\u00eda no solo acelera la velocidad de tus env\u00edos, sino que tambi\u00e9n salvaguarda tu flujo de caja al reducir significativamente los errores de entrega y optimizar los ciclos de cobro. Delega la complejidad log\u00edstica a Aveonline y enf\u00f3cate en expandir tu negocio en Medell\u00edn y a nivel nacional.","btn_register_text":"Activa tu cuenta en Medell\u00edn \u2013 Gratis y sin riesgos","btn_register_url":"https:\/\/guias.aveonline.co\/registrarse"},"yoastFields":{"_yoast_wpseo_metadesc":"Aveonline revoluciona tu e-commerce en Medell\u00edn. M\u00e1s de 12 a\u00f1os de experiencia, IA y automatizaciones para optimizar tu log\u00edstica, proteger tu flujo de caja y escalar sin problemas. \u00a1Reg\u00edstrate gratis!","_yoast_wpseo_content_score":"90","_yoast_wpseo_estimated-reading-time-minutes":"1"}},{"title":"Automatiza tu log\u00edstica en Bogot\u00e1 con Aveonline","customFields":{"titulo_principal":"Automatiza tu log\u00edstica en Bogot\u00e1.","titulo_secundario":"Protege tu flujo de caja. Escala sin fricciones en Bogot\u00e1.","descripccion_principal":"Aveonline se establece como el aliado tecnol\u00f3gico fundamental para impulsar tu comercio electr\u00f3nico en la din\u00e1mica capital colombiana, Bogot\u00e1. Respaldados por una experiencia s\u00f3lida de m\u00e1s de 12 a\u00f1os en la optimizaci\u00f3n de operaciones log\u00edsticas y de e-commerce, te brindamos acceso a tecnolog\u00eda de punta, potenciada por inteligencia artificial y un conjunto de automatizaciones preconfiguradas, listas para ser implementadas sin demoras. Elimina la necesidad de realizar costosos y prolongados desarrollos personalizados. Desde la confirmaci\u00f3n precisa de direcciones en cada localidad y upz de Bogot\u00e1, asegurando que tus productos lleguen a sus destinatarios finales de manera eficiente, hasta la gesti\u00f3n transparente del recaudo de tus ventas, nuestro sistema funciona de forma aut\u00f3noma para ti desde la primera interacci\u00f3n, perfeccionando cada componente de tu operaci\u00f3n log\u00edstica. Conocemos a fondo los desaf\u00edos y las oportunidades log\u00edsticas \u00fanicas de Bogot\u00e1, y hemos desarrollado nuestras soluciones para garantizar una ejecuci\u00f3n impecable. Nuestra plataforma no solo agiliza la distribuci\u00f3n de tus pedidos, sino que tambi\u00e9n refuerza tu salud financiera al minimizar las devoluciones y optimizar los tiempos de recepci\u00f3n de pagos. Permite que Aveonline asuma la responsabilidad de la complejidad operativa para que t\u00fa puedas dedicar tu energ\u00eda a hacer crecer tu negocio en Bogot\u00e1 y m\u00e1s all\u00e1 de sus fronteras.","btn_register_text":"Activa tu cuenta en Bogot\u00e1 \u2013 Gratis y sin riesgos","btn_register_url":"https:\/\/guias.aveonline.co\/registrarse"},"yoastFields":{"_yoast_wpseo_metadesc":"Optimiza tu log\u00edstica en Bogot\u00e1 con Aveonline. 12+ a\u00f1os de experiencia, IA y automatizaciones para tu e-commerce. Protege tu flujo de caja y escala sin complicaciones. \u00a1Activa tu cuenta gratis!","_yoast_wpseo_content_score":"90","_yoast_wpseo_estimated-reading-time-minutes":"1"}}]}}}', true));
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
    //OK: ELIMINAR TODOS
    if (isset($_POST['submit_delete']) && $_POST['submit_delete'] == 'delete_all') {
        $GPAI_USE_DATA_DUPLICADOS->set([]);
        $DUPLICADOS = $GPAI_USE_DATA_DUPLICADOS->get();
        $respond_duplicates_pendding = [
            "status" => "ok",
            "message" => "Eiminacion Exitosa.",
            'data' => [],
        ];
    }
    //OK: ELIMINAR UNA VARIAION
    if (isset($_POST['submit_delete']) && $_POST['submit_delete'] != 'generate_all') {
        [$post_id, $prompt, $v] = explode(GPAI_KEY_SEPARETE, $_POST['submit_delete']);
        $post_id = (int)$post_id;
        $v = (int)$v;
        $GPAI_USE_DATA_DUPLICADOS->deleteVariation($post_id, $prompt, $v);
        $DUPLICADOS = $GPAI_USE_DATA_DUPLICADOS->get();
        $respond_duplicates_pendding = [
            "status" => "ok",
            "message" => "Eiminacion Exitosa.",
            'data' => [],
        ];
    }
    // OK: GENERAR TODOS
    if (isset($_POST['submit_generate']) && $_POST['submit_generate'] == 'generate_all') {
        $respond_duplicates_pendding_all = $GPAI_USE_DATA_DUPLICADOS->generateAllVariations();
        $DUPLICADOS = $GPAI_USE_DATA_DUPLICADOS->get();
    }
    // OK: GENERAR UNO
    if (isset($_POST['submit_generate']) && $_POST['submit_generate'] != 'generate_all') {
        [$post_id, $prompt, $v] = explode(GPAI_KEY_SEPARETE, $_POST['submit_generate']);
        $post_id = (int)$post_id;
        $v = (int)$v;
        $respond_duplicates_pendding = $GPAI_USE_DATA_DUPLICADOS->generateVariation($post_id, $prompt, $v);
        $DUPLICADOS = $GPAI_USE_DATA_DUPLICADOS->get();
    }
    // OK: GENERAR UNO
    if (isset($_POST['submit_save_custom_fields'])) {
        [$post_id, $prompt, $v] = explode(GPAI_KEY_SEPARETE, $_POST['submit_save_custom_fields']);
        $post_id = (int)$post_id;
        $v = (int)$v;
        $DUPLICADOS = $GPAI_USE_DATA_DUPLICADOS->get();
        $DATA = $DUPLICADOS[$post_id]['variations'][$prompt][$v];
        $customFields = $DATA['customFields'] ?? [];
        if (!empty($customFields)) {
            GPAI_CF::SET($post_id, $customFields);
            $respond_duplicates_pendding = [
                "status" => "ok",
                "message" => "Campos personalisados Guardados.",
                'data' => [],
            ];
        }
        $yoastFields = $DATA['yoastFields'] ?? [];
        if (!empty($yoastFields)) {
            GPAI_YOAST::SET($post_id, $yoastFields);
            $respond_duplicates_pendding = [
                "status" => "ok",
                "message" => "Campos personalisados Guardados.",
                'data' => [],
            ];
        }
        $gpaiSeoFields = $DATA['gpaiSeoFields'] ?? [];
        if (!empty($gpaiSeoFields)) {
            GPAI_SEO::SET($post_id, $gpaiSeoFields);
            $respond_duplicates_pendding = [
                "status" => "ok",
                "message" => "Campos personalisados Guardados.",
                'data' => [],
            ];
        }
        if(isset($respond_duplicates_pendding['status']) && $respond_duplicates_pendding['status'] === "ok"){
            ?>
            <script>
                document.querySelector('[data-tab="<?= $TAGS[1]['key'] ?>"]').click()
                setTimeout(() => {
                    window.location.reload()
                }, 500);
            </script>
            <?php 
            exit;
        }
        
    }
}

function getHeadCollapseVariation($value, $customFields, $post_id, $prompt, $v)
{
    ob_start();
?>
    <div class="content-btn" style="width: 100%;">
        <strong>
            <?= $value['title'] ?>
        </strong>
        <div style="margin-left: auto;margin-right:2rem;">
            <?php
            $url = add_query_arg($customFields, get_permalink($post_id));
            ?>

            <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer" class="button delete">
                Previsualizar
            </a>
            <button
                type="submit"
                name="submit_delete"
                value="<?= $post_id . GPAI_KEY_SEPARETE . $prompt . GPAI_KEY_SEPARETE . $v ?>"
                class="button button-primary">
                Eliminar
            </button>

            <button
                type="submit"
                name="submit_generate"
                value="<?= $post_id . GPAI_KEY_SEPARETE . $prompt . GPAI_KEY_SEPARETE . $v ?>"
                class="button button-primary">
                Generar Nueva Pagina
            </button>
            <button
                type="submit"
                name="submit_save_custom_fields"
                value="<?= $post_id . GPAI_KEY_SEPARETE . $prompt . GPAI_KEY_SEPARETE . $v ?>"
                class="button">
                Guardar Contenido en Pagina Inicial
            </button>
        </div>
    </div>
    <br>
<?php
    return ob_get_clean();
}

?>
<form method="post">
    <?php FWURespond::render($respond_duplicates_pendding) ?>
    <?php
    if (isset($respond_duplicates_pendding_all)) {
        if ($respond_duplicates_pendding_all['status'] == 'error') {
            FWURespond::render($respond_duplicates_pendding_all);
        } else {
            foreach ($respond_duplicates_pendding_all['data'] as $key => $respond) {
                FWURespond::render($respond);
            }
        }
    }
    ?>
    <input type="hidden" name="save" value="duplicates_pendding">
    <?php
    if (count($DUPLICADOS) == 0) {
    ?>
        <h3>
            No tienes contenido generado.
        </h3>
        <?php
        if (GPAI_MODE_DEV) {
        ?>
            <button
                type="submit"
                name="submit_test"
                value="submit_test"
                class="button button-primary">
                Test
            </button>
        <?php
        }
        ?>
    <?php
    } else {
    ?>
        <div class="content-title-btn">
            <h3>
                Lista de contendigo generado.
            </h3>
            <div class="content-btn">
                <?php
                if (GPAI_MODE_DEV) {
                ?>
                    <button
                        type="submit"
                        name="submit_test"
                        value="submit_test"
                        class="button button-primary">
                        Test
                    </button>
                <?php
                }
                ?>
                <button
                    type="submit"
                    name="submit_delete"
                    value="delete_all"
                    class="button button-primary">
                    Eliminar todos
                </button>
                <button
                    type="submit"
                    name="submit_generate"
                    value="generate_all"
                    class="button button-primary">
                    Generar todos
                </button>
            </div>
        </div>

    <?php
    }

    ?>
    <table class="form-table">
        <?php
        foreach ($DUPLICADOS as $post_id => $duplication) {
            $customFields = $duplication['customFields'];
            $variations = $duplication['variations'];
        ?>
            <tr>
                <th scope="row">
                    <?php FWUTooltip::render(" Post Name", "Nombre de la pagina a generar.") ?>
                </th>
                <td>
                    <?= get_the_title($post_id); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php FWUTooltip::render("Variaciones", "Variacion de pagina a generar.") ?>
                </th>
            </tr>
            <tr>
                <td colspan="2">
                    <table class="form-table">
                        <?php
                        foreach ($variations as $prompt => $variation) {
                        ?>
                            <tr>
                                <th scope="row">
                                    <label>
                                        Prompt
                                    </label>
                                </th>
                                <td>
                                    <i>
                                        "<?= $prompt ?>"
                                    </i>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <?php
                                    foreach ($variation as $v => $value) {
                                        $customFields = $value['customFields'];
                                        $yoastFields = $value['yoastFields'];
                                        $gpaiSeoFields = $value['gpaiSeoFields'] ?? [];
                                    ?>
                                        <?php FWUCollapse::render(
                                            getHeadCollapseVariation($value, $customFields, $post_id, $prompt, $v),
                                            "" .
                                                FWUCollapse::html(
                                                    "Custom Fields",
                                                    GPAI_Custom_Fields($customFields, false),
                                                    true
                                                ) .
                                                FWUCollapse::html(
                                                    "Custom SEO",
                                                    GPAI_Custom_Gpai_Seo($gpaiSeoFields, false),
                                                    true
                                                ) .
                                                (
                                                    function_exists('YoastSEO') ?  FWUCollapse::html(
                                                        "Yoast Seo",
                                                        GPAI_Custom_Fields($yoastFields, false),
                                                        true
                                                    )
                                                    : ""
                                                ),
                                            true
                                        )
                                        ?>
                                    <?php
                                    }
                                    ?>

                                </td>
                            </tr>
                    </table>
                </td>
            </tr>
        <?php
                        }
        ?>
    </table>
    </td>
    </tr>
<?php
        }
?>

</table>

</form>