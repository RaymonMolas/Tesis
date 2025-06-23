<?php
if (!isset($_SESSION["validarIngreso"])) {
    echo '<script>window.location = "index.php?pagina=login";</script>';
    return;
} elseif ($_SESSION["validarIngreso"] != "ok") {
    echo '<script>window.location = "index.php?pagina=login";</script>';
    return;
}

$presupuestos = PresupuestoControlador::ctrListarPresupuestos();

// Calcular estadísticas
$totalPresupuestos = count($presupuestos);
$presupuestosPendientes = array_filter($presupuestos, function ($p) {
    return $p['estado'] == 'pendiente';
});
$presupuestosAprobados = array_filter($presupuestos, function ($p) {
    return $p['estado'] == 'aprobado';
});
$presupuestosVencidos = array_filter($presupuestos, function ($p) {
    return $p['estado'] == 'vencido' || (strtotime($p['fecha_validez']) < time() && $p['estado'] == 'pendiente');
});
$presupuestosHoy = array_filter($presupuestos, function ($p) {
    return date('Y-m-d', strtotime($p['fecha_emision'])) == date('Y-m-d');
});

// Calcular valor total de presupuestos
$valorTotalPresupuestos = array_sum(array_column($presupuestos, 'total'));
$valorPendiente = array_sum(array_column($presupuestosPendientes, 'total'));
$valorAprobado = array_sum(array_column($presupuestosAprobados, 'total'));
?>

<!-- Encabezado de la página -->
<div class="mb-8">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Gestión de Presupuestos</h1>
            <p class="mt-2 text-sm text-gray-700">
                Administra todos los presupuestos del taller
            </p>
        </div>
        <div class="mt-4 sm:mt-0 sm:flex sm:space-x-3">
            <button type="button" id="btn-filtros" 
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-motor-red-500">
                <i data-lucide="filter" class="w-4 h-4 mr-2"></i>
                Filtros
            </button>
            <a href="index.php?pagina=nuevo/presupuesto" 
               class="inline-flex items-center justify-center rounded-lg bg-motor-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-motor-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-motor-red-600 transition-colors duration-200">
                <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                Nuevo Presupuesto
            </a>
        </div>
    </div>
</div>

<!-- Estadísticas principales -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-6 mb-8">
    <!-- Total Presupuestos -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-shadow duration-300">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-50 text-blue-600">
                <i data-lucide="file-text" class="w-6 h-6"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo $totalPresupuestos; ?></p>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-blue-600">
                <i data-lucide="activity" class="w-4 h-4 mr-1"></i>
                <span>₲ <?php echo number_format($valorTotalPresupuestos, 0, ',', '.'); ?></span>
            </div>
        </div>
    </div>

    <!-- Pendientes -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-shadow duration-300">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-50 text-yellow-600">
                <i data-lucide="clock" class="w-6 h-6"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Pendientes</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo count($presupuestosPendientes); ?></p>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-yellow-600">
                <i data-lucide="trending-up" class="w-4 h-4 mr-1"></i>
                <span>₲ <?php echo number_format($valorPendiente, 0, ',', '.'); ?></span>
            </div>
        </div>
    </div>

    <!-- Aprobados -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-shadow duration-300">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-50 text-green-600">
                <i data-lucide="check-circle" class="w-6 h-6"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Aprobados</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo count($presupuestosAprobados); ?></p>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-green-600">
                <i data-lucide="check" class="w-4 h-4 mr-1"></i>
                <span>₲ <?php echo number_format($valorAprobado, 0, ',', '.'); ?></span>
            </div>
        </div>
    </div>

    <!-- Vencidos -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-shadow duration-300">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-red-50 text-red-600">
                <i data-lucide="alert-triangle" class="w-6 h-6"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Vencidos</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo count($presupuestosVencidos); ?></p>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-red-600">
                <i data-lucide="alert-circle" class="w-4 h-4 mr-1"></i>
                <span>Requieren atención</span>
            </div>
        </div>
    </div>

    <!-- Hoy -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-shadow duration-300">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-motor-red-50 text-motor-red-600">
                <i data-lucide="calendar" class="w-6 h-6"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Hoy</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo count($presupuestosHoy); ?></p>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-motor-red-600">
                <i data-lucide="calendar-check" class="w-4 h-4 mr-1"></i>
                <span>Generados hoy</span>
            </div>
        </div>
    </div>
