<?php
if (!isset($_SESSION["validarIngreso"])) {
    echo '<script>window.location = "index.php?pagina=login";</script>';
    return;
} elseif ($_SESSION["validarIngreso"] != "ok") {
    echo '<script>window.location = "index.php?pagina=login";</script>';
    return;
}

// Mantener selección del combo con sesión
if (isset($_POST["tipo_usuario"])) {
    $_SESSION["tipo_usuario_seleccionado"] = $_POST["tipo_usuario"];
}

$tipo = $_SESSION["tipo_usuario_seleccionado"] ?? "cliente";
$usuarios = ControladorUsuario::buscarUsuarios($tipo);
?>

<title>USUARIOS</title>
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
    <h1 class="text-center">USUARIOS</h1>

    <!-- Selector tipo de usuario -->
    <form method="post" class="d-flex justify-content-between align-items-center my-4 flex-wrap gap-3">
        <div class="d-flex align-items-center">
            <label for="tipo_usuario" class="me-2">Seleccionar tipo de usuario:</label>
            <select name="tipo_usuario" id="tipo_usuario" onchange="this.form.submit()" class="form-select w-auto">
                <option value="cliente" <?php if ($tipo == "cliente") echo "selected"; ?>>Cliente</option>
                <option value="personal" <?php if ($tipo == "personal") echo "selected"; ?>>Personal</option>
            </select>
        </div>
    </form>

    <!-- Botón nuevo y buscador -->
    <div class="d-flex justify-content-between my-3 flex-wrap gap-3">
        <a href="index.php?pagina=nuevo/usuario&tipo=<?php echo $tipo; ?>" class="btn btn-danger">
            <i class="bi bi-person-plus-fill"></i> Nuevo Usuario
        </a>
        <input type="text" id="buscadorUsuarios" class="form-control w-50" placeholder="Buscar usuario...">
    </div>

    <!-- Tabla de usuarios -->
    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle" id="tablaUsuarios">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre <?php echo ($tipo == "cliente") ? "del Cliente" : "del Personal"; ?></th>
                    <th>Usuario</th>
                    <th>Contraseña</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody class="table-light">
                <?php foreach ($usuarios as $value): ?>
                    <tr>
                        <td><?php echo $tipo == "cliente" ? $value["id_usuario_cliente"] : $value["id_usuario_personal"]; ?></td>
                        <td><?php echo $value["nombre"] ?? 'No encontrado'; ?></td>
                        <td><?php echo $value["usuario"]; ?></td>
                        <td><?php echo $value["contrasena"]; ?></td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="index.php?pagina=editar/usuario&tipo=<?php echo $tipo; ?>&id=<?php echo ($tipo == 'cliente') ? $value['id_usuario_cliente'] : $value['id_usuario_personal']; ?>" class="btn btn-warning btn-sm" title="Editar">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="eliminarRegistro" value="<?php echo ($tipo == "cliente") ? $value["id_usuario_cliente"] : $value["id_usuario_personal"]; ?>">
                                    <input type="hidden" name="tipoEliminar" value="<?php echo $tipo; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este usuario?');">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                    <?php
                                        $eliminar = new ControladorUsuario();
                                        $eliminar->eliminarUsuario();
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

<!-- Buscador en tiempo real -->
<script>
document.getElementById("buscadorUsuarios").addEventListener("keyup", function() {
    const filtro = this.value.toLowerCase();
    const filas = document.querySelectorAll("#tablaUsuarios tbody tr");

    filas.forEach(function(fila) {
        const texto = fila.textContent.toLowerCase();
        fila.style.display = texto.includes(filtro) ? "" : "none";
    });
});
</script>
