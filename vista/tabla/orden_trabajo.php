<?php
if ($_SESSION["tipo_usuario"] != "personal") {
    echo '<script>window.location = "index.php?pagina=inicio";</script>';
    return;
}

// Obtener lista de órdenes de trabajo
$ordenes = ControladorOrdenTrabajo::ctrMostrarOrdenes(null, null);
?>

<div class="content-wrapper">
    <!-- Header de la página -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="content-title">
                        <i class="bi bi-tools me-2"></i>
                        Gestión de Órdenes de Trabajo
                    </h1>
                    <p class="content-subtitle">Administra trabajos y servicios en curso</p>
                </div>
                <div class="col-sm-6">
                    <div class="content-actions">
                        <button type="button" class="btn btn-success me-2" onclick="window.location='index.php?pagina=nuevo/orden_trabajo'">
                            <i class="bi bi-plus-circle"></i>
                            <span class="d-none d-sm-inline">Nueva Orden</span>
                        </button>
                        <button type="button" class="btn btn-info" onclick="actualizarTabla()">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido principal -->
    <section class="content">
        <div class="container-fluid">
            
            <!-- Tarjetas de estadísticas -->
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="info-card">
                        <div class="info-card-content">
                            <div class="info-card-number">
                                <?php echo count($ordenes); ?>
                            </div>
                            <div class="info-card-text">Total Órdenes</div>
                        </div>
                        <div class="info-card-icon bg-primary">
                            <i class="bi bi-tools"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="info-card">
                        <div class="info-card-content">
                            <div class="info-card-number">
                                <?php 
                                $enProceso = array_filter($ordenes, function($o) { return $o['estado'] == 'en_proceso'; });
                                echo count($enProceso);
                                ?>
                            </div>
                            <div class="info-card-text">En Proceso</div>
                        </div>
                        <div class="info-card-icon bg-warning">
                            <i class="bi bi-gear-fill"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="info-card">
                        <div class="info-card-content">
                            <div class="info-card-number">
                                <?php 
                                $completadas = array_filter($ordenes, function($o) { return $o['estado'] == 'completado'; });
                                echo count($completadas);
                                ?>
                            </div>
                            <div class="info-card-text">Completadas</div>
                        </div>
                        <div class="info-card-icon bg-success">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="info-card">
                        <div class="info-card-content">
                            <div class="info-card-number">
                                <?php 
                                $noFacturadas = array_filter($ordenes, function($o) { return $o['facturado'] == 0 && $o['estado'] == 'completado'; });
                                echo count($noFacturadas);
                                ?>
                            </div>
                            <div class="info-card-text">Por Facturar</div>
                        </div>
                        <div class="info-card-icon bg-info">
                            <i class="bi bi-receipt"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card card-custom mb-4">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-funnel me-2"></i>
                        Filtros de Búsqueda
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="limpiarFiltros()">
                            <i class="bi bi-x-circle"></i> Limpiar
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Buscar cliente o vehículo:</label>
                                <input type="text" class="form-control" id="filtroTexto" placeholder="Cliente, vehículo o matrícula">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Estado:</label>
                                <select class="form-control" id="filtroEstado">
                                    <option value="">Todos</option>
                                    <option value="en_proceso">En Proceso</option>
                                    <option value="completado">Completado</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Personal:</label>
                                <select class="form-control" id="filtroPersonal">
                                    <option value="">Todos</option>
                                    <?php 
                                    $personal = array_unique(array_filter(array_column($ordenes, 'nombre_personal')));
                                    foreach($personal as $p): 
                                    ?>
                                        <option value="<?php echo htmlspecialchars($p); ?>"><?php echo htmlspecialchars($p); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Facturado:</label>
                                <select class="form-control" id="filtroFacturado">
                                    <option value="">Todos</option>
                                    <option value="si">Sí</option>
                                    <option value="no">No</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Fecha desde:</label>
                                <input type="date" class="form-control" id="filtroFechaDesde">
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button class="btn btn-primary btn-block" onclick="aplicarFiltros()">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de órdenes de trabajo -->
            <div class="card card-custom">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-table me-2"></i>
                        Lista de Órdenes de Trabajo
                    </h3>
                    <div class="card-tools">
                        <div class="input-group">
                            <input type="text" class="form-control form-control-sm" id="busquedaRapida" placeholder="Búsqueda rápida...">
                            <div class="input-group-append">
                                <button class="btn btn-sm btn-default">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-body table-responsive">
                    <table id="tablaOrdenes" class="table table-bordered table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 10px">#</th>
                                <th>ID Orden</th>
                                <th>Cliente</th>
                                <th>Vehículo</th>
                                <th>Personal</th>
                                <th>Tipo Servicio</th>
                                <th>Fecha Ingreso</th>
                                <th>Fecha Salida</th>
                                <th>Estado</th>
                                <th>Facturado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ordenes as $key => $value): ?>
                            <tr>
                                <td><?php echo $key + 1; ?></td>
                                <td>
                                    <span class="badge bg-primary">
                                        #<?php echo str_pad($value["id_orden"], 6, '0', STR_PAD_LEFT); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="client-info">
                                        <strong><?php echo htmlspecialchars($value["nombre_cliente"] . " " . $value["apellido_cliente"]); ?></strong>
                                        <?php if (!empty($value["telefono_cliente"])): ?>
                                            <small class="text-muted d-block">
                                                <i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($value["telefono_cliente"]); ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="vehicle-info">
                                        <strong><?php echo htmlspecialchars($value["marca"] . " " . $value["modelo"]); ?></strong>
                                        <small class="text-muted d-block">
                                            <i class="bi bi-credit-card me-1"></i><?php echo htmlspecialchars($value["matricula"]); ?>
                                        </small>
                                        <?php if (!empty($value["año"])): ?>
                                            <small class="text-muted">Año: <?php echo $value["año"]; ?></small>
                                        <?php endif; ?>
                                        <?php if (!empty($value["kilometraje_actual"])): ?>
                                            <small class="text-muted d-block">
                                                <i class="bi bi-speedometer me-1"></i><?php echo number_format($value["kilometraje_actual"], 0, ',', '.'); ?> km
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($value["nombre_personal"])): ?>
                                        <span class="badge bg-info">
                                            <?php echo htmlspecialchars($value["nombre_personal"] . " " . $value["apellido_personal"]); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">No asignado</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($value["tipo_servicio"])): ?>
                                        <span class="badge bg-secondary">
                                            <?php echo htmlspecialchars($value["tipo_servicio"]); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">No especificado</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <i class="bi bi-calendar-plus me-1"></i>
                                    <?php echo date("d/m/Y", strtotime($value["fecha_ingreso"])); ?>
                                    <small class="text-muted d-block">
                                        <?php echo date("H:i", strtotime($value["fecha_ingreso"])); ?>
                                    </small>
                                </td>
                                <td>
                                    <?php if (!empty($value["fecha_salida"])): ?>
                                        <i class="bi bi-calendar-check me-1"></i>
                                        <?php echo date("d/m/Y", strtotime($value["fecha_salida"])); ?>
                                        <small class="text-muted d-block">
                                            <?php echo date("H:i", strtotime($value["fecha_salida"])); ?>
                                        </small>
                                    <?php else: ?>
                                        <span class="text-muted">En taller</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($value["estado"] == "en_proceso"): ?>
                                        <span class="badge bg-warning">
                                            <i class="bi bi-gear-fill me-1"></i>En Proceso
                                        </span>
                                    <?php elseif ($value["estado"] == "completado"): ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>Completado
                                        </span>
                                    <?php elseif ($value["estado"] == "cancelado"): ?>
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle me-1"></i>Cancelado
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($value["facturado"]): ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-receipt me-1"></i>Sí
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-receipt me-1"></i>No
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                                onclick="verOrden(<?php echo $value['id_orden']; ?>)"
                                                title="Ver detalles">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-info btn-sm" 
                                                onclick="imprimirOrden(<?php echo $value['id_orden']; ?>)"
                                                title="Imprimir PDF">
                                            <i class="bi bi-printer"></i>
                                        </button>
                                        <?php if ($value["estado"] == "en_proceso"): ?>
                                            <button type="button" class="btn btn-outline-warning btn-sm" 
                                                    onclick="editarOrden(<?php echo $value['id_orden']; ?>)"
                                                    title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                                                        data-bs-toggle="dropdown" title="Más acciones">
                                                    <i class="bi bi-three-dots"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#" onclick="completarOrden(<?php echo $value['id_orden']; ?>)">
                                                        <i class="bi bi-check-circle me-2"></i>Marcar Completada
                                                    </a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="#" onclick="cancelarOrden(<?php echo $value['id_orden']; ?>)">
                                                        <i class="bi bi-x-circle me-2"></i>Cancelar Orden
                                                    </a></li>
                                                </ul>
                                            </div>
                                        <?php elseif ($value["estado"] == "completado" && !$value["facturado"]): ?>
                                            <button type="button" class="btn btn-outline-success btn-sm" 
                                                    onclick="crearFactura(<?php echo $value['id_orden']; ?>)"
                                                    title="Crear factura">
                                                <i class="bi bi-receipt"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="card-footer">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="dataTables_info">
                                Mostrando <?php echo count($ordenes); ?> órdenes en total
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="dataTables_paginate float-right">
                                <!-- Aquí se puede agregar paginación si es necesario -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Ver Orden -->