</div>

<!-- Panel de filtros -->
<div id="panel-filtros" class="hidden bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <!-- Estado -->
            <div>
                <label for="filtro-estado" class="block text-sm font-medium text-gray-700 mb-2">
                    Estado
                </label>
                <select id="filtro-estado" 
                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-motor-red-500 focus:border-motor-red-500">
                    <option value="">Todos</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="aprobado">Aprobado</option>
                    <option value="rechazado">Rechazado</option>
                    <option value="vencido">Vencido</option>
                </select>
            </div>
            
            <!-- Personal -->
            <div>
                <label for="filtro-personal" class="block text-sm font-medium text-gray-700 mb-2">
                    Personal
                </label>
                <select id="filtro-personal" 
                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-motor-red-500 focus:border-motor-red-500">
                    <option value="">Todo el personal</option>
                    <!-- Aquí cargarías dinámicamente el personal -->
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
            
            <!-- Monto mínimo -->
            <div>
                <label for="filtro-monto-min" class="block text-sm font-medium text-gray-700 mb-2">
                    Monto Mínimo
                </label>
                <input type="number" id="filtro-monto-min" 
                       class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-motor-red-500 focus:border-motor-red-500"
                       placeholder="0">
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

<!-- Búsqueda y controles -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
    <div class="p-4">
        <div class="flex items-center justify-between">
            <div class="flex-1 max-w-lg">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="h-5 w-5 text-gray-400"></i>
                    </div>
                    <input type="text" id="busqueda-general" 
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-motor-red-500 focus:border-motor-red-500" 
                           placeholder="Buscar por número, cliente, vehículo...">
                </div>
            </div>
            
            <div class="flex items-center space-x-3 ml-4">
                <!-- Selector de vista -->
                <div class="flex items-center space-x-1 bg-gray-100 rounded-lg p-1">
                    <button id="vista-tabla" 
                            class="px-3 py-1 text-sm font-medium rounded-md bg-white text-gray-900 shadow-sm">
                        <i data-lucide="list" class="w-4 h-4"></i>
                    </button>
                    <button id="vista-cards" 
                            class="px-3 py-1 text-sm font-medium rounded-md text-gray-500 hover:text-gray-900">
                        <i data-lucide="grid" class="w-4 h-4"></i>
                    </button>
                </div>
                
                <!-- Exportar -->
                <div class="relative">
                    <button type="button" id="export-menu-button" 
                            class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:ring-2 focus:ring-motor-red-500">
                        <i data-lucide="download" class="w-4 h-4 mr-2"></i>
                        Exportar
                        <i data-lucide="chevron-down" class="w-4 h-4 ml-2"></i>
                    </button>
                    <div id="export-menu" class="hidden absolute right-0 z-10 mt-2 w-48 rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5">
                        <div class="py-1">
                            <a href="#" onclick="exportarPresupuestos('excel')" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i data-lucide="file-spreadsheet" class="w-4 h-4 mr-3 text-green-500"></i>
                                Excel
                            </a>
                            <a href="#" onclick="exportarPresupuestos('pdf')" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i data-lucide="file-text" class="w-4 h-4 mr-3 text-red-500"></i>
                                PDF
                            </a>
                            <a href="#" onclick="exportarPresupuestos('csv')" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i data-lucide="file-csv" class="w-4 h-4 mr-3 text-blue-500"></i>
                                CSV
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Vista de tabla (por defecto) -->
<div id="contenido-tabla" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200" id="tablaPresupuestos">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <div class="flex items-center space-x-1">
                            <span>Presupuesto</span>
                            <i data-lucide="arrow-up-down" class="w-3 h-3"></i>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Cliente / Vehículo
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <div class="flex items-center space-x-1">
                            <span>Fechas</span>
                            <i data-lucide="arrow-up-down" class="w-3 h-3"></i>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Personal
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Estado
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Total
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Acciones
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($presupuestos as $presupuesto): ?>
                        <?php
                        // Determinar si está vencido
                        $vencido = strtotime($presupuesto['fecha_validez']) < time() && $presupuesto['estado'] == 'pendiente';
                        $diasRestantes = ceil((strtotime($presupuesto['fecha_validez']) - time()) / 86400);
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-200 <?php echo $vencido ? 'bg-red-50' : ''; ?>">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0">
                                        <div class="h-10 w-10 rounded-full bg-motor-red-100 flex items-center justify-center">
                                            <i data-lucide="calculator" class="w-5 h-5 text-motor-red-600"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            #<?php echo str_pad($presupuesto["id_presupuesto"], 6, '0', STR_PAD_LEFT); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo date('d/m/Y H:i', strtotime($presupuesto["fecha_emision"])); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <div class="font-medium"><?php echo $presupuesto["nombre_cliente"]; ?></div>
                                    <div class="text-gray-500">
                                        <?php echo $presupuesto["marca"] . " " . $presupuesto["modelo"]; ?>
                                        <span class="text-xs text-gray-400">(<?php echo $presupuesto["matricula"]; ?>)</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <div class="flex items-center mb-1">
                                        <i data-lucide="calendar" class="w-3 h-3 mr-1 text-gray-400"></i>
                                        <span class="font-medium">Emisión:</span>
                                        <span class="ml-1"><?php echo date('d/m/Y', strtotime($presupuesto["fecha_emision"])); ?></span>
                                    </div>
                                    <div class="flex items-center <?php echo $vencido ? 'text-red-600' : ($diasRestantes <= 7 ? 'text-yellow-600' : 'text-green-600'); ?>">
                                        <i data-lucide="calendar-check" class="w-3 h-3 mr-1"></i>
                                        <span class="font-medium">Validez:</span>
                                        <span class="ml-1"><?php echo date('d/m/Y', strtotime($presupuesto["fecha_validez"])); ?></span>
                                    </div>
                                    <?php if (!$vencido && $presupuesto['estado'] == 'pendiente'): ?>
                                            <div class="text-xs <?php echo $diasRestantes <= 7 ? 'text-yellow-600' : 'text-green-600'; ?>">
                                                <?php echo $diasRestantes > 0 ? $diasRestantes . ' días restantes' : 'Vence hoy'; ?>
                                            </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                        <span class="text-xs font-medium text-blue-600">
                                            <?php echo strtoupper(substr($presupuesto["nombre_personal"], 0, 2)); ?>
                                        </span>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo $presupuesto["nombre_personal"]; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $estadoClases = [
                                    'pendiente' => 'bg-yellow-100 text-yellow-800',
                                    'aprobado' => 'bg-green-100 text-green-800',
                                    'rechazado' => 'bg-red-100 text-red-800'
                                ];
                                $estadoTextos = [
                                    'pendiente' => 'Pendiente',
                                    'aprobado' => 'Aprobado',
                                    'rechazado' => 'Rechazado'
                                ];

                                $estado = $vencido ? 'vencido' : $presupuesto['estado'];
                                if ($vencido) {
                                    $estadoClases['vencido'] = 'bg-red-100 text-red-800';
                                    $estadoTextos['vencido'] = 'Vencido';
                                }
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $estadoClases[$estado]; ?>">
                                    <?php echo $estadoTextos[$estado]; ?>
                                </span>
                            
                                <?php if ($presupuesto['facturado']): ?>
                                        <div class="mt-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                <i data-lucide="file-text" class="w-3 h-3 mr-1"></i>
                                                Facturado
                                            </span>
                                        </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-bold text-gray-900">
                                    ₲ <?php echo number_format($presupuesto["total"], 0, ',', '.'); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <!-- Ver detalles -->
                                    <a href="index.php?pagina=ver/presupuesto&id=<?php echo $presupuesto['id_presupuesto']; ?>" 
                                       class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50 transition-colors duration-200" 
                                       title="Ver detalles">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                
                                    <?php if ($presupuesto['estado'] == 'pendiente' && !$vencido): ?>
                                            <!-- Editar -->
                                            <a href="index.php?pagina=editar/presupuesto&id=<?php echo $presupuesto['id_presupuesto']; ?>" 
                                               class="text-yellow-600 hover:text-yellow-900 p-1 rounded hover:bg-yellow-50 transition-colors duration-200" 
                                               title="Editar">
                                                <i data-lucide="edit" class="w-4 h-4"></i>
                                            </a>
                                    
                                            <!-- Aprobar -->
                                            <button onclick="aprobarPresupuesto(<?php echo $presupuesto['id_presupuesto']; ?>)" 
                                                    class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-50 transition-colors duration-200" 
                                                    title="Aprobar">
                                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                            </button>
                                    
                                            <!-- Rechazar -->
                                            <button onclick="rechazarPresupuesto(<?php echo $presupuesto['id_presupuesto']; ?>)" 
                                                    class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50 transition-colors duration-200" 
                                                    title="Rechazar">
                                                <i data-lucide="x-circle" class="w-4 h-4"></i>
                                            </button>
                                    <?php endif; ?>
                                
                                    <?php if ($presupuesto['estado'] == 'aprobado' && !$presupuesto['facturado']): ?>
                                            <!-- Crear orden -->
                                            <a href="index.php?pagina=nuevo/orden_trabajo&desde_presupuesto=<?php echo $presupuesto['id_presupuesto']; ?>" 
                                               class="text-purple-600 hover:text-purple-900 p-1 rounded hover:bg-purple-50 transition-colors duration-200" 
                                               title="Crear orden de trabajo">
                                                <i data-lucide="wrench" class="w-4 h-4"></i>
                                            </a>
                                    <?php endif; ?>
                                
                                    <!-- PDF -->
                                    <a href="../modelo/pdf/presupuesto_pdf.php?id=<?php echo $presupuesto['id_presupuesto']; ?>" 
                                       target="_blank"
                                       class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50 transition-colors duration-200" 
                                       title="Generar PDF">
                                        <i data-lucide="file-pdf" class="w-4 h-4"></i>
                                    </a>
                                
                                    <!-- Más opciones -->
                                    <div class="relative">
                                        <button onclick="toggleMenuPresupuesto(<?php echo $presupuesto['id_presupuesto']; ?>)" 
                                                class="text-gray-600 hover:text-gray-900 p-1 rounded hover:bg-gray-50 transition-colors duration-200" 
                                                title="Más opciones">
                                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                                        </button>
                                        <div id="menu-presupuesto-<?php echo $presupuesto['id_presupuesto']; ?>" 
                                             class="hidden absolute right-0 z-10 mt-2 w-48 rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5">
                                            <div class="py-1">
                                                <button onclick="duplicarPresupuesto(<?php echo $presupuesto['id_presupuesto']; ?>)" 
                                                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    <i data-lucide="copy" class="w-4 h-4 mr-3"></i>
                                                    Duplicar
                                                </button>
                                                <button onclick="enviarPorEmail(<?php echo $presupuesto['id_presupuesto']; ?>)" 
                                                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    <i data-lucide="mail" class="w-4 h-4 mr-3"></i>
                                                    Enviar por email
                                                </button>
                                                <?php if ($presupuesto['estado'] == 'pendiente'): ?>
                                                        <button onclick="extenderValidez(<?php echo $presupuesto['id_presupuesto']; ?>)" 
                                                                class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                            <i data-lucide="calendar-plus" class="w-4 h-4 mr-3"></i>
                                                            Extender validez
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

