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

// Verificar que se proporcione un ID
if (!isset($_GET["id"]) || empty($_GET["id"])) {
    echo '<script>window.location = "index.php?pagina=tabla/orden_trabajo";</script>';
    return;
}

$id_orden = $_GET["id"];
$orden = OrdenTrabajoControlador::ctrObtenerOrdenTrabajo($id_orden);

if (!$orden) {
    echo '<script>
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "No se encontró la orden de trabajo especificada"
        }).then(() => {
            window.location = "index.php?pagina=tabla/orden_trabajo";
        });
    </script>';
    return;
}

$clientes = ClienteControlador::buscarCliente();
$vehiculos = VehiculoControlador::ctrListarVehiculos();
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="bi bi-pencil-square"></i> Editar Orden de Trabajo #<?php echo str_pad($orden['id_orden'], 6, '0', STR_PAD_LEFT); ?> - Motor Service
                    </h4>
                    <small>Estado actual: <span class="badge bg-<?php echo $orden['estado'] == 'completado' ? 'success' : ($orden['estado'] == 'en_proceso' ? 'primary' : 'secondary'); ?>"><?php echo ucfirst($orden['estado']); ?></span></small>
                </div>
                <div class="card-body">
                    <form method="post" id="formEditarOrden">
                        <input type="hidden" name="id_orden" value="<?php echo $orden['id_orden']; ?>">
                        
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
                                                        <option value="<?php echo $cliente['id_cliente']; ?>"
                                                            <?php echo ($cliente['id_cliente'] == $orden['id_cliente']) ? 'selected' : ''; ?>>
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
                                                <option value="">Seleccione un vehículo</option>
                                                <?php foreach ($vehiculos as $vehiculo): ?>
                                                        <option value="<?php echo $vehiculo['id_vehiculo']; ?>"
                                                            data-cliente="<?php echo $vehiculo['id_cliente']; ?>"
                                                            <?php echo ($vehiculo['id_vehiculo'] == $orden['id_vehiculo']) ? 'selected' : ''; ?>>
                                                            <?php echo $vehiculo['marca'] . ' ' . $vehiculo['modelo'] . ' (' . $vehiculo['matricula'] . ')'; ?>
                                                        </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="kilometraje_actual" class="form-label">Kilometraje Actual</label>
                                            <input type="number" class="form-control" id="kilometraje_actual" name="kilometraje_actual" 
                                                   min="0" value="<?php echo $orden['kilometraje_actual']; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="estado" class="form-label">Estado de la Orden</label>
                                            <select class="form-select" id="estado" name="estado" required>
                                                <option value="en_proceso" <?php echo ($orden['estado'] == 'en_proceso') ? 'selected' : ''; ?>>En Proceso</option>
                                                <option value="completado" <?php echo ($orden['estado'] == 'completado') ? 'selected' : ''; ?>>Completado</option>
                                                <option value="entregado" <?php echo ($orden['estado'] == 'entregado') ? 'selected' : ''; ?>>Entregado</option>
                                                <option value="cancelado" <?php echo ($orden['estado'] == 'cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="observaciones" class="form-label">Observaciones</label>
                                    <textarea class="form-control" id="observaciones" name="observaciones" rows="3"><?php echo htmlspecialchars($orden['observaciones']); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Fechas de Servicio -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="bi bi-calendar-event"></i> Fechas de Servicio</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="fecha_entrada" class="form-label">Fecha y Hora de Entrada</label>
                                            <input type="datetime-local" class="form-control" id="fecha_entrada" name="fecha_entrada" 
                                                   value="<?php echo date('Y-m-d\TH:i', strtotime($orden['fecha_ingreso'])); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="fecha_salida" class="form-label">Fecha Estimada/Real de Salida</label>
                                            <input type="datetime-local" class="form-control" id="fecha_salida" name="fecha_salida" 
                                                   value="<?php echo $orden['fecha_salida'] ? date('Y-m-d\TH:i', strtotime($orden['fecha_salida'])) : ''; ?>" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SERVICIOS ACTUALIZADOS -->
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5><i class="bi bi-gear-fill"></i> Servicios Automotrices Especializados</h5>
                                <small>Modifique los servicios realizados en el vehículo</small>
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

                        <!-- Detalle de Servicios -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="bi bi-list-task"></i> Detalle de Servicios</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="serviciosTable">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Tipo de Servicio</th>
                                                <th>Descripción</th>
                                                <th>Cantidad</th>
                                                <th>Precio Unitario</th>
                                                <th>Subtotal</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Los servicios se cargarán aquí -->
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-warning">
                                                <th colspan="4" class="text-end">TOTAL GENERAL:</th>
                                                <th id="totalGeneral">Gs. 0</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="index.php?pagina=tabla/orden_trabajo" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> Volver
                                    </a>
                                    <div>
                                        <?php if ($orden['estado'] != 'cancelado'): ?>
                                                <button type="submit" class="btn btn-warning btn-lg me-2" id="btnActualizarOrden">
                                                    <i class="bi bi-save"></i> Actualizar Orden
                                                </button>
                                                <?php if ($orden['estado'] == 'completado' && !$orden['facturado']): ?>
                                                        <a href="index.php?pagina=nuevo/factura&desde_orden=<?php echo $orden['id_orden']; ?>" class="btn btn-success btn-lg">
                                                            <i class="bi bi-receipt"></i> Facturar
                                                        </a>
                                                <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Campo oculto para servicios -->
                        <input type="hidden" id="servicios" name="servicios" value="">
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
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="servicioModalLabel">Editar Servicio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="servicioForm">
                    <input type="hidden" id="tipo_servicio" name="tipo_servicio">
                    
                    <div class="mb-3">
                        <label for="descripcion_servicio" class="form-label">Descripción del Servicio</label>
                        <textarea class="form-control" id="descripcion_servicio" name="descripcion_servicio" rows="3" required></textarea>
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
                <button type="button" class="btn btn-warning" id="agregarServicio">Actualizar Servicio</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const servicioModal = new bootstrap.Modal(document.getElementById('servicioModal'));
    const vehiculos = <?php echo json_encode($vehiculos); ?>;
    let serviciosSeleccionados = [];
    
    // Cargar servicios existentes
    const serviciosExistentes = <?php echo json_encode($orden['detalles'] ?? []); ?>;
    
    // Convertir servicios existentes al formato esperado
    serviciosExistentes.forEach(detalle => {
        serviciosSeleccionados.push({
            tipo: detalle.tipo_servicio,
            descripcion: detalle.descripcion,
            cantidad: parseInt(detalle.cantidad),
            precioUnitario: parseFloat(detalle.precio_unitario),
            subtotal: parseFloat(detalle.subtotal)
        });
        
        // Marcar checkbox correspondiente
        const checkbox = document.querySelector(`[data-servicio="${detalle.tipo_servicio}"]`);
        if (checkbox) {
            checkbox.checked = true;
        }
    });
    
    // Actualizar tabla con servicios existentes
    actualizarTablaServicios();

    // Filtrar vehículos por cliente
    const vehiculoActual = <?php echo $orden['id_vehiculo']; ?>;
    document.getElementById('id_cliente').addEventListener('change', function() {
        const clienteId = this.value;
        const vehiculoSelect = document.getElementById('id_vehiculo');
        
        // Limpiar opciones
        vehiculoSelect.innerHTML = '<option value="">Seleccione un vehículo</option>';
        
        if (clienteId) {
            const vehiculosCliente = vehiculos.filter(v => v.id_cliente == clienteId);
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
                document.getElementById('servicioModalLabel').textContent = 
                    'Editar Servicio: ' + this.nextElementSibling.textContent.trim();
                
                // Buscar servicio existente para pre-cargar datos
                const servicioExistente = serviciosSeleccionados.find(s => s.tipo === this.dataset.servicio);
                
                if (servicioExistente) {
                    document.getElementById('descripcion_servicio').value = servicioExistente.descripcion;
                    document.getElementById('cantidad').value = servicioExistente.cantidad;
                    document.getElementById('precio_unitario').value = servicioExistente.precioUnitario;
                    calcularSubtotal();
                } else {
                    // Limpiar formulario para nuevo servicio
                    document.getElementById('servicioForm').reset();
                    document.getElementById('cantidad').value = 1;
                }
                
                document.getElementById('tipo_servicio').value = this.dataset.servicio;
                servicioModal.show();
            } else {
                // Remover servicio si se desmarca
                eliminarServicio(this.dataset.servicio);
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

    // Actualizar servicio
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

        // Remover servicio existente
        serviciosSeleccionados = serviciosSeleccionados.filter(s => s.tipo !== tipoServicio);

        // Agregar servicio actualizado
        serviciosSeleccionados.push({
            tipo: tipoServicio,
            descripcion: descripcion,
            cantidad: cantidad,
            precioUnitario: precioUnitario,
            subtotal: subtotal
        });

        actualizarTablaServicios();
        servicioModal.hide();
        
        // Mantener checkbox marcado
        document.querySelector(`[data-servicio="${tipoServicio}"]`).checked = true;
    });

    function actualizarTablaServicios() {
        const tbody = document.querySelector('#serviciosTable tbody');
        tbody.innerHTML = '';
        let totalGeneral = 0;

        serviciosSeleccionados.forEach(servicio => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><span class="badge bg-warning text-dark">${formatearTipoServicio(servicio.tipo)}</span></td>
                <td>${servicio.descripcion}</td>
                <td class="text-center">${servicio.cantidad}</td>
                <td class="text-end">Gs. ${servicio.precioUnitario.toLocaleString()}</td>
                <td class="text-end">Gs. ${servicio.subtotal.toLocaleString()}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarServicio('${servicio.tipo}')">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
            totalGeneral += servicio.subtotal;
        });

        document.getElementById('totalGeneral').textContent = 'Gs. ' + totalGeneral.toLocaleString();
        
        // Actualizar campo oculto
        document.getElementById('servicios').value = JSON.stringify(serviciosSeleccionados);
    }

    function formatearTipoServicio(tipo) {
        const nombres = {
            'aceite_motor': 'Aceite Motor',
            'aceite_dif_trasero': 'Aceite Dif. Trasero',
            'aceite_dif_delantero': 'Aceite Dif. Delantero',
            'aceite_caja': 'Aceite Caja',
            'aceite_reductora': 'Aceite Reductora',
            'filtro_aire': 'Filtro de Aire',
            'filtro_aceite': 'Filtro de Aceite',
            'filtro_combustible': 'Filtro de Combustible',
            'bujias': 'Bujías',
            'liquidos': 'Líquidos',
            'mano_obra': 'Mano de Obra',
            'otro': 'Otro'
        };
        return nombres[tipo] || tipo;
    }

    // Función global para eliminar servicio
    window.eliminarServicio = function(tipoServicio) {
        serviciosSeleccionados = serviciosSeleccionados.filter(s => s.tipo !== tipoServicio);
        document.querySelector(`[data-servicio="${tipoServicio}"]`).checked = false;
        actualizarTablaServicios();
    };

    // Validar formulario antes de enviar
    document.getElementById('formEditarOrden').addEventListener('submit', function(e) {
        if (serviciosSeleccionados.length === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Sin servicios',
                text: 'Debe tener al menos un servicio en la orden de trabajo'
            });
            return;
        }

        // Confirmar actualización
        e.preventDefault();
        const estado = document.getElementById('estado').value;
        const textoEstado = estado.charAt(0).toUpperCase() + estado.slice(1).replace('_', ' ');
        
        Swal.fire({
            title: '¿Actualizar Orden de Trabajo?',
            text: `Se actualizará la orden con estado: ${textoEstado}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, actualizar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });
});
</script>

<?php
$actualizacion = OrdenTrabajoControlador::ctrActualizarOrdenTrabajo();
if ($actualizacion == "ok") {
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
    color: #ffc107;
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

#serviciosTable tbody tr:hover {
    background-color: #fff3cd;
}

.modal-header {
    border-bottom: 2px solid rgba(0,0,0,0.1);
}

.btn-warning {
    color: #000;
}

.btn-warning:hover {
    color: #000;
}

@media (max-width: 768px) {
    .col-md-4 {
        margin-bottom: 1rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-lg {
        font-size: 1rem;
        padding: 0.5rem 1rem;
    }
}
</style>