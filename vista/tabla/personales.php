<?php
if (!isset($_SESSION["validarIngreso"])) {
    echo '<script>window.location = "index.php?pagina=login";</script>';
    return;
} elseif ($_SESSION["validarIngreso"] != "ok") {
    echo '<script>window.location = "index.php?pagina=login";</script>';
    return;
}

$personal = ControladorPersonal::buscarPersonal(null, null);
?>

<title>PERSONAL</title>
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

<div class="container mt-5">
    <h1 class="text-center">PERSONAL</h1>

    <div class="d-flex justify-content-between my-3">
        <a href="index.php?pagina=nuevo/personal" class="btn btn-danger">
            <i class="bi bi-person-plus-fill"></i> Nuevo Personal
        </a>
        <input type="text" id="buscadorPersonal" class="form-control w-50" placeholder="Buscar personal...">
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle" id="tablaPersonal">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Cargo</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody class="table-light">
                <?php foreach ($personal as $p): ?>
                    <tr>
                        <td><?php echo $p["id_personal"]; ?></td>
                        <td><?php echo $p["nombre"]; ?></td>
                        <td><?php echo $p["apellido"]; ?></td>
                        <td><?php echo $p["cargo"]; ?></td>
                        <td><?php echo $p["telefono"]; ?></td>
                        <td><?php echo $p["email"]; ?></td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="index.php?pagina=editar/personal&id=<?php echo $p["id_personal"]; ?>" class="btn btn-warning btn-sm" title="Editar">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="eliminarRegistro" value="<?php echo $p["id_personal"]; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este personal?');">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                    <?php
                                        $eliminar = new ControladorPersonal();
                                        $eliminar->eliminarPersonal();
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
document.getElementById("buscadorPersonal").addEventListener("keyup", function() {
    const filtro = this.value.toLowerCase();
    const filas = document.querySelectorAll("#tablaPersonal tbody tr");

    filas.forEach(function(fila) {
        const texto = fila.textContent.toLowerCase();
        fila.style.display = texto.includes(filtro) ? "" : "none";
    });
});
</script>
