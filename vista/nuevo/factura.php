<?php
if(!isset($_SESSION["validarIngreso"])){
    echo '<script>window.location = "index.php?pagina=login";</script>';
    return;
}else{
    if($_SESSION["validarIngreso"] != "ok"){
        echo '<script>window.location = "index.php?pagina=login";</script>';
        return;
    }   
}

/* Removed caja open check as caja is no longer used */
// Verificar que hay caja abierta
// $cajaAbierta = CajaControlador::ctrVerificarCajaAbierta();
// if (!$cajaAbierta) {
//     echo '<script>
//         Swal.fire({
//             icon: "warning",
//             title: "Caja Cerrada",
//             text: "Debe abrir la caja antes de facturar",
//             confirmButtonText: "Abrir Caja"
//         }).then((result) => {
//             if (result.value) {
//                 window.location = "index.php?pagina=caja/apertura";
//             } else {
//                 window.location = "index.php?pagina=inicio";
//             }
//         });
//     </script>';
//     return;
// }

$clientes = ClienteControlador::buscarCliente();
$productos = ProductoControlador::ctrListarProductos();
$vehiculos = VehiculoControlador::ctrListarVehiculos();

// Si viene desde una orden o presupuesto
$orden = null;
$presupuesto = null;
if (isset($_GET['desde_orden'])) {
    $orden = OrdenTrabajoControlador::ctrObtenerOrdenTrabajo($_GET['desde_orden']);
}
if (isset($_GET['desde_presupuesto'])) {
    $presupuesto = PresupuestoControlador::ctrObtenerPresupuesto($_GET['desde_presupuesto']);
}
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">Nueva Factura</h2>
            
            <?php if ($orden): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Facturando Orden de Trabajo #<?php echo $orden['id_orden']; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($presupuesto): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Facturando Presupuesto #<?php echo $presupuesto['id_presupuesto']; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" id="facturaForm">
                <!-- Campos ocultos para orden/presupuesto -->
                <?php if ($orden): ?>
                    <input type="hidden" name="id_orden" value="<?php echo $orden['id_orden']; ?>">
                <?php endif; ?>
                <?php if ($presupuesto): ?>
                    <input type="hidden" name="id_presupuesto" value="<?php echo $presupuesto['id_presupuesto']; ?>">
                <?php endif; ?>
                
                <!-- Información Legal y Timbrado -->
                <!-- Eliminado el formulario para datos estáticos de la empresa ya que se obtienen desde la base de datos -->

                <!-- Información del Cliente -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Información del Cliente</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="id_cliente" class="form-label">Cliente</label>
                                    <select class="form-select" id="id_cliente" name="id_cliente" required>
                                        <option value="">Seleccione un cliente</option>
                                        <?php foreach ($clientes as $cliente): ?>
                                            <option value="<?php echo $cliente['id_cliente']; ?>"
                                                <?php 
                                                if ($orden && isset($orden['id_cliente'])) {
                                                    echo ($cliente['id_cliente'] == $orden['id_cliente']) ? 'selected' : '';
                                                } elseif ($presupuesto && isset($presupuesto['id_cliente'])) {
                                                    echo ($cliente['id_cliente'] == $presupuesto['id_cliente']) ? 'selected' : '';
                                                }
                                                ?>>
                                                <?php echo $cliente['nombre'] . ' ' . $cliente['apellido'] . ' - ' . $cliente['cedula']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="tipo_factura" class="form-label">Tipo de Factura</label>
                                    <select class="form-select" id="tipo_factura" name="tipo_factura" required>
                                        <option value="contado">Contado</option>
                                        <option value="credito">Crédito</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="metodo_pago" class="form-label">Método de Pago</label>
                                    <select class="form-select" id="metodo_pago" name="metodo_pago" required>
                                        <option value="efectivo">Efectivo</option>
                                        <option value="tarjeta">Tarjeta</option>
                                        <option value="transferencia">Transferencia</option>
                                        <option value="cheque">Cheque</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección de Productos -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Productos</h5>
                    </div>
                    <div class="card-body">
                        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#productoModal">
                            <i class="bi bi-plus-circle"></i> Agregar Producto
                        </button>
                    </div>
                </div>

                <!-- Sección de Servicios -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Servicios</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check mb-3">
                                    <input class="form-check-input servicio-check" type="checkbox" id="cambio_aceite" data-servicio="cambio_aceite">
                                    <label class="form-check-label" for="cambio_aceite">Cambio de Aceite</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mb-3">
                                    <input class="form-check-input servicio-check" type="checkbox" id="frenos" data-servicio="frenos">
                                    <label class="form-check-label" for="frenos">Servicio de Frenos</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mb-3">
                                    <input class="form-check-input servicio-check" type="checkbox" id="suspension" data-servicio="suspension">
                                    <label class="form-check-label" for="suspension">Suspensión</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mb-3">
                                    <input class="form-check-input servicio-check" type="checkbox" id="motor" data-servicio="motor">
                                    <label class="form-check-label" for="motor">Reparación de Motor</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mb-3">
                                    <input class="form-check-input servicio-check" type="checkbox" id="electricidad" data-servicio="electricidad">
                                    <label class="form-check-label" for="electricidad">Sistema Eléctrico</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mb-3">
                                    <input class="form-check-input servicio-check" type="checkbox" id="diagnostico" data-servicio="diagnostico">
                                    <label class="form-check-label" for="diagnostico">Diagnóstico General</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detalle de la Factura -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Detalle de la Factura</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table" id="detallesTable">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Descripción</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unitario</th>
                                        <th>Descuento</th>
                                        <th>Subtotal</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Los items se cargarán dinámicamente -->
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Totales -->
                        <div class="row mt-3">
                            <div class="col-md-8"></div>
                            <div class="col-md-4">
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Subtotal:</strong></td>
                                        <td class="text-end"><span id="subtotal">₲ 0</span></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <label for="descuento">Descuento:</label>
                                            <input type="number" class="form-control form-control-sm" id="descuento" name="descuento" value="0" min="0">
                                        </td>
                                        <td class="text-end"><span id="descuento_display">₲ 0</span></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <label for="iva">IVA:</label>
                                            <input type="number" class="form-control form-control-sm" id="iva" name="iva" value="0" min="0">
                                        </td>
                                        <td class="text-end"><span id="iva_display">₲ 0</span></td>
                                    </tr>
                                    <tr class="table-dark">
                                        <td><strong>Total:</strong></td>
                                        <td class="text-end"><strong><span id="total">₲ 0</span></strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Observaciones y Estado -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select" id="estado" name="estado" required>
                                <option value="pendiente">Pendiente</option>
                                <option value="pagada">Pagada</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Campos ocultos -->
                <input type="hidden" name="detalles" id="detallesInput">

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-receipt"></i> Crear Factura
                    </button>
                    <a href="index.php?pagina=tabla/facturas" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>

            <?php 
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_cliente'])) {
                $registro = FacturaControlador::ctrRegistrarFactura();
            }
            ?>
        </div>
    </div>
