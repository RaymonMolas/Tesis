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


// Obtener datos según el tipo de usuario
if ($_SESSION["tipo_usuario"] == "personal") {
    $citasPendientes = ControladorAgendamiento::listarSolicitudesPendientes();
    $ordenesRecientes = OrdenTrabajoControlador::ctrListarOrdenesTrabajo();
    $totalVehiculos = VehiculoControlador::ctrContarVehiculos();
    $totalClientes = ClienteControlador::ctrContarClientes();
} else {
    $id_cliente = $_SESSION["id_cliente"];
    $misCitas = ControladorAgendamiento::obtenerCitasCliente($id_cliente);
    $misVehiculos = VehiculoControlador::ctrListarVehiculosCliente($id_cliente);
}
?>

<title>INICIO - Panel de Control</title>

<style>
    .dashboard-card {
        background-color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .stat-card {
        background-color: #f8f9fa;
        border-left: 4px solid #dc3545;
        padding: 15px;
        margin-bottom: 15px;
    }

    .quick-action {
        background-color: #dc3545;
        color: white;
        padding: 15px;
        border-radius: 5px;
        text-decoration: none;
        display: inline-block;
        margin: 5px;
    }

    .quick-action:hover {
        background-color: #c82333;
        color: white;
    }
</style>

<div class="container mt-4">
    <?php if ($_SESSION["tipo_usuario"] == "personal"): ?>
        <!-- Panel de Personal -->
        <div class="row">
            <div class="col-md-8">
                <div class="dashboard-card">
                    <h3>Citas Pendientes de Aprobación</h3>
                    <?php if (!empty($citasPendientes)): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Fecha</th>
                                        <th>Hora</th>
                                        <th>Motivo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($citasPendientes as $cita): ?>
                                        <tr>
                                            <td><?php echo $cita['cliente']; ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($cita['fecha'])); ?></td>
                                            <td><?php echo date('H:i', strtotime($cita['hora'])); ?></td>
                                            <td><?php echo $cita['motivo']; ?></td>
                                            <td>
                                                <form method="post" class="d-inline">
                                                    <button type="submit" name="accion_aprobar_<?php echo $cita['id_cita']; ?>"
                                                        class="btn btn-success btn-sm">Aprobar</button>
                                                    <button type="submit" name="accion_rechazar_<?php echo $cita['id_cita']; ?>"
                                                        class="btn btn-danger btn-sm">Rechazar</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No hay citas pendientes de aprobación.</p>
                    <?php endif; ?>
                </div>

                <div class="dashboard-card">
                    <h3>Órdenes de Trabajo Recientes</h3>
                    <?php if (!empty($ordenesRecientes)): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Vehículo</th>
                                        <th>Cliente</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($ordenesRecientes, 0, 5) as $orden): ?>
                                        <tr>
                                            <td><?php echo $orden['id_orden']; ?></td>
                                            <td><?php echo $orden['marca'] . ' ' . $orden['modelo']; ?></td>
                                            <td><?php echo $orden['nombre_cliente']; ?></td>
                                            <td>
                                                <span class="badge <?php
                                                echo $orden['estado'] == 'en_proceso' ? 'bg-warning' :
                                                    ($orden['estado'] == 'completado' ? 'bg-success' : 'bg-danger');
                                                ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $orden['estado'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="index.php?pagina=ver/orden_trabajo&id=<?php echo $orden['id_orden']; ?>"
                                                    class="btn btn-info btn-sm">Ver</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No hay órdenes de trabajo recientes.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-4">
                <div class="dashboard-card">
                    <h3>Estadísticas</h3>
                    <div class="stat-card">
                        <h4>Total Vehículos</h4>
                        <h2><?php echo $totalVehiculos; ?></h2>
                    </div>
                    <div class="stat-card">
                        <h4>Total Clientes</h4>
                        <h2><?php echo $totalClientes; ?></h2>
                    </div>
                </div>
                <div class="dashboard-card">
                    <h3>Acciones Rápidas</h3>
                    <div class="d-grid gap-2">
                        <a href="index.php?pagina=nuevo/orden_trabajo" class="quick-action">
                            <i class="bi bi-plus-circle"></i> Nueva Orden de Trabajo
                        </a>
                        <a href="index.php?pagina=nuevo/cliente" class="quick-action">
                            <i class="bi bi-person-plus"></i> Nuevo Cliente
                        </a>
                        <a href="index.php?pagina=nuevo/vehiculo" class="quick-action">
                            <i class="bi bi-car-front"></i> Nuevo Vehículo
                        </a>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- Panel de Cliente -->
        <div class="row">
            <div class="col-md-8">
                <div class="dashboard-card">
                    <h3>Mis Citas</h3>
                    <?php if (!empty($misCitas)): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Hora</th>
                                        <th>Estado</th>
                                        <th>Motivo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($misCitas as $cita): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($cita['fecha'])); ?></td>
                                            <td><?php echo date('H:i', strtotime($cita['hora'])); ?></td>
                                            <td>
                                                <span class="badge <?php
                                                echo $cita['estado'] == 'pendiente' ? 'bg-warning' :
                                                    ($cita['estado'] == 'aprobado' ? 'bg-success' : 'bg-danger');
                                                ?>">
                                                    <?php echo ucfirst($cita['estado']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $cita['motivo']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No tienes citas programadas.</p>
                    <?php endif; ?>
                </div>

                <div class="dashboard-card">
                    <h3>Mis Vehículos</h3>
                    <?php if (!empty($misVehiculos)): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Marca</th>
                                        <th>Modelo</th>
                                        <th>Año</th>
                                        <th>Matrícula</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($misVehiculos as $vehiculo): ?>
                                        <tr>
                                            <td><?php echo $vehiculo['marca']; ?></td>
                                            <td><?php echo $vehiculo['modelo']; ?></td>
                                            <td><?php echo $vehiculo['anio']; ?></td>
                                            <td><?php echo $vehiculo['matricula']; ?></td>
                                            <td>
                                                <a href="index.php?pagina=tabla/historial&id=<?php echo $vehiculo['id_vehiculo']; ?>"
                                                    class="btn btn-info btn-sm">Ver Historial</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No tienes vehículos registrados.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-4">
                <div class="dashboard-card">
                    <h3>Acciones Rápidas</h3>
                    <div class="d-grid gap-2">
                        <a href="index.php?pagina=agendamiento" class="quick-action">
                            <i class="bi bi-calendar-plus"></i> Agendar Cita
                        </a>
                        <a href="index.php?pagina=tabla/historial" class="quick-action">
                            <i class="bi bi-clock-history"></i> Ver Historial
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>