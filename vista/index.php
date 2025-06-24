<?php
/**
 * INDEX PRINCIPAL - MOTOR SERVICE
 * Sistema de Gestión Automotriz - Servicio Integral
 * Versión: 2.0
 * Fecha: 2025-06-24
 */

// Inicializar el sistema
require_once "../includes/init.php";

// Verificar que el sistema esté listo
if (!defined('MOTOR_SERVICE_READY')) {
    die('Error: Sistema no inicializado correctamente');
}

// =====================================================
// INCLUIR CONTROLADORES Y MODELOS ACTUALIZADOS
// =====================================================

// Controladores principales
require_once "../controlador/plantilla_controlador.php";
require_once "../controlador/login_controlador.php";
require_once "../controlador/cliente_controlador.php";
require_once "../controlador/agendamiento_controlador.php";
require_once "../controlador/personal_controlador.php";
require_once "../controlador/usuario_controlador.php";
require_once "../controlador/producto_controlador.php";
require_once "../controlador/historicocitas_controlador.php";
require_once "../controlador/orden_trabajo_controlador.php";
require_once "../controlador/vehiculo_controlador.php";
require_once "../controlador/presupuesto_controlador.php";
require_once "../controlador/factura_controlador.php";

// Modelos principales
require_once "../modelo/modelo_orden_trabajo.php";
require_once "../modelo/modelo_orden_detalle.php";
require_once "../modelo/modelo_vehiculo.php";
require_once "../modelo/modelo_historicocitas.php";
require_once "../modelo/modelo_producto.php";
require_once "../modelo/modelo_usuario.php";
require_once "../modelo/modelo_personal.php";
require_once "../modelo/modelo_agendamiento.php";
require_once "../modelo/modelo_cliente.php";
require_once "../modelo/modelo_presupuesto.php";
require_once "../modelo/modelo_factura.php";
require_once "../modelo/modelo_detalle_factura.php";
require_once "../modelo/modelo_empresa.php";
require_once "../modelo/modelo_historialvehiculo.php";
require_once "../modelo/login_modelo.php";

// =====================================================
// VERIFICAR SEGURIDAD Y PERMISOS
// =====================================================

// Verificar acceso a página específica si está logueado
if (isset($_GET["pagina"]) && $_GET["pagina"] != "login") {
    // Verificar que la sesión sea válida
    if (!LoginControlador::ctrVerificarSesion()) {
        echo '<script>window.location = "index.php?pagina=login";</script>';
        exit;
    }

    // Validar acceso a la página solicitada
    LoginControlador::ctrValidarAccesoPagina($_GET["pagina"]);
}

// =====================================================
// PROCESAR ACCIONES DEL SISTEMA
// =====================================================

// Procesar login
if (isset($_POST["usuario_login"])) {
    LoginControlador::ctrLogin();
}

// Procesar logout
if (isset($_GET["logout"])) {
    LoginControlador::ctrCerrarSesion();
}

// Procesar cambio de contraseña
if (isset($_POST["cambiar_contrasena"])) {
    LoginControlador::ctrCambiarContrasena();
}

// =====================================================
// PROCESAR ACCIONES DE CRUD
// =====================================================

// Acciones de Cliente
if (isset($_POST["crear_cliente"])) {
    ClienteControlador::ctrCrearCliente();
}
if (isset($_POST["editar_cliente"])) {
    ClienteControlador::ctrEditarCliente();
}
if (isset($_GET["eliminar_cliente"])) {
    ClienteControlador::ctrEliminarCliente();
}

// Acciones de Personal
if (isset($_POST["crear_personal"])) {
    PersonalControlador::ctrCrearPersonal();
}
if (isset($_POST["editar_personal"])) {
    PersonalControlador::ctrEditarPersonal();
}
if (isset($_GET["eliminar_personal"])) {
    PersonalControlador::ctrEliminarPersonal();
}

// Acciones de Usuario
if (isset($_POST["crear_usuario_personal"])) {
    UsuarioControlador::ctrCrearUsuarioPersonal();
}
if (isset($_POST["crear_usuario_cliente"])) {
    UsuarioControlador::ctrCrearUsuarioCliente();
}
if (isset($_POST["editar_usuario_personal"])) {
    UsuarioControlador::ctrEditarUsuarioPersonal();
}
if (isset($_POST["editar_usuario_cliente"])) {
    UsuarioControlador::ctrEditarUsuarioCliente();
}

// Acciones de Producto
if (isset($_POST["crear_producto"])) {
    ProductoControlador::ctrCrearProducto();
}
if (isset($_POST["editar_producto"])) {
    ProductoControlador::ctrEditarProducto();
}
if (isset($_GET["eliminar_producto"])) {
    ProductoControlador::ctrEliminarProducto();
}

