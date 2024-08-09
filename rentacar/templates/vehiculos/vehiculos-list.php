<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$vehiculos_table = $wpdb->prefix . 'rentacar_vehiculos';

// Verificar si se ha solicitado la eliminación de un vehículo
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($id) {
        $vehiculo = $wpdb->get_row($wpdb->prepare("SELECT * FROM $vehiculos_table WHERE id = %d", $id));
        if ($vehiculo) {
            ?>
            <div id="confirm-modal" style="display: block;">
                <div class="modal-overlay"></div>
                <div class="modal-content">
                    <h2><?php esc_html_e('Confirmar Eliminación', 'rentacar'); ?></h2>
                    <p><?php printf(esc_html__('Estás a punto de eliminar el vehículo %s. ¿Estás seguro de que deseas continuar?', 'rentacar'), esc_html($vehiculo->nombre)); ?></p>
                    <a href="?page=rentacar-vehiculos&action=confirm_delete&id=<?php echo esc_attr($id); ?>" class="button button-primary"><?php esc_html_e('Sí, Eliminar', 'rentacar'); ?></a>
                    <a href="?page=rentacar-vehiculos" class="button"><?php esc_html_e('No Eliminar', 'rentacar'); ?></a>
                </div>
            </div>
            <?php
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Vehículo no encontrado.', 'rentacar') . '</p></div>';
        }
    }
    exit;
}

// Confirmar eliminación
if (isset($_GET['action']) && $_GET['action'] === 'confirm_delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($id) {
        $deleted = $wpdb->delete($vehiculos_table, ['id' => $id]);
        if ($deleted !== false) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Vehículo eliminado con éxito', 'rentacar') . '</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Hubo un problema al eliminar el vehículo.', 'rentacar') . '</p></div>';
        }
    }
}

// Obtener los vehículos
$vehiculos = $wpdb->get_results("SELECT * FROM $vehiculos_table");

?>

<div class="wrap-list">
    <h1><?php esc_html_e('Lista de Vehículos', 'rentacar'); ?></h1>

    <a href="?page=rentacar-vehiculos&action=new" class="button button-primary"><?php esc_html_e('Nuevo Vehículo', 'rentacar'); ?></a>

    <?php if (empty($vehiculos)): ?>
        <p><?php esc_html_e('No hay vehículos registrados.', 'rentacar'); ?></p>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('ID', 'rentacar'); ?></th>
                    <th><?php esc_html_e('Vin/Patente', 'rentacar'); ?></th>
                    <th><?php esc_html_e('Nombre del Vehículo', 'rentacar'); ?></th>
                    <th><?php esc_html_e('Tipo de Combustible', 'rentacar'); ?></th>
                    <th><?php esc_html_e('Transmisión', 'rentacar'); ?></th>
                    <th><?php esc_html_e('Tipo de Vehículo', 'rentacar'); ?></th>
                    <th><?php esc_html_e('Precio por Día', 'rentacar'); ?></th>
                    <th><?php esc_html_e('Acciones', 'rentacar'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vehiculos as $vehiculo): ?>
                    <tr>
                        <td><?php echo esc_html($vehiculo->id); ?></td>
                        <td><?php echo esc_html($vehiculo->vin_patente); ?></td>
                        <td><?php echo esc_html($vehiculo->nombre); ?></td>
                        <td><?php echo esc_html($vehiculo->tipo_combustible); ?></td>
                        <td><?php echo esc_html($vehiculo->tipo_transmision); ?></td>
                        <td><?php echo esc_html($vehiculo->tipo_vehiculo); ?></td>
                        <td><?php echo esc_html($vehiculo->precio_por_dia); ?></td>
                        <td>
                            <a href="?page=rentacar-vehiculos&action=edit&id=<?php echo esc_attr($vehiculo->id); ?>" class="button"><?php esc_html_e('Editar', 'rentacar'); ?></a>
                            |
                            <a href="?page=rentacar-vehiculos&action=delete&id=<?php echo esc_attr($vehiculo->id); ?>" class="button" onclick="return openDeleteModal('<?php echo esc_js($vehiculo->nombre); ?>', '<?php echo esc_url(admin_url('admin.php?page=rentacar-vehiculos&action=confirm_delete&id=' . $vehiculo->id)); ?>');"><?php esc_html_e('Eliminar', 'rentacar'); ?></a>
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