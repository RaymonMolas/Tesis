<?php
if (!isset($_SESSION["validarIngreso"])) {
    echo '<script>window.location = "index.php?pagina=login";</script>';
    return;
} elseif ($_SESSION["validarIngreso"] != "ok") {
    echo '<script>window.location = "index.php?pagina=login";</script>';
    return;
}

// Verificar que se haya proporcionado un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<script>
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "ID de orden no válido",
            confirmButtonColor: "#dc2626"
        }).then(() => {
            window.location = "index.php?pagina=tabla/orden_trabajo";
        });
    </script>';
    return;
}

$id_orden = (int) $_GET['id'];

// Obtener datos de la orden
$orden = OrdenTrabajoControlador::ctrObtenerOrdenTrabajo($id_orden);

if (!$orden) {
    echo '<script>
        Swal.fire({
            icon: "error",
            title: "Orden no encontrada",
            text: "La orden de trabajo especificada no existe",
            confirmButtonColor: "#dc2626"
        }).then(() => {
            window.location = "index.php?pagina=tabla/orden_trabajo";
        });
    </script>';
    return;
}

// Obtener detalles de la orden
$detalles = ModeloOrdenDetalle::mdlObtenerDetalles($id_orden);
$total = ModeloOrdenDetalle::mdlCalcularTotal($id_orden);

// Calcular estadísticas
$totalServicios = 0;
$totalProductos = 0;
$tiempoTotal = 0;

foreach ($detalles as $detalle) {
    if ($detalle['tipo_servicio'] == 'servicio') {
        $totalServicios += $detalle['subtotal'];
    } else {
        $totalProductos += $detalle['subtotal'];
    }
    $tiempoTotal += $detalle['tiempo_estimado'] ?? 0;
}
?>

<!-- Encabezado de la página -->
<div class="mb-6">
    <nav class="flex" aria-label="Breadcrumb">
        <ol role="list" class="flex items-center space-x-4">
            <li>
                <div>
                    <a href="index.php?pagina=inicio" class="text-gray-400 hover:text-gray-500">
                        <i data-lucide="home" class="h-5 w-5"></i>
                        <span class="sr-only">Inicio</span>
                    </a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <i data-lucide="chevron-right" class="h-5 w-5 text-gray-400"></i>
                    <a href="index.php?pagina=tabla/orden_trabajo" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">
                        Órdenes de Trabajo
                    </a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <i data-lucide="chevron-right" class="h-5 w-5 text-gray-400"></i>
                    <span class="ml-4 text-sm font-medium text-gray-900">
                        Orden #<?php echo str_pad($orden['id_orden'], 6, '0', STR_PAD_LEFT); ?>
                    </span>
                </div>
            </li>
        </ol>
    </nav>
</div>

