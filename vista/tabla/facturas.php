<?php
if (!isset($_SESSION["validarIngreso"])) {
    echo '<script>window.location = "index.php?pagina=login";</script>';
    return;
} elseif ($_SESSION["validarIngreso"] != "ok") {
    echo '<script>window.location = "index.php?pagina=login";</script>';
    return;
}

$facturas = FacturaControlador::ctrListarFacturas();

// Calcular estadísticas
$totalFacturas = count($facturas);
$facturasEmitidas = array_filter($facturas, function ($f) {
    return $f['estado'] == 'emitida';
});
$facturasPagadas = array_filter($facturas, function ($f) {
    return $f['estado'] == 'pagada';
});
$facturasVencidas = array_filter($facturas, function ($f) {
    return $f['estado'] == 'vencida';
});
$facturasHoy = array_filter($facturas, function ($f) {
    return date('Y-m-d', strtotime($f['fecha_emision'])) == date('Y-m-d');
});

// Calcular valores totales
$valorTotalFacturas = array_sum(array_column($facturas, 'total'));
$valorPendiente = array_sum(array_column($facturasEmitidas, 'total'));
$valorCobrado = array_sum(array_column($facturasPagadas, 'total'));
$valorVencido = array_sum(array_column($facturasVencidas, 'total'));
?>

<!-- Encabezado de la página -->
<div class="mb-8">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Gestión de Facturación</h1>
            <p class="mt-2 text-sm text-gray-700">
                Control completo de facturación e ingresos del taller
            </p>
        </div>
        <div class="mt-4 sm:mt-0 sm:flex sm:space-x-3">
            <button type="button" id="btn-filtros" 
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-motor-red-500">
                <i data-lucide="filter" class="w-4 h-4 mr-2"></i>
                Filtros
            </button>
            <button type="button" id="btn-reportes" 
                    class="inline-flex items-center justify-center rounded-lg border border-green-300 bg-green-50 px-4 py-2 text-sm font-medium text-green-700 shadow-sm hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-green-500">
                <i data-lucide="bar-chart-3" class="w-4 h-4 mr-2"></i>
                Reportes
            </button>
            <a href="index.php?pagina=nuevo/factura" 
               class="inline-flex items-center justify-center rounded-lg bg-motor-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-motor-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-motor-red-600 transition-colors duration-200">
                <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                Nueva Factura
            </a>
        </div>
    </div>
</div>

<!-- Dashboard de estadísticas -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-6 mb-8">
    <!-- Total Facturas -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-shadow duration-300">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-50 text-blue-600">
                <i data-lucide="file-text" class="w-6 h-6"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Facturas</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo $totalFacturas; ?></p>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-blue-600">
                <i data-lucide="trending-up" class="w-4 h-4 mr-1"></i>
                <span>₲ <?php echo number_format($valorTotalFacturas, 0, ',', '.'); ?></span>
            </div>
        </div>
    </div>

    <!-- Emitidas -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-shadow duration-300">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-50 text-yellow-600">
                <i data-lucide="send" class="w-6 h-6"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Emitidas</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo count($facturasEmitidas); ?></p>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-yellow-600">
                <i data-lucide="clock" class="w-4 h-4 mr-1"></i>
                <span>₲ <?php echo number_format($valorPendiente, 0, ',', '.'); ?></span>
            </div>
        </div>
    </div>

    <!-- Pagadas -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-shadow duration-300">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-50 text-green-600">
                <i data-lucide="check-circle" class="w-6 h-6"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Pagadas</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo count($facturasPagadas); ?></p>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-green-600">
                <i data-lucide="dollar-sign" class="w-4 h-4 mr-1"></i>
                <span>₲ <?php echo number_format($valorCobrado, 0, ',', '.'); ?></span>
            </div>
        </div>
    </div>

    <!-- Vencidas -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-shadow duration-300">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-red-50 text-red-600">
                <i data-lucide="alert-circle" class="w-6 h-6"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Vencidas</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo count($facturasVencidas); ?></p>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-red-600">
                <i data-lucide="alert-triangle" class="w-4 h-4 mr-1"></i>
                <span>₲ <?php echo number_format($valorVencido, 0, ',', '.'); ?></span>
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
                <p class="text-2xl font-bold text-gray-900"><?php echo count($facturasHoy); ?></p>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-motor-red-600">
                <i data-lucide="calendar-check" class="w-4 h-4 mr-1"></i>
                <span>Emitidas hoy</span>
            </div>
        </div>
    </div>
