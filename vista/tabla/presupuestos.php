<?php
if ($_SESSION["tipo_usuario"] != "personal") {
    echo '<script>window.location = "index.php?pagina=inicio";</script>';
    return;
}

// Obtener lista de presupuestos
$presupuestos = ControladorPresupuesto::ctrMostrarPresupuestos(null, null);
?>

<div class="content-wrapper">
    <!-- Header de la página -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="content-title">
                        <i class="bi bi-file-earmark-text me-2"></i>
                        Gestión de Presupuestos
                    </h1>
                    <p class="content-subtitle">Administra cotizaciones y presupuestos</p>
                </div>
                <div class="col-sm-6">
                    <div class="content-actions">
                        <button type="button" class="btn btn-success me-2" onclick="window.location='index.php?pagina=nuevo/presupuesto'">
                            <i class="bi bi-file-earmark-plus"></i>
                            <span class="d-none d-sm-inline">Nuevo Presupuesto</span>
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
                                <?php echo count($presupuestos); ?>
                            </div>
                            <div class="info-card-text">Total Presupuestos</div>
                        </div>
                        <div class="info-card-icon bg-primary">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="info-card">
                        <div class="info-card-content">
                            <div class="info-card-number">
                                <?php 
                                $pendientes = array_filter($presupuestos, function($p) { return $p['estado'] == 'pendiente'; });
                                echo count($pendientes);
                                ?>
                            </div>
                            <div class="info-card-text">Pendientes</div>
                        </div>
                        <div class="info-card-icon bg-warning">
                            <i class="bi bi-clock"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="info-card">
                        <div class="info-card-content">
                            <div class="info-card-number">
                                <?php 
                                $aprobados = array_filter($presupuestos, function($p) { return $p['estado'] == 'aprobado'; });
                                echo count($aprobados);
                                ?>
                            </div>
                            <div class="info-card-text">Aprobados</div>
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
                                ₲ <?php 
                                $total = array_sum(array_column($presupuestos, 'total'));
                                echo number_format($total, 0, ',', '.');
                                ?>
                            </div>
                            <div class="info-card-text">Valor Total</div>
                        </div>
                        <div class="info-card-icon bg-info">
                            <i class="bi bi-currency-dollar"></i>
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
                                    <option value="pendiente">Pendiente</option>
                                    <option value="aprobado">Aprobado</option>
                                    <option value="rechazado">Rechazado</option>
                                    <option value="vencido">Vencido</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Personal:</label>
                                <select class="form-control" id="filtroPersonal">
                                    <option value="">Todos</option>
                                    <?php 
                                    $personal = array_unique(array_filter(array_column($presupuestos, 'nombre_personal')));
                                    foreach($personal as $p): 
                                    ?>
                                        <option value="<?php echo htmlspecialchars($p); ?>"><?php echo htmlspecialchars($p); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Fecha emisión desde:</label>
                                <input type="date" class="form-control" id="filtroFechaDesde">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Fecha emisión hasta:</label>
                                <input type="date" class="form-control" id="filtroFechaHasta">
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

            <!-- Tabla de presupuestos -->
            <div class="card card-custom">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-table me-2"></i>
                        Lista de Presupuestos
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
                    <table id="tablaPresupuestos" class="table table-bordered table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 10px">#</th>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Vehículo</th>
                                <th>Personal</th>
                                <th>Fecha Emisión</th>
                                <th>Fecha Validez</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Facturado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($presupuestos as $key => $value): ?>
                            <tr class="<?php echo ($value['fecha_validez'] < date('Y-m-d') && $value['estado'] == 'pendiente') ? 'table-warning' : ''; ?>">
                                <td><?php echo $key + 1; ?></td>
                                <td>
                                    <span class="badge bg-primary">
                                        #<?php echo str_pad($value["id_presupuesto"], 6, '0', STR_PAD_LEFT); ?>
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
                                    <i class="bi bi-calendar me-1"></i>
                                    <?php echo date("d/m/Y", strtotime($value["fecha_emision"])); ?>
                                    <small class="text-muted d-block">
                                        <?php echo date("H:i", strtotime($value["fecha_emision"])); ?>
                                    </small>
                                </td>
                                <td>
                                    <i class="bi bi-calendar-event me-1"></i>
                                    <?php echo date("d/m/Y", strtotime($value["fecha_validez"])); ?>
                                    <?php if ($value['fecha_validez'] < date('Y-m-d') && $value['estado'] == 'pendiente'): ?>
                                        <small class="text-danger d-block">
                                            <i class="bi bi-exclamation-triangle me-1"></i>Vencido
                                        </small>
                                    <?php else: ?>
                                        <?php 
                                        $diasRestantes = (strtotime($value['fecha_validez']) - time()) / (60 * 60 * 24);
                                        if ($diasRestantes > 0 && $diasRestantes <= 7 && $value['estado'] == 'pendiente'):
                                        ?>
                                            <small class="text-warning d-block">
                                                <i class="bi bi-clock me-1"></i><?php echo ceil($diasRestantes); ?> días restantes
                                            </small>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="text-success fw-bold">
                                        ₲ <?php echo number_format($value["total"], 0, ',', '.'); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($value["estado"] == "pendiente"): ?>
                                        <?php if ($value['fecha_validez'] < date('Y-m-d')): ?>
                                            <span class="badge bg-danger">
                                                <i class="bi bi-x-circle me-1"></i>Vencido
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">
                                                <i class="bi bi-clock me-1"></i>Pendiente
                                            </span>
                                        <?php endif; ?>
                                    <?php elseif ($value["estado"] == "aprobado"): ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>Aprobado
                                        </span>
                                    <?php elseif ($value["estado"] == "rechazado"): ?>
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle me-1"></i>Rechazado
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-question-circle me-1"></i><?php echo ucfirst($value["estado"]); ?>
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
                                                onclick="verPresupuesto(<?php echo $value['id_presupuesto']; ?>)"
                                                title="Ver detalles">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-info btn-sm" 
                                                onclick="imprimirPresupuesto(<?php echo $value['id_presupuesto']; ?>)"
                                                title="Imprimir PDF">
                                            <i class="bi bi-printer"></i>
                                        </button>
                                        <?php if ($value["estado"] == "pendiente" && $value['fecha_validez'] >= date('Y-m-d')): ?>
                                            <button type="button" class="btn btn-outline-warning btn-sm" 
                                                    onclick="editarPresupuesto(<?php echo $value['id_presupuesto']; ?>)"
                                                    title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                                                        data-bs-toggle="dropdown" title="Más acciones">
                                                    <i class="bi bi-three-dots"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#" onclick="cambiarEstadoPresupuesto(<?php echo $value['id_presupuesto']; ?>, 'aprobado')">
                                                        <i class="bi bi-check-circle me-2"></i>Aprobar
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="cambiarEstadoPresupuesto(<?php echo $value['id_presupuesto']; ?>, 'rechazado')">
                                                        <i class="bi bi-x-circle me-2"></i>Rechazar
                                                    </a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="#" onclick="eliminarPresupuesto(<?php echo $value['id_presupuesto']; ?>)">
                                                        <i class="bi bi-trash me-2"></i>Eliminar
                                                    </a></li>
                                                </ul>
                                            </div>
                                        <?php elseif ($value["estado"] == "aprobado" && !$value["facturado"]): ?>
                                            <button type="button" class="btn btn-outline-success btn-sm" 
                                                    onclick="crearFactura(<?php echo $value['id_presupuesto']; ?>)"
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
                                Mostrando <?php echo count($presupuestos); ?> presupuestos en total
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

