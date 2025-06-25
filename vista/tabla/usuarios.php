<?php
if ($_SESSION["tipo_usuario"] != "personal") {
    echo '<script>window.location = "index.php?pagina=inicio";</script>';
    return;
}

// Obtener lista de usuarios
$usuariosPersonal = ControladorUsuarioPersonal::ctrMostrarUsuarios(null, null);
$usuariosCliente = ControladorUsuarioCliente::ctrMostrarUsuarios(null, null);
?>

<div class="content-wrapper">
    <!-- Header de la página -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="content-title">
                        <i class="bi bi-people-fill me-2"></i>
                        Gestión de Usuarios
                    </h1>
                    <p class="content-subtitle">Administra todos los usuarios del sistema</p>
                </div>
                <div class="col-sm-6">
                    <div class="content-actions">
                        <div class="dropdown me-2">
                            <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-plus"></i>
                                <span class="d-none d-sm-inline">Nuevo Usuario</span>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalNuevoUsuarioPersonal">
                                    <i class="bi bi-person-gear me-2"></i>Usuario Personal
                                </a></li>
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalNuevoUsuarioCliente">
                                    <i class="bi bi-person me-2"></i>Usuario Cliente
                                </a></li>
                            </ul>
                        </div>
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
                                <?php echo count($usuariosPersonal) + count($usuariosCliente); ?>
                            </div>
                            <div class="info-card-text">Total Usuarios</div>
                        </div>
                        <div class="info-card-icon bg-primary">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="info-card">
                        <div class="info-card-content">
                            <div class="info-card-number">
                                <?php echo count($usuariosPersonal); ?>
                            </div>
                            <div class="info-card-text">Personal</div>
                        </div>
                        <div class="info-card-icon bg-success">
                            <i class="bi bi-person-gear"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="info-card">
                        <div class="info-card-content">
                            <div class="info-card-number">
                                <?php echo count($usuariosCliente); ?>
                            </div>
                            <div class="info-card-text">Clientes</div>
                        </div>
                        <div class="info-card-icon bg-info">
                            <i class="bi bi-person"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="info-card">
                        <div class="info-card-content">
                            <div class="info-card-number">
                                <?php 
                                $activos = 0;
                                foreach($usuariosPersonal as $user) {
                                    if($user['estado'] == 'activo') $activos++;
                                }
                                foreach($usuariosCliente as $user) {
                                    if($user['estado'] == 'activo') $activos++;
                                }
                                echo $activos;
                                ?>
                            </div>
                            <div class="info-card-text">Usuarios Activos</div>
                        </div>
                        <div class="info-card-icon bg-warning">
                            <i class="bi bi-person-check"></i>
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
                                <label>Buscar por usuario o nombre:</label>
                                <input type="text" class="form-control" id="filtroTexto" placeholder="Usuario o nombre">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Tipo de Usuario:</label>
                                <select class="form-control" id="filtroTipo">
                                    <option value="">Todos</option>
                                    <option value="personal">Personal</option>
                                    <option value="cliente">Cliente</option>
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
                                    <option value="bloqueado">Bloqueado</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Fecha registro desde:</label>
                                <input type="date" class="form-control" id="filtroFechaDesde">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Fecha registro hasta:</label>
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

            <!-- Tabla de usuarios -->
            <div class="card card-custom">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-table me-2"></i>
                        Lista de Usuarios
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
                    <table id="tablaUsuarios" class="table table-bordered table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 10px">#</th>
                                <th>Usuario</th>
                                <th>Nombre Completo</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th>Último Acceso</th>
                                <th>Intentos Fallidos</th>
                                <th>Fecha Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $contador = 1;
                            
                            // Mostrar usuarios de personal
                            foreach ($usuariosPersonal as $usuario): 
                            ?>
                            <tr>
                                <td><?php echo $contador++; ?></td>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar bg-success">
                                            <i class="bi bi-person-gear"></i>
                                        </div>
                                        <div class="user-details">
                                            <strong><?php echo htmlspecialchars($usuario["usuario"]); ?></strong>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($usuario["nombre_personal"] . " " . $usuario["apellido_personal"]); ?>
                                    <small class="text-muted d-block"><?php echo htmlspecialchars($usuario["cargo"]); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-success">
                                        <i class="bi bi-person-gear me-1"></i>Personal
                                    </span>
                                </td>
                                <td>
                                    <?php if ($usuario["estado"] == "activo"): ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>Activo
                                        </span>
                                    <?php elseif ($usuario["estado"] == "bloqueado"): ?>
                                        <span class="badge bg-danger">
                                            <i class="bi bi-lock me-1"></i>Bloqueado
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-x-circle me-1"></i>Inactivo
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($usuario["ultimo_acceso"])): ?>
                                        <i class="bi bi-clock me-1"></i>
                                        <?php echo date("d/m/Y H:i", strtotime($usuario["ultimo_acceso"])); ?>
                                    <?php else: ?>
                                        <span class="text-muted">Nunca</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($usuario["intentos_fallidos"] > 0): ?>
                                        <span class="badge bg-warning">
                                            <i class="bi bi-exclamation-triangle me-1"></i><?php echo $usuario["intentos_fallidos"]; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success">0</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <i class="bi bi-calendar me-1"></i>
                                    <?php echo date("d/m/Y", strtotime($usuario["fecha_creacion"])); ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                                onclick="verUsuario('personal', <?php echo $usuario['id_usuario_personal']; ?>)"
                                                title="Ver detalles">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-warning btn-sm" 
                                                onclick="resetearPassword('personal', <?php echo $usuario['id_usuario_personal']; ?>)"
                                                title="Resetear contraseña">
                                            <i class="bi bi-key"></i>
                                        </button>
                                        <?php if ($usuario["estado"] == "activo"): ?>
                                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                                    onclick="cambiarEstadoUsuario('personal', <?php echo $usuario['id_usuario_personal']; ?>, 'inactivo')"
                                                    title="Desactivar">
                                                <i class="bi bi-person-x"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline-success btn-sm" 
                                                    onclick="cambiarEstadoUsuario('personal', <?php echo $usuario['id_usuario_personal']; ?>, 'activo')"
                                                    title="Activar">
                                                <i class="bi bi-person-check"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($usuario["estado"] == "bloqueado"): ?>
                                            <button type="button" class="btn btn-outline-info btn-sm" 
                                                    onclick="desbloquearUsuario('personal', <?php echo $usuario['id_usuario_personal']; ?>)"
                                                    title="Desbloquear">
                                                <i class="bi bi-unlock"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php 
                            // Mostrar usuarios de cliente
                            foreach ($usuariosCliente as $usuario): 
                            ?>
                            <tr>
                                <td><?php echo $contador++; ?></td>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar bg-info">
                                            <i class="bi bi-person"></i>
                                        </div>
                                        <div class="user-details">
                                            <strong><?php echo htmlspecialchars($usuario["usuario"]); ?></strong>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($usuario["nombre_cliente"] . " " . $usuario["apellido_cliente"]); ?>
                                    <small class="text-muted d-block"><?php echo htmlspecialchars($usuario["cedula"]); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <i class="bi bi-person me-1"></i>Cliente
                                    </span>
                                </td>
                                <td>
                                    <?php if ($usuario["estado"] == "activo"): ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>Activo
                                        </span>
                                    <?php elseif ($usuario["estado"] == "bloqueado"): ?>
                                        <span class="badge bg-danger">
                                            <i class="bi bi-lock me-1"></i>Bloqueado
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-x-circle me-1"></i>Inactivo
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($usuario["ultimo_acceso"])): ?>
                                        <i class="bi bi-clock me-1"></i>
                                        <?php echo date("d/m/Y H:i", strtotime($usuario["ultimo_acceso"])); ?>
                                    <?php else: ?>
                                        <span class="text-muted">Nunca</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($usuario["intentos_fallidos"] > 0): ?>
                                        <span class="badge bg-warning">
                                            <i class="bi bi-exclamation-triangle me-1"></i><?php echo $usuario["intentos_fallidos"]; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success">0</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <i class="bi bi-calendar me-1"></i>
                                    <?php echo date("d/m/Y", strtotime($usuario["fecha_creacion"])); ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                                onclick="verUsuario('cliente', <?php echo $usuario['id_usuario_cliente']; ?>)"
                                                title="Ver detalles">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-warning btn-sm" 
                                                onclick="resetearPassword('cliente', <?php echo $usuario['id_usuario_cliente']; ?>)"
                                                title="Resetear contraseña">
                                            <i class="bi bi-key"></i>
                                        </button>
                                        <?php if ($usuario["estado"] == "activo"): ?>
                                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                                    onclick="cambiarEstadoUsuario('cliente', <?php echo $usuario['id_usuario_cliente']; ?>, 'inactivo')"
                                                    title="Desactivar">
                                                <i class="bi bi-person-x"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline-success btn-sm" 
                                                    onclick="cambiarEstadoUsuario('cliente', <?php echo $usuario['id_usuario_cliente']; ?>, 'activo')"
                                                    title="Activar">
                                                <i class="bi bi-person-check"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($usuario["estado"] == "bloqueado"): ?>
                                            <button type="button" class="btn btn-outline-info btn-sm" 
                                                    onclick="desbloquearUsuario('cliente', <?php echo $usuario['id_usuario_cliente']; ?>)"
                                                    title="Desbloquear">
                                                <i class="bi bi-unlock"></i>
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
                                Mostrando <?php echo count($usuariosPersonal) + count($usuariosCliente); ?> usuarios en total
                                (<?php echo count($usuariosPersonal); ?> personal, <?php echo count($usuariosCliente); ?> clientes)
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Nuevo Usuario Personal -->
<div class="modal fade" id="modalNuevoUsuarioPersonal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" id="formNuevoUsuarioPersonal">
                <div class="modal-header">
                    <h4 class="modal-title">
                        <i class="bi bi-person-gear me-2"></i>
                        Crear Usuario de Personal
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Seleccionar Personal *</label>
                                <select class="form-control" name="id_personal" required>
                                    <option value="">Seleccionar personal</option>
                                    <?php 
                                    $personal = ControladorPersonal::ctrMostrarPersonal(null, null);
                                    foreach($personal as $p): 
                                        // Verificar si ya tiene usuario
                                        $tieneUsuario = false;
                                        foreach($usuariosPersonal as $up) {
                                            if($up['id_personal'] == $p['id_personal']) {
                                                $tieneUsuario = true;
                                                break;
                                            }
                                        }
                                        if (!$tieneUsuario && $p['estado'] == 'activo'):
                                    ?>
                                        <option value="<?php echo $p['id_personal']; ?>">
                                            <?php echo htmlspecialchars($p['nombre'] . ' ' . $p['apellido'] . ' - ' . $p['cargo']); ?>
                                        </option>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Usuario *</label>
                                <input type="text" class="form-control" name="usuario" required>
                                <small class="text-muted">Solo letras, números y guiones bajos</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Contraseña *</label>
                                <input type="password" class="form-control" name="contrasena" required>
                                <small class="text-muted">Mínimo 6 caracteres</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Confirmar Contraseña *</label>
                                <input type="password" class="form-control" name="confirmar_contrasena" required>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i>Crear Usuario
                    </button>
                </div>

                <?php
                $crearUsuario = new ControladorUsuarioPersonal();
                $crearUsuario->ctrRegistrarUsuario();
                ?>
            </form>
        </div>
    </div>
