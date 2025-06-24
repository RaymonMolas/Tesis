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

$clientes = ClienteControlador::buscarCliente();
$vehiculos = VehiculoControlador::ctrListarVehiculos();
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-wrench-adjustable"></i> Nueva Orden de Trabajo - Motor Service
                    </h4>
                </div>
                <div class="card-body">
                    <form method="post" id="formOrdenTrabajo">
                        
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
                                            <label for="kilometraje_actual" class="form-label">Kilometraje Actual</label>
                                            <input type="number" class="form-control" id="kilometraje_actual" name="kilometraje_actual" min="0">
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
                                                   value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="fecha_salida" class="form-label">Fecha Estimada de Salida</label>
                                            <input type="datetime-local" class="form-control" id="fecha_salida" name="fecha_salida" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- NUEVOS SERVICIOS ESPECÍFICOS -->
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5><i class="bi bi-gear-fill"></i> Servicios Automotrices Especializados</h5>
                                <small>Seleccione los servicios que se realizarán en el vehículo</small>
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
                                <h5><i class="bi bi-list-task"></i> Detalle de Servicios Seleccionados</h5>
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
                                            <!-- Los servicios se agregarán aquí dinámicamente -->
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-primary">
                                                <th colspan="4" class="text-end">TOTAL GENERAL:</th>
                                                <th id="totalGeneral">Gs. 0</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div class="alert alert-info mt-3" id="alertaServicios" style="display: none;">
                                    <i class="bi bi-info-circle"></i> Seleccione los servicios que desea realizar y complete los detalles en el modal que aparecerá.
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
                                    <button type="submit" class="btn btn-danger btn-lg" id="btnGuardarOrden">
                                        <i class="bi bi-save"></i> Crear Orden de Trabajo
                                    </button>
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
                        <div class="form-text">Detalle específico del servicio a realizar</div>
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
                <button type="button" class="btn btn-primary" id="agregarServicio">Agregar Servicio</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const servicioModal = new bootstrap.Modal(document.getElementById('servicioModal'));
    const vehiculos = <?php echo json_encode($vehiculos); ?>;
    let serviciosSeleccionados = [];

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
                    'Detalle del Servicio: ' + this.nextElementSibling.textContent.trim();
                
                // Limpiar formulario
                document.getElementById('servicioForm').reset();
                document.getElementById('tipo_servicio').value = this.dataset.servicio;
                document.getElementById('cantidad').value = 1;
                
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

    // Agregar servicio a la tabla
    document.getElementById('agregarServicio').addEventListener('click', function() {
        const form = document.getElementById('servicioForm');
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
        serviciosSeleccionados = serviciosSeleccionados.filter(s => s.tipo !== tipoServicio);

        // Agregar nuevo servicio
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
                <td><span class="badge bg-primary">${formatearTipoServicio(servicio.tipo)}</span></td>
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
        
        // Mostrar/ocultar alerta
        const alerta = document.getElementById('alertaServicios');
        if (serviciosSeleccionados.length === 0) {
            alerta.style.display = 'block';
        } else {
            alerta.style.display = 'none';
        }
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
    document.getElementById('formOrdenTrabajo').addEventListener('submit', function(e) {
        if (serviciosSeleccionados.length === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Sin servicios',
                text: 'Debe seleccionar al menos un servicio para crear la orden de trabajo'
            });
            return;
        }

        // Confirmar creación
        e.preventDefault();
        Swal.fire({
            title: '¿Crear Orden de Trabajo?',
            text: `Se creará la orden con ${serviciosSeleccionados.length} servicio(s)`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, crear orden',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });

    // Mostrar alerta inicial
    document.getElementById('alertaServicios').style.display = 'block';
});
</script>

<?php
$registro = OrdenTrabajoControlador::ctrRegistrarOrdenTrabajo();
if($registro == "ok"){
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
    color: #dc3545;
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
    background-color: #f8f9fa;
}

.modal-header {
    border-bottom: 2px solid rgba(255,255,255,0.2);
}

.alert-info {
    border-left: 4px solid #0dcaf0;
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