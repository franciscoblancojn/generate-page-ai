<?php

class GPAI_USE_DATA_HTACCESS
{
    private $filePath;

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

    public function save($content)
    {
        $content = wp_unslash($content);
        $content = trim($content);
        return file_put_contents($this->filePath, $content) !== false;
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
