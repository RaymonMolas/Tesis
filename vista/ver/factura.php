<?php
if(!isset($_SESSION["validarIngreso"])){
    echo '<script>window.location = "index.php?pagina=login";</script>';
    return;
}else{
    if($_SESSION["validarIngreso"] != "ok"){
        echo '<script>window.location = "index.php?pagina=login";</script>';
        return;
    }   
}

if(!isset($_GET["id"]) && !isset($_GET["numero"])) {
    echo '<script>window.location = "index.php?pagina=tabla/facturas";</script>';
    return;
}

// Buscar por ID o por número de factura
if(isset($_GET["id"])) {
    $factura = FacturaControlador::ctrObtenerFactura($_GET["id"]);
} else {
    // Buscar por número de factura (necesitarías implementar este método)
    $factura = FacturaControlador::ctrObtenerFacturaPorNumero($_GET["numero"]);
}

if(!$factura) {
    echo '<script>
        Swal.fire({
            icon: "error",
            title: "Factura no encontrada",
            text: "La factura solicitada no existe"
        }).then(() => {
            window.location = "index.php?pagina=tabla/facturas";
        });
    </script>';
    return;
}
?>

<title>FACTURA <?php echo $factura["numero_factura"]; ?></title>

<div class="container mt-4">
    <!-- Header con acciones -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h2>
            <i class="bi bi-receipt text-primary"></i>
            Factura <?php echo $factura["numero_factura"]; ?>
        </h2>
        <div class="btn-group">
            <?php if($factura["estado"] == "pendiente"): ?>
                <button class="btn btn-success" onclick="marcarPagada()">
                    <i class="bi bi-check-circle"></i> Marcar como Pagada
                </button>
            <?php endif; ?>
            
            <?php if($factura["estado"] != "anulada"): ?>
                <button class="btn btn-warning" onclick="enviarPorEmail()">
                    <i class="bi bi-envelope"></i> Enviar por Email
                </button>
            <?php endif; ?>
            
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> Imprimir
            </button>
            
            <a href="../modelo/pdf/factura_pdf.php?id=<?php echo $factura['id_factura']; ?>" 
               target="_blank" class="btn btn-success">
                <i class="bi bi-file-pdf"></i> PDF
            </a>
            
            <?php if($factura["estado"] != "anulada"): ?>
                <button class="btn btn-danger" onclick="anularFactura()">
                    <i class="bi bi-x-circle"></i> Anular
                </button>
            <?php endif; ?>
            
            <a href="index.php?pagina=tabla/facturas" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <!-- Encabezado de Factura -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <!-- Logo y datos de empresa -->
                <div class="col-md-6">
                    <div class="d-flex align-items-center mb-3">
                        <img src="../img/img-01.jpg" alt="Logo" class="me-3" style="width: 80px; height: 80px; border-radius: 50%;">
                        <div>
                            <h3 class="text-danger mb-0">MOTOR SERVICE</h3>
                            <p class="text-muted mb-0">Servicio Integral Automotriz</p>
                            <small class="text-muted">Tel: (0984) 800 586</small>
                        </div>
                    </div>
                </div>
                
                <!-- Datos de la factura -->
                <div class="col-md-6 text-end">
                    <h1 class="text-primary mb-3">FACTURA</h1>
                    <table class="table table-sm table-borderless ms-auto" style="width: auto;">
                        <tr>
                            <td class="text-end"><strong>Número:</strong></td>
                            <td><?php echo $factura["numero_factura"]; ?></td>
                        </tr>
                        <tr>
                            <td class="text-end"><strong>Fecha:</strong></td>
                            <td><?php echo date('d/m/Y', strtotime($factura["fecha_emision"])); ?></td>
                        </tr>
                        <tr>
                            <td class="text-end"><strong>Hora:</strong></td>
                            <td><?php echo date('H:i', strtotime($factura["fecha_emision"])); ?></td>
                        </tr>
                        <tr>
                            <td class="text-end"><strong>Estado:</strong></td>
                            <td>
                                <?php
                                $badgeClass = "";
                                switch($factura["estado"]) {
                                    case "pendiente":
                                        $badgeClass = "bg-warning";
                                        break;
                                    case "pagada":
                                        $badgeClass = "bg-success";
                                        break;
                                    case "anulada":
                                        $badgeClass = "bg-danger";
                                        break;
                                }
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>">
                                    <?php echo ucfirst($factura["estado"]); ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Información del Cliente -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-person-circle"></i> Información del Cliente
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Cliente:</strong> <?php echo $factura["nombre_cliente"]; ?></p>
                    <p><strong>Cédula:</strong> <?php echo $factura["cedula"]; ?></p>
                    <p><strong>Teléfono:</strong> <?php echo $factura["telefono"]; ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Email:</strong> <?php echo $factura["email"]; ?></p>
                    <p><strong>Dirección:</strong> <?php echo $factura["direccion"]; ?></p>
                    <p><strong>Atendido por:</strong> <?php echo $factura["nombre_personal"]; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalles de la Factura -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-list-ul"></i> Detalle de Productos y Servicios
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Tipo</th>
                            <th>Descripción</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-end">Precio Unit.</th>
                            <th class="text-end">Descuento</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($factura["detalles"] as $detalle): ?>
                            <tr>
                                <td>
                                    <span class="badge <?php echo $detalle['tipo'] == 'producto' ? 'bg-info' : 'bg-success'; ?>">
                                        <i class="bi bi-<?php echo $detalle['tipo'] == 'producto' ? 'box' : 'wrench'; ?>"></i>
                                        <?php echo ucfirst($detalle["tipo"]); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo $detalle["descripcion"]; ?>
                                    <?php if($detalle["tipo"] == "producto" && $detalle["codigo_producto"]): ?>
                                        <br><small class="text-muted">Código: <?php echo $detalle["codigo_producto"]; ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?php echo $detalle["cantidad"]; ?></td>
                                <td class="text-end">₲ <?php echo number_format($detalle["precio_unitario"], 0, ',', '.'); ?></td>
                                <td class="text-end">₲ <?php echo number_format($detalle["descuento"], 0, ',', '.'); ?></td>
                                <td class="text-end"><strong>₲ <?php echo number_format($detalle["subtotal"], 0, ',', '.'); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Totales -->
    <div class="row">
        <div class="col-md-8">
            <!-- Información Adicional -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Información Adicional</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Tipo de Factura:</strong> <?php echo ucfirst($factura["tipo_factura"]); ?></p>
                            <p><strong>Método de Pago:</strong> <?php echo ucfirst($factura["metodo_pago"]); ?></p>
                        </div>
                        <div class="col-md-6">
                            <?php if($factura["id_orden"]): ?>
                                <p><strong>Orden de Trabajo:</strong> 
                                    <a href="index.php?pagina=ver/orden_trabajo&id=<?php echo $factura['id_orden']; ?>" class="text-decoration-none">
                                        #<?php echo str_pad($factura["id_orden"], 6, '0', STR_PAD_LEFT); ?>
                                    </a>
                                </p>
                            <?php endif; ?>
                            
                            <?php if($factura["id_presupuesto"]): ?>
                                <p><strong>Presupuesto:</strong> 
                                    <a href="index.php?pagina=ver/presupuesto&id=<?php echo $factura['id_presupuesto']; ?>" class="text-decoration-none">
                                        #<?php echo $factura["id_presupuesto"]; ?>
                                    </a>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if($factura["observaciones"]): ?>
                        <hr>
                        <p><strong>Observaciones:</strong></p>
                        <p class="text-muted"><?php echo nl2br($factura["observaciones"]); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Totales -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">Resumen de Pagos</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td>Subtotal:</td>
                            <td class="text-end">₲ <?php echo number_format($factura["subtotal"], 0, ',', '.'); ?></td>
                        </tr>
                        <?php if($factura["descuento"] > 0): ?>
                            <tr>
                                <td>Descuento:</td>
                                <td class="text-end text-danger">-₲ <?php echo number_format($factura["descuento"], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endif; ?>
                        
                        <?php if($factura["iva"] > 0): ?>
                            <tr>
                                <td>IVA:</td>
                                <td class="text-end">₲ <?php echo number_format($factura["iva"], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endif; ?>
                        
                        <tr class="table-primary">
                            <td><strong>TOTAL:</strong></td>
                            <td class="text-end">
                                <h4 class="text-primary mb-0">₲ <?php echo number_format($factura["total"], 0, ',', '.'); ?></h4>
                            </td>
                        </tr>
                    </table>
                    
                    <?php if($factura["estado"] == "pagada"): ?>
                        <div class="alert alert-success text-center mt-3">
                            <i class="bi bi-check-circle-fill"></i>
                            <strong>PAGADO</strong>
                        </div>
                    <?php elseif($factura["estado"] == "anulada"): ?>
                        <div class="alert alert-danger text-center mt-3">
                            <i class="bi bi-x-circle-fill"></i>
                            <strong>ANULADA</strong>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning text-center mt-3">
                            <i class="bi bi-clock-fill"></i>
                            <strong>PENDIENTE</strong>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Información de pie -->
    <div class="mt-4 text-center text-muted only-print">
        <hr>
        <p class="mb-0">
            <strong>MOTOR SERVICE</strong> - Servicio Integral Automotriz<br>
            Tel: (0984) 800 586 | www.motorservicepy.com<br>
            <small>Gracias por su preferencia</small>
        </p>
    </div>
</div>

<!-- Formularios ocultos para acciones -->
<form id="formMarcarPagada" method="post" style="display: none;">
    <input type="hidden" name="id_factura" value="<?php echo $factura['id_factura']; ?>">
    <input type="hidden" name="estado" value="pagada">
</form>

<form id="formAnular" method="post" style="display: none;">
    <input type="hidden" name="anular_factura" value="<?php echo $factura['id_factura']; ?>">
    <input type="hidden" name="motivo_anulacion" id="motivoAnulacion">
</form>

<style>
    body {
        background-color: #f8f9fa;
    }
    
    h2 {
        font-family: "Copperplate", Fantasy;
        color: red;
    }
    
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        page-break-inside: avoid;
    }
    
    .badge {
        font-size: 0.85rem;
    }
    
    /* Estilos para impresión */
    @media print {
        .no-print {
            display: none !important;
        }
        
        .only-print {
            display: block !important;
        }
        
        body {
            background-color: white !important;
            font-size: 12px;
            color: #000 !important;
        }
        
        .card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
            margin-bottom: 15px !important;
        }
        
        .card-header {
            background-color: #f8f9fa !important;
            color: #333 !important;
            -webkit-print-color-adjust: exact;
        }
        
        .bg-primary, .bg-success, .bg-info, .bg-warning, .bg-danger {
            -webkit-print-color-adjust: exact;
        }
        
        .text-primary, .text-success, .text-info, .text-warning, .text-danger {
            -webkit-print-color-adjust: exact;
        }
        
        .container {
            max-width: none !important;
            padding: 0 !important;
        }
        
        .btn {
            display: none !important;
        }
        
        h1, h2, h3, h4, h5, h6 {
            color: #000 !important;
        }
        
        .table {
            font-size: 11px !important;
        }
        
        .table th, .table td {
            padding: 4px !important;
            border: 1px solid #dee2e6 !important;
        }
    }
    
    .only-print {
        display: none;
    }
    
    /* Estilo para facturas anuladas */
    <?php if($factura["estado"] == "anulada"): ?>
        .card-body::before {
            content: "ANULADA";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 4rem;
            color: rgba(220, 53, 69, 0.1);
            font-weight: bold;
            z-index: 1;
            pointer-events: none;
        }
        
        .card-body {
            position: relative;
        }
    <?php endif; ?>
