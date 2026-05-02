<?php

use franciscoblancojn\wordpress_utils\FWUSystemLog;

class DPAI_PROMPT
{

    public static function getPrompt($CONFIG)
    {
        [
            "prompt" => $prompt,
            "campos" => $campos,
        ] = $CONFIG;

        $PROMPT = "
            -----PROMPT ACTUAL-----
            " . $prompt . "
            -----
            Necesito mejorar las siguientes partes del prompt
            " . implode(",", $campos) . "
            Necesito que generes un json con el siguente formato:
            {'parte del prompt':valor mejorado o creado, puede ser string o json}
            Importante manten las misma claves de los json, si empiesa por _ mantenlo asi, tampoco crees campos nuevos.
        ";
        return $PROMPT;
    }
    public static function getMejoraPromptByPrompt($PROMPT)
    {
        $jsonResponse = [];
        try {
            $result = DPAI_AI::sendPrompt($PROMPT);

            if ($result['status'] == 'error') {
                return $result;
            }
            $result['message'] = "Prompts Mejorados";
            FWUSystemLog::add(DPAI_KEY, [
                'type' => "IA Prompt Mejorado result",
                'result' => $result,
            ]);
            $result['data'] = DPAI_AI::parseJson($result['data']);
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
            FWUSystemLog::add(DPAI_KEY, [
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
            return $error;
        }
    }
}
