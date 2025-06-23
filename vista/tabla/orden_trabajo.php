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

$ordenes = OrdenTrabajoControlador::ctrListarOrdenesTrabajo();

// Estadísticas para el dashboard
$ordenesEnProceso = array_filter($ordenes, function ($orden) {
    return $orden['estado'] == 'en_proceso';
});

$ordenesCompletadas = array_filter($ordenes, function ($orden) {
    return $orden['estado'] == 'completado';
});

$ordenesCanceladas = array_filter($ordenes, function ($orden) {
    return $orden['estado'] == 'cancelado';
});

$ordenesHoy = array_filter($ordenes, function ($orden) {
    return date('Y-m-d', strtotime($orden['fecha_ingreso'])) == date('Y-m-d');
});
?>

<!-- Encabezado de la página -->
<div class="mb-8">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Órdenes de Trabajo</h1>
            <p class="mt-2 text-sm text-gray-700">
                Gestiona todas las órdenes de trabajo del taller
            </p>
        </div>
        <div class="mt-4 sm:mt-0 sm:flex sm:space-x-3">
            <button type="button" id="btn-filtros" 
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-motor-red-500">
                <i data-lucide="filter" class="w-4 h-4 mr-2"></i>
                Filtros
            </button>
            <a href="index.php?pagina=nuevo/orden_trabajo" 
               class="inline-flex items-center justify-center rounded-lg bg-motor-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-motor-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-motor-red-600 transition-colors duration-200">
                <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                Nueva Orden
            </a>
        </div>
    </div>
</div>

<!-- Estadísticas principales -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
    <!-- En Proceso -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-shadow duration-300">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-50 text-yellow-600">
                <i data-lucide="clock" class="w-6 h-6"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">En Proceso</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo count($ordenesEnProceso); ?></p>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-yellow-600">
                <i data-lucide="trending-up" class="w-4 h-4 mr-1"></i>
                <span>Requieren atención</span>
            </div>
        </div>
    </div>

    <!-- Completadas -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-shadow duration-300">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-50 text-green-600">
                <i data-lucide="check-circle" class="w-6 h-6"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Completadas</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo count($ordenesCompletadas); ?></p>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-green-600">
                <i data-lucide="check" class="w-4 h-4 mr-1"></i>
                <span>Listas para facturar</span>
            </div>
        </div>
    </div>

    <!-- Hoy -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-shadow duration-300">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-50 text-blue-600">
                <i data-lucide="calendar" class="w-6 h-6"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Hoy</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo count($ordenesHoy); ?></p>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-blue-600">
                <i data-lucide="calendar-check" class="w-4 h-4 mr-1"></i>
                <span>Ingresadas hoy</span>
            </div>
        </div>
    </div>

    <!-- Total -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-shadow duration-300">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-motor-red-50 text-motor-red-600">
                <i data-lucide="wrench" class="w-6 h-6"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Órdenes</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo count($ordenes); ?></p>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-motor-red-600">
                <i data-lucide="activity" class="w-4 h-4 mr-1"></i>
                <span>Historial completo</span>
            </div>
        </div>
    </div>
</div>

