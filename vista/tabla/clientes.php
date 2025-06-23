<?php
if (!isset($_SESSION["validarIngreso"])) {
	echo '<script>window.location = "index.php?pagina=login";</script>';
	return;
} elseif ($_SESSION["validarIngreso"] != "ok") {
	echo '<script>window.location = "index.php?pagina=login";</script>';
	return;
}

$clientes = ClienteControlador::buscarCliente();
?>

<!-- Encabezado de la página -->
<div class="mb-8">
	<div class="sm:flex sm:items-center sm:justify-between">
		<div>
			<h1 class="text-2xl font-bold text-gray-900">Gestión de Clientes</h1>
			<p class="mt-2 text-sm text-gray-700">
				Administra la información de todos los clientes registrados en el sistema
			</p>
		</div>
		<div class="mt-4 sm:mt-0">
			<a href="index.php?pagina=nuevo/cliente"
				class="inline-flex items-center justify-center rounded-lg bg-motor-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-motor-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-motor-red-600 transition-colors duration-200">
				<i data-lucide="user-plus" class="w-4 h-4 mr-2"></i>
				Nuevo Cliente
			</a>
		</div>
	</div>
</div>

<!-- Estadísticas rápidas -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
	<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
		<div class="flex items-center">
			<div class="p-2 bg-blue-50 rounded-lg">
				<i data-lucide="users" class="w-5 h-5 text-blue-600"></i>
			</div>
			<div class="ml-3">
				<p class="text-sm font-medium text-gray-600">Total Clientes</p>
				<p class="text-xl font-bold text-gray-900"><?php echo count($clientes); ?></p>
			</div>
		</div>
	</div>

	<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
		<div class="flex items-center">
			<div class="p-2 bg-green-50 rounded-lg">
				<i data-lucide="user-check" class="w-5 h-5 text-green-600"></i>
			</div>
			<div class="ml-3">
				<p class="text-sm font-medium text-gray-600">Clientes Activos</p>
				<p class="text-xl font-bold text-gray-900"><?php echo count($clientes); ?></p>
			</div>
		</div>
	</div>

	<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
		<div class="flex items-center">
			<div class="p-2 bg-yellow-50 rounded-lg">
				<i data-lucide="calendar" class="w-5 h-5 text-yellow-600"></i>
			</div>
			<div class="ml-3">
				<p class="text-sm font-medium text-gray-600">Este Mes</p>
				<p class="text-xl font-bold text-gray-900">
					<?php
					$clientesEsteMes = array_filter($clientes, function ($cliente) {
						return date('Y-m', strtotime($cliente['fecha_registro'])) == date('Y-m');
					});
					echo count($clientesEsteMes);
					?>
				</p>
			</div>
		</div>
	</div>

	<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
		<div class="flex items-center">
			<div class="p-2 bg-motor-red-50 rounded-lg">
				<i data-lucide="car" class="w-5 h-5 text-motor-red-600"></i>
			</div>
			<div class="ml-3">
				<p class="text-sm font-medium text-gray-600">Con Vehículos</p>
				<p class="text-xl font-bold text-gray-900">
					<?php
					// Aquí podrías contar clientes con vehículos registrados
					echo count($clientes);
					?>
				</p>
			</div>
		</div>
	</div>
</div>

<!-- Filtros y búsqueda -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
	<div class="p-6">
		<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
			<!-- Búsqueda general -->
			<div class="md:col-span-2">
				<label for="busqueda-general" class="block text-sm font-medium text-gray-700 mb-2">
					Búsqueda General
				</label>
				<div class="relative">
					<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
						<i data-lucide="search" class="h-5 w-5 text-gray-400"></i>
					</div>
					<input type="text" id="busqueda-general"
						class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-motor-red-500 focus:border-motor-red-500"
						placeholder="Buscar por nombre, cédula, teléfono o email...">
				</div>
			</div>

			<!-- Filtro por fecha -->
			<div>
				<label for="filtro-fecha" class="block text-sm font-medium text-gray-700 mb-2">
					Período de Registro
				</label>
				<select id="filtro-fecha"
					class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-motor-red-500 focus:border-motor-red-500">
					<option value="">Todos los períodos</option>
					<option value="hoy">Hoy</option>
					<option value="semana">Esta semana</option>
					<option value="mes">Este mes</option>
					<option value="trimestre">Este trimestre</option>
					<option value="año">Este año</option>
				</select>
			</div>

			<!-- Botón limpiar filtros -->
			<div class="flex items-end">
				<button id="limpiar-filtros"
					class="w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-motor-red-500 focus:border-motor-red-500 transition-colors duration-200">
					<i data-lucide="x" class="w-4 h-4 mr-2 inline"></i>
					Limpiar
				</button>
			</div>
		</div>
	</div>
