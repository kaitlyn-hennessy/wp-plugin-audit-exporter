<?php

/**
 * Plugin Name:       Plugin Audit Exporter
 * Description:       Export formatted plugin audits.
 * Version:           0.1.0
 * Requires PHP:      >=7.2
 * Author:            CodingIT
 * Author URI:        https://codingit.dev
 */

namespace PluginAuditExporter;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once(__DIR__ . '/classes/PluginAuditExporterAdmin.php');
add_action('plugins_loaded', function() {
    if (is_admin()) {
        PluginAuditExporterAdmin::get_instance();
    }
});