<!-- Panel de filtros -->
<div id="panel-filtros" class="hidden bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Estado -->
            <div>
                <label for="filtro-estado" class="block text-sm font-medium text-gray-700 mb-2">
                    Estado
                </label>
                <select id="filtro-estado" 
                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-motor-red-500 focus:border-motor-red-500">
                    <option value="">Todos los estados</option>
                    <option value="en_proceso">En Proceso</option>
                    <option value="completado">Completado</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </div>
            
            <!-- Personal -->
            <div>
                <label for="filtro-personal" class="block text-sm font-medium text-gray-700 mb-2">
                    Personal Asignado
                </label>
                <select id="filtro-personal" 
                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-motor-red-500 focus:border-motor-red-500">
                    <option value="">Todo el personal</option>
                    <!-- Aquí cargarías dinámicamente el personal -->
                    <option value="1">Juan Pérez</option>
                    <option value="2">María González</option>
                    <option value="3">Carlos Rodríguez</option>
                </select>
            </div>
            
            <!-- Fecha desde -->
            <div>
                <label for="filtro-fecha-desde" class="block text-sm font-medium text-gray-700 mb-2">
                    Desde
                </label>
                <input type="date" id="filtro-fecha-desde" 
                       class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-motor-red-500 focus:border-motor-red-500">
            </div>
            
            <!-- Fecha hasta -->
            <div>
                <label for="filtro-fecha-hasta" class="block text-sm font-medium text-gray-700 mb-2">
                    Hasta
                </label>
                <input type="date" id="filtro-fecha-hasta" 
                       class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-motor-red-500 focus:border-motor-red-500">
            </div>
            
            <!-- Acciones -->
            <div class="flex items-end space-x-2">
                <button id="aplicar-filtros" 
                        class="flex-1 px-3 py-2 bg-motor-red-600 text-white rounded-lg hover:bg-motor-red-700 focus:ring-2 focus:ring-motor-red-500 transition-colors duration-200">
                    Aplicar
                </button>
                <button id="limpiar-filtros" 
                        class="px-3 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-motor-red-500 transition-colors duration-200">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Búsqueda rápida -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
    <div class="p-4">
        <div class="flex items-center space-x-4">
            <div class="flex-1">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="h-5 w-5 text-gray-400"></i>
                    </div>
                    <input type="text" id="busqueda-general" 
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-motor-red-500 focus:border-motor-red-500" 
                           placeholder="Buscar por orden, vehículo, cliente o personal...">
                </div>
            </div>
            
            <!-- Botones de vista -->
            <div class="flex items-center space-x-2">
                <button id="vista-lista" 
                        class="p-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-motor-red-500 transition-colors duration-200 active">
                    <i data-lucide="list" class="w-4 h-4"></i>
                </button>
                <button id="vista-kanban" 
                        class="p-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-motor-red-500 transition-colors duration-200">
                    <i data-lucide="columns" class="w-4 h-4"></i>
                </button>
            </div>
            
            <!-- Exportar -->
            <div class="relative">
                <button type="button" id="export-menu-button" 
                        class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:ring-2 focus:ring-motor-red-500">
                    <i data-lucide="download" class="w-4 h-4 mr-2"></i>
                    Exportar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Vista de lista (por defecto) -->
