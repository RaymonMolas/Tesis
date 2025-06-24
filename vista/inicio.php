<?php
/**
 * Dashboard Principal - Motor Service
 * Sistema de Gestión Automotriz - Versión 2.0
 */

// Verificar acceso
if (!isset($_SESSION["validarIngreso"]) || $_SESSION["validarIngreso"] != "ok") {
    echo '<script>window.location = "index.php?pagina=login";</script>';
    exit;
}

$tipo_usuario = $_SESSION["tipo_usuario"];
$usuario_info = obtenerUsuarioActual();

// Obtener estadísticas según el tipo de usuario
if ($tipo_usuario == "personal") {
    // Estadísticas para personal
    $stats_clientes = ClienteControlador::ctrEstadisticasClientes();
    $stats_vehiculos = VehiculoControlador::ctrEstadisticasVehiculos();
    $stats_personal = PersonalControlador::ctrEstadisticasPersonal();
    $stats_productos = ProductoControlador::ctrEstadisticasProductos();
    $stats_agendamiento = ControladorAgendamiento::ctrEstadisticasAgendamiento();
    $stats_ordenes = OrdenTrabajoControlador::ctrEstadisticasOrdenes();
    $stats_presupuestos = PresupuestoControlador::ctrEstadisticasPresupuestos();
    $stats_facturas = FacturaControlador::ctrEstadisticasFacturacion();
    
    // Alertas del sistema
    $citas_pendientes = ControladorAgendamiento::listarSolicitudesPendientes();
    $productos_stock_bajo = ProductoControlador::ctrProductosStockBajo();
    $presupuestos_vencer = PresupuestoControlador::ctrPresupuestosProximosVencer(7);
    $ordenes_proceso = OrdenTrabajoControlador::ctrListarOrdenesPorEstado('en_proceso');
    
} else {
    // Estadísticas para cliente
    $id_cliente = $_SESSION["id_cliente"];
    $mis_vehiculos = VehiculoControlador::ctrListarVehiculosCliente($id_cliente);
    $mis_citas = ControladorAgendamiento::obtenerCitasCliente($id_cliente);
    $mi_historial = HistoricoCitasControlador::ctrObtenerHistorialCliente($id_cliente);
    $mis_presupuestos = PresupuestoControlador::ctrPresupuestosCliente($id_cliente);
    $tiene_cita_activa = ModeloAgendamiento::clienteTieneCitaActiva($id_cliente);
    
    // Notificaciones para el cliente
    $notificaciones = ControladorAgendamiento::obtenerNotificacionesCliente($id_cliente);
}
?>

<title>Dashboard - Motor Service</title>

