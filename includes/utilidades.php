<?php
/**
 * Utilidades del Sistema Motor Service
 * Funciones comunes utilizadas en todo el sistema
 */

// Evitar acceso directo
if (!defined('MOTOR_SERVICE_INIT')) {
    die('Acceso directo no permitido');
}

// =====================================================
// FUNCIONES DE VALIDACIÓN
// =====================================================

/**
 * Validar cédula paraguaya
 */
function validar_cedula($cedula) {
    // Remover puntos y espacios
    $cedula = preg_replace('/[^0-9]/', '', $cedula);
    
    // Debe tener entre 6 y 8 dígitos
    if (strlen($cedula) < 6 || strlen($cedula) > 8) {
        return false;
    }
    
    return is_numeric($cedula);
}

/**
 * Validar matrícula de vehículo
 */
function validar_matricula($matricula) {
    // Formato básico: letras y números
    $matricula = strtoupper(trim($matricula));
    
    if (strlen($matricula) < 3 || strlen($matricula) > 10) {
        return false;
    }
    
    return preg_match('/^[A-Z0-9]+$/', $matricula);
}

/**
 * Validar código de producto
 */
function validar_codigo_producto($codigo) {
    $codigo = strtoupper(trim($codigo));
    
    if (strlen($codigo) < 3 || strlen($codigo) > 20) {
        return false;
    }
    
    return preg_match('/^[A-Z0-9_-]+$/', $codigo);
}

/**
 * Validar precio
 */
function validar_precio($precio) {
    if (!is_numeric($precio)) {
        return false;
    }
    
    return floatval($precio) >= 0;
}

/**
 * Validar fecha
 */
function validar_fecha($fecha, $formato = 'Y-m-d') {
    $d = DateTime::createFromFormat($formato, $fecha);
    return $d && $d->format($formato) === $fecha;
}

/**
 * Validar hora
 */
function validar_hora($hora) {
    return preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $hora);
}

// =====================================================
// FUNCIONES DE FORMATO
// =====================================================

/**
 * Formatear número con separadores
 */
function formatear_numero($numero, $decimales = 0) {
    return number_format($numero, $decimales, SEPARADOR_DECIMALES, SEPARADOR_MILES);
}

/**
 * Formatear cédula con puntos
 */
function formatear_cedula($cedula) {
    $cedula = preg_replace('/[^0-9]/', '', $cedula);
    
    if (strlen($cedula) >= 6) {
        return number_format($cedula, 0, '', '.');
    }
    
    return $cedula;
}

/**
 * Formatear RUC
 */
function formatear_ruc($ruc) {
    $ruc = preg_replace('/[^0-9-]/', '', $ruc);
    
    if (strlen($ruc) >= 8 && strpos($ruc, '-') === false) {
        $base = substr($ruc, 0, -1);
        $dv = substr($ruc, -1);
        return $base . '-' . $dv;
    }
    
    return $ruc;
}

/**
 * Formatear teléfono
 */
function formatear_telefono($telefono) {
    $telefono = preg_replace('/[^0-9]/', '', $telefono);
    
    // Formato: (0XXX) XXX-XXX
    if (strlen($telefono) == 10 && substr($telefono, 0, 1) == '0') {
        return '(' . substr($telefono, 0, 4) . ') ' . 
               substr($telefono, 4, 3) . '-' . 
               substr($telefono, 7, 3);
    }
    
    return $telefono;
}

/**
 * Formatear nombre propio
 */
function formatear_nombre($nombre) {
    return ucwords(strtolower(trim($nombre)));
}

/**
 * Formatear tiempo transcurrido
 */
function tiempo_transcurrido($fecha) {
    $timestamp = is_numeric($fecha) ? $fecha : strtotime($fecha);
    $diferencia = time() - $timestamp;
    
    if ($diferencia < 60) {
        return 'hace unos segundos';
    } elseif ($diferencia < 3600) {
        $minutos = floor($diferencia / 60);
        return "hace $minutos minuto" . ($minutos > 1 ? 's' : '');
    } elseif ($diferencia < 86400) {
        $horas = floor($diferencia / 3600);
        return "hace $horas hora" . ($horas > 1 ? 's' : '');
    } elseif ($diferencia < 2592000) {
        $dias = floor($diferencia / 86400);
        return "hace $dias día" . ($dias > 1 ? 's' : '');
    } else {
        return formatear_fecha($fecha);
    }
}

