<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$vehiculos_table = $wpdb->prefix . 'rentacar_vehiculos';

// Obtener el ID del vehículo a editar
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
    // Recolectar y sanitizar datos del formulario
    $vin_patente = sanitize_text_field($_POST['vin_patente']);
    $nombre = sanitize_text_field($_POST['nombre']);
    $imagen = esc_url_raw($_POST['imagen']);
    $tipo_combustible = sanitize_text_field($_POST['tipo_combustible']);
    $cantidad_puertas = intval($_POST['cantidad_puertas']);
    $cantidad_personas = intval($_POST['cantidad_personas']);
    $cantidad_valijas = intval($_POST['cantidad_valijas']);
    $tipo_transmision = sanitize_text_field($_POST['tipo_transmision']);
    $tipo_vehiculo = sanitize_text_field($_POST['tipo_vehiculo']);
    $precio_por_dia = floatval($_POST['precio_por_dia']);
    $descuento = floatval($_POST['descuento']);
    $descripcion_breve = sanitize_text_field($_POST['descripcion_breve']);

    // Actualizar los datos del vehículo en la base de datos
    $result = $wpdb->update($vehiculos_table, [
        'vin_patente' => $vin_patente,
        'nombre' => $nombre,
        'imagen' => $imagen,
        'tipo_combustible' => $tipo_combustible,
        'cantidad_puertas' => $cantidad_puertas,
        'cantidad_personas' => $cantidad_personas,
        'cantidad_valijas' => $cantidad_valijas,
        'tipo_transmision' => $tipo_transmision,
        'tipo_vehiculo' => $tipo_vehiculo,
        'precio_por_dia' => $precio_por_dia,
        'descuento' => $descuento,
        'descripcion_breve' => $descripcion_breve,
    ], ['id' => $id]);

    if ($result !== false) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Vehículo actualizado con éxito', 'rentacar') . '</p></div>';
        echo '<script>setTimeout(function(){ window.location.href = "' . admin_url('admin.php?page=rentacar-vehiculos') . '"; }, 2000);</script>';
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Error al actualizar el vehículo. Por favor, inténtelo de nuevo.', 'rentacar') . '</p></div>';
    }
}

// Obtener los datos del vehículo
$vehiculo = $wpdb->get_row($wpdb->prepare("SELECT * FROM $vehiculos_table WHERE id = %d", $id));
if (!$vehiculo) {
    echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Vehículo no encontrado.', 'rentacar') . '</p></div>';
    exit;
}
?>

