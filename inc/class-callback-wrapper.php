<?php

defined('ABSPATH') || exit;

class WP_Hook_Profiler_Callback_Wrapper {
    
    private $original_function;
    private $hook_name;
    private $priority;
    private $accepted_args;
    private WP_Hook_Profiler_Engine $engine;
    private $uses_references;
    
    public function __construct($original_function, $hook_name, $priority, $accepted_args, $engine) {
        $this->original_function = $original_function;
        $this->hook_name = $hook_name;
        $this->priority = $priority;
        $this->accepted_args = $accepted_args;
        $this->engine = $engine;
        $this->uses_references = $this->callback_uses_references($original_function);
    }
    
    public function __invoke(...$args ) {

        $callback_name = $this->engine->get_callback_name($this->original_function);
        $plugin_info = $this->engine->get_plugin_info_safe($this->original_function);
        $callback_key = $callback_name . '|' . $this->hook_name;
        
        if (!isset($this->engine->callback_aggregates[$callback_key])) {
            $this->engine->callback_aggregates[$callback_key] = [
                'hook' => $this->hook_name,
                'callback' => $callback_name,
                'plugin' => $plugin_info['plugin'],
                'source_file' => $plugin_info['file'],
                'total_time' => 0,
                'call_count' => 0,
                'average_time' => 0,
                'priority' => $this->priority,
                'accepted_args' => $this->accepted_args
            ];
        }
        $start = hrtime(true);
//        if ($this->uses_references) {
//            $result = $this->call_with_references($this->original_function, $args);
//        } else {
//            $result = call_user_func_array($this->original_function, $args);
//        }

		$original_function = $this->original_function;
		$result = $original_function(...$args);
        $end = hrtime(true);
        $eta = $end - $start;
        $eta /= 1e+6; // nanoseconds to milliseconds
        
        if (is_finite($eta) && $eta >= 0) {
            $this->engine->callback_aggregates[$callback_key]['total_time'] += $eta;
            $this->engine->callback_aggregates[$callback_key]['call_count']++;
            $this->engine->total_execution_time += $eta;
            
            if ($this->engine->callback_aggregates[$callback_key]['call_count'] > 0) {
                $this->engine->callback_aggregates[$callback_key]['average_time'] = 
                    $this->engine->callback_aggregates[$callback_key]['total_time'] / 
                    $this->engine->callback_aggregates[$callback_key]['call_count'];
            }
        }
        
        // Update plugin totals
        $plugin_key = $plugin_info['plugin'];
        if (!isset($this->engine->timing_data[$plugin_key])) {
            $this->engine->timing_data[$plugin_key] = [
                'total_time' => 0,
                'hook_count' => 0,
                'callback_count' => 0,
                'hooks' => [],
                'plugin_name' => $plugin_info['plugin_name'],
                'plugin_file' => $plugin_info['plugin_file']
            ];
        }
        
        $this->engine->timing_data[$plugin_key]['total_time'] += $eta;
        $this->engine->timing_data[$plugin_key]['callback_count']++;
        
        if (!in_array($this->hook_name, $this->engine->timing_data[$plugin_key]['hooks'])) {
            $this->engine->timing_data[$plugin_key]['hooks'][] = $this->hook_name;
            $this->engine->timing_data[$plugin_key]['hook_count']++;
        }
        
        return $result;
    }
    
    private function callback_uses_references($callback) {
        try {
            $reflection = $this->engine->get_callback_reflection($callback);
            if (!$reflection) {
                return false;
            }
            
            foreach ($reflection->getParameters() as $param) {
                if ($param->isPassedByReference()) {
                    return true;
                }
            }
        } catch (ReflectionException $e) {
            return false;
        }
        
        return false;
    }
    
    private function call_with_references($callback, $args) {
        if (is_string($callback)) {
            return $callback(...$args);
        } elseif (is_array($callback) && count($callback) === 2) {
            return $callback[0]->{$callback[1]}(...$args);
        } elseif (is_object($callback)) {
            if ($callback instanceof Closure) {
                return $callback(...$args);
            } else {
                return $callback(...$args);
            }
        }
        
        return call_user_func_array($callback, $args);
    }
}