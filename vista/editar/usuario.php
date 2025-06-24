<?php
if (!isset($_SESSION["validarIngreso"]) || $_SESSION["validarIngreso"] != "ok") {
    echo '<script>window.location = "index.php?pagina=login";</script>';
    return;
}

$tipo = (isset($_GET["tipo"]) && $_GET["tipo"] == "personal") ? "personal" : "cliente";
$titulo = "EDITAR USUARIO DE " . strtoupper($tipo);

if (!isset($_GET["id"])) {
    echo "<script>window.location = 'index.php?pagina=tabla/usuarios';</script>";
    return;
}
$id = $_GET["id"];
$usuario = ($tipo === "cliente") ? ControladorUsuario::buscarUsuarioClientePorId($id) : ControladorUsuario::buscarUsuarioPersonalPorId($id);

if (!$usuario) {
    echo "<script>window.location = 'index.php?pagina=tabla/usuarios';</script>";
    return;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id_usuario'])) {
    $actualizado = ControladorUsuario::actualizarUsuario($tipo);
    if ($actualizado === "ok") {
        echo '<script>window.location = "index.php?pagina=tabla/usuarios";</script>';
        exit();
    }
}
?>

<title><?php echo htmlspecialchars($titulo); ?></title>
<style>
    h1 {
        font-family: "Copperplate", Fantasy;
        color: red;
    }
</style>

<div class="container mt-4">
    <h1 class="text-center"><?php echo htmlspecialchars($titulo); ?></h1>
    <div class="d-flex justify-content-center">
        <form class="p-5 w-75 bg-light shadow-sm rounded" method="post">
            <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($id); ?>">
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Usuario"
                    value="<?php echo htmlspecialchars($usuario["usuario"]); ?>" required>
                <label for="usuario">NOMBRE DE USUARIO</label>
            </div>
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="contrasena" name="contrasena" placeholder="Contraseña">
                <label for="contrasena">NUEVA CONTRASEÑA (dejar en blanco para no cambiar)</label>
            </div>
            <div class="d-flex justify-content-center gap-2 mt-4">
                <button type="submit" class="btn btn-danger">MODIFICAR USUARIO</button>
                <a href="index.php?pagina=tabla/usuarios" class="btn btn-secondary">VOLVER</a>
            </div>
        </form>
    </div>
</div>