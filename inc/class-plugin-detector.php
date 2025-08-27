<?php

defined('ABSPATH') || exit;

class WP_Hook_Profiler_Plugin_Detector {
    
    private $plugins_cache = null;
    private $themes_cache = null;
    private $wp_core_path = null;
    
    public function __construct() {
        $this->wp_core_path = ABSPATH . 'wp-includes/';
    }
    
    public function identify_callback_source($callback) {
        $source_info = [
            'plugin' => 'wordpress-core',
            'plugin_name' => 'WordPress Core',
            'plugin_file' => null,
            'file' => null,
            'line' => null
        ];
        
        try {
            $reflection = $this->get_callback_reflection($callback);
            
            if (!$reflection) {
                return $this->unknown_source();
            }
            
            $filename = $reflection->getFileName();
            $line = $reflection->getStartLine();
            
            if (!$filename) {
                return $this->unknown_source();
            }
            
            $source_info['file'] = $filename;
            $source_info['line'] = $line;
            
            $plugin_info = $this->match_file_to_plugin($filename);
            if ($plugin_info) {
                return array_merge($source_info, $plugin_info);
            }
            
            $theme_info = $this->match_file_to_theme($filename);
            if ($theme_info) {
                return array_merge($source_info, $theme_info);
            }
            
            if ($this->is_wordpress_core($filename)) {
                return $source_info;
            }
            
            return $this->unknown_source($filename, $line);
            
        } catch (Exception $e) {
            return $this->unknown_source();
        }
    }
    
    public function get_callback_reflection($callback) {
        if (is_string($callback)) {
            if (function_exists($callback)) {
                return new ReflectionFunction($callback);
            }
        } elseif (is_array($callback) && count($callback) === 2) {
            $class = $callback[0];
            $method = $callback[1];
            
            if (is_object($class)) {
                return new ReflectionMethod($class, $method);
            } elseif (is_string($class) && class_exists($class)) {
                return new ReflectionMethod($class, $method);
            }
        } elseif (is_object($callback)) {
            if ($callback instanceof Closure) {
                return new ReflectionFunction($callback);
            } elseif (method_exists($callback, '__invoke')) {
                return new ReflectionMethod($callback, '__invoke');
            }
        }
        
        return null;
    }
    
    private function match_file_to_plugin($filename) {
        $plugins = $this->get_plugins_data();
        
        foreach ($plugins as $plugin_file => $plugin_data) {
            $plugin_dir = dirname(WP_PLUGIN_DIR . '/' . $plugin_file);

			if (!str_contains($plugin_file, '/')) {
				if ($filename === $plugin_file) {
					return [
						'plugin' => $this->get_plugin_slug($plugin_file),
						'plugin_name' => $plugin_data['Name'] ?? 'Unknown Plugin',
						'plugin_file' => $plugin_file
					];
				}
			} else if (strpos($filename, $plugin_dir) === 0) {
                return [
                    'plugin' => $this->get_plugin_slug($plugin_file),
                    'plugin_name' => $plugin_data['Name'] ?? 'Unknown Plugin',
                    'plugin_file' => $plugin_file
                ];
            }
        }
        
        if (strpos($filename, WP_PLUGIN_DIR) === 0) {
            $relative_path = str_replace(WP_PLUGIN_DIR . '/', '', $filename);
            $plugin_slug = explode('/', $relative_path)[0];
            
            return [
                'plugin' => $plugin_slug,
                'plugin_name' => ucwords(str_replace(['-', '_'], ' ', $plugin_slug)),
                'plugin_file' => null
            ];
        }
        
        return null;
    }
    
    private function match_file_to_theme($filename) {
        $theme_root = get_theme_root();
        
        if (strpos($filename, $theme_root) === 0) {
            $relative_path = str_replace($theme_root . '/', '', $filename);
            $theme_slug = explode('/', $relative_path)[0];
            
            $theme = wp_get_theme($theme_slug);
            
            return [
                'plugin' => 'theme-' . $theme_slug,
                'plugin_name' => $theme->exists() ? $theme->get('Name') : ucwords(str_replace(['-', '_'], ' ', $theme_slug)),
                'plugin_file' => null
            ];
        }
        
        return null;
    }
    
    private function is_wordpress_core($filename) {
        return strpos($filename, $this->wp_core_path) === 0 ||
               strpos($filename, ABSPATH . 'wp-admin/') === 0 ||
               strpos($filename, ABSPATH . 'wp-content/mu-plugins/') === 0;
    }
    
    private function get_plugins_data() {
        if ($this->plugins_cache !== null) {
            return $this->plugins_cache;
        }
        
        try {
            if (!function_exists('get_plugins')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            
            $this->plugins_cache = get_plugins();
        } catch (Exception $e) {
            $this->plugins_cache = [];
        }
        
        return $this->plugins_cache;
    }
    
    private function get_plugin_slug($plugin_file) {
        if (strpos($plugin_file, '/') !== false) {
            return dirname($plugin_file);
        }
        
        return pathinfo($plugin_file, PATHINFO_FILENAME);
    }
    
    private function unknown_source($file = null, $line = null) {
        return [
            'plugin' => $file && str_contains($file,'multisite-ultimate')? 'Multisite Ultimate': 'unknown',
            'plugin_name' => $file && str_contains($file,'multisite-ultimate')? 'multisite-ultimate.php': 'unknown',
            'plugin_file' => null,
            'file' => $file,
            'line' => $line
        ];
    }
}