<!-- Encabezado con acciones -->
<div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
    <div class="px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                    <i data-lucide="wrench" class="w-6 h-6 mr-3 text-motor-red-600"></i>
                    Orden de Trabajo #<?php echo str_pad($orden['id_orden'], 6, '0', STR_PAD_LEFT); ?>
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    Creada el <?php echo date('d/m/Y H:i', strtotime($orden['fecha_ingreso'])); ?>
                    por <?php echo $orden['nombre_personal']; ?>
                </p>
            </div>
            
            <!-- Estado de la orden -->
            <div class="flex items-center space-x-4">
                <?php
                $estadoClases = [
                    'en_proceso' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                    'completado' => 'bg-green-100 text-green-800 border-green-200',
                    'cancelado' => 'bg-red-100 text-red-800 border-red-200'
                ];
                $estadoTextos = [
                    'en_proceso' => 'En Proceso',
                    'completado' => 'Completado',
                    'cancelado' => 'Cancelado'
                ];
                ?>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border <?php echo $estadoClases[$orden['estado']]; ?>">
                    <?php echo $estadoTextos[$orden['estado']]; ?>
                </span>
                
                <?php if (!$orden['facturado'] && $orden['estado'] == 'completado'): ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 border border-blue-200">
                            <i data-lucide="file-text" class="w-4 h-4 mr-1"></i>
                            Pendiente Facturación
                        </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Botones de acción -->
    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <a href="index.php?pagina=tabla/orden_trabajo" 
                   class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-motor-red-500 transition-colors duration-200">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                    Volver
                </a>
                
                <?php if ($orden['estado'] == 'en_proceso'): ?>
                        <a href="index.php?pagina=editar/orden_trabajo&id=<?php echo $orden['id_orden']; ?>" 
                           class="inline-flex items-center px-3 py-2 border border-yellow-300 rounded-lg text-sm font-medium text-yellow-700 bg-yellow-50 hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-yellow-500 transition-colors duration-200">
                            <i data-lucide="edit" class="w-4 h-4 mr-2"></i>
                            Editar
                        </a>
                    
                        <button onclick="completarOrden(<?php echo $orden['id_orden']; ?>)" 
                                class="inline-flex items-center px-3 py-2 border border-green-300 rounded-lg text-sm font-medium text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors duration-200">
                            <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>
                            Completar
                        </button>
                <?php endif; ?>
                
                <?php if ($orden['estado'] == 'completado' && !$orden['facturado']): ?>
                        <a href="index.php?pagina=nuevo/factura&desde_orden=<?php echo $orden['id_orden']; ?>" 
                           class="inline-flex items-center px-3 py-2 border border-purple-300 rounded-lg text-sm font-medium text-purple-700 bg-purple-50 hover:bg-purple-100 focus:outline-none focus:ring-2 focus:ring-purple-500 transition-colors duration-200">
                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i>
                            Facturar
                        </a>
                <?php endif; ?>
            </div>
            
            <div class="flex items-center space-x-3">
                <button onclick="enviarPorEmail()" 
                        class="inline-flex items-center px-3 py-2 border border-blue-300 rounded-lg text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200">
                    <i data-lucide="mail" class="w-4 h-4 mr-2"></i>
                    Enviar por Email
                </button>
                
                <button onclick="window.print()" 
                        class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-motor-red-500 transition-colors duration-200">
                    <i data-lucide="printer" class="w-4 h-4 mr-2"></i>
                    Imprimir
                </button>
                
                <a href="../modelo/pdf/orden_trabajo_pdf.php?id=<?php echo $orden['id_orden']; ?>" 
                   target="_blank"
                   class="inline-flex items-center px-3 py-2 border border-motor-red-300 rounded-lg text-sm font-medium text-motor-red-700 bg-motor-red-50 hover:bg-motor-red-100 focus:outline-none focus:ring-2 focus:ring-motor-red-500 transition-colors duration-200">
                    <i data-lucide="download" class="w-4 h-4 mr-2"></i>
                    Generar PDF
                </a>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <!-- Contenido principal -->
    <div class="xl:col-span-2 space-y-6">
        
        <!-- Información del Vehículo y Cliente -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <i data-lucide="car" class="w-5 h-5 mr-2 text-motor-red-600"></i>
                    Información del Vehículo
                </h3>
            </div>
            <div class="px-6 py-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Datos del vehículo -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Datos del Vehículo</h4>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Marca/Modelo:</dt>
                                <dd class="font-medium text-gray-900"><?php echo $orden['marca'] . ' ' . $orden['modelo']; ?></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Matrícula:</dt>
                                <dd class="font-medium text-gray-900 font-mono"><?php echo $orden['matricula']; ?></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Año:</dt>
                                <dd class="font-medium text-gray-900"><?php echo $orden['anho']; ?></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Color:</dt>
                                <dd class="font-medium text-gray-900"><?php echo $orden['color']; ?></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Kilometraje:</dt>
                                <dd class="font-medium text-gray-900">
                                    <?php echo $orden['kilometraje_actual'] ? number_format($orden['kilometraje_actual']) . ' km' : 'No registrado'; ?>
                                </dd>
                            </div>
                        </dl>
                    </div>
                    
                    <!-- Datos del cliente -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Datos del Cliente</h4>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Cliente:</dt>
                                <dd class="font-medium text-gray-900"><?php echo $orden['nombre_cliente']; ?></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Teléfono:</dt>
                                <dd class="font-medium text-gray-900"><?php echo $orden['telefono_cliente'] ?? 'No registrado'; ?></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Email:</dt>
                                <dd class="font-medium text-gray-900"><?php echo $orden['email_cliente'] ?? 'No registrado'; ?></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Dirección:</dt>
                                <dd class="font-medium text-gray-900"><?php echo $orden['direccion_cliente'] ?? 'No registrada'; ?></dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Servicios y Productos -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <i data-lucide="list" class="w-5 h-5 mr-2 text-motor-red-600"></i>
                    Servicios y Productos
                </h3>
            </div>
            <div class="px-6 py-6">
                <?php if (!empty($detalles)): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Unit.</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($detalles as $detalle): ?>
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <?php if ($detalle['tipo_servicio'] == 'servicio'): ?>
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                                <i data-lucide="wrench" class="w-3 h-3 mr-1"></i>
                                                                Servicio
                                                            </span>
                                                    <?php else: ?>
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                <i data-lucide="package" class="w-3 h-3 mr-1"></i>
                                                                Producto
                                                            </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <div class="text-sm font-medium text-gray-900"><?php echo $detalle['tipo_servicio']; ?></div>
                                                    <div class="text-sm text-gray-500"><?php echo $detalle['descripcion']; ?></div>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <span class="text-sm font-medium text-gray-900"><?php echo $detalle['cantidad']; ?></span>
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <span class="text-sm font-medium text-gray-900">₲ <?php echo number_format($detalle['precio_unitario'], 0, ',', '.'); ?></span>
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <span class="text-sm font-bold text-gray-900">₲ <?php echo number_format($detalle['subtotal'], 0, ',', '.'); ?></span>
                                                </td>
                                            </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="4" class="px-4 py-3 text-right text-sm font-medium text-gray-900">Total:</td>
                                        <td class="px-4 py-3 text-right text-lg font-bold text-motor-red-600">
                                            ₲ <?php echo number_format($total, 0, ',', '.'); ?>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                <?php else: ?>
                        <div class="text-center py-8">
                            <i data-lucide="package-x" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>
                            <p class="text-gray-500">No hay servicios o productos registrados</p>
                        </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Observaciones -->
        <?php if (!empty($orden['observaciones'])): ?>
                <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 flex items-center">
                            <i data-lucide="message-square" class="w-5 h-5 mr-2 text-motor-red-600"></i>
                            Observaciones
                        </h3>
                    </div>
                    <div class="px-6 py-6">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-700 whitespace-pre-line"><?php echo htmlspecialchars($orden['observaciones']); ?></p>
                        </div>
                    </div>
                </div>
        <?php endif; ?>
    </div>

    <!-- Panel lateral -->
    <div class="space-y-6">
        
        <!-- Resumen financiero -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <i data-lucide="calculator" class="w-5 h-5 mr-2 text-motor-red-600"></i>
                    Resumen Financiero
                </h3>
            </div>
            <div class="px-6 py-6 space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Servicios:</span>
                    <span class="text-sm font-medium text-gray-900">₲ <?php echo number_format($totalServicios, 0, ',', '.'); ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Productos:</span>
                    <span class="text-sm font-medium text-gray-900">₲ <?php echo number_format($totalProductos, 0, ',', '.'); ?></span>
                </div>
                <hr class="border-gray-200">
                <div class="flex justify-between items-center">
                    <span class="text-base font-medium text-gray-900">Total:</span>
                    <span class="text-lg font-bold text-motor-red-600">₲ <?php echo number_format($total, 0, ',', '.'); ?></span>
                </div>
            </div>
        </div>

        <!-- Información de fechas -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <i data-lucide="calendar" class="w-5 h-5 mr-2 text-motor-red-600"></i>
                    Cronología
                </h3>
            </div>
            <div class="px-6 py-6 space-y-4">
                <div class="flex items-center space-x-3">
                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">Ingreso</p>
                        <p class="text-xs text-gray-500"><?php echo date('d/m/Y H:i', strtotime($orden['fecha_ingreso'])); ?></p>
                    </div>
                </div>
                
                <?php if ($orden['fecha_salida']): ?>
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Salida</p>
                                <p class="text-xs text-gray-500"><?php echo date('d/m/Y H:i', strtotime($orden['fecha_salida'])); ?></p>
                            </div>
                        </div>
                    
                        <?php
                        $tiempoTrabajo = strtotime($orden['fecha_salida']) - strtotime($orden['fecha_ingreso']);
                        $dias = floor($tiempoTrabajo / 86400);
                        $horas = floor(($tiempoTrabajo % 86400) / 3600);
                        ?>
                        <div class="bg-green-50 rounded-lg p-3">
                            <p class="text-sm font-medium text-green-800">Tiempo total:</p>
                            <p class="text-lg font-bold text-green-900">
                                <?php echo $dias > 0 ? $dias . 'd ' : ''; ?>    <?php echo $horas; ?>h
                            </p>
                        </div>
                <?php else: ?>
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-yellow-500 rounded-full animate-pulse"></div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">En proceso</p>
                                <p class="text-xs text-gray-500">
                                    <?php
                                    $tiempoTranscurrido = time() - strtotime($orden['fecha_ingreso']);
                                    $horasTranscurridas = floor($tiempoTranscurrido / 3600);
                                    echo $horasTranscurridas . ' horas transcurridas';
                                    ?>
                                </p>
                            </div>
                        </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Personal asignado -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <i data-lucide="users" class="w-5 h-5 mr-2 text-motor-red-600"></i>
                    Personal Asignado
                </h3>
            </div>
            <div class="px-6 py-6">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <span class="text-sm font-medium text-blue-600">
                            <?php echo strtoupper(substr($orden['nombre_personal'], 0, 2)); ?>
                        </span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900"><?php echo $orden['nombre_personal']; ?></p>
                        <p class="text-xs text-gray-500">Responsable</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones rápidas -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <i data-lucide="zap" class="w-5 h-5 mr-2 text-motor-red-600"></i>
                    Acciones Rápidas
                </h3>
            </div>
            <div class="px-6 py-6 space-y-3">
                <button onclick="duplicarOrden()" 
                        class="w-full flex items-center justify-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-motor-red-500 transition-colors duration-200">
                    <i data-lucide="copy" class="w-4 h-4 mr-2"></i>
                    Duplicar Orden
                </button>
                
                <button onclick="crearPresupuesto()" 
                        class="w-full flex items-center justify-center px-3 py-2 border border-purple-300 rounded-lg text-sm font-medium text-purple-700 bg-purple-50 hover:bg-purple-100 focus:outline-none focus:ring-2 focus:ring-purple-500 transition-colors duration-200">
                    <i data-lucide="calculator" class="w-4 h-4 mr-2"></i>
                    Crear Presupuesto
                </button>
                
                <button onclick="verHistorialVehiculo()" 
                        class="w-full flex items-center justify-center px-3 py-2 border border-green-300 rounded-lg text-sm font-medium text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors duration-200">
                    <i data-lucide="history" class="w-4 h-4 mr-2"></i>
                    Historial del Vehículo
                </button>
                
                <?php if ($orden['estado'] != 'cancelado'): ?>
                        <button onclick="cancelarOrden()" 
                                class="w-full flex items-center justify-center px-3 py-2 border border-red-300 rounded-lg text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors duration-200">
                            <i data-lucide="x-circle" class="w-4 h-4 mr-2"></i>
                            Cancelar Orden
                        </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar iconos
    lucide.createIcons();
    
    // Configuración de impresión
    window.addEventListener('beforeprint', function() {
        // Ocultar elementos no necesarios para la impresión
        document.querySelectorAll('.no-print').forEach(el => el.style.display = 'none');
    });
    
    window.addEventListener('afterprint', function() {
        // Restaurar elementos después de la impresión
        document.querySelectorAll('.no-print').forEach(el => el.style.display = '');
    });
});

