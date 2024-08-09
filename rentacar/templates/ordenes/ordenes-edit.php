<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rentacar_nonce']) && wp_verify_nonce($_POST['rentacar_nonce'], 'rentacar_update_order')) {
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

    $ordenes_table = $wpdb->prefix . 'rentacar_ordenes';

    // Actualización de datos en la base de datos
    $result = $wpdb->update($ordenes_table, [
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
    ], ['id' => $order_id]);

    if ($result === false) {
        $error = $wpdb->last_error;
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Error al actualizar la orden: ', 'rentacar') . $error . '</p></div>';
    } else {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Orden actualizada con éxito', 'rentacar') . '</p></div>';
        echo '<script>setTimeout(function(){ window.location.href = "' . admin_url('admin.php?page=rentacar-ordenes') . '"; }, 2000);</script>';
    }
}

// Obtener la orden para editar
$order = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}rentacar_ordenes WHERE id = %d", $order_id));
if (!$order) {
    echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Orden no encontrada', 'rentacar') . '</p></div>';
    return;
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

<div class="wrap">
    <h1><?php esc_html_e('Editar Orden', 'rentacar'); ?></h1>
    <form method="post" action="">
        <?php wp_nonce_field('rentacar_update_order', 'rentacar_nonce'); ?>
        <table class="form-table">
            <tr>
                <th><label for="usuario"><?php esc_html_e('Usuario', 'rentacar'); ?></label></th>
                <td>
                    <select id="usuario" name="usuario_id" required>
                        <option value=""><?php esc_html_e('Selecciona un Usuario', 'rentacar'); ?></option>
                        <?php foreach ($usuarios as $usuario) : ?>
                            <option value="<?php echo esc_attr($usuario->id); ?>" <?php selected($order->usuario_id, $usuario->id); ?>><?php echo esc_html($usuario->nombre_completo); ?></option>
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
                            <option value="<?php echo esc_attr($vehiculo->id); ?>" data-precio="<?php echo esc_attr($vehiculo->precio_por_dia); ?>" <?php selected($order->vehiculo_id, $vehiculo->id); ?>><?php echo esc_html($vehiculo->nombre); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="fecha_entrada">Fecha de Entrada</label></th>
                <td>
                    <input type="date" id="fecha_entrada" name="fecha_entrada" value="<?php echo esc_attr($order->fecha_entrada); ?>">
                    <input type="time" id="hora_entrada" name="hora_entrada" value="<?php echo esc_attr($order->hora_entrada); ?>">
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="fecha_salida">Fecha de Salida</label></th>
                <td>
                    <input type="date" id="fecha_salida" name="fecha_salida" value="<?php echo esc_attr($order->fecha_salida); ?>">
                    <input type="time" id="hora_salida" name="hora_salida" value="<?php echo esc_attr($order->hora_salida); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="tipo_pago"><?php esc_html_e('Tipo de Pago', 'rentacar'); ?></label></th>
                <td>
                    <select id="tipo_pago" name="tipo_pago" required>
                        <option value=""><?php esc_html_e('Selecciona un Tipo de Pago', 'rentacar'); ?></option>
                        <?php foreach ($tipos_pago as $tipo) : ?>
                            <option value="<?php echo esc_attr($tipo); ?>" <?php selected($order->tipo_pago, $tipo); ?>><?php echo esc_html($tipo); ?></option>
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
                            <option value="<?php echo esc_attr($ubicacion); ?>" <?php selected($order->retira_en, $ubicacion); ?>><?php echo esc_html($ubicacion); ?></option>
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
                            <option value="<?php echo esc_attr($ubicacion); ?>" <?php selected($order->entrega_en, $ubicacion); ?>><?php echo esc_html($ubicacion); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="numero_vuelo"><?php esc_html_e('Número de Vuelo', 'rentacar'); ?></label></th>
                <td><input type="text" id="numero_vuelo" name="numero_vuelo" value="<?php echo esc_attr($order->numero_vuelo); ?>"></td>
            </tr>
            <tr>
                <th><label for="precio_total"><?php esc_html_e('Precio Total', 'rentacar'); ?></label></th>
                <td>
                    <input type="number" id="precio_total" name="precio_total" step="0.01" value="<?php echo esc_attr($order->precio_total); ?>" readonly>
                </td>
            </tr>
            <tr>
                <th><label for="adelanto_alquiler"><?php esc_html_e('Adelanto de Alquiler', 'rentacar'); ?></label></th>
                <td><input type="number" id="adelanto_alquiler" name="adelanto_alquiler" step="0.01" value="<?php echo esc_attr($order->adelanto_alquiler); ?>"></td>
            </tr>
            <tr>
                <th><label for="saldo_pagar"><?php esc_html_e('Saldo a Pagar', 'rentacar'); ?></label></th>
                <td><input type="number" id="saldo_pagar" name="saldo_pagar" step="0.01" value="<?php echo esc_attr($order->saldo_pagar); ?>" readonly></td>
            </tr>
            <tr>
                <th><label for="estado"><?php esc_html_e('Estado de la Orden', 'rentacar'); ?></label></th>
                <td>
                    <select id="estado" name="estado" required>
                        <option value=""><?php esc_html_e('Selecciona un Estado', 'rentacar'); ?></option>
                        <option value="Pendiente" <?php selected($order->estado, 'Pendiente'); ?>><?php esc_html_e('Pendiente', 'rentacar'); ?></option>
                        <option value="Confirmada" <?php selected($order->estado, 'Confirmada'); ?>><?php esc_html_e('Confirmada', 'rentacar'); ?></option>
                        <option value="Cancelada" <?php selected($order->estado, 'Cancelada'); ?>><?php esc_html_e('Cancelada', 'rentacar'); ?></option>
                    </select>
                </td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" class="button-primary" value="<?php esc_attr_e('Actualizar Orden', 'rentacar'); ?>">
        </p>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fechaEntrada = document.getElementById('fecha_entrada');
    const fechaSalida = document.getElementById('fecha_salida');
    const vehiculoSelect = document.getElementById('vehiculo');
    const precioTotal = document.getElementById('precio_total');
    const adelantoAlquiler = document.getElementById('adelanto_alquiler');
    const saldoPagar = document.getElementById('saldo_pagar');

    function calculatePriceTotal() {
        const entrada = new Date(fechaEntrada.value);
        const salida = new Date(fechaSalida.value);
        const vehiculo = vehiculoSelect.options[vehiculoSelect.selectedIndex];

        if (!entrada || !salida || !vehiculo.dataset.precio) {
            return;
        }

        const diffTime = Math.abs(salida - entrada);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        const precioPorDia = parseFloat(vehiculo.dataset.precio);
        
        if (!isNaN(diffDays) && !isNaN(precioPorDia)) {
            const total = (diffDays * precioPorDia).toFixed(2);
            precioTotal.value = total;

            // Recalcular el saldo a pagar
            const adelanto = parseFloat(adelantoAlquiler.value) || 0;
            saldoPagar.value = (total - adelanto).toFixed(2);
        } else {
            precioTotal.value = '0.00';
            saldoPagar.value = '0.00';
        }
    }

    function calculateSaldoPagar() {
        const total = parseFloat(precioTotal.value) || 0;
        const adelanto = parseFloat(adelantoAlquiler.value) || 0;
        saldoPagar.value = (total - adelanto).toFixed(2);
    }

    fechaEntrada.addEventListener('change', calculatePriceTotal);
    fechaSalida.addEventListener('change', calculatePriceTotal);
    vehiculoSelect.addEventListener('change', calculatePriceTotal);
    adelantoAlquiler.addEventListener('input', calculateSaldoPagar);
});
</script>