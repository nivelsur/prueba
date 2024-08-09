<?php
function rentacar_calendario_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('No tienes permisos suficientes para acceder a esta página.', 'rentacar'));
    }
    
    // Encolar Bootstrap
    wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'); // conflicto con eliminacion de ordenes
    wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', ['jquery'], null, true);// nuevo 0.34
    // Incluir Font Awesome
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');

    // Obtener eventos de las órdenes desde la base de datos
    global $wpdb;
    $events = [];
    $orders = $wpdb->get_results("
        SELECT o.id, o.fecha_entrada, o.fecha_salida, v.nombre AS vehiculo_nombre
        FROM {$wpdb->prefix}rentacar_ordenes o
        JOIN {$wpdb->prefix}rentacar_vehiculos v ON o.vehiculo_id = v.id
    ");
    foreach ($orders as $order) {
        $events[] = [
            'title' => "Orden #" . $order->id . " - Vehículo: " . $order->vehiculo_nombre,
            'start' => $order->fecha_entrada,
            'end' => $order->fecha_salida
        ];
    }
    ?>
    <div class="wrap">
        <h1><?php _e('Calendario de Reservas', 'rentacar'); ?></h1>
        <div id="calendar"></div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es', // Establecer el idioma a español
            themeSystem: 'bootstrap',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth' // Agregar opciones de vista
            },
            views: {
                listMonth: { // Vista de lista mensual
                    type: 'list',
                    duration: { months: 1 }
                },
                dayGridSixMonth: { // Vista de seis meses
                    type: 'dayGrid',
                    duration: { months: 6 },
                    buttonText: '6 meses'
                },
                dayGridYear: { // Vista anual
                    type: 'dayGrid',
                    duration: { year: 1 },
                    buttonText: 'Año'
                }
            },
            events: <?php echo json_encode($events); ?>, // Eventos obtenidos desde la base de datos
            editable: true,
            droppable: true
        });

        calendar.render();
        
        // Personalizar los botones del calendario
        document.querySelector('.fc-prev-button').innerHTML = '<i class="fas fa-chevron-left"></i>';
        document.querySelector('.fc-next-button').innerHTML = '<i class="fas fa-chevron-right"></i>';
    });
    </script>
    <?php
}
?>