</div>

<!-- Modal para Productos -->
<div class="modal fade" id="productoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="productoForm">
                    <div class="mb-3">
                        <label for="id_producto" class="form-label">Producto</label>
                        <select class="form-select" id="id_producto" required>
                            <option value="">Seleccione un producto</option>
                            <?php foreach ($productos as $producto): ?>
                                <option value="<?php echo $producto['id_producto']; ?>" 
                                        data-precio="<?php echo $producto['precio']; ?>"
                                        data-nombre="<?php echo $producto['nombre']; ?>"
                                        data-stock="<?php echo $producto['stock']; ?>">
                                    <?php echo $producto['nombre'] . ' - ' . $producto['codigo'] . ' (Stock: ' . $producto['stock'] . ')'; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="cantidad_producto" class="form-label">Cantidad</label>
                        <input type="number" class="form-control" id="cantidad_producto" value="1" min="1" required>
                        <small id="stock_info" class="text-muted"></small>
                    </div>
                    <div class="mb-3">
                        <label for="precio_producto" class="form-label">Precio Unitario (₲)</label>
                        <input type="number" class="form-control" id="precio_producto" required>
                    </div>
                    <div class="mb-3">
                        <label for="descuento_producto" class="form-label">Descuento (₲)</label>
                        <input type="number" class="form-control" id="descuento_producto" value="0" min="0">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="agregarProducto">Agregar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Servicios -->
