<?php
if ($_SESSION["tipo_usuario"] != "personal") {
    echo '<script>window.location = "index.php?pagina=inicio";</script>';
    return;
}

// Obtener lista de personal
$personal = ControladorPersonal::ctrMostrarPersonal(null, null);
?>

<div class="content-wrapper">
    <!-- Header de la página -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="content-title">
                        <i class="bi bi-people-fill me-2"></i>
                        Gestión de Personal
                    </h1>
                    <p class="content-subtitle">Administra el personal del taller</p>
                </div>
                <div class="col-sm-6">
                    <div class="content-actions">
                        <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#modalNuevoPersonal">
                            <i class="bi bi-person-plus"></i>
                            <span class="d-none d-sm-inline">Nuevo Personal</span>
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
                            <div class="info-card-number" id="totalPersonal">
                                <?php echo count($personal); ?>
                            </div>
                            <div class="info-card-text">Total Personal</div>
                        </div>
                        <div class="info-card-icon bg-primary">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="info-card">
                        <div class="info-card-content">
                            <div class="info-card-number" id="personalActivo">
                                <?php 
                                $activos = array_filter($personal, function($p) { return $p['estado'] == 'activo'; });
                                echo count($activos);
                                ?>
                            </div>
                            <div class="info-card-text">Personal Activo</div>
                        </div>
                        <div class="info-card-icon bg-success">
                            <i class="bi bi-person-check"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="info-card">
                        <div class="info-card-content">
                            <div class="info-card-number" id="personalInactivo">
                                <?php 
                                $inactivos = array_filter($personal, function($p) { return $p['estado'] == 'inactivo'; });
                                echo count($inactivos);
                                ?>
                            </div>
                            <div class="info-card-text">Personal Inactivo</div>
                        </div>
                        <div class="info-card-icon bg-warning">
                            <i class="bi bi-person-x"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="info-card">
                        <div class="info-card-content">
                            <div class="info-card-number">
                                <?php 
                                $cargos = array_unique(array_column($personal, 'cargo'));
                                echo count($cargos);
                                ?>
                            </div>
                            <div class="info-card-text">Cargos Diferentes</div>
                        </div>
                        <div class="info-card-icon bg-info">
                            <i class="bi bi-diagram-3"></i>
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
                                <label>Buscar por nombre o cédula:</label>
                                <input type="text" class="form-control" id="filtroTexto" placeholder="Nombre, apellido o cédula">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Cargo:</label>
                                <select class="form-control" id="filtroCargo">
                                    <option value="">Todos los cargos</option>
                                    <?php 
                                    $cargos = array_unique(array_column($personal, 'cargo'));
                                    foreach($cargos as $cargo): 
                                    ?>
                                        <option value="<?php echo htmlspecialchars($cargo); ?>"><?php echo htmlspecialchars($cargo); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Estado:</label>
                                <select class="form-control" id="filtroEstado">
                                    <option value="">Todos</option>
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Fecha ingreso desde:</label>
                                <input type="date" class="form-control" id="filtroFechaDesde">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Fecha ingreso hasta:</label>
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

            <!-- Tabla de personal -->
            <div class="card card-custom">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-table me-2"></i>
                        Lista de Personal
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
                    <table id="tablaPersonal" class="table table-bordered table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 10px">#</th>
                                <th>Nombre Completo</th>
                                <th>Cédula</th>
                                <th>Cargo</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Fecha Ingreso</th>
                                <th>Salario</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($personal as $key => $value): ?>
                            <tr>
                                <td><?php echo $key + 1; ?></td>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            <i class="bi bi-person-circle"></i>
                                        </div>
                                        <div class="user-details">
                                            <strong><?php echo htmlspecialchars($value["nombre"] . " " . $value["apellido"]); ?></strong>
                                            <?php if (!empty($value["direccion"])): ?>
                                                <small class="text-muted d-block"><?php echo htmlspecialchars($value["direccion"]); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-info"><?php echo htmlspecialchars($value["cedula"]); ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-secondary"><?php echo htmlspecialchars($value["cargo"]); ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($value["telefono"])): ?>
                                        <a href="tel:<?php echo $value["telefono"]; ?>" class="text-decoration-none">
                                            <i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($value["telefono"]); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">No especificado</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($value["email"])): ?>
                                        <a href="mailto:<?php echo $value["email"]; ?>" class="text-decoration-none">
                                            <i class="bi bi-envelope me-1"></i><?php echo htmlspecialchars($value["email"]); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">No especificado</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <i class="bi bi-calendar me-1"></i>
                                    <?php echo date("d/m/Y", strtotime($value["fecha_ingreso"])); ?>
                                </td>
                                <td>
                                    <?php if (!empty($value["salario"])): ?>
                                        <span class="text-success">
                                            <i class="bi bi-currency-dollar me-1"></i>
                                            <?php echo "₲ " . number_format($value["salario"], 0, ',', '.'); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">No especificado</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($value["estado"] == "activo"): ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>Activo
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle me-1"></i>Inactivo
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                                onclick="verPersonal(<?php echo $value['id_personal']; ?>)"
                                                title="Ver detalles">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-warning btn-sm" 
                                                onclick="editarPersonal(<?php echo $value['id_personal']; ?>)"
                                                title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <?php if ($value["estado"] == "activo"): ?>
                                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                                    onclick="cambiarEstadoPersonal(<?php echo $value['id_personal']; ?>, 'inactivo')"
                                                    title="Desactivar">
                                                <i class="bi bi-person-x"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline-success btn-sm" 
                                                    onclick="cambiarEstadoPersonal(<?php echo $value['id_personal']; ?>, 'activo')"
                                                    title="Activar">
                                                <i class="bi bi-person-check"></i>
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
                                Mostrando <?php echo count($personal); ?> registros en total
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

