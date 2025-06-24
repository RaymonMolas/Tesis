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

$citasCompletadas = ControladorHistoricoCitas::ctrObtenerCitasCompletadas();
?>

<title>HISTORIAL DE CITAS</title>
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
	<h1 class="text-center">HISTORIAL DE CITAS COMPLETADAS</h1>

	<div class="d-flex justify-content-between my-3">
		<span></span> <!-- Espacio para botón u otra acción futura -->
		<input type="text" id="buscadorCitas" class="form-control w-50" placeholder="Buscar cita por cliente...">
	</div>

	<div class="table-responsive">
		<table class="table table-hover table-bordered align-middle" id="tablaCitas">
			<thead class="table-dark">
				<tr>
					<th>ID</th>
					<th>Cliente</th>
					<th>Fecha</th>
					<th>Hora</th>
					<th>Motivo</th>
				</tr>
			</thead>
			<tbody class="table-light">
				<?php if (!empty($citasCompletadas)): ?>
					<?php $contador = 1; ?>
					<?php foreach ($citasCompletadas as $cita): ?>
						<tr>
							<td><?php echo $contador++; ?></td>
							<td><?php echo $cita["nombre"] . ' ' . $cita["apellido"]; ?></td>
							<td><?php echo $cita["fecha"]; ?></td>
							<td><?php echo $cita["hora"]; ?></td>
							<td><?php echo $cita["motivo"]; ?></td>
						</tr>
					<?php endforeach; ?>
				<?php else: ?>
					<tr>
						<td colspan="5" class="text-center">No hay citas completadas aún.</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>

<!-- Buscador en tiempo real -->
<script>
document.getElementById("buscadorCitas").addEventListener("keyup", function() {
	const filtro = this.value.toLowerCase();
	const filas = document.querySelectorAll("#tablaCitas tbody tr");

	filas.forEach(function(fila) {
		const textoFila = fila.textContent.toLowerCase();
		fila.style.display = textoFila.includes(filtro) ? "" : "none";
	});
});
</script>