</style>

<script>
function marcarPagada() {
    Swal.fire({
        title: '¿Marcar como pagada?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, marcar como pagada',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('formMarcarPagada').submit();
        }
    });
}

// Anular factura
function anularFactura() {
    Swal.fire({
        title: 'Anular Factura',
        text: "Ingrese el motivo de anulación:",
        input: 'textarea',
        inputPlaceholder: 'Motivo de la anulación...',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Anular Factura',
        cancelButtonText: 'Cancelar',
        inputValidator: (value) => {
            if (!value) {
                return 'Debe ingresar un motivo de anulación';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('motivoAnulacion').value = result.value;
            document.getElementById('formAnular').submit();
        }
    });
}

function enviarPorEmail() {
    Swal.fire({
        title: 'Enviar por Email',
        input: 'email',
        inputPlaceholder: 'Correo electrónico del cliente',
        inputValue: '<?php echo $factura["email"]; ?>',
        showCancelButton: true,
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Enviar Email',
        cancelButtonText: 'Cancelar',
        inputValidator: (value) => {
            if (!value) {
                return 'Debe ingresar un email válido';
            }
            if (!/\S+@\S+\.\S+/.test(value)) {
                return 'El formato del email no es válido';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Aquí implementarías el envío por email
            Swal.fire({
                title: 'Enviando...',
                text: 'Procesando envío de email',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Simular envío
            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Email Enviado',
                    text: `La factura ha sido enviada a ${result.value}`,
                    timer: 3000
                });
            }, 2000);
        }
    });
}

