<?php

namespace PluginAuditExporter;

use JetBrains\PhpStorm\NoReturn;

class PluginAuditExporterAdmin
{
    private static ?self $instance = null;
    private const HEADERS = ['Name', 'Status', 'Current Version', 'New Version', 'Notes'];

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
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const dlBtn = document.getElementById('generate_plugin_audit');
                const formData = new FormData();
                formData.append('action', 'generate_plugin_audit');

                dlBtn.addEventListener('click', function () {
                    dlBtn.disabled = true;
                    dlBtn.textContent = 'Generating file...';

                    fetch('<?= esc_url(admin_url('admin-post.php'))?>', {
                        method: 'POST',
                        body: formData,
                    }).then(async (response) => {
                        // @TODO
                    }).catch(error => {
                        console.error('Error generating CSV:', error);
                        dlBtn.disabled = false;
                        dlBtn.textContent = 'Download Audit';
                    });
                })
            });
        </script>
        <div class="wrap">
            <h1>test</h1>
            <button id="generate_plugin_audit">Download Audit</button>
        </div>
        <?php
    }

    #[NoReturn] public static function generate_plugin_audit(): void
    {
        $plugins = get_plugins();
        if (empty($plugins)) {
            // @TODO
            die;
        }

        $csv_data = [];
//        $available_updates = get_plugin_updates();
//        foreach ($plugins as $plugin) {
//            $plugin_csv_data = [
//                'name' => $plugin['Name'],
//                'status' => is_plugin_active($plugin['Name']) ? 'Active' : 'Inactive',
//                'current_version' => $plugin['Version'],
//            ];
//
//            $update_key = array_filter(array_keys($available_updates), static fn($key) => str_contains($key, $plugin['TextDomain'] . '/'));
//            $update_data = null;
//            if (!empty($update_key) && count($update_key) === 1) {
//                $update_data = $available_updates[$update_key[0]];
//            }
//
//            $csv_data[] = array_merge($plugin_csv_data, self::get_update_information($plugin['Version'], $update_data));
//        }

        // Download the CSV
        $filename = 'plugin_audit.csv';
        $delimiter = ',';
        $f = fopen('php://output', 'wb');
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'";');
        fputcsv($f, self::HEADERS, $delimiter);
//        foreach($csv_data as $line){
//            fputcsv($f, array_values($line), $delimiter);
//        }


        echo $f;
        fclose($f);
        die();
    }

    private static function get_update_information(string $current_version, ?\stdClass $update_data): array
    {
        if (is_null($update_data)) {
            return  [
               'new_version' =>  null,
                'notes' => null,
            ];
        }

        $current_version_int = (int)(explode('.', $current_version)[0]);
        $new_version = $update_data->update->new_version;
        $new_version_int = (int)(explode('.', $new_version)[0]);

        return [
            'new_version' => $update_data->newVersion,
            'notes' => $new_version_int > $current_version_int ? 'Major version change' : null,
        ];
    }
}