// Acciones de Vehículo
if (isset($_POST["crear_vehiculo"])) {
    VehiculoControlador::ctrCrearVehiculo();
}
if (isset($_POST["editar_vehiculo"])) {
    VehiculoControlador::ctrEditarVehiculo();
}
if (isset($_GET["eliminar_vehiculo"])) {
    VehiculoControlador::ctrEliminarVehiculo();
}

// Acciones de Orden de Trabajo
if (isset($_POST["crear_orden_trabajo"])) {
    OrdenTrabajoControlador::ctrCrearOrdenTrabajo();
}
if (isset($_POST["editar_orden_trabajo"])) {
    OrdenTrabajoControlador::ctrEditarOrdenTrabajo();
}
if (isset($_GET["eliminar_orden"])) {
    OrdenTrabajoControlador::ctrEliminarOrdenTrabajo();
}

// Acciones de Presupuesto
if (isset($_POST["crear_presupuesto"])) {
    PresupuestoControlador::ctrCrearPresupuesto();
}
if (isset($_POST["editar_presupuesto"])) {
    PresupuestoControlador::ctrEditarPresupuesto();
}
if (isset($_GET["eliminar_presupuesto"])) {
    PresupuestoControlador::ctrEliminarPresupuesto();
}

// Acciones de Factura
if (isset($_POST["crear_factura"])) {
    FacturaControlador::ctrCrearFactura();
}
if (isset($_POST["editar_factura"])) {
    FacturaControlador::ctrEditarFactura();
}
if (isset($_GET["anular_factura"])) {
    FacturaControlador::ctrAnularFactura();
}

// Acciones de Agendamiento
if (isset($_POST["crear_cita"])) {
    ControladorAgendamiento::ctrCrearCita();
}
if (isset($_POST["editar_cita"])) {
    ControladorAgendamiento::ctrEditarCita();
}

// =====================================================
// ACCIONES AJAX
// =====================================================

// Manejar peticiones AJAX
if (isset($_POST["accion_ajax"])) {
    header('Content-Type: application/json');

    switch ($_POST["accion_ajax"]) {
        case "cambiar_estado_cliente":
            ClienteControlador::ctrCambiarEstadoCliente();
            break;

        case "cambiar_estado_personal":
            PersonalControlador::ctrCambiarEstadoPersonal();
            break;

        case "cambiar_estado_producto":
            ProductoControlador::ctrCambiarEstadoProducto();
            break;

        case "actualizar_stock":
            ProductoControlador::ctrActualizarStock();
            break;

        case "cambiar_estado_presupuesto":
            PresupuestoControlador::ctrCambiarEstadoPresupuesto();
            break;

        case "asignar_personal_cita":
            ControladorAgendamiento::ctrAsignarPersonal();
            break;

        case "obtener_disponibilidad":
            ControladorAgendamiento::ctrObtenerDisponibilidad();
            break;

        case "buscar_vehiculos":
            echo json_encode(VehiculoControlador::ctrBuscarVehiculos());
            break;

        case "obtener_vehiculos_cliente":
            if (isset($_POST["id_cliente"])) {
                echo json_encode(VehiculoControlador::ctrListarVehiculosCliente($_POST["id_cliente"]));
            }
            break;

        case "buscar_productos":
            if (isset($_POST["termino"])) {
                echo json_encode(ProductoControlador::ctrBuscarProductos($_POST["termino"]));
            }
            break;

        case "obtener_info_producto":
            if (isset($_POST["id_producto"])) {
                echo json_encode(ProductoControlador::ctrObtenerProducto($_POST["id_producto"]));
            }
            break;

        default:
            echo json_encode(["error" => "Acción no reconocida"]);
    }
    exit;
}

// =====================================================
// ESTADÍSTICAS PARA DASHBOARD
// =====================================================

// Obtener estadísticas solo si está en la página de inicio
if (!isset($_GET["pagina"]) || $_GET["pagina"] == "inicio") {
    // Verificar que el usuario esté logueado
    if (isset($_SESSION["validarIngreso"]) && $_SESSION["validarIngreso"] == "ok") {

        // Estadísticas generales
        $total_clientes = ClienteControlador::ctrContarClientes();
        $total_vehiculos = VehiculoControlador::ctrContarVehiculos();
        $total_personal = PersonalControlador::ctrEstadisticasPersonal();
        $total_productos = ProductoControlador::ctrEstadisticasProductos();

        // Estadísticas específicas según el tipo de usuario
        if ($_SESSION["tipo_usuario"] == "personal") {
            $citas_pendientes = count(ControladorAgendamiento::listarSolicitudesPendientes());
            $ordenes_en_proceso = OrdenTrabajoControlador::ctrContarOrdenesPorEstado('en_proceso');
            $presupuestos_pendientes = count(PresupuestoControlador::ctrPresupuestosPendientes());
            $productos_stock_bajo = count(ProductoControlador::ctrProductosStockBajo());

            // Alertas del sistema
            $alertas = array_merge(
                ControladorAgendamiento::ctrObtenerAlertasAgendamiento(),
                PresupuestoControlador::ctrObtenerAlertasPresupuestos(),
                ProductoControlador::ctrAlertasStock()
            );
        } else {
            // Para clientes
            $mis_vehiculos = VehiculoControlador::ctrListarVehiculosCliente($_SESSION["id_cliente"]);
            $mis_citas = ControladorAgendamiento::obtenerCitasCliente($_SESSION["id_cliente"]);
            $mi_historial = HistoricoCitasControlador::ctrObtenerHistorialCliente($_SESSION["id_cliente"]);
        }
    }
}

