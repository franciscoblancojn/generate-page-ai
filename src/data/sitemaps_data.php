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

    public function saveSitemap($filename, $content)
    {
        $filepath = $this->sitemapDir . $filename;
        $content = wp_unslash($content);
        return file_put_contents($filepath, $content) !== false;
    }

    public function deleteSitemap($filename)
    {
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
        $filepath = $this->sitemapDir . $filename;
        if (file_exists($filepath)) {
            return false;
        }
        $content = wp_unslash($content);
        return file_put_contents($filepath, $content) !== false;
    }
}