<!-- Modal Ver Presupuesto -->
<div class="modal fade" id="modalVerPresupuesto" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    Detalles del Presupuesto
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body" id="contenidoVerPresupuesto">
                <!-- El contenido se carga dinámicamente -->
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cerrar
                </button>
                <button type="button" class="btn btn-primary" id="btnImprimirModal" onclick="imprimirPresupuestoModal()">
                    <i class="bi bi-printer me-1"></i>Imprimir PDF
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Inicializar DataTable
    $('#tablaPresupuestos').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "responsive": true,
        "order": [[5, "desc"]],
        "pageLength": 25,
        "columnDefs": [
            { "orderable": false, "targets": [10] },
            { "width": "80px", "targets": [1] },
            { "width": "100px", "targets": [7] },
            { "width": "80px", "targets": [8] },
            { "width": "80px", "targets": [9] },
            { "width": "150px", "targets": [10] }
        ]
    });

    // Filtro de búsqueda rápida
    $('#busquedaRapida').on('keyup', function() {
        $('#tablaPresupuestos').DataTable().search(this.value).draw();
    });

    // Marcar filas vencidas
    $('tr').each(function() {
        var fechaValidez = $(this).find('td:eq(6)').text();
        var estado = $(this).find('td:eq(8) .badge').text().trim();
        
        if (estado === 'Pendiente') {
            var fecha = fechaValidez.split('/');
            var fechaVencimiento = new Date(fecha[2], fecha[1] - 1, fecha[0]);
            var hoy = new Date();
            
            if (fechaVencimiento < hoy) {
                $(this).addClass('table-danger');
            } else if ((fechaVencimiento - hoy) / (1000 * 60 * 60 * 24) <= 7) {
                $(this).addClass('table-warning');
            }
        }
    });
});

