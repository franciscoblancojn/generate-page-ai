<?php

use franciscoblancojn\wordpress_utils\FWUSystemLog;

class GPAI_USE_DATA_HTACCESS
{
    private $filePath;
    private $lastError = '';

    public function __construct()
    {
        $this->filePath = ABSPATH . '.htaccess';
    }

    public function get()
    {
        if (!file_exists($this->filePath) || !is_file($this->filePath)) {
            return [
                'exists' => false,
                'content' => '',
                'path' => $this->filePath,
                'size' => 0,
                'modified' => 0,
                'readable' => is_readable(dirname($this->filePath)),
                'writable' => is_writable(dirname($this->filePath)) && (!file_exists($this->filePath) || is_writable($this->filePath)),
            ];
        }

        return [
            'exists' => true,
            'content' => file_get_contents($this->filePath),
            'path' => $this->filePath,
            'size' => filesize($this->filePath),
            'modified' => filemtime($this->filePath),
            'readable' => is_readable($this->filePath),
            'writable' => is_writable($this->filePath),
        ];
    }

    /**
     * Escribe el .htaccess. Recibe el contenido ya "unslashed": no aplica wp_unslash().
     *
     * Tras escribir hace una petición al home; si responde 5xx restaura el
     * contenido anterior y devuelve false (ver getLastError()).
     */
    public function save($content)
    {
        $this->lastError = '';
        $content = trim((string) $content);

        $existed = file_exists($this->filePath);
        $previous = $existed ? file_get_contents($this->filePath) : '';

        if (file_put_contents($this->filePath, $content) === false) {
            $this->lastError = 'No se pudo escribir el archivo .htaccess.';
            return false;
        }

        $status = $this->checkSiteStatus();
        if ($status < 500) {
            return true;
        }

        if ($existed) {
            file_put_contents($this->filePath, $previous);
        } else {
            unlink($this->filePath);
        }

        $this->lastError = 'El sitio respondió ' . $status . ' con el nuevo .htaccess; se restauró la versión anterior.';
        FWUSystemLog::add(GPAI_KEY, [
            'type' => 'htaccess_rollback',
            'status' => $status,
            'content' => $content,
        ]);

        return false;
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Código HTTP del home, o 0 si no se pudo hacer la petición (loopback
     * bloqueado, timeout...). 0 no se trata como fallo para no revertir a ciegas.
     */
    private function checkSiteStatus()
    {
        $response = wp_remote_get(add_query_arg('gpai_htaccess_check', time(), home_url('/')), [
            'timeout' => 10,
            'redirection' => 0,
            'sslverify' => false,
            'limit_response_size' => 1024,
        ]);

        if (is_wp_error($response)) {
            return 0;
        }

        return (int) wp_remote_retrieve_response_code($response);
    }

    public function backup()
    {
        if (!file_exists($this->filePath)) {
            return false;
        }
        $backup = $this->filePath . '.' . date('Y-m-d-H-i-s') . '.bak';
        return copy($this->filePath, $backup);
    }
}
