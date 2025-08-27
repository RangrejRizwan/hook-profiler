<?php

/**
 * Plugin Name: Early Loading Plugin Timing Tracker
 * Description: Tracks loading time of each plugin
 * Version:     1.0.0
 * Author:      David Stone
 */
/**
 * This must-use plugin loads first alphabetically to initialize timing
 * tracking for all other plugins and core functionality.
 * 
 * @package WP_Hook_Profiler
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

// Global array to store timing data
global $wp_hook_profiler_plugin_timings, $wp_hook_profiler_current_plugin_start;

$wp_hook_profiler_plugin_timings = [];
$wp_hook_profiler_current_plugin_start = microtime(true);

/**
 * Initialize timing tracking system
 */
function wp_hook_profiler_init_timing() {
	global $wp_hook_profiler_plugin_timings, $wp_hook_profiler_current_plugin_start;
	
	// Record sunrise.php start time if available
	if (defined('WP_HOOK_PROFILER_SUNRISE_START_TIME')) {
		$wp_hook_profiler_plugin_timings['sunrise.php'] = [
			'start_time' => WP_HOOK_PROFILER_SUNRISE_START_TIME,
			'end_time' => null,
			'duration' => 0,
			'type' => 'sunrise'
		];
	}
	
	// Initialize current plugin start time
	$wp_hook_profiler_current_plugin_start = microtime(true);
}

/**
 * Record plugin timing data
 */
function wp_hook_profiler_record_plugin_timing($plugin_file, $type = 'plugin') {
	global $wp_hook_profiler_plugin_timings, $wp_hook_profiler_current_plugin_start;
	
	$end_time = microtime(true);
	$duration = $end_time - $wp_hook_profiler_current_plugin_start;
	
	$wp_hook_profiler_plugin_timings[$plugin_file] = [
		'start_time' => $wp_hook_profiler_current_plugin_start,
		'end_time' => $end_time,
		'duration' => $duration,
		'type' => $type
	];
	
	// Reset start time for next plugin
	$wp_hook_profiler_current_plugin_start = microtime(true);
}

/**
 * Get all timing data
 */
function wp_hook_profiler_get_timing_data() {
	global $wp_hook_profiler_plugin_timings;
	
	// Finalize sunrise.php timing if it exists
	if (isset($wp_hook_profiler_plugin_timings['sunrise.php']) && 
		$wp_hook_profiler_plugin_timings['sunrise.php']['end_time'] === null && 
		defined('WP_HOOK_PROFILER_SUNRISE_END_TIME')) {
		
		$wp_hook_profiler_plugin_timings['sunrise.php']['end_time'] = WP_HOOK_PROFILER_SUNRISE_END_TIME;
		$wp_hook_profiler_plugin_timings['sunrise.php']['duration'] = 
			WP_HOOK_PROFILER_SUNRISE_END_TIME - WP_HOOK_PROFILER_SUNRISE_START_TIME;
	}
	
	return $wp_hook_profiler_plugin_timings ?: [];
}

// Initialize timing system
wp_hook_profiler_init_timing();