<?php

namespace PluginAuditExporter;

class PluginAuditExporterAdmin
{
    private static ?self $instance = null;
    private const HEADERS = ['Name', 'Status', 'Current Version', 'New Version', 'Notes'];
    private const DELIMITER = ',';

    public static function get_instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        add_action('admin_post_generate_plugin_audit', [self::class, 'generate_plugin_audit']);
        add_action('admin_menu', function () {
            add_submenu_page(
                'tools.php',
                'Plugin Audit Exporter',
                'Plugin Audit Exporter',
                'manage_options',
                'plugin-audit-exporter',
                fn() => $this->plugin_audit_exporter_view(),
                99
            );
        });
    }

    protected function plugin_audit_exporter_view(): void
    {
        $url = esc_url(admin_url('admin-post.php?action=generate_plugin_audit'))
        ?>
        <div class="wrap">
            <h1>Plugin Audit Exporter</h1>
            <div>
                <p>Click the button below to automatically download a full plugin audit. Includes name, status, current version, new version, and notes.</p>
                <a class="button button-primary"
                   href="<?= $url ?>"
                   download>Download Audit</a>
            </div>
        </div>
        <?php
    }

    /**
     * Build the plugin audit and trigger immediate download.
     *
     * @return void
     */
    public static function generate_plugin_audit(): void
    {
        $plugins = get_plugins();
        if (empty($plugins)) {
            // @TODO
            exit;
        }

        // Init file for writing and download
        $url = get_bloginfo('url');
        $cleaned_url = str_replace(['https://', 'http://', '.com', '.org', '.gov', '.edu', '.test'], '', $url);
        $file_name = sprintf('%s_plugin-audit_%s.csv', $cleaned_url, gmdate('Y-m-d'));
        $filestream = fopen('php://output', 'wb');

        // Prep headers for browser and start building file
        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('X-Content-Type-Options: nosniff');
        fputcsv($filestream, self::HEADERS, self::DELIMITER);

        $available_updates = get_plugin_updates();
        foreach ($plugins as $plugin_file => $plugin) {
            $plugin_csv_data = [
                'name' => $plugin['Name'],
                'status' => is_plugin_active($plugin_file) ? 'Active' : 'Inactive',
                'current_version' => $plugin['Version'],
                'new_version' => null,
                'notes' => null,
            ];

            if (!array_key_exists($plugin_file, $available_updates)) {
                // No update data to examine- write what we have and continue to next plugin
                fputcsv($filestream, $plugin_csv_data, self::DELIMITER);
                continue;
            }


            $current_major_version_int = (int)(explode('.', $plugin['Version'])[0]);
            $new_version = $available_updates[$plugin_file]->update->new_version;
            $new_major_version_int = (int)(explode('.', $new_version)[0]);
            $plugin_csv_data['new_version'] = $new_version;

            if ($new_major_version_int > $current_major_version_int) {
                $plugin_csv_data['notes'] = 'Major version change';
            }

            fputcsv($filestream, $plugin_csv_data, self::DELIMITER);
        }

        fclose($filestream);
        exit;
    }
}
