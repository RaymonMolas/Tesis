<?php
if ($_SESSION["tipo_usuario"] != "personal") {
    echo '<script>window.location = "index.php?pagina=inicio";</script>';
    return;
}

// Obtener lista de facturas
$facturas = ControladorFactura::ctrMostrarFacturas(null, null);
?>

<div class="content-wrapper">
    <!-- Header de la página -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="content-title">
                        <i class="bi bi-receipt me-2"></i>
                        Gestión de Facturas
                    </h1>
                    <p class="content-subtitle">Administra facturas y ventas</p>
                </div>
                <div class="col-sm-6">
                    <div class="content-actions">
                        <button type="button" class="btn btn-success me-2" onclick="window.location='index.php?pagina=nuevo/factura'">
                            <i class="bi bi-receipt"></i>
                            <span class="d-none d-sm-inline">Nueva Factura</span>
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
                                <?php echo count($facturas); ?>
                            </div>
                            <div class="info-card-text">Total Facturas</div>
                        </div>
                        <div class="info-card-icon bg-primary">
                            <i class="bi bi-receipt"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="info-card">
                        <div class="info-card-content">
                            <div class="info-card-number">
                                <?php 
                                $pendientes = array_filter($facturas, function($f) { return $f['estado'] == 'pendiente'; });
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
                                $pagadas = array_filter($facturas, function($f) { return $f['estado'] == 'pagada'; });
                                echo count($pagadas);
                                ?>
                            </div>
                            <div class="info-card-text">Pagadas</div>
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
                                $total = array_sum(array_column($facturas, 'total'));
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
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Número de factura:</label>
                                <input type="text" class="form-control" id="filtroNumero" placeholder="Número">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Cliente:</label>
                                <input type="text" class="form-control" id="filtroCliente" placeholder="Nombre del cliente">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Estado:</label>
                                <select class="form-control" id="filtroEstado">
                                    <option value="">Todos</option>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="pagada">Pagada</option>
                                    <option value="anulada">Anulada</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Fecha desde:</label>
                                <input type="date" class="form-control" id="filtroFechaDesde">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Fecha hasta:</label>
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

            <!-- Tabla de facturas -->
            <div class="card card-custom">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-table me-2"></i>
                        Lista de Facturas
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
                    <table id="tablaFacturas" class="table table-bordered table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 10px">#</th>
                                <th>Número Factura</th>
                                <th>Cliente</th>
                                <th>Personal</th>
                                <th>Fecha Emisión</th>
                                <th>Tipo</th>
                                <th>Subtotal</th>
                                <th>IVA</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Método Pago</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($facturas as $key => $value): ?>
                            <tr>
                                <td><?php echo $key + 1; ?></td>
                                <td>
                                    <span class="badge bg-primary">
                                        <?php echo htmlspecialchars($value["numero_factura"]); ?>
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
                                    <?php if ($value["tipo_factura"] == "contado"): ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-cash me-1"></i>Contado
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">
                                            <i class="bi bi-credit-card me-1"></i>Crédito
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="text-muted">
                                        ₲ <?php echo number_format($value["subtotal"], 0, ',', '.'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted">
                                        ₲ <?php echo number_format($value["iva"], 0, ',', '.'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-success fw-bold">
                                        ₲ <?php echo number_format($value["total"], 0, ',', '.'); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($value["estado"] == "pendiente"): ?>
                                        <span class="badge bg-warning">
                                            <i class="bi bi-clock me-1"></i>Pendiente
                                        </span>
                                    <?php elseif ($value["estado"] == "pagada"): ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>Pagada
                                        </span>
                                    <?php elseif ($value["estado"] == "anulada"): ?>
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle me-1"></i>Anulada
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo ucfirst(htmlspecialchars($value["metodo_pago"])); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                                onclick="verFactura(<?php echo $value['id_factura']; ?>)"
                                                title="Ver detalles">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-info btn-sm" 
                                                onclick="imprimirFactura(<?php echo $value['id_factura']; ?>)"
                                                title="Imprimir PDF">
                                            <i class="bi bi-printer"></i>
                                        </button>
                                        <?php if ($value["estado"] == "pendiente"): ?>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                                                        data-bs-toggle="dropdown" title="Más acciones">
                                                    <i class="bi bi-three-dots"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#" onclick="marcarPagada(<?php echo $value['id_factura']; ?>)">
                                                        <i class="bi bi-check-circle me-2"></i>Marcar como Pagada
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="editarFactura(<?php echo $value['id_factura']; ?>)">
                                                        <i class="bi bi-pencil me-2"></i>Editar
                                                    </a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="#" onclick="anularFactura(<?php echo $value['id_factura']; ?>)">
                                                        <i class="bi bi-x-circle me-2"></i>Anular Factura
                                                    </a></li>
                                                </ul>
                                            </div>
                                        <?php elseif ($value["estado"] == "pagada"): ?>
                                            <button type="button" class="btn btn-outline-success btn-sm" 
                                                    onclick="verComprobantePago(<?php echo $value['id_factura']; ?>)"
                                                    title="Ver comprobante">
                                                <i class="bi bi-receipt-cutoff"></i>
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
                                Mostrando <?php echo count($facturas); ?> facturas en total
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

<!-- Modal Ver Factura -->
<div class="modal fade" id="modalVerFactura" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="bi bi-receipt me-2"></i>
                    Detalles de la Factura
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body" id="contenidoVerFactura">
                <!-- El contenido se carga dinámicamente -->
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cerrar
                </button>
                <button type="button" class="btn btn-primary" id="btnImprimirModal" onclick="imprimirFacturaModal()">
                    <i class="bi bi-printer me-1"></i>Imprimir PDF
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Marcar como Pagada -->
<div class="modal fade" id="modalMarcarPagada" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formMarcarPagada">
                <div class="modal-header">
                    <h4 class="modal-title">
                        <i class="bi bi-check-circle me-2"></i>
                        Marcar Factura como Pagada
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <input type="hidden" id="facturaIdPago" name="id_factura">
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Método de Pago</label>
                        <select class="form-control" name="metodo_pago" required>
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta_debito">Tarjeta de Débito</option>
                            <option value="tarjeta_credito">Tarjeta de Crédito</option>
                            <option value="transferencia">Transferencia Bancaria</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Fecha de Pago</label>
                        <input type="datetime-local" class="form-control" name="fecha_pago" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea class="form-control" name="observaciones" rows="3" placeholder="Observaciones sobre el pago (opcional)"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i>Confirmar Pago
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Inicializar DataTable
    $('#tablaFacturas').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "responsive": true,
        "order": [[4, "desc"]],
        "pageLength": 25,
        "columnDefs": [
            { "orderable": false, "targets": [11] },
            { "width": "100px", "targets": [1] },
            { "width": "80px", "targets": [8] },
            { "width": "80px", "targets": [9] },
            { "width": "150px", "targets": [11] }
        ]
    });

    // Filtro de búsqueda rápida
    $('#busquedaRapida').on('keyup', function() {
        $('#tablaFacturas').DataTable().search(this.value).draw();
    });

    // Establecer fecha y hora actual para el pago
    var now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    $('input[name="fecha_pago"]').val(now.toISOString().slice(0, 16));

    // Envío del formulario de pago
    $('#formMarcarPagada').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        formData.append('accion', 'marcar_pagada');
        
        $.ajax({
            url: "ajax/factura_ajax.php",
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
                            $('#modalMarcarPagada').modal('hide');
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

var facturaActual = null;

function verFactura(id) {
    facturaActual = id;
    
    $.post("ajax/factura_ajax.php", {accion: "obtener", id: id}, function(data) {
        try {
            var factura = JSON.parse(data);
            if (factura.error) {
                Swal.fire('Error', factura.error, 'error');
                return;
            }
            
            var html = `
                <div class="row mb-4">
                    <div class="col-md-8">
                        <h5>Información del Cliente</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Cliente:</strong></td>
                                <td>${factura.nombre_cliente} ${factura.apellido_cliente}</td>
                            </tr>
                            <tr>
                                <td><strong>Cédula:</strong></td>
                                <td>${factura.cedula || 'No especificada'}</td>
                            </tr>
                            <tr>
                                <td><strong>Teléfono:</strong></td>
                                <td>${factura.telefono_cliente || 'No especificado'}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>${factura.email_cliente || 'No especificado'}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-4">
                        <h5>Información de la Factura</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Número:</strong></td>
                                <td>${factura.numero_factura}</td>
                            </tr>
                            <tr>
                                <td><strong>Fecha Emisión:</strong></td>
                                <td>${factura.fecha_emision}</td>
                            </tr>
                            <tr>
                                <td><strong>Tipo:</strong></td>
                                <td>${factura.tipo_factura === 'contado' ? 'Contado' : 'Crédito'}</td>
                            </tr>
                            <tr>
                                <td><strong>Personal:</strong></td>
                                <td>${factura.nombre_personal ? factura.nombre_personal + ' ' + factura.apellido_personal : 'No asignado'}</td>
                            </tr>
                            <tr>
                                <td><strong>Estado:</strong></td>
                                <td>
                                    <span class="badge ${factura.estado === 'pendiente' ? 'bg-warning' : factura.estado === 'pagada' ? 'bg-success' : 'bg-danger'}">
                                        ${factura.estado.charAt(0).toUpperCase() + factura.estado.slice(1)}
                                    </span>
                                </td>
                            </tr>
                            ${factura.fecha_pago ? `
                            <tr>
                                <td><strong>Fecha Pago:</strong></td>
                                <td>${factura.fecha_pago}</td>
                            </tr>` : ''}
                        </table>
                        
                        ${factura.observaciones ? `
                        <h6>Observaciones</h6>
                        <p class="text-muted">${factura.observaciones}</p>
                        ` : ''}
                    </div>
                </div>
                
                <h5>Detalles de la Factura</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Tipo</th>
                                <th>Descripción</th>
                                <th>Cantidad</th>
                                <th>Precio Unitario</th>
                                <th>Descuento</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            if (factura.detalles && factura.detalles.length > 0) {
                factura.detalles.forEach(function(detalle) {
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
                            <td class="text-end">₲ ${parseInt(detalle.descuento || 0).toLocaleString()}</td>
                            <td class="text-end">₲ ${parseInt(detalle.subtotal).toLocaleString()}</td>
                        </tr>
                    `;
                });
            } else {
                html += '<tr><td colspan="6" class="text-center">No hay detalles registrados</td></tr>';
            }
            
            html += `
                        </tbody>
                        <tfoot class="table-dark">
                            <tr>
                                <th colspan="5" class="text-end">SUBTOTAL:</th>
                                <th class="text-end">₲ ${parseInt(factura.subtotal).toLocaleString()}</th>
                            </tr>
                            <tr>
                                <th colspan="5" class="text-end">IVA (${factura.iva_porcentaje || 10}%):</th>
                                <th class="text-end">₲ ${parseInt(factura.iva).toLocaleString()}</th>
                            </tr>
                            <tr>
                                <th colspan="5" class="text-end">TOTAL:</th>
                                <th class="text-end">₲ ${parseInt(factura.total).toLocaleString()}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            `;
            
            $('#contenidoVerFactura').html(html);
            $('#modalVerFactura').modal('show');
            
        } catch (e) {
            Swal.fire('Error', 'Error al procesar la respuesta del servidor', 'error');
        }
    }).fail(function() {
        Swal.fire('Error', 'Error de conexión al servidor', 'error');
    });
}

