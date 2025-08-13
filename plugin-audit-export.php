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

// Useful constants.
define( 'PLUGIN_AUDIT_EXPORT_VERSION', '0.1.0' );
define( 'PLUGIN_AUDIT_EXPORT_FILE', __FILE__ );
define( 'PLUGIN_AUDIT_EXPORT_DIR', plugin_dir_path( __FILE__ ) );

require_once(__DIR__ . '/classes/PluginAuditExporterAdmin.php');
add_action('plugins_loaded', function() {
    if (is_admin()) {
        PluginAuditExporterAdmin::get_instance();
    }
});