// =====================================================
// MOSTRAR PLANTILLA PRINCIPAL
// =====================================================

$plantilla = new ControladorPlantilla();
$plantilla->ctrTraerPlantilla();

// =====================================================
// FUNCIONES AUXILIARES PARA LAS VISTAS
// =====================================================

/**
 * Función para verificar permisos en las vistas
 */
function tienePermiso($permisos)
{
    return LoginControlador::ctrValidarPermisos(is_array($permisos) ? $permisos : [$permisos]);
}

/**
 * Función para obtener información del usuario actual
 */
function obtenerUsuarioActual()
{
    return LoginControlador::ctrObtenerInfoUsuario();
}

/**
 * Función para formatear fecha en las vistas
 */
function formatearFechaVista($fecha, $incluir_hora = false)
{
    return formatear_fecha($fecha, $incluir_hora);
}

/**
 * Función para formatear moneda en las vistas
 */
function formatearMonedaVista($cantidad)
{
    return formatear_moneda($cantidad);
}

/**
 * Función para obtener estados disponibles
 */
function obtenerEstados($tipo)
{
    $estados = [
        'cliente' => ESTADOS_CLIENTE,
        'personal' => ESTADOS_PERSONAL,
        'vehiculo' => ESTADOS_VEHICULO,
        'producto' => ESTADOS_PRODUCTO,
        'cita' => ESTADOS_CITA,
        'orden' => ESTADOS_ORDEN,
        'presupuesto' => ESTADOS_PRESUPUESTO,
        'factura' => ESTADOS_FACTURA,
        'usuario' => ESTADOS_USUARIO
    ];

    return $estados[$tipo] ?? [];
}

/**
 * Función para obtener tipos de servicio
 */
function obtenerTiposServicio()
{
    return TIPOS_SERVICIO;
}

/**
 * Función para obtener roles de personal
 */
function obtenerRolesPersonal()
{
    return ROLES_PERSONAL;
}

/**
 * Función para obtener cargos de personal
 */
function obtenerCargosPersonal()
{
    return CARGOS_PERSONAL;
}

// =====================================================
// MANEJO DE NOTIFICACIONES
// =====================================================

// Mostrar notificaciones guardadas en sesión
if (isset($_SESSION["mensajeJS"])) {
    echo '<script>' . $_SESSION["mensajeJS"] . '</script>';
    unset($_SESSION["mensajeJS"]);
}

// =====================================================
// INFORMACIÓN PARA DEBUG EN DESARROLLO
// =====================================================

if (DESARROLLO && isset($_GET["debug"])) {
    // Mostrar información adicional de debug
    $debug_info = [
        'version_sistema' => SISTEMA_VERSION,
        'version_php' => PHP_VERSION,
        'memoria_utilizada' => formatear_bytes(memory_get_usage()),
        'usuario_logueado' => $_SESSION["usuario"] ?? 'No logueado',
        'tipo_usuario' => $_SESSION["tipo_usuario"] ?? 'N/A',
        'pagina_actual' => $_GET["pagina"] ?? 'inicio',
        'sesion_valida' => isset($_SESSION["validarIngreso"]) && $_SESSION["validarIngreso"] == "ok",
        'timestamp' => date('Y-m-d H:i:s')
    ];

    // Agregar información de debug al HTML (se mostrará al final por la función registrada)
    $GLOBALS['debug_motor_service'] = $debug_info;
}

// =====================================================
// LIMPIEZA FINAL
// =====================================================

// Limpiar variables que ya no se necesitan
unset($plantilla);

// Ejecutar garbage collection si es necesario
if (function_exists('gc_collect_cycles')) {
    gc_collect_cycles();
}

// Escribir log de finalización de request
if (LOG_HABILITADO && DESARROLLO) {
    $pagina = $_GET["pagina"] ?? 'inicio';
    $usuario = $_SESSION["usuario"] ?? 'anonimo';
    $tiempo_ejecucion = round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2);

    escribir_log("Request completado: $pagina por $usuario en {$tiempo_ejecucion}ms", 'REQUEST');
}
?>