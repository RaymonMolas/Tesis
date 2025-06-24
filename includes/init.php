<?php
/**
 * Inicialización del Sistema Motor Service
 * Este archivo se encarga de configurar e inicializar todo el sistema
 */

// Verificar versión de PHP
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    die('Motor Service requiere PHP 7.4 o superior. Versión actual: ' . PHP_VERSION);
}

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    // Configurar sesión de forma segura
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    
    session_name('MOTOR_SERVICE_SESSION');
    session_start();
}

// Cargar configuración del sistema
require_once __DIR__ . '/../config/sistema.php';

// Cargar utilidades
require_once __DIR__ . '/utilidades.php';

// =====================================================
// CONFIGURACIÓN DE ERRORES
// =====================================================

if (DESARROLLO) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Configurar archivo de log de errores
if (LOG_HABILITADO) {
    ini_set('error_log', LOG_PATH . 'errores_' . date('Y-m-d') . '.log');
}

// =====================================================
// AUTOLOAD DE CLASES
// =====================================================

spl_autoload_register(function ($clase) {
    // Convertir nombre de clase a nombre de archivo
    $archivos_posibles = [
        MODEL_PATH . strtolower($clase) . '.php',
        MODEL_PATH . 'modelo_' . strtolower($clase) . '.php',
        CONTROLLER_PATH . strtolower($clase) . '.php',
        CONTROLLER_PATH . strtolower($clase) . '_controlador.php'
    ];
    
    foreach ($archivos_posibles as $archivo) {
        if (file_exists($archivo)) {
            require_once $archivo;
            return;
        }
    }
});

// =====================================================
// CONFIGURACIÓN DE ZONA HORARIA
// =====================================================

date_default_timezone_set(TIMEZONE);

// =====================================================
// CONFIGURACIÓN DE LOCALE
// =====================================================

setlocale(LC_TIME, 'es_PY.utf8', 'es_PY', 'es_ES.utf8', 'es_ES', 'Spanish');
setlocale(LC_MONETARY, 'es_PY.utf8', 'es_PY', 'es_ES.utf8', 'es_ES', 'Spanish');

// =====================================================
// FUNCIONES DE MANEJO DE ERRORES
// =====================================================

/**
 * Manejador personalizado de errores
 */
function manejar_error($nivel, $mensaje, $archivo, $linea) {
    if (!(error_reporting() & $nivel)) {
        return false;
    }
    
    $tipos_error = [
        E_ERROR => 'ERROR',
        E_WARNING => 'WARNING',
        E_PARSE => 'PARSE',
        E_NOTICE => 'NOTICE',
        E_CORE_ERROR => 'CORE_ERROR',
        E_CORE_WARNING => 'CORE_WARNING',
        E_COMPILE_ERROR => 'COMPILE_ERROR',
        E_COMPILE_WARNING => 'COMPILE_WARNING',
        E_USER_ERROR => 'USER_ERROR',
        E_USER_WARNING => 'USER_WARNING',
        E_USER_NOTICE => 'USER_NOTICE',
        E_STRICT => 'STRICT',
        E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
        E_DEPRECATED => 'DEPRECATED',
        E_USER_DEPRECATED => 'USER_DEPRECATED',
    ];
    
    $tipo = isset($tipos_error[$nivel]) ? $tipos_error[$nivel] : 'UNKNOWN';
    $log_mensaje = "[$tipo] $mensaje en $archivo línea $linea";
    
    escribir_log($log_mensaje, 'ERROR');
    
    if (DESARROLLO) {
        echo "<div style='background: #fff5f5; border: 1px solid #f87171; color: #dc2626; padding: 10px; margin: 5px; border-radius: 4px;'>";
        echo "<strong>$tipo:</strong> $mensaje<br>";
        echo "<small>Archivo: $archivo (línea $linea)</small>";
        echo "</div>";
    }
    
    return true;
}

/**
 * Manejador de excepciones no capturadas
 */
