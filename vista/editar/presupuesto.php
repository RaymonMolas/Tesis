<?php
// --- 1. VALIDACIÓN Y CARGA DE DATOS ---

// Validar que el usuario tenga sesión activa y sea personal
if (!isset($_SESSION["validarIngreso"]) || !is_numeric($_SESSION["id_personal"])) {
    echo '<script>window.location = "index.php?pagina=login";</script>';
    return;
}

// Validar que se proporcionó un ID de presupuesto
if (!isset($_GET["id"])) {
    $_SESSION['mensaje_flash'] = ["tipo" => "error", "titulo" => "Error", "texto" => "No se especificó el presupuesto a editar."];
    echo '<script>window.location = "index.php?pagina=tabla/presupuestos";</script>';
    return;
}

// Obtener los datos del presupuesto y sus detalles
$presupuesto = PresupuestoControlador::ctrObtenerPresupuesto($_GET["id"]);
if (!$presupuesto) {
    $_SESSION['mensaje_flash'] = ["tipo" => "error", "titulo" => "No Encontrado", "texto" => "El presupuesto que intenta editar no existe."];
    echo '<script>window.location = "index.php?pagina=tabla/presupuestos";</script>';
    return;
}

// Cargar datos necesarios para los selects
$clientes = ClienteControlador::buscarCliente();
$productos = ProductoControlador::ctrListarProductos();
$vehiculos = VehiculoControlador::ctrListarVehiculos();
$detalles_existentes = $presupuesto['detalles'] ?? [];

// --- 2. PROCESAMIENTO DEL FORMULARIO ---

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_presupuesto'])) {
    // El controlador se encargará de la lógica de actualización y de los mensajes flash.
    PresupuestoControlador::ctrActualizarPresupuesto();
}
?>

<title>EDITAR PRESUPUESTO #<?php echo htmlspecialchars($presupuesto['id_presupuesto']); ?></title>
<style>
    h2 { font-family: "Copperplate", Fantasy; color: red; }
    .card-header h5 { font-family: "Copperplate", Fantasy; }
</style>