var presupuestoActual = null;

function verPresupuesto(id) {
    presupuestoActual = id;
    
    $.post("ajax/presupuesto_ajax.php", {accion: "obtener", id: id}, function(data) {
        try {
            var presupuesto = JSON.parse(data);
            if (presupuesto.error) {
                Swal.fire('Error', presupuesto.error, 'error');
                return;
            }
            
            var html = `
                <div class="row mb-4">
                    <div class="col-md-8">
                        <h5>Información del Cliente</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Cliente:</strong></td>
                                <td>${presupuesto.nombre_cliente} ${presupuesto.apellido_cliente}</td>
                            </tr>
                            <tr>
                                <td><strong>Cédula:</strong></td>
                                <td>${presupuesto.cedula || 'No especificada'}</td>
                            </tr>
                            <tr>
                                <td><strong>Teléfono:</strong></td>
                                <td>${presupuesto.telefono_cliente || 'No especificado'}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>${presupuesto.email_cliente || 'No especificado'}</td>
                            </tr>
                        </table>
                        
                        <h5>Información del Vehículo</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Vehículo:</strong></td>
                                <td>${presupuesto.marca} ${presupuesto.modelo} (${presupuesto.año || 'N/A'})</td>
                            </tr>
                            <tr>
                                <td><strong>Matrícula:</strong></td>
                                <td>${presupuesto.matricula}</td>
                            </tr>
                            <tr>
                                <td><strong>Color:</strong></td>
                                <td>${presupuesto.color || 'No especificado'}</td>
                            </tr>
                            <tr>
                                <td><strong>Kilometraje:</strong></td>
                                <td>${presupuesto.kilometraje ? parseInt(presupuesto.kilometraje).toLocaleString() + ' km' : 'No especificado'}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-4">
                        <h5>Información del Presupuesto</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>ID:</strong></td>
                                <td>#${presupuesto.id_presupuesto.toString().padStart(6, '0')}</td>
                            </tr>
                            <tr>
                                <td><strong>Fecha Emisión:</strong></td>
                                <td>${presupuesto.fecha_emision}</td>
                            </tr>
                            <tr>
                                <td><strong>Válido hasta:</strong></td>
                                <td>${presupuesto.fecha_validez}</td>
                            </tr>
                            <tr>
                                <td><strong>Personal:</strong></td>
                                <td>${presupuesto.nombre_personal ? presupuesto.nombre_personal + ' ' + presupuesto.apellido_personal : 'No asignado'}</td>
                            </tr>
                            <tr>
                                <td><strong>Estado:</strong></td>
                                <td>
                                    <span class="badge ${presupuesto.estado === 'pendiente' ? 'bg-warning' : presupuesto.estado === 'aprobado' ? 'bg-success' : 'bg-danger'}">
                                        ${presupuesto.estado.charAt(0).toUpperCase() + presupuesto.estado.slice(1)}
                                    </span>
                                </td>
                            </tr>
                        </table>
                        
                        ${presupuesto.observaciones ? `
                        <h6>Observaciones</h6>
                        <p class="text-muted">${presupuesto.observaciones}</p>
                        ` : ''}
                    </div>
                </div>
                
                <h5>Detalles del Presupuesto</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
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
            `;
            
            if (presupuesto.detalles && presupuesto.detalles.length > 0) {
                presupuesto.detalles.forEach(function(detalle) {
                    html += `
                        <tr>
                            <td>
                                <span class="badge ${detalle.tipo === 'producto' ? 'bg-primary' : 'bg-info'}">
                                    ${detalle.tipo === 'producto' ? 'Producto' : 'Servicio'}
                                </span>
                            </td>
                            <td>${detalle.descripcion}</td>
                            <td class="text-center">${detalle.cantidad}</td>
                            <td class="text-end">₲ ${parseInt(detalle.precio_unitario).toLocaleString()}</td>
                            <td class="text-end">₲ ${parseInt(detalle.subtotal).toLocaleString()}</td>
                        </tr>
                    `;
                });
            } else {
                html += '<tr><td colspan="5" class="text-center">No hay detalles registrados</td></tr>';
            }
            
            html += `
                        </tbody>
                        <tfoot class="table-dark">
                            <tr>
                                <th colspan="4" class="text-end">TOTAL:</th>
                                <th class="text-end">₱ ${parseInt(presupuesto.total).toLocaleString()}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            `;
            
            $('#contenidoVerPresupuesto').html(html);
            $('#modalVerPresupuesto').modal('show');
            
        } catch (e) {
            Swal.fire('Error', 'Error al procesar la respuesta del servidor', 'error');
        }
    }).fail(function() {
        Swal.fire('Error', 'Error de conexión al servidor', 'error');
    });
}