// =====================================================
// FUNCIONES DE SEGURIDAD
// =====================================================

/**
 * Sanitizar entrada de datos
 */
function sanitizar($datos) {
    if (is_array($datos)) {
        return array_map('sanitizar', $datos);
    }
    
    return htmlspecialchars(trim($datos), ENT_QUOTES, 'UTF-8');
}

/**
 * Generar hash seguro
 */
function generar_hash($datos) {
    return hash('sha256', $datos . time() . rand());
}

/**
 * Verificar CSRF token
 */
function verificar_csrf($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generar CSRF token
 */
function generar_csrf() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

// =====================================================
// FUNCIONES DE ARCHIVO
// =====================================================

/**
 * Validar tipo de archivo
 */
function validar_tipo_archivo($archivo, $tipos_permitidos) {
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    return in_array($extension, $tipos_permitidos);
}

/**
 * Validar tamaño de archivo
 */
function validar_tamano_archivo($archivo, $tamano_max = null) {
    $tamano_max = $tamano_max ?: MAX_FILE_SIZE;
    return $archivo['size'] <= $tamano_max;
}

/**
 * Generar nombre único para archivo
 */
function generar_nombre_archivo($archivo, $prefijo = '') {
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    return $prefijo . uniqid() . '_' . time() . '.' . $extension;
}

/**
 * Subir archivo de forma segura
 */
function subir_archivo($archivo, $directorio, $tipos_permitidos, $prefijo = '') {
    // Validaciones
    if (!validar_tipo_archivo($archivo, $tipos_permitidos)) {
        return ['error' => 'Tipo de archivo no permitido'];
    }
    
    if (!validar_tamano_archivo($archivo)) {
        return ['error' => 'Archivo demasiado grande'];
    }
    
    // Crear directorio si no existe
    if (!is_dir($directorio)) {
        mkdir($directorio, 0755, true);
    }
    
    // Generar nombre único
    $nombre_archivo = generar_nombre_archivo($archivo, $prefijo);
    $ruta_completa = $directorio . $nombre_archivo;
    
    // Mover archivo
    if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
        return ['success' => true, 'archivo' => $nombre_archivo, 'ruta' => $ruta_completa];
    }
    
    return ['error' => 'Error al subir el archivo'];
}

// =====================================================
// FUNCIONES DE BASE DE DATOS
// =====================================================

/**
 * Escapar datos para SQL
 */
function escapar_sql($datos) {
    if (is_array($datos)) {
        return array_map('escapar_sql', $datos);
    }
    
    return addslashes(trim($datos));
}

/**
 * Generar WHERE dinámico
 */
function generar_where($filtros, $operador = 'AND') {
    if (empty($filtros)) {
        return '';
    }
    
    $condiciones = [];
    foreach ($filtros as $campo => $valor) {
        if ($valor !== '' && $valor !== null) {
            if (is_array($valor)) {
                $valores = implode("','", array_map('escapar_sql', $valor));
                $condiciones[] = "$campo IN ('$valores')";
            } else {
                $valor_escapado = escapar_sql($valor);
                $condiciones[] = "$campo = '$valor_escapado'";
            }
        }
    }
    
    return empty($condiciones) ? '' : 'WHERE ' . implode(" $operador ", $condiciones);
}

/**
 * Generar ORDER BY dinámico
 */
function generar_order_by($orden, $direccion = 'ASC') {
    if (empty($orden)) {
        return '';
    }
    
    $direccion = strtoupper($direccion) === 'DESC' ? 'DESC' : 'ASC';
    return "ORDER BY $orden $direccion";
}

/**
 * Generar LIMIT dinámico
 */