<div class="container mt-5">
    <h2 class="mb-4">Editar Presupuesto #<?php echo htmlspecialchars($presupuesto['id_presupuesto']); ?></h2>
    
    <form method="post" id="presupuestoForm">
        <input type="hidden" name="id_presupuesto" value="<?php echo htmlspecialchars($presupuesto['id_presupuesto']); ?>">
        
        <div class="card mb-4">
            <div class="card-header"><h5>1. Información General</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="id_cliente" class="form-label">Cliente</label>
                        <select class="form-select" id="id_cliente" name="id_cliente" required>
                            <option value="">Seleccione un cliente</option>
                            <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?php echo htmlspecialchars($cliente['id_cliente']); ?>" <?php if ($presupuesto['id_cliente'] == $cliente['id_cliente'])
                                           echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?>
                                    </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="id_vehiculo" class="form-label">Vehículo</label>
                        <select class="form-select" id="id_vehiculo" name="id_vehiculo" required>
                            </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="fecha_validez" class="form-label">Válido Hasta</label>
                        <input type="date" class="form-control" id="fecha_validez" name="fecha_validez" value="<?php echo date('Y-m-d', strtotime($presupuesto['fecha_validez'])); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-select" id="estado" name="estado" required>
                            <option value="pendiente" <?php if ($presupuesto['estado'] == 'pendiente')
                                echo 'selected'; ?>>Pendiente</option>
                            <option value="aprobado" <?php if ($presupuesto['estado'] == 'aprobado')
                                echo 'selected'; ?>>Aprobado</option>
                            <option value="rechazado" <?php if ($presupuesto['estado'] == 'rechazado')
                                echo 'selected'; ?>>Rechazado</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5>2. Agregar o Modificar Items</h5></div>
            <div class="card-body d-flex gap-2">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productoModal"><i class="bi bi-box-seam"></i> Agregar Producto</button>
                <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#servicioModal"><i class="bi bi-wrench"></i> Agregar Servicio</button>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5>3. Detalle y Costos</h5></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table" id="detallesTable">
                        <thead class="table-light">
                            <tr><th>Tipo</th><th>Descripción</th><th>Cantidad</th><th>Precio Unitario</th><th>Subtotal</th><th>Acciones</th></tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr class="table-dark">
                                <td colspan="4" class="text-end"><strong>TOTAL:</strong></td>
                                <td><strong><span id="total">₲ 0</span></strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="observaciones" class="form-label">Observaciones</label>
            <textarea class="form-control" id="observaciones" name="observaciones" rows="3"><?php echo htmlspecialchars($presupuesto['observaciones']); ?></textarea>
        </div>

        <input type="hidden" name="detalles" id="detallesInput">
        <input type="hidden" name="total" id="totalInput">

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Actualizar Presupuesto</button>
            <a href="index.php?pagina=tabla/presupuestos" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<div class="modal fade" id="productoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Agregar Producto</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="productoForm">
                    <div class="mb-3"><label for="id_producto" class="form-label">Producto</label><select class="form-select" id="id_producto" required><option value="">Seleccione...</option>
                        <?php foreach ($productos as $producto): ?>
                                <option value="<?php echo htmlspecialchars($producto['id_producto']); ?>" data-precio="<?php echo htmlspecialchars($producto['precio']); ?>" data-nombre="<?php echo htmlspecialchars($producto['nombre']); ?>"><?php echo htmlspecialchars($producto['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select></div>
                    <div class="mb-3"><label for="cantidad_producto" class="form-label">Cantidad</label><input type="number" class="form-control" id="cantidad_producto" value="1" min="1" required></div>
                    <div class="mb-3"><label for="precio_producto" class="form-label">Precio Unitario (₲)</label><input type="number" class="form-control" id="precio_producto" required readonly></div>
                </form>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="button" class="btn btn-primary" id="agregarProducto">Agregar</button></div>
        </div>
    </div>
</div>

<div class="modal fade" id="servicioModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Agregar Servicio</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="servicioForm">
                    <div class="mb-3"><label for="descripcion_servicio" class="form-label">Descripción</label><textarea class="form-control" id="descripcion_servicio" rows="3" required></textarea></div>
                    <div class="mb-3"><label for="cantidad_servicio" class="form-label">Cantidad</label><input type="number" class="form-control" id="cantidad_servicio" value="1" min="1" required></div>
                    <div class="mb-3"><label for="precio_servicio" class="form-label">Precio Unitario (₲)</label><input type="number" class="form-control" id="precio_servicio" required></div>
                </form>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="button" class="btn btn-primary" id="agregarServicio">Agregar</button></div>
        </div>
    </div>
</div>

<script>
// --- LÓGICA DE JAVASCRIPT PARA EL FORMULARIO DE EDICIÓN ---
document.addEventListener('DOMContentLoaded', function() {
    // --- Variables y referencias a elementos del DOM ---
    const productoModal = new bootstrap.Modal(document.getElementById('productoModal'));
    const servicioModal = new bootstrap.Modal(document.getElementById('servicioModal'));
    let detalles = [];

    // --- Datos iniciales cargados desde PHP ---
    const todosVehiculos = <?php echo json_encode($vehiculos); ?>;
    const detallesExistentes = <?php echo json_encode($detalles_existentes); ?>;
    const vehiculoSeleccionadoID = <?php echo json_encode($presupuesto['id_vehiculo']); ?>;
    
    // --- Lógica para cargar vehículos del cliente seleccionado ---
    const clienteSelect = document.getElementById('id_cliente');
    const vehiculoSelect = document.getElementById('id_vehiculo');

    function cargarVehiculosCliente(clienteId, vehiculoASeleccionar) {
        vehiculoSelect.innerHTML = '<option value="">Cargando...</option>';
        const vehiculosCliente = todosVehiculos.filter(v => v.id_cliente == clienteId);
        
        vehiculoSelect.innerHTML = '<option value="">Seleccione un vehículo</option>';
        vehiculosCliente.forEach(vehiculo => {
            const option = document.createElement('option');
            option.value = vehiculo.id_vehiculo;
            option.textContent = `${vehiculo.marca} ${vehiculo.modelo} (${vehiculo.matricula})`;
            if (vehiculo.id_vehiculo == vehiculoASeleccionar) {
                option.selected = true;
            }
            vehiculoSelect.appendChild(option);
        });
    }

    // --- Lógica para manejo de detalles (agregar, eliminar, actualizar tabla) ---
    function agregarDetalle(detalle) {
        detalles.push(detalle);
        actualizarTabla();
    }

    window.eliminarDetalle = function(index) {
        detalles.splice(index, 1);
        actualizarTabla();
    }

    function actualizarTabla() {
        const tbody = document.querySelector('#detallesTable tbody');
        const totalSpan = document.getElementById('total');
        tbody.innerHTML = '';
        let totalGeneral = 0;

        detalles.forEach((detalle, index) => {
            const tr = document.createElement('tr');
            const subtotal = detalle.cantidad * detalle.precio_unitario;
            totalGeneral += subtotal;
            
            tr.innerHTML = `
                <td>${detalle.tipo.charAt(0).toUpperCase() + detalle.tipo.slice(1)}</td>
                <td>${detalle.descripcion}</td>
                <td>${detalle.cantidad}</td>
                <td>₲ ${subtotal.toLocaleString('es-ES')}</td>
                <td>₲ ${subtotal.toLocaleString('es-ES')}</td>
                <td><button type="button" class="btn btn-danger btn-sm" onclick="eliminarDetalle(${index})"><i class="bi bi-trash"></i></button></td>
            `;
            tbody.appendChild(tr);
        });

        totalSpan.textContent = `₲ ${totalGeneral.toLocaleString('es-ES')}`;
        document.getElementById('totalInput').value = totalGeneral;
        document.getElementById('detallesInput').value = JSON.stringify(detalles);
    }

    // --- Event Listeners ---
    clienteSelect.addEventListener('change', () => cargarVehiculosCliente(clienteSelect.value));
    
    document.getElementById('id_producto').addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        document.getElementById('precio_producto').value = option.dataset.precio || '';
    });

    document.getElementById('agregarProducto').addEventListener('click', function() {
        // ... (lógica para agregar producto)
        productoModal.hide();
    });

    document.getElementById('agregarServicio').addEventListener('click', function() {
        // ... (lógica para agregar servicio)
        servicioModal.hide();
    });

    // --- Inicialización del formulario ---
    detallesExistentes.forEach(d => {
        detalles.push({
            tipo: d.tipo,
            descripcion: d.descripcion,
            cantidad: parseInt(d.cantidad),
            precio_unitario: parseFloat(d.precio_unitario),
            id_producto: d.id_producto || null
        });
    });
    
    cargarVehiculosCliente(clienteSelect.value, vehiculoSeleccionadoID);
    actualizarTabla();
});
</script>