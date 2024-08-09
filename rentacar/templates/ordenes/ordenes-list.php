<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$ordenes_table = $wpdb->prefix . 'rentacar_ordenes';

// Definir la variable $search_user para evitar el warning
$search_user = '';

// Verificar si se ha solicitado la eliminación de una orden
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($id) {
        $orden = $wpdb->get_row($wpdb->prepare("SELECT * FROM $ordenes_table WHERE id = %d", $id));
        if ($orden) {
            ?>
            <div id="confirm-modal" style="display: block;">
                <div class="modal-overlay"></div>
                <div class="modal-content">
                    <h2><?php esc_html_e('Confirmar Eliminación', 'rentacar'); ?></h2>
                    <p><?php printf(esc_html__('Estás a punto de eliminar la orden con ID %d. ¿Estás seguro de que deseas continuar?', 'rentacar'), esc_html($orden->id)); ?></p>
                    <a href="?page=rentacar-ordenes&action=confirm_delete&id=<?php echo esc_attr($id); ?>" class="button button-primary"><?php esc_html_e('Sí, Eliminar', 'rentacar'); ?></a>
                    <a href="?page=rentacar-ordenes" class="button"><?php esc_html_e('No Eliminar', 'rentacar'); ?></a>
                </div>
            </div>
            <?php
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Orden no encontrada.', 'rentacar') . '</p></div>';
        }
    }
    exit;
}

// Confirmar eliminación
if (isset($_GET['action']) && $_GET['action'] === 'confirm_delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($id) {
        $deleted = $wpdb->delete($ordenes_table, ['id' => $id]);
        if ($deleted !== false) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Orden eliminada con éxito', 'rentacar') . '</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Hubo un problema al eliminar la orden.', 'rentacar') . '</p></div>';
        }
    }
}

// Verificar y sanitizar parámetros de búsqueda
$user_search = isset($_GET['user_search']) ? sanitize_text_field($_GET['user_search']) : '';
$fecha_entrada = isset($_GET['fecha_entrada']) ? sanitize_text_field($_GET['fecha_entrada']) : '';
$fecha_salida = isset($_GET['fecha_salida']) ? sanitize_text_field($_GET['fecha_salida']) : '';

// Obtener las órdenes filtradas
$ordenes = $wpdb->get_results($wpdb->prepare("
    SELECT o.*, u.usuario, v.nombre AS vehiculo
    FROM $ordenes_table o
    JOIN {$wpdb->prefix}rentacar_usuarios u ON o.usuario_id = u.id
    JOIN {$wpdb->prefix}rentacar_vehiculos v ON o.vehiculo_id = v.id
    WHERE 1=1
    AND (%s = '' OR u.usuario LIKE %s)
    AND (%s = '' OR o.fecha_entrada >= %s)
    AND (%s = '' OR o.fecha_salida <= %s)
    ORDER BY o.id DESC
", $user_search, '%' . $wpdb->esc_like($user_search) . '%', $fecha_entrada, $fecha_entrada, $fecha_salida, $fecha_salida));

?>

<div class="wrap-list">
    <h1><?php esc_html_e('Lista de Órdenes', 'rentacar'); ?></h1>

    <a href="?page=rentacar-ordenes&action=new" class="page-title-action">Nueva Orden</a>
    <hr class="wp-header-end">

    <form id="filtros-ordenes" method="get">
        <input type="hidden" name="page" value="rentacar-ordenes">
        <a>Buscar por Usuario</a><input type="text" name="user_search" placeholder="Buscar por Usuario" value="<?php echo esc_attr($search_user); ?>">
        <a>Fecha de Entrada</a><input type="date" name="fecha_entrada" placeholder="Fecha de Entrada" value="<?php echo esc_attr($fecha_entrada); ?>">
        <a>Fecha de Salida</a><input type="date" name="fecha_salida" placeholder="Fecha de Salida" value="<?php echo esc_attr($fecha_salida); ?>">
        <input type="submit" value="Filtrar" class="button">
    </form>

    <?php if (empty($ordenes)): ?>
        <p><?php esc_html_e('No hay órdenes registradas.', 'rentacar'); ?></p>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('ID', 'rentacar'); ?></th>
                    <th><?php esc_html_e('Usuario', 'rentacar'); ?></th>
                    <th><?php esc_html_e('Vehículo', 'rentacar'); ?></th>
                    <th><?php esc_html_e('Fecha de Entrada', 'rentacar'); ?></th>
                    <th><?php esc_html_e('Fecha de Salida', 'rentacar'); ?></th>
                    <th><?php esc_html_e('Precio Total', 'rentacar'); ?></th>
                    <th><?php esc_html_e('Estado', 'rentacar'); ?></th>
                    <th><?php esc_html_e('Acciones', 'rentacar'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ordenes as $orden): ?>
                    <tr>
                        <td data-title="ID"><span>ID: </span><?php echo esc_html($orden->id); ?></td>
                        <td data-title="Usuario"><span>Usuario: </span><?php echo esc_html($orden->usuario); ?></td>
                        <td data-title="Vehículo"><span>Vehículo: </span><?php echo esc_html($orden->vehiculo); ?></td>
                        <td data-title="Fecha de Entrada"><span>Fecha de Entrada: </span><?php echo esc_html(date('Y-m-d', strtotime($orden->fecha_entrada))); ?></td>
                        <td data-title="Fecha de Salida"><span>Fecha de Salida: </span><?php echo esc_html(date('Y-m-d', strtotime($orden->fecha_salida))); ?></td>
                        <td data-title="Precio Total"><span>Precio Total: </span><?php echo esc_html(number_format($orden->precio_total, 2)); ?></td>
                        <td data-title="Estado"><span>Estado: </span><?php echo esc_html($orden->estado); ?></td>
                        <td>
                            <a href="?page=rentacar-ordenes&action=edit&id=<?php echo esc_attr($orden->id); ?>" class="button"><?php esc_html_e('Editar', 'rentacar'); ?></a>
                            |
                            <a href="?page=rentacar-ordenes&action=delete&id=<?php echo esc_attr($orden->id); ?>" class="button delete-button" data-id="<?php echo esc_attr($orden->id); ?>"><?php esc_html_e('Eliminar', 'rentacar'); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Modal HTML -->
<div id="confirm-modal" style="display: none;">
    <div class="modal-overlay"></div>
    <div class="modal-content">
        <h2><?php esc_html_e('Confirmar Eliminación', 'rentacar'); ?></h2>
        <p id="delete-message"></p>
        <div class="modal-content-boton">
            <a id="confirm-delete" href="#" class="button button-primary"><?php esc_html_e('Sí, Eliminar', 'rentacar'); ?></a>
            <a id="cancel-delete" href="#" class="button"><?php esc_html_e('No Eliminar', 'rentacar'); ?></a>
        </div>
    </div>
</div>