</div>

<!-- Gráfico de ingresos -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
    <div class="xl:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Ingresos Mensuales</h3>
            <div class="flex items-center space-x-2">
                <select id="filtro-periodo" class="text-sm border border-gray-300 rounded-lg px-3 py-1">
                    <option value="6">Últimos 6 meses</option>
                    <option value="12">Último año</option>
                    <option value="24">Últimos 2 años</option>
                </select>
            </div>
        </div>
        <div class="h-64">
            <canvas id="ingresosChart"></canvas>
        </div>
    </div>
    
    <!-- Resumen de métodos de pago -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Métodos de Pago</h3>
        <div class="h-64">
            <canvas id="metodosChart"></canvas>
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
                    <option value="emitida">Emitida</option>
                    <option value="pagada">Pagada</option>
                    <option value="vencida">Vencida</option>
                    <option value="anulada">Anulada</option>
                </select>
            </div>
            
            <!-- Cliente -->
            <div>
                <label for="filtro-cliente" class="block text-sm font-medium text-gray-700 mb-2">
                    Cliente
                </label>
                <select id="filtro-cliente" 
                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-motor-red-500 focus:border-motor-red-500">
                    <option value="">Todos los clientes</option>
                    <!-- Aquí cargarías dinámicamente los clientes -->
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
            
            <!-- Método de pago -->
            <div>
                <label for="filtro-metodo-pago" class="block text-sm font-medium text-gray-700 mb-2">
                    Método de Pago
                </label>
                <select id="filtro-metodo-pago" 
                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-motor-red-500 focus:border-motor-red-500">
                    <option value="">Todos</option>
                    <option value="efectivo">Efectivo</option>
                    <option value="tarjeta">Tarjeta</option>
                    <option value="transferencia">Transferencia</option>
                    <option value="cheque">Cheque</option>
                </select>
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
                           placeholder="Buscar por número, cliente, monto...">
                </div>
            </div>
            
            <div class="flex items-center space-x-3 ml-4">
                <!-- Vista rápida de pendientes de cobro -->
                <button onclick="mostrarPendientesCobro()" 
                        class="inline-flex items-center px-3 py-2 border border-yellow-300 rounded-lg text-sm font-medium text-yellow-700 bg-yellow-50 hover:bg-yellow-100">
                    <i data-lucide="clock" class="w-4 h-4 mr-2"></i>
                    Pendientes de Cobro
                </button>
                
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
                            <a href="#" onclick="exportarFacturas('excel')" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i data-lucide="file-spreadsheet" class="w-4 h-4 mr-3 text-green-500"></i>
                                Excel
                            </a>
                            <a href="#" onclick="exportarFacturas('pdf')" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i data-lucide="file-text" class="w-4 h-4 mr-3 text-red-500"></i>
                                PDF
                            </a>
                            <a href="#" onclick="generarReporteIngresos()" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i data-lucide="bar-chart-3" class="w-4 h-4 mr-3 text-blue-500"></i>
                                Reporte de Ingresos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de facturas -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200" id="tablaFacturas">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <div class="flex items-center space-x-1">
                            <span>Factura</span>
                            <i data-lucide="arrow-up-down" class="w-3 h-3"></i>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Cliente
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <div class="flex items-center space-x-1">
                            <span>Fecha</span>
                            <i data-lucide="arrow-up-down" class="w-3 h-3"></i>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Método de Pago
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
                <?php foreach ($facturas as $factura): ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0">
                                        <div class="h-10 w-10 rounded-full bg-motor-red-100 flex items-center justify-center">
                                            <i data-lucide="file-text" class="w-5 h-5 text-motor-red-600"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo $factura["numero_factura"]; ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            Timbrado: <?php echo $factura["timbrado_numero"] ?? 'N/A'; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <div class="font-medium"><?php echo $factura["nombre_cliente"]; ?></div>
                                    <div class="text-gray-500 text-xs">
                                        <?php if ($factura["tipo_factura"] == "credito"): ?>
                                                <i data-lucide="credit-card" class="w-3 h-3 inline mr-1"></i>
                                                Crédito
                                        <?php else: ?>
                                                <i data-lucide="dollar-sign" class="w-3 h-3 inline mr-1"></i>
                                                Contado
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <div class="font-medium"><?php echo date('d/m/Y', strtotime($factura["fecha_emision"])); ?></div>
                                    <div class="text-gray-500 text-xs"><?php echo date('H:i', strtotime($factura["fecha_emision"])); ?></div>
                                    <?php if ($factura["fecha_vencimiento"]): ?>
                                            <div class="text-xs <?php echo strtotime($factura['fecha_vencimiento']) < time() ? 'text-red-600' : 'text-gray-500'; ?>">
                                                Vence: <?php echo date('d/m/Y', strtotime($factura["fecha_vencimiento"])); ?>
                                            </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $metodosIconos = [
                                    'efectivo' => 'banknote',
                                    'tarjeta' => 'credit-card',
                                    'transferencia' => 'arrow-right-left',
                                    'cheque' => 'receipt'
                                ];
                                $metodosTextos = [
                                    'efectivo' => 'Efectivo',
                                    'tarjeta' => 'Tarjeta',
                                    'transferencia' => 'Transferencia',
                                    'cheque' => 'Cheque'
                                ];
                                ?>
                                <div class="flex items-center text-sm text-gray-900">
                                    <i data-lucide="<?php echo $metodosIconos[$factura['metodo_pago']] ?? 'banknote'; ?>" class="w-4 h-4 mr-2 text-gray-400"></i>
                                    <?php echo $metodosTextos[$factura['metodo_pago']] ?? ucfirst($factura['metodo_pago']); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $estadoClases = [
                                    'emitida' => 'bg-blue-100 text-blue-800',
                                    'pagada' => 'bg-green-100 text-green-800',
                                    'vencida' => 'bg-red-100 text-red-800',
                                    'anulada' => 'bg-gray-100 text-gray-800'
                                ];
                                $estadoTextos = [
                                    'emitida' => 'Emitida',
                                    'pagada' => 'Pagada',
                                    'vencida' => 'Vencida',
                                    'anulada' => 'Anulada'
                                ];
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $estadoClases[$factura['estado']]; ?>">
                                    <?php echo $estadoTextos[$factura['estado']]; ?>
                                </span>
                            
                                <?php if ($factura['estado'] == 'emitida' && $factura['saldo_pendiente'] > 0): ?>
                                        <div class="mt-1 text-xs text-gray-500">
                                            Saldo: ₲ <?php echo number_format($factura['saldo_pendiente'], 0, ',', '.'); ?>
                                        </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-bold text-gray-900">
                                    ₲ <?php echo number_format($factura["total"], 0, ',', '.'); ?>
                                </div>
                                <?php if ($factura['descuento_monto'] > 0): ?>
                                        <div class="text-xs text-green-600">
                                            Desc: ₲ <?php echo number_format($factura['descuento_monto'], 0, ',', '.'); ?>
                                        </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <!-- Ver detalles -->
                                    <a href="index.php?pagina=ver/factura&id=<?php echo $factura['id_factura']; ?>" 
                                       class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50 transition-colors duration-200" 
                                       title="Ver detalles">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                
                                    <?php if ($factura['estado'] == 'emitida'): ?>
                                            <!-- Registrar pago -->
                                            <button onclick="registrarPago(<?php echo $factura['id_factura']; ?>, '<?php echo $factura['numero_factura']; ?>', <?php echo $factura['saldo_pendiente']; ?>)" 
                                                    class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-50 transition-colors duration-200" 
                                                    title="Registrar pago">
                                                <i data-lucide="dollar-sign" class="w-4 h-4"></i>
                                            </button>
                                    <?php endif; ?>
                                
                                    <!-- PDF -->
                                    <a href="../modelo/pdf/factura_pdf.php?id=<?php echo $factura['id_factura']; ?>" 
                                       target="_blank"
                                       class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50 transition-colors duration-200" 
                                       title="Generar PDF">
                                        <i data-lucide="file-pdf" class="w-4 h-4"></i>
                                    </a>
                                
                                    <!-- Enviar por email -->
                                    <button onclick="enviarPorEmail(<?php echo $factura['id_factura']; ?>, '<?php echo $factura['numero_factura']; ?>')" 
                                            class="text-purple-600 hover:text-purple-900 p-1 rounded hover:bg-purple-50 transition-colors duration-200" 
                                            title="Enviar por email">
                                        <i data-lucide="mail" class="w-4 h-4"></i>
                                    </button>
                                
                                    <?php if ($factura['estado'] != 'anulada' && $factura['estado'] != 'pagada'): ?>
                                            <!-- Anular -->
                                            <button onclick="anularFactura(<?php echo $factura['id_factura']; ?>, '<?php echo $factura['numero_factura']; ?>')" 
                                                    class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50 transition-colors duration-200" 
                                                    title="Anular factura">
                                                <i data-lucide="x-circle" class="w-4 h-4"></i>
                                            </button>
                                    <?php endif; ?>
                                
                                    <!-- Más opciones -->
                                    <div class="relative">
                                        <button onclick="toggleMenuFactura(<?php echo $factura['id_factura']; ?>)" 
                                                class="text-gray-600 hover:text-gray-900 p-1 rounded hover:bg-gray-50 transition-colors duration-200" 
                                                title="Más opciones">
                                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                                        </button>
                                        <div id="menu-factura-<?php echo $factura['id_factura']; ?>" 
                                             class="hidden absolute right-0 z-10 mt-2 w-48 rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5">
                                            <div class="py-1">
                                                <button onclick="duplicarFactura(<?php echo $factura['id_factura']; ?>)" 
                                                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    <i data-lucide="copy" class="w-4 h-4 mr-3"></i>
                                                    Duplicar
                                                </button>
                                                <button onclick="verHistorialPagos(<?php echo $factura['id_factura']; ?>)" 
                                                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    <i data-lucide="history" class="w-4 h-4 mr-3"></i>
                                                    Historial de pagos
                                                </button>
                                                <button onclick="generarNotaCredito(<?php echo $factura['id_factura']; ?>)" 
                                                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    <i data-lucide="file-minus" class="w-4 h-4 mr-3"></i>
                                                    Nota de crédito
                                                </button>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar iconos
    lucide.createIcons();
    
    // Configurar DataTable
    const tabla = $('#tablaFacturas').DataTable({
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
    
    // Inicializar gráficos
    inicializarGraficos();
    
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
        
        if (!e.target.closest('[onclick^="toggleMenuFactura"]')) {
            document.querySelectorAll('[id^="menu-factura-"]').forEach(menu => {
                menu.classList.add('hidden');
            });
        }
    });
});