</div>

<!-- Tabla de clientes -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
	<div class="px-6 py-4 border-b border-gray-200">
		<div class="flex items-center justify-between">
			<h3 class="text-lg font-semibold text-gray-900">Lista de Clientes</h3>
			<div class="flex items-center space-x-2">
				<!-- Botón exportar -->
				<div class="relative inline-block text-left">
					<button type="button" id="export-menu-button"
						class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:ring-2 focus:ring-motor-red-500">
						<i data-lucide="download" class="w-4 h-4 mr-2"></i>
						Exportar
						<i data-lucide="chevron-down" class="w-4 h-4 ml-2"></i>
					</button>
					<div id="export-menu"
						class="hidden absolute right-0 z-10 mt-2 w-48 rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5">
						<div class="py-1">
							<a href="#" onclick="exportTable('excel')"
								class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
								<i data-lucide="file-spreadsheet" class="w-4 h-4 mr-3 text-green-500"></i>
								Excel
							</a>
							<a href="#" onclick="exportTable('pdf')"
								class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
								<i data-lucide="file-text" class="w-4 h-4 mr-3 text-red-500"></i>
								PDF
							</a>
							<a href="#" onclick="exportTable('csv')"
								class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
								<i data-lucide="file-csv" class="w-4 h-4 mr-3 text-blue-500"></i>
								CSV
							</a>
						</div>
					</div>
				</div>

				<!-- Botón vista -->
				<button type="button" id="vista-toggle"
					class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:ring-2 focus:ring-motor-red-500">
					<i data-lucide="grid" class="w-4 h-4 mr-2"></i>
					Vista
				</button>
			</div>
		</div>
	</div>

	<!-- Vista de tabla (por defecto) -->
	<div id="vista-tabla" class="overflow-x-auto">
		<table class="min-w-full divide-y divide-gray-200" id="tablaClientes">
			<thead class="bg-gray-50">
				<tr>
					<th scope="col"
						class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
						<div class="flex items-center space-x-1">
							<span>Cliente</span>
							<i data-lucide="arrow-up-down" class="w-3 h-3"></i>
						</div>
					</th>
					<th scope="col"
						class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
						<div class="flex items-center space-x-1">
							<span>Cédula</span>
							<i data-lucide="arrow-up-down" class="w-3 h-3"></i>
						</div>
					</th>
					<th scope="col"
						class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
						Contacto
					</th>
					<th scope="col"
						class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
						Dirección
					</th>
					<th scope="col"
						class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
						<div class="flex items-center space-x-1">
							<span>Registro</span>
							<i data-lucide="arrow-up-down" class="w-3 h-3"></i>
						</div>
					</th>
					<th scope="col"
						class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
						Acciones
					</th>
				</tr>
			</thead>
			<tbody class="bg-white divide-y divide-gray-200">
				<?php foreach ($clientes as $cliente): ?>
					<tr class="hover:bg-gray-50 transition-colors duration-200">
						<td class="px-6 py-4 whitespace-nowrap">
							<div class="flex items-center">
								<div class="h-10 w-10 flex-shrink-0">
									<div class="h-10 w-10 rounded-full bg-motor-red-100 flex items-center justify-center">
										<span class="text-sm font-medium text-motor-red-600">
											<?php echo strtoupper(substr($cliente["nombre"], 0, 1) . substr($cliente["apellido"], 0, 1)); ?>
										</span>
									</div>
								</div>
								<div class="ml-4">
									<div class="text-sm font-medium text-gray-900">
										<?php echo $cliente["nombre"] . " " . $cliente["apellido"]; ?>
									</div>
									<div class="text-sm text-gray-500">
										ID: #<?php echo str_pad($cliente["id_cliente"], 4, '0', STR_PAD_LEFT); ?>
									</div>
								</div>
							</div>
						</td>
						<td class="px-6 py-4 whitespace-nowrap">
							<div class="text-sm text-gray-900 font-mono"><?php echo $cliente["cedula"]; ?></div>
						</td>
						<td class="px-6 py-4 whitespace-nowrap">
							<div class="text-sm text-gray-900">
								<div class="flex items-center mb-1">
									<i data-lucide="phone" class="w-3 h-3 mr-1 text-gray-400"></i>
									<?php echo $cliente["telefono"] ?: 'No registrado'; ?>
								</div>
								<div class="flex items-center">
									<i data-lucide="mail" class="w-3 h-3 mr-1 text-gray-400"></i>
									<span
										class="truncate max-w-xs"><?php echo $cliente["email"] ?: 'No registrado'; ?></span>
								</div>
							</div>
						</td>
						<td class="px-6 py-4">
							<div class="text-sm text-gray-900 max-w-xs truncate"
								title="<?php echo $cliente["direccion"]; ?>">
								<?php echo $cliente["direccion"] ?: 'No registrada'; ?>
							</div>
						</td>
						<td class="px-6 py-4 whitespace-nowrap">
							<div class="text-sm text-gray-900">
								<?php echo date('d/m/Y', strtotime($cliente["fecha_registro"])); ?>
							</div>
							<div class="text-sm text-gray-500">
								<?php echo date('H:i', strtotime($cliente["fecha_registro"])); ?>
							</div>
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
							<div class="flex items-center space-x-2">
								<!-- Ver detalles -->
								<button onclick="verDetalleCliente(<?php echo $cliente['id_cliente']; ?>)"
									class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50 transition-colors duration-200"
									title="Ver detalles">
									<i data-lucide="eye" class="w-4 h-4"></i>
								</button>

								<!-- Editar -->
								<a href="index.php?pagina=editar/cliente&id=<?php echo $cliente["id_cliente"]; ?>"
									class="text-yellow-600 hover:text-yellow-900 p-1 rounded hover:bg-yellow-50 transition-colors duration-200"
									title="Editar">
									<i data-lucide="edit" class="w-4 h-4"></i>
								</a>

								<!-- Vehículos -->
								<button onclick="verVehiculos(<?php echo $cliente['id_cliente']; ?>)"
									class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-50 transition-colors duration-200"
									title="Ver vehículos">
									<i data-lucide="car" class="w-4 h-4"></i>
								</button>

								<!-- Historial -->
								<button onclick="verHistorial(<?php echo $cliente['id_cliente']; ?>)"
									class="text-purple-600 hover:text-purple-900 p-1 rounded hover:bg-purple-50 transition-colors duration-200"
									title="Ver historial">
									<i data-lucide="history" class="w-4 h-4"></i>
								</button>

								<!-- Eliminar -->
								<button
									onclick="eliminarCliente(<?php echo $cliente['id_cliente']; ?>, '<?php echo $cliente['nombre'] . ' ' . $cliente['apellido']; ?>')"
									class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50 transition-colors duration-200"
									title="Eliminar">
									<i data-lucide="trash-2" class="w-4 h-4"></i>
								</button>
							</div>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<!-- Vista de tarjetas (oculta por defecto) -->
	<div id="vista-tarjetas" class="hidden p-6">
		<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
			<?php foreach ($clientes as $cliente): ?>
				<div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow duration-200">
					<div class="flex items-center mb-4">
						<div class="h-12 w-12 rounded-full bg-motor-red-100 flex items-center justify-center">
							<span class="text-lg font-medium text-motor-red-600">
								<?php echo strtoupper(substr($cliente["nombre"], 0, 1) . substr($cliente["apellido"], 0, 1)); ?>
							</span>
						</div>
						<div class="ml-3">
							<h3 class="text-lg font-medium text-gray-900">
								<?php echo $cliente["nombre"] . " " . $cliente["apellido"]; ?>
							</h3>
							<p class="text-sm text-gray-500">ID:
								#<?php echo str_pad($cliente["id_cliente"], 4, '0', STR_PAD_LEFT); ?></p>
						</div>
					</div>

					<div class="space-y-2 mb-4">
						<div class="flex items-center text-sm text-gray-600">
							<i data-lucide="id-card" class="w-4 h-4 mr-2"></i>
							<span class="font-mono"><?php echo $cliente["cedula"]; ?></span>
						</div>
						<div class="flex items-center text-sm text-gray-600">
							<i data-lucide="phone" class="w-4 h-4 mr-2"></i>
							<span><?php echo $cliente["telefono"] ?: 'No registrado'; ?></span>
						</div>
						<div class="flex items-center text-sm text-gray-600">
							<i data-lucide="mail" class="w-4 h-4 mr-2"></i>
							<span class="truncate"><?php echo $cliente["email"] ?: 'No registrado'; ?></span>
						</div>
						<div class="flex items-center text-sm text-gray-600">
							<i data-lucide="map-pin" class="w-4 h-4 mr-2"></i>
							<span class="truncate"><?php echo $cliente["direccion"] ?: 'No registrada'; ?></span>
						</div>
					</div>

					<div class="flex items-center justify-between pt-4 border-t border-gray-200">
						<span class="text-xs text-gray-500">
							Registro: <?php echo date('d/m/Y', strtotime($cliente["fecha_registro"])); ?>
						</span>
						<div class="flex items-center space-x-1">
							<button onclick="verDetalleCliente(<?php echo $cliente['id_cliente']; ?>)"
								class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50" title="Ver detalles">
								<i data-lucide="eye" class="w-4 h-4"></i>
							</button>
							<a href="index.php?pagina=editar/cliente&id=<?php echo $cliente["id_cliente"]; ?>"
								class="text-yellow-600 hover:text-yellow-900 p-1 rounded hover:bg-yellow-50" title="Editar">
								<i data-lucide="edit" class="w-4 h-4"></i>
							</a>
							<button
								onclick="eliminarCliente(<?php echo $cliente['id_cliente']; ?>, '<?php echo $cliente['nombre'] . ' ' . $cliente['apellido']; ?>')"
								class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50" title="Eliminar">
								<i data-lucide="trash-2" class="w-4 h-4"></i>
							</button>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>