<!-- Vista de tarjetas (oculta por defecto) -->
<div id="contenido-cards" class="hidden">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($presupuestos as $presupuesto): ?>
                <?php
                $vencido = strtotime($presupuesto['fecha_validez']) < time() && $presupuesto['estado'] == 'pendiente';
                $diasRestantes = ceil((strtotime($presupuesto['fecha_validez']) - time()) / 86400);
                ?>
                <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow duration-200 <?php echo $vencido ? 'border-red-300 bg-red-50' : ''; ?>">
                    <!-- Header de la tarjeta -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="h-10 w-10 bg-motor-red-100 rounded-full flex items-center justify-center">
                                <i data-lucide="calculator" class="w-5 h-5 text-motor-red-600"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-lg font-medium text-gray-900">
                                    #<?php echo str_pad($presupuesto["id_presupuesto"], 6, '0', STR_PAD_LEFT); ?>
                                </h3>
                                <p class="text-sm text-gray-500">
                                    <?php echo date('d/m/Y', strtotime($presupuesto["fecha_emision"])); ?>
                                </p>
                            </div>
                        </div>
                    
                        <!-- Estado -->
                        <?php
                        $estado = $vencido ? 'vencido' : $presupuesto['estado'];
                        $estadoClases = [
                            'pendiente' => 'bg-yellow-100 text-yellow-800',
                            'aprobado' => 'bg-green-100 text-green-800',
                            'rechazado' => 'bg-red-100 text-red-800',
                            'vencido' => 'bg-red-100 text-red-800'
                        ];
                        $estadoTextos = [
                            'pendiente' => 'Pendiente',
                            'aprobado' => 'Aprobado',
                            'rechazado' => 'Rechazado',
                            'vencido' => 'Vencido'
                        ];
                        ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $estadoClases[$estado]; ?>">
                            <?php echo $estadoTextos[$estado]; ?>
                        </span>
                    </div>
                
                    <!-- Información del presupuesto -->
                    <div class="space-y-3 mb-4">
                        <div class="flex items-center text-sm text-gray-600">
                            <i data-lucide="user" class="w-4 h-4 mr-2"></i>
                            <span class="font-medium"><?php echo $presupuesto["nombre_cliente"]; ?></span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i data-lucide="car" class="w-4 h-4 mr-2"></i>
                            <span><?php echo $presupuesto["marca"] . " " . $presupuesto["modelo"]; ?></span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i data-lucide="user-check" class="w-4 h-4 mr-2"></i>
                            <span><?php echo $presupuesto["nombre_personal"]; ?></span>
                        </div>
                    </div>
                
                    <!-- Fechas y total -->
                    <div class="border-t border-gray-200 pt-4 space-y-2">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-600">Validez:</span>
                            <span class="font-medium <?php echo $vencido ? 'text-red-600' : ($diasRestantes <= 7 ? 'text-yellow-600' : 'text-green-600'); ?>">
                                <?php echo date('d/m/Y', strtotime($presupuesto["fecha_validez"])); ?>
                            </span>
                        </div>
                        <?php if (!$vencido && $presupuesto['estado'] == 'pendiente'): ?>
                                <div class="text-xs <?php echo $diasRestantes <= 7 ? 'text-yellow-600' : 'text-green-600'; ?>">
                                    <?php echo $diasRestantes > 0 ? $diasRestantes . ' días restantes' : 'Vence hoy'; ?>
                                </div>
                        <?php endif; ?>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Total:</span>
                            <span class="text-lg font-bold text-motor-red-600">
                                ₲ <?php echo number_format($presupuesto["total"], 0, ',', '.'); ?>
                            </span>
                        </div>
                    </div>
                
                    <!-- Acciones -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-200 mt-4">
                        <div class="flex space-x-2">
                            <a href="index.php?pagina=ver/presupuesto&id=<?php echo $presupuesto['id_presupuesto']; ?>" 
                               class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50" 
                               title="Ver">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </a>
                            <?php if ($presupuesto['estado'] == 'pendiente' && !$vencido): ?>
                                    <button onclick="aprobarPresupuesto(<?php echo $presupuesto['id_presupuesto']; ?>)" 
                                            class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-50" 
                                            title="Aprobar">
                                        <i data-lucide="check" class="w-4 h-4"></i>
                                    </button>
                            <?php endif; ?>
                            <a href="../modelo/pdf/presupuesto_pdf.php?id=<?php echo $presupuesto['id_presupuesto']; ?>" 
                               target="_blank" 
                               class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50" 
                               title="PDF">
                                <i data-lucide="file-pdf" class="w-4 h-4"></i>
                            </a>
                        </div>
                        <div class="text-xs text-gray-500">
                            ID: <?php echo $presupuesto['id_presupuesto']; ?>
                        </div>
                    </div>
                </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar iconos
    lucide.createIcons();
    
    // Configurar DataTable
    const tabla = $('#tablaPresupuestos').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: false, targets: [6] }
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
    document.getElementById('vista-tabla').addEventListener('click', function() {
        document.getElementById('contenido-tabla').classList.remove('hidden');
        document.getElementById('contenido-cards').classList.add('hidden');
        this.classList.add('bg-white', 'text-gray-900', 'shadow-sm');
        this.classList.remove('text-gray-500');
        document.getElementById('vista-cards').classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
        document.getElementById('vista-cards').classList.add('text-gray-500');
    });
    
    document.getElementById('vista-cards').addEventListener('click', function() {
        document.getElementById('contenido-tabla').classList.add('hidden');
        document.getElementById('contenido-cards').classList.remove('hidden');
        this.classList.add('bg-white', 'text-gray-900', 'shadow-sm');
        this.classList.remove('text-gray-500');
        document.getElementById('vista-tabla').classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
        document.getElementById('vista-tabla').classList.add('text-gray-500');
    });
    
    // Menu exportar
    document.getElementById('export-menu-button').addEventListener('click', function() {
        const menu = document.getElementById('export-menu');
        menu.classList.toggle('hidden');
    });
    
    // Cerrar menús al hacer click fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#export-menu-button')) {
            document.getElementById('export-menu').classList.add('hidden');
        }
        
        // Cerrar menús de presupuestos
        if (!e.target.closest('[onclick^="toggleMenuPresupuesto"]')) {
            document.querySelectorAll('[id^="menu-presupuesto-"]').forEach(menu => {
                menu.classList.add('hidden');
            });
        }
    });
});