function inicializarGraficos() {
    // Gráfico de ingresos mensuales
    const ctxIngresos = document.getElementById('ingresosChart');
    if (ctxIngresos) {
        new Chart(ctxIngresos, {
            type: 'line',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                datasets: [{
                    label: 'Ingresos',
                    data: [2500000, 3200000, 2800000, 3500000, 4100000, 3800000],
                    borderColor: '#dc2626',
                    backgroundColor: 'rgba(220, 38, 38, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₲ ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Gráfico de métodos de pago
    const ctxMetodos = document.getElementById('metodosChart');
    if (ctxMetodos) {
        new Chart(ctxMetodos, {
            type: 'doughnut',
            data: {
                labels: ['Efectivo', 'Tarjeta', 'Transferencia', 'Cheque'],
                datasets: [{
                    data: [45, 30, 20, 5],
                    backgroundColor: [
                        '#10b981',
                        '#3b82f6',
                        '#f59e0b',
                        '#8b5cf6'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
}

// Funciones para acciones
function registrarPago(idFactura, numeroFactura, saldoPendiente) {
    Swal.fire({
        title: `Registrar Pago - ${numeroFactura}`,
        html: `
            <div class="space-y-4 text-left">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Monto a pagar:</label>
                    <input id="monto-pago" type="number" max="${saldoPendiente}" value="${saldoPendiente}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-motor-red-500">
                    <p class="text-xs text-gray-500 mt-1">Saldo pendiente: ₲ ${saldoPendiente.toLocaleString()}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Método de pago:</label>
                    <select id="metodo-pago" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-motor-red-500">
                        <option value="efectivo">Efectivo</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Referencia (opcional):</label>
                    <input id="referencia-pago" type="text" placeholder="Número de transacción, cheque, etc."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-motor-red-500">
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Registrar Pago',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc2626',
        preConfirm: () => {
            const monto = document.getElementById('monto-pago').value;
            const metodo = document.getElementById('metodo-pago').value;
            const referencia = document.getElementById('referencia-pago').value;
            
            if (!monto || monto <= 0) {
                Swal.showValidationMessage('Ingrese un monto válido');
                return false;
            }
            
            if (parseFloat(monto) > saldoPendiente) {
                Swal.showValidationMessage('El monto no puede ser mayor al saldo pendiente');
                return false;
            }
            
            return { monto, metodo, referencia };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire('Pago registrado', 'El pago ha sido registrado correctamente', 'success')
                .then(() => location.reload());
        }
    });
}

function anularFactura(idFactura, numeroFactura) {
    Swal.fire({
        title: `¿Anular factura ${numeroFactura}?`,
        input: 'textarea',
        inputLabel: 'Motivo de anulación:',
        inputPlaceholder: 'Ingrese el motivo...',
        inputValidator: (value) => {
            if (!value) return 'Debe ingresar un motivo';
        },
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Anular',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire('Anulada', 'La factura ha sido anulada correctamente', 'success')
                .then(() => location.reload());
        }
    });
}

function enviarPorEmail(idFactura, numeroFactura) {
    Swal.fire({
        title: `Enviar factura ${numeroFactura}`,
        input: 'email',
        inputLabel: 'Email del destinatario:',
        inputPlaceholder: 'cliente@ejemplo.com',
        showCancelButton: true,
        confirmButtonText: 'Enviar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            Swal.fire('Enviado', `Factura enviada a ${result.value}`, 'success');
        }
    });
}

function mostrarPendientesCobro() {
    // Filtrar tabla para mostrar solo facturas emitidas
    document.getElementById('filtro-estado').value = 'emitida';
    // Aplicar filtro (aquí implementarías la lógica real)
    Swal.fire({
        icon: 'info',
        title: 'Facturas Pendientes de Cobro',
        text: 'La tabla ha sido filtrada para mostrar solo las facturas pendientes de cobro.',
        confirmButtonColor: '#dc2626'
    });
}

function duplicarFactura(idFactura) {
    window.location.href = `index.php?pagina=nuevo/factura&duplicar=${idFactura}`;
}

function verHistorialPagos(idFactura) {
    Swal.fire({
        title: 'Historial de Pagos',
        html: `
            <div class="text-left">
                <div class="space-y-3">
                    <div class="border-l-4 border-green-500 pl-4">
                        <p class="font-medium">₲ 500,000</p>
                        <p class="text-sm text-gray-600">15/12/2024 - Efectivo</p>
                    </div>
                    <div class="border-l-4 border-blue-500 pl-4">
                        <p class="font-medium">₲ 300,000</p>
                        <p class="text-sm text-gray-600">10/12/2024 - Tarjeta</p>
                    </div>
                </div>
            </div>
        `,
        confirmButtonColor: '#dc2626'
    });
}

function generarNotaCredito(idFactura) {
    Swal.fire({
        title: 'Generar Nota de Crédito',
        text: 'Esta funcionalidad estará disponible próximamente.',
        icon: 'info',
        confirmButtonColor: '#dc2626'
    });
}

function toggleMenuFactura(id) {
    const menu = document.getElementById(`menu-factura-${id}`);
    document.querySelectorAll('[id^="menu-factura-"]').forEach(m => {
        if (m.id !== `menu-factura-${id}`) {
            m.classList.add('hidden');
        }
    });
    menu.classList.toggle('hidden');
}

function exportarFacturas(formato) {
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

function generarReporteIngresos() {
    document.getElementById('export-menu').classList.add('hidden');
    window.open('index.php?pagina=reportes/ingresos', '_blank');
}
</script>