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

// Obtener información de la empresa
$infoEmpresa = ControladorPlantilla::ctrObtenerInfoEmpresa();

// Obtener datos según el tipo de usuario
if ($_SESSION["tipo_usuario"] == "personal") {
    // Datos para personal
    require_once "../controlador/agendamiento_controlador.php";
    require_once "../controlador/orden_trabajo_controlador.php";
    require_once "../controlador/cliente_controlador.php";
    require_once "../controlador/vehiculo_controlador.php";
    require_once "../controlador/presupuesto_controlador.php";
    require_once "../controlador/factura_controlador.php";
    
    $citasPendientes = ControladorAgendamiento::listarSolicitudesPendientes();
    $ordenesRecientes = OrdenTrabajoControlador::ctrListarOrdenesTrabajo();
    $totalVehiculos = VehiculoControlador::ctrContarVehiculos();
    $totalClientes = ClienteControlador::ctrContarClientes();
    $presupuestosPendientes = PresupuestoControlador::ctrContarPresupuestosPorEstado("pendiente");
    $facturasPendientes = FacturaControlador::ctrContarFacturasPorEstado("pendiente");
} else {
    // Datos para cliente
    $id_cliente = $_SESSION["id_cliente"];
    $misCitas = ControladorAgendamiento::obtenerCitasCliente($id_cliente);
    $misVehiculos = VehiculoControlador::ctrListarVehiculosCliente($id_cliente);
    $misOrdenes = OrdenTrabajoControlador::ctrObtenerOrdenesPorCliente($id_cliente);
    $misPresupuestos = PresupuestoControlador::ctrObtenerPresupuestosPorCliente($id_cliente);
}
?>

<title>INICIO - Panel de Control | <?php echo $infoEmpresa['nombre_empresa']; ?></title>

