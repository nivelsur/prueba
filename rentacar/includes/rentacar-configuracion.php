<?php
function rentacar_export_data() {
    global $wpdb;

    $usuarios_table = $wpdb->prefix . 'rentacar_usuarios';
    $vehiculos_table = $wpdb->prefix . 'rentacar_vehiculos';
    $ordenes_table = $wpdb->prefix . 'rentacar_ordenes';

    $usuarios = $wpdb->get_results("SELECT * FROM $usuarios_table", ARRAY_A);
    $vehiculos = $wpdb->get_results("SELECT * FROM $vehiculos_table", ARRAY_A);
    $ordenes = $wpdb->get_results("SELECT * FROM $ordenes_table", ARRAY_A);

    $data = [
        'usuarios' => $usuarios,
        'vehiculos' => $vehiculos,
        'ordenes' => $ordenes,
    ];

    $json_data = json_encode($data);

    header('Content-Description: File Transfer');
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename=rentacar_copiadeseguridad.json');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . strlen($json_data));
    
    echo $json_data;
    exit;
}

    if (isset($_POST['export_data'])) {
        rentacar_export_data();
    } //fin de descarga de copia de seguridad 0.33

function rentacar_import_data($file) {
    global $wpdb;

    $json_data = file_get_contents($file);
    $data = json_decode($json_data, true);

    if ($data && is_array($data)) {
        $usuarios_table = $wpdb->prefix . 'rentacar_usuarios';
        $vehiculos_table = $wpdb->prefix . 'rentacar_vehiculos';
        $ordenes_table = $wpdb->prefix . 'rentacar_ordenes';

        // Limpiar tablas
        $wpdb->query("TRUNCATE TABLE $usuarios_table");
        $wpdb->query("TRUNCATE TABLE $vehiculos_table");
        $wpdb->query("TRUNCATE TABLE $ordenes_table");

        // Insertar datos importados
        foreach ($data['usuarios'] as $usuario) {
            $wpdb->insert($usuarios_table, $usuario);
        }
        foreach ($data['vehiculos'] as $vehiculo) {
            $wpdb->insert($vehiculos_table, $vehiculo);
        }
        foreach ($data['ordenes'] as $orden) {
            $wpdb->insert($ordenes_table, $orden);
        }

        echo '<div class="updated"><p>' . __('Copia de seguridad restaurada correctamente.', 'rentacar') . '</p></div>';
    } else {
        echo '<div class="error"><p>' . __('Error al restaurar la copia de seguridad.', 'rentacar') . '</p></div>';
    }
}

function rentacar_configuracion_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('No tienes permisos suficientes para acceder a esta página.', 'rentacar'));
    }

    // Procesamiento antes de la salida HTML
    if (isset($_POST['rentacar_backup_download'])) {
        rentacar_export_data();
    }

    if (isset($_POST['rentacar_configuracion_save'])) {
        // Guardar la configuración si el formulario ha sido enviado
        update_option('rentacar_nombre_empresa', sanitize_text_field($_POST['rentacar_nombre_empresa']));
        update_option('rentacar_direccion', sanitize_text_field($_POST['rentacar_direccion']));
        update_option('rentacar_telefono', sanitize_text_field($_POST['rentacar_telefono']));
        update_option('rentacar_whatsapp', sanitize_text_field($_POST['rentacar_whatsapp']));
        update_option('rentacar_email', sanitize_email($_POST['rentacar_email']));

        // Guardar los horarios de atención
        $horarios = [];
        for ($i = 0; $i < 7; $i++) {
            $horarios[$i] = [
                'mañana' => sanitize_text_field($_POST["horario_{$i}_mañana"]),
                'tarde' => sanitize_text_field($_POST["horario_{$i}_tarde"]),
            ];
        }
        update_option('rentacar_horarios', $horarios);

        echo '<div class="updated"><p>' . __('Configuración guardada.', 'rentacar') . '</p></div>';
    }

    if (isset($_POST['rentacar_backup_upload_submit']) && isset($_FILES['rentacar_backup_upload'])) {
        if ($_FILES['rentacar_backup_upload']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['rentacar_backup_upload']['tmp_name'];
            rentacar_import_data($file_tmp);
        } else {
            echo '<div class="error"><p>' . __('Error al subir el archivo.', 'rentacar') . '</p></div>';
        }
    }

    // Obtener la configuración actual
    $nombre_empresa = get_option('rentacar_nombre_empresa', '');
    $direccion = get_option('rentacar_direccion', '');
    $telefono = get_option('rentacar_telefono', '');
    $whatsapp = get_option('rentacar_whatsapp', '');
    $email = get_option('rentacar_email', '');

    // Obtener los horarios de atención
    $horarios = get_option('rentacar_horarios', []);
    if (empty($horarios)) {
        $horarios = array_fill(0, 7, ['mañana' => '', 'tarde' => '']);
    }

    ?>
    <div class="wrap">
        <h1><?php _e('Configuración de Rentacar', 'rentacar'); ?></h1>
        <form method="post" action="" enctype="multipart/form-data">
            <?php wp_nonce_field('rentacar_configuracion_save', 'rentacar_configuracion_nonce'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Nombre de la Empresa', 'rentacar'); ?></th>
                    <td><input type="text" name="rentacar_nombre_empresa" value="<?php echo esc_attr($nombre_empresa); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Dirección', 'rentacar'); ?></th>
                    <td><input type="text" name="rentacar_direccion" value="<?php echo esc_attr($direccion); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Teléfono de la Empresa', 'rentacar'); ?></th>
                    <td><input type="text" name="rentacar_telefono" value="<?php echo esc_attr($telefono); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Número de WhatsApp', 'rentacar'); ?></th>
                    <td><input type="text" name="rentacar_whatsapp" value="<?php echo esc_attr($whatsapp); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Correo Electrónico de la Empresa', 'rentacar'); ?></th>
                    <td><input type="email" name="rentacar_email" value="<?php echo esc_attr($email); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Horarios de Atención Comercial', 'rentacar'); ?></th>
                    <td>
                        <table class="horarios-table">
                            <?php
                            $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
                            foreach ($dias as $index => $dia) {
                                ?>
                                <tr class="horario-row">
                                    <td>
                                        <label for="horario_<?php echo $index; ?>_mañana"><?php echo esc_html($dia); ?></label>
                                    </td>
                                    <td>
                                        <label for="horario_<?php echo $index; ?>_mañana"><?php _e('Mañana', 'rentacar'); ?></label>
                                        <input type="text" name="horario_<?php echo $index; ?>_mañana" id="horario_<?php echo $index; ?>_mañana" value="<?php echo esc_attr($horarios[$index]['mañana']); ?>" />
                                    </td>
                                    <td>
                                        <label for="horario_<?php echo $index; ?>_tarde"><?php _e('Tarde', 'rentacar'); ?></label>
                                        <input type="text" name="horario_<?php echo $index; ?>_tarde" id="horario_<?php echo $index; ?>_tarde" value="<?php echo esc_attr($horarios[$index]['tarde']); ?>" />
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                        </table>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Copia de Seguridad', 'rentacar'); ?></th>
                    <td>
                        <input type="submit" name="export_data" value="Exportar Datos" />
                        <input type="file" name="rentacar_backup_upload" />
                        <input type="submit" name="rentacar_backup_upload_submit" value="<?php _e('Subir Copia de Seguridad', 'rentacar'); ?>" class="button" />
                    </td>
                </tr>
            </table>
            <?php submit_button(__('Guardar Configuración', 'rentacar'), 'primary', 'rentacar_configuracion_save'); ?>
        </form>
    </div>
    <?php
}
?>
