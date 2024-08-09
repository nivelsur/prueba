<?php
if (!defined('ABSPATH')) {
    exit;
}

function rentacar_ordenes_page() {
    $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';

    switch ($action) {
        case 'new':
            $title = 'Crear Nueva Orden';
            $button_text = 'Crear Orden';
            require_once RENTACAR_PLUGIN_DIR . 'templates/ordenes/ordenes-new.php';
            break;
        case 'edit':
            $title = 'Editar Orden';
            $button_text = 'Guardar Cambios';
            require_once RENTACAR_PLUGIN_DIR . 'templates/ordenes/ordenes-edit.php';
            break;
        default:
            require_once RENTACAR_PLUGIN_DIR . 'templates/ordenes/ordenes-list.php';
            break;
    }
}
?>