function completarOrden(id) {
    Swal.fire({
        title: '¿Completar orden?',
        html: `
            <p class="mb-4">¿Está seguro de que desea marcar esta orden como completada?</p>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-left">
                <h4 class="font-medium text-blue-900 mb-2">Al completar la orden:</h4>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>• Se establecerá la fecha de salida</li>
                    <li>• Se podrá proceder a facturar</li>
                    <li>• No se podrán agregar más servicios</li>
                </ul>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, completar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Aquí harías la petición AJAX para completar la orden
            Swal.fire({
                title: 'Completando orden...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Simular proceso
            setTimeout(() => {
                Swal.fire(
                    'Completada',
                    'La orden ha sido completada correctamente.',
                    'success'
                ).then(() => {
                    location.reload();
                });
            }, 2000);
        }
    });
}

function enviarPorEmail() {
    Swal.fire({
        title: 'Enviar orden por email',
        html: `
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email del destinatario:</label>
                    <input id="email-destinatario" type="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-motor-red-500" 
                           placeholder="cliente@ejemplo.com" value="<?php echo $orden['email_cliente'] ?? ''; ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mensaje adicional (opcional):</label>
                    <textarea id="mensaje-adicional" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-motor-red-500" 
                              rows="3" placeholder="Mensaje personalizado para el cliente..."></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Enviar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc2626',
        preConfirm: () => {
            const email = document.getElementById('email-destinatario').value;
            const mensaje = document.getElementById('mensaje-adicional').value;
            
            if (!email) {
                Swal.showValidationMessage('Por favor ingrese un email válido');
                return false;
            }
            
            return { email, mensaje };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Enviando...',
                text: 'Enviando orden por correo electrónico',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Simular envío
            setTimeout(() => {
                Swal.fire(
                    'Enviado',
                    `La orden ha sido enviada a ${result.value.email}`,
                    'success'
                );
            }, 2000);
        }
    });
}

