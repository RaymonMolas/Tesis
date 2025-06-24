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
$orden = null;
$presupuesto = null;

// Verificar si viene desde una orden de trabajo
if (isset($_GET['desde_orden']) && !empty($_GET['desde_orden'])) {
    $orden = OrdenTrabajoControlador::ctrObtenerOrdenTrabajo($_GET['desde_orden']);
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
}

// Verificar si viene desde un presupuesto
if (isset($_GET['desde_presupuesto']) && !empty($_GET['desde_presupuesto'])) {
    $presupuesto = PresupuestoControlador::ctrObtenerPresupuesto($_GET['desde_presupuesto']);
    if (!$presupuesto) {
        echo '<script>
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "No se encontró el presupuesto especificado"
            }).then(() => {
                window.location = "index.php?pagina=tabla/presupuestos";
            });
        </script>';
        return;
    }
}
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-receipt"></i> Nueva Factura - Motor Service
                    </h4>
                    <?php if ($orden): ?>
                            <small>Facturando Orden de Trabajo #<?php echo str_pad($orden['id_orden'], 6, '0', STR_PAD_LEFT); ?></small>
                    <?php elseif ($presupuesto): ?>
                            <small>Facturando Presupuesto #<?php echo str_pad($presupuesto['id_presupuesto'], 6, '0', STR_PAD_LEFT); ?></small>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <form method="post" id="formFactura">
                        
                        <!-- Información del Cliente EDITABLE -->
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5><i class="bi bi-person-circle"></i> Información del Cliente</h5>
                                <small>Los datos son editables para casos de facturación empresarial</small>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nombre_cliente" class="form-label">Nombre Completo / Razón Social</label>
                                            <input type="text" class="form-control" id="nombre_cliente" name="nombre_cliente" 
                                                   value="<?php
                                                   if ($orden) {
                                                       echo htmlspecialchars($orden['nombre_cliente']);
                                                   } elseif ($presupuesto) {
                                                       echo htmlspecialchars($presupuesto['nombre_cliente']);
                                                   }
                                                   ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="cedula_ruc" class="form-label">Cédula/RUC</label>
                                            <input type="text" class="form-control" id="cedula_ruc" name="cedula_ruc" 
                                                   value="<?php
                                                   if ($orden) {
                                                       echo !empty($orden['ruc']) ? $orden['ruc'] : $orden['cedula'];
                                                   } elseif ($presupuesto) {
                                                       echo !empty($presupuesto['ruc']) ? $presupuesto['ruc'] : $presupuesto['cedula'];
                                                   }
                                                   ?>" required>
                                            <div class="form-text">Para personas: Cédula | Para empresas: RUC</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="telefono_cliente" class="form-label">Teléfono</label>
                                            <input type="text" class="form-control" id="telefono_cliente" name="telefono_cliente"
                                                   value="<?php
                                                   if ($orden) {
                                                       echo htmlspecialchars($orden['telefono']);
                                                   } elseif ($presupuesto) {
                                                       echo htmlspecialchars($presupuesto['telefono']);
                                                   }
                                                   ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email_cliente" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email_cliente" name="email_cliente"
                                                   value="<?php
                                                   if ($orden) {
                                                       echo htmlspecialchars($orden['email']);
                                                   } elseif ($presupuesto) {
                                                       echo htmlspecialchars($presupuesto['email']);
                                                   }
                                                   ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="direccion_cliente" class="form-label">Dirección</label>
                                            <input type="text" class="form-control" id="direccion_cliente" name="direccion_cliente"
                                                   value="<?php
                                                   if ($orden) {
                                                       echo htmlspecialchars($orden['direccion']);
                                                   } elseif ($presupuesto) {
                                                       echo htmlspecialchars($presupuesto['direccion']);
                                                   }
                                                   ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Botón para cargar datos del cliente seleccionado -->
                                <div class="mb-3">
                                    <label for="cliente_base" class="form-label">Cargar datos desde cliente existente (opcional)</label>
                                    <div class="input-group">
                                        <select class="form-select" id="cliente_base">
                                            <option value="">-- Seleccionar cliente base --</option>
                                            <?php foreach ($clientes as $cliente): ?>
                                                    <option value="<?php echo $cliente['id_cliente']; ?>" 
                                                            data-nombre="<?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?>"
                                                            data-cedula="<?php echo htmlspecialchars($cliente['cedula']); ?>"
                                                            data-ruc="<?php echo htmlspecialchars($cliente['ruc']); ?>"
                                                            data-telefono="<?php echo htmlspecialchars($cliente['telefono']); ?>"
                                                            data-email="<?php echo htmlspecialchars($cliente['email']); ?>"
                                                            data-direccion="<?php echo htmlspecialchars($cliente['direccion']); ?>">
                                                        <?php echo $cliente['nombre'] . ' ' . $cliente['apellido']; ?>
                                                        <?php if (!empty($cliente['ruc'])): ?>
                                                                - RUC: <?php echo $cliente['ruc']; ?>
                                                        <?php endif; ?>
                                                    </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="button" class="btn btn-outline-primary" id="cargarDatosCliente">
                                            <i class="bi bi-download"></i> Cargar Datos
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información del Vehículo (si viene de orden/presupuesto) -->
                        <?php if ($orden || $presupuesto): ?>
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5><i class="bi bi-car-front"></i> Información del Vehículo</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <p><strong>Matrícula:</strong> <?php echo $orden ? $orden['matricula'] : $presupuesto['matricula']; ?></p>
                                        </div>
                                        <div class="col-md-4">
                                            <p><strong>Marca:</strong> <?php echo $orden ? $orden['marca'] : $presupuesto['marca']; ?></p>
                                        </div>
                                        <div class="col-md-4">
                                            <p><strong>Modelo:</strong> <?php echo $orden ? $orden['modelo'] : $presupuesto['modelo']; ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Servicios desde Orden de Trabajo o Presupuesto -->
                        <div class="card mb-4">
                            <div class="card-header bg-info text-white">
                                <h5><i class="bi bi-list-task"></i> 
                                    <?php if ($orden): ?>
                                            Servicios desde Orden de Trabajo
                                    <?php elseif ($presupuesto): ?>
                                            Servicios desde Presupuesto
                                    <?php else: ?>
                                            Servicios y Productos a Facturar
                                    <?php endif; ?>
                                </h5>
                                <?php if (!$orden && !$presupuesto): ?>
                                        <small>Para facturar servicios, debe crear primero una orden de trabajo o presupuesto</small>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="detallesTable">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Tipo</th>
                                                <th>Descripción</th>
                                                <th>Cantidad</th>
                                                <th>Precio Unitario</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Los detalles se cargarán automáticamente -->
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-success">
                                                <th colspan="4" class="text-end">TOTAL A FACTURAR:</th>
                                                <th id="totalFactura">Gs. 0</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                
                                <?php if (!$orden && !$presupuesto): ?>
                                    <div class="alert alert-warning mt-3">
                                        <i class="bi bi-exclamation-triangle"></i> 
                                        <strong>Facturación Directa:</strong> Para crear una factura, primero debe generar una 
                                        <a href="index.php?pagina=nuevo/orden_trabajo" class="alert-link">Orden de Trabajo</a> o 
                                        <a href="index.php?pagina=nuevo/presupuesto" class="alert-link">Presupuesto</a>.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Configuración de Factura -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="bi bi-gear"></i> Configuración de Factura</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tipo_factura" class="form-label">Tipo de Factura</label>
                                            <select class="form-select" id="tipo_factura" name="tipo_factura" required>
                                                <option value="contado">Contado</option>
                                                <option value="credito">Crédito</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="fecha_vencimiento" class="form-label">Fecha de Vencimiento</label>
                                            <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento" 
                                                   value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>">
                                            <div class="form-text">Solo para facturas a crédito</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="observaciones_factura" class="form-label">Observaciones</label>
                                            <textarea class="form-control" id="observaciones_factura" name="observaciones_factura" rows="2"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="<?php
                                    if ($orden) {
                                        echo 'index.php?pagina=tabla/orden_trabajo';
                                    } elseif ($presupuesto) {
                                        echo 'index.php?pagina=tabla/presupuestos';
                                    } else {
                                        echo 'index.php?pagina=tabla/facturas';
                                    }
                                    ?>" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> Volver
                                    </a>
                                    <?php if ($orden || $presupuesto): ?>
                                            <button type="submit" class="btn btn-success btn-lg" id="btnGenerarFactura">
                                                <i class="bi bi-receipt"></i> Generar Factura
                                            </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Campos ocultos -->
                        <?php if ($orden): ?>
                                <input type="hidden" name="id_orden" value="<?php echo $orden['id_orden']; ?>">
                                <input type="hidden" name="id_cliente_original" value="<?php echo $orden['id_cliente']; ?>">
                        <?php elseif ($presupuesto): ?>
                                <input type="hidden" name="id_presupuesto" value="<?php echo $presupuesto['id_presupuesto']; ?>">
                                <input type="hidden" name="id_cliente_original" value="<?php echo $presupuesto['id_cliente']; ?>">
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let detalles = [];
    
    // Cargar detalles desde orden o presupuesto
    <?php if ($orden && isset($orden['detalles'])): ?>
            const detallesOrden = <?php echo json_encode($orden['detalles']); ?>;
            detallesOrden.forEach(detalle => {
                detalles.push({
                    tipo: 'servicio',
                    descripcion: detalle.descripcion,
                    cantidad: parseInt(detalle.cantidad),
                    precio_unitario: parseFloat(detalle.precio_unitario),
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
                    descripcion: detalle.descripcion,
                    cantidad: parseInt(detalle.cantidad),
                    precio_unitario: parseFloat(detalle.precio_unitario),
                    subtotal: parseFloat(detalle.subtotal)
                });
            });
            actualizarTabla();
    <?php endif; ?>

    // Cargar datos del cliente seleccionado
    document.getElementById('cargarDatosCliente').addEventListener('click', function() {
        const select = document.getElementById('cliente_base');
        const option = select.options[select.selectedIndex];
        
        if (option.value) {
            document.getElementById('nombre_cliente').value = option.dataset.nombre;
            
            // Usar RUC si existe, sino cédula
            const documento = option.dataset.ruc || option.dataset.cedula;
            document.getElementById('cedula_ruc').value = documento;
            
            document.getElementById('telefono_cliente').value = option.dataset.telefono;
            document.getElementById('email_cliente').value = option.dataset.email;
            document.getElementById('direccion_cliente').value = option.dataset.direccion;
            
            Swal.fire({
                icon: 'success',
                title: 'Datos cargados',
                text: 'Los datos del cliente han sido cargados correctamente',
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Seleccione un cliente',
                text: 'Debe seleccionar un cliente para cargar los datos'
            });
        }
    });

    // Manejar tipo de factura
    document.getElementById('tipo_factura').addEventListener('change', function() {
        const fechaVencimiento = document.getElementById('fecha_vencimiento');
        if (this.value === 'contado') {
            fechaVencimiento.disabled = true;
            fechaVencimiento.required = false;
        } else {
            fechaVencimiento.disabled = false;
            fechaVencimiento.required = true;
        }
    });

    function actualizarTabla() {
        const tbody = document.querySelector('#detallesTable tbody');
        tbody.innerHTML = '';
        let total = 0;

        detalles.forEach(detalle => {
            const row = document.createElement('tr');
            const tipoLabel = detalle.tipo === 'servicio' ? 'Servicio' : 'Producto';
            const tipoBadge = detalle.tipo === 'servicio' ? 'bg-primary' : 'bg-success';
            
            row.innerHTML = `
                <td><span class="badge ${tipoBadge}">${tipoLabel}</span></td>
                <td>${detalle.descripcion}</td>
                <td class="text-center">${detalle.cantidad}</td>
                <td class="text-end">Gs. ${detalle.precio_unitario.toLocaleString()}</td>
                <td class="text-end">Gs. ${detalle.subtotal.toLocaleString()}</td>
            `;
            tbody.appendChild(row);
            total += detalle.subtotal;
        });

        document.getElementById('totalFactura').textContent = 'Gs. ' + total.toLocaleString();
    }

    // Validar formulario antes de enviar
    document.getElementById('formFactura').addEventListener('submit', function(e) {
        <?php if ($orden || $presupuesto): ?>
            if (detalles.length === 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Sin detalles',
                    text: 'No hay servicios o productos para facturar'
                });
                return;
            }

            // Validar campos obligatorios
            const nombreCliente = document.getElementById('nombre_cliente').value.trim();
            const cedulaRuc = document.getElementById('cedula_ruc').value.trim();
        
            if (!nombreCliente || !cedulaRuc) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Datos incompletos',
                    text: 'El nombre del cliente y cédula/RUC son obligatorios'
                });
                return;
            }

            // Confirmar generación
            e.preventDefault();
            const tipoFactura = document.getElementById('tipo_factura').value;
            const totalFactura = document.getElementById('totalFactura').textContent;
        
            Swal.fire({
                title: '¿Generar Factura?',
                html: `Se generará una factura ${tipoFactura} por:<br>
                   <strong>${totalFactura}</strong><br>
                   Cliente: ${nombreCliente}`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, generar factura',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        <?php else: ?>
            e.preventDefault();
            Swal.fire({
                icon: 'info',
                title: 'Facturación Directa',
                text: 'Para crear una factura, primero debe generar una orden de trabajo o presupuesto',
                showCancelButton: true,
                confirmButtonText: 'Crear Orden de Trabajo',
                cancelButtonText: 'Crear Presupuesto'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location = 'index.php?pagina=nuevo/orden_trabajo';
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    window.location = 'index.php?pagina=nuevo/presupuesto';
                }
            });
        <?php endif; ?>
    });
});
</script>

<?php
$registro = FacturaControlador::ctrRegistrarFactura();
if ($registro == "ok") {
    echo '<script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>';
}
?>

<style>
.card-header {
    border-bottom: 2px solid #dee2e6;
}

.table th {
    border-top: none;
}

.badge {
    font-size: 0.875em;
}

.form-control:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

.alert-warning {
    border-left: 4px solid #ffc107;
}

.bg-success {
    background-color: #28a745 !important;
}

.input-group .btn {
    border-color: #dee2e6;
}

.input-group .btn:hover {
    background-color: #e9ecef;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-lg {
        font-size: 1rem;
        padding: 0.5rem 1rem;
    }
}
</style>