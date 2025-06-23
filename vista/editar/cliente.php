<?php
// Validar sesión y permisos.
if (!isset($_SESSION["validarIngreso"]) || $_SESSION["validarIngreso"] != "ok") {
	echo '<script>window.location = "index.php?pagina=login";</script>';
	return;
}

// Validar que se haya proporcionado un ID.
if (!isset($_GET["id"])) {
	$_SESSION['mensaje_flash'] = ["tipo" => "error", "titulo" => "Error", "texto" => "No se especificó el cliente a editar."];
	echo '<script>window.location = "index.php?pagina=tabla/clientes";</script>';
	return;
}

$item = "id_cliente";
$valor = $_GET["id"];
$cliente = ClienteControlador::buscarcliente($item, $valor);

// Si no se encuentra el cliente, redirigir.
if (!$cliente) {
	$_SESSION['mensaje_flash'] = ["tipo" => "error", "titulo" => "No Encontrado", "texto" => "El cliente que intenta editar no existe."];
	echo '<script>window.location = "index.php?pagina=tabla/clientes";</script>';
	return;
}

// Procesar la actualización del formulario.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_cliente'])) {
	$actualizar = ClienteControlador::actualizarCliente();
	if ($actualizar == "ok") {
		$_SESSION['mensaje_flash'] = ["tipo" => "success", "titulo" => "¡Actualizado!", "texto" => "El cliente ha sido actualizado correctamente."];
		echo '<script>window.location = "index.php?pagina=tabla/clientes";</script>';
		exit();
	}
}
?>

<title>EDITAR CLIENTE</title>
<style>
	h1 {
		font-family: "Copperplate", Fantasy;
		color: red;
	}
</style>

<div class="container mt-4">
	<h1 class="text-center">EDITAR CLIENTE</h1>
	<div class="d-flex justify-content-center">
		<form class="p-5 w-75 bg-light shadow-sm rounded" method="post">
			<input type="hidden" name="id_cliente" value="<?php echo htmlspecialchars($cliente["id_cliente"]); ?>">

			<div class="form-floating mb-3">
				<input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre"
					value="<?php echo htmlspecialchars($cliente["nombre"]); ?>" required>
				<label for="nombre">NOMBRE</label>
			</div>
			<div class="form-floating mb-3">
				<input type="text" class="form-control" id="apellido" name="apellido" placeholder="Apellido"
					value="<?php echo htmlspecialchars($cliente["apellido"]); ?>" required>
				<label for="apellido">APELLIDO</label>
			</div>
			<div class="form-floating mb-3">
				<input type="text" class="form-control" id="cedula" name="cedula" placeholder="Cédula"
					value="<?php echo htmlspecialchars($cliente["cedula"]); ?>" required>
				<label for="cedula">CÉDULA</label>
			</div>
			<div class="form-floating mb-3">
				<input type="text" class="form-control" id="direccion" name="direccion" placeholder="Dirección"
					value="<?php echo htmlspecialchars($cliente["direccion"]); ?>" required>
				<label for="direccion">DIRECCIÓN</label>
			</div>
			<div class="form-floating mb-3">
				<input type="text" class="form-control" id="telefono" name="telefono" placeholder="Teléfono"
					value="<?php echo htmlspecialchars($cliente["telefono"]); ?>" required>
				<label for="telefono">TELÉFONO</label>
			</div>
			<div class="form-floating mb-3">
				<input type="email" class="form-control" id="email" name="email" placeholder="Email"
					value="<?php echo htmlspecialchars($cliente["email"]); ?>" required>
				<label for="email">EMAIL</label>
			</div>
			<div class="d-flex justify-content-center gap-2 mt-4">
				<button type="submit" class="btn btn-danger">MODIFICAR CLIENTE</button>
				<a href="index.php?pagina=tabla/clientes" class="btn btn-secondary">CANCELAR</a>
			</div>
		</form>
	</div>
</div>