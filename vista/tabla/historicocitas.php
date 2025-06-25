<?php
if ($_SESSION["tipo_usuario"] != "personal") {
    echo '<script>window.location = "index.php?pagina=inicio";</script>';
    return;
}

// Obtener histórico de citas
$historico = ControladorHistoricoCitas::ctrMostrarHistorico(null, null);
?>

<div class="content-wrapper">
    <!-- Header de la página -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="content-title">
                        <i class="bi bi-clock-history me-2"></i>
                        Histórico de Citas
                    </h1>
                    <p class="content-subtitle">Registro completo de citas pasadas</p>
                </div>
                <div class="col-sm-6">
                    <div class="content-actions">
                        <button type="button" class="btn btn-primary me-2" onclick="exportarHistorico()">
                            <i class="bi bi-download"></i>
                            <span class="d-none d-sm-inline">Exportar</span>
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
                                <?php echo count($historico); ?>
                            </div>
                            <div class="info-card-text">Total Registros</div>
                        </div>
                        <div class="info-card-icon bg-primary">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="info-card">
                        <div class="info-card-content">
                            <div class="info-card-number">
                                <?php 
                                $completadas = array_filter($historico, function($h) { return $h['estado'] == 'completada'; });
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
                                $canceladas = array_filter($historico, function($h) { return $h['estado'] == 'cancelada'; });
                                echo count($canceladas);
                                ?>
                            </div>
                            <div class="info-card-text">Canceladas</div>
                        </div>
                        <div class="info-card-icon bg-warning">
                            <i class="bi bi-x-circle"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="info-card">
                        <div class="info-card-content">
                            <div class="info-card-number">
                                <?php 
                                $noAsistio = array_filter($historico, function($h) { return $h['estado'] == 'no_asistio'; });
                                echo count($noAsistio);
                                ?>
                            </div>
                            <div class="info-card-text">No Asistió</div>
                        </div>
                        <div class="info-card-icon bg-danger">
                            <i class="bi bi-person-x"></i>
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
                                <label>Buscar cliente:</label>
                                <input type="text" class="form-control" id="filtroCliente" placeholder="Nombre del cliente">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Estado:</label>
                                <select class="form-control" id="filtroEstado">
                                    <option value="">Todos</option>
                                    <option value="completada">Completada</option>
                                    <option value="cancelada">Cancelada</option>
                                    <option value="no_asistio">No Asistió</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Personal:</label>
                                <select class="form-control" id="filtroPersonal">
                                    <option value="">Todos</option>
                                    <?php 
                                    $personal = array_unique(array_filter(array_column($historico, 'nombre_personal')));
                                    foreach($personal as $p): 
                                    ?>
                                        <option value="<?php echo htmlspecialchars($p); ?>"><?php echo htmlspecialchars($p); ?></option>
                                    <?php endforeach; ?>
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

            <!-- Tabla de histórico -->
            <div class="card card-custom">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-table me-2"></i>
                        Historial de Citas
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
                    <table id="tablaHistorico" class="table table-bordered table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 10px">#</th>
                                <th>Cliente</th>
                                <th>Fecha Cita</th>
                                <th>Hora</th>
                                <th>Motivo</th>
                                <th>Personal Atendió</th>
                                <th>Estado</th>
                                <th>Observaciones</th>
                                <th>Fecha Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historico as $key => $value): ?>
                            <tr>
                                <td><?php echo $key + 1; ?></td>
                                <td>
                                    <div class="client-info">
                                        <strong><?php echo htmlspecialchars($value["nombre_cliente"] . " " . $value["apellido_cliente"]); ?></strong>
                                        <?php if (!empty($value["telefono_cliente"])): ?>
                                            <small class="text-muted d-block">
                                                <i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($value["telefono_cliente"]); ?>
                                            </small>
                                        <?php endif; ?>
                                        <?php if (!empty($value["cedula"])): ?>
                                            <small class="text-muted d-block">
                                                <i class="bi bi-person-badge me-1"></i><?php echo htmlspecialchars($value["cedula"]); ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <i class="bi bi-calendar me-1"></i>
                                    <?php echo date("d/m/Y", strtotime($value["fecha_cita"])); ?>
                                    <small class="text-muted d-block">
                                        <?php 
                                        $fecha = new DateTime($value["fecha_cita"]);
                                        $dias = array('Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado');
                                        echo $dias[$fecha->format('w')];
                                        ?>
                                    </small>
                                </td>
                                <td>
                                    <?php if (!empty($value["hora_cita"])): ?>
                                        <i class="bi bi-clock me-1"></i>
                                        <?php echo date("H:i", strtotime($value["hora_cita"])); ?>
                                    <?php else: ?>
                                        <span class="text-muted">No especificada</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="motivo-text">
                                        <?php 
                                        $motivo = htmlspecialchars($value["motivo"]);
                                        if (strlen($motivo) > 50) {
                                            echo '<span title="' . $motivo . '">' . substr($motivo, 0, 50) . '...</span>';
                                        } else {
                                            echo $motivo;
                                        }
                                        ?>
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
                                    <?php if ($value["estado"] == "completada"): ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>Completada
                                        </span>
                                    <?php elseif ($value["estado"] == "cancelada"): ?>
                                        <span class="badge bg-warning">
                                            <i class="bi bi-x-circle me-1"></i>Cancelada
                                        </span>
                                    <?php elseif ($value["estado"] == "no_asistio"): ?>
                                        <span class="badge bg-danger">
                                            <i class="bi bi-person-x me-1"></i>No Asistió
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($value["observaciones"])): ?>
                                        <div class="observaciones-text">
                                            <?php 
                                            $observaciones = htmlspecialchars($value["observaciones"]);
                                            if (strlen($observaciones) > 40) {
                                                echo '<span title="' . $observaciones . '">' . substr($observaciones, 0, 40) . '...</span>';
                                            } else {
                                                echo $observaciones;
                                            }
                                            ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">Sin observaciones</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <i class="bi bi-calendar-plus me-1"></i>
                                    <?php echo date("d/m/Y", strtotime($value["fecha_registro"])); ?>
                                    <small class="text-muted d-block">
                                        <?php echo date("H:i", strtotime($value["fecha_registro"])); ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                                onclick="verDetalleHistorico(<?php echo $value['id_historicocita']; ?>)"
                                                title="Ver detalles">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <?php if ($value["estado"] == "completada"): ?>
                                            <button type="button" class="btn btn-outline-success btn-sm" 
                                                    onclick="crearNuevaCita(<?php echo $value['id_cliente']; ?>)"
                                                    title="Nueva cita para este cliente">
                                                <i class="bi bi-calendar-plus"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-outline-info btn-sm" 
                                                onclick="verHistorialCliente(<?php echo $value['id_cliente']; ?>)"
                                                title="Ver historial completo del cliente">
                                            <i class="bi bi-clock-history"></i>
                                        </button>
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
                                Mostrando <?php echo count($historico); ?> registros en total
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

