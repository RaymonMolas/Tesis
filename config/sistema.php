<?php
/**
 * Configuración del Sistema Motor Service
 * Sistema de Gestión Automotriz
 * 
 * Este archivo contiene todas las configuraciones principales del sistema
 */

// Evitar acceso directo
if (!defined('MOTOR_SERVICE_INIT')) {
    die('Acceso directo no permitido');
}

// =====================================================
// INFORMACIÓN DEL SISTEMA
// =====================================================
define('SISTEMA_NOMBRE', 'Motor Service');
define('SISTEMA_VERSION', '2.0');
define('SISTEMA_DESCRIPCION', 'Sistema de Gestión Automotriz - Servicio Integral');
define('SISTEMA_DESARROLLADOR', 'Motor Service Team');
define('SISTEMA_FECHA_VERSION', '2025-06-24');

// =====================================================
// CONFIGURACIÓN DE BASE DE DATOS
// =====================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'backup_taller');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', '3306');
define('DB_CHARSET', 'utf8mb4');

// =====================================================
// CONFIGURACIÓN DE ZONA HORARIA
// =====================================================
define('TIMEZONE', 'America/Asuncion'); // Paraguay
date_default_timezone_set(TIMEZONE);

// =====================================================
// CONFIGURACIÓN DE SESIÓN
// =====================================================
define('SESSION_TIMEOUT', 3600); // 1 hora en segundos
define('SESSION_NAME', 'MOTOR_SERVICE_SESSION');
define('COOKIE_LIFETIME', 0); // Hasta cerrar navegador

// =====================================================
// CONFIGURACIÓN DE SEGURIDAD
// =====================================================
define('MAX_INTENTOS_LOGIN', 5);
define('TIEMPO_BLOQUEO_LOGIN', 30); // minutos
define('LONGITUD_MIN_PASSWORD', 6);
define('REQUIRE_STRONG_PASSWORD', false);

// =====================================================
// CONFIGURACIÓN DE ARCHIVOS
// =====================================================
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx', 'xls', 'xlsx']);
define('UPLOAD_PATH', '../uploads/');
define('BACKUP_PATH', '../backups/');
define('EXPORT_PATH', '../exports/');

// =====================================================
// CONFIGURACIÓN DE EMPRESA
// =====================================================
define('EMPRESA_NOMBRE', 'Motor Service - Servicio Integral Automotriz');
define('EMPRESA_RUC', '80012345-1');
define('EMPRESA_DIRECCION', 'Asunción, Paraguay');
define('EMPRESA_TELEFONO', '(0984) 800 586');
define('EMPRESA_EMAIL', 'info@motorservicepy.com');
define('EMPRESA_WEBSITE', 'www.motorservicepy.com');
define('EMPRESA_LOGO', 'img/img-01.jpg');

// =====================================================
// CONFIGURACIÓN DE FACTURACIÓN
// =====================================================
define('TIMBRADO_NUMERO', '12345678');
define('TIMBRADO_VENCIMIENTO', '2025-12-31');
define('FORMATO_FACTURA', '001-001-0000000');
define('IVA_PORCENTAJE', 10);