<!-- Modal para detalles del cliente -->
<div id="modal-detalle-cliente" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title"
	role="dialog" aria-modal="true">
	<div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
		<div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
		<span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
		<div
			class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
			<div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
				<div class="sm:flex sm:items-start">
					<div class="w-full">
						<h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
							Detalles del Cliente
						</h3>
						<div class="mt-4" id="detalle-cliente-content">
							<!-- Contenido dinámico -->
						</div>
					</div>
				</div>
			</div>
			<div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
				<button type="button" onclick="cerrarModal('modal-detalle-cliente')"
					class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-motor-red-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
					Cerrar
				</button>
			</div>
		</div>
	</div>
</div>

<script>
	document.addEventListener('DOMContentLoaded', function () {
		// Inicializar iconos
		lucide.createIcons();

		// Configurar DataTable
		const tabla = $('#tablaClientes').DataTable({
			responsive: true,
			pageLength: 25,
			order: [[0, 'asc']],
			columnDefs: [
				{ orderable: false, targets: [5] } // Columna de acciones no ordenable
			],
			language: {
				url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
			}
		});

		// Búsqueda general
		document.getElementById('busqueda-general').addEventListener('keyup', function () {
			tabla.search(this.value).draw();
		});

		// Filtro por fecha
		document.getElementById('filtro-fecha').addEventListener('change', function () {
			const valor = this.value;
			if (valor === '') {
				tabla.column(4).search('').draw();
			} else {
				// Aquí puedes implementar la lógica de filtrado por fecha
				// según el valor seleccionado
			}
		});

		// Limpiar filtros
		document.getElementById('limpiar-filtros').addEventListener('click', function () {
			document.getElementById('busqueda-general').value = '';
			document.getElementById('filtro-fecha').value = '';
			tabla.search('').columns().search('').draw();
		});

		// Toggle vista
		document.getElementById('vista-toggle').addEventListener('click', function () {
			const vistaTabla = document.getElementById('vista-tabla');
			const vistaTarjetas = document.getElementById('vista-tarjetas');
			const icon = this.querySelector('i');

			if (vistaTabla.classList.contains('hidden')) {
				vistaTabla.classList.remove('hidden');
				vistaTarjetas.classList.add('hidden');
				icon.setAttribute('data-lucide', 'grid');
				this.querySelector('span').textContent = 'Vista';
			} else {
				vistaTabla.classList.add('hidden');
				vistaTarjetas.classList.remove('hidden');
				icon.setAttribute('data-lucide', 'list');
				this.querySelector('span').textContent = 'Tabla';
			}
			lucide.createIcons();
		});

		// Menu exportar
		document.getElementById('export-menu-button').addEventListener('click', function () {
			const menu = document.getElementById('export-menu');
			menu.classList.toggle('hidden');
		});

		// Cerrar menu al hacer click fuera
		document.addEventListener('click', function (e) {
			const menu = document.getElementById('export-menu');
			const button = document.getElementById('export-menu-button');
			if (!button.contains(e.target) && !menu.contains(e.target)) {
				menu.classList.add('hidden');
			}
		});
	});

	// Funciones para acciones
	function verDetalleCliente(id) {
		// Aquí harías una petición AJAX para obtener los detalles
		// Por ahora mostramos datos de ejemplo
		const contenido = `
		<div class="space-y-4">
			<div class="grid grid-cols-2 gap-4">
				<div>
					<label class="block text-sm font-medium text-gray-700">Nombre Completo</label>
					<p class="mt-1 text-sm text-gray-900">Juan Pérez</p>
				</div>
				<div>
					<label class="block text-sm font-medium text-gray-700">Cédula</label>
					<p class="mt-1 text-sm text-gray-900">1.234.567-8</p>
				</div>
				<div>
					<label class="block text-sm font-medium text-gray-700">Teléfono</label>
					<p class="mt-1 text-sm text-gray-900">0981 123 456</p>
				</div>
				<div>
					<label class="block text-sm font-medium text-gray-700">Email</label>
					<p class="mt-1 text-sm text-gray-900">juan@email.com</p>
				</div>
			</div>
			<div>
				<label class="block text-sm font-medium text-gray-700">Dirección</label>
				<p class="mt-1 text-sm text-gray-900">Barrio San Pablo, Asunción</p>
			</div>
			<div class="border-t pt-4">
				<h4 class="font-medium text-gray-900 mb-2">Estadísticas</h4>
				<div class="grid grid-cols-3 gap-4 text-center">
					<div>
						<p class="text-2xl font-bold text-motor-red-600">3</p>
						<p class="text-xs text-gray-500">Vehículos</p>
					</div>
					<div>
						<p class="text-2xl font-bold text-blue-600">12</p>
						<p class="text-xs text-gray-500">Servicios</p>
					</div>
					<div>
						<p class="text-2xl font-bold text-green-600">5</p>
						<p class="text-xs text-gray-500">Facturas</p>
					</div>
				</div>
			</div>
		</div>
	`;

		document.getElementById('detalle-cliente-content').innerHTML = contenido;
		document.getElementById('modal-detalle-cliente').classList.remove('hidden');
	}

	function verVehiculos(id) {
		window.location.href = `index.php?pagina=tabla/vehiculos&cliente=${id}`;
	}

	function verHistorial(id) {
		window.location.href = `index.php?pagina=tabla/historicocitas&cliente=${id}`;
	}

	function eliminarCliente(id, nombre) {
		Swal.fire({
			title: '¿Estás seguro?',
			text: `¿Deseas eliminar al cliente "${nombre}"? Esta acción no se puede deshacer.`,
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#dc2626',
			cancelButtonColor: '#6b7280',
			confirmButtonText: 'Sí, eliminar',
			cancelButtonText: 'Cancelar'
		}).then((result) => {
			if (result.isConfirmed) {
				// Aquí harías la petición para eliminar
				Swal.fire(
					'Eliminado',
					'El cliente ha sido eliminado correctamente.',
					'success'
				).then(() => {
					location.reload();
				});
			}
		});
	}

	function cerrarModal(modalId) {
		document.getElementById(modalId).classList.add('hidden');
	}

	function exportTable(format) {
		document.getElementById('export-menu').classList.add('hidden');

		Swal.fire({
			title: 'Exportando...',
			text: `Generando archivo ${format.toUpperCase()}`,
			allowOutsideClick: false,
			didOpen: () => {
				Swal.showLoading();
			}
		});

		// Simular export (aquí implementarías la lógica real)
		setTimeout(() => {
			Swal.fire({
				icon: 'success',
				title: 'Exportación completa',
				text: `El archivo ${format.toUpperCase()} ha sido generado correctamente.`,
				showConfirmButton: true
			});
		}, 2000);
	}
</script>