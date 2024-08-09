<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'rentacar_usuarios';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$usuario = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));

if (!$usuario) {
    echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Usuario no encontrado', 'rentacar') . '</p></div>';
    return;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar y sanitizar datos
    $nombre = sanitize_text_field($_POST['nombre']);
    $apellido = sanitize_text_field($_POST['apellido']);
    $pais = sanitize_text_field($_POST['pais']);
    $ciudad = sanitize_text_field($_POST['ciudad']);
    $codigo_postal = sanitize_text_field($_POST['codigo_postal']);
    $telefono = sanitize_text_field($_POST['telefono']);
    $email = sanitize_email($_POST['email']);
    $password = sanitize_text_field($_POST['password']);
    $status = sanitize_text_field($_POST['status']);

    // Validar campos vacíos
    if (empty($nombre) || empty($apellido) || empty($pais) || empty($ciudad) || empty($codigo_postal) || empty($telefono) || empty($email) || empty($status)) {
        $errors[] = 'Todos los campos son obligatorios.';
    }

    // Validar contraseña si se proporciona
    if (!empty($password) && !preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
        $errors[] = 'La contraseña debe tener al menos 8 caracteres y contener al menos una mayúscula, una minúscula, un número y un carácter especial.';
    }

    if (empty($errors)) {
        $data = [
            'nombre' => $nombre,
            'apellido' => $apellido,
            'pais' => $pais,
            'ciudad' => $ciudad,
            'codigo_postal' => $codigo_postal,
            'telefono' => $telefono,
            'email' => $email,
            'status' => $status,
        ];

        if (!empty($password)) {
            $data['password'] = wp_hash_password($password);
        }

        $wpdb->update($table_name, $data, ['id' => $id]);

        $success = 'Usuario actualizado con éxito';
        echo '<script>window.location.href = "?page=rentacar-usuarios";</script>';
    }
}
?>
<div class="wrap">
    <h1><?php esc_html_e('Editar Usuario', 'rentacar'); ?></h1>
    <?php if (!empty($errors)): ?>
        <div class="notice notice-error is-dismissible">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo esc_html($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html($success); ?></p>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <table class="form-table">
            <tr>
                <th><label for="usuario"><?php esc_html_e('Usuario', 'rentacar'); ?></label></th>
                <td><input type="text" name="usuario" id="usuario" class="regular-text" value="<?php echo esc_attr($usuario->usuario); ?>" readonly /></td>
            </tr>
            <tr>
                <th><label for="nombre"><?php esc_html_e('Nombre', 'rentacar'); ?></label></th>
                <td><input type="text" name="nombre" id="nombre" class="regular-text" value="<?php echo esc_attr($usuario->nombre); ?>" required /></td>
            </tr>
            <tr>
                <th><label for="apellido"><?php esc_html_e('Apellido', 'rentacar'); ?></label></th>
                <td><input type="text" name="apellido" id="apellido" class="regular-text" value="<?php echo esc_attr($usuario->apellido); ?>" required /></td>
            </tr>
            <tr>
                <th><label for="pais"><?php esc_html_e('País', 'rentacar'); ?></label></th>
                <td><input type="text" name="pais" id="pais" class="regular-text" value="<?php echo esc_attr($usuario->pais); ?>" required /></td>
            </tr>
            <tr>
                <th><label for="ciudad"><?php esc_html_e('Ciudad', 'rentacar'); ?></label></th>
                <td><input type="text" name="ciudad" id="ciudad" class="regular-text" value="<?php echo esc_attr($usuario->ciudad); ?>" required /></td>
            </tr>
            <tr>
                <th><label for="codigo_postal"><?php esc_html_e('Código Postal', 'rentacar'); ?></label></th>
                <td><input type="text" name="codigo_postal" id="codigo_postal" class="regular-text" value="<?php echo esc_attr($usuario->codigo_postal); ?>" required /></td>
            </tr>
            <tr>
                <th><label for="telefono"><?php esc_html_e('Teléfono', 'rentacar'); ?></label></th>
                <td><input type="tel" name="telefono" id="telefono" class="regular-text" value="<?php echo esc_attr($usuario->telefono); ?>" required /></td>
            </tr>
            <tr>
                <th><label for="email"><?php esc_html_e('Correo Electrónico', 'rentacar'); ?></label></th>
                <td><input type="email" name="email" id="email" class="regular-text" value="<?php echo esc_attr($usuario->email); ?>" required /></td>
            </tr>
            <tr>
                <th><label for="password"><?php esc_html_e('Contraseña', 'rentacar'); ?></label></th>
                <td>
                    <input type="password" name="password" id="password" class="regular-text" value="" />
                    <?php if (current_user_can('administrator')): ?>
                        <button type="button" id="toggle-password" class="button"><?php esc_html_e('Ver contraseña', 'rentacar'); ?></button>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th><label for="status"><?php esc_html_e('Status', 'rentacar'); ?></label></th>
                <td>
                    <select name="status" id="status" class="regular-text" required>
                        <option value="Pendiente" <?php echo ($usuario->status == 'Pendiente') ? 'selected' : ''; ?>><?php esc_html_e('Pendiente', 'rentacar'); ?></option>
                        <option value="Aprobado" <?php echo ($usuario->status == 'Aprobado') ? 'selected' : ''; ?>><?php esc_html_e('Aprobado', 'rentacar'); ?></option>
                    </select>
                </td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Actualizar', 'rentacar'); ?>">
        </p>
    </form>
</div>

<script>
document.getElementById('toggle-password').addEventListener('click', function() {
    var passwordField = document.getElementById('password');
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        this.textContent = '<?php esc_html_e('Ocultar contraseña', 'rentacar'); ?>';
    } else {
        passwordField.type = 'password';
        this.textContent = '<?php esc_html_e('Ver contraseña', 'rentacar'); ?>';
    }
});
</script>