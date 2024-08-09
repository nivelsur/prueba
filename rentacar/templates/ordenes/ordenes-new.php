<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

$usuario_id = $vehiculo_id = $fecha_entrada = $hora_entrada = $fecha_salida = $hora_salida = '';
$tipo_pago = $retira_en = $entrega_en = $numero_vuelo = $precio_total = $adelanto_alquiler = $saldo_pagar = $estado = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rentacar_nonce']) && wp_verify_nonce($_POST['rentacar_nonce'], 'rentacar_save_order')) {
    // Sanitización y validación de datos
    $usuario_id = intval($_POST['usuario_id']);
    $vehiculo_id = intval($_POST['vehiculo_id']);
    $fecha_entrada = sanitize_text_field($_POST['fecha_entrada']);
    $hora_entrada = sanitize_text_field($_POST['hora_entrada']);
    $fecha_salida = sanitize_text_field($_POST['fecha_salida']);
    $hora_salida = sanitize_text_field($_POST['hora_salida']);
    $tipo_pago = sanitize_text_field($_POST['tipo_pago']);
    $retira_en = sanitize_text_field($_POST['retira_en']);
    $entrega_en = sanitize_text_field($_POST['entrega_en']);
    $numero_vuelo = sanitize_text_field($_POST['numero_vuelo']);
    $precio_total = floatval($_POST['precio_total']);
    $adelanto_alquiler = floatval($_POST['adelanto_alquiler']);
    $saldo_pagar = $precio_total - $adelanto_alquiler;
    $estado = sanitize_text_field($_POST['estado']);

    // Verificar disponibilidad del vehículo
    $vehiculo_disponible = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}rentacar_ordenes WHERE vehiculo_id = %d AND 
        ((fecha_entrada BETWEEN %s AND %s) OR (fecha_salida BETWEEN %s AND %s) OR (%s BETWEEN fecha_entrada AND fecha_salida) OR (%s BETWEEN fecha_entrada AND fecha_salida))",
        $vehiculo_id, $fecha_entrada, $fecha_salida, $fecha_entrada, $fecha_salida, $fecha_entrada, $fecha_salida
    ));

    if ($vehiculo_disponible > 0) {
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('El vehículo seleccionado no está disponible en las fechas indicadas.', 'rentacar') . '</p></div>';
    } else {
        // Inserción de datos en la base de datos
        $ordenes_table = $wpdb->prefix . 'rentacar_ordenes';
        $result = $wpdb->insert($ordenes_table, [
            'usuario_id' => $usuario_id,
            'vehiculo_id' => $vehiculo_id,
            'fecha_entrada' => $fecha_entrada,
            'hora_entrada' => $hora_entrada,
            'fecha_salida' => $fecha_salida,
            'hora_salida' => $hora_salida,
            'tipo_pago' => $tipo_pago,
            'retira_en' => $retira_en,
            'entrega_en' => $entrega_en,
            'numero_vuelo' => $numero_vuelo,
            'precio_total' => $precio_total,
            'adelanto_alquiler' => $adelanto_alquiler,
            'saldo_pagar' => $saldo_pagar,
            'estado' => $estado,
        ]);

        if ($result === false) {
            $error = $wpdb->last_error;
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Error al añadir la orden: ', 'rentacar') . $error . '</p></div>';
        } else {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Orden añadida con éxito', 'rentacar') . '</p></div>';
            echo '<script>setTimeout(function(){ window.location.href = "' . admin_url('admin.php?page=rentacar-ordenes') . '"; }, 2000);</script>';
        }
    }
}

// Obtener usuarios y vehículos para los selectores
$usuarios = $wpdb->get_results("SELECT id, CONCAT(nombre, ' ', apellido) AS nombre_completo FROM {$wpdb->prefix}rentacar_usuarios");
$vehiculos = $wpdb->get_results("SELECT id, nombre, precio_por_dia FROM {$wpdb->prefix}rentacar_vehiculos");