// Funciones para acciones
function aprobarPresupuesto(id) {
    Swal.fire({
        title: '¿Aprobar presupuesto?',
        text: '¿Está seguro de que desea aprobar este presupuesto?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, aprobar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire('Aprobado', 'El presupuesto ha sido aprobado correctamente.', 'success')
                .then(() => location.reload());
        }
    });
}

function rechazarPresupuesto(id) {
    Swal.fire({
        title: '¿Rechazar presupuesto?',
        input: 'textarea',
        inputLabel: 'Motivo del rechazo:',
        inputPlaceholder: 'Ingrese el motivo...',
        inputValidator: (value) => {
            if (!value) return 'Debe ingresar un motivo';
        },
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Rechazar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire('Rechazado', 'El presupuesto ha sido rechazado.', 'success')
                .then(() => location.reload());
        }
    });
}

function duplicarPresupuesto(id) {
    window.location.href = `index.php?pagina=nuevo/presupuesto&duplicar=${id}`;
}

function enviarPorEmail(id) {
    Swal.fire({
        title: 'Enviar presupuesto',
        input: 'email',
        inputLabel: 'Email del destinatario:',
        inputPlaceholder: 'ejemplo@correo.com',
        showCancelButton: true,
        confirmButtonText: 'Enviar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            Swal.fire('Enviado', `Presupuesto enviado a ${result.value}`, 'success');
        }
    });
}

function extenderValidez(id) {
    Swal.fire({
        title: 'Extender validez',
        input: 'number',
        inputLabel: 'Días adicionales:',
        inputPlaceholder: '30',
        inputAttributes: {
            min: 1,
            max: 365
        },
        showCancelButton: true,
        confirmButtonText: 'Extender',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            Swal.fire('Validez extendida', `Se agregaron ${result.value} días adicionales`, 'success')
                .then(() => location.reload());
        }
    });
}

function toggleMenuPresupuesto(id) {
    const menu = document.getElementById(`menu-presupuesto-${id}`);
    document.querySelectorAll('[id^="menu-presupuesto-"]').forEach(m => {
        if (m.id !== `menu-presupuesto-${id}`) {
            m.classList.add('hidden');
        }
    });
    menu.classList.toggle('hidden');
}

function exportarPresupuestos(formato) {
    document.getElementById('export-menu').classList.add('hidden');
    Swal.fire({
        title: 'Exportando...',
        text: `Generando archivo ${formato.toUpperCase()}`,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Exportación completa',
            text: `El archivo ${formato.toUpperCase()} ha sido generado correctamente.`,
            showConfirmButton: true
        });
    }, 2000);
}
</script>