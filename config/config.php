<?php
/**
 * CONFIGURACIÓN GENERAL DEL SISTEMA MOTOR SERVICE
 * 
 * Este archivo contiene todas las configuraciones principales del sistema
 * de gestión de taller automotriz Motor Service.
 * 
 * @author Motor Service Team
 * @version 2.0
 * @since 2024
 */

// Prevenir acceso directo
if (!defined('MOTOR_SERVICE_SYSTEM')) {
    define('MOTOR_SERVICE_SYSTEM', true);
}

// Configuración de errores para desarrollo/producción
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Configuración de zona horaria
date_default_timezone_set('America/Asuncion');

// =============================================================================
// CONFIGURACIÓN DE BASE DE DATOS
// =============================================================================

define('DB_CONFIG', [
    'host' => 'localhost',
    'port' => '3306',
    'database' => 'tesis_taller',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]
]);

// =============================================================================
// CONFIGURACIÓN DEL SISTEMA
// =============================================================================

define('SYSTEM_CONFIG', [
    'name' => 'Motor Service',
    'version' => '2.0.0',
    'description' => 'Sistema de Gestión de Taller Automotriz',
    'url' => 'http://localhost/motor-service',
    'admin_email' => 'admin@motorservicepy.com',
    'support_email' => 'soporte@motorservicepy.com',
    'company' => [
        'name' => 'Motor Service',
        'ruc' => '80012345-1',
        'address' => 'Asunción, Paraguay',
        'phone' => '(0984) 800 586',
        'email' => 'info@motorservicepy.com',
        'website' => 'www.motorservicepy.com'
    ]
]);

// =============================================================================
// CONFIGURACIÓN DE SEGURIDAD
// =============================================================================

define('SECURITY_CONFIG', [
    'session_name' => 'MOTOR_SERVICE_SESSION',
    'session_lifetime' => 3600, // 1 hora
    'password_min_length' => 6,
    'max_login_attempts' => 5,
    'lockout_duration' => 900, // 15 minutos
    'csrf_token_lifetime' => 1800, // 30 minutos
    'secure_cookies' => false, // Cambiar a true en HTTPS
    'encryption_key' => 'motor_service_2024_key_change_in_production',
    'allowed_file_types' => [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
        'backup' => ['sql', 'zip']
    ],
    'max_file_size' => 5242880, // 5MB
]);

// =============================================================================
// CONFIGURACIÓN DE APLICACIÓN
// =============================================================================

define('APP_CONFIG', [
    'maintenance_mode' => false,
    'debug_mode' => true, // Cambiar a false en producción
    'log_level' => 'DEBUG', // DEBUG, INFO, WARNING, ERROR
    'cache_enabled' => false,
    'backup_enabled' => true,
    'notifications_enabled' => true,
    'pagination' => [
        'per_page' => 25,
        'max_per_page' => 100
    ],
    'uploads' => [
        'path' => __DIR__ . '/../uploads/',
        'url' => '/uploads/',
        'temp_path' => __DIR__ . '/../temp/'
    ],
    'logs' => [
        'path' => __DIR__ . '/../logs/',
        'max_files' => 30, // Mantener logs por 30 días
        'max_size' => 10485760 // 10MB por archivo
    ]
]);

// =============================================================================
// CONFIGURACIÓN DE FACTURACIÓN
// =============================================================================

define('BILLING_CONFIG', [
    'currency' => 'PYG',
    'currency_symbol' => '₲',
    'tax_rate' => 10, // IVA 10%
    'invoice_prefix' => '001-',
    'invoice_series' => '001',
    'timbrado' => [
        'number' => '12345678',
        'expiry_date' => '2025-12-31'
    ],
    'payment_methods' => [
        'efectivo' => 'Efectivo',
        'tarjeta' => 'Tarjeta de Crédito/Débito',
        'transferencia' => 'Transferencia Bancaria',
        'cheque' => 'Cheque'
    ]
]);

// =============================================================================
// CONFIGURACIÓN DE EMAIL
// =============================================================================