function imprimirFactura(id) {
    window.open("modelo/pdf/factura_pdf.php?id=" + id, "_blank");
}

function imprimirFacturaModal() {
    if (facturaActual) {
        window.open("modelo/pdf/factura_pdf.php?id=" + facturaActual, "_blank");
    }
}

function editarFactura(id) {
    window.location = "index.php?pagina=editar/factura&id=" + id;
}

function marcarPagada(id) {
    $('#facturaIdPago').val(id);
    $('#modalMarcarPagada').modal('show');
}

function anularFactura(id) {
    Swal.fire({
        title: 'Anular Factura',
        text: '¿Está seguro de anular esta factura? Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, anular',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("ajax/factura_ajax.php", {
                accion: "anular",
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

function verComprobantePago(id) {
    // Implementar vista de comprobante de pago si es necesario
    Swal.fire('Info', 'Función de comprobante de pago en desarrollo', 'info');
}

function aplicarFiltros() {
    var tabla = $('#tablaFacturas').DataTable();
    
    // Limpiar filtros anteriores
    tabla.columns().search('');
    
    // Aplicar filtros
    var filtroNumero = $('#filtroNumero').val();
    var filtroCliente = $('#filtroCliente').val();
    var filtroEstado = $('#filtroEstado').val();
    
    if (filtroNumero) {
        tabla.column(1).search(filtroNumero);
    }
    if (filtroCliente) {
        tabla.column(2).search(filtroCliente);
    }
    if (filtroEstado) {
        tabla.column(9).search(filtroEstado);
    }
    
    tabla.draw();
}

function limpiarFiltros() {
    $('#filtroNumero').val('');
    $('#filtroCliente').val('');
    $('#filtroEstado').val('');
    $('#filtroFechaDesde').val('');
    $('#filtroFechaHasta').val('');
    $('#tablaFacturas').DataTable().columns().search('').draw();
}

function actualizarTabla() {
    location.reload();
}
</script>