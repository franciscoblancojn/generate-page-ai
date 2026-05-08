<?php

use franciscoblancojn\wordpress_utils\FWUSystemLog;

class GPAI_PROMPT
{

    public static function getPrompt($CONFIG)
    {
        [
            "prompt" => $prompt,
            "campos" => $campos,
        ] = $CONFIG;

        $PROMPT = "
            -----PROMPT ACTUAL NO PROCESAR SOLO LEER-----
            " . $prompt . "
            -----FIN DE PROMPT ACTUAL NO PROCESAR SOLO LEER-----
            Necesito mejorar las siguientes partes del prompt
            " . implode(",", $campos) . "
            Necesito que generes un json con el siguente formato:
            {'parte del prompt (" . implode(",", $campos) . ")':valor mejorado o creado, puede ser string o json}
            Importante los valore que necesito que generes no son los valores del campo sino prompts para generar esos campos.
            Importante manten las misma claves de los json, si empiesa por _ mantenlo asi, tampoco crees campos nuevos.
            Importante las url por lo general se mantienen igual.
        ";
        return $PROMPT;
    }
    public static function getMejoraPromptByPrompt($PROMPT)
    {
        $jsonResponse = [];
        try {
            $result = GPAI_AI::sendPrompt($PROMPT);

            if ($result['status'] == 'error') {
                return $result;
            }
            $result['message'] = "Prompts Mejorados";
            FWUSystemLog::add(GPAI_KEY, [
                'type' => "IA Prompt Mejorado result",
                'result' => $result,
            ]);
            $result['data'] = GPAI_AI::parseJson($result['data']);
            return $result;
        } catch (\Throwable $th) {
            $error = [
                "status" => "error",
                "message" => $th->getMessage(),
                'data' => [
                    'line' => $th->getLine(),
                    'file' => $th->getFile(),
                    'jsonResponse' => $jsonResponse
                ]
            ];
            return $error;
        }
    }

    public static function getMejoraPrompt($CONFIG)
    {
        try {
            $PROMPT = self::getPrompt($CONFIG);
            $result = self::getMejoraPromptByPrompt($PROMPT);
            FWUSystemLog::add(GPAI_KEY, [
                'type' => "IA Prompt Mejorado",
                 ...$CONFIG,
                'result' => $result,
            ]);
            return $result;
        } catch (\Throwable $th) {
            $error = [
                "status" => "error",
                "message" => $th->getMessage(),
                'data' => [
                    'line' => $th->getLine(),
                    'file' => $th->getFile(),
                ]
            ];
            FWUSystemLog::add(GPAI_KEY, [
                'type' => "IA Error Prompt Mejorado",
                 ...$CONFIG,
                'error' => $error,
            ]);
            return $error;
        }
    }
}
