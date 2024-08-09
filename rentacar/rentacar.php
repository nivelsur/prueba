<?php
/*
Plugin Name: Rentacar
Description: Plugin de gestión de alquiler de autos.
Version: 0.38
Author: Tomas Zuniga
Text Domain: rentacar
*/

if (!defined('ABSPATH')) {
    exit;
}

define('RENTACAR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RENTACAR_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once RENTACAR_PLUGIN_DIR . 'includes/rentacar-functions.php';
require_once RENTACAR_PLUGIN_DIR . 'includes/rentacar-admin-page.php';
require_once RENTACAR_PLUGIN_DIR . 'includes/rentacar-usuarios.php';
require_once RENTACAR_PLUGIN_DIR . 'includes/rentacar-vehiculos.php';
require_once RENTACAR_PLUGIN_DIR . 'includes/rentacar-ordenes.php';
require_once RENTACAR_PLUGIN_DIR . 'includes/rentacar-configuracion.php'; 
require_once RENTACAR_PLUGIN_DIR . 'includes/rentacar-tables.php'; 
require_once RENTACAR_PLUGIN_DIR . 'includes/rentacar-calendario.php'; // Incluir el archivo del calendario

// Agregar menú en el admin
function rentacar_admin_menu() {
    add_menu_page(
        __('Rentacar', 'rentacar'),
        __('Rentacar', 'rentacar'),
        'manage_options',
        'rentacar',
        'rentacar_admin_page',
        'dashicons-admin-site'
    );

    add_submenu_page(
        'rentacar',
        __('Usuarios', 'rentacar'),
        __('Usuarios', 'rentacar'),
        'manage_options',
        'rentacar-usuarios',
        'rentacar_usuarios_page'
    );

    add_submenu_page(
        'rentacar',
        __('Vehículos', 'rentacar'),
        __('Vehículos', 'rentacar'),
        'manage_options',
        'rentacar-vehiculos',
        'rentacar_vehiculos_page'
    );

    add_submenu_page(
        'rentacar',
        __('Órdenes', 'rentacar'),
        __('Órdenes', 'rentacar'),
        'manage_options',
        'rentacar-ordenes',
        'rentacar_ordenes_page'
    );

    /*add_submenu_page(
        'rentacar',
        __('Reportes', 'rentacar'),
        __('Reportes', 'rentacar'),
        'manage_options',
        'rentacar-reportes',
        'rentacar_reportes_page'
    );*/

    add_submenu_page(
        'rentacar',
        __('Configuración', 'rentacar'),
        __('Configuración', 'rentacar'),
        'manage_options',
        'rentacar-configuracion',
        'rentacar_configuracion_page'
    );

    add_submenu_page(
        'rentacar',
        __('Calendario', 'rentacar'),
        __('Calendario', 'rentacar'),
        'manage_options',
        'rentacar-calendario',
        'rentacar_calendario_page'
    );
}

add_action('admin_menu', 'rentacar_admin_menu');

// Encolar scripts y estilos
function rentacar_enqueue_scripts($hook) {
    if (strpos($hook, 'rentacar') === false) {
        return;
    }
    wp_enqueue_style('rentacar-style', RENTACAR_PLUGIN_URL . 'assets/style.css');
    wp_enqueue_script('rentacar-script', RENTACAR_PLUGIN_URL . 'js/script.js', array('jquery'), false, true);
    wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', [], null, true);
    wp_enqueue_media(); // Asegúrate de encolar la biblioteca wp.media
    
    // Encolar Bootstrap
    /*wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');*/ // se mudo a calendario y rentacar-admin-page por conflicto con eliminacion de ordenes 
    wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', ['jquery'], null, true);// nuevo 0.34
    // Incluir Font Awesome
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');

    // Encolar FullCalendar
    wp_enqueue_style('fullcalendar-style', 'https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css');// nuevo 0.34
    wp_enqueue_script('fullcalendar-script', 'https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js', ['jquery'], null, true);
    wp_enqueue_script('fullcalendar-bootstrap', 'https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/locales/es.js', ['fullcalendar-script'], null, true);
    // Encolar el script del calendario solo en la página del calendario
    if ($hook === 'rentacar_page_rentacar-calendario') {
        wp_enqueue_script('rentacar-calendario', RENTACAR_PLUGIN_URL . 'js/rentacar-calendario.js', [], false, true);
    }//hasta aqui lo nuevo
}
    add_action('admin_enqueue_scripts', 'rentacar_enqueue_scripts');

// Funciones de activación y desactivación
function rentacar_add_roles() {
    add_role('cliente', __('Cliente', 'rentacar'), [
        'read' => true,
        'edit_own_data' => true,
    ]);
}
register_activation_hook(__FILE__, 'rentacar_add_roles');

function rentacar_remove_roles() {
    remove_role('cliente');

    $role = get_role('administrator');
    $role->remove_cap('manage_rentacar');
}
register_deactivation_hook(__FILE__, 'rentacar_remove_roles');

// Crear tablas en la activación
register_activation_hook(__FILE__, 'rentacar_create_tables');

// Eliminar tablas en la desactivación (si decides hacerlo aquí en vez de uninstall.php)
// register_deactivation_hook(__FILE__, 'rentacar_delete_tables');
?>