</div>

<!-- Modal Nuevo Usuario Cliente -->
<div class="modal fade" id="modalNuevoUsuarioCliente" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" id="formNuevoUsuarioCliente">
                <div class="modal-header">
                    <h4 class="modal-title">
                        <i class="bi bi-person me-2"></i>
                        Crear Usuario de Cliente
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Seleccionar Cliente *</label>
                                <select class="form-control" name="id_cliente" required>
                                    <option value="">Seleccionar cliente</option>
                                    <?php 
                                    $clientes = ControladorCliente::ctrMostrarCliente(null, null);
                                    foreach($clientes as $c): 
                                        // Verificar si ya tiene usuario
                                        $tieneUsuario = false;
                                        foreach($usuariosCliente as $uc) {
                                            if($uc['id_cliente'] == $c['id_cliente']) {
                                                $tieneUsuario = true;
                                                break;
                                            }
                                        }
                                        if (!$tieneUsuario && $c['estado'] == 'activo'):
                                    ?>
                                        <option value="<?php echo $c['id_cliente']; ?>">
                                            <?php echo htmlspecialchars($c['nombre'] . ' ' . $c['apellido'] . ' - ' . $c['cedula']); ?>
                                        </option>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Usuario *</label>
                                <input type="text" class="form-control" name="usuario" required>
                                <small class="text-muted">Solo letras, números y guiones bajos</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Contraseña *</label>
                                <input type="password" class="form-control" name="contrasena" required>
                                <small class="text-muted">Mínimo 6 caracteres</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Confirmar Contraseña *</label>
                                <input type="password" class="form-control" name="confirmar_contrasena" required>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i>Crear Usuario
                    </button>
                </div>

                <?php
                $crearUsuarioCliente = new ControladorUsuarioCliente();
                $crearUsuarioCliente->ctrRegistrarUsuario();
                ?>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ver Usuario -->