<!-- Modal Nuevo Personal -->
<div class="modal fade" id="modalNuevoPersonal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" id="formNuevoPersonal">
                <div class="modal-header">
                    <h4 class="modal-title">
                        <i class="bi bi-person-plus me-2"></i>
                        Registrar Nuevo Personal
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Nombre *</label>
                                <input type="text" class="form-control" name="nombre" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Apellido *</label>
                                <input type="text" class="form-control" name="apellido" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Cédula *</label>
                                <input type="text" class="form-control" name="cedula" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Teléfono *</label>
                                <input type="text" class="form-control" name="telefono" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Cargo *</label>
                                <select class="form-control" name="cargo" required>
                                    <option value="">Seleccionar cargo</option>
                                    <option value="Administrador">Administrador</option>
                                    <option value="Mecánico">Mecánico</option>
                                    <option value="Electricista">Electricista</option>
                                    <option value="Soldador">Soldador</option>
                                    <option value="Chapista">Chapista</option>
                                    <option value="Pintor">Pintor</option>
                                    <option value="Recepcionista">Recepcionista</option>
                                    <option value="Vendedor">Vendedor</option>
                                    <option value="Supervisor">Supervisor</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Fecha de Ingreso *</label>
                                <input type="date" class="form-control" name="fecha_ingreso" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Salario</label>
                                <input type="number" class="form-control" name="salario" step="0.01" min="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Dirección</label>
                        <textarea class="form-control" name="direccion" rows="3"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i>Registrar Personal
                    </button>
                </div>

                <?php
                $registrarPersonal = new ControladorPersonal();
                $registrarPersonal->ctrRegistrarPersonal();
                ?>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ver Personal -->
<div class="modal fade" id="modalVerPersonal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="bi bi-person-lines-fill me-2"></i>
                    Detalles del Personal
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body" id="contenidoVerPersonal">
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

<script>
$(document).ready(function() {
    // Inicializar DataTable
    $('#tablaPersonal').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "responsive": true,
        "order": [[1, "asc"]],
        "pageLength": 25,
        "columnDefs": [
            { "orderable": false, "targets": [9] }
        ]
    });

    // Filtro de búsqueda rápida
    $('#busquedaRapida').on('keyup', function() {
        $('#tablaPersonal').DataTable().search(this.value).draw();
    });

    // Validación del formulario
    $('#formNuevoPersonal').on('submit', function(e) {
        var cedula = $('input[name="cedula"]').val();
        var telefono = $('input[name="telefono"]').val();
        
        // Validar formato de cédula (números y guión)
        if (!/^\d{1,8}-?\d?$/.test(cedula)) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'El formato de la cédula no es válido'
            });
            return false;
        }
        
        // Validar formato de teléfono
        if (!/^[\d\-\s\+\(\)]{7,15}$/.test(telefono)) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'El formato del teléfono no es válido'
            });
            return false;
        }
    });
});

