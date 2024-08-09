<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$usuarios_table = $wpdb->prefix . 'rentacar_usuarios';

// Verificar si se ha solicitado la eliminación de un usuario
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($id) {
        $usuario = $wpdb->get_row($wpdb->prepare("SELECT * FROM $usuarios_table WHERE id = %d", $id));
        if ($usuario) {
            // Mostrar el modal de confirmación
            ?>
            <div id="confirm-modal" style="display: block;">
                <div class="modal-overlay"></div>
                <div class="modal-content">
                    <h2><?php esc_html_e('Confirmar Eliminación', 'rentacar'); ?></h2>
                    <p id="delete-message"><?php printf(esc_html__('Estás a punto de eliminar al usuario %s. ¿Estás seguro de que deseas continuar?', 'rentacar'), esc_html($usuario->nombre)); ?></p>
                    <a href="?page=rentacar-usuarios&action=confirm_delete&id=<?php echo esc_attr($id); ?>" id="confirm-delete" class="button button-primary"><?php esc_html_e('Sí, Eliminar', 'rentacar'); ?></a>
                    <a href="?page=rentacar-usuarios" id="cancel-delete" class="button"><?php esc_html_e('No Eliminar', 'rentacar'); ?></a>
                </div>
            </div>
            <?php
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Usuario no encontrado.', 'rentacar') . '</p></div>';
        }
    }
    exit;
}

// Confirmar eliminación
if (isset($_GET['action']) && $_GET['action'] === 'confirm_delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($id) {
        $deleted = $wpdb->delete($usuarios_table, ['id' => $id]);
        if ($deleted !== false) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Usuario eliminado con éxito', 'rentacar') . '</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Hubo un problema al eliminar el usuario.', 'rentacar') . '</p></div>';
        }
    }
}

// Obtener los usuarios
$usuarios = $wpdb->get_results("SELECT * FROM $usuarios_table");

?>

<div class="wrap-list">
    <h1><?php esc_html_e('Lista de Usuarios', 'rentacar'); ?></h1>

    <a href="?page=rentacar-usuarios&action=new" class="button button-primary"><?php esc_html_e('Nuevo Usuario', 'rentacar'); ?></a>

    <?php if (empty($usuarios)): ?>
        <p><?php esc_html_e('No hay usuarios registrados.', 'rentacar'); ?></p>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('ID', 'rentacar'); ?></th>
                    <th><?php esc_html_e('Usuario', 'rentacar'); ?></th>
                    <th><?php esc_html_e('Nombre', 'rentacar'); ?></th>
                    <th><?php esc_html_e('País', 'rentacar'); ?></th>
                    <th><?php esc_html_e('Teléfono', 'rentacar'); ?></th>
                    <th><?php esc_html_e('Email', 'rentacar'); ?></th>
                    <th><?php esc_html_e('Status', 'rentacar'); ?></th>
                    <th><?php esc_html_e('Acciones', 'rentacar'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?php echo esc_html($usuario->id); ?></td>
                        <td><?php echo esc_html($usuario->usuario); ?></td>
                        <td><?php echo esc_html($usuario->nombre); ?></td>
                        <td><?php echo esc_html($usuario->pais); ?></td>
                        <td><?php echo esc_html($usuario->telefono); ?></td>
                        <td><?php echo esc_html($usuario->email); ?></td>
                        <td><?php echo esc_html($usuario->status); ?></td>
                        <td>
                            <a href="?page=rentacar-usuarios&action=edit&id=<?php echo esc_attr($usuario->id); ?>" class="button"><?php esc_html_e('Editar', 'rentacar'); ?></a>
                            |
                            <a href="?page=rentacar-usuarios&action=delete&id=<?php echo esc_attr($usuario->id); ?>" class="button" onclick="return openDeleteModal('<?php echo esc_html($usuario->nombre); ?>', '<?php echo esc_url('?page=rentacar-usuarios&action=confirm_delete&id=' . esc_attr($usuario->id)); ?>')"><?php esc_html_e('Eliminar', 'rentacar'); ?></a>
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