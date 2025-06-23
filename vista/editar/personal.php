<?php
if (!isset($_SESSION["validarIngreso"])) {
	echo '<script>window.location = "index.php?pagina=login";</script>';
	return;
} elseif ($_SESSION["validarIngreso"] != "ok") {
	echo '<script>window.location = "index.php?pagina=login";</script>';
	return;
}

if (isset($_GET["id"])) {
	$item = "id_personal";
	$valor = $_GET["id"];
	$personal = ControladorPersonal::buscarPersonal($item, $valor);
}
?>

<br>
<title>EDITAR PERSONAL</title>
<style>
	body {
		background-color: white;
	}
	h1 {
		font-family: "Copperplate", Fantasy;
		color: red;
	}
</style>

<center><h1>EDITAR PERSONAL</h1></center>

<div class="btn-group">
	<a href="index.php?pagina=tabla/personales" class="btn btn-danger">Volver</a>
</div>

<div class="d-flex justify-content-center">
	<form class="p-5 w-50 bg-light" method="post">
		<input type="hidden" name="id" value="<?php echo $personal["id_personal"]; ?>">

		<div class="form-floating mb-3">
			<input type="text" class="form-control" name="nombre" value="<?php echo $personal["nombre"]; ?>" required>
			<label>NOMBRE</label>
		</div>

		<div class="form-floating mb-3">
			<input type="text" class="form-control" name="apellido" value="<?php echo $personal["apellido"]; ?>" required>
			<label>APELLIDO</label>
		</div>

		<div class="form-floating mb-3">
			<input type="text" class="form-control" name="cargo" value="<?php echo $personal["cargo"]; ?>" required>
			<label>CARGO</label>
		</div>

		<div class="form-floating mb-3">
			<input type="text" class="form-control" name="telefono" value="<?php echo $personal["telefono"]; ?>" required>
			<label>TELÉFONO</label>
		</div>

		<div class="form-floating mb-3">
			<input type="email" class="form-control" name="email" value="<?php echo $personal["email"]; ?>" required>
			<label>EMAIL</label>
		</div>

		<?php
			$actualizar = ControladorPersonal::actualizarPersonal();
			if ($actualizar == "ok") {
				echo '<script>
					if (window.history.replaceState) {
						window.history.replaceState(null, null, window.location.href);
					}
				</script>';
				echo '<div class="alert alert-success">El personal ha sido actualizado</div>
				<script>
					setTimeout(function(){
						window.location = "index.php?pagina=tabla/personales";
					}, 1000);
				</script>';
			}
		?>

		<br>
		<center><button type="submit" class="btn btn-danger">MODIFICAR</button></center>
	</form>
</div>
