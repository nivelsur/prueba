<?php
if (!defined('ABSPATH')) {
    exit;
}

function rentacar_vehiculos_page() {
    $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';

    switch ($action) {
        case 'new':
            $title = 'Añadir Nuevo Vehículo';
            $button_text = 'Crear Vehículo';
            require_once RENTACAR_PLUGIN_DIR . 'templates/vehiculos/vehiculos-new.php';
            break;
        case 'edit':
            $title = 'Editar Vehículo';
            $button_text = 'Actualizar';
            require_once RENTACAR_PLUGIN_DIR . 'templates/vehiculos/vehiculos-edit.php';
            break;
        default:
            require_once RENTACAR_PLUGIN_DIR . 'templates/vehiculos/vehiculos-list.php';
            break;
    }
}
?>