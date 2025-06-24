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

if(isset($_GET["id"])){
    $orden = OrdenTrabajoControlador::ctrObtenerOrdenTrabajo($_GET["id"]);
} else {
    echo '<script>window.location = "index.php?pagina=tabla/orden_trabajo";</script>';
    return;
}
?>

<title>VER ORDEN DE TRABAJO</title>
<style>
    body {
        background-color: white;
    }
    h1, h2 {
        font-family: "Copperplate", Fantasy;
        color: red;
    }
    .info-section {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    @media print {
        .no-print {
            display: none;
        }
        body {
            padding: 20px;
        }
    }
</style>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>ORDEN DE TRABAJO #<?php echo str_pad($orden['id_orden'], 6, '0', STR_PAD_LEFT); ?></h1>
        <div class="no-print">
            <div class="btn-group">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="bi bi-printer"></i> Imprimir
                </button>
                <a href="../modelo/pdf/orden_trabajo_pdf.php?id=<?php echo $orden['id_orden']; ?>" target="_blank" class="btn btn-success">
                    <i class="bi bi-file-pdf"></i> Exportar PDF
                </a>
                <a href="index.php?pagina=tabla/orden_trabajo" class="btn btn-danger">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="info-section">
                <h5>Información del Vehículo</h5>
                <p><strong>Marca:</strong> <?php echo $orden['marca']; ?></p>
                <p><strong>Modelo:</strong> <?php echo $orden['modelo']; ?></p>
                <p><strong>Matrícula:</strong> <?php echo $orden['matricula']; ?></p>
                <p><strong>Cliente:</strong> <?php echo $orden['nombre_cliente']; ?></p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="info-section">
                <h5>Información de la Orden</h5>
                <p><strong>Fecha de Ingreso:</strong> <?php echo date('d/m/Y H:i', strtotime($orden['fecha_ingreso'])); ?></p>
                <p><strong>Fecha de Salida:</strong> 
                    <?php 
                    if ($orden['fecha_salida']) {
                        echo date('d/m/Y H:i', strtotime($orden['fecha_salida']));
                    } else {
                        echo "Pendiente";
                    }
                    ?>
                </p>
                <p><strong>Estado:</strong> 
                    <span class="badge <?php 
                        echo ($orden['estado'] == 'en_proceso') ? 'bg-warning' : 
                            (($orden['estado'] == 'completado') ? 'bg-success' : 'bg-danger'); 
                    ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $orden['estado'])); ?>
                    </span>
                </p>
                <p><strong>Responsable:</strong> <?php echo $orden['nombre_personal']; ?></p>
            </div>
        </div>
    </div>

    <?php
    require_once "../modelo/modelo_orden_detalle.php";
    $detalles = ModeloOrdenDetalle::mdlObtenerDetalles($orden['id_orden']);
    ?>
    <div class="info-section">
        <h5>Detalle de Servicios</h5>
        <?php if ($detalles && count($detalles) > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Servicio</th>
                            <th>Descripción</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detalles as $detalle): ?>
                            <tr>
                                <td><?php echo ucfirst(str_replace('_', ' ', $detalle['tipo_servicio'])); ?></td>
                                <td><?php echo htmlspecialchars($detalle['descripcion']); ?></td>
                                <td><?php echo $detalle['cantidad']; ?></td>
                                <td>₲ <?php echo number_format($detalle['precio_unitario'], 0, ',', '.'); ?></td>
                                <td>₲ <?php echo number_format($detalle['subtotal'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end"><strong>Total:</strong></td>
                            <td>
                                ₲ <?php
                                $total = 0;
                                foreach ($detalles as $detalle) {
                                    $total += $detalle['subtotal'];
                                }
                                echo number_format($total, 0, ',', '.');
                                ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php else: ?>
            <p>No hay detalles disponibles.</p>
        <?php endif; ?>
    </div>

    <?php if ($orden['estado'] == 'completado'): ?>
        <div class="alert alert-success text-center">
            <i class="bi bi-check-circle"></i> Esta orden de trabajo ha sido completada
        </div>
    <?php elseif ($orden['estado'] == 'cancelado'): ?>
        <div class="alert alert-danger text-center">
            <i class="bi bi-x-circle"></i> Esta orden de trabajo ha sido cancelada
        </div>
    <?php endif; ?>

</div>
</div>
