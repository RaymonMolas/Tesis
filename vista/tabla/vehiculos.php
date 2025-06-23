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

// Handle AJAX request for vehicles by client
if(isset($_GET['accion']) && $_GET['accion'] == 'listar_por_cliente' && isset($_GET['id_cliente'])) {
    $vehiculos = VehiculoControlador::ctrListarVehiculosCliente($_GET['id_cliente']);
    header('Content-Type: application/json');
    echo json_encode($vehiculos);
    exit;
}

// Normal page load
$vehiculos = VehiculoControlador::ctrListarVehiculos();
?>

<!-- Rest of the vehiculos.php table view content -->
<div class="container mt-5">
    <h1 class="text-center">VEHÍCULOS</h1>

    <div class="d-flex justify-content-between my-3">
        <a href="index.php?pagina=nuevo/vehiculo" class="btn btn-danger">
            <i class="bi bi-plus-circle"></i> Nuevo Vehículo
        </a>
        <input type="text" id="buscadorVehiculos" class="form-control w-50" placeholder="Buscar vehículo...">
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle" id="tablaVehiculos">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Matrícula</th>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Año</th>
                    <th>Color</th>
                    <th>Cliente</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody class="table-light">
                <?php foreach ($vehiculos as $vehiculo): ?>
                    <tr>
                        <td><?php echo $vehiculo["id_vehiculo"]; ?></td>
                        <td><?php echo $vehiculo["matricula"]; ?></td>
                        <td><?php echo $vehiculo["marca"]; ?></td>
                        <td><?php echo $vehiculo["modelo"]; ?></td>
                        <td><?php echo $vehiculo["anho"]; ?></td>
                        <td><?php echo $vehiculo["color"]; ?></td>
                        <td><?php echo $vehiculo["nombre_cliente"]; ?></td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="index.php?pagina=editar/vehiculo&id=<?php echo $vehiculo["id_vehiculo"]; ?>" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="id_vehiculo" value="<?php echo $vehiculo["id_vehiculo"]; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este vehículo?');">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                    <?php
                                        $eliminar = new VehiculoControlador();
                                        $eliminar->ctrEliminarVehiculo();
                                    ?>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById("buscadorVehiculos").addEventListener("keyup", function() {
    const filtro = this.value.toLowerCase();
    document.querySelectorAll("#tablaVehiculos tbody tr").forEach(row => {
        const texto = row.textContent.toLowerCase();
        row.style.display = texto.includes(filtro) ? "" : "none";
    });
});
</script>

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