define('EMAIL_CONFIG', [
    'enabled' => false, // Cambiar a true cuando se configure SMTP
    'smtp' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'encryption' => 'tls',
        'username' => 'tu-email@gmail.com',
        'password' => 'tu-password-de-aplicacion'
    ],
    'from' => [
        'email' => 'no-reply@motorservicepy.com',
        'name' => 'Motor Service'
    ],
    'templates' => [
        'path' => __DIR__ . '/../templates/email/',
        'cache_path' => __DIR__ . '/../cache/email/'
    ]
]);

// =============================================================================
// CONFIGURACIÓN DE MÓDULOS
// =============================================================================

define('MODULES_CONFIG', [
    'clientes' => [
        'enabled' => true,
        'auto_create_user' => false,
        'required_fields' => ['nombre', 'apellido', 'cedula']
    ],
    'vehiculos' => [
        'enabled' => true,
        'auto_generate_qr' => false,
        'required_fields' => ['matricula', 'marca', 'modelo', 'anho']
    ],
    'ordenes' => [
        'enabled' => true,
        'auto_number' => true,
        'require_signature' => false,
        'max_services' => 20
    ],
    'presupuestos' => [
        'enabled' => true,
        'validity_days' => 30,
        'auto_expire' => true
    ],
    'facturacion' => [
        'enabled' => true,
        'auto_number' => true,
        'require_timbrado' => true
    ],
    'inventario' => [
        'enabled' => true,
        'track_stock' => true,
        'low_stock_alert' => 5
    ],
    'reportes' => [
        'enabled' => true,
        'cache_duration' => 300, // 5 minutos
        'export_formats' => ['pdf', 'excel', 'csv']
    ]
]);

// =============================================================================
// CONFIGURACIÓN DE NOTIFICACIONES
// =============================================================================

define('NOTIFICATIONS_CONFIG', [
    'types' => [
        'cita_pendiente' => [
            'enabled' => true,
            'email' => false,
            'sms' => false,
            'system' => true
        ],
        'orden_completada' => [
            'enabled' => true,
            'email' => true,
            'sms' => false,
            'system' => true
        ],
        'factura_generada' => [
            'enabled' => true,
            'email' => true,
            'sms' => false,
            'system' => true
        ],
        'stock_bajo' => [
            'enabled' => true,
            'email' => true,
            'sms' => false,
            'system' => true
        ]
    ],
    'delivery' => [
        'batch_size' => 50,
        'retry_attempts' => 3,
        'retry_delay' => 300 // 5 minutos
    ]
]);

// =============================================================================
// CONFIGURACIÓN DE BACKUP
// =============================================================================

define('BACKUP_CONFIG', [
    'enabled' => true,
    'auto_backup' => true,
    'frequency' => 'daily', // daily, weekly, monthly
    'retention_days' => 30,
    'path' => __DIR__ . '/../backups/',
    'tables' => [
        'include_all' => true,
        'exclude' => ['logs', 'sessions']
    ],
    'compression' => true,
    'encryption' => false
]);

// =============================================================================
// RUTAS DEL SISTEMA
// =============================================================================

define('PATHS', [
    'root' => dirname(__DIR__),
    'app' => dirname(__DIR__) . '/app',
    'public' => dirname(__DIR__) . '/public',
    'storage' => dirname(__DIR__) . '/storage',
    'uploads' => dirname(__DIR__) . '/uploads',
    'logs' => dirname(__DIR__) . '/logs',
    'cache' => dirname(__DIR__) . '/cache',
    'backups' => dirname(__DIR__) . '/backups',
    'temp' => dirname(__DIR__) . '/temp'
]);

// =============================================================================
// URLS DEL SISTEMA
// =============================================================================

define('URLS', [
    'base' => SYSTEM_CONFIG['url'],
    'assets' => SYSTEM_CONFIG['url'] . '/assets',
    'uploads' => SYSTEM_CONFIG['url'] . '/uploads',
    'api' => SYSTEM_CONFIG['url'] . '/api'
]);

// =============================================================================
// FUNCIONES AUXILIARES
// =============================================================================

/**
 * Obtiene una configuración por su clave
 */
