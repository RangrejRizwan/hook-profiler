<?php
defined('ABSPATH') || exit;
?>

<div id="wp-hook-profiler-panel" class="wp-hook-profiler-panel" style="display: none;">
    <div class="wp-hook-profiler-header">
        <h3>
            <span class="dashicons dashicons-performance"></span>
            WordPress Hook Profiler
        </h3>
        <div class="wp-hook-profiler-controls">
            <button id="wp-hook-profiler-close" class="button button-small">×</button>
        </div>
    </div>
    
    <div class="wp-hook-profiler-loading">
        <span class="spinner is-active"></span>
        Loading profiling data...
    </div>
    
    <div class="wp-hook-profiler-content" style="display: none;">
        <div class="wp-hook-profiler-tabs">
            <button class="wp-hook-profiler-tab active" data-tab="plugins">Plugins Overview</button>
            <button class="wp-hook-profiler-tab" data-tab="callbacks">Slowest Callbacks</button>
            <button class="wp-hook-profiler-tab" data-tab="hooks">Hook Details</button>
            <button class="wp-hook-profiler-tab" data-tab="plugin-loading">Plugin Loading</button>
        </div>
        
        <div class="wp-hook-profiler-summary">
            <div class="wp-hook-profiler-summary-item">
                <strong>Total Hooks:</strong>
                <span id="wp-hook-profiler-total-hooks">-</span>
            </div>
            <div class="wp-hook-profiler-summary-item">
                <strong>Total Time:</strong>
                <span id="wp-hook-profiler-total-time">-</span>ms
            </div>
            <div class="wp-hook-profiler-summary-item">
                <strong>Profiling Overhead:</strong>
                <span id="wp-hook-profiler-overhead">-</span>ms
            </div>
        </div>
        
        <div id="wp-hook-profiler-tab-plugins" class="wp-hook-profiler-tab-content">
            <div class="wp-hook-profiler-search">
                <input type="text" id="wp-hook-profiler-search-plugins" placeholder="Search plugins..." />
            </div>
            <div class="wp-hook-profiler-table-wrapper">
                <table class="wp-hook-profiler-table">
                    <thead>
                        <tr>
                            <th class="sortable" data-sort="plugin_name">Plugin</th>
                            <th class="sortable numeric" data-sort="total_time">Total Time (ms)</th>
                            <th class="sortable numeric" data-sort="hook_count">Hooks Used</th>
                            <th class="sortable numeric" data-sort="callback_count">Callbacks</th>
                            <th class="sortable numeric" data-sort="avg_time">Avg Time (ms)</th>
                        </tr>
                    </thead>
                    <tbody id="wp-hook-profiler-plugins-table">
                        <tr><td colspan="5">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div id="wp-hook-profiler-tab-callbacks" class="wp-hook-profiler-tab-content" style="display: none;">
            <div class="wp-hook-profiler-search">
                <input type="text" id="wp-hook-profiler-search-callbacks" placeholder="Search callbacks..." />
            </div>
            <div class="wp-hook-profiler-table-wrapper">
                <table class="wp-hook-profiler-table">
                    <thead>
                        <tr>
                            <th class="sortable" data-sort="callback">Callback</th>
                            <th class="sortable" data-sort="hook">Hook</th>
                            <th class="sortable" data-sort="plugin">Plugin</th>
                            <th class="sortable numeric" data-sort="execution_time">Time (ms)</th>
                            <th class="sortable numeric" data-sort="priority">Call Count</th>
                        </tr>
                    </thead>
                    <tbody id="wp-hook-profiler-callbacks-table">
                        <tr><td colspan="5">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div id="wp-hook-profiler-tab-hooks" class="wp-hook-profiler-tab-content" style="display: none;">
            <div class="wp-hook-profiler-search">
                <input type="text" id="wp-hook-profiler-search-hooks" placeholder="Search hooks..." />
                <select id="wp-hook-profiler-filter-plugin">
                    <option value="">All Plugins</option>
                </select>
            </div>
            <div id="wp-hook-profiler-hooks-list">
                Loading hook details...
            </div>
        </div>
        
        <div id="wp-hook-profiler-tab-plugin-loading" class="wp-hook-profiler-tab-content" style="display: none;">
            <div class="wp-hook-profiler-search">
                <input type="text" id="wp-hook-profiler-search-plugin-loading" placeholder="Search plugin files..." />
                <select id="wp-hook-profiler-filter-loading-type">
                    <option value="">All Types</option>
                    <option value="sunrise">Sunrise.php</option>
                    <option value="mu_plugin">MU Plugins</option>
                    <option value="network_plugin">Network Plugins</option>
                    <option value="plugin">Regular Plugins</option>
                    <option value="core">Core</option>
                </select>
            </div>
            <div class="wp-hook-profiler-table-wrapper">
                <table class="wp-hook-profiler-table">
                    <thead>
                        <tr>
                            <th class="sortable" data-sort="file">File</th>
                            <th class="sortable" data-sort="type">Type</th>
                            <th class="sortable numeric" data-sort="duration">Load Time (ms)</th>
                            <th class="sortable numeric" data-sort="start_time">Start Time</th>
                            <th class="sortable numeric" data-sort="end_time">End Time</th>
                        </tr>
                    </thead>
                    <tbody id="wp-hook-profiler-plugin-loading-table">
                        <tr><td colspan="5">Loading plugin loading data...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="wp-hook-profiler-loading-summary">
                <h4>Plugin Loading Summary</h4>
                <div id="wp-hook-profiler-loading-chart">
                    <div class="wp-hook-profiler-loading-stats">
                        <div class="wp-hook-profiler-stat">
                            <strong>Sunrise.php:</strong>
                            <span id="wp-hook-profiler-sunrise-time">-</span>ms
                        </div>
                        <div class="wp-hook-profiler-stat">
                            <strong>MU Plugins:</strong>
                            <span id="wp-hook-profiler-mu-plugins-time">-</span>ms
                        </div>
                        <div class="wp-hook-profiler-stat">
                            <strong>Network Plugins:</strong>
                            <span id="wp-hook-profiler-network-plugins-time">-</span>ms
                        </div>
                        <div class="wp-hook-profiler-stat">
                            <strong>Regular Plugins:</strong>
                            <span id="wp-hook-profiler-plugins-time">-</span>ms
                        </div>
                        <div class="wp-hook-profiler-stat">
                            <strong>Total Loading:</strong>
                            <span id="wp-hook-profiler-total-loading-time">-</span>ms
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="wp-hook-profiler-error" id="wp-hook-profiler-error" style="display: none;">
            <strong>Error:</strong> <span id="wp-hook-profiler-error-message"></span>
        </div>
    </div>
</div>

<div id="wp-hook-profiler-overlay" class="wp-hook-profiler-overlay" style="display: none;"></div>