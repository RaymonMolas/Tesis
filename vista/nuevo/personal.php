<?php
if (!isset($_SESSION["validarIngreso"])) {
	echo '<script>window.location = "index.php?pagina=login";</script>';
	return;
} elseif ($_SESSION["validarIngreso"] != "ok") {
	echo '<script>window.location = "index.php?pagina=login";</script>';
	return;
}
?>
<br>
<title>NUEVO PERSONAL</title>
<style>
	body {
		background-color: white;
	}
	h1 {
		font-family: "Copperplate", Fantasy;
		color: red;
	}
</style>

<center><h1>NUEVO PERSONAL</h1></center>

<div class="btn-group">
	<a href="index.php?pagina=tabla/personales" class="btn btn-danger">Volver</a>
</div>

<div class="d-flex justify-content-center">
	<form class="p-5 w-50 bg-light" method="post">
		<div class="form-floating mb-3">
			<input type="text" class="form-control" name="nombre" placeholder="Nombre" required>
			<label>NOMBRE</label>
		</div>

		<div class="form-floating mb-3">
			<input type="text" class="form-control" name="apellido" placeholder="Apellido" required>
			<label>APELLIDO</label>
		</div>

		<div class="form-floating mb-3">
			<input type="text" class="form-control" name="cargo" placeholder="Cargo" required>
			<label>CARGO</label>
		</div>

		<div class="form-floating mb-3">
			<input type="text" class="form-control" name="telefono" placeholder="Teléfono" required>
			<label>TELÉFONO</label>
		</div>

		<div class="form-floating mb-3">
			<input type="email" class="form-control" name="email" placeholder="Email" required>
			<label>EMAIL</label>
		</div>

		<?php
			$registro = ControladorPersonal::guardarPersonal();
			if ($registro == "ok") {
				echo '<script>
					if (window.history.replaceState) {
						window.history.replaceState(null, null, window.location.href);
					}
				</script>';
				echo '<div class="alert alert-success">El personal ha sido registrado</div>
				<script>
					setTimeout(function(){
						window.location = "index.php?pagina=tabla/personales";
					}, 1000);
				</script>';
			}
		?>

		<br>
		<center><button type="submit" class="btn btn-danger">GUARDAR</button></center>
	</form>
</div>