<!-- Modal Ver Detalle Histórico -->
<div class="modal fade" id="modalVerHistorico" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="bi bi-clock-history me-2"></i>
                    Detalles del Registro Histórico
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body" id="contenidoVerHistorico">
                <!-- El contenido se carga dinámicamente -->
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Historial Completo del Cliente -->
<div class="modal fade" id="modalHistorialCliente" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="bi bi-person-lines-fill me-2"></i>
                    Historial Completo del Cliente
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body" id="contenidoHistorialCliente">
                <!-- El contenido se carga dinámicamente -->
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cerrar
                </button>
                <button type="button" class="btn btn-success" id="btnNuevaCitaModal" onclick="crearNuevaCitaModal()">
                    <i class="bi bi-calendar-plus me-1"></i>Nueva Cita
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Inicializar DataTable
    $('#tablaHistorico').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "responsive": true,
        "order": [[8, "desc"]],
        "pageLength": 25,
        "columnDefs": [
            { "orderable": false, "targets": [9] },
            { "width": "150px", "targets": [1] },
            { "width": "100px", "targets": [2] },
            { "width": "80px", "targets": [3] },
            { "width": "200px", "targets": [4] },
            { "width": "100px", "targets": [6] },
            { "width": "150px", "targets": [9] }
        ]
    });

    // Filtro de búsqueda rápida
    $('#busquedaRapida').on('keyup', function() {
        $('#tablaHistorico').DataTable().search(this.value).draw();
    });

    // Tooltips para textos largos
    $('[title]').tooltip();
});