<div class="modal fade" id="modalVerOrden" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="bi bi-tools me-2"></i>
                    Detalles de la Orden de Trabajo
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body" id="contenidoVerOrden">
                <!-- El contenido se carga dinámicamente -->
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cerrar
                </button>
                <button type="button" class="btn btn-primary" id="btnImprimirModal" onclick="imprimirOrdenModal()">
                    <i class="bi bi-printer me-1"></i>Imprimir PDF
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Completar Orden -->
<div class="modal fade" id="modalCompletarOrden" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formCompletarOrden">
                <div class="modal-header">
                    <h4 class="modal-title">
                        <i class="bi bi-check-circle me-2"></i>
                        Completar Orden de Trabajo
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <input type="hidden" id="ordenIdCompletar" name="id_orden">
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Fecha y Hora de Salida</label>
                        <input type="datetime-local" class="form-control" name="fecha_salida" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Kilometraje Final</label>
                        <input type="number" class="form-control" name="kilometraje_final" min="0">
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Observaciones de Finalización</label>
                        <textarea class="form-control" name="observaciones_finalizacion" rows="3" placeholder="Observaciones sobre los trabajos realizados"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i>Completar Orden
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Inicializar DataTable
    $('#tablaOrdenes').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "responsive": true,
        "order": [[6, "desc"]],
        "pageLength": 25,
        "columnDefs": [
            { "orderable": false, "targets": [10] },
            { "width": "80px", "targets": [1] },
            { "width": "80px", "targets": [8] },
            { "width": "80px", "targets": [9] },
            { "width": "150px", "targets": [10] }
        ]
    });

    // Filtro de búsqueda rápida
    $('#busquedaRapida').on('keyup', function() {
        $('#tablaOrdenes').DataTable().search(this.value).draw();
    });

    // Establecer fecha y hora actual para completar orden
    var now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    $('input[name="fecha_salida"]').val(now.toISOString().slice(0, 16));

    // Envío del formulario de completar orden
    $('#formCompletarOrden').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        formData.append('accion', 'completar');
        
        $.ajax({
            url: "ajax/orden_trabajo_ajax.php",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(data) {
                try {
                    var response = JSON.parse(data);
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            $('#modalCompletarOrden').modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                } catch (e) {
                    Swal.fire('Error', 'Error al procesar la respuesta', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error de conexión', 'error');
            }
        });
    });
});

