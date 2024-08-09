<?php
if (!defined('ABSPATH')) {
    exit;
}

// Función para mostrar la página de administración principal
function rentacar_admin_page() {
    global $wpdb;

    // Obtener las órdenes pendientes de aprobación
    $ordenes_pendientes = $wpdb->get_results("
        SELECT * 
        FROM {$wpdb->prefix}rentacar_ordenes 
        WHERE estado = 'Pendiente de Aprobación'
    ");

    // Obtener el calendario de reservas aprobadas
    $reservas_aprobadas = $wpdb->get_results("
        SELECT * 
        FROM {$wpdb->prefix}rentacar_ordenes 
        WHERE estado = 'Aprobado'
    ");

    // Obtener los últimos 5 reportes de órdenes con montos totales
    $ultimas_ordenes = $wpdb->get_results("
        SELECT o.*, u.nombre as usuario_nombre, v.nombre as vehiculo_nombre 
        FROM {$wpdb->prefix}rentacar_ordenes o
        JOIN {$wpdb->prefix}rentacar_usuarios u ON o.usuario_id = u.id
        JOIN {$wpdb->prefix}rentacar_vehiculos v ON o.vehiculo_id = v.id
        ORDER BY o.id DESC
        LIMIT 10
    ");
    
    // Encolar Bootstrap
    wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'); // conflicto con eliminacion de ordenes
    wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', ['jquery'], null, true);// nuevo 0.34
    // Incluir Font Awesome
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');

    // Obtener eventos para el calendario
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
        <h1><?php esc_html_e('Panel', 'rentacar'); ?></h1>
        <div class="rentacar-dashboard">
            <!-- Sección Últimas Órdenes -->
            <div class="panel-ordenes">
                <h2><?php esc_html_e('Últimas Órdenes', 'rentacar'); ?></h2>
                <?php if (empty($ultimas_ordenes)): ?>
                    <p><?php esc_html_e('No hay órdenes registradas.', 'rentacar'); ?></p>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('ID', 'rentacar'); ?></th>
                                <th><?php esc_html_e('Fecha de Entrada', 'rentacar'); ?></th>
                                <th><?php esc_html_e('Fecha de Salida', 'rentacar'); ?></th>
                                <th><?php esc_html_e('Precio Total', 'rentacar'); ?></th>
                                <th><?php esc_html_e('Estado', 'rentacar'); ?></th>
                                <th><?php esc_html_e('Acciones', 'rentacar'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ultimas_ordenes as $orden): ?>
                                <tr>
                                    <td><?php echo esc_html($orden->id); ?></td>
                                    <td><?php echo esc_html(date('Y-m-d', strtotime($orden->fecha_entrada))); ?></td>
                                    <td><?php echo esc_html(date('Y-m-d', strtotime($orden->fecha_salida))); ?></td>
                                    <td><?php echo esc_html(number_format($orden->precio_total, 2)); ?></td>
                                    <td><?php echo esc_html($orden->estado); ?></td>
                                    <td>
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=rentacar-ordenes')); ?>" class="button">Ver Órdenes </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Sección Calendario -->
            <div class="panel-calendario">
                <h2><?php esc_html_e('Calendario', 'rentacar'); ?></h2>
                <div id="calendar"></div>
            </div>


            <!-- Sección de Ganancias Mensuales -->
            <div class="ganancias-mensuales" class="container">
                <h2>Historial de Ganancias</h2>
                <canvas id="grafico-ganancias"></canvas>
            </div>
            
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                fetch('<?php echo admin_url('admin-ajax.php?action=obtener_ganancias_mensuales'); ?>')
                    .then(response => response.json())
                    .then(data => {
                        const ctx = document.getElementById('grafico-ganancias').getContext('2d');
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: data.map(d => d.mes_anio),
                                datasets: [{
                                    label: 'Ganancia Total',
                                    data: data.map(d => d.ganancia_total),
                                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                                    borderColor: 'rgba(54, 162, 235, 1)',
                                    borderWidth: 1,
                                    borderRadius: 5,
                                    borderSkipped: false
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                let label = context.dataset.label || '';
                                                if (label) {
                                                    label += ': ';
                                                }
                                                if (context.parsed.y !== null) {
                                                    label += new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'ARS' }).format(context.parsed.y);
                                                }
                                                return label;
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        grid: {
                                            display: false
                                        }
                                    },
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function(value) {
                                                return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'ARS' }).format(value);
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    });
            });
            </script>
        </div>
    </div>

    <!-- Asegúrate de que este script se incluya en tu página de administración -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.4/main.min.css' rel='stylesheet' />

    <!-- FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.4/main.min.js'></script>

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