function getConfig($key, $default = null) {
    $configs = [
        'db' => DB_CONFIG,
        'system' => SYSTEM_CONFIG,
        'security' => SECURITY_CONFIG,
        'app' => APP_CONFIG,
        'billing' => BILLING_CONFIG,
        'email' => EMAIL_CONFIG,
        'modules' => MODULES_CONFIG,
        'notifications' => NOTIFICATIONS_CONFIG,
        'backup' => BACKUP_CONFIG
    ];
    
    if (strpos($key, '.') !== false) {
        $keys = explode('.', $key);
        $config = $configs;
        
        foreach ($keys as $k) {
            if (isset($config[$k])) {
                $config = $config[$k];
            } else {
                return $default;
            }
        }
        
        return $config;
    }
    
    return isset($configs[$key]) ? $configs[$key] : $default;
}

/**
 * Verifica si un módulo está habilitado
 */
function isModuleEnabled($module) {
    return getConfig("modules.{$module}.enabled", false);
}

/**
 * Obtiene la configuración de la base de datos
 */
function getDatabaseConfig() {
    return DB_CONFIG;
}

/**
 * Verifica si el sistema está en modo mantenimiento
 */
function isMaintenanceMode() {
    return getConfig('app.maintenance_mode', false);
}

/**
 * Verifica si el modo debug está activado
 */
function isDebugMode() {
    return getConfig('app.debug_mode', false);
}

/**
 * Formatea un monto de dinero
 */
function formatMoney($amount, $includeCurrency = true) {
    $formatted = number_format($amount, 0, ',', '.');
    return $includeCurrency ? BILLING_CONFIG['currency_symbol'] . ' ' . $formatted : $formatted;
}

/**
 * Obtiene la configuración de paginación
 */
function getPaginationConfig() {
    return getConfig('app.pagination', ['per_page' => 25, 'max_per_page' => 100]);
}

/**
 * Valida si un tipo de archivo está permitido
 */
function isFileTypeAllowed($extension, $category = null) {
    $allowedTypes = SECURITY_CONFIG['allowed_file_types'];
    
    if ($category && isset($allowedTypes[$category])) {
        return in_array(strtolower($extension), $allowedTypes[$category]);
    }
    
    // Si no se especifica categoría, buscar en todas
    foreach ($allowedTypes as $types) {
        if (in_array(strtolower($extension), $types)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Genera un token CSRF
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Valida un token CSRF
 */
function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
        return false;
    }
    
    // Verificar expiración
    if (time() - $_SESSION['csrf_token_time'] > SECURITY_CONFIG['csrf_token_lifetime']) {
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Crear directorios necesarios si no existen
 */
function createRequiredDirectories() {
    $directories = [
        PATHS['storage'],
        PATHS['uploads'],
        PATHS['logs'],
        PATHS['cache'],
        PATHS['backups'],
        PATHS['temp']
    ];
    
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            
            // Crear archivo .htaccess para proteger directorios sensibles
            if (in_array($dir, [PATHS['logs'], PATHS['cache'], PATHS['backups']])) {
                file_put_contents($dir . '/.htaccess', "Deny from all\n");
            }
        }
    }
}

/**
 * Inicializar configuración del sistema
 */
function initializeSystem() {
    // Configurar sesión
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SECURITY_CONFIG['session_name']);
        session_set_cookie_params([
            'lifetime' => SECURITY_CONFIG['session_lifetime'],
            'path' => '/',
            'secure' => SECURITY_CONFIG['secure_cookies'],
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        session_start();
    }
    
    // Crear directorios necesarios
    createRequiredDirectories();
    
    // Verificar modo mantenimiento
    if (isMaintenanceMode() && !isset($_SESSION['admin_override'])) {
        // Mostrar página de mantenimiento
        include PATHS['root'] . '/maintenance.php';
        exit;
    }
    
    // Limpiar archivos temporales antiguos
    cleanupTempFiles();
}

/**
 * Limpiar archivos temporales antiguos
 */
function cleanupTempFiles() {
    $tempDir = PATHS['temp'];
    if (is_dir($tempDir)) {
        $files = glob($tempDir . '/*');
        $now = time();
        
        foreach ($files as $file) {
            if (is_file($file) && ($now - filemtime($file)) > 3600) { // 1 hora
                unlink($file);
            }
        }
    }
}

// =============================================================================
// INICIALIZACIÓN
// =============================================================================

// Solo inicializar si no estamos en CLI
if (php_sapi_name() !== 'cli') {
    initializeSystem();
}

// Registrar función de cierre para cleanup
register_shutdown_function(function() {
    // Cualquier cleanup necesario al cerrar el script
});

?>