<?php
if (!defined('ABSPATH')) {
    exit;
}

// Handles actions
function rentacar_handle_actions() {
    global $wpdb;

    $usuarios_table = $wpdb->prefix . 'rentacar_usuarios';
    $vehiculos_table = $wpdb->prefix . 'rentacar_vehiculos';
    $ordenes_table = $wpdb->prefix . 'rentacar_ordenes';

    if (isset($_GET['action']) && isset($_GET['id'])) {
        $action = sanitize_text_field($_GET['action']);
        $id = intval($_GET['id']);

        switch ($action) {
            case 'delete_user':
                $wpdb->delete($usuarios_table, ['id' => $id]);
                break;
            case 'edit_user':
                // Handle user editing
                break;
            case 'delete_vehicle':
                $wpdb->delete($vehiculos_table, ['id' => $id]);
                break;
            case 'edit_vehicle':
                // Handle vehicle editing
                break;
            case 'delete_order':
                $wpdb->delete($ordenes_table, ['id' => $id]);
                break;
            case 'edit_order':
                // Handle order editing
                break;
        }
    }
}
add_action('admin_init', 'rentacar_handle_actions');

/* Codigo de prueba-------------------------------------------------------------------*/

add_action('wp_ajax_get_vehicle_price', 'rentacar_get_vehicle_price');
add_action('wp_ajax_nopriv_get_vehicle_price', 'rentacar_get_vehicle_price');

function rentacar_get_vehicle_price() {
    global $wpdb;
    $vehiculo_id = isset($_POST['vehiculo_id']) ? intval($_POST['vehiculo_id']) : 0;

    if ($vehiculo_id) {
        $table_name = $wpdb->prefix . 'rentacar_vehiculos';
        $price = $wpdb->get_var($wpdb->prepare("SELECT precio_por_dia FROM $table_name WHERE id = %d", $vehiculo_id));

        if ($price !== null) {
            wp_send_json_success(array('precio_por_dia' => $price));
        } else {
            wp_send_json_error('Precio no encontrado');
        }
    } else {
        wp_send_json_error('ID de vehículo inválido');
    }

    wp_die();
}

// Calendario Crear un Endpoint para Obtener las Órdenes 0.32
function rentacar_get_orders() {
    global $wpdb;

    $ordenes_table = $wpdb->prefix . 'rentacar_ordenes';
    $vehiculos_table = $wpdb->prefix . 'rentacar_vehiculos';

    $results = $wpdb->get_results("
        SELECT o.id, o.fecha_entrada, o.fecha_salida, v.marca, v.modelo 
        FROM $ordenes_table o
        JOIN $vehiculos_table v ON o.vehiculo_id = v.id
    ");

    $events = [];
    foreach ($results as $row) {
        $events[] = [
            'title' => $row->marca . ' ' . $row->modelo,
            'start' => $row->fecha_entrada,
            'end' => $row->fecha_salida,
            'extendedProps' => [
                'orderId' => $row->id,
                'vehicle' => $row->marca . ' ' . $row->modelo
            ]
        ];
    }

    wp_send_json_success(['orders' => $events]);
}

add_action('wp_ajax_get_orders', 'rentacar_get_orders');

// Añadir el hook de acción para obtener ganancias mensuales
add_action('wp_ajax_obtener_ganancias_mensuales', 'obtener_ganancias_mensuales_ajax');

function obtener_ganancias_mensuales_ajax() {
    global $wpdb;

    // Consulta SQL para obtener las ganancias mensuales
    $resultados = $wpdb->get_results("
        SELECT
            DATE_FORMAT(fecha_entrada, '%Y-%m') AS mes_anio,
            SUM(precio_total) AS ganancia_total
        FROM
            {$wpdb->prefix}rentacar_ordenes
        GROUP BY
            DATE_FORMAT(fecha_entrada, '%Y-%m');
    ");

    // Convertir los resultados a un formato JSON y enviar la respuesta
    error_log(print_r($resultados, true)); // Añadir esta línea para depuración
    wp_send_json($resultados);
}

// Shortcode para el formulario de registro de usuarios
function rentacar_user_registration_form() {
    ob_start();

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
        $status = 'Pendiente';  // Todos los nuevos registros desde la web estarán en estado pendiente

        // Validar campos vacíos
        if (empty($usuario) || empty($nombre) || empty($apellido) || empty($pais) || empty($ciudad) || empty($codigo_postal) || empty($telefono) || empty($email) || empty($password)) {
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
                // Mostrar mensaje de éxito
                $success = 'Registro completado con éxito. Su cuenta está pendiente de aprobación.';

                // Redireccionar después de un registro exitoso
                echo '<script>window.location.href = "' . esc_url(home_url('/registro-completado')) . '";</script>';
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
        <h1><?php esc_html_e('Registrarse', 'rentacar'); ?></h1>
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
            <div class="form-group">
                <label for="usuario"><?php esc_html_e('Usuario', 'rentacar'); ?></label>
                <input type="text" name="usuario" id="usuario" value="<?php echo isset($usuario) ? esc_attr($usuario) : ''; ?>" required />
            </div>
            <div class="form-group">
                <label for="nombre"><?php esc_html_e('Nombre', 'rentacar'); ?></label>
                <input type="text" name="nombre" id="nombre" value="<?php echo isset($nombre) ? esc_attr($nombre) : ''; ?>" required />
            </div>
            <div class="form-group">
                <label for="apellido"><?php esc_html_e('Apellido', 'rentacar'); ?></label>
                <input type="text" name="apellido" id="apellido" value="<?php echo isset($apellido) ? esc_attr($apellido) : ''; ?>" required />
            </div>
            <div class="form-group">
                <label for="pais"><?php esc_html_e('País', 'rentacar'); ?></label>
                <input type="text" name="pais" id="pais" value="<?php echo isset($pais) ? esc_attr($pais) : ''; ?>" required />
            </div>
            <div class="form-group">
                <label for="ciudad"><?php esc_html_e('Ciudad', 'rentacar'); ?></label>
                <input type="text" name="ciudad" id="ciudad" value="<?php echo isset($ciudad) ? esc_attr($ciudad) : ''; ?>" required />
            </div>
            <div class="form-group">
                <label for="codigo_postal"><?php esc_html_e('Código Postal', 'rentacar'); ?></label>
                <input type="text" name="codigo_postal" id="codigo_postal" value="<?php echo isset($codigo_postal) ? esc_attr($codigo_postal) : ''; ?>" required />
            </div>
            <div class="form-group">
                <label for="telefono"><?php esc_html_e('Teléfono', 'rentacar'); ?></label>
                <input type="tel" name="telefono" id="telefono" value="<?php echo isset($telefono) ? esc_attr($telefono) : ''; ?>" required />
            </div>
            <div class="form-group">
                <label for="email"><?php esc_html_e('Correo Electrónico', 'rentacar'); ?></label>
                <input type="email" name="email" id="email" value="<?php echo isset($email) ? esc_attr($email) : ''; ?>" required />
            </div>
            <div class="form-group">
                <label for="password"><?php esc_html_e('Contraseña', 'rentacar'); ?></label>
                <input type="password" name="password" id="password" value="<?php echo isset($password) ? esc_attr($password) : ''; ?>" required />
            </div>
            <div class="form-group">
                <button type="submit" name="submit" id="submit" class="button button-primary"><?php esc_attr_e('Registrarse', 'rentacar'); ?></button>
            </div>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode('rentacar_user_registration', 'rentacar_user_registration_form');



// No necesitas definir rentacar_create_tables() nuevamente aquí
// La función de creación de tablas debería estar solo en rentacar.php
?>