<div class="modal fade" id="servicioModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles del Servicio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="servicioForm">
                    <input type="hidden" id="tipo_servicio" name="tipo_servicio">
                    <div class="mb-3">
                        <label for="descripcion_servicio" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion_servicio" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="cantidad_servicio" class="form-label">Cantidad</label>
                        <input type="number" class="form-control" id="cantidad_servicio" value="1" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="precio_servicio" class="form-label">Precio Unitario (₲)</label>
                        <input type="number" class="form-control" id="precio_servicio" required>
                    </div>
                    <div class="mb-3">
                        <label for="descuento_servicio" class="form-label">Descuento (₲)</label>
                        <input type="number" class="form-control" id="descuento_servicio" value="0" min="0">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="agregarServicio">Agregar</button>
            </div>
        </div>
    </div>
</div>

<style>
    body {
        background-color: white;
    }
    h2 {
        font-family: "Copperplate", Fantasy;
        color: red;
    }
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    .card-header {
        background-color: #f8f9fa;
    }
    .table th {
        background-color: #f8f9fa;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productoModal = new bootstrap.Modal(document.getElementById('productoModal'));
    const servicioModal = new bootstrap.Modal(document.getElementById('servicioModal'));
    let detalles = [];

    // Datos predefinidos si viene de orden o presupuesto
    <?php if ($orden && isset($orden['detalles'])): ?>
        const detallesOrden = <?php echo json_encode($orden['detalles']); ?>;
        detallesOrden.forEach(detalle => {
            detalles.push({
                tipo: 'servicio',
                id_producto: null,
                descripcion: detalle.descripcion,
                cantidad: parseInt(detalle.cantidad),
                precio_unitario: parseFloat(detalle.precio_unitario),
                descuento: 0,
                subtotal: parseFloat(detalle.subtotal)
            });
        });
        actualizarTabla();
    <?php endif; ?>

    <?php if ($presupuesto && isset($presupuesto['detalles'])): ?>
        const detallesPresupuesto = <?php echo json_encode($presupuesto['detalles']); ?>;
        detallesPresupuesto.forEach(detalle => {
            detalles.push({
                tipo: detalle.tipo,
                id_producto: detalle.id_producto,
                descripcion: detalle.descripcion,
                cantidad: parseInt(detalle.cantidad),
                precio_unitario: parseFloat(detalle.precio_unitario),
                descuento: 0,
                subtotal: parseFloat(detalle.subtotal)
            });
        });
        actualizarTabla();
    <?php endif; ?>

    // Manejar clicks en checkboxes de servicios
    document.querySelectorAll('.servicio-check').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('tipo_servicio').value = this.dataset.servicio;
                servicioModal.show();
            } else {
                eliminarServicioPorTipo(this.dataset.servicio);
            }
        });
    });

    // Actualizar precio y stock cuando se selecciona un producto
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

    // Agregar producto
    document.getElementById('agregarProducto').addEventListener('click', function() {
        const productoSelect = document.getElementById('id_producto');
        const option = productoSelect.options[productoSelect.selectedIndex];
        const cantidad = parseInt(document.getElementById('cantidad_producto').value);
        const precioUnitario = parseFloat(document.getElementById('precio_producto').value);
        const descuento = parseFloat(document.getElementById('descuento_producto').value);
        const stock = parseInt(option.dataset.stock);
        
        if (!productoSelect.value || !cantidad || !precioUnitario) {
            alert('Por favor complete todos los campos');
            return;
        }

        if (cantidad > stock) {
            alert(`Solo hay ${stock} unidades en stock`);
            return;
        }

        const subtotal = (cantidad * precioUnitario) - descuento;

        const detalle = {
            tipo: 'producto',
            id_producto: productoSelect.value,
            descripcion: option.dataset.nombre,
            cantidad: cantidad,
            precio_unitario: precioUnitario,
            descuento: descuento,
            subtotal: subtotal
        };

        agregarDetalle(detalle);
        productoModal.hide();
        document.getElementById('productoForm').reset();
        document.getElementById('stock_info').textContent = '';
    });

    // Agregar servicio
    document.getElementById('agregarServicio').addEventListener('click', function() {
        const tipoServicio = document.getElementById('tipo_servicio').value;
        const descripcion = document.getElementById('descripcion_servicio').value;
        const cantidad = parseInt(document.getElementById('cantidad_servicio').value);
        const precioUnitario = parseFloat(document.getElementById('precio_servicio').value);
        const descuento = parseFloat(document.getElementById('descuento_servicio').value);
        
        if (!descripcion || !cantidad || !precioUnitario) {
            alert('Por favor complete todos los campos');
            return;
        }

        const subtotal = (cantidad * precioUnitario) - descuento;

        const detalle = {
            tipo: 'servicio',
            tipo_servicio: tipoServicio,
            id_producto: null,
            descripcion: descripcion,
            cantidad: cantidad,
            precio_unitario: precioUnitario,
            descuento: descuento,
            subtotal: subtotal
        };

        agregarDetalle(detalle);
        servicioModal.hide();
        document.getElementById('servicioForm').reset();
    });

    // Eventos para recalcular totales
    document.getElementById('descuento').addEventListener('input', calcularTotales);
    document.getElementById('iva').addEventListener('input', calcularTotales);

    function agregarDetalle(detalle) {
        detalles.push(detalle);
        actualizarTabla();
    }

    function eliminarDetalle(index) {
        const detalle = detalles[index];
        if (detalle.tipo === 'servicio' && detalle.tipo_servicio) {
            const checkbox = document.querySelector(`input[data-servicio="${detalle.tipo_servicio}"]`);
            if (checkbox) checkbox.checked = false;
        }
        detalles.splice(index, 1);
        actualizarTabla();
    }

    function eliminarServicioPorTipo(tipoServicio) {
        detalles = detalles.filter(d => d.tipo !== 'servicio' || d.tipo_servicio !== tipoServicio);
        actualizarTabla();
    }

    function actualizarTabla() {
        const tbody = document.querySelector('#detallesTable tbody');
        tbody.innerHTML = '';

        detalles.forEach((detalle, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${capitalizar(detalle.tipo)}</td>
                <td>${detalle.descripcion}</td>
                <td>${detalle.cantidad}</td>
                <td>₲ ${formatearNumero(detalle.precio_unitario)}</td>
                <td>₲ ${formatearNumero(detalle.descuento)}</td>
                <td>₲ ${formatearNumero(detalle.subtotal)}</td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm" onclick="eliminarDetalle(${index})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        calcularTotales();
        document.getElementById('detallesInput').value = JSON.stringify(detalles);
    }

    function calcularTotales() {
        const subtotal = detalles.reduce((sum, detalle) => sum + detalle.subtotal, 0);
        const descuento = parseFloat(document.getElementById('descuento').value) || 0;
        const iva = parseFloat(document.getElementById('iva').value) || 0;
        const total = subtotal - descuento + iva;

        document.getElementById('subtotal').textContent = `₲ ${formatearNumero(subtotal)}`;
        document.getElementById('descuento_display').textContent = `₲ ${formatearNumero(descuento)}`;
        document.getElementById('iva_display').textContent = `₲ ${formatearNumero(iva)}`;
        document.getElementById('total').textContent = `₲ ${formatearNumero(total)}`;
    }

    function capitalizar(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    function formatearNumero(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Hacer las funciones globales
    window.eliminarDetalle = eliminarDetalle;

    // Validación del formulario
    document.getElementById('facturaForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const clienteId = document.getElementById('id_cliente').value;
        
        if (!clienteId) {
            alert('Debe seleccionar un cliente');
            return;
        }
        
        if (detalles.length === 0) {
            alert('Debe agregar al menos un producto o servicio');
            return;
        }
        
        this.submit();
    });
});
</script>