function verPersonal(id) {
    $.post("ajax/personal_ajax.php", {accion: "obtener", id: id}, function(data) {
        try {
            var personal = JSON.parse(data);
            if (personal.error) {
                Swal.fire('Error', personal.error, 'error');
                return;
            }
            
            var html = `
                <div class="row">
                    <div class="col-md-6">
                        <h5>Información Personal</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Nombre:</strong></td>
                                <td>${personal.nombre} ${personal.apellido}</td>
                            </tr>
                            <tr>
                                <td><strong>Cédula:</strong></td>
                                <td>${personal.cedula}</td>
                            </tr>
                            <tr>
                                <td><strong>Teléfono:</strong></td>
                                <td>${personal.telefono}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>${personal.email || 'No especificado'}</td>
                            </tr>
                            <tr>
                                <td><strong>Dirección:</strong></td>
                                <td>${personal.direccion || 'No especificada'}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>Información Laboral</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Cargo:</strong></td>
                                <td>${personal.cargo}</td>
                            </tr>
                            <tr>
                                <td><strong>Fecha Ingreso:</strong></td>
                                <td>${personal.fecha_ingreso}</td>
                            </tr>
                            <tr>
                                <td><strong>Salario:</strong></td>
                                <td>${personal.salario ? '₲ ' + parseInt(personal.salario).toLocaleString() : 'No especificado'}</td>
                            </tr>
                            <tr>
                                <td><strong>Estado:</strong></td>
                                <td>
                                    <span class="badge ${personal.estado === 'activo' ? 'bg-success' : 'bg-danger'}">
                                        ${personal.estado === 'activo' ? 'Activo' : 'Inactivo'}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Registrado:</strong></td>
                                <td>${personal.fecha_creacion}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            `;
            
            $('#contenidoVerPersonal').html(html);
            $('#modalVerPersonal').modal('show');
            
        } catch (e) {
            Swal.fire('Error', 'Error al procesar la respuesta del servidor', 'error');
        }
    }).fail(function() {
        Swal.fire('Error', 'Error de conexión al servidor', 'error');
    });
}

function editarPersonal(id) {
    window.location = "index.php?pagina=editar/personal&id=" + id;
}

function cambiarEstadoPersonal(id, nuevoEstado) {
    var titulo = nuevoEstado === 'activo' ? 'Activar Personal' : 'Desactivar Personal';
    var texto = nuevoEstado === 'activo' ? 
        '¿Está seguro de activar este personal?' : 
        '¿Está seguro de desactivar este personal?';
    
    Swal.fire({
        title: titulo,
        text: texto,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: nuevoEstado === 'activo' ? '#28a745' : '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, ' + (nuevoEstado === 'activo' ? 'activar' : 'desactivar'),
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("ajax/personal_ajax.php", {
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

function aplicarFiltros() {
    var tabla = $('#tablaPersonal').DataTable();
    
    // Limpiar filtros anteriores
    tabla.columns().search('');
    
    // Aplicar filtros
    var filtroTexto = $('#filtroTexto').val();
    var filtroCargo = $('#filtroCargo').val();
    var filtroEstado = $('#filtroEstado').val();
    
    if (filtroTexto) {
        tabla.columns([1, 2]).search(filtroTexto);
    }
    if (filtroCargo) {
        tabla.column(3).search(filtroCargo);
    }
    if (filtroEstado) {
        tabla.column(8).search(filtroEstado);
    }
    
    tabla.draw();
}

function limpiarFiltros() {
    $('#filtroTexto').val('');
    $('#filtroCargo').val('');
    $('#filtroEstado').val('');
    $('#filtroFechaDesde').val('');
    $('#filtroFechaHasta').val('');
    $('#tablaPersonal').DataTable().columns().search('').draw();
}

function actualizarTabla() {
    location.reload();
}

// Establecer fecha actual por defecto en el modal
$(document).on('show.bs.modal', '#modalNuevoPersonal', function() {
    var today = new Date().toISOString().split('T')[0];
    $('input[name="fecha_ingreso"]').val(today);
});
</script>