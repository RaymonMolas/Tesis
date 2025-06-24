<?php
if (!isset($_SESSION["validarIngreso"]) || $_SESSION["validarIngreso"] != "ok") {
	echo '<script>window.location = "index.php?pagina=login";</script>';
	return;
}

if (!isset($_GET["id"])) {
	echo '<script>window.location = "index.php?pagina=tabla/personales";</script>';
	return;
}
$item = "id_personal";
$valor = $_GET["id"];
$personal = ControladorPersonal::buscarPersonal($item, $valor);

if (!$personal) {
	echo '<script>window.location = "index.php?pagina=tabla/personales";</script>';
	return;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_personal'])) {
	$actualizar = ControladorPersonal::actualizarPersonal();
	if ($actualizar == "ok") {
		$_SESSION['mensaje_flash'] = ["tipo" => "success", "titulo" => "¡Actualizado!", "texto" => "Los datos del personal han sido actualizados."];
		echo '<script>window.location = "index.php?pagina=tabla/personales";</script>';
		exit();
	}
}
?>

<title>EDITAR PERSONAL</title>
<style>
	h1 {
		font-family: "Copperplate", Fantasy;
		color: red;
	}
</style>

<div class="container mt-4">
	<h1 class="text-center">EDITAR PERSONAL</h1>
	<div class="d-flex justify-content-center">
		<form class="p-5 w-75 bg-light shadow-sm rounded" method="post">
			<input type="hidden" name="id_personal" value="<?php echo htmlspecialchars($personal["id_personal"]); ?>">

			<div class="form-floating mb-3"><input type="text" class="form-control" name="nombre"
					value="<?php echo htmlspecialchars($personal["nombre"]); ?>" required><label>NOMBRE</label></div>
			<div class="form-floating mb-3"><input type="text" class="form-control" name="apellido"
					value="<?php echo htmlspecialchars($personal["apellido"]); ?>" required><label>APELLIDO</label>
			</div>
			<div class="form-floating mb-3"><input type="text" class="form-control" name="cargo"
					value="<?php echo htmlspecialchars($personal["cargo"]); ?>" required><label>CARGO</label></div>
			<div class="form-floating mb-3"><input type="text" class="form-control" name="telefono"
					value="<?php echo htmlspecialchars($personal["telefono"]); ?>" required><label>TELÉFONO</label>
			</div>
			<div class="form-floating mb-3"><input type="email" class="form-control" name="email"
					value="<?php echo htmlspecialchars($personal["email"]); ?>" required><label>EMAIL</label></div>

			<div class="d-flex justify-content-center gap-2 mt-4">
				<button type="submit" class="btn btn-danger">MODIFICAR</button>
				<a href="index.php?pagina=tabla/personales" class="btn btn-secondary">CANCELAR</a>
			</div>
		</form>
	</div>
</div>