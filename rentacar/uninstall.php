<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'includes/rentacar-tables.php';

// Llama a la función para eliminar las tablas
rentacar_delete_tables();
?>