$tipos_pago = array('Pago en Efectivo', 'PayPal', 'Transferencia');
$ubicaciones = array(
    'Aeropuerto de Trelew',
    'Aeropuerto de Puerto Madryn',
    'Aeropuerto de Esquel',
    'Terminal de Ómnibus de Trelew',
    'Terminal de Ómnibus de Puerto Madryn',
    'Terminal de Ómnibus de Esquel'
);
?>

<div class="wrap-orden-new">
    <h1><?php esc_html_e('Añadir Nueva Orden', 'rentacar'); ?></h1>
    <form method="post" action="">
        <?php wp_nonce_field('rentacar_save_order', 'rentacar_nonce'); ?>
        <table class="form-table">
            <tr>
                <th><label for="usuario"><?php esc_html_e('Usuario', 'rentacar'); ?></label></th>
                <td>
                    <select id="usuario" name="usuario_id" required>
                        <option value=""><?php esc_html_e('Selecciona un Usuario', 'rentacar'); ?></option>
                        <?php foreach ($usuarios as $usuario) : ?>
                            <option value="<?php echo esc_attr($usuario->id); ?>" <?php selected($usuario_id, $usuario->id); ?>><?php echo esc_html($usuario->nombre_completo); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="vehiculo"><?php esc_html_e('Vehículo', 'rentacar'); ?></label></th>
                <td>
                    <select id="vehiculo" name="vehiculo_id" required>
                        <option value=""><?php esc_html_e('Selecciona un Vehículo', 'rentacar'); ?></option>
                        <?php foreach ($vehiculos as $vehiculo) : ?>
                            <option value="<?php echo esc_attr($vehiculo->id); ?>" <?php selected($vehiculo_id, $vehiculo->id); ?> data-precio="<?php echo esc_attr($vehiculo->precio_por_dia); ?>"><?php echo esc_html($vehiculo->nombre); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="fecha_entrada"><?php esc_html_e('Fecha de Entrada', 'rentacar'); ?></label></th>
                <td>
                    <input type="date" id="fecha_entrada" name="fecha_entrada" value="<?php echo esc_attr($fecha_entrada); ?>" required>
                    <input type="time" id="hora_entrada" name="hora_entrada" value="<?php echo esc_attr($hora_entrada); ?>" required>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="fecha_salida"><?php esc_html_e('Fecha de Salida', 'rentacar'); ?></label></th>
                <td>
                    <input type="date" id="fecha_salida" name="fecha_salida" value="<?php echo esc_attr($fecha_salida); ?>" required>
                    <input type="time" id="hora_salida" name="hora_salida" value="<?php echo esc_attr($hora_salida); ?>" required>
                </td>
            </tr>
            <tr>
                <th><label for="tipo_pago"><?php esc_html_e('Tipo de Pago', 'rentacar'); ?></label></th>
                <td>
                    <select id="tipo_pago" name="tipo_pago" required>
                        <option value=""><?php esc_html_e('Selecciona un Tipo de Pago', 'rentacar'); ?></option>
                        <?php foreach ($tipos_pago as $tipo) : ?>
                            <option value="<?php echo esc_attr($tipo); ?>" <?php selected($tipo_pago, $tipo); ?>><?php echo esc_html($tipo); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="retira_en"><?php esc_html_e('Retira en', 'rentacar'); ?></label></th>
                <td>
                    <select id="retira_en" name="retira_en" required>
                        <option value=""><?php esc_html_e('Selecciona una Ubicación', 'rentacar'); ?></option>
                        <?php foreach ($ubicaciones as $ubicacion) : ?>
                            <option value="<?php echo esc_attr($ubicacion); ?>" <?php selected($retira_en, $ubicacion); ?>><?php echo esc_html($ubicacion); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="entrega_en"><?php esc_html_e('Entrega en', 'rentacar'); ?></label></th>
                <td>
                    <select id="entrega_en" name="entrega_en" required>
                        <option value=""><?php esc_html_e('Selecciona una Ubicación', 'rentacar'); ?></option>
                        <?php foreach ($ubicaciones as $ubicacion) : ?>
                            <option value="<?php echo esc_attr($ubicacion); ?>" <?php selected($entrega_en, $ubicacion); ?>><?php echo esc_html($ubicacion); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="numero_vuelo"><?php esc_html_e('Número de Vuelo', 'rentacar'); ?></label></th>
                <td><input type="text" id="numero_vuelo" name="numero_vuelo" value="<?php echo esc_attr($numero_vuelo); ?>"></td>
            </tr>
            <tr>
                <th scope="row"><label for="precio_total"><?php esc_html_e('Precio Total', 'rentacar'); ?></label></th>
                <td><input type="number" id="precio_total" name="precio_total" value="<?php echo esc_attr($precio_total); ?>" readonly required></td>
            </tr>
            <tr>
                <th scope="row"><label for="adelanto_alquiler"><?php esc_html_e('Adelanto de Alquiler', 'rentacar'); ?></label></th>
                <td><input type="number" id="adelanto_alquiler" name="adelanto_alquiler" value="<?php echo esc_attr($adelanto_alquiler); ?>" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="saldo_pagar"><?php esc_html_e('Saldo a Pagar', 'rentacar'); ?></label></th>
                <td><input type="number" id="saldo_pagar" name="saldo_pagar" value="<?php echo esc_attr($saldo_pagar); ?>" readonly></td>
            </tr>
            <tr>
                <th scope="row"><label for="estado"><?php esc_html_e('Estado', 'rentacar'); ?></label></th>
                <td>
                    <select id="estado" name="estado" required>
                        <option value=""><?php esc_html_e('Selecciona un Estado', 'rentacar'); ?></option>
                        <option value="pendiente" <?php selected($estado, 'pendiente'); ?>><?php esc_html_e('Pendiente', 'rentacar'); ?></option>
                        <option value="confirmado" <?php selected($estado, 'confirmado'); ?>><?php esc_html_e('Confirmado', 'rentacar'); ?></option>
                        <option value="cancelado" <?php selected($estado, 'cancelado'); ?>><?php esc_html_e('Cancelado', 'rentacar'); ?></option>
                    </select>
                </td>
            </tr>
        </table>
        <p class="submit">
            <button type="submit" class="button button-primary"><?php esc_html_e('Guardar Orden', 'rentacar'); ?></button>
        </p>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const precioTotal = document.getElementById('precio_total');
    const saldoPagar = document.getElementById('saldo_pagar');
    const adelantoAlquiler = document.getElementById('adelanto_alquiler');
    const fechaEntrada = document.getElementById('fecha_entrada');
    const fechaSalida = document.getElementById('fecha_salida');
    const vehiculo = document.getElementById('vehiculo');

    function calculatePriceTotal() {
        if (vehiculo.value && fechaEntrada.value && fechaSalida.value) {
            const vehiculoPrecio = vehiculo.options[vehiculo.selectedIndex].dataset.precio;
            const entrada = new Date(fechaEntrada.value);
            const salida = new Date(fechaSalida.value);
            const diffTime = Math.abs(salida - entrada);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            const total = vehiculoPrecio * diffDays;
            precioTotal.value = total;
            saldoPagar.value = total - adelantoAlquiler.value;
        }
    }

    vehiculo.addEventListener('change', calculatePriceTotal);
    fechaEntrada.addEventListener('change', calculatePriceTotal);
    fechaSalida.addEventListener('change', calculatePriceTotal);
    adelantoAlquiler.addEventListener('input', () => {
        saldoPagar.value = precioTotal.value - adelantoAlquiler.value;
    });

    // Validación de fechas
    fechaEntrada.addEventListener('change', () => {
        if (new Date(fechaEntrada.value) >= new Date(fechaSalida.value)) {
            fechaSalida.value = '';
        }
    });

    fechaSalida.addEventListener('change', () => {
        if (new Date(fechaEntrada.value) >= new Date(fecha_salida.value)) {
            fechaSalida.value = '';
        }
    });
});
</script>