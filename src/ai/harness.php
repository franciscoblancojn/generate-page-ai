<?php

use franciscoblancojn\wordpress_utils\FWUSystemLog;

class GPAI_AI_HARNESS
{
    private static $recorded = [];
    private static $recording = false;

    /**
     * Inicia la grabación de todas las consultas IA.
     */
    public static function startRecording()
    {
        if (self::$recording) return;
        self::$recording = true;
        self::$recorded = [];

        add_action('gpai_ai_before_request', [self::class, '_captureRequest'], 10, 3);
        add_action('gpai_ai_after_request', [self::class, '_captureResponse'], 10, 4);
    }

    /**
     * Detiene la grabación y devuelve todas las interacciones registradas.
     */
    public static function stopRecording()
    {
        self::$recording = false;
        return self::$recorded;
    }

    /**
     * Configura un mock que responde siempre con el mismo contenido.
     */
    public static function setMockResponse($content, $status = 'ok', $message = 'Respuesta simulada')
    {
        add_filter('gpai_ai_mock_response', function () use ($content, $status, $message) {
            return [
                'status' => $status,
                'message' => $message,
                'data' => $content,
            ];
        }, 10, 0);
    }

    /**
     * Configura mock con lista de respuestas rotativas (pipeline).
     */
    public static function setMockPipeline($responses)
    {
        $index = 0;
        add_filter('gpai_ai_mock_response', function () use ($responses, &$index) {
            $response = $responses[$index % count($responses)];
            $index++;
            return is_array($response) ? $response : [
                'status' => 'ok',
                'message' => 'Respuesta simulada',
                'data' => $response,
            ];
        }, 10, 0);
    }

    /**
     * Elimina todos los mocks de respuesta.
     */
    public static function clearMocks()
    {
        remove_all_filters('gpai_ai_mock_response');
    }

    /**
     * Inyecta logs detallados de cada consulta IA por FWUSystemLog.
     */
    public static function enableDetailedLogging()
    {
        add_action('gpai_ai_before_request', function ($PROMPT, $data, $url) {
            FWUSystemLog::add(GPAI_KEY, [
                'type' => 'HARNESS_REQUEST',
                'prompt_preview' => substr($PROMPT, 0, 500),
                'url' => $url,
            ]);
        }, 10, 3);

        add_action('gpai_ai_after_request', function ($PROMPT, $data, $url, $result) {
            FWUSystemLog::add(GPAI_KEY, [
                'type' => 'HARNESS_RESPONSE',
                'status' => $result['status'] ?? 'unknown',
                'response_preview' => is_string($result['data'] ?? null)
                    ? substr($result['data'], 0, 500)
                    : null,
            ]);
        }, 10, 4);
    }

    /**
     * Captura réplicas exactas request/response (uso interno).
     */
    public static function _captureRequest($PROMPT, $data, $url)
    {
        self::$recorded[] = [
            'type' => 'request',
            'prompt' => $PROMPT,
            'data' => $data,
            'url' => $url,
            'time' => microtime(true),
        ];
    }

    public static function _captureResponse($PROMPT, $data, $url, $result)
    {
        $last = &self::$recorded[count(self::$recorded) - 1];
        if ($last && $last['type'] === 'request') {
            $last['type'] = 'complete';
            $last['response'] = $result;
            $last['duration'] = microtime(true) - $last['time'];
        }
    }
}