<div class="wrap">
    <h1><?php esc_html_e('Editar Vehículo', 'rentacar'); ?></h1>
    <form method="post" action="">
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="vin_patente"><?php esc_html_e('Vin/Patente', 'rentacar'); ?></label>
                </th>
                <td>
                    <input name="vin_patente" type="text" id="vin_patente" value="<?php echo esc_attr($vehiculo->vin_patente); ?>" class="regular-text" required>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="nombre"><?php esc_html_e('Nombre del Vehículo', 'rentacar'); ?></label>
                </th>
                <td>
                    <input name="nombre" type="text" id="nombre" value="<?php echo esc_attr($vehiculo->nombre); ?>" class="regular-text" required>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="imagen"><?php esc_html_e('Imagen', 'rentacar'); ?></label>
                </th>
                <td>
                    <input name="imagen" type="hidden" id="imagen" value="<?php echo esc_url($vehiculo->imagen); ?>">
                    <button type="button" class="button" id="upload_image_button"><?php esc_html_e('Seleccionar Imagen', 'rentacar'); ?></button>
                    <img id="image_preview" src="<?php echo esc_url($vehiculo->imagen); ?>" style="max-width: 150px; display: block;">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label><?php esc_html_e('Tipo de Combustible', 'rentacar'); ?></label>
                </th>
                <td>
                    <fieldset>
                        <label><input name="tipo_combustible" type="radio" value="Nafta" <?php checked($vehiculo->tipo_combustible, 'Nafta'); ?> required> <?php esc_html_e('Nafta', 'rentacar'); ?></label><br>
                        <label><input name="tipo_combustible" type="radio" value="Gas Oil" <?php checked($vehiculo->tipo_combustible, 'Gas Oil'); ?>> <?php esc_html_e('Gas Oil', 'rentacar'); ?></label><br>
                        <label><input name="tipo_combustible" type="radio" value="Eléctrico" <?php checked($vehiculo->tipo_combustible, 'Eléctrico'); ?>> <?php esc_html_e('Eléctrico', 'rentacar'); ?></label><br>
                        <label><input name="tipo_combustible" type="radio" value="Gas" <?php checked($vehiculo->tipo_combustible, 'Gas'); ?>> <?php esc_html_e('Gas', 'rentacar'); ?></label>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label><?php esc_html_e('Cantidad de Puertas', 'rentacar'); ?></label>
                </th>
                <td>
                    <fieldset>
                        <label><input name="cantidad_puertas" type="radio" value="2" <?php checked($vehiculo->cantidad_puertas, '2'); ?> required> <?php esc_html_e('2', 'rentacar'); ?></label><br>
                        <label><input name="cantidad_puertas" type="radio" value="3" <?php checked($vehiculo->cantidad_puertas, '3'); ?>> <?php esc_html_e('3', 'rentacar'); ?></label><br>
                        <label><input name="cantidad_puertas" type="radio" value="4" <?php checked($vehiculo->cantidad_puertas, '4'); ?>> <?php esc_html_e('4', 'rentacar'); ?></label><br>
                        <label><input name="cantidad_puertas" type="radio" value="5" <?php checked($vehiculo->cantidad_puertas, '5'); ?>> <?php esc_html_e('5', 'rentacar'); ?></label>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label><?php esc_html_e('Cantidad de Personas', 'rentacar'); ?></label>
                </th>
                <td>
                    <fieldset>
                        <label><input name="cantidad_personas" type="radio" value="2" <?php checked($vehiculo->cantidad_personas, '2'); ?> required> <?php esc_html_e('2', 'rentacar'); ?></label><br>
                        <label><input name="cantidad_personas" type="radio" value="3" <?php checked($vehiculo->cantidad_personas, '3'); ?>> <?php esc_html_e('3', 'rentacar'); ?></label><br>
                        <label><input name="cantidad_personas" type="radio" value="4" <?php checked($vehiculo->cantidad_personas, '4'); ?>> <?php esc_html_e('4', 'rentacar'); ?></label><br>
                        <label><input name="cantidad_personas" type="radio" value="5" <?php checked($vehiculo->cantidad_personas, '5'); ?>> <?php esc_html_e('5', 'rentacar'); ?></label>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label><?php esc_html_e('Cantidad de Valijas', 'rentacar'); ?></label>
                </th>
                <td>
                    <fieldset>
                        <label><input name="cantidad_valijas" type="radio" value="2" <?php checked($vehiculo->cantidad_valijas, '2'); ?> required> <?php esc_html_e('2', 'rentacar'); ?></label><br>
                        <label><input name="cantidad_valijas" type="radio" value="3" <?php checked($vehiculo->cantidad_valijas, '3'); ?>> <?php esc_html_e('3', 'rentacar'); ?></label><br>
                        <label><input name="cantidad_valijas" type="radio" value="4" <?php checked($vehiculo->cantidad_valijas, '4'); ?>> <?php esc_html_e('4', 'rentacar'); ?></label><br>
                        <label><input name="cantidad_valijas" type="radio" value="5" <?php checked($vehiculo->cantidad_valijas, '5'); ?>> <?php esc_html_e('5', 'rentacar'); ?></label>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="tipo_transmision"><?php esc_html_e('Tipo de Transmisión', 'rentacar'); ?></label>
                </th>
                <td>
                    <select name="tipo_transmision" id="tipo_transmision" required>
                        <option value="Manual" <?php selected($vehiculo->tipo_transmision, 'Manual'); ?>><?php esc_html_e('Manual', 'rentacar'); ?></option>
                        <option value="Automática" <?php selected($vehiculo->tipo_transmision, 'Automática'); ?>><?php esc_html_e('Automática', 'rentacar'); ?></option>
                        <option value="Semi-Automática" <?php selected($vehiculo->tipo_transmision, 'Semi-Automática'); ?>><?php esc_html_e('Semi-Automática', 'rentacar'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="tipo_vehiculo"><?php esc_html_e('Tipo de Vehículo', 'rentacar'); ?></label>
                </th>
                <td>
                    <select name="tipo_vehiculo" id="tipo_vehiculo" required>
                        <option value="Sedán"><?php esc_html_e('Sedán', 'rentacar'); ?></option>
                        <option value="Cupé"><?php esc_html_e('Cupé', 'rentacar'); ?></option>
                        <option value="Pickup"><?php esc_html_e('Pickup', 'rentacar'); ?></option>
                        <option value="Suv"><?php esc_html_e('SUV', 'rentacar'); ?></option>
                        <option value="Van"><?php esc_html_e('Van', 'rentacar'); ?></option>
                        <option value="Crossover"><?php esc_html_e('Crossover', 'rentacar'); ?></option>
                        <option value="Convertible"><?php esc_html_e('Convertible', 'rentacar'); ?></option>
                        <option value="Furgon"><?php esc_html_e('Furgón', 'rentacar'); ?></option>
                        <option value="Mini Van"><?php esc_html_e('Mini Van', 'rentacar'); ?></option>
                        <option value="Camioneta"><?php esc_html_e('Camioneta', 'rentacar'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="precio_por_dia"><?php esc_html_e('Precio por Día', 'rentacar'); ?></label>
                </th>
                <td>
                    <input name="precio_por_dia" type="number" id="precio_por_dia" value="<?php echo esc_attr($vehiculo->precio_por_dia); ?>" step="0.01" min="0" class="regular-text" required>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="descuento"><?php esc_html_e('Descuento', 'rentacar'); ?></label>
                </th>
                <td>
                    <input name="descuento" type="number" id="descuento" value="<?php echo esc_attr($vehiculo->descuento); ?>" step="0.01" min="0" max="100" class="regular-text">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="descripcion_breve"><?php esc_html_e('Descripción Breve', 'rentacar'); ?></label>
                </th>
                <td>
                    <textarea name="descripcion_breve" id="descripcion_breve" rows="5" class="large-text"><?php echo esc_textarea($vehiculo->descripcion_breve); ?></textarea>
                </td>
            </tr>
        </table>
        <?php submit_button(__('Actualizar Vehículo', 'rentacar')); ?>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    var mediaUploader;

    $('#upload_image_button').click(function(e) {
        e.preventDefault();

        // Si el medidor de medios ya está activo, ciérralo
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        // Crear un nuevo medidor de medios
        mediaUploader = wp.media({
            title: 'Seleccionar Imagen',
            button: {
                text: 'Seleccionar Imagen'
            },
            multiple: false
        });

        // Cuando se seleccione una imagen, actualizar el campo de entrada y mostrar la vista previa
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#imagen').val(attachment.url);
            $('#image_preview').attr('src', attachment.url).show();
        });

        // Abrir el medidor de medios
        mediaUploader.open();
    });
});
</script>