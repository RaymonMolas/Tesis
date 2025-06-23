<?php
if (!isset($_SESSION["validarIngreso"])) {
    echo '<script>window.location = "index.php?pagina=login";</script>';
    return;
} else {
    if ($_SESSION["validarIngreso"] != "ok") {
        echo '<script>window.location = "index.php?pagina=login";</script>';
        return;
    }
}
$ordenes = OrdenTrabajoControlador::ctrListarOrdenesTrabajo();

?>

<title>ORDENES DE TRABAJO</title>
<style>
    body {
        background-color: #f8f9fa;
    }

    h1 {
        font-family: "Copperplate", Fantasy;
        color: #dc3545;
        margin-top: 20px;
    }

    .table th,
    .table td {
        vertical-align: middle;
        text-align: center;
    }

    .btn i {
        font-size: 1rem;
    }
</style>

<div class="container mt-4">
    <h1 class="text-center mb-4">Orden de Trabajo</h1>

    <div class="d-flex justify-content-between mb-3">
        <a href="index.php?pagina=nuevo/orden_trabajo" class="btn btn-danger">
            <i class="bi bi-plus-circle"></i> Nueva Orden
        </a>
        <input type="text" id="buscador" class="form-control w-50" placeholder="Buscar presupuesto...">
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle" id="tablaOrden">
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
                    <?php if ($orden["estado"] !== "completado"): ?>
                    <tr>
                        <td><?php echo str_pad($orden["id_orden"], 6, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo $orden["marca"] . " " . $orden["modelo"] . "<br>(" . $orden["matricula"] . ")"; ?>
                        </td>
                        <td><?php echo $orden["nombre_cliente"]; ?></td>
                        <td><?php echo $orden["nombre_personal"]; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($orden["fecha_ingreso"])); ?></td>
                        <td>
                            <?php
                            if ($orden["fecha_salida"]) {
                                echo date('d/m/Y', strtotime($orden["fecha_salida"]));
                            } else {
                                echo "Pendiente";
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            $badgeClass = "";
                            switch ($orden["estado"]) {
                                case "en_proceso":
                                    $badgeClass = "bg-warning text-dark";
                                    break;
                                case "completado":
                                    $badgeClass = "bg-success";
                                    break;
                                case "cancelado":
                                    $badgeClass = "bg-danger";
                                    break;
                                default:
                                    $badgeClass = "bg-secondary";
                            }
                            ?>
                            <span class="badge <?php echo $badgeClass; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $orden["estado"])); ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="index.php?pagina=ver/orden_trabajo&id=<?php echo $orden["id_orden"]; ?>"
                                    class="btn btn-info btn-sm" title="Ver Detalles">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                                <?php if ($orden["estado"] == "en_proceso"): ?>
                                    <a href="index.php?pagina=editar/orden_trabajo&id=<?php echo $orden["id_orden"]; ?>"
                                        class="btn btn-warning btn-sm" title="Editar">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <button class="btn btn-danger btn-sm"
                                        onclick="eliminarOrden(<?php echo $orden['id_orden']; ?>)" title="Eliminar">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                <?php endif; ?>
                                <?php if ($orden["estado"] == "completado" && !$orden["facturado"]): ?>
<a href="index.php?pagina=nuevo/factura&desde_orden=<?php echo $orden["id_orden"]; ?>"
    class="btn btn-success btn-sm" title="Facturar">
    <i class="bi bi-receipt"></i>
</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Formulario oculto para eliminación -->
<form id="formEliminarOrden" method="post" style="display: none;">
    <input type="hidden" name="eliminarOrden" id="idOrdenEliminar">
</form>

<script>
    // Buscador en tiempo real
    document.getElementById('buscador').addEventListener('keyup', function () {
        const texto = this.value.toLowerCase();
        const tabla = document.getElementById('tablaOrden');
        const filas = tabla.getElementsByTagName('tr');

        for (let i = 1; i < filas.length; i++) {
            const fila = filas[i];
            const contenido = fila.textContent.toLowerCase();
            fila.style.display = contenido.includes(texto) ? '' : 'none';
        }
    });
    // Función para eliminar orden
    function eliminarOrden(id) {
        Swal.fire({
            title: '¿Está seguro?',
            text: "¡No podrá revertir esta acción!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Usar formulario oculto para enviar POST
                document.getElementById('idOrdenEliminar').value = id;
                document.getElementById('formEliminarOrden').submit();
            }
        });
    }
</script>

<?php
// Manejar eliminación correctamente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["eliminarOrden"])) {
    $eliminar = OrdenTrabajoControlador::ctrEliminarOrdenTrabajo();

    // Si la eliminación fue exitosa, recargar la página
    if ($eliminar == "ok") {
        echo '<script>
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }
            setTimeout(function(){
                window.location.reload();
            }, 1000);
        </script>';
    }
}
?>