var clienteActual = null;

function verDetalleHistorico(id) {
    $.post("ajax/historicocitas_ajax.php", {accion: "obtener", id: id}, function(data) {
        try {
            var registro = JSON.parse(data);
            if (registro.error) {
                Swal.fire('Error', registro.error, 'error');
                return;
            }
            
            var html = `
                <div class="row">
                    <div class="col-md-6">
                        <h5>Información del Cliente</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Cliente:</strong></td>
                                <td>${registro.nombre_cliente} ${registro.apellido_cliente}</td>
                            </tr>
                            <tr>
                                <td><strong>Cédula:</strong></td>
                                <td>${registro.cedula || 'No especificada'}</td>
                            </tr>
                            <tr>
                                <td><strong>Teléfono:</strong></td>
                                <td>${registro.telefono_cliente || 'No especificado'}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>${registro.email_cliente || 'No especificado'}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>Información de la Cita</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Fecha:</strong></td>
                                <td>${registro.fecha_cita}</td>
                            </tr>
                            <tr>
                                <td><strong>Hora:</strong></td>
                                <td>${registro.hora_cita || 'No especificada'}</td>
                            </tr>
                            <tr>
                                <td><strong>Personal:</strong></td>
                                <td>${registro.nombre_personal ? registro.nombre_personal + ' ' + registro.apellido_personal : 'No asignado'}</td>
                            </tr>
                            <tr>
                                <td><strong>Estado:</strong></td>
                                <td>
                                    <span class="badge ${registro.estado === 'completada' ? 'bg-success' : registro.estado === 'cancelada' ? 'bg-warning' : 'bg-danger'}">
                                        ${registro.estado === 'completada' ? 'Completada' : registro.estado === 'cancelada' ? 'Cancelada' : 'No Asistió'}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Registrado:</strong></td>
                                <td>${registro.fecha_registro}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-12">
                        <h5>Motivo de la Cita</h5>
                        <div class="alert alert-info">
                            ${registro.motivo}
                        </div>
                        
                        ${registro.observaciones ? `
                        <h5>Observaciones</h5>
                        <div class="alert alert-secondary">
                            ${registro.observaciones}
                        </div>
                        ` : ''}
                    </div>
                </div>
            `;
            
            $('#contenidoVerHistorico').html(html);
            $('#modalVerHistorico').modal('show');
            
        } catch (e) {
            Swal.fire('Error', 'Error al procesar la respuesta del servidor', 'error');
        }
    }).fail(function() {
        Swal.fire('Error', 'Error de conexión al servidor', 'error');
    });
}

