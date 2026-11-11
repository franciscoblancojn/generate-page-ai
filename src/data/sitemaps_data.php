<?php

class GPAI_USE_DATA_SITEMAPS
{
    private $sitemapDir;

    public function __construct()
    {
        $this->sitemapDir = ABSPATH;
    }

    public function getSitemaps()
    {
        $files = glob($this->sitemapDir . '*.xml');
        $sitemaps = [];
        foreach ($files as $filepath) {
            $filename = basename($filepath);
            if (!is_file($filepath)) continue;
            $sitemaps[$filename] = [
                'name' => $filename,
                'path' => $filepath,
                'content' => file_get_contents($filepath),
                'size' => filesize($filepath),
                'modified' => filemtime($filepath),
            ];
        }
        ksort($sitemaps);
        return $sitemaps;
    }

    /**
     * Recibe el contenido ya "unslashed": no aplica wp_unslash().
     */
    public function saveSitemap($filename, $content)
    {
        $filename = $this->sanitizeFilename($filename);
        if ($filename === '') {
            return false;
        }
        $filepath = $this->sitemapDir . $filename;
        return file_put_contents($filepath, $content) !== false;
    }

    public function deleteSitemap($filename)
    {
        $filename = $this->sanitizeFilename($filename);
        if ($filename === '') {
            return false;
        }
        $filepath = $this->sitemapDir . $filename;
        if (file_exists($filepath) && is_file($filepath)) {
            return unlink($filepath);
        }
        return false;
    }

    public function createSitemap($filename, $content = '')
    {
        if (!str_ends_with($filename, '.xml')) {
            $filename .= '.xml';
        }
        $filename = $this->sanitizeFilename($filename);
        if ($filename === '') {
            return false;
        }
        $filepath = $this->sitemapDir . $filename;
        if (file_exists($filepath)) {
            return false;
        }
        return file_put_contents($filepath, $content) !== false;
    }

    /**
     * Solo nombres de archivo .xml en la raíz (evita escribir/borrar fuera de ABSPATH).
     */
    private function sanitizeFilename($filename)
    {
        $filename = sanitize_file_name(basename((string) $filename));
        if ($filename === '' || substr($filename, -4) !== '.xml') {
            return '';
        }
        return $filename;
    }
}
