<?php
if (!defined('ABSPATH')) {
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recolectar y sanitizar datos del formulario
    global $wpdb;
    $vehiculos_table = $wpdb->prefix . 'rentacar_vehiculos';

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

    $result = $wpdb->insert($vehiculos_table, [
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
    ]);

    if ($result) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Vehículo añadido con éxito', 'rentacar') . '</p></div>';
        echo '<script>setTimeout(function(){ window.location.href = "' . admin_url('admin.php?page=rentacar-vehiculos') . '"; }, 2000);</script>';
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Error al añadir el vehículo. Por favor, inténtelo de nuevo.', 'rentacar') . '</p></div>';
    }
}
?>

<div class="wrap-vehiculos-new">
    <h1><?php esc_html_e('Añadir Nuevo Vehículo', 'rentacar'); ?></h1>
    <form method="post" action="">
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="vin_patente"><?php esc_html_e('Vin/Patente', 'rentacar'); ?></label>
                </th>
                <td>
                    <input name="vin_patente" type="text" id="vin_patente" value="" class="regular-text" required>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="nombre"><?php esc_html_e('Nombre del Vehículo', 'rentacar'); ?></label>
                </th>
                <td>
                    <input name="nombre" type="text" id="nombre" value="" class="regular-text" required>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="imagen"><?php esc_html_e('Imagen', 'rentacar'); ?></label>
                </th>
                <td>
                    <button type="button" class="button" id="upload_image_button"><?php esc_html_e('Seleccionar Imagen', 'rentacar'); ?></button>
                    <input name="imagen" type="hidden" id="imagen" value="">
                    <img id="image_preview" src="" style="max-width: 150px; display: none;">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label><?php esc_html_e('Tipo de Combustible', 'rentacar'); ?></label>
                </th>
                <td>
                    <fieldset>
                        <label><input name="tipo_combustible" type="radio" value="Nafta" required> <?php esc_html_e('Nafta', 'rentacar'); ?></label><br>
                        <label><input name="tipo_combustible" type="radio" value="Gas Oil"> <?php esc_html_e('Gas Oil', 'rentacar'); ?></label><br>
                        <label><input name="tipo_combustible" type="radio" value="Eléctrico"> <?php esc_html_e('Eléctrico', 'rentacar'); ?></label><br>
                        <label><input name="tipo_combustible" type="radio" value="Gas"> <?php esc_html_e('Gas', 'rentacar'); ?></label>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label><?php esc_html_e('Cantidad de Puertas', 'rentacar'); ?></label>
                </th>
                <td>
                    <fieldset>
                        <label><input name="cantidad_puertas" type="radio" value="2" required> <?php esc_html_e('2', 'rentacar'); ?></label><br>
                        <label><input name="cantidad_puertas" type="radio" value="3"> <?php esc_html_e('3', 'rentacar'); ?></label><br>
                        <label><input name="cantidad_puertas" type="radio" value="4"> <?php esc_html_e('4', 'rentacar'); ?></label><br>
                        <label><input name="cantidad_puertas" type="radio" value="5"> <?php esc_html_e('5', 'rentacar'); ?></label>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label><?php esc_html_e('Cantidad de Personas', 'rentacar'); ?></label>
                </th>
                <td>
                    <fieldset>
                        <label><input name="cantidad_personas" type="radio" value="2" required> <?php esc_html_e('2', 'rentacar'); ?></label><br>
                        <label><input name="cantidad_personas" type="radio" value="3"> <?php esc_html_e('3', 'rentacar'); ?></label><br>
                        <label><input name="cantidad_personas" type="radio" value="4"> <?php esc_html_e('4', 'rentacar'); ?></label><br>
                        <label><input name="cantidad_personas" type="radio" value="5"> <?php esc_html_e('5', 'rentacar'); ?></label>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label><?php esc_html_e('Cantidad de Valijas', 'rentacar'); ?></label>
                </th>
                <td>
                    <fieldset>
                        <label><input name="cantidad_valijas" type="radio" value="2" required> <?php esc_html_e('2', 'rentacar'); ?></label><br>
                        <label><input name="cantidad_valijas" type="radio" value="3"> <?php esc_html_e('3', 'rentacar'); ?></label><br>
                        <label><input name="cantidad_valijas" type="radio" value="4"> <?php esc_html_e('4', 'rentacar'); ?></label><br>
                        <label><input name="cantidad_valijas" type="radio" value="5"> <?php esc_html_e('5', 'rentacar'); ?></label>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="tipo_transmision"><?php esc_html_e('Tipo de Transmisión', 'rentacar'); ?></label>
                </th>
                <td>
                    <select name="tipo_transmision" id="tipo_transmision" required>
                        <option value="Tracción Delantera"><?php esc_html_e('Tracción Delantera', 'rentacar'); ?></option>
                        <option value="Tracción Trasera"><?php esc_html_e('Tracción Trasera', 'rentacar'); ?></option>
                        <option value="4x2"><?php esc_html_e('4x2', 'rentacar'); ?></option>
                        <option value="4x4"><?php esc_html_e('4x4', 'rentacar'); ?></option>
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
                    <input name="precio_por_dia" type="text" id="precio_por_dia" value="" class="regular-text" required>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="descuento"><?php esc_html_e('Descuento', 'rentacar'); ?></label>
                </th>
                <td>
                    <input name="descuento" type="text" id="descuento" value="" class="regular-text" textarea>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="descripcion_breve"><?php esc_html_e('Descripción Breve', 'rentacar'); ?></label>
                </th>
                <td>
                    <textarea name="descripcion_breve" id="descripcion_breve" rows="5" class="regular-text" textarea></textarea>
                </td>
            </tr>
        </table>
        <?php submit_button(__('Guardar Vehículo', 'rentacar')); ?>
    </form>
</div>