function generar_limit($pagina = 1, $por_pagina = null) {
    $por_pagina = $por_pagina ?: REGISTROS_POR_PAGINA;
    $offset = ($pagina - 1) * $por_pagina;
    
    return "LIMIT $offset, $por_pagina";
}

// =====================================================
// FUNCIONES DE NOTIFICACIÓN
// =====================================================

/**
 * Crear notificación del sistema
 */
function crear_notificacion($tipo, $titulo, $mensaje, $usuario_id = null, $datos_extra = null) {
    if (!NOTIFICACIONES_HABILITADAS) {
        return false;
    }
    
    $notificacion = [
        'tipo' => $tipo,
        'titulo' => $titulo,
        'mensaje' => $mensaje,
        'usuario_id' => $usuario_id,
        'datos_extra' => $datos_extra ? json_encode($datos_extra) : null,
        'fecha_creacion' => date('Y-m-d H:i:s'),
        'leida' => false
    ];
    
    // Aquí se implementaría la lógica para guardar en BD
    escribir_log("Notificación creada: $tipo - $titulo", 'INFO');
    
    return true;
}

/**
 * Enviar email si está configurado
 */
function enviar_email($destinatario, $asunto, $mensaje, $es_html = true) {
    if (!EMAIL_NOTIFICACIONES) {
        return false;
    }
    
    // Aquí se implementaría el envío real de email
    escribir_log("Email enviado a $destinatario: $asunto", 'INFO');
    
    return true;
}

// =====================================================
// FUNCIONES DE CACHE
// =====================================================

/**
 * Obtener del cache
 */
function obtener_cache($clave) {
    if (!CACHE_HABILITADO) {
        return false;
    }
    
    $archivo = CACHE_PATH . md5($clave) . '.cache';
    
    if (!file_exists($archivo)) {
        return false;
    }
    
    $contenido = file_get_contents($archivo);
    $datos = unserialize($contenido);
    
    // Verificar si expiró
    if ($datos['expiracion'] < time()) {
        unlink($archivo);
        return false;
    }
    
    return $datos['contenido'];
}

/**
 * Guardar en cache
 */
function guardar_cache($clave, $contenido, $tiempo_vida = null) {
    if (!CACHE_HABILITADO) {
        return false;
    }
    
    $tiempo_vida = $tiempo_vida ?: CACHE_LIFETIME;
    $archivo = CACHE_PATH . md5($clave) . '.cache';
    
    $datos = [
        'contenido' => $contenido,
        'expiracion' => time() + $tiempo_vida
    ];
    
    if (!is_dir(CACHE_PATH)) {
        mkdir(CACHE_PATH, 0755, true);
    }
    
    return file_put_contents($archivo, serialize($datos), LOCK_EX) !== false;
}

/**
 * Limpiar cache
 */
function limpiar_cache($patron = '*') {
    if (!CACHE_HABILITADO) {
        return false;
    }
    
    $archivos = glob(CACHE_PATH . $patron . '.cache');
    $eliminados = 0;
    
    foreach ($archivos as $archivo) {
        if (unlink($archivo)) {
            $eliminados++;
        }
    }
    
    return $eliminados;
}

// =====================================================
// FUNCIONES DE EXPORTACIÓN
// =====================================================

/**
 * Exportar datos a CSV
 */
