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

if(isset($_GET["id"])){
    $orden = OrdenTrabajoControlador::ctrObtenerOrdenTrabajo($_GET["id"]);
    $detalles = ModeloOrdenDetalle::mdlObtenerDetalles($_GET["id"]);
    $clientes = ClienteControlador::buscarCliente();
    $vehiculos = VehiculoControlador::ctrListarVehiculos();
} else {
    echo '<script>window.location = "index.php?pagina=tabla/orden_trabajo";</script>';
    return;
}
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">Editar Orden de Trabajo #<?php echo str_pad($orden['id_orden'], 6, '0', STR_PAD_LEFT); ?></h2>
            <form method="post" id="ordenTrabajoForm">
                <input type="hidden" name="id_orden" value="<?php echo $orden['id_orden']; ?>">
                
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
                                            <option value="<?php echo $cliente['id_cliente']; ?>" 
                                                <?php 
                                                // Encontrar el cliente del vehículo actual
                                                foreach($vehiculos as $v) {
                                                    if($v['id_vehiculo'] == $orden['id_vehiculo'] && $v['id_cliente'] == $cliente['id_cliente']) {
                                                        echo 'selected';
                                                        break;
                                                    }
                                                }
                                                ?>>
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
                                        <?php foreach ($vehiculos as $vehiculo): ?>
                                            <option value="<?php echo $vehiculo['id_vehiculo']; ?>" 
                                                <?php echo ($vehiculo['id_vehiculo'] == $orden['id_vehiculo']) ? 'selected' : ''; ?>>
                                                <?php echo $vehiculo['marca'] . ' ' . $vehiculo['modelo'] . ' (' . $vehiculo['matricula'] . ')'; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="fecha_entrada" class="form-label">Fecha de Entrada</label>
                                    <input type="datetime-local" class="form-control" id="fecha_entrada" name="fecha_entrada" 
                                           value="<?php echo date('Y-m-d\TH:i', strtotime($orden['fecha_ingreso'])); ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="fecha_salida" class="form-label">Fecha Estimada de Salida</label>
                                    <input type="datetime-local" class="form-control" id="fecha_salida" name="fecha_salida" 
                                           value="<?php echo date('Y-m-d\TH:i', strtotime($orden['fecha_salida'])); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="estado" class="form-label">Estado</label>
                                    <select class="form-select" id="estado" name="estado" required>
                                        <option value="en_proceso" <?php echo ($orden['estado'] == 'en_proceso') ? 'selected' : ''; ?>>En Proceso</option>
                                        <option value="completado" <?php echo ($orden['estado'] == 'completado') ? 'selected' : ''; ?>>Completado</option>
                                        <option value="cancelado" <?php echo ($orden['estado'] == 'cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                                    </select>
                                </div>
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
                                    <input class="form-check-input servicio-check" type="checkbox" id="cambio_aceite" data-servicio="cambio_aceite">
                                    <label class="form-check-label" for="cambio_aceite">
                                        Cambio de Aceite
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mb-3">
                                    <input class="form-check-input servicio-check" type="checkbox" id="frenos" data-servicio="frenos">
                                    <label class="form-check-label" for="frenos">
                                        Servicio de Frenos
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mb-3">
                                    <input class="form-check-input servicio-check" type="checkbox" id="suspension" data-servicio="suspension">
                                    <label class="form-check-label" for="suspension">
                                        Suspensión
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mb-3">
                                    <input class="form-check-input servicio-check" type="checkbox" id="motor" data-servicio="motor">
                                    <label class="form-check-label" for="motor">
                                        Reparación de Motor
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mb-3">
                                    <input class="form-check-input servicio-check" type="checkbox" id="electricidad" data-servicio="electricidad">
                                    <label class="form-check-label" for="electricidad">
                                        Sistema Eléctrico
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mb-3">
                                    <input class="form-check-input servicio-check" type="checkbox" id="diagnostico" data-servicio="diagnostico">
                                    <label class="form-check-label" for="diagnostico">
                                        Diagnóstico General
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección de Detalle de Servicios -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Detalle de Servicios</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table" id="serviciosTable">
                                <thead>
                                    <tr>
                                        <th>Servicio</th>
                                        <th>Descripción</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unitario</th>
                                        <th>Subtotal</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Los items se cargarán dinámicamente -->
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
                    <label for="observaciones" class="form-label">Observaciones Generales</label>
                    <textarea class="form-control" id="observaciones" name="observaciones" rows="3"><?php echo htmlspecialchars($orden['observaciones']); ?></textarea>
                </div>

                <!-- Campos ocultos para enviar datos -->
                <input type="hidden" name="servicios" id="serviciosInput">

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Actualizar Orden</button>
                    <a href="index.php?pagina=tabla/orden_trabajo" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>

            <?php 
            // Solo ejecutar si se envió el formulario
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_orden'])) {
                $actualizar = OrdenTrabajoControlador::ctrActualizarOrdenTrabajo();
                if($actualizar == "ok"){
                    echo '<script>
                        if (window.history.replaceState) {
                            window.history.replaceState(null, null, window.location.href);
                        }
                        window.location = "index.php?pagina=tabla/orden_trabajo";
                    </script>';
                }
            }
            ?>
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
                        <textarea class="form-control" id="descripcion_servicio" name="descripcion_servicio" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="cantidad" class="form-label">Cantidad</label>
                        <input type="number" class="form-control" id="cantidad" name="cantidad" value="1" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="precio_unitario" class="form-label">Precio Unitario (₲)</label>
                        <input type="number" class="form-control" id="precio_unitario" name="precio_unitario" required>
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
    const servicioModal = new bootstrap.Modal(document.getElementById('servicioModal'));
    let serviciosAgregados = [];
    const todosVehiculos = <?php echo json_encode($vehiculos); ?>;

    // Cargar datos existentes de la orden
    const detallesExistentes = <?php echo json_encode($detalles); ?>;

    // Cargar servicios existentes
    detallesExistentes.forEach(detalle => {
        const servicioFormateado = {
            tipo: detalle.tipo_servicio,
            descripcion: detalle.descripcion,
            cantidad: parseInt(detalle.cantidad),
            precioUnitario: parseFloat(detalle.precio_unitario),
            subtotal: parseFloat(detalle.subtotal)
        };
        
        serviciosAgregados.push(servicioFormateado);
    });

    // Marcar servicios existentes
    serviciosAgregados.forEach(servicio => {
        const checkbox = document.querySelector(`input[data-servicio="${servicio.tipo}"]`);
        if (checkbox) {
            checkbox.checked = true;
        }
    });

    // Actualizar tabla con datos existentes
    actualizarTablaServicios();

    // Actualizar vehículos cuando se selecciona un cliente
    document.getElementById('id_cliente').addEventListener('change', function() {
        const clienteId = this.value;
        const vehiculoSelect = document.getElementById('id_vehiculo');
        const vehiculoActual = vehiculoSelect.value; // Conservar selección actual si es posible
        
        vehiculoSelect.innerHTML = '<option value="">Seleccione un vehículo</option>';
        
        if (clienteId) {
            const vehiculosCliente = todosVehiculos.filter(v => v.id_cliente == clienteId);
            
            vehiculosCliente.forEach(vehiculo => {
                const option = document.createElement('option');
                option.value = vehiculo.id_vehiculo;
                option.textContent = `${vehiculo.marca} ${vehiculo.modelo} (${vehiculo.matricula})`;
                // Conservar selección si el vehículo pertenece al nuevo cliente
                if (vehiculo.id_vehiculo == vehiculoActual) {
                    option.selected = true;
                }
                vehiculoSelect.appendChild(option);
            });

            if (vehiculosCliente.length === 0) {
                vehiculoSelect.innerHTML = '<option value="">No hay vehículos para este cliente</option>';
            }
        }
    });

    // Manejar clicks en checkboxes de servicios
    document.querySelectorAll('.servicio-check').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('tipo_servicio').value = this.dataset.servicio;
                servicioModal.show();
            } else {
                // Remover servicio de la tabla
                eliminarServicio(this.dataset.servicio);
            }
        });
    });

    // Agregar servicio a la tabla
    document.getElementById('agregarServicio').addEventListener('click', function() {
        const form = document.getElementById('servicioForm');
        const tipoServicio = document.getElementById('tipo_servicio').value;
        const descripcion = document.getElementById('descripcion_servicio').value;
        const cantidad = parseInt(document.getElementById('cantidad').value);
        const precioUnitario = parseInt(document.getElementById('precio_unitario').value);
        const subtotal = cantidad * precioUnitario;

        if (!descripcion || !cantidad || !precioUnitario) {
            alert('Por favor complete todos los campos');
            return;
        }

        const servicio = {
            tipo: tipoServicio,
            descripcion: descripcion,
            cantidad: cantidad,
            precioUnitario: precioUnitario,
            subtotal: subtotal
        };

        agregarServicioATabla(servicio);
        servicioModal.hide();
        form.reset();
    });

    function agregarServicioATabla(servicio) {
        const tbody = document.querySelector('#serviciosTable tbody');
        const tr = document.createElement('tr');
        tr.dataset.tipo = servicio.tipo;

        tr.innerHTML = `
            <td>${capitalizar(servicio.tipo.replace('_', ' '))}</td>
            <td>${servicio.descripcion}</td>
            <td>${servicio.cantidad}</td>
            <td>₲ ${formatearNumero(servicio.precioUnitario)}</td>
            <td>₲ ${formatearNumero(servicio.subtotal)}</td>
            <td>
                <button type="button" class="btn btn-danger btn-sm eliminar-servicio">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;

        tbody.appendChild(tr);
        serviciosAgregados.push(servicio);
        actualizarTotal();
        actualizarServiciosInput();

        // Agregar evento al botón de eliminar
        tr.querySelector('.eliminar-servicio').addEventListener('click', function() {
            eliminarServicio(servicio.tipo);
        });
    }

    function eliminarServicio(tipo) {
        const tr = document.querySelector(`tr[data-tipo="${tipo}"]`);
        if (tr) {
            tr.remove();
            serviciosAgregados = serviciosAgregados.filter(s => s.tipo !== tipo);
            document.querySelector(`input[data-servicio="${tipo}"]`).checked = false;
            actualizarTotal();
            actualizarServiciosInput();
        }
    }

    function actualizarTablaServicios() {
        const tbody = document.querySelector('#serviciosTable tbody');
        tbody.innerHTML = '';

        serviciosAgregados.forEach((servicio, index) => {
            const tr = document.createElement('tr');
            tr.dataset.tipo = servicio.tipo;

            tr.innerHTML = `
                <td>${capitalizar(servicio.tipo.replace('_', ' '))}</td>
                <td>${servicio.descripcion}</td>
                <td>${servicio.cantidad}</td>
                <td>₲ ${formatearNumero(servicio.precioUnitario)}</td>
                <td>₲ ${formatearNumero(servicio.subtotal)}</td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm eliminar-servicio">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;

            tbody.appendChild(tr);

            // Agregar evento al botón de eliminar
            tr.querySelector('.eliminar-servicio').addEventListener('click', function() {
                eliminarServicio(servicio.tipo);
            });
        });

        actualizarTotal();
        actualizarServiciosInput();
    }

    function actualizarTotal() {
        const total = serviciosAgregados.reduce((sum, servicio) => sum + servicio.subtotal, 0);
        document.getElementById('total').textContent = `₲ ${formatearNumero(total)}`;
    }

    function actualizarServiciosInput() {
        document.getElementById('serviciosInput').value = JSON.stringify(serviciosAgregados);
    }

    function capitalizar(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    function formatearNumero(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Validación del formulario antes de enviar
    document.getElementById('ordenTrabajoForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validaciones
        const vehiculoId = document.getElementById('id_vehiculo').value;
        const fechaSalida = document.getElementById('fecha_salida').value;
        
        if (!vehiculoId) {
            alert('Debe seleccionar un vehículo');
            return;
        }
        
        if (!fechaSalida) {
            alert('Debe establecer una fecha de salida');
            return;
        }
        
        if (serviciosAgregados.length === 0) {
            alert('Debe agregar al menos un servicio');
            return;
        }

        // Actualizar campo oculto antes del envío
        actualizarServiciosInput();
        
        // Enviar formulario
        this.submit();
    });
});
</script>