function manejar_excepcion($excepcion) {
    $mensaje = "Excepción no capturada: " . $excepcion->getMessage();
    $archivo = $excepcion->getFile();
    $linea = $excepcion->getLine();
    $traza = $excepcion->getTraceAsString();
    
    $log_mensaje = "$mensaje en $archivo línea $linea\nTraza:\n$traza";
    escribir_log($log_mensaje, 'EXCEPTION');
    
    if (DESARROLLO) {
        echo "<div style='background: #fef2f2; border: 1px solid #fca5a5; color: #dc2626; padding: 15px; margin: 10px; border-radius: 4px;'>";
        echo "<h3>Excepción No Capturada</h3>";
        echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($mensaje) . "</p>";
        echo "<p><strong>Archivo:</strong> $archivo (línea $linea)</p>";
        echo "<details><summary>Ver traza completa</summary><pre>" . htmlspecialchars($traza) . "</pre></details>";
        echo "</div>";
    } else {
        // En producción, mostrar página de error genérica
        mostrar_pagina_error();
    }
}

/**
 * Manejador de errores fatales
 */
function manejar_error_fatal() {
    $error = error_get_last();
    
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $mensaje = "Error fatal: " . $error['message'];
        $archivo = $error['file'];
        $linea = $error['line'];
        
        escribir_log("$mensaje en $archivo línea $linea", 'FATAL');
        
        if (!DESARROLLO) {
            mostrar_pagina_error();
        }
    }
}

/**
 * Mostrar página de error para producción
 */
function mostrar_pagina_error() {
    http_response_code(500);
    
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error - Motor Service</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                background: #f8f9fa; 
                margin: 0; 
                padding: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
            }
            .error-container {
                background: white;
                border-radius: 10px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                padding: 40px;
                text-align: center;
                max-width: 500px;
                border-left: 5px solid #dc2626;
            }
            .error-icon {
                font-size: 48px;
                color: #dc2626;
                margin-bottom: 20px;
            }
            h1 {
                color: #dc2626;
                margin-bottom: 10px;
            }
            p {
                color: #666;
                line-height: 1.6;
                margin-bottom: 30px;
            }
            .btn {
                background: #dc2626;
                color: white;
                padding: 12px 24px;
                border: none;
                border-radius: 5px;
                text-decoration: none;
                display: inline-block;
                cursor: pointer;
            }
            .btn:hover {
                background: #b91c1c;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-icon">⚠️</div>
            <h1>Error del Sistema</h1>
            <p>Se ha producido un error interno. Nuestro equipo técnico ha sido notificado automáticamente.</p>
            <p>Por favor, intenta nuevamente en unos minutos.</p>
            <a href="javascript:history.back()" class="btn">← Volver</a>
            <a href="index.php?pagina=inicio" class="btn">🏠 Inicio</a>
        </div>
    </body>
    </html>';
    
    exit;
}

// Registrar manejadores de errores
set_error_handler('manejar_error');
set_exception_handler('manejar_excepcion');
register_shutdown_function('manejar_error_fatal');

// =====================================================
// VERIFICACIÓN DEL SISTEMA
// =====================================================

/**
 * Verificar estado del sistema al inicializar
 */