// =====================================================
// CONFIGURACIÓN DE AGENDAMIENTO
// =====================================================
define('MAX_CITAS_POR_DIA', 6);
define('HORARIO_INICIO', '08:00');
define('HORARIO_FIN', '18:00');
define('DIAS_LABORALES', ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado']);
define('DURACION_CITA_MIN', 60); // minutos

// =====================================================
// CONFIGURACIÓN DE NOTIFICACIONES
// =====================================================
define('NOTIFICACIONES_HABILITADAS', true);
define('EMAIL_NOTIFICACIONES', true);
define('SMS_NOTIFICACIONES', false);

// =====================================================
// CONFIGURACIÓN DE LOGS
// =====================================================
define('LOG_HABILITADO', true);
define('LOG_PATH', '../logs/');
define('LOG_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('LOG_RETENTION_DAYS', 90);

// =====================================================
// CONFIGURACIÓN DE CACHE
// =====================================================
define('CACHE_HABILITADO', false);
define('CACHE_PATH', '../cache/');
define('CACHE_LIFETIME', 3600); // 1 hora

// =====================================================
// CONFIGURACIÓN DE API
// =====================================================
define('API_HABILITADA', true);
define('API_VERSION', 'v1');
define('API_RATE_LIMIT', 100); // requests per hour

// =====================================================
// RUTAS DEL SISTEMA
// =====================================================
define('ROOT_PATH', dirname(__DIR__));
define('MODEL_PATH', ROOT_PATH . '/modelo/');
define('VIEW_PATH', ROOT_PATH . '/vista/');
define('CONTROLLER_PATH', ROOT_PATH . '/controlador/');
define('API_PATH', ROOT_PATH . '/api/');

// =====================================================
// CONFIGURACIÓN DE DESARROLLO
// =====================================================
define('DESARROLLO', false); // Cambiar a false en producción
define('DEBUG_ENABLED', DESARROLLO);
define('SHOW_ERRORS', DESARROLLO);

if (DESARROLLO) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// =====================================================
// TIPOS DE SERVICIOS DISPONIBLES
// =====================================================
define('TIPOS_SERVICIO', [
    'aceite_motor' => 'Cambio de Aceite de Motor',
    'aceite_dif_trasero' => 'Aceite Diferencial Trasero', 
    'aceite_dif_delantero' => 'Aceite Diferencial Delantero',
    'aceite_caja' => 'Aceite de Caja',
    'aceite_reductora' => 'Aceite de Reductora',
    'filtro_aire' => 'Filtro de Aire',
    'filtro_aceite' => 'Filtro de Aceite',
    'filtro_combustible' => 'Filtro de Combustible',
    'bujias' => 'Bujías',
    'liquidos' => 'Líquidos',
    'mano_obra' => 'Mano de Obra',
    'otro' => 'Otros Servicios'
]);

// =====================================================
// ESTADOS DEL SISTEMA
// =====================================================
define('ESTADOS_CLIENTE', ['activo', 'inactivo']);
define('ESTADOS_PERSONAL', ['activo', 'inactivo', 'vacaciones']);
define('ESTADOS_VEHICULO', ['activo', 'inactivo']);
define('ESTADOS_PRODUCTO', ['activo', 'inactivo', 'descontinuado']);
define('ESTADOS_CITA', ['pendiente', 'confirmada', 'completada', 'cancelada']);
define('ESTADOS_ORDEN', ['en_proceso', 'completado', 'entregado', 'cancelado']);
define('ESTADOS_PRESUPUESTO', ['pendiente', 'aprobado', 'rechazado', 'vencido']);
define('ESTADOS_FACTURA', ['pendiente', 'pagado', 'vencido', 'anulado']);
define('ESTADOS_USUARIO', ['activo', 'inactivo', 'bloqueado']);

// =====================================================
// ROLES Y PERMISOS
// =====================================================
define('ROLES_PERSONAL', ['administrador', 'gerente', 'empleado']);
define('CARGOS_PERSONAL', ['mecanico', 'electricista', 'gerente', 'recepcionista', 'administrador']);

define('PERMISOS_ADMINISTRADOR', [
    'todas' // Acceso completo
]);

define('PERMISOS_GERENTE', [
    'ver_reportes', 'gestionar_personal', 'gestionar_clientes', 
    'gestionar_vehiculos', 'gestionar_ordenes', 'gestionar_presupuestos', 
    'gestionar_facturas', 'ver_estadisticas'
]);

define('PERMISOS_EMPLEADO', [
    'gestionar_clientes', 'gestionar_vehiculos', 'gestionar_ordenes',
    'crear_presupuestos', 'ver_facturas'
]);

// =====================================================
// CONFIGURACIÓN DE MENSAJES
// =====================================================
define('MENSAJES_SISTEMA', [
    'login_exitoso' => '¡Bienvenido a Motor Service!',
    'login_fallido' => 'Credenciales incorrectas',
    'sesion_expirada' => 'Tu sesión ha expirado',
    'acceso_denegado' => 'No tienes permisos para realizar esta acción',
    'operacion_exitosa' => 'Operación realizada correctamente',
    'error_general' => 'Ha ocurrido un error. Intenta nuevamente.',
    'datos_guardados' => 'Los datos han sido guardados correctamente',
    'datos_eliminados' => 'Los datos han sido eliminados correctamente'
]);

// =====================================================
// CONFIGURACIÓN DE FORMATO
// =====================================================
define('FORMATO_FECHA', 'd/m/Y');
define('FORMATO_FECHA_HORA', 'd/m/Y H:i');
define('FORMATO_HORA', 'H:i');
define('FORMATO_MONEDA', 'Gs. %s');
define('SEPARADOR_MILES', '.');
define('SEPARADOR_DECIMALES', ',');

// =====================================================
// CONFIGURACIÓN DE PAGINACIÓN
// =====================================================
define('REGISTROS_POR_PAGINA', 20);
define('MAX_REGISTROS_EXPORT', 10000);

// =====================================================
// CONFIGURACIÓN DE BACKUP
// =====================================================
define('BACKUP_AUTOMATICO', true);
define('BACKUP_FRECUENCIA', 'diario'); // diario, semanal, mensual
define('BACKUP_MAX_ARCHIVOS', 30);

// =====================================================
// FUNCIONES DE UTILIDAD
// =====================================================

/**
 * Verificar si el sistema está en modo desarrollo
 */
function esDesarrollo() {
    return DESARROLLO;
}

/**
 * Obtener información completa del sistema
 */
function obtenerInfoSistema() {
    return [
        'nombre' => SISTEMA_NOMBRE,
        'version' => SISTEMA_VERSION,
        'descripcion' => SISTEMA_DESCRIPCION,
        'desarrollador' => SISTEMA_DESARROLLADOR,
        'fecha_version' => SISTEMA_FECHA_VERSION,
        'php_version' => PHP_VERSION,
        'timezone' => TIMEZONE,
        'modo' => DESARROLLO ? 'Desarrollo' : 'Producción'
    ];
}

/**
 * Verificar configuración del sistema
 */
function verificarConfiguracion() {
    $errores = [];
    
    // Verificar directorios necesarios
    $directorios = [UPLOAD_PATH, BACKUP_PATH, EXPORT_PATH, LOG_PATH];
    foreach ($directorios as $dir) {
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                $errores[] = "No se pudo crear el directorio: $dir";
            }
        } elseif (!is_writable($dir)) {
            $errores[] = "El directorio no tiene permisos de escritura: $dir";
        }
    }
    
    // Verificar extensiones PHP necesarias
    $extensiones = ['pdo', 'pdo_mysql', 'mbstring', 'gd'];
    foreach ($extensiones as $ext) {
        if (!extension_loaded($ext)) {
            $errores[] = "Extensión PHP requerida no encontrada: $ext";
        }
    }
    
    // Verificar configuración de PHP
    if (ini_get('file_uploads') != 1) {
        $errores[] = "La subida de archivos no está habilitada en PHP";
    }
    
    $max_file_size = ini_get('upload_max_filesize');
    if (parse_size($max_file_size) < MAX_FILE_SIZE) {
        $errores[] = "El tamaño máximo de archivo en PHP es menor al configurado";
    }
    
    return $errores;
}