function verHistorialCliente(id) {
    clienteActual = id;
    
    $.post("ajax/historicocitas_ajax.php", {accion: "historial_cliente", id_cliente: id}, function(data) {
        try {
            var historial = JSON.parse(data);
            if (historial.error) {
                Swal.fire('Error', historial.error, 'error');
                return;
            }
            
            var html = `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h5>Cliente: ${historial.cliente.nombre} ${historial.cliente.apellido}</h5>
                        <p class="text-muted">Cédula: ${historial.cliente.cedula || 'No especificada'}</p>
                        <p class="text-muted">Teléfono: ${historial.cliente.telefono || 'No especificado'}</p>
                    </div>
                    <div class="col-md-6">
                        <div class="text-end">
                            <span class="badge bg-primary fs-6">Total de citas: ${historial.citas.length}</span>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Motivo</th>
                                <th>Personal</th>
                                <th>Estado</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            if (historial.citas && historial.citas.length > 0) {
                historial.citas.forEach(function(cita) {
                    html += `
                        <tr>
                            <td>${cita.fecha_cita}</td>
                            <td>${cita.hora_cita || 'N/A'}</td>
                            <td>${cita.motivo.length > 50 ? cita.motivo.substring(0, 50) + '...' : cita.motivo}</td>
                            <td>${cita.nombre_personal ? cita.nombre_personal + ' ' + cita.apellido_personal : 'No asignado'}</td>
                            <td>
                                <span class="badge ${cita.estado === 'completada' ? 'bg-success' : cita.estado === 'cancelada' ? 'bg-warning' : 'bg-danger'}">
                                    ${cita.estado === 'completada' ? 'Completada' : cita.estado === 'cancelada' ? 'Cancelada' : 'No Asistió'}
                                </span>
                            </td>
                            <td>${cita.observaciones ? (cita.observaciones.length > 30 ? cita.observaciones.substring(0, 30) + '...' : cita.observaciones) : 'Sin observaciones'}</td>
                        </tr>
                    `;
                });
            } else {
                html += '<tr><td colspan="6" class="text-center">No hay registros de citas para este cliente</td></tr>';
            }
            
            html += '</tbody></table></div>';
            
            $('#contenidoHistorialCliente').html(html);
            $('#modalHistorialCliente').modal('show');
            
        } catch (e) {
            Swal.fire('Error', 'Error al procesar la respuesta del servidor', 'error');
        }
    }).fail(function() {
        Swal.fire('Error', 'Error de conexión al servidor', 'error');
    });
}

function crearNuevaCita(id_cliente) {
    window.location = "index.php?pagina=agendamiento&cliente=" + id_cliente;
}

function crearNuevaCitaModal() {
    if (clienteActual) {
        $('#modalHistorialCliente').modal('hide');
        window.location = "index.php?pagina=agendamiento&cliente=" + clienteActual;
    }
}

function exportarHistorico() {
    // Implementar exportación de datos
    Swal.fire({
        title: 'Exportar Histórico',
        text: 'Seleccione el formato de exportación',
        icon: 'question',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: 'Excel',
        denyButtonText: 'PDF',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#28a745',
        denyButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            // Exportar a Excel
            window.open("exportar/historico_excel.php", "_blank");
        } else if (result.isDenied) {
            // Exportar a PDF
            window.open("exportar/historico_pdf.php", "_blank");
        }
    });
}

function aplicarFiltros() {
    var tabla = $('#tablaHistorico').DataTable();
    
    // Limpiar filtros anteriores
    tabla.columns().search('');
    
    // Aplicar filtros
    var filtroCliente = $('#filtroCliente').val();
    var filtroEstado = $('#filtroEstado').val();
    var filtroPersonal = $('#filtroPersonal').val();
    
    if (filtroCliente) {
        tabla.column(1).search(filtroCliente);
    }
    if (filtroEstado) {
        tabla.column(6).search(filtroEstado);
    }
    if (filtroPersonal) {
        tabla.column(5).search(filtroPersonal);
    }
    
    tabla.draw();
}

function limpiarFiltros() {
    $('#filtroCliente').val('');
    $('#filtroEstado').val('');
    $('#filtroPersonal').val('');
    $('#filtroFechaDesde').val('');
    $('#filtroFechaHasta').val('');
    $('#tablaHistorico').DataTable().columns().search('').draw();
}

function actualizarTabla() {
    location.reload();
}

// Función para mostrar estadísticas rápidas
function mostrarEstadisticas() {
    // Esta función puede expandirse para mostrar gráficos o estadísticas detalladas
    var totalRegistros = <?php echo count($historico); ?>;
    var completadas = <?php echo count(array_filter($historico, function($h) { return $h['estado'] == 'completada'; })); ?>;
    var porcentajeExito = totalRegistros > 0 ? ((completadas / totalRegistros) * 100).toFixed(1) : 0;
    
    Swal.fire({
        title: 'Estadísticas Rápidas',
        html: `
            <div class="text-start">
                <p><strong>Total de registros:</strong> ${totalRegistros}</p>
                <p><strong>Citas completadas:</strong> ${completadas}</p>
                <p><strong>Tasa de éxito:</strong> ${porcentajeExito}%</p>
            </div>
        `,
        icon: 'info',
        confirmButtonText: 'Entendido'
    });
}
</script>