function exportar_csv($datos, $nombre_archivo, $cabeceras = null) {
    if (empty($datos)) {
        return false;
    }
    
    $ruta_archivo = EXPORT_PATH . $nombre_archivo . '_' . date('Y-m-d_H-i-s') . '.csv';
    
    if (!is_dir(EXPORT_PATH)) {
        mkdir(EXPORT_PATH, 0755, true);
    }
    
    $archivo = fopen($ruta_archivo, 'w');
    
    // Escribir BOM para UTF-8
    fprintf($archivo, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Escribir cabeceras
    if ($cabeceras) {
        fputcsv($archivo, $cabeceras);
    } elseif (!empty($datos)) {
        fputcsv($archivo, array_keys($datos[0]));
    }
    
    // Escribir datos
    foreach ($datos as $fila) {
        fputcsv($archivo, $fila);
    }
    
    fclose($archivo);
    
    return $ruta_archivo;
}

/**
 * Generar PDF básico
 */
function generar_pdf_basico($titulo, $contenido, $nombre_archivo) {
    // Esta función requeriría una librería como TCPDF
    // Por ahora solo creamos un archivo HTML que se puede imprimir
    
    $html = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>$titulo</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            h1 { color: #dc2626; border-bottom: 2px solid #dc2626; padding-bottom: 10px; }
            .header { text-align: center; margin-bottom: 30px; }
            .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>$titulo</h1>
            <p>Motor Service - Servicio Integral Automotriz</p>
            <p>Generado el " . date('d/m/Y H:i') . "</p>
        </div>
        
        $contenido
        
        <div class='footer'>
            <p>Este documento fue generado automáticamente por el sistema Motor Service</p>
        </div>
    </body>
    </html>";
    
    $ruta_archivo = EXPORT_PATH . $nombre_archivo . '_' . date('Y-m-d_H-i-s') . '.html';
    
    if (!is_dir(EXPORT_PATH)) {
        mkdir(EXPORT_PATH, 0755, true);
    }
    
    file_put_contents($ruta_archivo, $html);
    
    return $ruta_archivo;
}

// =====================================================
// FUNCIONES DE ESTADÍSTICAS
// =====================================================

/**
 * Calcular porcentaje
 */
function calcular_porcentaje($parte, $total) {
    if ($total == 0) {
        return 0;
    }
    
    return round(($parte / $total) * 100, 2);
}

/**
 * Calcular promedio
 */
function calcular_promedio($valores) {
    if (empty($valores)) {
        return 0;
    }
    
    return array_sum($valores) / count($valores);
}

/**
 * Obtener tendencia (crecimiento/decrecimiento)
 */
function calcular_tendencia($valor_anterior, $valor_actual) {
    if ($valor_anterior == 0) {
        return $valor_actual > 0 ? 100 : 0;
    }
    
    return round((($valor_actual - $valor_anterior) / $valor_anterior) * 100, 2);
}

/**
 * Generar colores para gráficos
 */
function generar_colores($cantidad) {
    $colores = [
        '#dc2626', '#ea580c', '#d97706', '#ca8a04', '#65a30d',
        '#16a34a', '#059669', '#0891b2', '#0284c7', '#2563eb',
        '#4f46e5', '#7c3aed', '#a21caf', '#be185d', '#e11d48'
    ];
    
    $resultado = [];
    for ($i = 0; $i < $cantidad; $i++) {
        $resultado[] = $colores[$i % count($colores)];
    }
    
    return $resultado;
}

// =====================================================
// FUNCIONES DE DEPURACIÓN
// =====================================================

/**
 * Debug variable (solo en desarrollo)
 */
function debug($variable, $salir = false) {
    if (!DESARROLLO) {
        return;
    }
    
    echo '<pre style="background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; margin: 10px; border-radius: 5px;">';
    print_r($variable);
    echo '</pre>';
    
    if ($salir) {
        exit;
    }
}

/**
 * Medir tiempo de ejecución
 */
function cronometrar($callback, $parametros = []) {
    $inicio = microtime(true);
    $resultado = call_user_func_array($callback, $parametros);
    $fin = microtime(true);
    
    $tiempo = round(($fin - $inicio) * 1000, 2); // en milisegundos
    
    if (DESARROLLO) {
        escribir_log("Tiempo de ejecución: {$tiempo}ms", 'DEBUG');
    }
    
    return ['resultado' => $resultado, 'tiempo' => $tiempo];
}

/**
 * Obtener uso de memoria
 */
function obtener_uso_memoria() {
    return [
        'uso_actual' => formatear_bytes(memory_get_usage()),
        'uso_pico' => formatear_bytes(memory_get_peak_usage()),
        'limite' => ini_get('memory_limit')
    ];
}
?>