<div class="modal fade" id="modalVerUsuario" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="bi bi-person-lines-fill me-2"></i>
                    Detalles del Usuario
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body" id="contenidoVerUsuario">
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
    $('#tablaUsuarios').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "responsive": true,
        "order": [[1, "asc"]],
        "pageLength": 25,
        "columnDefs": [
            { "orderable": false, "targets": [8] }
        ]
    });

    // Filtro de búsqueda rápida
    $('#busquedaRapida').on('keyup', function() {
        $('#tablaUsuarios').DataTable().search(this.value).draw();
    });

    // Validación del formulario de usuario personal
    $('#formNuevoUsuarioPersonal').on('submit', function(e) {
        var usuario = $('input[name="usuario"]').val();
        var contrasena = $('input[name="contrasena"]').val();
        var confirmar = $('input[name="confirmar_contrasena"]').val();
        
        // Validar formato de usuario
        if (!/^[a-zA-Z0-9_]{3,20}$/.test(usuario)) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'El usuario debe tener entre 3 y 20 caracteres, solo letras, números y guiones bajos'
            });
            return false;
        }
        
        // Validar contraseña
        if (contrasena.length < 6) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'La contraseña debe tener al menos 6 caracteres'
            });
            return false;
        }
        
        // Validar confirmación
        if (contrasena !== confirmar) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Las contraseñas no coinciden'
            });
            return false;
        }
    });

    // Validación del formulario de usuario cliente
    $('#formNuevoUsuarioCliente').on('submit', function(e) {
        var usuario = $(this).find('input[name="usuario"]').val();
        var contrasena = $(this).find('input[name="contrasena"]').val();
        var confirmar = $(this).find('input[name="confirmar_contrasena"]').val();
        
        // Validar formato de usuario
        if (!/^[a-zA-Z0-9_]{3,20}$/.test(usuario)) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'El usuario debe tener entre 3 y 20 caracteres, solo letras, números y guiones bajos'
            });
            return false;
        }
        
        // Validar contraseña
        if (contrasena.length < 6) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'La contraseña debe tener al menos 6 caracteres'
            });
            return false;
        }
        
        // Validar confirmación
        if (contrasena !== confirmar) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Las contraseñas no coinciden'
            });
            return false;
        }
    });
});

