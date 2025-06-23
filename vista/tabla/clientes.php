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
	$usuarios = ClienteControlador::buscarCliente(null, null);
?>

<title>CLIENTES</title>
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
	<h1 class="text-center">CLIENTES</h1>

	<div class="d-flex justify-content-between my-3">
		<a href="index.php?pagina=nuevo/cliente" class="btn btn-danger">
			<i class="bi bi-person-plus-fill"></i> Nuevo Cliente
		</a>
		<input type="text" id="buscadorClientes" class="form-control w-50" placeholder="Buscar cliente...">
	</div>

	<div class="table-responsive">
		<table class="table table-hover table-bordered align-middle" id="tablaClientes">
			<thead class="table-dark">
				<tr>
					<th>ID</th>
					<th>Nombre</th>
					<th>Apellido</th>
					<th>Cédula</th>
					<th>Dirección</th>
					<th>Teléfono</th>
					<th>Email</th>
					<th>Acciones</th>
				</tr>
			</thead>
			<tbody class="table-light">
				<?php foreach ($usuarios as $value): ?>
					<tr>
						<td><?php echo $value["id_cliente"]; ?></td>
						<td><?php echo $value["nombre"]; ?></td>
						<td><?php echo $value["apellido"]; ?></td>
						<td><?php echo $value["cedula"]; ?></td>
						<td><?php echo $value["direccion"]; ?></td>
						<td><?php echo $value["telefono"]; ?></td>
						<td><?php echo $value["email"]; ?></td>
						<td>
							<div class="d-flex justify-content-center gap-2">
								<a href="index.php?pagina=editar/cliente&id=<?php echo $value["id_cliente"]; ?>" class="btn btn-warning btn-sm" title="Editar">
									<i class="bi bi-pencil-fill"></i>
								</a>
								<form method="post" class="d-inline">
									<input type="hidden" name="eliminarRegistro" value="<?php echo $value["id_cliente"]; ?>">
									<button type="submit" class="btn btn-danger btn-sm" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este cliente?');">
										<i class="bi bi-trash-fill"></i>
									</button>
									<?php
										$eliminar = new ClienteControlador();
										$eliminar->eliminarCliente();
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
document.getElementById("buscadorClientes").addEventListener("keyup", function() {
	const filtro = this.value.toLowerCase();
	const filas = document.querySelectorAll("#tablaClientes tbody tr");

	filas.forEach(function(fila) {
		const textoFila = fila.textContent.toLowerCase();
		fila.style.display = textoFila.includes(filtro) ? "" : "none";
	});
});
</script>
