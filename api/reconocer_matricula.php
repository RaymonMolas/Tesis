<?php
/**
 * API de Reconocimiento de Matrículas con IA
 * Motor Service - Sistema de Gestión Automotriz
 * 
 * Esta API utiliza Tesseract OCR para reconocer matrículas paraguayas
 * automáticamente desde imágenes capturadas por cámara
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false, 
        'error' => 'Método no permitido. Use POST.',
        'debug' => 'HTTP_METHOD_NOT_ALLOWED'
    ]);
    exit;
}

// Verificar que se recibió una imagen
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
        'success' => false, 
        'error' => 'No se recibió imagen válida',
        'debug' => 'IMAGE_UPLOAD_ERROR'
    ]);
    exit;
}

try {
    $uploadedFile = $_FILES['image'];
    $tempPath = $uploadedFile['tmp_name'];
    
    // Validar que es una imagen
    $imageInfo = getimagesize($tempPath);
    if (!$imageInfo) {
        echo json_encode([
            'success' => false, 
            'error' => 'El archivo no es una imagen válida',
            'debug' => 'INVALID_IMAGE_FORMAT'
        ]);
        exit;
    }
    
    // Verificar tamaño del archivo (máximo 5MB)
    if ($uploadedFile['size'] > 5 * 1024 * 1024) {
        echo json_encode([
            'success' => false, 
            'error' => 'La imagen es demasiado grande (máximo 5MB)',
            'debug' => 'IMAGE_TOO_LARGE'
        ]);
        exit;
    }
    
    // Log para debugging
    error_log("Motor Service IA: Procesando imagen - Tamaño: " . $uploadedFile['size'] . " bytes, Tipo: " . $imageInfo['mime']);
    
    // Procesar imagen con diferentes métodos
    $resultado = processImageWithMultipleEngines($tempPath);
    
    if ($resultado['success']) {
        echo json_encode([
            'success' => true,
            'matricula' => $resultado['matricula'],
            'confidence' => $resultado['confidence'],
            'processing_time' => $resultado['processing_time'],
            'engine_used' => $resultado['engine_used']
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'error' => 'No se pudo reconocer la matrícula en la imagen',
            'debug' => $resultado['debug'] ?? 'RECOGNITION_FAILED',
            'suggestions' => [
                'Asegúrese de que la matrícula esté bien iluminada',
                'Evite reflejos y sombras en la matrícula',
                'La matrícula debe estar completamente visible',
                'Intente con una imagen más nítida'
            ]
        ]);
    }
    
} catch (Exception $e) {
    error_log('Motor Service IA Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'error' => 'Error interno del servidor de reconocimiento',
        'debug' => 'INTERNAL_SERVER_ERROR'
    ]);
}

/**
 * Procesar imagen con múltiples engines de reconocimiento
 */
function processImageWithMultipleEngines($imagePath) {
    $startTime = microtime(true);
    
    // Intentar con Tesseract OCR (método principal)
    $result = processWithTesseract($imagePath);
    if ($result['success']) {
        $result['processing_time'] = round((microtime(true) - $startTime) * 1000) . 'ms';
        $result['engine_used'] = 'tesseract';
        return $result;
    }
    
    // Fallback: Análisis básico de patrones
    $result = processWithPatternMatching($imagePath);
    if ($result['success']) {
        $result['processing_time'] = round((microtime(true) - $startTime) * 1000) . 'ms';
        $result['engine_used'] = 'pattern_matching';
        return $result;
    }
    
    // Fallback: OCR web service (si está disponible)
    $result = processWithWebOCR($imagePath);
    $result['processing_time'] = round((microtime(true) - $startTime) * 1000) . 'ms';
    $result['engine_used'] = $result['success'] ? 'web_ocr' : 'none';
    
    return $result;
}

/**
 * Procesar con Tesseract OCR
 */
function processWithTesseract($imagePath) {
    // Verificar si Tesseract está instalado
    $tesseractPath = exec('which tesseract 2>/dev/null');
    if (empty($tesseractPath)) {
        return [
            'success' => false,
            'debug' => 'TESSERACT_NOT_INSTALLED'
        ];
    }
    
    try {
        // Preprocesar imagen para mejor reconocimiento
        $processedImage = preprocessImage($imagePath);
        
        // Configurar Tesseract para reconocimiento de matrículas paraguayas
        $tesseractConfig = '--oem 3 --psm 8 -c tessedit_char_whitelist=ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        
        // Ejecutar Tesseract
        $command = "tesseract " . escapeshellarg($processedImage) . " stdout $tesseractConfig 2>/dev/null";
        $output = shell_exec($command);
        
        // Limpiar archivo temporal si se creó uno
        if ($processedImage !== $imagePath && file_exists($processedImage)) {
            unlink($processedImage);
        }
        
        if (!$output) {
            return [
                'success' => false,
                'debug' => 'TESSERACT_NO_OUTPUT'
            ];
        }
        
        // Limpiar y validar resultado
        $cleanText = cleanAndValidateText($output);
        
        if ($cleanText) {
            return [
                'success' => true,
                'matricula' => $cleanText,
                'confidence' => 85,
                'raw_output' => $output
            ];
        }
        
        return [
            'success' => false,
            'debug' => 'TESSERACT_INVALID_FORMAT',
            'raw_output' => $output
        ];
        
    } catch (Exception $e) {
        error_log('Tesseract Error: ' . $e->getMessage());
        return [
            'success' => false,
            'debug' => 'TESSERACT_EXCEPTION'
        ];
    }
}