function editarPresupuesto(id) {
    window.location = "index.php?pagina=editar/presupuesto&id=" + id;
}

function imprimirPresupuesto(id) {
    window.open("modelo/pdf/presupuesto_pdf.php?id=" + id, "_blank");
}

function imprimirPresupuestoModal() {
    if (presupuestoActual) {
        window.open("modelo/pdf/presupuesto_pdf.php?id=" + presupuestoActual, "_blank");
    }
}

function cambiarEstadoPresupuesto(id, nuevoEstado) {
    var titulo = 'Cambiar Estado del Presupuesto';
    var texto = '¿Está seguro de ' + (nuevoEstado === 'aprobado' ? 'aprobar' : 'rechazar') + ' este presupuesto?';
    var color = nuevoEstado === 'aprobado' ? '#28a745' : '#dc3545';
    
    Swal.fire({
        title: titulo,
        text: texto,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: color,
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, ' + (nuevoEstado === 'aprobado' ? 'aprobar' : 'rechazar'),
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("ajax/presupuesto_ajax.php", {
                accion: "cambiar_estado",
                id: id,
                estado: nuevoEstado
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
        text: '¿Desea crear una factura basada en este presupuesto?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, crear factura',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location = "index.php?pagina=nuevo/factura&presupuesto=" + id;
        }
    });
}

function eliminarPresupuesto(id) {
    Swal.fire({
        title: 'Eliminar Presupuesto',
        text: '¿Está seguro de eliminar este presupuesto? Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("ajax/presupuesto_ajax.php", {
                accion: "eliminar",
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

function aplicarFiltros() {
    var tabla = $('#tablaPresupuestos').DataTable();
    
    // Limpiar filtros anteriores
    tabla.columns().search('');
    
    // Aplicar filtros
    var filtroTexto = $('#filtroTexto').val();
    var filtroEstado = $('#filtroEstado').val();
    var filtroPersonal = $('#filtroPersonal').val();
    
    if (filtroTexto) {
        tabla.columns([2, 3]).search(filtroTexto);
    }
    if (filtroEstado) {
        tabla.column(8).search(filtroEstado);
    }
    if (filtroPersonal) {
        tabla.column(4).search(filtroPersonal);
    }
    
    tabla.draw();
}

function limpiarFiltros() {
    $('#filtroTexto').val('');
    $('#filtroEstado').val('');
    $('#filtroPersonal').val('');
    $('#filtroFechaDesde').val('');
    $('#filtroFechaHasta').val('');
    $('#tablaPresupuestos').DataTable().columns().search('').draw();
}

function actualizarTabla() {
    location.reload();
}

// Verificar presupuestos próximos a vencer cada minuto
setInterval(function() {
    $('.table-warning').each(function() {
        var fechaValidez = $(this).find('td:eq(6)').text().trim();
        if (fechaValidez) {
            var fecha = fechaValidez.split('/');
            var fechaVencimiento = new Date(fecha[2], fecha[1] - 1, fecha[0]);
            var hoy = new Date();
            
            if (fechaVencimiento < hoy) {
                $(this).removeClass('table-warning').addClass('table-danger');
                $(this).find('td:eq(8) .badge').removeClass('bg-warning').addClass('bg-danger').html('<i class="bi bi-x-circle me-1"></i>Vencido');
            }
        }
    });
}, 60000); // Cada minuto
</script>