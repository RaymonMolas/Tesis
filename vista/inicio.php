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

    // Contar citas por estado para gráficos
    $todasLasCitas = ControladorAgendamiento::listarTodasLasCitas();
    $citasAprobadas = array_filter($todasLasCitas, function ($cita) {
        return $cita['estado'] == 'aprobado';
    });
    $citasPendientesCount = array_filter($todasLasCitas, function ($cita) {
        return $cita['estado'] == 'pendiente';
    });
    $citasCompletadas = array_filter($todasLasCitas, function ($cita) {
        return $cita['estado'] == 'completado';
    });
} else {
    $id_cliente = $_SESSION["id_cliente"];
    $misCitas = ControladorAgendamiento::obtenerCitasCliente($id_cliente);
    $misVehiculos = VehiculoControlador::ctrListarVehiculosCliente($id_cliente);
}

// Obtener fecha actual para saludo
$hora = date('H');
if ($hora < 12) {
    $saludo = "Buenos días";
    $icono_saludo = "sun";
} elseif ($hora < 18) {
    $saludo = "Buenas tardes";
    $icono_saludo = "sun";
} else {
    $saludo = "Buenas noches";
    $icono_saludo = "moon";
}
?>

<!-- Encabezado de bienvenida -->
<div class="mb-8">
    <div class="bg-gradient-to-r from-motor-red-600 to-motor-red-800 rounded-xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center mb-2">
                    <i data-lucide="<?php echo $icono_saludo; ?>" class="w-6 h-6 mr-2"></i>
                    <h1 class="text-2xl font-bold"><?php echo $saludo; ?>, <?php echo $_SESSION["usuario"]; ?>!</h1>
                </div>
                <p class="text-motor-red-100">
                    <?php if ($_SESSION["tipo_usuario"] == "personal"): ?>
                        Bienvenido al panel de administración de Motor Service
                    <?php else: ?>
                        Bienvenido a tu portal de cliente Motor Service
                    <?php endif; ?>
                </p>
            </div>
            <div class="hidden md:block">
                <div class="text-right">
                    <p class="text-sm text-motor-red-100"><?php echo strftime("%A, %d de %B de %Y", time()); ?></p>
                    <p class="text-sm text-motor-red-100" id="currentTime"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($_SESSION["tipo_usuario"] == "personal"): ?>
    <!-- Panel de Personal/Administrador -->

    <!-- Estadísticas principales -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
        <!-- Total Clientes -->
        <div
            class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-shadow duration-300">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-50 text-blue-600">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Clientes</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $totalClientes; ?></p>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-center text-sm text-green-600">
                    <i data-lucide="trending-up" class="w-4 h-4 mr-1"></i>
                    <span>+5% este mes</span>
                </div>
            </div>
        </div>

        <!-- Total Vehículos -->
        <div
            class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-shadow duration-300">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-50 text-green-600">
                    <i data-lucide="car" class="w-6 h-6"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Vehículos</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $totalVehiculos; ?></p>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-center text-sm text-green-600">
                    <i data-lucide="trending-up" class="w-4 h-4 mr-1"></i>
                    <span>+8% este mes</span>
                </div>
            </div>
        </div>

        <!-- Citas Pendientes -->
        <div
            class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-shadow duration-300">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-50 text-yellow-600">
                    <i data-lucide="clock" class="w-6 h-6"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Citas Pendientes</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo count($citasPendientes); ?></p>
                </div>
            </div>
            <div class="mt-4">
                <?php if (count($citasPendientes) > 0): ?>
                    <div class="flex items-center text-sm text-yellow-600">
                        <i data-lucide="alert-circle" class="w-4 h-4 mr-1"></i>
                        <span>Requieren atención</span>
                    </div>
                <?php else: ?>
                    <div class="flex items-center text-sm text-green-600">
                        <i data-lucide="check-circle" class="w-4 h-4 mr-1"></i>
                        <span>Todo al día</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Órdenes Activas -->
        <div
            class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-shadow duration-300">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-motor-red-50 text-motor-red-600">
                    <i data-lucide="wrench" class="w-6 h-6"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Órdenes Activas</p>
                    <p class="text-2xl font-bold text-gray-900">
                        <?php
                        $ordenesActivas = array_filter($ordenesRecientes, function ($orden) {
                            return $orden['estado'] == 'en_proceso';
                        });
                        echo count($ordenesActivas);
                        ?>
                    </p>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-center text-sm text-motor-red-600">
                    <i data-lucide="settings" class="w-4 h-4 mr-1"></i>
                    <span>En proceso</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos y tablas -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
        <!-- Gráfico de citas -->
        <div class="xl:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Estado de Citas</h3>
                <div class="flex items-center space-x-2">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                        <span class="text-sm text-gray-600">Aprobadas</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></div>
                        <span class="text-sm text-gray-600">Pendientes</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                        <span class="text-sm text-gray-600">Completadas</span>
                    </div>
                </div>
            </div>
            <div class="h-64">
                <canvas id="citasChart"></canvas>
            </div>
        </div>

        <!-- Acciones rápidas -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Acciones Rápidas</h3>
            <div class="space-y-3">
                <a href="index.php?pagina=nuevo/orden_trabajo"
                    class="flex items-center p-4 bg-motor-red-50 hover:bg-motor-red-100 rounded-lg transition-colors duration-200 group">
                    <div
                        class="p-2 bg-motor-red-600 rounded-lg text-white group-hover:scale-110 transition-transform duration-200">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                    </div>
                    <div class="ml-4">
                        <p class="font-medium text-gray-900">Nueva Orden</p>
                        <p class="text-sm text-gray-600">Crear orden de trabajo</p>
                    </div>
                </a>

                <a href="index.php?pagina=nuevo/cliente"
                    class="flex items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors duration-200 group">
                    <div
                        class="p-2 bg-blue-600 rounded-lg text-white group-hover:scale-110 transition-transform duration-200">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                    </div>
                    <div class="ml-4">
                        <p class="font-medium text-gray-900">Nuevo Cliente</p>
                        <p class="text-sm text-gray-600">Registrar cliente</p>
                    </div>
                </a>

                <a href="index.php?pagina=nuevo/vehiculo"
                    class="flex items-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition-colors duration-200 group">
                    <div
                        class="p-2 bg-green-600 rounded-lg text-white group-hover:scale-110 transition-transform duration-200">
                        <i data-lucide="car" class="w-4 h-4"></i>
                    </div>
                    <div class="ml-4">
                        <p class="font-medium text-gray-900">Nuevo Vehículo</p>
                        <p class="text-sm text-gray-600">Registrar vehículo</p>
                    </div>
                </a>

                <a href="index.php?pagina=nuevo/presupuesto"
                    class="flex items-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors duration-200 group">
                    <div
                        class="p-2 bg-purple-600 rounded-lg text-white group-hover:scale-110 transition-transform duration-200">
                        <i data-lucide="calculator" class="w-4 h-4"></i>
                    </div>
                    <div class="ml-4">
                        <p class="font-medium text-gray-900">Nuevo Presupuesto</p>
                        <p class="text-sm text-gray-600">Crear presupuesto</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Citas pendientes y órdenes recientes -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- Citas Pendientes -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Citas Pendientes</h3>
                    <a href="index.php?pagina=tabla/agendamiento"
                        class="text-motor-red-600 hover:text-motor-red-700 text-sm font-medium">
                        Ver todas
                    </a>
                </div>
            </div>
            <div class="p-6">
                <?php if (!empty($citasPendientes)): ?>
                    <div class="space-y-4">
                        <?php foreach (array_slice($citasPendientes, 0, 5) as $cita): ?>
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                        <i data-lucide="calendar" class="w-5 h-5 text-yellow-600"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="font-medium text-gray-900"><?php echo $cita['nombre_cliente']; ?></p>
                                        <p class="text-sm text-gray-600">
                                            <?php echo date('d/m/Y H:i', strtotime($cita['fecha'] . ' ' . $cita['hora'])); ?></p>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <form method="post" class="inline">
                                        <input type="hidden" name="accion_cita" value="aprobar">
                                        <input type="hidden" name="id_cita" value="<?php echo $cita['id_cita']; ?>">
                                        <button type="submit"
                                            class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors duration-200">
                                            <i data-lucide="check" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                    <form method="post" class="inline">
                                        <input type="hidden" name="accion_cita" value="rechazar">
                                        <input type="hidden" name="id_cita" value="<?php echo $cita['id_cita']; ?>">
                                        <button type="submit"
                                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                            <i data-lucide="x" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <i data-lucide="calendar-check" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>
                        <p class="text-gray-500">No hay citas pendientes</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Órdenes Recientes -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Órdenes Recientes</h3>
                    <a href="index.php?pagina=tabla/orden_trabajo"
                        class="text-motor-red-600 hover:text-motor-red-700 text-sm font-medium">
                        Ver todas
                    </a>
                </div>
            </div>
            <div class="p-6">
                <?php if (!empty($ordenesRecientes)): ?>
                    <div class="space-y-4">
                        <?php foreach (array_slice($ordenesRecientes, 0, 5) as $orden): ?>
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-motor-red-100 rounded-full flex items-center justify-center">
                                        <i data-lucide="wrench" class="w-5 h-5 text-motor-red-600"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="font-medium text-gray-900"><?php echo $orden['marca'] . ' ' . $orden['modelo']; ?>
                                        </p>
                                        <p class="text-sm text-gray-600"><?php echo $orden['nombre_cliente']; ?></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        <?php echo $orden['estado'] == 'en_proceso' ? 'bg-yellow-100 text-yellow-800' :
                                            ($orden['estado'] == 'completado' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $orden['estado'])); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <i data-lucide="wrench" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>
                        <p class="text-gray-500">No hay órdenes de trabajo</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- Panel de Cliente -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Información principal -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Mis Citas -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Mis Citas</h3>
                        <a href="index.php?pagina=nuevo/agendamiento"
                            class="bg-motor-red-600 hover:bg-motor-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                            Nueva Cita
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    <?php if (!empty($misCitas)): ?>
                        <div class="space-y-4">
                            <?php foreach (array_slice($misCitas, 0, 3) as $cita): ?>
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-motor-red-100 rounded-full flex items-center justify-center">
                                            <i data-lucide="calendar" class="w-5 h-5 text-motor-red-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <p class="font-medium text-gray-900">
                                                <?php echo date('d/m/Y H:i', strtotime($cita['fecha'] . ' ' . $cita['hora'])); ?>
                                            </p>
                                            <p class="text-sm text-gray-600"><?php echo $cita['motivo']; ?></p>
                                        </div>
                                    </div>
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        <?php echo $cita['estado'] == 'aprobado' ? 'bg-green-100 text-green-800' :
                                            ($cita['estado'] == 'pendiente' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                                        <?php echo ucfirst($cita['estado']); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <i data-lucide="calendar-plus" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>
                            <p class="text-gray-500 mb-4">No tienes citas agendadas</p>
                            <a href="index.php?pagina=nuevo/agendamiento"
                                class="inline-flex items-center px-4 py-2 bg-motor-red-600 text-white rounded-lg hover:bg-motor-red-700 transition-colors duration-200">
                                <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                                Agendar primera cita
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Mis Vehículos -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Mis Vehículos</h3>
                </div>
                <div class="p-6">
                    <?php if (!empty($misVehiculos)): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach ($misVehiculos as $vehiculo): ?>
                                <div class="p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow duration-200">
                                    <div class="flex items-center mb-3">
                                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                            <i data-lucide="car" class="w-5 h-5 text-blue-600"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="font-medium text-gray-900">
                                                <?php echo $vehiculo['marca'] . ' ' . $vehiculo['modelo']; ?></p>
                                            <p class="text-sm text-gray-600"><?php echo $vehiculo['matricula']; ?></p>
                                        </div>
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        <p>Año: <?php echo $vehiculo['anho']; ?></p>
                                        <p>Color: <?php echo $vehiculo['color']; ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <i data-lucide="car" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>
                            <p class="text-gray-500">No tienes vehículos registrados</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar de información -->
        <div class="space-y-6">
            <!-- Información de contacto -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Contacto</h3>
                <div class="space-y-3">
                    <div class="flex items-center">
                        <i data-lucide="phone" class="w-5 h-5 text-gray-400 mr-3"></i>
                        <span class="text-sm text-gray-600">(0984) 800 586</span>
                    </div>
                    <div class="flex items-center">
                        <i data-lucide="mail" class="w-5 h-5 text-gray-400 mr-3"></i>
                        <span class="text-sm text-gray-600">info@motorservicepy.com</span>
                    </div>
                    <div class="flex items-center">
                        <i data-lucide="map-pin" class="w-5 h-5 text-gray-400 mr-3"></i>
                        <span class="text-sm text-gray-600">Asunción, Paraguay</span>
                    </div>
                </div>
            </div>

            <!-- Horarios -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Horarios</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Lunes - Viernes</span>
                        <span class="font-medium">08:00 - 18:00</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Sábados</span>
                        <span class="font-medium">08:00 - 13:00</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Domingos</span>
                        <span class="text-motor-red-600 font-medium">Cerrado</span>
                    </div>
                </div>
            </div>

            <!-- Acciones rápidas cliente -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Acciones Rápidas</h3>
                <div class="space-y-3">
                    <a href="index.php?pagina=nuevo/agendamiento"
                        class="flex items-center p-3 bg-motor-red-50 hover:bg-motor-red-100 rounded-lg transition-colors duration-200 group">
                        <div
                            class="p-2 bg-motor-red-600 rounded-lg text-white group-hover:scale-110 transition-transform duration-200">
                            <i data-lucide="calendar-plus" class="w-4 h-4"></i>
                        </div>
                        <span class="ml-3 font-medium text-gray-900">Agendar Cita</span>
                    </a>

                    <a href="index.php?pagina=tabla/agendamiento_cliente"
                        class="flex items-center p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors duration-200 group">
                        <div
                            class="p-2 bg-blue-600 rounded-lg text-white group-hover:scale-110 transition-transform duration-200">
                            <i data-lucide="calendar" class="w-4 h-4"></i>
                        </div>
                        <span class="ml-3 font-medium text-gray-900">Ver Mis Citas</span>
                    </a>

                    <a href="index.php?pagina=tabla/vehiculos_cliente"
                        class="flex items-center p-3 bg-green-50 hover:bg-green-100 rounded-lg transition-colors duration-200 group">
                        <div
                            class="p-2 bg-green-600 rounded-lg text-white group-hover:scale-110 transition-transform duration-200">
                            <i data-lucide="car" class="w-4 h-4"></i>
                        </div>
                        <span class="ml-3 font-medium text-gray-900">Mis Vehículos</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Inicializar iconos
        lucide.createIcons();

        // Actualizar reloj
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('es-PY', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            const timeElement = document.getElementById('currentTime');
            if (timeElement) {
                timeElement.textContent = timeString;
            }
        }

        updateTime();
        setInterval(updateTime, 1000);

        <?php if ($_SESSION["tipo_usuario"] == "personal"): ?>
            // Gráfico de citas (solo para personal)
            const ctx = document.getElementById('citasChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Aprobadas', 'Pendientes', 'Completadas'],
                        datasets: [{
                            data: [
                                <?php echo count($citasAprobadas); ?>,
                                <?php echo count($citasPendientesCount); ?>,
                                <?php echo count($citasCompletadas); ?>
                            ],
                            backgroundColor: [
                                '#3B82F6',
                                '#F59E0B',
                                '#10B981'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }
        <?php endif; ?>

        // Animaciones de entrada
        const cards = document.querySelectorAll('.bg-white');
        cards.forEach((card, index) => {
            setTimeout(() => {
                card.classList.add('animate-fade-in-up');
            }, index * 100);
        });
    });
</script>