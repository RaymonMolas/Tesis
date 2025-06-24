<?php
if (!isset($_SESSION["validarIngreso"]) || $_SESSION["validarIngreso"] != "ok") {
    echo '<script>window.location = "index.php?pagina=login";</script>';
    exit;
}


$tipo = $_SESSION["tipo_usuario"] ?? "";
$id_cliente = $_SESSION["id_cliente"] ?? null;

$ordenes = OrdenTrabajoControlador::ctrListarOrdenesTrabajo();

?>

<title>Historial de Vehículos</title>
<style>
    body {
        background-color: #f8f9fa;
    }
    h1 {
        font-family: "Copperplate", Fantasy;
        color: #dc3545;
        margin-top: 20px;
    }
    .table th, .table td {
        vertical-align: middle;
        text-align: center;
    }
    .btn i {
        font-size: 1rem;
    }
</style>

<div class="container mt-4">
    <h1 class="text-center mb-4">Historial de Vehículos</h1>

    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle" id="tablaHistorial">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Vehículo</th>
                    <th>Cliente</th>
                    <th>Personal</th>
                    <th>Fecha Ingreso</th>
                    <th>Fecha Salida</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ordenes as $orden): ?>
                    <?php if ($orden["estado"] === "completado"): ?>
                    <tr>
                        <td><?php echo str_pad($orden["id_orden"], 6, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo $orden["marca"] . " " . $orden["modelo"] . "<br>(" . $orden["matricula"] . ")"; ?></td>
                        <td><?php echo $orden["nombre_cliente"]; ?></td>
                        <td><?php echo $orden["nombre_personal"]; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($orden["fecha_ingreso"])); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($orden["fecha_salida"])); ?></td>
                        <td>
                            <span class="badge bg-success">
                                Completado
                            </span>
                        </td>
<td>
    <div class="btn-group">
        <a href="index.php?pagina=ver/orden_trabajo&id=<?php echo $orden["id_orden"]; ?>"
            class="btn btn-info btn-sm" title="Ver Detalles">
            <i class="bi bi-eye-fill"></i>
        </a>
        <a href="index.php?pagina=nuevo/factura&desde_orden=<?php echo $orden["id_orden"]; ?>"
            class="btn btn-success btn-sm" title="Facturar Orden">
            <i class="bi bi-receipt"></i>
        </a>
    </div>
</td>
                    </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById("buscadorHistorial")?.addEventListener("keyup", function() {
    const filtro = this.value.toLowerCase();
    const filas = document.querySelectorAll("#tablaHistorial tbody tr");
    filas.forEach(fila => {
        const texto = fila.textContent.toLowerCase();
        fila.style.display = texto.includes(filtro) ? "" : "none";
    });
});
</script>
