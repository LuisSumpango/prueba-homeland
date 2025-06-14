<x-app-layout>

    @include('product.item.create_modal')

    <!-- Modal de confirmación para eliminar -->
    <div id="confirmModal" class="modal">
        <div class="modal-content">
            <h2>Confirmar Eliminación</h2>
            <p>¿Está seguro de que desea eliminar este producto?</p>
            <div style="text-align: right;">
                
                <button class="btn button_edit" id="cancelDeleteBtn" style="color: black; width: auto; background-color: yellow; opacity: 0.9;">
                    Cancelar
                </button>
                <button class="btn button_delete" style="color: black; width: auto; background-color: red; opacity: 0.9;" id="confirmDeleteBtn">
                    Eliminar
                </button>
            </div>
        </div>
    </div>

    <div class="py-12">
        <div class="container mt-4 table-container">
            <h1 style="color: white; font-size: 25px;">Catálogo de Productos</h1>
            
            <button class="btn button_create" id="addProductBtn">
                Agregar Producto
            </button>
            
            <div id="productsTableContainer">
                @include('product.item.table', ['products' => $products])
            </div>
        </div>

        {{-- <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr class="table-row">
                        <th class="table-header">ID</th>
                        <th class="table-header">Name</th>
                        <th class="table-header">Email</th>
                        <th class="table-header">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="table-row">
                        <td class="table-cell">1</td>
                        <td class="table-cell">John Doe</td>
                        <td class="table-cell">john@example.com</td>
                        <td class="table-cell">Active</td>
                    </tr>
                    <tr class="table-row">
                        <td class="table-cell">2</td>
                        <td class="table-cell">Jane Smith</td>
                        <td class="table-cell">jane@example.com</td>
                        <td class="table-cell">Inactive</td>
                    </tr>
                    <tr class="table-row">
                        <td class="table-cell">3</td>
                        <td class="table-cell">Robert Johnson</td>
                        <td class="table-cell">robert@example.com</td>
                        <td class="table-cell">Active</td>
                    </tr>
                </tbody>
            </table>
        </div> --}}
    </div>
    @section('own_js')
    <script>
        // Esto nos sirve para incluir el token en odos los encabezados de ajax
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function() {
            $("#productModal").on("keydown", function(event) {
                if (event.key === "Enter") {
                    event.preventDefault();
                    return false;
                }
            });

            let productModal = $('#productModal');
            let confirmModal = $('#confirmModal');
            let currentProductId = null;
            let currentSort = 'entry_date';
            let currentOrder = 'desc';

            // Función para mostrar/ocultar modales
            function showModal(modal) {
                modal.css('display', 'block');
            }

            function hideModal(modal) {
                modal.css('display', 'none');
            }

            // Cerrar modales al hacer clic en la X
            $('.close').click(function() {
                hideModal(productModal);
            });

            $('#cancelDeleteBtn').click(function() {
                hideModal(confirmModal);
            });

            // Cerrar modales al hacer clic fuera del contenido
            $(window).click(function(event) {
                if (event.target === productModal[0]) {
                    hideModal(productModal);
                }
                if (event.target === confirmModal[0]) {
                    hideModal(confirmModal);
                }
            });

             // Cargar productos via AJAX
            function loadProducts(page = 1) {
                $.ajax({
                    url: '{{ route("product.index") }}',
                    data: {
                        page: page,
                        sort: currentSort,
                        order: currentOrder
                    },
                    success: function(response) {
                        $('#productsTableContainer').html(response.html);
                        // Actualizar solo la parte de paginación
                        $('.pagination-container').html(response.pagination);
                        assignEvents();
                    }
                });
            }

            // Asignar eventos a los elementos dinámicos
            function assignEvents() {
                // Ordenamiento
                $('.sortable').off('click').on('click', function() {
                    const sortField = $(this).data('sort');
                    currentOrder = (currentSort === sortField && currentOrder === 'asc') ? 'desc' : 'asc';
                    currentSort = sortField;
                    loadProducts();
                });

                // Paginación
                $(document).off('click', '.pagination a').on('click', '.pagination a', function(e) {
                    e.preventDefault();
                    const page = $(this).attr('href').split('page=')[1];
                    loadProducts(page);
                    // Scroll suave hacia arriba
                    $('html, body').animate({ scrollTop: 0 }, 'smooth');
                });

                // Botón editar
                $('.edit-btn').off('click').on('click', function() {
                    currentProductId = $(this).data('id');
                    loadProductForm(currentProductId);
                    $('#modalTitle').text('Editar Producto');
                    showModal(productModal);
                });

                // Botón eliminar
                $('.delete-btn').off('click').on('click', function() {
                    currentProductId = $(this).data('id');
                    showModal(confirmModal);
                });
            }

            // Cargar formulario en el modal
            function loadProductForm(productId = null) {
                const url = productId ? `/product/${productId}/edit` : '/product/create';
                
                $.get(url, function(response) {
                    $('#modalFormContainer').html(response);
                    assignFormEvents();
                });
            }

            // Asignar eventos al formulario
            function assignFormEvents() {
                const $form = $('#productForm');
                
                $form.off('submit').on('submit', function(e) {
                    e.preventDefault();
                    
                    if (validateForm()) {
                        const formData = new FormData(this);
                        const url = currentProductId ? `/product/${currentProductId}` : '/product';
                        const method = currentProductId ? 'PUT' : 'POST';

                        if (currentProductId) {
                            formData.append('_method', 'PUT');
                        }
                        
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                if (response.success) {
                                    showSuccessMessage('Producto guardado correctamente');
                                    hideModal(productModal);
                                    loadProducts();
                                }
                            },
                            error: function(xhr) {
                                if (xhr.status === 422) {
                                    // Error de validación
                                    const errors = xhr.responseJSON.errors;
                                    showErrorMessage(xhr.responseJSON.message || 'Error al guardar el producto');
                                    displayErrors(errors);
                                    
                                    // Mensaje específico para código duplicado
                                    
                                    if (errors.code && errors.code.includes('already been taken')) {
                                        $('#codeError').text('Este código de producto ya existe. Por favor ingrese uno diferente.');
                                    }
                                } else {
                                    console.error('Error:', xhr.responseText);
                                    showErrorMessage(xhr.responseJSON.message || 'Error al guardar el producto');
                                }
                            }
                        });
                    }
                });
            }

            // Validación del formulario
            function validateForm() {
                let isValid = true;
                $('.error-message').text('');
                
                // Validación de código
                const code = $('#code').val();
                if (!code.match(/^[a-zA-Z0-9]+$/)) {
                    $('#codeError').text('Solo letras y números permitidos');
                    isValid = false;
                }
                
                // Validación de nombre
                const name = $('#name').val();
                if (!name.match(/^[a-zA-Z\s]+$/)) {
                    $('#nameError').text('Solo letras y espacios permitidos');
                    isValid = false;
                }
                
                 // Validación de fechas
                const entryDateInput = document.getElementById('entry_date');
                const expirationDateInput = document.getElementById('expiration_date');
                
                if (!isValidDate(entryDateInput)) {
                    $('#entryDateError').text('Seleccione una fecha válida');
                    isValid = false;
                }
                
                if (!isValidDate(expirationDateInput)) {
                    $('#expirationDateError').text('Seleccione una fecha válida');
                    isValid = false;
                }
                
                if (isValid && !isDateBefore(entryDateInput, expirationDateInput)) {
                    $('#expirationDateError').text('La fecha de vencimiento debe ser posterior a la de ingreso');
                    isValid = false;
                }
                
                // Validación de imagen
                const imageInput = document.getElementById('image');
                if (imageInput.files.length > 0) {
                    const validation = validateImage(imageInput);
                    if (!validation.isValid) {
                        $('#imageError').text(validation.message);
                        isValid = false;
                    }
                }
    
                return isValid;
            }

            // Funciones auxiliares de validación
            function isValidDate(dateInput) {
                return dateInput.value !== '';
            }

            function isDateBefore(startDateInput, endDateInput) {
                if (!startDateInput.value || !endDateInput.value) return true;
                
                const startDate = new Date(startDateInput.value);
                const endDate = new Date(endDateInput.value);
                
                return startDate < endDate;
            }

            function displayErrors(errors) {
                // Limpiar errores anteriores
                $('.error-message').text('');
                $('.form-control').removeClass('is-invalid');
                
                // Mostrar nuevos errores
                for (const [field, messages] of Object.entries(errors)) {
                    const $field = $(`#${field}`);
                    const $error = $(`#${field}Error`);
                    
                    $field.addClass('is-invalid');
                    
                    // Mensaje personalizado para código duplicado
                    if (field === 'code' && messages[0].includes('already been taken')) {
                        $error.text('El código ingresado ya existe. Por favor utilice otro código.');
                    } else {
                        $error.text(messages[0]);
                    }
                }
            }

            // Botón agregar producto
            $('#addProductBtn').click(function() {
                currentProductId = null;
                loadProductForm();
                $('#modalTitle').text('Agregar Producto');
                showModal(productModal);
            });

            // Confirmar eliminación
            $('#confirmDeleteBtn').click(function() {
                $.ajax({
                    url: `/product/${currentProductId}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            showSuccessMessage('Producto eliminado correctamente');
                            hideModal(confirmModal);
                            loadProducts();
                        }
                    },
                    error: function(xhr) {
                        showErrorMessage('Error al eliminar el producto');
                    }
                });
            });

            // Cargar productos inicialmente
            loadProducts();

        });

        $(document).on('change', '#image', function() {
            let validation = validateImage(this);
            let $error = $('#imageError');
            
            if (!validation.isValid) {
                $error.text(validation.message);
                $(this).val(''); // Limpiar el input
            } else {
                $error.text('');
            }
        });

        function validateImage(input) {
            let file = input.files[0];
            if (!file) return true; // Si no hay archivo, no hay error
            
            // Validar tipo de archivo
            const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                return {
                    isValid: false,
                    message: 'Formato de imagen no válido. Solo se permiten JPEG, PNG, JPG o GIF'
                };
            }
            
            // Validar tamaño (1.5MB = 1572864 bytes)
            if (file.size > 1572864) {
                return {
                    isValid: false,
                    message: 'La imagen no debe exceder 1.5MB'
                };
            }
            
            return { isValid: true };
        }

        function showMessage(type, message) {
            // Eliminar mensajes anteriores
            $('.alert-message').remove();
            
            // Crear el elemento del mensaje
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const $message = $(`
                <div class="alert-message ${alertClass}">
                    ${message}
                    <span class="close-message">&times;</span>
                </div>
            `);
            
            // Agregar al DOM
            $('body').prepend($message);
            
            // Animación de aparición
            $message.hide().fadeIn(300);
            
            // Auto-ocultar después de 5 segundos
            setTimeout(() => {
                $message.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
            
            // Cerrar al hacer clic en la X
            $message.find('.close-message').click(function() {
                $message.fadeOut(300, function() {
                    $(this).remove();
                });
            });
        }

        // Función específica para éxito (opcional)
        function showSuccessMessage(message) {
            showMessage('success', message);
        }

        // Función específica para error (opcional)
        function showErrorMessage(message) {
            showMessage('error', message);
        }
    </script>
    @endsection
</x-app-layout>