function verificar_sistema() {
    $errores = [];
    
    // Verificar conexión a base de datos
    try {
        require_once MODEL_PATH . 'conexion.php';
        $verificacion_db = Conexion::verificarConexion();
        
        if ($verificacion_db['status'] !== 'ok') {
            $errores[] = 'Error de conexión a la base de datos: ' . $verificacion_db['message'];
        }
    } catch (Exception $e) {
        $errores[] = 'No se pudo verificar la conexión a la base de datos: ' . $e->getMessage();
    }
    
    // Verificar configuración
    $errores_config = verificarConfiguracion();
    $errores = array_merge($errores, $errores_config);
    
    // Verificar permisos de archivos críticos
    $archivos_criticos = [
        MODEL_PATH . 'conexion.php',
        __DIR__ . '/../config/sistema.php'
    ];
    
    foreach ($archivos_criticos as $archivo) {
        if (!file_exists($archivo)) {
            $errores[] = "Archivo crítico no encontrado: $archivo";
        } elseif (!is_readable($archivo)) {
            $errores[] = "No se puede leer el archivo: $archivo";
        }
    }
    
    // Log de errores encontrados
    if (!empty($errores)) {
        foreach ($errores as $error) {
            escribir_log($error, 'SYSTEM_CHECK');
        }
        
        if (!DESARROLLO) {
            // En producción, solo log y continuar
            escribir_log('Sistema iniciado con ' . count($errores) . ' advertencias', 'WARNING');
        } else {
            // En desarrollo, mostrar errores
            echo '<div style="background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; margin: 10px; border-radius: 5px;">';
            echo '<h4>Advertencias del Sistema:</h4>';
            echo '<ul>';
            foreach ($errores as $error) {
                echo '<li>' . htmlspecialchars($error) . '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }
    }
    
    return empty($errores);
}

// =====================================================
// FUNCIONES DE SEGURIDAD ADICIONALES
// =====================================================

/**
 * Verificar si la IP está en lista negra
 */
function verificar_ip_bloqueada() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    
    // Lista de IPs bloqueadas (se podría cargar desde BD)
    $ips_bloqueadas = [];
    
    if (in_array($ip, $ips_bloqueadas)) {
        http_response_code(403);
        escribir_log("Acceso bloqueado desde IP: $ip", 'SECURITY');
        die('Acceso denegado');
    }
}

/**
 * Verificar límite de peticiones por IP
 */
function verificar_rate_limit() {
    if (!API_HABILITADA) {
        return;
    }
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $hora_actual = date('Y-m-d H');
    $clave_cache = "rate_limit_{$ip}_{$hora_actual}";
    
    $peticiones = obtener_cache($clave_cache) ?: 0;
    
    if ($peticiones >= API_RATE_LIMIT) {
        http_response_code(429);
        escribir_log("Rate limit excedido para IP: $ip", 'SECURITY');
        die('Límite de peticiones excedido');
    }
    
    guardar_cache($clave_cache, $peticiones + 1, 3600);
}

/**
 * Aplicar cabeceras de seguridad
 */
function aplicar_cabeceras_seguridad() {
    // Prevenir clickjacking
    header('X-Frame-Options: DENY');
    
    // Prevenir MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Habilitar protección XSS
    header('X-XSS-Protection: 1; mode=block');
    
    // Política de referrer
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Content Security Policy básica
    if (!DESARROLLO) {
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:;");
    }
}

// =====================================================
// FUNCIONES DE LIMPIEZA Y MANTENIMIENTO
// =====================================================

/**
 * Ejecutar tareas de mantenimiento automático
 */
function ejecutar_mantenimiento_automatico() {
    // Solo ejecutar una vez por día
    $ultimo_mantenimiento = obtener_cache('ultimo_mantenimiento');
    $hoy = date('Y-m-d');
    
    if ($ultimo_mantenimiento === $hoy) {
        return;
    }
    
    // Limpiar logs antiguos
    if (LOG_HABILITADO) {
        $logs_eliminados = 0;
        $archivos_log = glob(LOG_PATH . '*.log');
        
        foreach ($archivos_log as $archivo) {
            $fecha_archivo = filemtime($archivo);
            $dias_antiguedad = (time() - $fecha_archivo) / (24 * 60 * 60);
            
            if ($dias_antiguedad > LOG_RETENTION_DAYS) {
                if (unlink($archivo)) {
                    $logs_eliminados++;
                }
            }
        }
        
        if ($logs_eliminados > 0) {
            escribir_log("Mantenimiento: $logs_eliminados archivos de log eliminados", 'MAINTENANCE');
        }
    }
    
    // Limpiar cache expirado
    if (CACHE_HABILITADO) {
        $cache_eliminado = limpiar_cache();
        if ($cache_eliminado > 0) {
            escribir_log("Mantenimiento: $cache_eliminado archivos de cache eliminados", 'MAINTENANCE');
        }
    }
    
    // Limpiar archivos temporales
    $archivos_temp = glob(sys_get_temp_dir() . '/motor_service_*');
    $temp_eliminados = 0;
    
    foreach ($archivos_temp as $archivo) {
        $fecha_archivo = filemtime($archivo);
        $horas_antiguedad = (time() - $fecha_archivo) / 3600;
        
        if ($horas_antiguedad > 24) { // Eliminar temporales de más de 24 horas
            if (unlink($archivo)) {
                $temp_eliminados++;
            }
        }
    }
    
    if ($temp_eliminados > 0) {
        escribir_log("Mantenimiento: $temp_eliminados archivos temporales eliminados", 'MAINTENANCE');
    }
    
    // Marcar mantenimiento completado
    guardar_cache('ultimo_mantenimiento', $hoy, 86400); // 24 horas
    escribir_log('Mantenimiento automático completado', 'MAINTENANCE');
}

// =====================================================
// INICIALIZACIÓN PRINCIPAL
// =====================================================

// Aplicar medidas de seguridad
aplicar_cabeceras_seguridad();
verificar_ip_bloqueada();
verificar_rate_limit();

// Verificar estado del sistema
$sistema_ok = verificar_sistema();

// Ejecutar mantenimiento automático (solo una vez por día)
if ($sistema_ok) {
    ejecutar_mantenimiento_automatico();
}

// Registrar inicio del sistema
escribir_log('Sistema Motor Service v' . SISTEMA_VERSION . ' inicializado correctamente', 'INIT');

// =====================================================
// VARIABLES GLOBALES DEL SISTEMA
// =====================================================

// Información del sistema disponible globalmente
$GLOBALS['motor_service'] = [
    'version' => SISTEMA_VERSION,
    'fecha_inicio' => date('Y-m-d H:i:s'),
    'sistema_ok' => $sistema_ok,
    'desarrollo' => DESARROLLO,
    'usuario_logueado' => isset($_SESSION['validarIngreso']) && $_SESSION['validarIngreso'] === 'ok'
];

// =====================================================
// HELPERS PARA VISTAS
// =====================================================

/**
 * Incluir archivo de vista de forma segura
 */
function incluir_vista($archivo) {
    $ruta_completa = VIEW_PATH . $archivo;
    
    if (file_exists($ruta_completa)) {
        include $ruta_completa;
    } else {
        escribir_log("Vista no encontrada: $archivo", 'ERROR');
        if (DESARROLLO) {
            echo "<div style='background: #fef2f2; border: 1px solid #fca5a5; color: #dc2626; padding: 10px; margin: 10px; border-radius: 4px;'>";
            echo "<strong>Error:</strong> No se pudo cargar la vista: $archivo";
            echo "</div>";
        } else {
            mostrar_pagina_error();
        }
    }
}

/**
 * Incluir archivo CSS/JS de forma segura
 */
function incluir_asset($archivo, $tipo = 'css') {
    $ruta = "../assets/$tipo/$archivo";
    
    if ($tipo === 'css') {
        echo "<link rel='stylesheet' href='$ruta'>";
    } elseif ($tipo === 'js') {
        echo "<script src='$ruta'></script>";
    }
}

// =====================================================
// FUNCIONES DE DEBUG PARA DESARROLLO
// =====================================================

if (DESARROLLO) {
    /**
     * Mostrar información de debug al final de la página
     */
    function mostrar_debug_info() {
        if (!isset($_GET['debug'])) {
            return;
        }
        
        $memoria = obtener_uso_memoria();
        $tiempo_ejecucion = round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2);
        
        echo '<div style="background: #f8f9fa; border-top: 3px solid #dc2626; padding: 15px; margin-top: 20px; font-family: monospace; font-size: 12px;">';
        echo '<h4 style="margin: 0 0 10px 0; color: #dc2626;">Información de Debug - Motor Service</h4>';
        echo '<p><strong>Tiempo de ejecución:</strong> ' . $tiempo_ejecucion . 'ms</p>';
        echo '<p><strong>Memoria utilizada:</strong> ' . $memoria['uso_actual'] . ' (Pico: ' . $memoria['uso_pico'] . ')</p>';
        echo '<p><strong>Sesión activa:</strong> ' . (isset($_SESSION['validarIngreso']) ? 'Sí' : 'No') . '</p>';
        echo '<p><strong>Usuario:</strong> ' . ($_SESSION['usuario'] ?? 'No logueado') . '</p>';
        echo '<p><strong>Tipo usuario:</strong> ' . ($_SESSION['tipo_usuario'] ?? 'N/A') . '</p>';
        echo '<p><strong>IP:</strong> ' . ($_SERVER['REMOTE_ADDR'] ?? 'N/A') . '</p>';
        echo '<p><strong>User Agent:</strong> ' . ($_SERVER['HTTP_USER_AGENT'] ?? 'N/A') . '</p>';
        echo '</div>';
    }
    
    // Registrar función para mostrar al final
    register_shutdown_function('mostrar_debug_info');
}

// Marcar que la inicialización está completa
define('MOTOR_SERVICE_READY', true);
?>