function verUsuario(tipo, id) {
    $.post("ajax/usuario_ajax.php", {accion: "obtener", tipo: tipo, id: id}, function(data) {
        try {
            var usuario = JSON.parse(data);
            if (usuario.error) {
                Swal.fire('Error', usuario.error, 'error');
                return;
            }
            
            var html = `
                <div class="row">
                    <div class="col-md-6">
                        <h5>Información del Usuario</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Usuario:</strong></td>
                                <td>${usuario.usuario}</td>
                            </tr>
                            <tr>
                                <td><strong>Tipo:</strong></td>
                                <td>
                                    <span class="badge ${tipo === 'personal' ? 'bg-success' : 'bg-info'}">
                                        ${tipo === 'personal' ? 'Personal' : 'Cliente'}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Estado:</strong></td>
                                <td>
                                    <span class="badge ${usuario.estado === 'activo' ? 'bg-success' : usuario.estado === 'bloqueado' ? 'bg-danger' : 'bg-secondary'}">
                                        ${usuario.estado === 'activo' ? 'Activo' : usuario.estado === 'bloqueado' ? 'Bloqueado' : 'Inactivo'}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Intentos Fallidos:</strong></td>
                                <td>${usuario.intentos_fallidos || 0}</td>
                            </tr>
                            <tr>
                                <td><strong>Último Acceso:</strong></td>
                                <td>${usuario.ultimo_acceso || 'Nunca'}</td>
                            </tr>
                            <tr>
                                <td><strong>Fecha Registro:</strong></td>
                                <td>${usuario.fecha_creacion}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>Información ${tipo === 'personal' ? 'del Personal' : 'del Cliente'}</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Nombre:</strong></td>
                                <td>${usuario.nombre_completo}</td>
                            </tr>
                            ${tipo === 'personal' ? `
                            <tr>
                                <td><strong>Cargo:</strong></td>
                                <td>${usuario.cargo || 'No especificado'}</td>
                            </tr>` : `
                            <tr>
                                <td><strong>Cédula:</strong></td>
                                <td>${usuario.cedula || 'No especificada'}</td>
                            </tr>`}
                            <tr>
                                <td><strong>Teléfono:</strong></td>
                                <td>${usuario.telefono || 'No especificado'}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>${usuario.email || 'No especificado'}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            `;
            
            $('#contenidoVerUsuario').html(html);
            $('#modalVerUsuario').modal('show');
            
        } catch (e) {
            Swal.fire('Error', 'Error al procesar la respuesta del servidor', 'error');
        }
    }).fail(function() {
        Swal.fire('Error', 'Error de conexión al servidor', 'error');
    });
}

