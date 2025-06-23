<?php
if (!isset($_SESSION["validarIngreso"])) {
    echo '<script>window.location = "index.php?pagina=login";</script>';
    return;
} elseif ($_SESSION["validarIngreso"] != "ok") {
    echo '<script>window.location = "index.php?pagina=login";</script>';
    return;
}

// Obtener datos necesarios para el formulario
$vehiculos = VehiculoControlador::ctrListarVehiculos();
$productos = ProductoControlador::buscarProducto();

// Si viene desde un presupuesto, cargar datos
$desdePresupuesto = isset($_GET['desde_presupuesto']) ? $_GET['desde_presupuesto'] : null;
$datosPresupuesto = null;
if ($desdePresupuesto) {
    $datosPresupuesto = PresupuestoControlador::ctrObtenerPresupuesto($desdePresupuesto);
}
?>

<!-- Encabezado de la página -->
<div class="mb-8">
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
                    <span class="ml-4 text-sm font-medium text-gray-900">Nueva Orden</span>
                </div>
            </li>
        </ol>
    </nav>
    
    <div class="mt-4">
        <h1 class="text-2xl font-bold text-gray-900">Crear Nueva Orden de Trabajo</h1>
        <p class="mt-2 text-sm text-gray-700">
            <?php if ($desdePresupuesto): ?>
                    Creando orden desde presupuesto #<?php echo str_pad($desdePresupuesto, 6, '0', STR_PAD_LEFT); ?>
            <?php else: ?>
                    Complete la información para crear una nueva orden de trabajo
            <?php endif; ?>
        </p>
    </div>
</div>

