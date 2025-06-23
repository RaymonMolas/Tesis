<?php
if (!isset($_SESSION["validarIngreso"])) {
	echo '<script>window.location = "index.php?pagina=login";</script>';
	return;
} elseif ($_SESSION["validarIngreso"] != "ok") {
	echo '<script>window.location = "index.php?pagina=login";</script>';
	return;
}
?>

<!-- Encabezado de la página -->
<div class="mb-8">
	<nav class="flex" aria-label="Breadcrumb">
		<ol role="list" class="flex items-center space-x-4">
			<li>
				<div>
					<a href="index.php?pagina=inicio" class="text-gray-400 hover:text-gray-500">
						<i data-lucide="home" class="h-5 w-5"></i>
						<span class="sr-only">Inicio</span>
					</a>
				</div>
			</li>
			<li>
				<div class="flex items-center">
					<i data-lucide="chevron-right" class="h-5 w-5 text-gray-400"></i>
					<a href="index.php?pagina=tabla/clientes" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">
						Clientes
					</a>
				</div>
			</li>
			<li>
				<div class="flex items-center">
					<i data-lucide="chevron-right" class="h-5 w-5 text-gray-400"></i>
					<span class="ml-4 text-sm font-medium text-gray-900">Nuevo Cliente</span>
				</div>
			</li>
		</ol>
	</nav>
	
	<div class="mt-4">
		<h1 class="text-2xl font-bold text-gray-900">Registrar Nuevo Cliente</h1>
		<p class="mt-2 text-sm text-gray-700">
			Complete la información del cliente para registrarlo en el sistema
		</p>
	</div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
	<!-- Formulario principal -->
	<div class="lg:col-span-2">
		<form method="post" id="formCliente" class="space-y-6">
			<!-- Información Personal -->
			<div class="bg-white shadow-sm rounded-lg border border-gray-200">
				<div class="px-6 py-4 border-b border-gray-200">
					<h3 class="text-lg font-medium text-gray-900 flex items-center">
						<i data-lucide="user" class="w-5 h-5 mr-2 text-motor-red-600"></i>
						Información Personal
					</h3>
				</div>
				<div class="px-6 py-6 space-y-6">
					<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
						<!-- Nombre -->
						<div>
							<label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
								Nombre *
							</label>
							<div class="relative">
								<input type="text" 
									   id="nombre" 
									   name="nombre" 
									   required
									   autocomplete="given-name"
									   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-motor-red-500 focus:ring-motor-red-500 transition-colors duration-200"
									   placeholder="Ingrese el nombre">
								<div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
									<i data-lucide="check" class="w-5 h-5 text-green-500 hidden" id="nombre-check"></i>
									<i data-lucide="x" class="w-5 h-5 text-red-500 hidden" id="nombre-error"></i>
								</div>
							</div>
							<p class="mt-1 text-sm text-red-600 hidden" id="nombre-mensaje"></p>
						</div>

						<!-- Apellido -->
						<div>
							<label for="apellido" class="block text-sm font-medium text-gray-700 mb-2">
								Apellido *
							</label>
							<div class="relative">
								<input type="text" 
									   id="apellido" 
									   name="apellido" 
									   required
									   autocomplete="family-name"
									   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-motor-red-500 focus:ring-motor-red-500 transition-colors duration-200"
									   placeholder="Ingrese el apellido">
								<div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
									<i data-lucide="check" class="w-5 h-5 text-green-500 hidden" id="apellido-check"></i>
									<i data-lucide="x" class="w-5 h-5 text-red-500 hidden" id="apellido-error"></i>
								</div>
							</div>
							<p class="mt-1 text-sm text-red-600 hidden" id="apellido-mensaje"></p>
						</div>
					</div>

					<!-- Cédula -->
					<div>
						<label for="cedula" class="block text-sm font-medium text-gray-700 mb-2">
							Cédula de Identidad *
						</label>
						<div class="relative">
							<input type="text" 
								   id="cedula" 
								   name="cedula" 
								   required
								   pattern="[0-9]{1,3}\.?[0-9]{3}\.?[0-9]{3}-?[0-9]"
								   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-motor-red-500 focus:ring-motor-red-500 transition-colors duration-200 font-mono"
								   placeholder="1.234.567-8">
							<div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
								<i data-lucide="check" class="w-5 h-5 text-green-500 hidden" id="cedula-check"></i>
								<i data-lucide="x" class="w-5 h-5 text-red-500 hidden" id="cedula-error"></i>
							</div>
						</div>
						<p class="mt-1 text-sm text-gray-500">Formato: 1.234.567-8</p>
						<p class="mt-1 text-sm text-red-600 hidden" id="cedula-mensaje"></p>
					</div>
				</div>
			</div>

			<!-- Información de Contacto -->
			<div class="bg-white shadow-sm rounded-lg border border-gray-200">
				<div class="px-6 py-4 border-b border-gray-200">
					<h3 class="text-lg font-medium text-gray-900 flex items-center">
						<i data-lucide="phone" class="w-5 h-5 mr-2 text-motor-red-600"></i>
						Información de Contacto
					</h3>
				</div>
				<div class="px-6 py-6 space-y-6">
					<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
						<!-- Teléfono -->
						<div>
							<label for="telefono" class="block text-sm font-medium text-gray-700 mb-2">
								Número de Teléfono
							</label>
							<div class="relative">
								<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
									<span class="text-gray-500 text-sm">+595</span>
								</div>
								<input type="tel" 
									   id="telefono" 
									   name="telefono"
									   autocomplete="tel"
									   class="block w-full pl-12 rounded-lg border-gray-300 shadow-sm focus:border-motor-red-500 focus:ring-motor-red-500 transition-colors duration-200"
									   placeholder="981 123 456">
								<div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
									<i data-lucide="check" class="w-5 h-5 text-green-500 hidden" id="telefono-check"></i>
									<i data-lucide="x" class="w-5 h-5 text-red-500 hidden" id="telefono-error"></i>
								</div>
							</div>
							<p class="mt-1 text-sm text-gray-500">Formato: 981 123 456</p>
							<p class="mt-1 text-sm text-red-600 hidden" id="telefono-mensaje"></p>
						</div>

						<!-- Email -->
						<div>
							<label for="email" class="block text-sm font-medium text-gray-700 mb-2">
								Correo Electrónico
							</label>
							<div class="relative">
								<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
									<i data-lucide="mail" class="w-5 h-5 text-gray-400"></i>
								</div>
								<input type="email" 
									   id="email" 
									   name="email"
									   autocomplete="email"
									   class="block w-full pl-10 rounded-lg border-gray-300 shadow-sm focus:border-motor-red-500 focus:ring-motor-red-500 transition-colors duration-200"
									   placeholder="cliente@ejemplo.com">
								<div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
									<i data-lucide="check" class="w-5 h-5 text-green-500 hidden" id="email-check"></i>
									<i data-lucide="x" class="w-5 h-5 text-red-500 hidden" id="email-error"></i>
								</div>
							</div>
							<p class="mt-1 text-sm text-red-600 hidden" id="email-mensaje"></p>
						</div>
					</div>

					<!-- Dirección -->
					<div>
						<label for="direccion" class="block text-sm font-medium text-gray-700 mb-2">
							Dirección
						</label>
						<div class="relative">
							<div class="absolute top-3 left-3 flex items-center pointer-events-none">
								<i data-lucide="map-pin" class="w-5 h-5 text-gray-400"></i>
							</div>
							<textarea id="direccion" 
									  name="direccion" 
									  rows="3"
									  autocomplete="street-address"
									  class="block w-full pl-10 rounded-lg border-gray-300 shadow-sm focus:border-motor-red-500 focus:ring-motor-red-500 transition-colors duration-200 resize-none"
									  placeholder="Ingrese la dirección completa del cliente"></textarea>
						</div>
						<p class="mt-1 text-sm text-gray-500">Incluya barrio, ciudad y referencias</p>
					</div>
				</div>
			</div>

			<!-- Botones de acción -->
			<div class="bg-white shadow-sm rounded-lg border border-gray-200 px-6 py-4">
				<div class="flex items-center justify-between">
					<a href="index.php?pagina=tabla/clientes" 
					   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-motor-red-500 transition-colors duration-200">
						<i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
						Cancelar
					</a>
					
					<div class="flex items-center space-x-3">
						<!-- Botón limpiar -->
						<button type="button" 
								id="btn-limpiar"
								class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200">
							<i data-lucide="refresh-cw" class="w-4 h-4 mr-2"></i>
							Limpiar
						</button>
						
						<!-- Botón guardar -->
						<button type="submit" 
								id="btn-guardar"
								class="inline-flex items-center px-6 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-motor-red-600 hover:bg-motor-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-motor-red-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
							<i data-lucide="save" class="w-4 h-4 mr-2"></i>
							<span id="btn-texto">Guardar Cliente</span>
							<div class="ml-2 hidden" id="btn-spinner">
								<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
							</div>
						</button>
					</div>
				</div>
			</div>

			<!-- Procesamiento PHP -->
			<?php
			$registro = ClienteControlador::guardarCliente();
			if ($registro == "ok") {
				echo '<script>
                        if (window.history.replaceState) {
                            window.history.replaceState(null, null, window.location.href);
                        }
                    </script>';
				echo '<div id="mensaje-exito" class="fixed top-4 right-4 z-50 bg-green-50 border border-green-200 rounded-lg p-4 max-w-md">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i data-lucide="check-circle" class="w-5 h-5 text-green-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800">
                                    ¡Cliente registrado correctamente!
                                </p>
                                <p class="text-sm text-green-700 mt-1">
                                    Redirigiendo a la lista de clientes...
                                </p>
                            </div>
                        </div>
                    </div>
                    <script>
                        lucide.createIcons();
                        setTimeout(function(){
                            window.location = "index.php?pagina=tabla/clientes";
                        }, 2000);
                    </script>';
			}
			?>
		</form>
	</div>

	<!-- Panel lateral -->
	<div class="space-y-6">
		<!-- Información de ayuda -->
		<div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
			<div class="flex items-center mb-4">
				<div class="p-2 bg-blue-100 rounded-lg">
					<i data-lucide="info" class="w-5 h-5 text-blue-600"></i>
				</div>
				<h3 class="ml-3 text-lg font-medium text-blue-900">Información Importante</h3>
			</div>
			<div class="space-y-3 text-sm text-blue-800">
				<div class="flex items-start">
					<i data-lucide="check" class="w-4 h-4 mr-2 mt-0.5 text-blue-600 flex-shrink-0"></i>
					<p>Los campos marcados con (*) son obligatorios</p>
				</div>
				<div class="flex items-start">
					<i data-lucide="check" class="w-4 h-4 mr-2 mt-0.5 text-blue-600 flex-shrink-0"></i>
					<p>La cédula debe ser única en el sistema</p>
				</div>
				<div class="flex items-start">
					<i data-lucide="check" class="w-4 h-4 mr-2 mt-0.5 text-blue-600 flex-shrink-0"></i>
					<p>El teléfono es importante para contactar al cliente</p>
				</div>
				<div class="flex items-start">
					<i data-lucide="check" class="w-4 h-4 mr-2 mt-0.5 text-blue-600 flex-shrink-0"></i>
					<p>La dirección ayuda para el servicio a domicilio</p>
				</div>
			</div>
		</div>

		<!-- Acciones rápidas -->
		<div class="bg-white border border-gray-200 rounded-lg p-6">
			<h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
				<i data-lucide="zap" class="w-5 h-5 mr-2 text-motor-red-600"></i>
				Después del Registro
			</h3>
			<div class="space-y-3">
				<div class="p-3 bg-gray-50 rounded-lg">
					<div class="flex items-center">
						<i data-lucide="car" class="w-4 h-4 mr-3 text-gray-600"></i>
						<span class="text-sm text-gray-700">Registrar vehículo del cliente</span>
					</div>
				</div>
				<div class="p-3 bg-gray-50 rounded-lg">
					<div class="flex items-center">
						<i data-lucide="calendar-plus" class="w-4 h-4 mr-3 text-gray-600"></i>
						<span class="text-sm text-gray-700">Agendar primera cita</span>
					</div>
				</div>
				<div class="p-3 bg-gray-50 rounded-lg">
					<div class="flex items-center">
						<i data-lucide="user-cog" class="w-4 h-4 mr-3 text-gray-600"></i>
						<span class="text-sm text-gray-700">Crear usuario de acceso</span>
					</div>
				</div>
			</div>
		</div>

		<!-- Estadísticas -->
		<div class="bg-white border border-gray-200 rounded-lg p-6">
			<h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
				<i data-lucide="bar-chart-3" class="w-5 h-5 mr-2 text-motor-red-600"></i>
				Estadísticas
			</h3>
			<div class="space-y-4">
				<div class="flex justify-between items-center">
					<span class="text-sm text-gray-600">Clientes este mes</span>
					<span class="text-lg font-bold text-motor-red-600">12</span>
				</div>
				<div class="flex justify-between items-center">
					<span class="text-sm text-gray-600">Total de clientes</span>
					<span class="text-lg font-bold text-blue-600">156</span>
				</div>
				<div class="flex justify-between items-center">
					<span class="text-sm text-gray-600">Clientes activos</span>
					<span class="text-lg font-bold text-green-600">142</span>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	// Inicializar iconos
	lucide.createIcons();
	
	// Elementos del formulario
	const form = document.getElementById('formCliente');
	const btnGuardar = document.getElementById('btn-guardar');
	const btnTexto = document.getElementById('btn-texto');
	const btnSpinner = document.getElementById('btn-spinner');
	const btnLimpiar = document.getElementById('btn-limpiar');
	
	// Validaciones en tiempo real
	const validaciones = {
		nombre: {
			campo: document.getElementById('nombre'),
			check: document.getElementById('nombre-check'),
			error: document.getElementById('nombre-error'),
			mensaje: document.getElementById('nombre-mensaje'),
			validar: function(valor) {
				if (valor.length < 2) {
					return { valido: false, mensaje: 'El nombre debe tener al menos 2 caracteres' };
				}
				if (!/^[a-zA-ZÀ-ÿ\s]+$/.test(valor)) {
					return { valido: false, mensaje: 'El nombre solo puede contener letras' };
				}
				return { valido: true, mensaje: '' };
			}
		},
		apellido: {
			campo: document.getElementById('apellido'),
			check: document.getElementById('apellido-check'),
			error: document.getElementById('apellido-error'),
			mensaje: document.getElementById('apellido-mensaje'),
			validar: function(valor) {
				if (valor.length < 2) {
					return { valido: false, mensaje: 'El apellido debe tener al menos 2 caracteres' };
				}
				if (!/^[a-zA-ZÀ-ÿ\s]+$/.test(valor)) {
					return { valido: false, mensaje: 'El apellido solo puede contener letras' };
				}
				return { valido: true, mensaje: '' };
			}
		},
		cedula: {
			campo: document.getElementById('cedula'),
			check: document.getElementById('cedula-check'),
			error: document.getElementById('cedula-error'),
			mensaje: document.getElementById('cedula-mensaje'),
			validar: function(valor) {
				const cedulaLimpia = valor.replace(/[.-]/g, '');
				if (cedulaLimpia.length < 6 || cedulaLimpia.length > 8) {
					return { valido: false, mensaje: 'La cédula debe tener entre 6 y 8 dígitos' };
				}
				if (!/^\d+$/.test(cedulaLimpia)) {
					return { valido: false, mensaje: 'La cédula solo puede contener números' };
				}
				return { valido: true, mensaje: '' };
			}
		},
		telefono: {
			campo: document.getElementById('telefono'),
			check: document.getElementById('telefono-check'),
			error: document.getElementById('telefono-error'),
			mensaje: document.getElementById('telefono-mensaje'),
			validar: function(valor) {
				if (valor === '') return { valido: true, mensaje: '' }; // Campo opcional
				const telefonoLimpio = valor.replace(/[\s-]/g, '');
				if (!/^\d{9,10}$/.test(telefonoLimpio)) {
					return { valido: false, mensaje: 'El teléfono debe tener 9 o 10 dígitos' };
				}
				return { valido: true, mensaje: '' };
			}
		},
		email: {
			campo: document.getElementById('email'),
			check: document.getElementById('email-check'),
			error: document.getElementById('email-error'),
			mensaje: document.getElementById('email-mensaje'),
			validar: function(valor) {
				if (valor === '') return { valido: true, mensaje: '' }; // Campo opcional
				const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
				if (!emailRegex.test(valor)) {
					return { valido: false, mensaje: 'Ingrese un email válido' };
				}
				return { valido: true, mensaje: '' };
			}
		}
	};
	
	// Función para validar un campo
	function validarCampo(nombre, valor) {
		const validacion = validaciones[nombre];
		if (!validacion) return true;
		
		const resultado = validacion.validar(valor);
		
		// Mostrar/ocultar iconos y mensajes
		if (resultado.valido) {
			validacion.check.classList.remove('hidden');
			validacion.error.classList.add('hidden');
			validacion.mensaje.classList.add('hidden');
			validacion.campo.classList.remove('border-red-300', 'focus:border-red-500', 'focus:ring-red-500');
			validacion.campo.classList.add('border-green-300', 'focus:border-green-500', 'focus:ring-green-500');
		} else {
			validacion.check.classList.add('hidden');
			validacion.error.classList.remove('hidden');
			validacion.mensaje.classList.remove('hidden');
			validacion.mensaje.textContent = resultado.mensaje;
			validacion.campo.classList.remove('border-green-300', 'focus:border-green-500', 'focus:ring-green-500');
			validacion.campo.classList.add('border-red-300', 'focus:border-red-500', 'focus:ring-red-500');
		}
		
		return resultado.valido;
	}
	
	// Agregar validaciones en tiempo real
	Object.keys(validaciones).forEach(nombre => {
		const campo = validaciones[nombre].campo;
		if (campo) {
			campo.addEventListener('input', function() {
				validarCampo(nombre, this.value.trim());
			});
			
			campo.addEventListener('blur', function() {
				validarCampo(nombre, this.value.trim());
			});
		}
	});
	
	// Formateo automático de cédula
	document.getElementById('cedula').addEventListener('input', function() {
		let valor = this.value.replace(/[^\d]/g, '');
		if (valor.length >= 7) {
			valor = valor.substring(0, valor.length - 1).replace(/(\d{1,3})(\d{3})(\d{3})/, '$1.$2.$3') + '-' + valor.substring(valor.length - 1);
		} else if (valor.length >= 6) {
			valor = valor.replace(/(\d{1,3})(\d{3})/, '$1.$2');
		}
		this.value = valor;
	});
	
	// Formateo automático de teléfono
	document.getElementById('telefono').addEventListener('input', function() {
		let valor = this.value.replace(/[^\d]/g, '');
		if (valor.length >= 6) {
			valor = valor.replace(/(\d{3})(\d{3})(\d{3,4})/, '$1 $2 $3');
		} else if (valor.length >= 3) {
			valor = valor.replace(/(\d{3})/, '$1 ');
		}
		this.value = valor;
	});
	
	// Función para validar todo el formulario
	function validarFormulario() {
		let formularioValido = true;
		
		// Validar campos obligatorios
		['nombre', 'apellido', 'cedula'].forEach(nombre => {
			const campo = validaciones[nombre].campo;
			if (!validarCampo(nombre, campo.value.trim()) || campo.value.trim() === '') {
				formularioValido = false;
			}
		});
		
		// Validar campos opcionales si tienen valor
		['telefono', 'email'].forEach(nombre => {
			const campo = validaciones[nombre].campo;
			if (campo.value.trim() !== '') {
				if (!validarCampo(nombre, campo.value.trim())) {
					formularioValido = false;
				}
			}
		});
		
		return formularioValido;
	}
	
	// Envío del formulario
	form.addEventListener('submit', function(e) {
		e.preventDefault();
		
		if (!validarFormulario()) {
			Swal.fire({
				icon: 'error',
				title: 'Errores en el formulario',
				text: 'Por favor, corrija los errores antes de continuar.',
				confirmButtonColor: '#dc2626'
			});
			return;
		}
		
		// Mostrar loading
		btnGuardar.disabled = true;
		btnTexto.textContent = 'Guardando...';
		btnSpinner.classList.remove('hidden');
		
		// Enviar formulario
		this.submit();
	});
	
	// Limpiar formulario
	btnLimpiar.addEventListener('click', function() {
		Swal.fire({
			title: '¿Limpiar formulario?',
			text: 'Se perderán todos los datos ingresados',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#dc2626',
			cancelButtonColor: '#6b7280',
			confirmButtonText: 'Sí, limpiar',
			cancelButtonText: 'Cancelar'
		}).then((result) => {
			if (result.isConfirmed) {
				form.reset();
				
				// Limpiar validaciones visuales
				Object.keys(validaciones).forEach(nombre => {
					const validacion = validaciones[nombre];
					validacion.check.classList.add('hidden');
					validacion.error.classList.add('hidden');
					validacion.mensaje.classList.add('hidden');
					validacion.campo.classList.remove('border-red-300', 'focus:border-red-500', 'focus:ring-red-500', 'border-green-300', 'focus:border-green-500', 'focus:ring-green-500');
				});
				
				// Focus en el primer campo
				document.getElementById('nombre').focus();
			}
		});
	});
	
	// Focus automático en el primer campo
	document.getElementById('nombre').focus();
	
	// Auto-hide del mensaje de éxito
	const mensajeExito = document.getElementById('mensaje-exito');
	if (mensajeExito) {
		setTimeout(() => {
			mensajeExito.style.opacity = '0';
			setTimeout(() => mensajeExito.remove(), 300);
		}, 5000);
	}
});
</script>