<div id="vista-tabla" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200" id="tablaOrdenes">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <div class="flex items-center space-x-1">
                            <span>Orden</span>
                            <i data-lucide="arrow-up-down" class="w-3 h-3"></i>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Vehículo / Cliente
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Personal Asignado
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <div class="flex items-center space-x-1">
                            <span>Fechas</span>
                            <i data-lucide="arrow-up-down" class="w-3 h-3"></i>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Estado
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Acciones
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($ordenes as $orden): ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0">
                                        <div class="h-10 w-10 rounded-full bg-motor-red-100 flex items-center justify-center">
                                            <i data-lucide="wrench" class="w-5 h-5 text-motor-red-600"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            Orden #<?php echo str_pad($orden["id_orden"], 6, '0', STR_PAD_LEFT); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php if ($orden['kilometraje_actual']): ?>
                                                    <?php echo number_format($orden['kilometraje_actual']); ?> km
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <div class="font-medium"><?php echo $orden["marca"] . " " . $orden["modelo"]; ?></div>
                                    <div class="text-gray-500"><?php echo $orden["matricula"]; ?></div>
                                    <div class="text-xs text-gray-400 mt-1">
                                        <i data-lucide="user" class="w-3 h-3 inline mr-1"></i>
                                        <?php echo $orden["nombre_cliente"]; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                        <span class="text-xs font-medium text-blue-600">
                                            <?php echo strtoupper(substr($orden["nombre_personal"], 0, 2)); ?>
                                        </span>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo $orden["nombre_personal"]; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <div class="flex items-center mb-1">
                                        <i data-lucide="calendar" class="w-3 h-3 mr-1 text-gray-400"></i>
                                        <span class="font-medium">Ingreso:</span>
                                        <span class="ml-1"><?php echo date('d/m/Y', strtotime($orden["fecha_ingreso"])); ?></span>
                                    </div>
                                    <?php if ($orden["fecha_salida"]): ?>
                                            <div class="flex items-center text-green-600">
                                                <i data-lucide="calendar-check" class="w-3 h-3 mr-1"></i>
                                                <span class="font-medium">Salida:</span>
                                                <span class="ml-1"><?php echo date('d/m/Y', strtotime($orden["fecha_salida"])); ?></span>
                                            </div>
                                    <?php else: ?>
                                            <div class="flex items-center text-yellow-600">
                                                <i data-lucide="clock" class="w-3 h-3 mr-1"></i>
                                                <span class="text-xs">En proceso</span>
                                            </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $estadoClases = [
                                    'en_proceso' => 'bg-yellow-100 text-yellow-800',
                                    'completado' => 'bg-green-100 text-green-800',
                                    'cancelado' => 'bg-red-100 text-red-800'
                                ];
                                $estadoTextos = [
                                    'en_proceso' => 'En Proceso',
                                    'completado' => 'Completado',
                                    'cancelado' => 'Cancelado'
                                ];
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $estadoClases[$orden['estado']]; ?>">
                                    <?php echo $estadoTextos[$orden['estado']]; ?>
                                </span>
                            
                                <?php if (!$orden['facturado'] && $orden['estado'] == 'completado'): ?>
                                        <div class="mt-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                <i data-lucide="file-text" class="w-3 h-3 mr-1"></i>
                                                Sin facturar
                                            </span>
                                        </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <!-- Ver detalles -->
                                    <a href="index.php?pagina=ver/orden_trabajo&id=<?php echo $orden['id_orden']; ?>" 
                                       class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50 transition-colors duration-200" 
                                       title="Ver detalles">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                
                                    <?php if ($orden['estado'] == 'en_proceso'): ?>
                                            <!-- Editar -->
                                            <a href="index.php?pagina=editar/orden_trabajo&id=<?php echo $orden['id_orden']; ?>" 
                                               class="text-yellow-600 hover:text-yellow-900 p-1 rounded hover:bg-yellow-50 transition-colors duration-200" 
                                               title="Editar">
                                                <i data-lucide="edit" class="w-4 h-4"></i>
                                            </a>
                                    
                                            <!-- Completar -->
                                            <button onclick="completarOrden(<?php echo $orden['id_orden']; ?>)" 
                                                    class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-50 transition-colors duration-200" 
                                                    title="Completar orden">
                                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                            </button>
                                    <?php endif; ?>
                                
                                    <?php if ($orden['estado'] == 'completado' && !$orden['facturado']): ?>
                                            <!-- Facturar -->
                                            <a href="index.php?pagina=nuevo/factura&desde_orden=<?php echo $orden['id_orden']; ?>" 
                                               class="text-purple-600 hover:text-purple-900 p-1 rounded hover:bg-purple-50 transition-colors duration-200" 
                                               title="Facturar">
                                                <i data-lucide="file-text" class="w-4 h-4"></i>
                                            </a>
                                    <?php endif; ?>
                                
                                    <!-- PDF -->
                                    <a href="../modelo/pdf/orden_trabajo_pdf.php?id=<?php echo $orden['id_orden']; ?>" 
                                       target="_blank"
                                       class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50 transition-colors duration-200" 
                                       title="Generar PDF">
                                        <i data-lucide="file-pdf" class="w-4 h-4"></i>
                                    </a>
                                
                                    <!-- Más opciones -->
                                    <div class="relative">
                                        <button onclick="toggleMenuOrden(<?php echo $orden['id_orden']; ?>)" 
                                                class="text-gray-600 hover:text-gray-900 p-1 rounded hover:bg-gray-50 transition-colors duration-200" 
                                                title="Más opciones">
                                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                                        </button>
                                        <div id="menu-orden-<?php echo $orden['id_orden']; ?>" 
                                             class="hidden absolute right-0 z-10 mt-2 w-48 rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5">
                                            <div class="py-1">
                                                <button onclick="duplicarOrden(<?php echo $orden['id_orden']; ?>)" 
                                                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    <i data-lucide="copy" class="w-4 h-4 mr-3"></i>
                                                    Duplicar
                                                </button>
                                                <button onclick="enviarEmail(<?php echo $orden['id_orden']; ?>)" 
                                                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    <i data-lucide="mail" class="w-4 h-4 mr-3"></i>
                                                    Enviar por email
                                                </button>
                                                <?php if ($orden['estado'] != 'cancelado'): ?>
                                                        <button onclick="cancelarOrden(<?php echo $orden['id_orden']; ?>)" 
                                                                class="flex items-center w-full px-4 py-2 text-sm text-red-700 hover:bg-red-50">
                                                            <i data-lucide="x-circle" class="w-4 h-4 mr-3"></i>
                                                            Cancelar orden
                                                        </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Vista Kanban (oculta por defecto) -->