<form method="post" id="formOrdenTrabajo" class="space-y-6">
    <?php if ($desdePresupuesto): ?>
            <input type="hidden" name="desde_presupuesto" value="<?php echo $desdePresupuesto; ?>">
    <?php endif; ?>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Columna principal -->
        <div class="xl:col-span-2 space-y-6">
            
            <!-- Información del Vehículo -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i data-lucide="car" class="w-5 h-5 mr-2 text-motor-red-600"></i>
                        Información del Vehículo
                    </h3>
                </div>
                <div class="px-6 py-6 space-y-6">
                    <!-- Selector de vehículo -->
                    <div>
                        <label for="id_vehiculo" class="block text-sm font-medium text-gray-700 mb-2">
                            Seleccionar Vehículo *
                        </label>
                        <div class="relative">
                            <select id="id_vehiculo" 
                                    name="id_vehiculo" 
                                    required
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-motor-red-500 focus:ring-motor-red-500 transition-colors duration-200">
                                <option value="">Seleccione un vehículo</option>
                                <?php foreach ($vehiculos as $vehiculo): ?>
                                        <option value="<?php echo $vehiculo['id_vehiculo']; ?>"
                                                data-marca="<?php echo $vehiculo['marca']; ?>"
                                                data-modelo="<?php echo $vehiculo['modelo']; ?>"
                                                data-anho="<?php echo $vehiculo['anho']; ?>"
                                                data-color="<?php echo $vehiculo['color']; ?>"
                                                data-matricula="<?php echo $vehiculo['matricula']; ?>"
                                                data-cliente="<?php echo $vehiculo['nombre_cliente']; ?>"
                                                <?php echo ($datosPresupuesto && $datosPresupuesto['id_vehiculo'] == $vehiculo['id_vehiculo']) ? 'selected' : ''; ?>>
                                            <?php echo $vehiculo['marca'] . ' ' . $vehiculo['modelo'] . ' - ' . $vehiculo['matricula'] . ' (' . $vehiculo['nombre_cliente'] . ')'; ?>
                                        </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i data-lucide="chevron-down" class="w-5 h-5 text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Detalles del vehículo seleccionado -->
                    <div id="detalles-vehiculo" class="hidden bg-gray-50 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Detalles del Vehículo</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Marca:</span>
                                <span id="detalle-marca" class="font-medium text-gray-900 ml-1"></span>
                            </div>
                            <div>
                                <span class="text-gray-500">Modelo:</span>
                                <span id="detalle-modelo" class="font-medium text-gray-900 ml-1"></span>
                            </div>
                            <div>
                                <span class="text-gray-500">Año:</span>
                                <span id="detalle-anho" class="font-medium text-gray-900 ml-1"></span>
                            </div>
                            <div>
                                <span class="text-gray-500">Color:</span>
                                <span id="detalle-color" class="font-medium text-gray-900 ml-1"></span>
                            </div>
                            <div class="col-span-2">
                                <span class="text-gray-500">Matrícula:</span>
                                <span id="detalle-matricula" class="font-medium text-gray-900 ml-1"></span>
                            </div>
                            <div class="col-span-2">
                                <span class="text-gray-500">Cliente:</span>
                                <span id="detalle-cliente" class="font-medium text-gray-900 ml-1"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Información adicional -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Kilometraje -->
                        <div>
                            <label for="kilometraje_actual" class="block text-sm font-medium text-gray-700 mb-2">
                                Kilometraje Actual
                            </label>
                            <div class="relative">
                                <input type="number" 
                                       id="kilometraje_actual" 
                                       name="kilometraje_actual"
                                       min="0"
                                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-motor-red-500 focus:ring-motor-red-500 transition-colors duration-200"
                                       placeholder="150000">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 text-sm">km</span>
                                </div>
                            </div>
                        </div>

                        <!-- Fecha de salida estimada -->
                        <div>
                            <label for="fecha_salida" class="block text-sm font-medium text-gray-700 mb-2">
                                Fecha de Salida Estimada
                            </label>
                            <input type="date" 
                                   id="fecha_salida" 
                                   name="fecha_salida"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-motor-red-500 focus:ring-motor-red-500 transition-colors duration-200"
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Servicios a Realizar -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900 flex items-center">
                            <i data-lucide="wrench" class="w-5 h-5 mr-2 text-motor-red-600"></i>
                            Servicios a Realizar
                        </h3>
                        <button type="button" id="btn-agregar-servicio"
                                class="inline-flex items-center px-3 py-2 border border-motor-red-300 rounded-lg text-sm font-medium text-motor-red-700 bg-motor-red-50 hover:bg-motor-red-100 focus:outline-none focus:ring-2 focus:ring-motor-red-500 transition-colors duration-200">
                            <i data-lucide="plus" class="w-4 h-4 mr-1"></i>
                            Agregar Servicio
                        </button>
                    </div>
                </div>
                <div class="px-6 py-6">
                    <!-- Servicios predefinidos -->
                    <div class="mb-6">
                        <h4 class="text-sm font-medium text-gray-900 mb-4">Servicios Comunes</h4>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors duration-200">
                                <input type="checkbox" class="servicio-predefinido text-motor-red-600 focus:ring-motor-red-500" 
                                       data-servicio="Cambio de aceite" data-precio="85000">
                                <span class="ml-3 text-sm text-gray-700">Cambio de aceite</span>
                            </label>
                            
                            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors duration-200">
                                <input type="checkbox" class="servicio-predefinido text-motor-red-600 focus:ring-motor-red-500" 
                                       data-servicio="Cambio de filtros" data-precio="45000">
                                <span class="ml-3 text-sm text-gray-700">Cambio de filtros</span>
                            </label>
                            
                            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors duration-200">
                                <input type="checkbox" class="servicio-predefinido text-motor-red-600 focus:ring-motor-red-500" 
                                       data-servicio="Revisión de frenos" data-precio="120000">
                                <span class="ml-3 text-sm text-gray-700">Revisión de frenos</span>
                            </label>
                            
                            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors duration-200">
                                <input type="checkbox" class="servicio-predefinido text-motor-red-600 focus:ring-motor-red-500" 
                                       data-servicio="Alineación y balanceo" data-precio="150000">
                                <span class="ml-3 text-sm text-gray-700">Alineación y balanceo</span>
                            </label>
                            
                            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors duration-200">
                                <input type="checkbox" class="servicio-predefinido text-motor-red-600 focus:ring-motor-red-500" 
                                       data-servicio="Diagnóstico general" data-precio="80000">
                                <span class="ml-3 text-sm text-gray-700">Diagnóstico general</span>
                            </label>
                            
                            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors duration-200">
                                <input type="checkbox" class="servicio-predefinido text-motor-red-600 focus:ring-motor-red-500" 
                                       data-servicio="Revisión eléctrica" data-precio="100000">
                                <span class="ml-3 text-sm text-gray-700">Revisión eléctrica</span>
                            </label>
                        </div>
                    </div>

                    <!-- Tabla de servicios -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-4">Servicios Seleccionados</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full" id="tabla-servicios">
                                <thead>
                                    <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <th class="py-2">Servicio</th>
                                        <th class="py-2">Cantidad</th>
                                        <th class="py-2">Precio Unitario</th>
                                        <th class="py-2">Subtotal</th>
                                        <th class="py-2">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="servicios-tbody" class="text-sm">
                                    <!-- Los servicios se agregarán dinámicamente aquí -->
                                </tbody>
                                <tfoot>
                                    <tr class="border-t border-gray-200 font-medium">
                                        <td class="py-2" colspan="3">Total:</td>
                                        <td class="py-2" id="total-servicios">₲ 0</td>
                                        <td class="py-2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div id="no-servicios" class="text-center py-8 text-gray-500">
                            <i data-lucide="wrench" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i>
                            <p>No hay servicios seleccionados</p>
                            <p class="text-sm">Seleccione servicios predefinidos o agregue servicios personalizados</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Observaciones -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i data-lucide="message-square" class="w-5 h-5 mr-2 text-motor-red-600"></i>
                        Observaciones
                    </h3>
                </div>
                <div class="px-6 py-6">
                    <textarea id="observaciones" 
                              name="observaciones" 
                              rows="4"
                              class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-motor-red-500 focus:ring-motor-red-500 transition-colors duration-200 resize-none"
                              placeholder="Ingrese observaciones adicionales sobre el trabajo a realizar, problemas detectados, recomendaciones, etc."></textarea>
                    <p class="mt-2 text-sm text-gray-500">
                        Incluya cualquier información importante sobre el estado del vehículo o instrucciones especiales.
                    </p>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <a href="index.php?pagina=tabla/orden_trabajo" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-motor-red-500 transition-colors duration-200">
                        <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                        Cancelar
                    </a>
                    
                    <div class="flex items-center space-x-3">
                        <button type="button" 
                                id="btn-borrador"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200">
                            <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                            Guardar Borrador
                        </button>
                        
                        <button type="submit" 
                                id="btn-crear-orden"
                                class="inline-flex items-center px-6 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-motor-red-600 hover:bg-motor-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-motor-red-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
                            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                            <span id="btn-texto">Crear Orden de Trabajo</span>
                            <div class="ml-2 hidden" id="btn-spinner">
                                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel lateral -->
        <div class="space-y-6">
            <!-- Resumen de la orden -->
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i data-lucide="clipboard" class="w-5 h-5 mr-2 text-motor-red-600"></i>
                    Resumen de la Orden
                </h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Personal asignado:</span>
                        <span class="font-medium text-gray-900"><?php echo $_SESSION["usuario"]; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Fecha de ingreso:</span>
                        <span class="font-medium text-gray-900"><?php echo date('d/m/Y'); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Hora de ingreso:</span>
                        <span class="font-medium text-gray-900"><?php echo date('H:i'); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Estado inicial:</span>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            En Proceso
                        </span>
                    </div>
                    <hr class="my-3">
                    <div class="flex justify-between font-medium">
                        <span class="text-gray-900">Total estimado:</span>
                        <span class="text-motor-red-600" id="resumen-total">₲ 0</span>
                    </div>
                </div>
            </div>

            <!-- Productos disponibles -->
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i data-lucide="package" class="w-5 h-5 mr-2 text-motor-red-600"></i>
                    Productos Disponibles
                </h3>
                <div class="space-y-2 max-h-64 overflow-y-auto">
                    <?php foreach (array_slice($productos, 0, 10) as $producto): ?>
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded text-sm">
                                <div>
                                    <div class="font-medium text-gray-900"><?php echo $producto['nombre']; ?></div>
                                    <div class="text-gray-500">Stock: <?php echo $producto['stock']; ?></div>
                                </div>
                                <div class="text-right">
                                    <div class="font-medium text-gray-900">₲ <?php echo number_format($producto['precio'], 0, ',', '.'); ?></div>
                                    <button type="button" 
                                            onclick="agregarProductoAServicio('<?php echo $producto['nombre']; ?>', <?php echo $producto['precio']; ?>)"
                                            class="text-xs text-motor-red-600 hover:text-motor-red-700">
                                        Agregar
                                    </button>
                                </div>
                            </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Ayuda -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <i data-lucide="help-circle" class="w-5 h-5 text-blue-600"></i>
                    </div>
                    <h3 class="ml-3 text-lg font-medium text-blue-900">Ayuda</h3>
                </div>
                <div class="space-y-3 text-sm text-blue-800">
                    <div class="flex items-start">
                        <i data-lucide="check" class="w-4 h-4 mr-2 mt-0.5 text-blue-600 flex-shrink-0"></i>
                        <p>Seleccione el vehículo que ingresa al taller</p>
                    </div>
                    <div class="flex items-start">
                        <i data-lucide="check" class="w-4 h-4 mr-2 mt-0.5 text-blue-600 flex-shrink-0"></i>
                        <p>Indique los servicios a realizar y sus costos</p>
                    </div>
                    <div class="flex items-start">
                        <i data-lucide="check" class="w-4 h-4 mr-2 mt-0.5 text-blue-600 flex-shrink-0"></i>
                        <p>El kilometraje ayuda a llevar un control del vehículo</p>
                    </div>
                    <div class="flex items-start">
                        <i data-lucide="check" class="w-4 h-4 mr-2 mt-0.5 text-blue-600 flex-shrink-0"></i>
                        <p>Las observaciones son importantes para el seguimiento</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Procesamiento PHP -->
    <?php
    $registro = OrdenTrabajoControlador::ctrRegistrarOrdenTrabajo();
    if ($registro === "ok") {
        echo '<script>
                if (window.history.replaceState) {
                    window.history.replaceState(null, null, window.location.href);
                }
            </script>';
        echo '<div id="mensaje-exito" class="fixed top-4 right-4 z-50 bg-green-50 border border-green-200 rounded-lg p-4 max-w-md">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i data-lucide="check-circle" class="w-5 h-5 text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">
                            ¡Orden de trabajo creada correctamente!
                        </p>
                        <p class="text-sm text-green-700 mt-1">
                            Redirigiendo a la lista de órdenes...
                        </p>
                    </div>
                </div>
            </div>
            <script>
                lucide.createIcons();
                setTimeout(function(){
                    window.location = "index.php?pagina=tabla/orden_trabajo";
                }, 2000);
            </script>';
    }
    ?>