/**
 * Convertir tamaño de archivo a bytes
 */
function parse_size($size) {
    $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
    $size = preg_replace('/[^0-9\.]/', '', $size);
    if ($unit) {
        return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
    } else {
        return round($size);
    }
}

/**
 * Formatear tamaño de archivo
 */
function formatear_bytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

/**
 * Formatear moneda
 */
function formatear_moneda($cantidad) {
    return sprintf(FORMATO_MONEDA, number_format($cantidad, 0, SEPARADOR_DECIMALES, SEPARADOR_MILES));
}

/**
 * Formatear fecha
 */
function formatear_fecha($fecha, $incluir_hora = false) {
    if (!$fecha) return '';
    
    $timestamp = is_numeric($fecha) ? $fecha : strtotime($fecha);
    $formato = $incluir_hora ? FORMATO_FECHA_HORA : FORMATO_FECHA;
    
    return date($formato, $timestamp);
}

/**
 * Generar ID único
 */
function generar_id_unico($prefijo = '') {
    return $prefijo . uniqid() . '_' . time();
}

/**
 * Limpiar texto para prevenir XSS
 */
function limpiar_texto($texto) {
    return htmlspecialchars(trim($texto), ENT_QUOTES, 'UTF-8');
}

/**
 * Validar email
 */
function validar_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validar teléfono paraguayo
 */
function validar_telefono($telefono) {
    // Formato: (0981) 123-456 o 0981123456 o +595981123456
    $patron = '/^(\+595|0)?(9[0-9]{8}|\([0-9]{4}\)\s?[0-9]{3}-?[0-9]{3})$/';
    return preg_match($patron, $telefono);
}

/**
 * Validar RUC paraguayo
 */
function validar_ruc($ruc) {
    // Formato: 12345678-9
    $patron = '/^[0-9]{7,8}-[0-9]$/';
    return preg_match($patron, $ruc);
}

/**
 * Generar contraseña aleatoria
 */
function generar_contrasena($longitud = 8) {
    $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $contrasena = '';
    
    for ($i = 0; $i < $longitud; $i++) {
        $contrasena .= $caracteres[rand(0, strlen($caracteres) - 1)];
    }
    
    return $contrasena;
}

/**
 * Log del sistema
 */
function escribir_log($mensaje, $nivel = 'INFO') {
    if (!LOG_HABILITADO) return;
    
    $fecha = date('Y-m-d H:i:s');
    $log_file = LOG_PATH . 'sistema_' . date('Y-m-d') . '.log';
    $linea = "[$fecha] [$nivel] $mensaje" . PHP_EOL;
    
    file_put_contents($log_file, $linea, FILE_APPEND | LOCK_EX);
}

// =====================================================
// INICIALIZACIÓN AUTOMÁTICA
// =====================================================

// Definir que el sistema está inicializado
define('MOTOR_SERVICE_INIT', true);

// Verificar configuración si estamos en desarrollo
if (DESARROLLO) {
    $errores_config = verificarConfiguracion();
    if (!empty($errores_config)) {
        escribir_log('Errores de configuración detectados: ' . implode(', ', $errores_config), 'ERROR');
    }
}

// Escribir log de inicio del sistema
escribir_log('Sistema Motor Service v' . SISTEMA_VERSION . ' inicializado');

?>