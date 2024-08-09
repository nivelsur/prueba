<?php
if (!defined('ABSPATH')) {
    exit;
}

function rentacar_usuarios_page() {
    $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';

    switch ($action) {
        case 'new':
            $title = 'Añadir Nuevo Usuario';
            $button_text = 'Crear Usuario';
            require_once RENTACAR_PLUGIN_DIR . 'templates/usuarios/usuarios-new.php';
            break;
        case 'edit':
            $title = 'Editar Usuario';
            $button_text = 'Actualizar';
            require_once RENTACAR_PLUGIN_DIR . 'templates/usuarios/usuarios-edit.php';
            break;
        default:
            require_once RENTACAR_PLUGIN_DIR . 'templates/usuarios/usuarios-list.php';
            break;
    }
}
?>