function cambiarEstadoUsuario(tipo, id, nuevoEstado) {
    var titulo = nuevoEstado === 'activo' ? 'Activar Usuario' : 'Desactivar Usuario';
    var texto = nuevoEstado === 'activo' ? 
        '¿Está seguro de activar este usuario?' : 
        '¿Está seguro de desactivar este usuario?';
    
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
            $.post("ajax/usuario_ajax.php", {
                accion: "cambiar_estado",
                tipo: tipo,
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

function resetearPassword(tipo, id) {
    Swal.fire({
        title: 'Resetear Contraseña',
        text: '¿Está seguro de resetear la contraseña de este usuario?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, resetear',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("ajax/usuario_ajax.php", {
                accion: "resetear_password",
                tipo: tipo,
                id: id
            }, function(data) {
                try {
                    var response = JSON.parse(data);
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Contraseña Reseteada',
                            html: `<p>${response.message}</p><p><strong>Nueva contraseña: ${response.nueva_password}</strong></p>`,
                            confirmButtonText: 'Entendido'
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

function desbloquearUsuario(tipo, id) {
    Swal.fire({
        title: 'Desbloquear Usuario',
        text: '¿Está seguro de desbloquear este usuario?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#17a2b8',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, desbloquear',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("ajax/usuario_ajax.php", {
                accion: "desbloquear",
                tipo: tipo,
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
    var tabla = $('#tablaUsuarios').DataTable();
    
    // Limpiar filtros anteriores
    tabla.columns().search('');
    
    // Aplicar filtros
    var filtroTexto = $('#filtroTexto').val();
    var filtroTipo = $('#filtroTipo').val();
    var filtroEstado = $('#filtroEstado').val();
    
    if (filtroTexto) {
        tabla.columns([1, 2]).search(filtroTexto);
    }
    if (filtroTipo) {
        tabla.column(3).search(filtroTipo);
    }
    if (filtroEstado) {
        tabla.column(4).search(filtroEstado);
    }
    
    tabla.draw();
}

function limpiarFiltros() {
    $('#filtroTexto').val('');
    $('#filtroTipo').val('');
    $('#filtroEstado').val('');
    $('#filtroFechaDesde').val('');
    $('#filtroFechaHasta').val('');
    $('#tablaUsuarios').DataTable().columns().search('').draw();
}

function actualizarTabla() {
    location.reload();
}
</script>