/**
 * Preprocesar imagen para mejor reconocimiento
 */
function preprocessImage($imagePath) {
    // Si GD no está disponible, usar imagen original
    if (!extension_loaded('gd')) {
        return $imagePath;
    }
    
    try {
        // Cargar imagen
        $imageInfo = getimagesize($imagePath);
        switch ($imageInfo[2]) {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($imagePath);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($imagePath);
                break;
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($imagePath);
                break;
            default:
                return $imagePath;
        }
        
        if (!$image) {
            return $imagePath;
        }
        
        // Obtener dimensiones originales
        $width = imagesx($image);
        $height = imagesy($image);
        
        // Redimensionar si es muy grande (máximo 1920px de ancho)
        if ($width > 1920) {
            $newWidth = 1920;
            $newHeight = intval($height * ($newWidth / $width));
            
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $resized;
        }
        
        // Convertir a escala de grises para mejor OCR
        imagefilter($image, IMG_FILTER_GRAYSCALE);
        
        // Aumentar contraste
        imagefilter($image, IMG_FILTER_CONTRAST, -30);
        
        // Guardar imagen procesada temporalmente
        $tempFile = sys_get_temp_dir() . '/matricula_processed_' . uniqid() . '.jpg';
        imagejpeg($image, $tempFile, 90);
        imagedestroy($image);
        
        return $tempFile;
        
    } catch (Exception $e) {
        error_log('Image preprocessing error: ' . $e->getMessage());
        return $imagePath;
    }
}

/**
 * Análisis básico de patrones (fallback)
 */
function processWithPatternMatching($imagePath) {
    // Este es un método básico que busca patrones comunes
    // en matrículas paraguayas usando análisis de píxeles
    
    try {
        // Simular procesamiento básico
        // En una implementación real, aquí iría análisis de píxeles
        // buscando patrones rectangulares típicos de matrículas
        
        // Por ahora, retornamos false para forzar el uso de otros métodos
        return [
            'success' => false,
            'debug' => 'PATTERN_MATCHING_NOT_IMPLEMENTED'
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'debug' => 'PATTERN_MATCHING_ERROR'
        ];
    }
}

/**
 * OCR web service (fallback)
 */
function processWithWebOCR($imagePath) {
    // Aquí se podría integrar con servicios como:
    // - Google Vision API
    // - Azure Computer Vision
    // - AWS Textract
    // - OCR.space API
    
    return [
        'success' => false,
        'debug' => 'WEB_OCR_NOT_CONFIGURED'
    ];
}

/**
 * Limpiar y validar texto reconocido
 */
function cleanAndValidateText($text) {
    // Limpiar texto
    $cleanText = trim(preg_replace('/[^A-Z0-9]/', '', strtoupper($text)));
    
    // Patrones de matrículas paraguayas
    $patterns = [
        '/^[A-Z]{3}[0-9]{3}$/',     // ABC123 (formato estándar)
        '/^[A-Z]{3}[0-9]{4}$/',     // ABC1234 (formato nuevo)
        '/^[A-Z]{2}[0-9]{4}$/',     // AB1234 (formato alternativo)
        '/^[A-Z]{4}[0-9]{2}$/',     // ABCD12 (formato especial)
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $cleanText)) {
            return $cleanText;
        }
    }
    
    // Si no coincide con patrones exactos, intentar extraer
    // la parte más probable de ser una matrícula
    if (strlen($cleanText) >= 5 && strlen($cleanText) <= 8) {
        // Buscar combinaciones de letras seguidas de números
        if (preg_match('/([A-Z]{2,4})([0-9]{2,4})/', $cleanText, $matches)) {
            $candidate = $matches[1] . $matches[2];
            if (strlen($candidate) >= 5 && strlen($candidate) <= 7) {
                return $candidate;
            }
        }
    }
    
    return false;
}

/**
 * Función de utilidad para logging
 */
function logProcessing($message, $data = null) {
    $logMessage = "[Motor Service IA] " . $message;
    if ($data) {
        $logMessage .= " - Data: " . json_encode($data);
    }
    error_log($logMessage);
}

/**
 * Generar respuesta de demostración (para testing sin Tesseract)
 */
function generateDemoResponse() {
    // Esta función se puede usar para testing cuando Tesseract no esté instalado
    $demoMatriculas = ['ABC123', 'DEF456', 'GHI789', 'JKL012', 'MNO345'];
    
    return [
        'success' => true,
        'matricula' => $demoMatriculas[array_rand($demoMatriculas)],
        'confidence' => rand(75, 95),
        'processing_time' => rand(800, 1500) . 'ms',
        'engine_used' => 'demo_mode',
        'note' => 'Esta es una respuesta de demostración. Instale Tesseract OCR para reconocimiento real.'
    ];
}

// Descomentar esta línea para usar modo demo
// echo json_encode(generateDemoResponse());
?>