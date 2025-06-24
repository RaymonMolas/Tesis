<?php
if (!isset($_SESSION["validarIngreso"])) {
    echo '<script>window.location = "index.php?pagina=login";</script>';
    return;
} else {
    if ($_SESSION["validarIngreso"] != "ok") {
        echo '<script>window.location = "index.php?pagina=login";</script>';
        return;
    }
}

$clientes = ClienteControlador::buscarCliente();
$vehiculos = VehiculoControlador::ctrListarVehiculos();
$productos = ProductoControlador::ctrListarProductos();
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-calculator"></i> Nuevo Presupuesto - Motor Service
                    </h4>
                </div>
                <div class="card-body">
                    <form method="post" id="formPresupuesto">
                        
                        <!-- Información del Vehículo y Cliente -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="bi bi-car-front"></i> Información del Vehículo</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="id_cliente" class="form-label">Cliente</label>
                                            <select class="form-select" id="id_cliente" name="id_cliente" required>
                                                <option value="">Seleccione un cliente</option>
                                                <?php foreach ($clientes as $cliente): ?>
                                                        <option value="<?php echo $cliente['id_cliente']; ?>">
                                                            <?php echo $cliente['nombre'] . ' ' . $cliente['apellido']; ?>
                                                            <?php if (!empty($cliente['ruc'])): ?>
                                                                    - RUC: <?php echo $cliente['ruc']; ?>
                                                            <?php else: ?>
                                                                    - CI: <?php echo $cliente['cedula']; ?>
                                                            <?php endif; ?>
                                                        </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="id_vehiculo" class="form-label">Vehículo</label>
                                            <select class="form-select" id="id_vehiculo" name="id_vehiculo" required>
                                                <option value="">Primero seleccione un cliente</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="fecha_validez" class="form-label">Fecha de Validez</label>
                                            <input type="date" class="form-control" id="fecha_validez" name="fecha_validez" 
                                                   value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" required>
                                            <div class="form-text">Fecha hasta la cual el presupuesto es válido</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="observaciones" class="form-label">Observaciones</label>
                                            <textarea class="form-control" id="observaciones" name="observaciones" rows="2"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SERVICIOS ESPECÍFICOS ACTUALIZADOS -->
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5><i class="bi bi-gear-fill"></i> Servicios Automotrices Especializados</h5>
                                <small>Seleccione los servicios que se incluirán en el presupuesto</small>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <!-- Aceites -->
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input servicio-check" type="checkbox" id="aceite_motor" data-servicio="aceite_motor">
                                            <label class="form-check-label" for="aceite_motor">
                                                <i class="bi bi-droplet-fill text-warning"></i> Aceite Motor
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input servicio-check" type="checkbox" id="aceite_dif_trasero" data-servicio="aceite_dif_trasero">
                                            <label class="form-check-label" for="aceite_dif_trasero">
                                                <i class="bi bi-droplet-fill text-warning"></i> Aceite Dif. Trasero
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input servicio-check" type="checkbox" id="aceite_dif_delantero" data-servicio="aceite_dif_delantero">
                                            <label class="form-check-label" for="aceite_dif_delantero">
                                                <i class="bi bi-droplet-fill text-warning"></i> Aceite Dif. Delantero
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input servicio-check" type="checkbox" id="aceite_caja" data-servicio="aceite_caja">
                                            <label class="form-check-label" for="aceite_caja">
                                                <i class="bi bi-droplet-fill text-warning"></i> Aceite Caja
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input servicio-check" type="checkbox" id="aceite_reductora" data-servicio="aceite_reductora">
                                            <label class="form-check-label" for="aceite_reductora">
                                                <i class="bi bi-droplet-fill text-warning"></i> Aceite Reductora
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <!-- Filtros -->
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input servicio-check" type="checkbox" id="filtro_aire" data-servicio="filtro_aire">
                                            <label class="form-check-label" for="filtro_aire">
                                                <i class="bi bi-funnel text-info"></i> Filtro de Aire
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input servicio-check" type="checkbox" id="filtro_aceite" data-servicio="filtro_aceite">
                                            <label class="form-check-label" for="filtro_aceite">
                                                <i class="bi bi-funnel text-info"></i> Filtro de Aceite
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input servicio-check" type="checkbox" id="filtro_combustible" data-servicio="filtro_combustible">
                                            <label class="form-check-label" for="filtro_combustible">
                                                <i class="bi bi-funnel text-info"></i> Filtro de Combustible
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <!-- Otros Servicios -->
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input servicio-check" type="checkbox" id="bujias" data-servicio="bujias">
                                            <label class="form-check-label" for="bujias">
                                                <i class="bi bi-lightning-fill text-warning"></i> Bujías
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input servicio-check" type="checkbox" id="liquidos" data-servicio="liquidos">
                                            <label class="form-check-label" for="liquidos">
                                                <i class="bi bi-droplet text-primary"></i> Líquidos
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input servicio-check" type="checkbox" id="mano_obra" data-servicio="mano_obra">
                                            <label class="form-check-label" for="mano_obra">
                                                <i class="bi bi-tools text-success"></i> Mano de Obra
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input servicio-check" type="checkbox" id="otro" data-servicio="otro">
                                            <label class="form-check-label" for="otro">
                                                <i class="bi bi-three-dots text-secondary"></i> Otro...
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Productos Adicionales -->
                        <div class="card mb-4">
                            <div class="card-header bg-success text-white">
                                <h5><i class="bi bi-box-seam"></i> Productos Adicionales</h5>
                                <small>Agregue productos específicos que no están incluidos en los servicios</small>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="id_producto" class="form-label">Producto</label>
                                            <select class="form-select" id="id_producto">
                                                <option value="">Seleccione un producto</option>
                                                <?php foreach ($productos as $producto): ?>
                                                        <option value="<?php echo $producto['id_producto']; ?>" 
                                                                data-nombre="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                                                data-precio="<?php echo $producto['precio_venta']; ?>"
                                                                data-stock="<?php echo $producto['stock_actual']; ?>">
                                                            <?php echo $producto['codigo_producto'] . ' - ' . $producto['nombre']; ?> 
                                                            (Stock: <?php echo $producto['stock_actual']; ?>)
                                                        </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="cantidad_producto" class="form-label">Cantidad</label>
                                            <input type="number" class="form-control" id="cantidad_producto" min="1" value="1">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="precio_producto" class="form-label">Precio (Gs.)</label>
                                            <input type="number" class="form-control" id="precio_producto" step="1000" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="button" class="btn btn-success" id="agregarProducto">
                                            <i class="bi bi-plus-circle"></i> Agregar Producto
                                        </button>
                                        <small class="form-text text-muted ms-3" id="stock_info"></small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Detalle del Presupuesto -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="bi bi-list-task"></i> Detalle del Presupuesto</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="presupuestoTable">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Tipo</th>
                                                <th>Descripción</th>
                                                <th>Cantidad</th>
                                                <th>Precio Unitario</th>
                                                <th>Subtotal</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Los items se agregarán aquí dinámicamente -->
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-info">
                                                <th colspan="4" class="text-end">TOTAL PRESUPUESTO:</th>
                                                <th id="totalPresupuesto">Gs. 0</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div class="alert alert-info mt-3" id="alertaPresupuesto" style="display: none;">
                                    <i class="bi bi-info-circle"></i> Agregue servicios o productos para generar el presupuesto.
                                </div>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="index.php?pagina=tabla/presupuestos" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> Volver
                                    </a>
                                    <button type="submit" class="btn btn-info btn-lg" id="btnGuardarPresupuesto">
                                        <i class="bi bi-save"></i> Crear Presupuesto
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Campos ocultos -->
                        <input type="hidden" id="servicios" name="servicios" value="">
                        <input type="hidden" id="productos_lista" name="productos_lista" value="">
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Detalle de Servicio -->
<div class="modal fade" id="servicioModal" tabindex="-1" aria-labelledby="servicioModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="servicioModalLabel">Detalle del Servicio</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="servicioForm">
                    <input type="hidden" id="tipo_servicio" name="tipo_servicio">
                    
                    <div class="mb-3">
                        <label for="descripcion_servicio" class="form-label">Descripción del Servicio</label>
                        <textarea class="form-control" id="descripcion_servicio" name="descripcion_servicio" rows="3" required></textarea>
                        <div class="form-text">Detalle específico del servicio para el presupuesto</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="cantidad" class="form-label">Cantidad</label>
                                <input type="number" class="form-control" id="cantidad" name="cantidad" min="1" value="1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="precio_unitario" class="form-label">Precio Unitario (Gs.)</label>
                                <input type="number" class="form-control" id="precio_unitario" name="precio_unitario" min="0" step="1000" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="subtotal_servicio" class="form-label">Subtotal</label>
                        <input type="text" class="form-control" id="subtotal_servicio" readonly>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="agregarServicio">Agregar al Presupuesto</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const servicioModal = new bootstrap.Modal(document.getElementById('servicioModal'));
    const vehiculos = <?php echo json_encode($vehiculos); ?>;
    let itemsPresupuesto = [];

    // Cargar vehículos según cliente seleccionado
    document.getElementById('id_cliente').addEventListener('change', function() {
        const clienteId = this.value;
        const vehiculoSelect = document.getElementById('id_vehiculo');
        
        vehiculoSelect.innerHTML = '<option value="">Seleccione un vehículo</option>';
        
        if (clienteId) {
            const vehiculosCliente = vehiculos.filter(v => v.id_cliente == clienteId);
            vehiculosCliente.forEach(vehiculo => {
                const option = document.createElement('option');
                option.value = vehiculo.id_vehiculo;
                option.textContent = `${vehiculo.marca} ${vehiculo.modelo} (${vehiculo.matricula})`;
                vehiculoSelect.appendChild(option);
            });
        }
    });

    // Manejar selección de servicios
    document.querySelectorAll('.servicio-check').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('tipo_servicio').value = this.dataset.servicio;
                document.getElementById('servicioModalLabel').textContent = 
                    'Presupuesto de Servicio: ' + this.nextElementSibling.textContent.trim();
                
                // Limpiar formulario
                document.getElementById('servicioForm').reset();
                document.getElementById('tipo_servicio').value = this.dataset.servicio;
                document.getElementById('cantidad').value = 1;
                
                servicioModal.show();
            } else {
                // Remover servicio si se desmarca
                eliminarItem('servicio', this.dataset.servicio);
            }
        });
    });

    // Calcular subtotal automáticamente
    document.getElementById('cantidad').addEventListener('input', calcularSubtotal);
    document.getElementById('precio_unitario').addEventListener('input', calcularSubtotal);

    function calcularSubtotal() {
        const cantidad = parseInt(document.getElementById('cantidad').value) || 0;
        const precio = parseInt(document.getElementById('precio_unitario').value) || 0;
        const subtotal = cantidad * precio;
        document.getElementById('subtotal_servicio').value = 'Gs. ' + subtotal.toLocaleString();
    }

    // Agregar servicio al presupuesto
    document.getElementById('agregarServicio').addEventListener('click', function() {
        const tipoServicio = document.getElementById('tipo_servicio').value;
        const descripcion = document.getElementById('descripcion_servicio').value;
        const cantidad = parseInt(document.getElementById('cantidad').value);
        const precioUnitario = parseInt(document.getElementById('precio_unitario').value);
        const subtotal = cantidad * precioUnitario;

        if (!descripcion || !cantidad || !precioUnitario) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos incompletos',
                text: 'Por favor complete todos los campos del servicio'
            });
            return;
        }

        // Remover servicio existente si ya estaba
        itemsPresupuesto = itemsPresupuesto.filter(item => !(item.tipo === 'servicio' && item.identificador === tipoServicio));

        // Agregar nuevo servicio
        itemsPresupuesto.push({
            tipo: 'servicio',
            identificador: tipoServicio,
            descripcion: descripcion,
            cantidad: cantidad,
            precioUnitario: precioUnitario,
            subtotal: subtotal
        });

        actualizarTablaPresupuesto();
        servicioModal.hide();
        
        // Mantener checkbox marcado
        document.querySelector(`[data-servicio="${tipoServicio}"]`).checked = true;
    });

    // Manejar selección de productos
    document.getElementById('id_producto').addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (option.value) {
            document.getElementById('precio_producto').value = option.dataset.precio;
            document.getElementById('stock_info').textContent = `Stock disponible: ${option.dataset.stock}`;
            
            // Validar cantidad según stock
            const cantidadInput = document.getElementById('cantidad_producto');
            cantidadInput.max = option.dataset.stock;
        } else {
            document.getElementById('precio_producto').value = '';
            document.getElementById('stock_info').textContent = '';
        }
    });

    // Agregar producto al presupuesto
    document.getElementById('agregarProducto').addEventListener('click', function() {
        const productoSelect = document.getElementById('id_producto');
        const option = productoSelect.options[productoSelect.selectedIndex];
        const cantidad = parseInt(document.getElementById('cantidad_producto').value);
        const precioUnitario = parseInt(document.getElementById('precio_producto').value);
        const stock = parseInt(option.dataset.stock);
        
        if (!productoSelect.value || !cantidad || !precioUnitario) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos incompletos',
                text: 'Por favor seleccione un producto y complete la cantidad'
            });
            return;
        }

        if (cantidad > stock) {
            Swal.fire({
                icon: 'warning',
                title: 'Stock insuficiente',
                text: `Solo hay ${stock} unidades disponibles`
            });
            return;
        }

        const subtotal = cantidad * precioUnitario;

        const item = {
            tipo: 'producto',
            identificador: productoSelect.value,
            descripcion: option.dataset.nombre,
            cantidad: cantidad,
            precioUnitario: precioUnitario,
            subtotal: subtotal
        };

        itemsPresupuesto.push(item);
        actualizarTablaPresupuesto();
        
        // Limpiar formulario
        document.getElementById('id_producto').value = '';
        document.getElementById('cantidad_producto').value = 1;
        document.getElementById('precio_producto').value = '';
        document.getElementById('stock_info').textContent = '';
    });

    function actualizarTablaPresupuesto() {
        const tbody = document.querySelector('#presupuestoTable tbody');
        tbody.innerHTML = '';
        let totalPresupuesto = 0;

        itemsPresupuesto.forEach((item, index) => {
            const row = document.createElement('tr');
            const tipoLabel = item.tipo === 'servicio' ? 'Servicio' : 'Producto';
            const tipoBadge = item.tipo === 'servicio' ? 'bg-primary' : 'bg-success';
            
            row.innerHTML = `
                <td><span class="badge ${tipoBadge}">${tipoLabel}</span></td>
                <td>${item.descripcion}</td>
                <td class="text-center">${item.cantidad}</td>
                <td class="text-end">Gs. ${item.precioUnitario.toLocaleString()}</td>
                <td class="text-end">Gs. ${item.subtotal.toLocaleString()}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarItem('${item.tipo}', '${item.identificador}')">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
            totalPresupuesto += item.subtotal;
        });

        document.getElementById('totalPresupuesto').textContent = 'Gs. ' + totalPresupuesto.toLocaleString();
        
        // Actualizar campos ocultos
        const servicios = itemsPresupuesto.filter(item => item.tipo === 'servicio');
        const productos = itemsPresupuesto.filter(item => item.tipo === 'producto');
        
        document.getElementById('servicios').value = JSON.stringify(servicios);
        document.getElementById('productos_lista').value = JSON.stringify(productos);
        
        // Mostrar/ocultar alerta
        const alerta = document.getElementById('alertaPresupuesto');
        if (itemsPresupuesto.length === 0) {
            alerta.style.display = 'block';
        } else {
            alerta.style.display = 'none';
        }
    }

    // Función global para eliminar item
    window.eliminarItem = function(tipo, identificador) {
        itemsPresupuesto = itemsPresupuesto.filter(item => 
            !(item.tipo === tipo && item.identificador === identificador)
        );
        
        if (tipo === 'servicio') {
            document.querySelector(`[data-servicio="${identificador}"]`).checked = false;
        }
        
        actualizarTablaPresupuesto();
    };

    // Validar formulario antes de enviar
    document.getElementById('formPresupuesto').addEventListener('submit', function(e) {
        if (itemsPresupuesto.length === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Presupuesto vacío',
                text: 'Debe agregar al menos un servicio o producto para crear el presupuesto'
            });
            return;
        }

        // Confirmar creación
        e.preventDefault();
        const totalItems = itemsPresupuesto.length;
        const serviciosCount = itemsPresupuesto.filter(i => i.tipo === 'servicio').length;
        const productosCount = itemsPresupuesto.filter(i => i.tipo === 'producto').length;
        
        Swal.fire({
            title: '¿Crear Presupuesto?',
            html: `Se creará el presupuesto con:<br>
                   • ${serviciosCount} servicio(s)<br>
                   • ${productosCount} producto(s)`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#17a2b8',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, crear presupuesto',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });

    // Mostrar alerta inicial
    document.getElementById('alertaPresupuesto').style.display = 'block';
});
</script>

<?php
$registro = PresupuestoControlador::ctrRegistrarPresupuesto();
if ($registro == "ok") {
    echo '<script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>';
}
?>

<style>
.form-check-label {
    cursor: pointer;
    user-select: none;
}

.form-check-input:checked + .form-check-label {
    color: #17a2b8;
    font-weight: 500;
}

.card-header {
    border-bottom: 2px solid #dee2e6;
}

.table th {
    border-top: none;
}

.badge {
    font-size: 0.875em;
}

#presupuestoTable tbody tr:hover {
    background-color: #e7f3ff;
}

.modal-header {
    border-bottom: 2px solid rgba(255,255,255,0.2);
}

.alert-info {
    border-left: 4px solid #17a2b8;
}

.bg-info {
    background-color: #17a2b8 !important;
}

@media (max-width: 768px) {
    .col-md-4 {
        margin-bottom: 1rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
}
</style>