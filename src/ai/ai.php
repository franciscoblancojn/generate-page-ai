<?php

use franciscoblancojn\wordpress_utils\FWUSystemLog;

class GPAI_AI
{
    private static function getConfig()
    {
        $GPAI_USE_DATA_CONFIG = new GPAI_USE_DATA_CONFIG();
        return $GPAI_USE_DATA_CONFIG->get();
    }
    private static function request(
        $url,
        $method = "GET",
        $data = null
    ) {
        $jsonResponse = [];

        try {
            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $timeout = 300000;
            if (defined('GPAI_HTTP_TIMEOUT')) {
                $timeout = GPAI_HTTP_TIMEOUT;
            }
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

            if ($method == "POST") {
                curl_setopt($ch, CURLOPT_POST, true);
            }

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);

            if (isset($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
            }

            $response = curl_exec($ch);

            // ❌ Error de cURL
            if (curl_errno($ch)) {
                throw new \RuntimeException('Error en cURL: ' . curl_error($ch));
            }

            // 📡 Código HTTP
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            $jsonResponse = json_decode($response, true);

            // ❌ Manejo de rate limit o errores HTTP
            if ($httpCode >= 400) {
                throw new \RuntimeException(
                    'API Error: ' . ($jsonResponse['error']['message'] ?? 'Error desconocido')
                );
            }

            // ❌ Error de API (Google)
            if (isset($jsonResponse['error'])) {
                throw new \RuntimeException(
                    'API Error: ' . $jsonResponse['error']['message']
                );
            }

            return [
                "status" => "ok",
                "message" => "Respuesta Exitosa",
                'data' => $jsonResponse
            ];
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

            FWUSystemLog::add(GPAI_KEY, [
                'type' => "IA error",
                'data' => $error
            ]);

            return $error;
        }
    }
    public static function sendPrompt($PROMPT)
    {
        $jsonResponse = [];
        try {
            $CONFIG = self::getConfig();
            // 1. Configuración de parámetros
            $apiKey = $CONFIG['apikey']; // Reemplaza con tu clave real
            $modelo = $CONFIG['modelo'];
            $url = "https://generativelanguage.googleapis.com/v1/models/{$modelo}:generateContent?key={$apiKey}";

            // 2. Estructura del cuerpo de la petición (JSON)
            $data = [
                "contents" => [
                    [
                        "parts" => [
                            ["text" => $PROMPT]
                        ]
                    ]
                ],
                "generationConfig" => [
                    "maxOutputTokens" => 65536,
                    "temperature" => 0.2
                ]
            ];

            $result = self::request($url, "POST", $data);
            if ($result['status'] == 'error') {
                return $result;
            }
            $jsonResponse = $result['data'];
            // 3. Extraer el texto de la respuesta siguiendo la estructura de la API
            if (isset($jsonResponse['candidates'][0]['content']['parts'][0]['text'])) {
                $data = $jsonResponse['candidates'][0]['content']['parts'][0]['text'];
                return [
                    "status" => "ok",
                    "message" => "Respuesta Exitosa",
                    'data' => $data,
                ];
            } else {
                throw new \RuntimeException('Error en cURL');
            }
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
            FWUSystemLog::add(GPAI_KEY, [
                'type' => "IA error",
                'data' => $error
            ]);
            return $error;
        }
    }
    public static function getModels()
    {
        $jsonResponse = [];

        try {
            $CONFIG = self::getConfig();

            $apiKey = $CONFIG['apikey'];

            // Endpoint para listar modelos
            $url = "https://generativelanguage.googleapis.com/v1/models?key={$apiKey}";

            $result = self::request($url);
            if ($result['status'] == 'error') {
                return $result;
            }
            $jsonResponse = $result['data'];

            $models = [];

            if (!empty($jsonResponse['models']) && is_array($jsonResponse['models'])) {
                foreach ($jsonResponse['models'] as $model) {

                    $methods = $model['supportedGenerationMethods'] ?? [];

                    // Filtrar solo los que soportan generateContent
                    if (!in_array('generateContent', $methods)) {
                        continue;
                    }

                    $models[] = [
                        'name' => $model['name'],
                        'model' => str_replace('models/', '', $model['name']),
                        'displayName' => $model['displayName'] ?? $model['name'],
                    ];
                }
            }

            return [
                "status" => "ok",
                "message" => "Modelos obtenidos correctamente",
                "data" => $models,
            ];
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

            FWUSystemLog::add(GPAI_KEY, [
                'type' => "IA modelos error",
                'data' => $error
            ]);

            return $error;
        }
    }
    public static function parseJson($dataString)
    {
        if (!$dataString) {
            throw new \RuntimeException('Respuesta vacía');
        }

        // 1. Quitar bloques ```json ... ```
        $dataString = preg_replace('/^```json\s*/i', '', $dataString);
        $dataString = preg_replace('/^```/i', '', $dataString);
        $dataString = preg_replace('/```$/', '', $dataString);

        // 2. Trim
        $dataString = trim($dataString);

        // 3. Intento directo
        $data = json_decode($dataString, true);

        // 4. Si falla, intentar limpiar más agresivo (muy común en IA)
        if (json_last_error() !== JSON_ERROR_NONE) {

            // Extraer solo el JSON válido (array o objeto)
            if (preg_match('/(\{.*\}|\[.*\])/s', $dataString, $matches)) {
                $dataString = $matches[0];
                $data = json_decode($dataString, true);
            }
        }

        // 5. Validación final
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(
                'Error al parsear JSON: ' . json_last_error_msg() .
                    ' | String recibido: ' . substr($dataString, 0, 500)
            );
        }

        return $data;
    }
}
