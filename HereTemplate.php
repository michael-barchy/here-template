<?php

namespace HereTemplate;

class HereTemplate
{
    /** @var string */
    private $dir = '.';
    /** @var string */
    private $cache = 'cache';
    /**
     * @param array<string, mixed> $options
     */
    public function __construct($options = array())
    {
        $dir = $this->dir;
        if (array_key_exists('dir', $options) && is_string($options['dir'])) {
            $dir = $options['dir'];
        }
        
        if (is_dir(realpath($dir))) {
            $this->dir = $dir;
        }
        
        $cache = $this->cache;
        if (array_key_exists('cache', $options) && is_string($options['cache'])) {
            $cache = $options['cache'];
        }
        
        if (is_dir(realpath($cache))) {
            $this->cache = $cache;
        }
    }
    
    /**
     * @param string $template
     * @param array<string, mixed> $context
     * @return string
     */
    public function render($template, $context = array())
    {
        // Cache file
        $templateFile = $this->dir . DIRECTORY_SEPARATOR . $template;
        $ext = strrchr(basename($templateFile), '.');
        $name = basename($templateFile, $ext);
        $hash = md5(file_get_contents($templateFile));
        $cacheFile = $this->cache . DIRECTORY_SEPARATOR . $name . '.' . $hash . '.php';
        if (!is_file($cacheFile)) {
            copy($templateFile, $cacheFile);
        }
        
        // Clear cache
        $cacheFiles = scandir($this->cache);
        $cacheFiles = array_filter($cacheFiles, function($f) use ($name, $cacheFile) {
           return basename($cacheFile) !== $f && $name === substr($f, 0, strlen($name));
        });
        foreach ($cacheFiles as $f) {
            unlink($this->cache . DIRECTORY_SEPARATOR . $f);
        }
        
        ob_start();
        extract($context);
        $result = include($cacheFile);
        if (1 === $result) { // No return statement
            $result = eval(sprintf(
                "return <<<HTML\n%s\nHTML;",
                ob_get_clean()
            ));
        }
        
        return $result;
    }
}
