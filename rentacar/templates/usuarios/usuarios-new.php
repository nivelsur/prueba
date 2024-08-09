<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$usuarios_table = $wpdb->prefix . 'rentacar_usuarios';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar y sanitizar datos
    $usuario = sanitize_text_field($_POST['usuario']);
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
    if (empty($usuario) || empty($nombre) || empty($apellido) || empty($pais) || empty($ciudad) || empty($codigo_postal) || empty($telefono) || empty($email) || empty($password) || empty($status)) {
        $errors[] = 'Todos los campos son obligatorios.';
    }

    // Verificar si el usuario ya existe
    $existing_user = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $usuarios_table WHERE usuario = %s", $usuario));
    if ($existing_user > 0) {
        $errors[] = 'El usuario ya existe. Por favor elija otro nombre de usuario.';
    }

    // Verificar si el email ya está registrado
    $existing_email = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $usuarios_table WHERE email = %s", $email));
    if ($existing_email > 0) {
        $errors[] = 'El correo electrónico ya está registrado. Por favor, utilice otro.';
    }

    // Validar contraseña
    if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
        $errors[] = 'La contraseña debe tener al menos 8 caracteres y contener al menos una mayúscula, una minúscula, un número y un carácter especial.';
    }

    if (empty($errors)) {
        $hashed_password = wp_hash_password($password);

        $inserted = $wpdb->insert($usuarios_table, [
            'usuario' => $usuario,
            'nombre' => $nombre,
            'apellido' => $apellido,
            'pais' => $pais,
            'ciudad' => $ciudad,
            'codigo_postal' => $codigo_postal,
            'telefono' => $telefono,
            'email' => $email,
            'password' => $hashed_password,
            'status' => $status,
        ]);

        if ($inserted) {
            $success = 'Usuario añadido con éxito';
            echo '<script>window.location.href = "?page=rentacar-usuarios";</script>';
            exit;
        } else {
            $errors[] = 'Hubo un problema al guardar el usuario en la base de datos.';
            echo '<pre>';
            echo 'Última consulta: ' . esc_html($wpdb->last_query) . "\n";
            echo 'Error: ' . esc_html($wpdb->last_error);
            echo '</pre>';
        }
    }
}
?>
<div class="wrap-usuarios-new">
    <h1><?php esc_html_e('Añadir Nuevo Usuario', 'rentacar'); ?></h1>
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
                <td><input type="text" name="usuario" id="usuario" class="regular-text" value="<?php echo isset($usuario) ? esc_attr($usuario) : ''; ?>" required /></td>
            </tr>
            <tr>
                <th><label for="nombre"><?php esc_html_e('Nombre', 'rentacar'); ?></label></th>
                <td><input type="text" name="nombre" id="nombre" class="regular-text" value="<?php echo isset($nombre) ? esc_attr($nombre) : ''; ?>" required /></td>
            </tr>
            <tr>
                <th><label for="apellido"><?php esc_html_e('Apellido', 'rentacar'); ?></label></th>
                <td><input type="text" name="apellido" id="apellido" class="regular-text" value="<?php echo isset($apellido) ? esc_attr($apellido) : ''; ?>" required /></td>
            </tr>
            <tr>
                <th><label for="pais"><?php esc_html_e('País', 'rentacar'); ?></label></th>
                <td><input type="text" name="pais" id="pais" class="regular-text" value="<?php echo isset($pais) ? esc_attr($pais) : ''; ?>" required /></td>
            </tr>
            <tr>
                <th><label for="ciudad"><?php esc_html_e('Ciudad', 'rentacar'); ?></label></th>
                <td><input type="text" name="ciudad" id="ciudad" class="regular-text" value="<?php echo isset($ciudad) ? esc_attr($ciudad) : ''; ?>" required /></td>
            </tr>
            <tr>
                <th><label for="codigo_postal"><?php esc_html_e('Código Postal', 'rentacar'); ?></label></th>
                <td><input type="text" name="codigo_postal" id="codigo_postal" class="regular-text" value="<?php echo isset($codigo_postal) ? esc_attr($codigo_postal) : ''; ?>" required /></td>
            </tr>
            <tr>
                <th><label for="telefono"><?php esc_html_e('Teléfono', 'rentacar'); ?></label></th>
                <td><input type="tel" name="telefono" id="telefono" class="regular-text" value="<?php echo isset($telefono) ? esc_attr($telefono) : ''; ?>" required /></td>
            </tr>
            <tr>
                <th><label for="email"><?php esc_html_e('Correo Electrónico', 'rentacar'); ?></label></th>
                <td><input type="email" name="email" id="email" class="regular-text" value="<?php echo isset($email) ? esc_attr($email) : ''; ?>" required /></td>
            </tr>
            <tr>
                <th><label for="password"><?php esc_html_e('Contraseña', 'rentacar'); ?></label></th>
                <td>
                    <input type="password" name="password" id="password" class="regular-text" value="<?php echo isset($password) ? esc_attr($password) : ''; ?>" required />
                    <?php if (current_user_can('administrator')): ?>
                        <button type="button" id="toggle-password" class="button"><?php esc_html_e('Ver contraseña', 'rentacar'); ?></button>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th><label for="status"><?php esc_html_e('Status', 'rentacar'); ?></label></th>
                <td>
                    <select name="status" id="status" class="regular-text" required>
                        <option value="Pendiente" <?php echo (isset($status) && $status == 'Pendiente') ? 'selected' : ''; ?>><?php esc_html_e('Pendiente', 'rentacar'); ?></option>
                        <option value="Aprobado" <?php echo (isset($status) && $status == 'Aprobado') ? 'selected' : ''; ?>><?php esc_html_e('Aprobado', 'rentacar'); ?></option>
                    </select>
                </td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Crear Usuario', 'rentacar'); ?>">
        </p>
    </form>
</div>