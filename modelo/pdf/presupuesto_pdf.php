<?php
/**
 * Generador de PDF para Presupuestos - Motor Service
 * Sistema de Gestión Automotriz - Versión 2.0
 */

// Incluir archivos necesarios
require_once(__DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php');
require_once(__DIR__ . '/../conexion.php');
require_once(__DIR__ . '/../modelo_presupuesto.php');
require_once(__DIR__ . '/../modelo_empresa.php');
require_once(__DIR__ . '/../../controlador/presupuesto_controlador.php');

class PresupuestoPDF extends TCPDF
{
    public function Header()
    {
        // Fondo del header en rojo corporativo
        $this->SetFillColor(220, 38, 38);
        $this->Rect(0, 0, 210, 50, 'F');

        // Logo Motor Service
        $logo_path = __DIR__ . '/../../img/img-01.jpg';
        if (file_exists($logo_path)) {
            $this->Image($logo_path, 15, 8, 35, 25, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        } else {
            // Fallback: texto del logo
            $this->SetXY(15, 8);
            $this->SetFont('helvetica', '', 9);
            $this->SetTextColor(255, 255, 255);
            $this->Cell(40, 4, 'Servicio Integral', 0, 1, 'C');
            $this->SetX(15);
            $this->Cell(40, 4, 'Automotriz', 0, 1, 'C');

            $this->SetXY(15, 18);
            $this->SetFont('helvetica', 'B', 16);
            $this->Cell(40, 8, 'Motor', 0, 0, 'C');
            $this->SetXY(15, 26);
            $this->SetFont('helvetica', 'B', 12);
            $this->SetTextColor(0, 0, 0);
            $this->Cell(40, 6, 'Service', 0, 1, 'C');
        }

        // Título principal
        $this->SetXY(65, 12);
        $this->SetFont('helvetica', 'B', 24);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(80, 15, 'PRESUPUESTO', 0, 0, 'C');

        // Información de contacto
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

        $this->SetTextColor(0, 0, 0);
        $this->SetDrawColor(0, 0, 0);
        $this->Ln(18);
    }

    public function Footer()
    {
        $this->SetY(-25);

        // Línea decorativa
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

// Verificar parámetros
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('<div style="text-align:center; margin-top:50px; font-family:Arial; background:#fff5f5; padding:30px; border-radius:10px; max-width:600px; margin:50px auto; box-shadow:0 4px 6px rgba(0,0,0,0.1); border-left:5px solid #dc2626;">
        <h2 style="color:#dc2626; margin-bottom:20px;">⚠️ Error - Motor Service</h2>
        <p>ID de presupuesto no especificado o inválido</p>
        <button onclick="window.history.back()" style="background:#dc2626; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">← Volver</button>
    </div>');
}

$id_presupuesto = (int) $_GET['id'];

try {
    // Obtener datos del presupuesto
    $presupuesto = PresupuestoControlador::ctrObtenerPresupuesto($id_presupuesto);

    if (!$presupuesto) {
        throw new Exception('Presupuesto no encontrado');
    }

    // Obtener detalles del presupuesto
    $detalles = PresupuestoControlador::ctrObtenerDetallesPresupuesto($id_presupuesto);

    // Obtener información de la empresa
    $empresa = ModeloEmpresa::mdlObtenerInfoEmpresa();

    // Crear PDF
    $pdf = new PresupuestoPDF('P', 'mm', 'A4', true, 'UTF-8', false);

    // Configurar documento
    $pdf->SetCreator('Motor Service - Sistema de Gestión');
    $pdf->SetAuthor('Motor Service - Servicio Integral Automotriz');
    $pdf->SetTitle('Presupuesto #' . str_pad($presupuesto['id_presupuesto'], 6, '0', STR_PAD_LEFT));
    $pdf->SetSubject('Presupuesto - Motor Service');
    $pdf->SetKeywords('presupuesto, motor service, servicios, vehículo, automotriz');

    // Configurar márgenes
    $pdf->SetMargins(15, 55, 15);
    $pdf->SetHeaderMargin(5);
    $pdf->SetFooterMargin(20);
    $pdf->SetAutoPageBreak(TRUE, 30);

    $pdf->AddPage();

    // Número de presupuesto destacado
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->SetFillColor(220, 38, 38);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetDrawColor(220, 38, 38);
    $pdf->RoundedRect(15, $pdf->GetY(), 180, 15, 3, '1111', 'DF');
    $pdf->Cell(0, 15, 'PRESUPUESTO N° ' . str_pad($presupuesto['id_presupuesto'], 6, '0', STR_PAD_LEFT), 0, 1, 'C');

    $pdf->Ln(5);

    // Fechas importantes
    $pdf->SetTextColor(100, 100, 100);
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->Cell(0, 8, 'Fecha de emisión: ' . date('d/m/Y', strtotime($presupuesto['fecha_emision'])), 0, 1, 'C');
    $pdf->Cell(0, 8, 'Válido hasta: ' . date('d/m/Y', strtotime($presupuesto['fecha_validez'])), 0, 1, 'C');

    // Estado del presupuesto
    $estado_color = ['pendiente' => [255, 193, 7], 'aprobado' => [40, 167, 69], 'rechazado' => [220, 53, 69], 'vencido' => [108, 117, 125]];
    $color = $estado_color[$presupuesto['estado']] ?? [108, 117, 125];

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor($color[0], $color[1], $color[2]);
    $pdf->Cell(0, 10, 'ESTADO: ' . strtoupper($presupuesto['estado']), 0, 1, 'C');

    $pdf->Ln(10);

    // Información de la empresa (lado izquierdo)
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Cell(85, 8, 'DATOS DE LA EMPRESA', 1, 0, 'C', true);
    $pdf->Cell(10, 8, '', 0, 0); // Espacio
    $pdf->Cell(85, 8, 'DATOS DEL CLIENTE', 1, 1, 'C', true);

    // Información de empresa
    $pdf->SetFont('helvetica', '', 9);
    $y_inicial = $pdf->GetY();

    $pdf->Cell(85, 6, $empresa['nombre_empresa'] ?? 'Motor Service', 1, 1, 'L');
    $pdf->Cell(85, 6, 'RUC: ' . ($empresa['ruc_empresa'] ?? '80012345-1'), 1, 1, 'L');
    $pdf->Cell(85, 6, $empresa['direccion_empresa'] ?? 'Asunción, Paraguay', 1, 1, 'L');
    $pdf->Cell(85, 6, 'Tel: ' . ($empresa['telefono_empresa'] ?? '(0984) 800 586'), 1, 1, 'L');
    $pdf->Cell(85, 6, $empresa['email_empresa'] ?? 'info@motorservicepy.com', 1, 1, 'L');

    // Información del cliente (lado derecho)
    $pdf->SetXY(110, $y_inicial);
    $pdf->Cell(85, 6, $presupuesto['nombre_cliente'], 1, 1, 'L');
    $pdf->SetX(110);

    $documento = !empty($presupuesto['ruc']) ? 'RUC: ' . $presupuesto['ruc'] : 'CI: ' . $presupuesto['cedula'];
    $pdf->Cell(85, 6, $documento, 1, 1, 'L');
    $pdf->SetX(110);
    $pdf->Cell(85, 6, $presupuesto['direccion'] ?? 'No especificada', 1, 1, 'L');
    $pdf->SetX(110);
    $pdf->Cell(85, 6, 'Tel: ' . ($presupuesto['telefono'] ?? 'No especificado'), 1, 1, 'L');
    $pdf->SetX(110);
    $pdf->Cell(85, 6, $presupuesto['email'] ?? 'No especificado', 1, 1, 'L');

    $pdf->Ln(10);

    // Información del vehículo
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetFillColor(220, 38, 38);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 10, 'INFORMACIÓN DEL VEHÍCULO', 1, 1, 'C', true);

    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetFillColor(248, 249, 250);

    // Crear tabla de información del vehículo
    $vehiculo_info = [
        ['Matrícula:', $presupuesto['matricula']],
        ['Marca:', $presupuesto['marca']],
        ['Modelo:', $presupuesto['modelo']],
        ['Año:', $presupuesto['anho']],
        ['Color:', $presupuesto['color'] ?? 'No especificado']
    ];

    foreach ($vehiculo_info as $info) {
        $pdf->Cell(40, 8, $info[0], 1, 0, 'L', true);
        $pdf->Cell(140, 8, $info[1], 1, 1, 'L');
    }

    $pdf->Ln(10);

    // Tabla de servicios y productos
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetFillColor(220, 38, 38);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 10, 'DETALLE DEL PRESUPUESTO', 1, 1, 'C', true);

    // Encabezados de la tabla
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetFillColor(240, 240, 240);
    $pdf->SetTextColor(0, 0, 0);

    $pdf->Cell(15, 8, 'ITEM', 1, 0, 'C', true);
    $pdf->Cell(20, 8, 'TIPO', 1, 0, 'C', true);
    $pdf->Cell(70, 8, 'DESCRIPCIÓN', 1, 0, 'C', true);
    $pdf->Cell(20, 8, 'CANT.', 1, 0, 'C', true);
    $pdf->Cell(25, 8, 'PRECIO UNIT.', 1, 0, 'C', true);
    $pdf->Cell(30, 8, 'SUBTOTAL', 1, 1, 'C', true);

    // Contenido de la tabla
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetFillColor(255, 255, 255);

    $total_general = 0;
    $item_numero = 1;

    foreach ($detalles as $detalle) {
        $tipo_texto = ($detalle['tipo'] == 'producto') ? 'PROD.' : 'SERV.';

        $pdf->Cell(15, 8, str_pad($item_numero, 2, '0', STR_PAD_LEFT), 1, 0, 'C');
        $pdf->Cell(20, 8, $tipo_texto, 1, 0, 'C');

        // Descripción con ajuste de texto
        $descripcion = $detalle['descripcion'];
        if (strlen($descripcion) > 45) {
            $descripcion = substr($descripcion, 0, 42) . '...';
        }
        $pdf->Cell(70, 8, $descripcion, 1, 0, 'L');

        $pdf->Cell(20, 8, number_format($detalle['cantidad'], 0), 1, 0, 'C');
        $pdf->Cell(25, 8, 'Gs. ' . number_format($detalle['precio_unitario'], 0, ',', '.'), 1, 0, 'R');
        $pdf->Cell(30, 8, 'Gs. ' . number_format($detalle['subtotal'], 0, ',', '.'), 1, 1, 'R');

        $total_general += $detalle['subtotal'];
        $item_numero++;
    }

    // Totales
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetFillColor(240, 240, 240);

    $pdf->Cell(150, 10, 'TOTAL GENERAL:', 1, 0, 'R', true);
    $pdf->SetFillColor(220, 38, 38);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(30, 10, 'Gs. ' . number_format($total_general, 0, ',', '.'), 1, 1, 'R', true);

    $pdf->Ln(10);

    // Observaciones
    if (!empty($presupuesto['observaciones'])) {
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(0, 8, 'OBSERVACIONES', 1, 1, 'C', true);

        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->MultiCell(0, 8, $presupuesto['observaciones'], 1, 'L', true);

        $pdf->Ln(5);
    }

    // Información adicional
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 5, 'Personal responsable: ' . $presupuesto['nombre_personal'], 0, 1, 'L');

    // Días restantes para vencimiento
    if ($presupuesto['dias_restantes'] > 0) {
        $pdf->SetTextColor(40, 167, 69);
        $pdf->Cell(0, 5, 'Este presupuesto vence en ' . $presupuesto['dias_restantes'] . ' días', 0, 1, 'L');
    } elseif ($presupuesto['dias_restantes'] == 0) {
        $pdf->SetTextColor(255, 193, 7);
        $pdf->Cell(0, 5, 'Este presupuesto vence HOY', 0, 1, 'L');
    } else {
        $pdf->SetTextColor(220, 53, 69);
        $pdf->Cell(0, 5, 'Este presupuesto está VENCIDO', 0, 1, 'L');
    }

    $pdf->Ln(15);

    // Términos y condiciones
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 8, 'TÉRMINOS Y CONDICIONES', 0, 1, 'C');

    $pdf->SetFont('helvetica', '', 8);
    $terminos = [
        '• Los precios están expresados en Guaraníes (Gs.) y son válidos hasta la fecha indicada.',
        '• Los trabajos se realizarán en el orden de llegada y disponibilidad del taller.',
        '• La empresa no se responsabiliza por objetos de valor dejados en el vehículo.',
        '• Este presupuesto no incluye trabajos adicionales que puedan surgir durante la reparación.',
        '• Los repuestos utilizados tienen garantía según las especificaciones del fabricante.',
        '• Para proceder con los trabajos se requiere la aprobación expresa del cliente.',
        '• Los vehículos no retirados dentro de los 30 días generarán gastos de estadía.'
    ];

    foreach ($terminos as $termino) {
        $pdf->Cell(0, 5, $termino, 0, 1, 'L');
    }

    $pdf->Ln(10);

    // Firmas
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(90, 5, '', 0, 0); // Espacio izquierdo
    $pdf->Cell(90, 5, 'Conforme:', 0, 1, 'C');
    $pdf->Ln(15);

    $pdf->Cell(90, 5, '', 0, 0); // Espacio izquierdo
    $pdf->Cell(90, 1, '', 'T', 1, 'C'); // Línea para firma
    $pdf->Cell(90, 5, '', 0, 0); // Espacio izquierdo
    $pdf->Cell(90, 5, 'Firma del Cliente', 0, 1, 'C');

    // Salida del PDF
    $nombre_archivo = 'Presupuesto_' . str_pad($presupuesto['id_presupuesto'], 6, '0', STR_PAD_LEFT) . '_' . $presupuesto['nombre_cliente'];
    $pdf->Output($nombre_archivo . '.pdf', 'I');

} catch (Exception $e) {
    die('<div style="text-align:center; margin-top:50px; font-family:Arial; background:#fff5f5; padding:30px; border-radius:10px; max-width:600px; margin:50px auto; box-shadow:0 4px 6px rgba(0,0,0,0.1); border-left:5px solid #dc2626;">
        <h2 style="color:#dc2626; margin-bottom:20px;">⚠️ Error - Motor Service</h2>
        <p>Error al generar el presupuesto: ' . htmlspecialchars($e->getMessage()) . '</p>
        <button onclick="window.history.back()" style="background:#dc2626; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">← Volver</button>
    </div>');
}
?>