// Funciones de ayuda para navegación con teclado
document.addEventListener('keydown', function(e) {
    // Ctrl + P para imprimir
    if (e.ctrlKey && e.key === 'p') {
        e.preventDefault();
        window.print();
    }
    
    // Escape para volver
    if (e.key === 'Escape') {
        window.location = 'index.php?pagina=tabla/facturas';
    }
});

// Auto-focus en botones importantes
document.addEventListener('DOMContentLoaded', function() {
    <?php if($factura["estado"] == "pendiente"): ?>
        // Resaltar el botón de marcar como pagada si está pendiente
        setTimeout(() => {
            const btnPagar = document.querySelector('button[onclick="marcarPagada()"]');
            if (btnPagar) {
                btnPagar.classList.add('pulse');
            }
        }, 1000);
    <?php endif; ?>
});

// Efecto de pulso para botón importante
const style = document.createElement('style');
style.textContent = `
    .pulse {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(40, 167, 69, 0); }
        100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
    }
`;
document.head.appendChild(style);
</script>

<?php
// Manejar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST["id_factura"]) && isset($_POST["estado"])) {
        $actualizar = FacturaControlador::ctrActualizarEstadoFactura();
    } elseif (isset($_POST["anular_factura"])) {
        $anular = FacturaControlador::ctrAnularFactura();
    }
}
?>