function duplicarOrden() {
    Swal.fire({
        title: '¿Duplicar orden?',
        text: 'Se creará una nueva orden con los mismos servicios y productos',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, duplicar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `index.php?pagina=nuevo/orden_trabajo&duplicar=${<?php echo $orden['id_orden']; ?>}`;
        }
    });
}

function crearPresupuesto() {
    window.location.href = `index.php?pagina=nuevo/presupuesto&desde_orden=${<?php echo $orden['id_orden']; ?>}`;
}

function verHistorialVehiculo() {
    window.location.href = `index.php?pagina=tabla/historial_vehiculo&vehiculo=${<?php echo $orden['id_vehiculo']; ?>}`;
}

function cancelarOrden() {
    Swal.fire({
        title: '¿Cancelar orden?',
        input: 'textarea',
        inputLabel: 'Motivo de cancelación:',
        inputPlaceholder: 'Ingrese el motivo por el cual se cancela la orden...',
        inputValidator: (value) => {
            if (!value) {
                return 'Debe ingresar un motivo para la cancelación';
            }
        },
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Aquí harías la petición para cancelar la orden
            Swal.fire(
                'Cancelada',
                'La orden ha sido cancelada.',
                'success'
            ).then(() => {
                location.reload();
            });
        }
    });
}
</script>

<!-- Estilos adicionales para impresión -->
<style media="print">
    .no-print {
        display: none !important;
    }
    
    body {
        font-size: 12px;
    }
    
    .shadow-sm, .shadow-lg {
        box-shadow: none !important;
    }
    
    .border {
        border: 1px solid #000 !important;
    }
    
    .bg-gray-50 {
        background-color: #f9f9f9 !important;
    }
</style>