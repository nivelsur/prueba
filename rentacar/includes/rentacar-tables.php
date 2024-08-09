<?php
if (!defined('ABSPATH')) {
    exit;
}

function rentacar_create_tables() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    // Tabla de usuarios
    $table_name = $wpdb->prefix . 'rentacar_usuarios';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        usuario varchar(255) NOT NULL,
        nombre varchar(255) NOT NULL,
        apellido varchar(255) NOT NULL,
        pais varchar(255) NOT NULL,
        ciudad varchar(255) NOT NULL,
        codigo_postal varchar(20) NOT NULL,
        telefono varchar(20) NOT NULL,
        email varchar(255) NOT NULL,
        password varchar(255) NOT NULL,
        status varchar(20) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Tabla de vehículos
    $table_name = $wpdb->prefix . 'rentacar_vehiculos';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        vin_patente varchar(50) NOT NULL,
        nombre varchar(50) NOT NULL,
        imagen text NOT NULL,
        tipo_combustible tinytext NOT NULL,
        cantidad_puertas int(11) NOT NULL,
        cantidad_personas int(11) NOT NULL,
        cantidad_valijas int(11) NOT NULL,
        tipo_transmision varchar(50) NOT NULL,
        tipo_vehiculo varchar(50) NOT NULL,
        precio_por_dia float NOT NULL,
        descuento float NOT NULL,
        descripcion_breve text NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    dbDelta($sql);

    // Tabla de órdenes
    $table_name = $wpdb->prefix . 'rentacar_ordenes';
    $sql = "CREATE TABLE $table_name (
        id INT(11) NOT NULL AUTO_INCREMENT,
        usuario_id INT(11) NOT NULL,
        vehiculo_id INT(11) NOT NULL,
        fecha_entrada DATE NOT NULL,
        hora_entrada TIME NOT NULL,
        fecha_salida DATE NOT NULL,
        hora_salida TIME NOT NULL,
        tipo_pago VARCHAR(50) NOT NULL,
        retira_en VARCHAR(100) NOT NULL,
        entrega_en VARCHAR(100) NOT NULL,
        precio_total DECIMAL(10, 2) NOT NULL,
        estado VARCHAR(50) NOT NULL,
        numero_vuelo VARCHAR(255) NOT NULL,
        adelanto_alquiler DECIMAL(10, 2) NOT NULL,
        saldo_pagar DECIMAL(10, 2) NOT NULL,
        PRIMARY KEY (id)
    );";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function rentacar_delete_tables() {
    global $wpdb;

    $usuarios_table = $wpdb->prefix . 'rentacar_usuarios';
    $vehiculos_table = $wpdb->prefix . 'rentacar_vehiculos';
    $ordenes_table = $wpdb->prefix . 'rentacar_ordenes';

    $wpdb->query("DROP TABLE IF EXISTS $usuarios_table");
    $wpdb->query("DROP TABLE IF EXISTS $vehiculos_table");
    $wpdb->query("DROP TABLE IF EXISTS $ordenes_table");
}
?>