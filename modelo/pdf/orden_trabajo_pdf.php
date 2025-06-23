<?php
// Incluir archivos necesarios con rutas absolutas corregidas
require_once(__DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php');
require_once(__DIR__ . '/../conexion.php');
require_once(__DIR__ . '/../modelo_orden_trabajo.php');
require_once(__DIR__ . '/../modelo_orden_detalle.php');
require_once(__DIR__ . '/../../controlador/orden_trabajo_controlador.php');

class MYPDF extends TCPDF
{
    public function Header()
    {
        // Fondo del header en rojo corporativo
        $this->SetFillColor(220, 38, 38); // Rojo del logo
        $this->Rect(0, 0, 210, 50, 'F');

        // Logo Motor Service usando la imagen real - RUTA CORREGIDA
        $logo_path = __DIR__ . '/../../img/img-01.jpg';
        if (file_exists($logo_path)) {
            // Usar el logo real
            $this->Image($logo_path, 15, 8, 35, 25, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        } else {
            // Fallback: crear el texto del logo si no se encuentra la imagen
            $this->SetXY(15, 8);
            $this->SetFont('helvetica', '', 9);
            $this->SetTextColor(255, 255, 255);
            $this->Cell(40, 4, 'Servicio Integral', 0, 1, 'C');
            $this->SetX(15);
            $this->Cell(40, 4, 'Automotriz', 0, 1, 'C');

            $this->SetXY(15, 18);
            $this->SetFont('helvetica', 'B', 16);
            $this->SetTextColor(255, 255, 255);
            $this->Cell(40, 8, 'Motor', 0, 0, 'C');
            $this->SetXY(15, 26);
            $this->SetFont('helvetica', 'B', 12);
            $this->SetTextColor(0, 0, 0); // Negro para "Service"
            $this->Cell(40, 6, 'Service', 0, 1, 'C');
        }

        // Título principal
        $this->SetXY(65, 12);
        $this->SetFont('helvetica', 'B', 24);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(80, 15, 'ORDEN DE TRABAJO', 0, 0, 'C');

        // Información de contacto (lado derecho)
        $this->SetXY(150, 8);
        $this->SetFont('helvetica', '', 8);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(45, 4, 'Tel: (0984) 800 586', 0, 1, 'R');
        $this->SetX(150);
        $this->Cell(45, 4, 'www.motorservicepy.com', 0, 1, 'R');
        $this->SetX(150);
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(45, 4, 'Servicio de Calidad', 0, 1, 'R');

        // Línea decorativa
        $this->SetDrawColor(255, 255, 255);
        $this->SetLineWidth(1);
        $this->Line(15, 42, 195, 42);

        // Resetear colores
        $this->SetTextColor(0, 0, 0);
        $this->SetDrawColor(0, 0, 0);
        $this->Ln(18);
    }

    public function Footer()
    {
        $this->SetY(-25);

        // Línea decorativa en rojo
        $this->SetDrawColor(220, 38, 38);
        $this->SetLineWidth(1);
        $this->Line(15, $this->GetY(), 195, $this->GetY());

        // Fondo del footer
        $this->SetFillColor(248, 249, 250);
        $this->Rect(15, $this->GetY() + 2, 180, 15, 'F');

        $this->Ln(5);
        $this->SetFont('helvetica', 'B', 9);
        $this->SetTextColor(220, 38, 38);
        $this->Cell(0, 5, 'MOTOR SERVICE - Servicio Integral Automotriz', 0, 1, 'C');

        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 4, 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages() . ' - Documento generado el ' . date('d/m/Y'), 0, 0, 'C');

        $this->SetTextColor(0, 0, 0);
    }
}

// Verificar si se recibió el ID de la orden
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('<div style="text-align:center; margin-top:50px; font-family:Arial; background:#fff5f5; padding:30px; border-radius:10px; max-width:600px; margin:50px auto; box-shadow:0 4px 6px rgba(0,0,0,0.1); border-left:5px solid #dc2626;">
        <h2 style="color:#dc2626; margin-bottom:20px;">⚠️ Error - Motor Service</h2>
        <p>ID de orden de trabajo no especificado o inválido</p>
        <button onclick="window.history.back()" style="background:#dc2626; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">← Volver</button>
    </div>');
}

$id_orden = (int) $_GET['id'];

try {
    // Obtener datos de la orden
    $orden = OrdenTrabajoControlador::ctrObtenerOrdenTrabajo($id_orden);

    if (!$orden) {
        throw new Exception('Orden de trabajo no encontrada');
    }

    // Obtener detalles de la orden
    $detalles = ModeloOrdenDetalle::mdlObtenerDetalles($id_orden);

    // Crear nuevo documento PDF
    $pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false);

    // Establecer información del documento
    $pdf->SetCreator('Motor Service - Sistema de Gestión');
    $pdf->SetAuthor('Motor Service - Servicio Integral Automotriz');
    $pdf->SetTitle('Orden de Trabajo #' . str_pad($orden['id_orden'], 6, '0', STR_PAD_LEFT));
    $pdf->SetSubject('Orden de Trabajo - Motor Service');
    $pdf->SetKeywords('orden, trabajo, motor service, servicios, vehículo, automotriz');

    // Establecer márgenes
    $pdf->SetMargins(15, 55, 15);
    $pdf->SetHeaderMargin(5);
    $pdf->SetFooterMargin(20);

    // Establecer saltos de página automáticos
    $pdf->SetAutoPageBreak(TRUE, 30);

    // Agregar una página
    $pdf->AddPage();

    // Número de orden destacado con colores corporativos
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->SetFillColor(220, 38, 38); // Rojo corporativo
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetDrawColor(220, 38, 38);
    $pdf->RoundedRect(15, $pdf->GetY(), 180, 15, 3, '1111', 'DF');
    $pdf->Cell(0, 15, 'ORDEN N° ' . str_pad($orden['id_orden'], 6, '0', STR_PAD_LEFT), 0, 1, 'C');

    // Fecha de emisión
    $pdf->SetTextColor(100, 100, 100);
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->Cell(0, 8, 'Fecha de emisión: ' . date('d/m/Y'), 0, 1, 'C');
    $pdf->Ln(5);

    // Resetear colores
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetDrawColor(0, 0, 0);

    // Sección de información del vehículo y cliente
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetFillColor(248, 249, 250);
    $pdf->SetTextColor(220, 38, 38); // Rojo corporativo
    $pdf->Cell(0, 12, 'INFORMACION DEL VEHICULO Y CLIENTE', 0, 1, 'L', true);

    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 11);

    // Tabla con estilo Motor Service
    $pdf->SetFillColor(250, 250, 250);
    $pdf->SetDrawColor(200, 200, 200);

    // Primera fila
    $pdf->Cell(35, 10, 'Marca:', 1, 0, 'L', true);
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetTextColor(220, 38, 38);
    $pdf->Cell(50, 10, $orden['marca'], 1, 0, 'L');
    $pdf->SetFont('helvetica', '', 11);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(35, 10, 'Cliente:', 1, 0, 'L', true);
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetTextColor(220, 38, 38);
    $pdf->Cell(60, 10, $orden['nombre_cliente'], 1, 1, 'L');

    // Segunda fila
    $pdf->SetFont('helvetica', '', 11);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(35, 10, 'Modelo:', 1, 0, 'L', true);
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetTextColor(220, 38, 38);
    $pdf->Cell(50, 10, $orden['modelo'], 1, 0, 'L');
    $pdf->SetFont('helvetica', '', 11);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(35, 10, 'Matrícula:', 1, 0, 'L', true);
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetTextColor(220, 38, 38);
    $pdf->Cell(60, 10, $orden['matricula'], 1, 1, 'L');

    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(8);

    // Sección de información de la orden
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetFillColor(248, 249, 250);
    $pdf->SetTextColor(220, 38, 38);
    $pdf->Cell(0, 12, 'INFORMACION DE LA ORDEN', 0, 1, 'L', true);

    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->SetFillColor(250, 250, 250);

    // Fechas
    $pdf->Cell(40, 10, 'Fecha Ingreso:', 1, 0, 'L', true);
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(50, 10, date('d/m/Y', strtotime($orden['fecha_ingreso'])), 1, 0, 'L');
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(40, 10, 'Fecha Salida:', 1, 0, 'L', true);
    $pdf->SetFont('helvetica', 'B', 11);
    if ($orden['fecha_salida']) {
        $pdf->Cell(50, 10, date('d/m/Y', strtotime($orden['fecha_salida'])), 1, 1, 'L');
    } else {
        $pdf->SetTextColor(220, 38, 38);
        $pdf->Cell(50, 10, 'Pendiente', 1, 1, 'L');
        $pdf->SetTextColor(0, 0, 0);
    }

    // Estado y Responsable
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(40, 10, 'Estado:', 1, 0, 'L', true);
    $pdf->SetFont('helvetica', 'B', 11);

    // Color según estado
    $estado_texto = ucfirst(str_replace('_', ' ', $orden['estado']));
    switch ($orden['estado']) {
        case 'en_proceso':
            $pdf->SetTextColor(255, 140, 0); // Naranja
            break;
        case 'completado':
            $pdf->SetTextColor(0, 150, 0); // Verde
            break;
        case 'cancelado':
            $pdf->SetTextColor(220, 38, 38); // Rojo corporativo
            break;
        default:
            $pdf->SetTextColor(0, 0, 0);
    }
    $pdf->Cell(50, 10, $estado_texto, 1, 0, 'L');

    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(40, 10, 'Responsable:', 1, 0, 'L', true);
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(50, 10, $orden['nombre_personal'], 1, 1, 'L');

    $pdf->Ln(8);

    // Servicios realizados con colores Motor Service
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetFillColor(248, 249, 250);
    $pdf->SetTextColor(220, 38, 38);
    $pdf->Cell(0, 12, 'SERVICIOS REALIZADOS', 0, 1, 'L', true);

    $pdf->SetTextColor(0, 0, 0);

    if ($detalles && count($detalles) > 0) {
        // Encabezados de tabla con colores corporativos
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(220, 38, 38); // Rojo corporativo
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetDrawColor(220, 38, 38);

        $pdf->Cell(45, 10, 'Tipo de Servicio', 1, 0, 'C', true);
        $pdf->Cell(60, 10, 'Descripción', 1, 0, 'C', true);
        $pdf->Cell(20, 10, 'Cantidad', 1, 0, 'C', true);
        $pdf->Cell(30, 10, 'Precio Unitario', 1, 0, 'C', true);
        $pdf->Cell(25, 10, 'Subtotal', 1, 1, 'C', true);

        // Datos de la tabla con colores alternados
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetDrawColor(200, 200, 200);
        $total = 0;
        $fila = 0;

        foreach ($detalles as $detalle) {
            // Alternar colores de fila
            if ($fila % 2 == 0) {
                $pdf->SetFillColor(255, 255, 255);
            } else {
                $pdf->SetFillColor(254, 250, 250); // Muy suave tono rojizo
            }

            // Tipo de servicio
            $tipo_servicio = ucfirst(str_replace('_', ' ', $detalle['tipo_servicio']));
            $pdf->Cell(45, 8, $tipo_servicio, 1, 0, 'L', true);

            // Descripción
            $descripcion = !empty($detalle['descripcion']) ? $detalle['descripcion'] : 'Sin descripción';
            if (strlen($descripcion) > 40) {
                $descripcion = substr($descripcion, 0, 37) . '...';
            }
            $pdf->Cell(60, 8, $descripcion, 1, 0, 'L', true);

            // Cantidad
            $pdf->Cell(20, 8, $detalle['cantidad'], 1, 0, 'C', true);

            // Precio unitario
            $pdf->Cell(30, 8, 'Gs. ' . number_format($detalle['precio_unitario'], 0, ',', '.'), 1, 0, 'R', true);

            // Subtotal
            $pdf->Cell(25, 8, 'Gs. ' . number_format($detalle['subtotal'], 0, ',', '.'), 1, 1, 'R', true);

            $total += $detalle['subtotal'];
            $fila++;
        }

        // Fila del total con estilo Motor Service
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetFillColor(220, 38, 38);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetDrawColor(220, 38, 38);
        $pdf->Cell(155, 12, 'TOTAL GENERAL', 1, 0, 'R', true);
        $pdf->Cell(25, 12, 'Gs. ' . number_format($total, 0, ',', '.'), 1, 1, 'R', true);

    } else {
        $pdf->SetFont('helvetica', 'I', 12);
        $pdf->SetTextColor(150, 150, 150);
        $pdf->Cell(0, 20, 'No hay servicios registrados para esta orden.', 1, 1, 'C');
    }

    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(8);

    // Observaciones
    if (!empty($orden['observaciones'])) {
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetFillColor(248, 249, 250);
        $pdf->SetTextColor(220, 38, 38);
        $pdf->Cell(0, 12, 'OBSERVACIONES', 0, 1, 'L', true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetDrawColor(200, 200, 200);

        $pdf->MultiCell(0, 8, $orden['observaciones'], 1, 'L', true);
        $pdf->Ln(5);
    }

    // Generar el PDF
    $nombre_archivo = 'Motor_Service_Orden_' . str_pad($orden['id_orden'], 6, '0', STR_PAD_LEFT) . '.pdf';
    $pdf->Output($nombre_archivo, 'I');

} catch (Exception $e) {
    error_log("Error generando PDF: " . $e->getMessage());
    echo '<div style="text-align:center; margin-top:50px; font-family:Arial; background:#fff5f5; padding:30px; border-radius:10px; max-width:600px; margin:50px auto; box-shadow:0 4px 6px rgba(0,0,0,0.1); border-left:5px solid #dc2626;">';
    echo '<h2 style="color:#dc2626; margin-bottom:20px;">⚠️ Error - Motor Service</h2>';
    echo '<div style="background:white; padding:20px; border-radius:5px; margin:20px 0; text-align:left; border:1px solid #fee2e2;">';
    echo '<p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><strong>Línea:</strong> ' . $e->getLine() . '</p>';
    echo '<p><strong>Archivo:</strong> ' . basename($e->getFile()) . '</p>';
    echo '</div>';
    echo '<button onclick="window.history.back()" style="background:#dc2626; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">← Volver a Motor Service</button>';
    echo '</div>';
}
?>