<div id="vista-kanban" class="hidden">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Columna En Proceso -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <div class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></div>
                    En Proceso
                    <span class="ml-2 bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">
                        <?php echo count($ordenesEnProceso); ?>
                    </span>
                </h3>
            </div>
            <div class="p-4 space-y-4 max-h-96 overflow-y-auto">
                <?php foreach ($ordenesEnProceso as $orden): ?>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-900">
                                    #<?php echo str_pad($orden["id_orden"], 6, '0', STR_PAD_LEFT); ?>
                                </span>
                                <span class="text-xs text-gray-500">
                                    <?php echo date('d/m', strtotime($orden["fecha_ingreso"])); ?>
                                </span>
                            </div>
                            <div class="text-sm text-gray-700 mb-2">
                                <div class="font-medium"><?php echo $orden["marca"] . " " . $orden["modelo"]; ?></div>
                                <div class="text-xs text-gray-500"><?php echo $orden["nombre_cliente"]; ?></div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="text-xs text-gray-500">
                                    <i data-lucide="user" class="w-3 h-3 inline mr-1"></i>
                                    <?php echo $orden["nombre_personal"]; ?>
                                </div>
                                <div class="flex space-x-1">
                                    <button onclick="completarOrden(<?php echo $orden['id_orden']; ?>)" 
                                            class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-50" 
                                            title="Completar">
                                        <i data-lucide="check" class="w-3 h-3"></i>
                                    </button>
                                    <a href="index.php?pagina=ver/orden_trabajo&id=<?php echo $orden['id_orden']; ?>" 
                                       class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50" 
                                       title="Ver">
                                        <i data-lucide="eye" class="w-3 h-3"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Columna Completadas -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                    Completadas
                    <span class="ml-2 bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                        <?php echo count($ordenesCompletadas); ?>
                    </span>
                </h3>
            </div>
            <div class="p-4 space-y-4 max-h-96 overflow-y-auto">
                <?php foreach ($ordenesCompletadas as $orden): ?>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-900">
                                    #<?php echo str_pad($orden["id_orden"], 6, '0', STR_PAD_LEFT); ?>
                                </span>
                                <span class="text-xs text-gray-500">
                                    <?php echo date('d/m', strtotime($orden["fecha_salida"])); ?>
                                </span>
                            </div>
                            <div class="text-sm text-gray-700 mb-2">
                                <div class="font-medium"><?php echo $orden["marca"] . " " . $orden["modelo"]; ?></div>
                                <div class="text-xs text-gray-500"><?php echo $orden["nombre_cliente"]; ?></div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="text-xs text-gray-500">
                                    <i data-lucide="user" class="w-3 h-3 inline mr-1"></i>
                                    <?php echo $orden["nombre_personal"]; ?>
                                </div>
                                <div class="flex space-x-1">
                                    <?php if (!$orden['facturado']): ?>
                                            <a href="index.php?pagina=nuevo/factura&desde_orden=<?php echo $orden['id_orden']; ?>" 
                                               class="text-purple-600 hover:text-purple-900 p-1 rounded hover:bg-purple-50" 
                                               title="Facturar">
                                                <i data-lucide="file-text" class="w-3 h-3"></i>
                                            </a>
                                    <?php endif; ?>
                                    <a href="index.php?pagina=ver/orden_trabajo&id=<?php echo $orden['id_orden']; ?>" 
                                       class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50" 
                                       title="Ver">
                                        <i data-lucide="eye" class="w-3 h-3"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Columna Canceladas -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                    Canceladas
                    <span class="ml-2 bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">
                        <?php echo count($ordenesCanceladas); ?>
                    </span>
                </h3>
            </div>
            <div class="p-4 space-y-4 max-h-96 overflow-y-auto">
                <?php foreach ($ordenesCanceladas as $orden): ?>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-900">
                                    #<?php echo str_pad($orden["id_orden"], 6, '0', STR_PAD_LEFT); ?>
                                </span>
                                <span class="text-xs text-gray-500">
                                    <?php echo date('d/m', strtotime($orden["fecha_ingreso"])); ?>
                                </span>
                            </div>
                            <div class="text-sm text-gray-700 mb-2">
                                <div class="font-medium"><?php echo $orden["marca"] . " " . $orden["modelo"]; ?></div>
                                <div class="text-xs text-gray-500"><?php echo $orden["nombre_cliente"]; ?></div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="text-xs text-gray-500">
                                    <i data-lucide="user" class="w-3 h-3 inline mr-1"></i>
                                    <?php echo $orden["nombre_personal"]; ?>
                                </div>
                                <div class="flex space-x-1">
                                    <a href="index.php?pagina=ver/orden_trabajo&id=<?php echo $orden['id_orden']; ?>" 
                                       class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50" 
                                       title="Ver">
                                        <i data-lucide="eye" class="w-3 h-3"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar iconos
    lucide.createIcons();
    
    // Configurar DataTable
    const tabla = $('#tablaOrdenes').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[0, 'desc']], // Ordenar por número de orden descendente
        columnDefs: [
            { orderable: false, targets: [5] } // Columna de acciones no ordenable
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        }
    });
    
    // Toggle filtros
    document.getElementById('btn-filtros').addEventListener('click', function() {
        const panel = document.getElementById('panel-filtros');
        panel.classList.toggle('hidden');
    });
    
    // Búsqueda general
    document.getElementById('busqueda-general').addEventListener('keyup', function() {
        tabla.search(this.value).draw();
    });
    
    // Cambio de vista
    document.getElementById('vista-lista').addEventListener('click', function() {
        document.getElementById('vista-tabla').classList.remove('hidden');
        document.getElementById('vista-kanban').classList.add('hidden');
        this.classList.add('bg-motor-red-100', 'text-motor-red-600');
        document.getElementById('vista-kanban').previousElementSibling.classList.remove('bg-motor-red-100', 'text-motor-red-600');
    });
    
    document.getElementById('vista-kanban').addEventListener('click', function() {
        document.getElementById('vista-tabla').classList.add('hidden');
        document.getElementById('vista-kanban').classList.remove('hidden');
        this.classList.add('bg-motor-red-100', 'text-motor-red-600');
        document.getElementById('vista-lista').classList.remove('bg-motor-red-100', 'text-motor-red-600');
    });
    
    // Aplicar filtros
    document.getElementById('aplicar-filtros').addEventListener('click', function() {
        // Aquí implementarías la lógica de filtrado
        console.log('Aplicando filtros...');
    });
    
    // Limpiar filtros
    document.getElementById('limpiar-filtros').addEventListener('click', function() {
        document.getElementById('filtro-estado').value = '';
        document.getElementById('filtro-personal').value = '';
        document.getElementById('filtro-fecha-desde').value = '';
        document.getElementById('filtro-fecha-hasta').value = '';
        tabla.search('').columns().search('').draw();
    });
});