var ordenActual = null;

function verOrden(id) {
    ordenActual = id;
    
    $.post("ajax/orden_trabajo_ajax.php", {accion: "obtener", id: id}, function(data) {
        try {
            var orden = JSON.parse(data);
            if (orden.error) {
                Swal.fire('Error', orden.error, 'error');
                return;
            }
            
            var html = `
                <div class="row mb-4">
                    <div class="col-md-8">
                        <h5>Información del Cliente</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Cliente:</strong></td>
                                <td>${orden.nombre_cliente} ${orden.apellido_cliente}</td>
                            </tr>
                            <tr>
                                <td><strong>Cédula:</strong></td>
                                <td>${orden.cedula || 'No especificada'}</td>
                            </tr>
                            <tr>
                                <td><strong>Teléfono:</strong></td>
                                <td>${orden.telefono_cliente || 'No especificado'}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>${orden.email_cliente || 'No especificado'}</td>
                            </tr>
                        </table>
                        
                        <h5>Información del Vehículo</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Vehículo:</strong></td>
                                <td>${orden.marca} ${orden.modelo} (${orden.año || 'N/A'})</td>
                            </tr>
                            <tr>
                                <td><strong>Matrícula:</strong></td>
                                <td>${orden.matricula}</td>
                            </tr>
                            <tr>
                                <td><strong>Color:</strong></td>
                                <td>${orden.color || 'No especificado'}</td>
                            </tr>
                            <tr>
                                <td><strong>Kilometraje Ingreso:</strong></td>
                                <td>${orden.kilometraje_actual ? parseInt(orden.kilometraje_actual).toLocaleString() + ' km' : 'No especificado'}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-4">
                        <h5>Información de la Orden</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>ID Orden:</strong></td>
                                <td>#${orden.id_orden.toString().padStart(6, '0')}</td>
                            </tr>
                            <tr>
                                <td><strong>Fecha Ingreso:</strong></td>
                                <td>${orden.fecha_ingreso}</td>
                            </tr>
                            <tr>
                                <td><strong>Fecha Salida:</strong></td>
                                <td>${orden.fecha_salida || 'En taller'}</td>
                            </tr>
                            <tr>
                                <td><strong>Personal:</strong></td>
                                <td>${orden.nombre_personal ? orden.nombre_personal + ' ' + orden.apellido_personal : 'No asignado'}</td>
                            </tr>
                            <tr>
                                <td><strong>Tipo Servicio:</strong></td>
                                <td>${orden.tipo_servicio || 'No especificado'}</td>
                            </tr>
                            <tr>
                                <td><strong>Estado:</strong></td>
                                <td>
                                    <span class="badge ${orden.estado === 'en_proceso' ? 'bg-warning' : orden.estado === 'completado' ? 'bg-success' : 'bg-danger'}">
                                        ${orden.estado === 'en_proceso' ? 'En Proceso' : orden.estado === 'completado' ? 'Completado' : 'Cancelado'}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Facturado:</strong></td>
                                <td>
                                    <span class="badge ${orden.facturado ? 'bg-success' : 'bg-secondary'}">
                                        ${orden.facturado ? 'Sí' : 'No'}
                                    </span>
                                </td>
                            </tr>
                        </table>
                        
                        ${orden.observaciones ? `
                        <h6>Observaciones</h6>
                        <p class="text-muted">${orden.observaciones}</p>
                        ` : ''}
                    </div>
                </div>
                
                <h5>Detalles de Servicios</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Tipo Servicio</th>
                                <th>Descripción</th>
                                <th>Cantidad</th>
                                <th>Precio Unitario</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            if (orden.detalles && orden.detalles.length > 0) {
                var total = 0;
                orden.detalles.forEach(function(detalle) {
                    total += parseFloat(detalle.subtotal);
                    html += `
                        <tr>
                            <td>
                                <span class="badge bg-info">
                                    ${detalle.tipo_servicio}
                                </span>
                            </td>
                            <td>${detalle.descripcion}</td>
                            <td class="text-center">${detalle.cantidad}</td>
                            <td class="text-end">₲ ${parseInt(detalle.precio_unitario).toLocaleString()}</td>
                            <td class="text-end">₲ ${parseInt(detalle.subtotal).toLocaleString()}</td>
                        </tr>
                    `;
                });
                
                html += `
                        </tbody>
                        <tfoot class="table-dark">
                            <tr>
                                <th colspan="4" class="text-end">TOTAL:</th>
                                <th class="text-end">₲ ${parseInt(total).toLocaleString()}</th>
                            </tr>
                        </tfoot>
                `;
            } else {
                html += '<tr><td colspan="5" class="text-center">No hay servicios registrados</td></tr></tbody>';
            }
            
            html += `
                    </table>
                </div>
            `;
            
            $('#contenidoVerOrden').html(html);
            $('#modalVerOrden').modal('show');
            
        } catch (e) {
            Swal.fire('Error', 'Error al procesar la respuesta del servidor', 'error');
        }
    }).fail(function() {
        Swal.fire('Error', 'Error de conexión al servidor', 'error');
    });
}

function editarOrden(id) {
    window.location = "index.php?pagina=editar/orden_trabajo&id=" + id;
}

function imprimirOrden(id) {
    window.open("modelo/pdf/orden_trabajo_pdf.php?id=" + id, "_blank");
}

function imprimirOrdenModal() {
    if (ordenActual) {
        window.open("modelo/pdf/orden_trabajo_pdf.php?id=" + ordenActual, "_blank");
    }
}

function completarOrden(id) {
    $('#ordenIdCompletar').val(id);
    $('#modalCompletarOrden').modal('show');
}

function cancelarOrden(id) {
    Swal.fire({
        title: 'Cancelar Orden',
        text: '¿Está seguro de cancelar esta orden de trabajo?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("ajax/orden_trabajo_ajax.php", {
                accion: "cancelar",
                id: id
            }, function(data) {
                try {
                    var response = JSON.parse(data);
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                } catch (e) {
                    Swal.fire('Error', 'Error al procesar la respuesta', 'error');
                }
            }).fail(function() {
                Swal.fire('Error', 'Error de conexión', 'error');
            });
        }
    });
}

function crearFactura(id) {
    Swal.fire({
        title: 'Crear Factura',
        text: '¿Desea crear una factura basada en esta orden de trabajo?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, crear factura',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location = "index.php?pagina=nuevo/factura&orden=" + id;
        }
    });
}

function aplicarFiltros() {
    var tabla = $('#tablaOrdenes').DataTable();
    
    // Limpiar filtros anteriores
    tabla.columns().search('');
    
    // Aplicar filtros
    var filtroTexto = $('#filtroTexto').val();
    var filtroEstado = $('#filtroEstado').val();
    var filtroPersonal = $('#filtroPersonal').val();
    var filtroFacturado = $('#filtroFacturado').val();
    
    if (filtroTexto) {
        tabla.columns([2, 3]).search(filtroTexto);
    }
    if (filtroEstado) {
        tabla.column(8).search(filtroEstado);
    }
    if (filtroPersonal) {
        tabla.column(4).search(filtroPersonal);
    }
    if (filtroFacturado) {
        if (filtroFacturado === 'si') {
            tabla.column(9).search('Sí');
        } else if (filtroFacturado === 'no') {
            tabla.column(9).search('No');
        }
    }
    
    tabla.draw();
}

function limpiarFiltros() {
    $('#filtroTexto').val('');
    $('#filtroEstado').val('');
    $('#filtroPersonal').val('');
    $('#filtroFacturado').val('');
    $('#filtroFechaDesde').val('');
    $('#tablaOrdenes').DataTable().columns().search('').draw();
}

function actualizarTabla() {
    location.reload();
}
</script>