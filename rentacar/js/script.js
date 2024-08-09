jQuery(document).ready(function($) {
    // Función para abrir el modal de eliminación
    function openDeleteModal(entityName, deleteUrl) {
        var modal = document.getElementById('confirm-modal');
        var overlay = document.querySelector('.modal-overlay');
        var yesButton = document.getElementById('confirm-delete');
        var noButton = document.getElementById('cancel-delete');
        var deleteMessage = document.getElementById('delete-message');

        deleteMessage.textContent = 'Estás a punto de eliminar ' + entityName + '. ¿Estás seguro de que deseas continuar?';

        yesButton.setAttribute('href', deleteUrl);

        modal.style.display = 'block';
        overlay.style.display = 'block';

        noButton.addEventListener('click', function() {
            modal.style.display = 'none';
            overlay.style.display = 'none';
        });

        return false;
    }

    // Ventana modal
    window.openDeleteModal = openDeleteModal;

    // Seleccionar imagen
    $('#upload_image_button').click(function(e) {
        e.preventDefault();

        var mediaUploader = wp.media({
            title: 'Seleccionar Imagen',
            button: {
                text: 'Seleccionar Imagen'
            },
            multiple: false
        }).on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#imagen').val(attachment.url);
            $('#image_preview').attr('src', attachment.url).show();
        }).open();
    });

    // Ver contraseña
    $('#toggle-password').on('click', function() {
        var passwordField = $('#password');
        var type = passwordField.attr('type') === 'password' ? 'text' : 'password';
        passwordField.attr('type', type);
        $(this).text(type === 'password' ? 'Ver contraseña' : 'Ocultar contraseña');
    });

    // Lógica de eliminación
    const modal = document.getElementById('confirm-modal');
    const deleteButtons = document.querySelectorAll('.delete-button');
    const deleteMessage = document.getElementById('delete-message');
    const confirmDeleteButton = document.getElementById('confirm-delete');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const orderId = this.getAttribute('data-id');
            deleteMessage.textContent = `Estás a punto de eliminar la orden con ID ${orderId}. ¿Estás seguro de que deseas continuar?`;
            confirmDeleteButton.setAttribute('href', `?page=rentacar-ordenes&action=confirm_delete&id=${orderId}`);
            modal.style.display = 'block';
        });
    });

    document.getElementById('cancel-delete').addEventListener('click', function(e) {
        e.preventDefault();
        modal.style.display = 'none';
    });

    confirmDeleteButton.addEventListener('click', function() {
        modal.style.display = 'none';
    });
    
    //Calendario Obtener Datos de las Órdenes
    document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es', // Para mostrar el calendario en español
        events: function(fetchInfo, successCallback, failureCallback) {
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    action: 'get_orders'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    successCallback(data.orders);
                } else {
                    failureCallback(data.message);
                }
            })
            .catch(error => {
                console.error('Error fetching orders:', error);
                failureCallback(error);
            });
        }
    });

    calendar.render();
});


    console.log('Rentacar plugin loaded.');
});