// Funciones para acciones
function completarOrden(id) {
    Swal.fire({
        title: '¿Completar orden?',
        text: '¿Está seguro de que desea marcar esta orden como completada?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, completar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Aquí harías la petición para completar la orden
            Swal.fire(
                'Completada',
                'La orden ha sido completada correctamente.',
                'success'
            ).then(() => {
                location.reload();
            });
        }
    });
}

function cancelarOrden(id) {
    Swal.fire({
        title: '¿Cancelar orden?',
        text: 'Esta acción no se puede deshacer. ¿Está seguro?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No'
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

function duplicarOrden(id) {
    Swal.fire({
        title: 'Duplicando orden...',
        text: 'Creando una nueva orden basada en la actual',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Simular duplicación
    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Orden duplicada',
            text: 'Se ha creado una nueva orden basada en la seleccionada.',
            showConfirmButton: true
        });
    }, 2000);
}

function enviarEmail(id) {
    Swal.fire({
        title: 'Enviar por email',
        input: 'email',
        inputLabel: 'Dirección de correo electrónico',
        inputPlaceholder: 'ejemplo@correo.com',
        showCancelButton: true,
        confirmButtonText: 'Enviar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed && result.value) {
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
                    `La orden ha sido enviada a ${result.value}`,
                    'success'
                );
            }, 2000);
        }
    });
}

function toggleMenuOrden(id) {
    const menu = document.getElementById(`menu-orden-${id}`);
    
    // Cerrar todos los otros menús
    document.querySelectorAll('[id^="menu-orden-"]').forEach(m => {
        if (m.id !== `menu-orden-${id}`) {
            m.classList.add('hidden');
        }
    });
    
    menu.classList.toggle('hidden');
}

// Cerrar menús al hacer click fuera
document.addEventListener('click', function(e) {
    if (!e.target.closest('[onclick^="toggleMenuOrden"]')) {
        document.querySelectorAll('[id^="menu-orden-"]').forEach(menu => {
            menu.classList.add('hidden');
        });
    }
});
</script>