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
$productos = ProductoControlador::ctrListarProductos();
$vehiculos = VehiculoControlador::ctrListarVehiculos();
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">Nuevo Presupuesto</h2>
            <form method="post" id="presupuestoForm">
                <!-- Sección de Encabezado -->
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
                                            <option value="<?php echo $cliente['id_cliente']; ?>">
                                                <?php echo $cliente['nombre'] . ' ' . $cliente['apellido'] . ' - ' . $cliente['cedula']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="id_vehiculo" class="form-label">Vehículo</label>
                                    <select class="form-select" id="id_vehiculo" name="id_vehiculo" required>
                                        <option value="">Seleccione un vehículo</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fecha_validez" class="form-label">Válido Hasta</label>
                                    <input type="datetime-local" class="form-control" id="fecha_validez"
                                        name="fecha_validez" required>
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
                        <div class="row mb-3">
                            <div class="col-12">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#productoModal">
                                    <i class="bi bi-plus-circle"></i> Agregar Producto
                                </button>
                            </div>
                        </div>
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
                                    <input class="form-check-input servicio-check" type="checkbox" id="cambio_aceite"
                                        data-servicio="cambio_aceite">
                                    <label class="form-check-label" for="cambio_aceite">
                                        Cambio de Aceite
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mb-3">
                                    <input class="form-check-input servicio-check" type="checkbox" id="frenos"
                                        data-servicio="frenos">
                                    <label class="form-check-label" for="frenos">
                                        Servicio de Frenos
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mb-3">
                                    <input class="form-check-input servicio-check" type="checkbox" id="suspension"
                                        data-servicio="suspension">
                                    <label class="form-check-label" for="suspension">
                                        Suspensión
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mb-3">
                                    <input class="form-check-input servicio-check" type="checkbox" id="motor"
                                        data-servicio="motor">
                                    <label class="form-check-label" for="motor">
                                        Reparación de Motor
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mb-3">
                                    <input class="form-check-input servicio-check" type="checkbox" id="electricidad"
                                        data-servicio="electricidad">
                                    <label class="form-check-label" for="electricidad">
                                        Sistema Eléctrico
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mb-3">
                                    <input class="form-check-input servicio-check" type="checkbox" id="diagnostico"
                                        data-servicio="diagnostico">
                                    <label class="form-check-label" for="diagnostico">
                                        Diagnóstico General
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección de Detalle -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Detalle del Presupuesto</h5>
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
                                        <th>Subtotal</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Los items se agregarán dinámicamente -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                        <td><span id="total">₲ 0</span></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="observaciones" class="form-label">Observaciones</label>
                    <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
                </div>

                <input type="hidden" name="detalles" id="detallesInput">
                <input type="hidden" name="total" id="totalInput">

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Crear Presupuesto</button>
                    <a href="index.php?pagina=tabla/presupuestos" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>

            <?php
            // CORRECCIÓN: Solo ejecutar si se envió el formulario
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_vehiculo'])) {
                $registro = PresupuestoControlador::ctrRegistrarPresupuesto();
                if ($registro == "ok") {
                    echo '<script>
                        if (window.history.replaceState) {
                            window.history.replaceState(null, null, window.location.href);
                        }
                        window.location = "index.php?pagina=tabla/presupuestos";
                    </script>';
                }
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
                                    data-nombre="<?php echo $producto['nombre']; ?>">
                                    <?php echo $producto['nombre'] . ' - ' . $producto['codigo']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="cantidad_producto" class="form-label">Cantidad</label>
                        <input type="number" class="form-control" id="cantidad_producto" value="1" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="precio_producto" class="form-label">Precio Unitario (₲)</label>
                        <input type="number" class="form-control" id="precio_producto" required readonly>
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
    document.addEventListener('DOMContentLoaded', function () {
        const productoModal = new bootstrap.Modal(document.getElementById('productoModal'));
        const servicioModal = new bootstrap.Modal(document.getElementById('servicioModal'));
        let detalles = [];

        // CORRECCIÓN: Usar datos PHP en lugar de AJAX
        const todosVehiculos = <?php echo json_encode($vehiculos); ?>;

        // Actualizar vehículos cuando se selecciona un cliente
        document.getElementById('id_cliente').addEventListener('change', function () {
            const clienteId = this.value;
            const vehiculoSelect = document.getElementById('id_vehiculo');
            vehiculoSelect.innerHTML = '<option value="">Seleccione un vehículo</option>';

            if (clienteId) {
                // Filtrar vehículos por cliente
                const vehiculosCliente = todosVehiculos.filter(v => v.id_cliente == clienteId);

                vehiculosCliente.forEach(vehiculo => {
                    const option = document.createElement('option');
                    option.value = vehiculo.id_vehiculo;
                    option.textContent = `${vehiculo.marca} ${vehiculo.modelo} (${vehiculo.matricula})`;
                    vehiculoSelect.appendChild(option);
                });

                if (vehiculosCliente.length === 0) {
                    vehiculoSelect.innerHTML = '<option value="">No hay vehículos para este cliente</option>';
                }
            }
        });

        // Manejar clicks en checkboxes de servicios
        document.querySelectorAll('.servicio-check').forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                if (this.checked) {
                    document.getElementById('tipo_servicio').value = this.dataset.servicio;
                    servicioModal.show();
                } else {
                    // Remover servicio de la tabla
                    eliminarServicioPorTipo(this.dataset.servicio);
                }
            });
        });

        // Actualizar precio cuando se selecciona un producto
        document.getElementById('id_producto').addEventListener('change', function () {
            const option = this.options[this.selectedIndex];
            if (option.value) {
                document.getElementById('precio_producto').value = option.dataset.precio;
            } else {
                document.getElementById('precio_producto').value = '';
            }
        });

        // Agregar producto
        document.getElementById('agregarProducto').addEventListener('click', function () {
            const productoSelect = document.getElementById('id_producto');
            const option = productoSelect.options[productoSelect.selectedIndex];
            const cantidad = parseInt(document.getElementById('cantidad_producto').value);
            const precioUnitario = parseFloat(document.getElementById('precio_producto').value);

            if (!productoSelect.value || !cantidad || !precioUnitario) {
                alert('Por favor complete todos los campos');
                return;
            }

            const detalle = {
                tipo: 'producto',
                id_producto: productoSelect.value,
                descripcion: option.dataset.nombre,
                cantidad: cantidad,
                precio_unitario: precioUnitario,
                subtotal: cantidad * precioUnitario
            };

            agregarDetalle(detalle);
            productoModal.hide();
            document.getElementById('productoForm').reset();
        });

        // Agregar servicio
        document.getElementById('agregarServicio').addEventListener('click', function () {
            const tipoServicio = document.getElementById('tipo_servicio').value;
            const descripcion = document.getElementById('descripcion_servicio').value;
            const cantidad = parseInt(document.getElementById('cantidad_servicio').value);
            const precioUnitario = parseFloat(document.getElementById('precio_servicio').value);

            if (!descripcion || !cantidad || !precioUnitario) {
                alert('Por favor complete todos los campos');
                return;
            }

            const detalle = {
                tipo: 'servicio',
                tipo_servicio: tipoServicio,
                descripcion: descripcion,
                cantidad: cantidad,
                precio_unitario: precioUnitario,
                subtotal: cantidad * precioUnitario
            };

            agregarDetalle(detalle);
            servicioModal.hide();
            document.getElementById('servicioForm').reset();
        });

        function agregarDetalle(detalle) {
            detalles.push(detalle);
            actualizarTabla();
        }

        function eliminarDetalle(index) {
            const detalle = detalles[index];
            if (detalle.tipo === 'servicio') {
                // Desmarcar el checkbox correspondiente
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
            let total = 0;

            detalles.forEach((detalle, index) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                <td>${capitalizar(detalle.tipo)}</td>
                <td>${detalle.descripcion}</td>
                <td>${detalle.cantidad}</td>
                <td>₲ ${formatearNumero(detalle.precio_unitario)}</td>
                <td>₲ ${formatearNumero(detalle.subtotal)}</td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm" onclick="eliminarDetalle(${index})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
                tbody.appendChild(tr);
                total += detalle.subtotal;
            });

            document.getElementById('total').textContent = `₲ ${formatearNumero(total)}`;
            document.getElementById('totalInput').value = total;
            document.getElementById('detallesInput').value = JSON.stringify(detalles);
        }

        function capitalizar(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        function formatearNumero(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        // Hacer las funciones globales
        window.eliminarDetalle = eliminarDetalle;

        // Validación del formulario antes de enviar
        document.getElementById('presupuestoForm').addEventListener('submit', function (e) {
            e.preventDefault();

            // Validaciones
            const clienteId = document.getElementById('id_cliente').value;
            const vehiculoId = document.getElementById('id_vehiculo').value;
            const fechaValidez = document.getElementById('fecha_validez').value;

            if (!clienteId) {
                alert('Debe seleccionar un cliente');
                return;
            }

            if (!vehiculoId) {
                alert('Debe seleccionar un vehículo');
                return;
            }

            if (!fechaValidez) {
                alert('Debe establecer una fecha de validez');
                return;
            }

            if (detalles.length === 0) {
                alert('Debe agregar al menos un producto o servicio');
                return;
            }

            // Enviar formulario
            this.submit();
        });
    });
</script>