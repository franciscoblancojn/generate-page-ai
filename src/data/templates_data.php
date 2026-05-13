<?php

use franciscoblancojn\wordpress_utils\FWUSystemLog;

define("GPAI_TEMPLATES_CONFIG", 'GPAI_TEMPLATES_CONFIG');
define("GPAI_TEMPLATES_CONTENT", 'GPAI_TEMPLATES_CONTENT');

class GPAI_USE_DATA_TEMPLATES extends GPAI_USE_DATA_BASE
{
    protected $KEY = GPAI_TEMPLATES_CONFIG;
}

class GPAI_USE_DATA_TEMPLATES_CONTENT extends GPAI_USE_DATA_BASE
{
    protected $KEY = GPAI_TEMPLATES_CONTENT;

    public function deleteTemplate($template_id)
    {
        $DATA = $this->get();
        if (isset($DATA[$template_id])) {
            unset($DATA[$template_id]);
            $this->set($DATA);
        }
    }

    public function deletePrompt($template_id, $prompt)
    {
        $DATA = $this->get();
        if (isset($DATA[$template_id]['variations'][$prompt])) {
            unset($DATA[$template_id]['variations'][$prompt]);
            $this->set($DATA);
            if (count($DATA[$template_id]['variations']) == 0) {
                $this->deleteTemplate($template_id);
            }
        }
    }

    public function deleteVariation($template_id, $prompt, $v)
    {
        $DATA = $this->get();
        if (isset($DATA[$template_id]['variations'][$prompt][$v])) {
            unset($DATA[$template_id]['variations'][$prompt][$v]);
            $DATA[$template_id]['variations'][$prompt] = array_values(
                $DATA[$template_id]['variations'][$prompt]
            );
            $this->set($DATA);
            if (count($DATA[$template_id]['variations'][$prompt]) == 0) {
                $this->deletePrompt($template_id, $prompt);
            }
        }
    }
}
