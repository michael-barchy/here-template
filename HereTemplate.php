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
        $source = file_get_contents($templateFile);
        $hash = md5($source);
        $cacheFile = $this->cache . DIRECTORY_SEPARATOR . $name . '.' . $hash . '.php';
        if (!is_file($cacheFile) || filemtime(__FILE__) > filemtime($cacheFile)) {
            file_put_contents($cacheFile, $this->compile($source));
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
    
    /**
     * @param bool $condition
     * @param mixed $then
     * @param mixed $else
     */
    public function if($condition, $then = '', $else = '')
    {
        return $condition ? $then : $else;
    }
    
    /**
     * @param array<array-key, mixed> $array
     * @param string $items
     * @param string $block
     * @return string
     */
    public function foreach($array, $items, $block)
    {
        $items = explode(',', $items);
        $result = array_map(function($entry) use ($items, $block) {
            if (!is_array($entry)) {
                $entry = array($entry);
            }
            $params = array();
            foreach ($items as $item) {
                if (array_key_exists($item, $entry)) {
                    array_push($params, $entry[$item]);
                }
            }
            if (empty($params)) {
                $params = array_values($entry);
            }
            return vsprintf($block, $params);
        }, $array);
        
        return implode('', $result);
    }
    
    /**
     * @param string $source
     * @return string
     */
    private function compile($source)
    {
        $tags = array(
            '/@if\s*\((.*?)\)\s*\r?\n(.*?)(?:\r?\n\s*@else\s*\r?\n(.*?))?\r?\n(\s*)@endif\s*\r?\n/s' => "{\$this->if(%s,<<<HTML\n%s\nHTML\n,<<<HTML\n%s\nHTML\n%s)}\n",
            '/@foreach\s*\((.*?) as (.*?)\)\s*\r?\n(.*?)\r?\n(\s*)@endforeach\s*\r?\n/s' => "{\$this->foreach(\$%s, '%s', <<<HTML\n%s\nHTML\n%s)}\n"
        );
        
        foreach ($tags as $tag => $here) {
            $source = preg_replace_callback($tag, function($matches) use ($here) {
                array_shift($matches);
                return vsprintf($here, $matches);
            }, $source);
        }
        
        return $source;
    }
}