</form>

<!-- Modal para agregar servicio personalizado -->
<div id="modal-servicio" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                            Agregar Servicio Personalizado
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label for="servicio-nombre" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nombre del Servicio *
                                </label>
                                <input type="text" id="servicio-nombre" 
                                       class="block w-full rounded-lg border-gray-300 focus:border-motor-red-500 focus:ring-motor-red-500"
                                       placeholder="Ej: Reparación de motor">
                            </div>
                            <div>
                                <label for="servicio-descripcion" class="block text-sm font-medium text-gray-700 mb-2">
                                    Descripción
                                </label>
                                <textarea id="servicio-descripcion" rows="3"
                                          class="block w-full rounded-lg border-gray-300 focus:border-motor-red-500 focus:ring-motor-red-500"
                                          placeholder="Descripción detallada del servicio"></textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="servicio-cantidad" class="block text-sm font-medium text-gray-700 mb-2">
                                        Cantidad *
                                    </label>
                                    <input type="number" id="servicio-cantidad" min="1" value="1"
                                           class="block w-full rounded-lg border-gray-300 focus:border-motor-red-500 focus:ring-motor-red-500">
                                </div>
                                <div>
                                    <label for="servicio-precio" class="block text-sm font-medium text-gray-700 mb-2">
                                        Precio Unitario (₲) *
                                    </label>
                                    <input type="number" id="servicio-precio" min="0" step="1000"
                                           class="block w-full rounded-lg border-gray-300 focus:border-motor-red-500 focus:ring-motor-red-500">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="btn-agregar-servicio-modal"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-motor-red-600 text-base font-medium text-white hover:bg-motor-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-motor-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Agregar Servicio
                </button>
                <button type="button" onclick="cerrarModalServicio()" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-motor-red-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar iconos
    lucide.createIcons();
    
    // Variables globales
    let serviciosSeleccionados = [];
    let contadorServicios = 0;
    
    // Elementos del DOM
    const form = document.getElementById('formOrdenTrabajo');
    const selectVehiculo = document.getElementById('id_vehiculo');
    const detallesVehiculo = document.getElementById('detalles-vehiculo');
    const btnAgregarServicio = document.getElementById('btn-agregar-servicio');
    const modalServicio = document.getElementById('modal-servicio');
    const btnCrearOrden = document.getElementById('btn-crear-orden');
    const btnTexto = document.getElementById('btn-texto');
    const btnSpinner = document.getElementById('btn-spinner');
    
    // Mostrar detalles del vehículo seleccionado
    selectVehiculo.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (this.value) {
            document.getElementById('detalle-marca').textContent = selectedOption.dataset.marca;
            document.getElementById('detalle-modelo').textContent = selectedOption.dataset.modelo;
            document.getElementById('detalle-anho').textContent = selectedOption.dataset.anho;
            document.getElementById('detalle-color').textContent = selectedOption.dataset.color;
            document.getElementById('detalle-matricula').textContent = selectedOption.dataset.matricula;
            document.getElementById('detalle-cliente').textContent = selectedOption.dataset.cliente;
            
            detallesVehiculo.classList.remove('hidden');
        } else {
            detallesVehiculo.classList.add('hidden');
        }
    });
    
    // Servicios predefinidos
    document.querySelectorAll('.servicio-predefinido').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                agregarServicio({
                    tipo: 'servicio',
                    nombre: this.dataset.servicio,
                    descripcion: this.dataset.servicio,
                    cantidad: 1,
                    precioUnitario: parseInt(this.dataset.precio),
                    subtotal: parseInt(this.dataset.precio)
                });
            } else {
                // Remover servicio si se desmarca
                const nombre = this.dataset.servicio;
                serviciosSeleccionados = serviciosSeleccionados.filter(s => s.nombre !== nombre);
                actualizarTablaServicios();
            }
        });
    });
    
    // Abrir modal para agregar servicio personalizado
    btnAgregarServicio.addEventListener('click', function() {
        modalServicio.classList.remove('hidden');
        document.getElementById('servicio-nombre').focus();
    });
    
    // Agregar servicio desde modal
    document.getElementById('btn-agregar-servicio-modal').addEventListener('click', function() {
        const nombre = document.getElementById('servicio-nombre').value.trim();
        const descripcion = document.getElementById('servicio-descripcion').value.trim();
        const cantidad = parseInt(document.getElementById('servicio-cantidad').value);
        const precio = parseInt(document.getElementById('servicio-precio').value);
        
        if (!nombre || !cantidad || !precio) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos requeridos',
                text: 'Por favor, complete todos los campos obligatorios.',
                confirmButtonColor: '#dc2626'
            });
            return;
        }
        
        agregarServicio({
            tipo: 'servicio',
            nombre: nombre,
            descripcion: descripcion || nombre,
            cantidad: cantidad,
            precioUnitario: precio,
            subtotal: cantidad * precio
        });
        
        cerrarModalServicio();
    });
    
    // Función para agregar servicio
    function agregarServicio(servicio) {
        // Verificar si ya existe
        const existe = serviciosSeleccionados.find(s => s.nombre === servicio.nombre);
        if (existe) {
            Swal.fire({
                icon: 'info',
                title: 'Servicio ya agregado',
                text: 'Este servicio ya está en la lista.',
                confirmButtonColor: '#dc2626'
            });
            return;
        }
        
        servicio.id = ++contadorServicios;
        serviciosSeleccionados.push(servicio);
        actualizarTablaServicios();
    }
    
    // Función para actualizar tabla de servicios
    function actualizarTablaServicios() {
        const tbody = document.getElementById('servicios-tbody');
        const noServicios = document.getElementById('no-servicios');
        const totalElement = document.getElementById('total-servicios');
        const resumenTotal = document.getElementById('resumen-total');
        
        if (serviciosSeleccionados.length === 0) {
            tbody.innerHTML = '';
            noServicios.classList.remove('hidden');
            totalElement.textContent = '₲ 0';
            resumenTotal.textContent = '₲ 0';
            return;
        }
        
        noServicios.classList.add('hidden');
        
        let total = 0;
        tbody.innerHTML = '';
        
        serviciosSeleccionados.forEach(servicio => {
            total += servicio.subtotal;
            
            const row = document.createElement('tr');
            row.className = 'border-b border-gray-200';
            row.innerHTML = `
                <td class="py-2">
                    <div class="font-medium text-gray-900">${servicio.nombre}</div>
                    <div class="text-xs text-gray-500">${servicio.descripcion}</div>
                </td>
                <td class="py-2">
                    <input type="number" min="1" value="${servicio.cantidad}" 
                           onchange="actualizarCantidadServicio(${servicio.id}, this.value)"
                           class="w-20 rounded border-gray-300 text-sm focus:border-motor-red-500 focus:ring-motor-red-500">
                </td>
                <td class="py-2">₲ ${servicio.precioUnitario.toLocaleString()}</td>
                <td class="py-2 font-medium">₲ ${servicio.subtotal.toLocaleString()}</td>
                <td class="py-2">
                    <button type="button" onclick="eliminarServicio(${servicio.id})" 
                            class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50 transition-colors duration-200">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
        
        totalElement.textContent = `₲ ${total.toLocaleString()}`;
        resumenTotal.textContent = `₲ ${total.toLocaleString()}`;
        
        // Reinicializar iconos para los nuevos elementos
        lucide.createIcons();
    }
    
    // Función para actualizar cantidad de servicio
    window.actualizarCantidadServicio = function(id, nuevaCantidad) {
        const servicio = serviciosSeleccionados.find(s => s.id === id);
        if (servicio) {
            servicio.cantidad = parseInt(nuevaCantidad);
            servicio.subtotal = servicio.cantidad * servicio.precioUnitario;
            actualizarTablaServicios();
        }
    };
    
    // Función para eliminar servicio
    window.eliminarServicio = function(id) {
        serviciosSeleccionados = serviciosSeleccionados.filter(s => s.id !== id);
        
        // Desmarcar checkbox si es un servicio predefinido
        const servicio = serviciosSeleccionados.find(s => s.id === id);
        if (servicio) {
            const checkbox = document.querySelector(`[data-servicio="${servicio.nombre}"]`);
            if (checkbox) {
                checkbox.checked = false;
            }
        }
        
        actualizarTablaServicios();
    };
    
    // Función para agregar producto como servicio
    window.agregarProductoAServicio = function(nombre, precio) {
        agregarServicio({
            tipo: 'producto',
            nombre: nombre,
            descripcion: `Producto: ${nombre}`,
            cantidad: 1,
            precioUnitario: precio,
            subtotal: precio
        });
    };
    
    // Función para cerrar modal
    window.cerrarModalServicio = function() {
        modalServicio.classList.add('hidden');
        document.getElementById('servicio-nombre').value = '';
        document.getElementById('servicio-descripcion').value = '';
        document.getElementById('servicio-cantidad').value = '1';
        document.getElementById('servicio-precio').value = '';
    };
    
    // Envío del formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validaciones
        if (!selectVehiculo.value) {
            Swal.fire({
                icon: 'error',
                title: 'Vehículo requerido',
                text: 'Por favor, seleccione un vehículo.',
                confirmButtonColor: '#dc2626'
            });
            return;
        }
        
        if (serviciosSeleccionados.length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Servicios requeridos',
                text: 'Por favor, agregue al menos un servicio.',
                confirmButtonColor: '#dc2626'
            });
            return;
        }
        
        // Agregar servicios al formulario
        const inputServicios = document.createElement('input');
        inputServicios.type = 'hidden';
        inputServicios.name = 'servicios';
        inputServicios.value = JSON.stringify(serviciosSeleccionados);
        form.appendChild(inputServicios);
        
        // Mostrar loading
        btnCrearOrden.disabled = true;
        btnTexto.textContent = 'Creando orden...';
        btnSpinner.classList.remove('hidden');
        
        // Enviar formulario
        this.submit();
    });
    
    // Cargar datos del presupuesto si existe
    <?php if ($datosPresupuesto): ?>
            // Aquí cargarías los servicios del presupuesto
            // Por simplicidad, no lo implemento completamente
    <?php endif; ?>
    
    // Establecer fecha mínima para fecha de salida
    const fechaSalida = document.getElementById('fecha_salida');
    const hoy = new Date();
    hoy.setDate(hoy.getDate() + 1); // Mínimo mañana
    fechaSalida.value = hoy.toISOString().split('T')[0];
    
    // Auto-hide del mensaje de éxito
    const mensajeExito = document.getElementById('mensaje-exito');
    if (mensajeExito) {
        setTimeout(() => {
            mensajeExito.style.opacity = '0';
            setTimeout(() => mensajeExito.remove(), 300);
        }, 5000);
    }
});
</script>