<style>
    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
    }

    .dashboard-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
        border-radius: 0 0 20px 20px;
    }

    .stat-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, #ff6b6b, #4ecdc4, #45b7d1, #96ceb4);
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: bold;
        color: #2c3e50;
        margin-bottom: 0.5rem;
    }

    .stat-label {
        color: #7f8c8d;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .stat-icon {
        position: absolute;
        right: 1rem;
        top: 1rem;
        font-size: 2rem;
        opacity: 0.2;
    }

    .quick-action {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 10px;
        text-decoration: none;
        display: inline-block;
        margin: 0.5rem;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .quick-action:hover {
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
    }

    .recent-activities {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .activity-item {
        padding: 1rem;
        border-left: 3px solid #3498db;
        margin-bottom: 1rem;
        background: #f8f9fa;
        border-radius: 0 10px 10px 0;
    }

    .activity-time {
        color: #7f8c8d;
        font-size: 0.85rem;
    }

    .welcome-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
        text-align: center;
    }

    .chart-container {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        margin-bottom: 1.5rem;
    }

    .alert-custom {
        border-radius: 10px;
        border: none;
        padding: 1rem 1.5rem;
    }

    @media (max-width: 768px) {
        .stat-card {
            margin-bottom: 1rem;
        }
        
        .quick-action {
            display: block;
            text-align: center;
            margin: 0.5rem 0;
        }
    }
</style>

<div class="container-fluid">
    <?php if ($_SESSION["tipo_usuario"] == "personal"): ?>
        <!-- PANEL DE PERSONAL -->
        <div class="welcome-card">
            <h1><i class="bi bi-speedometer2"></i> Panel de Administración</h1>
            <p class="mb-0">Bienvenido/a <?php echo $_SESSION["nombre"] . " " . $_SESSION["apellido"]; ?></p>
            <small><?php echo date('l, d \d\e F \d\e Y'); ?></small>
        </div>

        <!-- ESTADÍSTICAS PRINCIPALES -->
        <div class="row">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-people-fill text-primary"></i>
                    </div>
                    <div class="stat-number"><?php echo number_format($totalClientes); ?></div>
                    <div class="stat-label">Total Clientes</div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-car-front-fill text-success"></i>
                    </div>
                    <div class="stat-number"><?php echo number_format($totalVehiculos); ?></div>
                    <div class="stat-label">Vehículos Registrados</div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-clock-fill text-warning"></i>
                    </div>
                    <div class="stat-number"><?php echo count($citasPendientes); ?></div>
                    <div class="stat-label">Citas Pendientes</div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-tools text-danger"></i>
                    </div>
                    <div class="stat-number"><?php echo count(array_filter($ordenesRecientes, function($o) { return $o['estado'] == 'en_proceso'; })); ?></div>
                    <div class="stat-label">Órdenes en Proceso</div>
                </div>
            </div>
        </div>

        <!-- ESTADÍSTICAS SECUNDARIAS -->
        <div class="row">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-file-earmark-text text-info"></i>
                    </div>
                    <div class="stat-number"><?php echo $presupuestosPendientes; ?></div>
                    <div class="stat-label">Presupuestos Pendientes</div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-receipt text-purple"></i>
                    </div>
                    <div class="stat-number"><?php echo $facturasPendientes; ?></div>
                    <div class="stat-label">Facturas Pendientes</div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-calendar-check text-success"></i>
                    </div>
                    <div class="stat-number"><?php echo date('d'); ?></div>
                    <div class="stat-label">Día del Mes</div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-graph-up text-success"></i>
                    </div>
                    <div class="stat-number"><?php echo date('H'); ?>:<?php echo date('i'); ?></div>
                    <div class="stat-label">Hora Actual</div>
                </div>
            </div>
        </div>

        <!-- ACCIONES RÁPIDAS -->
        <div class="row">
            <div class="col-md-12">
                <div class="recent-activities">
                    <h4><i class="bi bi-lightning-fill"></i> Acciones Rápidas</h4>
                    <div class="text-center">
                        <a href="index.php?pagina=nuevo/cliente" class="quick-action">
                            <i class="bi bi-person-plus-fill"></i> Nuevo Cliente
                        </a>
                        <a href="index.php?pagina=nuevo/vehiculo" class="quick-action">
                            <i class="bi bi-car-front"></i> Registrar Vehículo
                        </a>
                        <a href="index.php?pagina=nuevo/orden_trabajo" class="quick-action">
                            <i class="bi bi-tools"></i> Nueva Orden
                        </a>
                        <a href="index.php?pagina=nuevo/presupuesto" class="quick-action">
                            <i class="bi bi-file-earmark-plus"></i> Nuevo Presupuesto
                        </a>
                        <a href="index.php?pagina=nuevo/factura" class="quick-action">
                            <i class="bi bi-receipt"></i> Nueva Factura
                        </a>
                        <a href="index.php?pagina=tabla/productos" class="quick-action">
                            <i class="bi bi-box"></i> Inventario
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- CITAS PENDIENTES Y ÓRDENES RECIENTES -->
        <div class="row">
            <div class="col-md-6">
                <div class="recent-activities">
                    <h4><i class="bi bi-calendar-event"></i> Citas Pendientes</h4>
                    <?php if (!empty($citasPendientes)): ?>
                        <?php foreach (array_slice($citasPendientes, 0, 5) as $cita): ?>
                            <div class="activity-item">
                                <strong><?php echo $cita['nombre_cliente']; ?></strong>
                                <p class="mb-1"><?php echo $cita['motivo_cita']; ?></p>
                                <small class="activity-time">
                                    <i class="bi bi-calendar"></i> <?php echo date('d/m/Y', strtotime($cita['fecha_cita'])); ?>
                                    <i class="bi bi-clock"></i> <?php echo date('H:i', strtotime($cita['hora_cita'])); ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                        <div class="text-center mt-3">
                            <a href="index.php?pagina=agendamiento" class="btn btn-primary btn-sm">
                                Ver todas las citas
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info alert-custom">
                            <i class="bi bi-info-circle"></i> No hay citas pendientes
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="recent-activities">
                    <h4><i class="bi bi-tools"></i> Órdenes Recientes</h4>
                    <?php if (!empty($ordenesRecientes)): ?>
                        <?php foreach (array_slice($ordenesRecientes, 0, 5) as $orden): ?>
                            <div class="activity-item">
                                <strong>Orden #<?php echo $orden['id_orden']; ?></strong>
                                <p class="mb-1"><?php echo $orden['nombre_cliente']; ?> - <?php echo $orden['marca'] . ' ' . $orden['modelo']; ?></p>
                                <small class="activity-time">
                                    <span class="badge <?php echo $orden['estado'] == 'completado' ? 'bg-success' : ($orden['estado'] == 'en_proceso' ? 'bg-warning' : 'bg-secondary'); ?>">
                                        <?php echo ucfirst($orden['estado']); ?>
                                    </span>
                                    <i class="bi bi-calendar"></i> <?php echo date('d/m/Y', strtotime($orden['fecha_ingreso'])); ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                        <div class="text-center mt-3">
                            <a href="index.php?pagina=tabla/orden_trabajo" class="btn btn-primary btn-sm">
                                Ver todas las órdenes
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info alert-custom">
                            <i class="bi bi-info-circle"></i> No hay órdenes registradas
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- PANEL DE CLIENTE -->
        <div class="welcome-card">
            <h1><i class="bi bi-person-circle"></i> Mi Panel de Cliente</h1>
            <p class="mb-0">Bienvenido/a <?php echo $_SESSION["nombre"] . " " . $_SESSION["apellido"]; ?></p>
            <small><?php echo date('l, d \d\e F \d\e Y'); ?></small>
        </div>

        <!-- ESTADÍSTICAS DEL CLIENTE -->
        <div class="row">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-car-front-fill text-primary"></i>
                    </div>
                    <div class="stat-number"><?php echo count($misVehiculos); ?></div>
                    <div class="stat-label">Mis Vehículos</div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-calendar-event text-success"></i>
                    </div>
                    <div class="stat-number"><?php echo count($misCitas); ?></div>
                    <div class="stat-label">Mis Citas</div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-tools text-warning"></i>
                    </div>
                    <div class="stat-number"><?php echo count($misOrdenes); ?></div>
                    <div class="stat-label">Órdenes de Trabajo</div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-file-earmark-text text-info"></i>
                    </div>
                    <div class="stat-number"><?php echo count($misPresupuestos); ?></div>
                    <div class="stat-label">Presupuestos</div>
                </div>
            </div>
        </div>

        <!-- ACCIONES RÁPIDAS PARA CLIENTE -->
        <div class="row">
            <div class="col-md-12">
                <div class="recent-activities">
                    <h4><i class="bi bi-lightning-fill"></i> Acciones Disponibles</h4>
                    <div class="text-center">
                        <a href="index.php?pagina=agendamiento" class="quick-action">
                            <i class="bi bi-calendar-plus"></i> Agendar Cita
                        </a>
                        <a href="index.php?pagina=tabla/historial" class="quick-action">
                            <i class="bi bi-clock-history"></i> Mi Historial
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- MIS VEHÍCULOS Y CITAS -->
        <div class="row">
            <div class="col-md-6">
                <div class="recent-activities">
                    <h4><i class="bi bi-car-front"></i> Mis Vehículos</h4>
                    <?php if (!empty($misVehiculos)): ?>
                        <?php foreach ($misVehiculos as $vehiculo): ?>
                            <div class="activity-item">
                                <strong><?php echo $vehiculo['marca'] . ' ' . $vehiculo['modelo']; ?></strong>
                                <p class="mb-1">Matrícula: <?php echo $vehiculo['matricula']; ?></p>
                                <small class="activity-time">
                                    <?php echo $vehiculo['año']; ?> - <?php echo $vehiculo['color']; ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-info alert-custom">
                            <i class="bi bi-info-circle"></i> No tienes vehículos registrados
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="recent-activities">
                    <h4><i class="bi bi-calendar-event"></i> Mis Citas Recientes</h4>
                    <?php if (!empty($misCitas)): ?>
                        <?php foreach (array_slice($misCitas, 0, 5) as $cita): ?>
                            <div class="activity-item">
                                <strong>Cita #<?php echo $cita['id_agendamiento']; ?></strong>
                                <p class="mb-1"><?php echo $cita['motivo_cita']; ?></p>
                                <small class="activity-time">
                                    <span class="badge <?php echo $cita['estado'] == 'completada' ? 'bg-success' : ($cita['estado'] == 'confirmada' ? 'bg-info' : 'bg-warning'); ?>">
                                        <?php echo ucfirst($cita['estado']); ?>
                                    </span>
                                    <i class="bi bi-calendar"></i> <?php echo date('d/m/Y', strtotime($cita['fecha_cita'])); ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-info alert-custom">
                            <i class="bi bi-info-circle"></i> No tienes citas registradas
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Scripts para interactividad -->
<script>
    // Actualizar reloj en tiempo real
    function actualizarReloj() {
        const ahora = new Date();
        const hora = ahora.getHours().toString().padStart(2, '0');
        const minutos = ahora.getMinutes().toString().padStart(2, '0');
        
        // Buscar el elemento del reloj si existe
        const elementoHora = document.querySelector('.stat-number');
        if (elementoHora && elementoHora.textContent.includes(':')) {
            elementoHora.textContent = `${hora}:${minutos}`;
        }
    }

    // Actualizar cada minuto
    setInterval(actualizarReloj, 60000);

    // Animaciones de entrada
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.stat-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });
</script>