<style>
    .dashboard-container {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        min-height: 100vh;
        padding: 20px 0;
    }
    
    .welcome-card {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        color: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 8px 25px rgba(220, 38, 38, 0.3);
    }
    
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-left: 5px solid #dc2626;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
    
    .stat-number {
        font-size: 2.5rem;
        font-weight: bold;
        color: #dc2626;
        margin: 0;
    }
    
    .stat-label {
        font-size: 1rem;
        color: #6c757d;
        margin-top: 5px;
    }
    
    .stat-icon {
        font-size: 2.5rem;
        color: #dc2626;
        opacity: 0.8;
    }
    
    .alert-card {
        background: white;
        border-radius: 12px;
        margin-bottom: 20px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .alert-header {
        padding: 15px 20px;
        font-weight: bold;
        border-bottom: 1px solid #dee2e6;
    }
    
    .alert-warning { border-left: 5px solid #ffc107; }
    .alert-warning .alert-header { background: #fff3cd; color: #856404; }
    
    .alert-danger { border-left: 5px solid #dc3545; }
    .alert-danger .alert-header { background: #f8d7da; color: #721c24; }
    
    .alert-info { border-left: 5px solid #0dcaf0; }
    .alert-info .alert-header { background: #d1ecf1; color: #055160; }
    
    .alert-success { border-left: 5px solid #198754; }
    .alert-success .alert-header { background: #d1e7dd; color: #0f5132; }
    
    .quick-actions {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .quick-action-btn {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        border: none;
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        text-decoration: none;
        display: inline-block;
        margin: 5px;
        transition: all 0.3s ease;
        font-weight: 500;
    }
    
    .quick-action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(220, 38, 38, 0.4);
        color: white;
        text-decoration: none;
    }
    
    .chart-container {
        background: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .progress-custom {
        height: 10px;
        border-radius: 5px;
        background: #e9ecef;
        overflow: hidden;
    }
    
    .progress-bar-custom {
        height: 100%;
        background: linear-gradient(90deg, #dc2626, #b91c1c);
        border-radius: 5px;
        transition: width 0.3s ease;
    }
    
    .notification-badge {
        background: #dc2626;
        color: white;
        border-radius: 50%;
        padding: 2px 8px;
        font-size: 12px;
        font-weight: bold;
    }
    
    .recent-activity {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .activity-item {
        padding: 15px;
        border-bottom: 1px solid #f1f3f4;
        display: flex;
        align-items: center;
    }
    
    .activity-item:last-child {
        border-bottom: none;
    }
    
    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 18px;
        color: white;
    }
    
    .activity-content h6 {
        margin: 0 0 5px 0;
        font-weight: 600;
    }
    
    .activity-time {
        font-size: 12px;
        color: #6c757d;
    }
</style>

<div class="dashboard-container">
    <div class="container">
        
        <!-- Mensaje de Bienvenida -->
        <div class="welcome-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">
                        ¡Bienvenido<?php echo $tipo_usuario == "cliente" ? "" : " de nuevo"; ?>, <?php echo $usuario_info['nombre_completo']; ?>! 👋
                    </h1>
                    <p class="mb-0 opacity-75">
                        <?php if ($tipo_usuario == "personal"): ?>
                            Panel de control administrativo - Motor Service v<?php echo SISTEMA_VERSION; ?>
                        <?php else: ?>
                            Portal del cliente - Gestiona tus vehículos y citas
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="text-white-50">
                        <i class="bi bi-calendar3"></i> <?php echo formatear_fecha(date('Y-m-d'), true); ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($tipo_usuario == "personal"): ?>
            <!-- Dashboard para Personal -->
            
            <!-- Estadísticas Principales -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-number"><?php echo $stats_clientes['total_clientes'] ?? 0; ?></div>
                                <div class="stat-label">Clientes</div>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-people"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-number"><?php echo $stats_vehiculos['total_vehiculos'] ?? 0; ?></div>
                                <div class="stat-label">Vehículos</div>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-car-front"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-number"><?php echo count($ordenes_proceso ?? []); ?></div>
                                <div class="stat-label">Órdenes en Proceso</div>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-gear"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-number"><?php echo count($citas_pendientes ?? []); ?></div>
                                <div class="stat-label">Citas Pendientes</div>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alertas del Sistema -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5 class="mb-3"><i class="bi bi-exclamation-triangle text-warning"></i> Alertas del Sistema</h5>
                    
                    <?php if (!empty($productos_stock_bajo)): ?>
                        <div class="alert-card alert-warning">
                            <div class="alert-header">
                                <i class="bi bi-box-seam"></i> Productos con Stock Bajo
                                <span class="notification-badge"><?php echo count($productos_stock_bajo); ?></span>
                            </div>
                            <div class="p-3">
                                <?php foreach (array_slice($productos_stock_bajo, 0, 3) as $producto): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span><?php echo $producto['nombre']; ?></span>
                                        <span class="badge bg-warning"><?php echo $producto['stock_actual']; ?> unidades</span>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (count($productos_stock_bajo) > 3): ?>
                                    <small class="text-muted">Y <?php echo count($productos_stock_bajo) - 3; ?> productos más...</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($presupuestos_vencer)): ?>
                        <div class="alert-card alert-info">
                            <div class="alert-header">
                                <i class="bi bi-clock"></i> Presupuestos por Vencer
                                <span class="notification-badge"><?php echo count($presupuestos_vencer); ?></span>
                            </div>
                            <div class="p-3">
                                <?php foreach (array_slice($presupuestos_vencer, 0, 3) as $presupuesto): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span>Presupuesto #<?php echo $presupuesto['id_presupuesto']; ?></span>
                                        <span class="badge bg-info"><?php echo $presupuesto['dias_restantes']; ?> días</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($citas_pendientes)): ?>
                        <div class="alert-card alert-success">
                            <div class="alert-header">
                                <i class="bi bi-calendar-plus"></i> Nuevas Solicitudes de Citas
                                <span class="notification-badge"><?php echo count($citas_pendientes); ?></span>
                            </div>
                            <div class="p-3">
                                <?php foreach (array_slice($citas_pendientes, 0, 3) as $cita): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span><?php echo $cita['cliente']; ?></span>
                                        <span class="badge bg-success"><?php echo date('d/m', strtotime($cita['fecha_cita'])); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Acciones Rápidas -->
                <div class="col-md-6">
                    <h5 class="mb-3"><i class="bi bi-lightning text-primary"></i> Acciones Rápidas</h5>
                    <div class="quick-actions">
                        <h6 class="mb-3">Crear Nuevo</h6>
                        <a href="index.php?pagina=nuevo/cliente" class="quick-action-btn">
                            <i class="bi bi-person-plus"></i> Cliente
                        </a>
                        <a href="index.php?pagina=nuevo/vehiculo" class="quick-action-btn">
                            <i class="bi bi-car-front"></i> Vehículo
                        </a>
                        <a href="index.php?pagina=nuevo/orden_trabajo" class="quick-action-btn">
                            <i class="bi bi-gear"></i> Orden de Trabajo
                        </a>
                        <a href="index.php?pagina=nuevo/presupuesto" class="quick-action-btn">
                            <i class="bi bi-calculator"></i> Presupuesto
                        </a>
                        <a href="index.php?pagina=nuevo/factura" class="quick-action-btn">
                            <i class="bi bi-receipt"></i> Factura
                        </a>
                        <a href="index.php?pagina=nuevo/producto" class="quick-action-btn">
                            <i class="bi bi-box"></i> Producto
                        </a>
                        
                        <h6 class="mb-3 mt-4">Gestión</h6>
                        <a href="index.php?pagina=agendamiento" class="quick-action-btn">
                            <i class="bi bi-calendar-event"></i> Ver Citas
                        </a>
                        <a href="index.php?pagina=tabla/orden_trabajo" class="quick-action-btn">
                            <i class="bi bi-list-task"></i> Órdenes
                        </a>
                        <a href="index.php?pagina=tabla/facturas" class="quick-action-btn">
                            <i class="bi bi-receipt-cutoff"></i> Facturas
                        </a>
                    </div>
                </div>
            </div>

            <!-- Gráficos y Estadísticas Detalladas -->
            <div class="row">
                <div class="col-md-8">
                    <div class="chart-container">
                        <h5 class="mb-4"><i class="bi bi-bar-chart"></i> Resumen de Actividad</h5>
                        
                        <!-- Estadísticas de Órdenes -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6>Estado de Órdenes de Trabajo</h6>
                                <div class="mb-2">
                                    <small class="text-muted">En Proceso</small>
                                    <div class="progress-custom">
                                        <div class="progress-bar-custom" style="width: <?php echo calcular_porcentaje(count($ordenes_proceso ?? []), ($stats_ordenes['total_ordenes'] ?? 1)); ?>%"></div>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Completadas</small>
                                    <div class="progress-custom">
                                        <div class="progress-bar-custom" style="width: <?php echo calcular_porcentaje(($stats_ordenes['completadas'] ?? 0), ($stats_ordenes['total_ordenes'] ?? 1)); ?>%"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h6>Estado de Presupuestos</h6>
                                <div class="mb-2">
                                    <small class="text-muted">Pendientes</small>
                                    <div class="progress-custom">
                                        <div class="progress-bar-custom" style="width: <?php echo calcular_porcentaje(($stats_presupuestos['pendientes'] ?? 0), ($stats_presupuestos['total_presupuestos'] ?? 1)); ?>%"></div>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Aprobados</small>
                                    <div class="progress-custom">
                                        <div class="progress-bar-custom" style="width: <?php echo calcular_porcentaje(($stats_presupuestos['aprobados'] ?? 0), ($stats_presupuestos['total_presupuestos'] ?? 1)); ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="chart-container">
                        <h5 class="mb-4"><i class="bi bi-activity"></i> Actividad Reciente</h5>
                        <div class="recent-activity">
                            <!-- Actividad reciente simulada -->
                            <div class="activity-item">
                                <div class="activity-icon" style="background: #28a745;">
                                    <i class="bi bi-check"></i>
                                </div>
                                <div class="activity-content">
                                    <h6>Orden completada</h6>
                                    <p class="mb-1">Orden #001234 finalizada</p>
                                    <div class="activity-time">Hace 2 horas</div>
                                </div>
                            </div>
                            
                            <div class="activity-item">
                                <div class="activity-icon" style="background: #17a2b8;">
                                    <i class="bi bi-person-plus"></i>
                                </div>
                                <div class="activity-content">
                                    <h6>Nuevo cliente</h6>
                                    <p class="mb-1">Cliente registrado en el sistema</p>
                                    <div class="activity-time">Hace 4 horas</div>
                                </div>
                            </div>
                            
                            <div class="activity-item">
                                <div class="activity-icon" style="background: #ffc107;">
                                    <i class="bi bi-calendar"></i>
                                </div>
                                <div class="activity-content">
                                    <h6>Cita programada</h6>
                                    <p class="mb-1">Nueva cita para mañana</p>
                                    <div class="activity-time">Hace 6 horas</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- Dashboard para Cliente -->
            
            <!-- Estadísticas del Cliente -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-number"><?php echo count($mis_vehiculos); ?></div>
                                <div class="stat-label">Mis Vehículos</div>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-car-front"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-number"><?php echo count($mis_citas); ?></div>
                                <div class="stat-label">Citas Programadas</div>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-number"><?php echo count($mi_historial); ?></div>
                                <div class="stat-label">Servicios Realizados</div>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-wrench"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-number"><?php echo count($mis_presupuestos); ?></div>
                                <div class="stat-label">Presupuestos</div>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-calculator"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones del Cliente -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <h5 class="mb-3"><i class="bi bi-lightning text-primary"></i> Acciones Disponibles</h5>
                    <div class="quick-actions">
                        <?php if (!$tiene_cita_activa): ?>
                            <a href="index.php?pagina=agendamiento" class="quick-action-btn">
                                <i class="bi bi-calendar-plus"></i> Agendar Nueva Cita
                            </a>
                        <?php else: ?>
                            <span class="quick-action-btn" style="background: #6c757d;">
                                <i class="bi bi-calendar-check"></i> Ya tienes una cita activa
                            </span>
                        <?php endif; ?>
                        
                        <a href="index.php?pagina=tabla/historial" class="quick-action-btn">
                            <i class="bi bi-clock-history"></i> Ver Historial
                        </a>
                        <a href="index.php?pagina=nuevo/vehiculo" class="quick-action-btn">
                            <i class="bi bi-car-front-fill"></i> Registrar Vehículo
                        </a>
                    </div>
                </div>

                <!-- Notificaciones -->
                <div class="col-md-4">
                    <?php if (!empty($notificaciones)): ?>
                        <h5 class="mb-3"><i class="bi bi-bell text-warning"></i> Notificaciones</h5>
                        <div class="alert-card alert-info">
                            <div class="alert-header">
                                <i class="bi bi-info-circle"></i> Mensajes del Sistema
                                <span class="notification-badge"><?php echo count(array_filter($notificaciones, function($n) { return !$n['leida']; })); ?></span>
                            </div>
                            <div class="p-3">
                                <?php foreach (array_slice($notificaciones, 0, 3) as $notif): ?>
                                    <div class="mb-2 <?php echo $notif['leida'] ? 'text-muted' : ''; ?>">
                                        <small><?php echo $notif['mensaje']; ?></small>
                                        <br><small class="text-muted"><?php echo tiempo_transcurrido($notif['fecha_creacion']); ?></small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Mis Vehículos -->
            <div class="row">
                <div class="col-12">
                    <div class="chart-container">
                        <h5 class="mb-4"><i class="bi bi-car-front"></i> Mis Vehículos</h5>
                        
                        <?php if (!empty($mis_vehiculos)): ?>
                            <div class="row">
                                <?php foreach ($mis_vehiculos as $vehiculo): ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body">
                                                <h6 class="card-title text-primary">
                                                    <i class="bi bi-car-front"></i> <?php echo $vehiculo['matricula']; ?>
                                                </h6>
                                                <p class="card-text mb-2">
                                                    <strong><?php echo $vehiculo['marca'] . ' ' . $vehiculo['modelo']; ?></strong><br>
                                                    <small class="text-muted">Año: <?php echo $vehiculo['anho']; ?></small>
                                                </p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="badge bg-<?php echo $vehiculo['estado'] == 'activo' ? 'success' : 'secondary'; ?>">
                                                        <?php echo ucfirst($vehiculo['estado']); ?>
                                                    </span>
                                                    <small class="text-muted">
                                                        <i class="bi bi-speedometer2"></i> 
                                                        <?php echo number_format($vehiculo['kilometraje_actual'] ?? 0); ?> km
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-car-front display-1 text-muted"></i>
                                <h5 class="mt-3">No tienes vehículos registrados</h5>
                                <p class="text-muted">Registra tu primer vehículo para comenzar a usar nuestros servicios</p>
                                <a href="index.php?pagina=nuevo/vehiculo" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Registrar Vehículo
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        <?php endif; ?>

    </div>
</div>

<script>
// Actualizar notificaciones cada 30 segundos (solo para personal)
<?php if ($tipo_usuario == "personal"): ?>
setInterval(function() {
    fetch('?accion_ajax=obtener_notificaciones', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'accion_ajax=obtener_notificaciones'
    })
    .then(response => response.json())
    .then(data => {
        // Actualizar badges de notificación
        if (data.citas_pendientes) {
            document.querySelectorAll('.notification-badge').forEach(badge => {
                if (badge.closest('.alert-success')) {
                    badge.textContent = data.citas_pendientes;
                }
            });
        }
    })
    .catch(error => console.log('Error actualizando notificaciones:', error));
}, 30000);
<?php endif; ?>

// Animaciones para las tarjetas estadísticas
document.addEventListener('DOMContentLoaded', function() {
    const statNumbers = document.querySelectorAll('.stat-number');
    
    statNumbers.forEach(stat => {
        const finalValue = parseInt(stat.textContent);
        let currentValue = 0;
        const increment = finalValue / 30;
        
        const updateCounter = () => {
            if (currentValue < finalValue) {
                currentValue += increment;
                stat.textContent = Math.floor(currentValue);
                requestAnimationFrame(updateCounter);
            } else {
                stat.textContent = finalValue;
            }
        };
        
        // Iniciar animación cuando la tarjeta sea visible
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    updateCounter();
                    observer.unobserve(entry.target);
                }
            });
        });
        
        observer.observe(stat.closest('.stat-card'));
    });
});
</script>