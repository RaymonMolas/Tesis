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

$clientes = ClienteControlador::buscarCliente();
?>

<div class="container-fluid mt-4">
	<div class="row">
		<div class="col-12">
			<div class="card shadow">
				<div class="card-header bg-primary text-white">
					<h4 class="mb-0">
						<i class="bi bi-car-front-fill"></i> Nuevo Vehículo - Motor Service
					</h4>
					<small>Registre un nuevo vehículo con reconocimiento automático de matrícula</small>
				</div>
				<div class="card-body">
					<form method="post" id="formVehiculo">

						<!-- Información de la Matrícula con IA -->
						<div class="card mb-4">
							<div class="card-header bg-info text-white">
								<h5><i class="bi bi-camera-fill"></i> Matrícula del Vehículo</h5>
								<small>Use la cámara para reconocimiento automático o ingrese manualmente</small>
							</div>
							<div class="card-body">
								<div class="row">
									<div class="col-md-8">
										<div class="mb-3">
											<label for="matricula" class="form-label">Matrícula</label>
											<div class="input-group">
												<input type="text" class="form-control form-control-lg" id="matricula"
													name="matricula" placeholder="Ej: ABC123" required
													style="text-transform: uppercase;">
												<button type="button" class="btn btn-outline-primary"
													id="btnReconocerMatricula">
													<i class="bi bi-camera"></i> Reconocer con IA
												</button>
											</div>
											<div class="form-text">
												<i class="bi bi-info-circle"></i>
												La matrícula será convertida automáticamente a mayúsculas
											</div>
										</div>
									</div>
									<div class="col-md-4">
										<div class="text-center">
											<div class="badge bg-success p-3 mb-2">
												<i class="bi bi-robot" style="font-size: 2rem;"></i>
											</div>
											<h6>Reconocimiento IA</h6>
											<small class="text-muted">Tecnología avanzada para detectar matrículas
												automáticamente</small>
										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- Información del Vehículo -->
						<div class="card mb-4">
							<div class="card-header">
								<h5><i class="bi bi-info-circle"></i> Información del Vehículo</h5>
							</div>
							<div class="card-body">
								<div class="row">
									<div class="col-md-6">
										<div class="mb-3">
											<label for="marca" class="form-label">Marca</label>
											<input type="text" class="form-control" id="marca" name="marca"
												placeholder="Ej: Toyota, Honda, Nissan" required>
										</div>
									</div>
									<div class="col-md-6">
										<div class="mb-3">
											<label for="modelo" class="form-label">Modelo</label>
											<input type="text" class="form-control" id="modelo" name="modelo"
												placeholder="Ej: Corolla, Civic, Sentra" required>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6">
										<div class="mb-3">
											<label for="anho" class="form-label">Año</label>
											<input type="number" class="form-control" id="anho" name="anho" min="1900"
												max="<?php echo date('Y'); ?>" value="<?php echo date('Y'); ?>"
												required>
										</div>
									</div>
									<div class="col-md-6">
										<div class="mb-3">
											<label for="color" class="form-label">Color</label>
											<input type="text" class="form-control" id="color" name="color"
												placeholder="Ej: Blanco, Negro, Rojo" required>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<div class="mb-3">
											<label for="id_cliente" class="form-label">Propietario</label>
											<select class="form-select" id="id_cliente" name="id_cliente" required>
												<option value="">Seleccione el propietario del vehículo</option>
												<?php foreach ($clientes as $cliente): ?>
													<option value="<?php echo $cliente['id_cliente']; ?>">
														<?php echo $cliente['nombre'] . ' ' . $cliente['apellido']; ?>
														<?php if (!empty($cliente['ruc'])): ?>
															- RUC: <?php echo $cliente['ruc']; ?>
														<?php else: ?>
															- CI: <?php echo $cliente['cedula']; ?>
														<?php endif; ?>
													</option>
												<?php endforeach; ?>
											</select>
										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- Botones de Acción -->
						<div class="row">
							<div class="col-12">
								<div class="d-flex justify-content-between">
									<a href="index.php?pagina=tabla/vehiculos" class="btn btn-secondary">
										<i class="bi bi-arrow-left"></i> Volver
									</a>
									<button type="submit" class="btn btn-primary btn-lg">
										<i class="bi bi-save"></i> Registrar Vehículo
									</button>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Modal para Reconocimiento de Matrícula -->
<div class="modal fade" id="matriculaModal" tabindex="-1" aria-labelledby="matriculaModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h5 class="modal-title" id="matriculaModalLabel">
					<i class="bi bi-camera-fill"></i> Reconocimiento Automático de Matrícula
				</h5>
				<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
					aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-lg-8">
						<div class="text-center">
							<video id="cameraVideo" autoplay muted playsinline class="img-fluid border rounded"
								style="max-width: 100%; max-height: 400px; background: #f8f9fa;">
							</video>
							<canvas id="captureCanvas" style="display: none;"></canvas>

							<!-- Overlay para ayuda visual -->
							<div class="mt-3">
								<div class="alert alert-info">
									<i class="bi bi-bullseye"></i>
									<strong>Alinee la matrícula dentro del recuadro de la cámara</strong>
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-4">
						<div class="card bg-light">
							<div class="card-header">
								<h6><i class="bi bi-lightbulb"></i> Instrucciones</h6>
							</div>
							<div class="card-body">
								<ul class="list-unstyled">
									<li class="mb-2"><i class="bi bi-check-circle text-success"></i> Asegúrese de que
										haya buena iluminación</li>
									<li class="mb-2"><i class="bi bi-check-circle text-success"></i> La matrícula debe
										estar completamente visible</li>
									<li class="mb-2"><i class="bi bi-check-circle text-success"></i> Evite reflejos y
										sombras</li>
									<li class="mb-2"><i class="bi bi-check-circle text-success"></i> Mantenga la cámara
										estable</li>
									<li class="mb-2"><i class="bi bi-check-circle text-success"></i> Presione "Capturar"
										cuando esté listo</li>
								</ul>

								<hr>

								<div class="text-center">
									<div class="spinner-border text-primary d-none" id="processingSpinner"
										role="status">
										<span class="visually-hidden">Procesando...</span>
									</div>
									<div id="resultadoIA" class="mt-3"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
					<i class="bi bi-x-circle"></i> Cancelar
				</button>
				<button type="button" class="btn btn-warning" id="btnReiniciarCamara">
					<i class="bi bi-arrow-clockwise"></i> Reiniciar Cámara
				</button>
				<button type="button" class="btn btn-primary" id="btnCapturarMatricula">
					<i class="bi bi-camera"></i> Capturar y Analizar
				</button>
			</div>
		</div>
	</div>
</div>

<!-- JavaScript para Reconocimiento de Matrícula -->
<script>
	class MatriculaRecognition {
		constructor() {
			this.video = null;
			this.canvas = null;
			this.context = null;
			this.stream = null;
			this.isProcessing = false;
			this.modal = null;
		}

		async initCamera() {
			try {
				// Solicitar permisos de cámara
				this.stream = await navigator.mediaDevices.getUserMedia({
					video: {
						width: { ideal: 1280 },
						height: { ideal: 720 },
						facingMode: 'environment' // Cámara trasera en móviles
					}
				});

				this.video = document.getElementById('cameraVideo');
				this.video.srcObject = this.stream;

				// Esperar a que el video esté listo
				return new Promise((resolve) => {
					this.video.onloadedmetadata = () => {
						resolve(true);
					};
				});

			} catch (error) {
				console.error('Error accessing camera:', error);
				this.showError('No se pudo acceder a la cámara. Verifique los permisos del navegador.');
				return false;
			}
		}

		async captureAndProcess() {
			if (this.isProcessing) return;

			this.isProcessing = true;
			this.showProcessing(true);

			try {
				// Capturar imagen del video
				const canvas = document.getElementById('captureCanvas');
				const context = canvas.getContext('2d');

				canvas.width = this.video.videoWidth;
				canvas.height = this.video.videoHeight;
				context.drawImage(this.video, 0, 0);

				// Convertir a blob para envío
				const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', 0.8));

				// Enviar a API de reconocimiento
				const result = await this.processWithAPI(blob);

				if (result.success && result.matricula) {
					document.getElementById('matricula').value = result.matricula.toUpperCase();
					this.showSuccess('¡Matrícula detectada exitosamente!', result.matricula);

					// Cerrar modal después de 2 segundos
					setTimeout(() => {
						this.modal.hide();
					}, 2000);

				} else {
					this.showError('No se pudo detectar la matrícula. Intente nuevamente con mejor iluminación.');
				}

			} catch (error) {
				console.error('Error processing image:', error);
				this.showError('Error procesando la imagen. Verifique su conexión e intente nuevamente.');
			} finally {
				this.isProcessing = false;
				this.showProcessing(false);
			}
		}

		async processWithAPI(imageBlob) {
			const formData = new FormData();
			formData.append('image', imageBlob);

			try {
				const response = await fetch('api/reconocer_matricula.php', {
					method: 'POST',
					body: formData
				});

				if (!response.ok) {
					throw new Error('Error en la respuesta del servidor');
				}

				return await response.json();
			} catch (error) {
				console.error('API Error:', error);
				return {
					success: false,
					error: 'Error de conexión con el servidor de reconocimiento'
				};
			}
		}

		showProcessing(show) {
			const spinner = document.getElementById('processingSpinner');
			const button = document.getElementById('btnCapturarMatricula');

			if (show) {
				spinner.classList.remove('d-none');
				button.disabled = true;
				button.innerHTML = '<i class="bi bi-hourglass-split"></i> Analizando...';
				this.showResult('<div class="alert alert-info"><i class="bi bi-cpu"></i> Procesando imagen con IA...</div>');
			} else {
				spinner.classList.add('d-none');
				button.disabled = false;
				button.innerHTML = '<i class="bi bi-camera"></i> Capturar y Analizar';
			}
		}

		showSuccess(message, matricula) {
			this.showResult(`
			<div class="alert alert-success">
				<i class="bi bi-check-circle-fill"></i> ${message}
				<hr>
				<h4 class="alert-heading">Matrícula: ${matricula}</h4>
			</div>
		`);

			// SweetAlert para confirmación
			Swal.fire({
				icon: 'success',
				title: '¡Éxito!',
				text: `Matrícula detectada: ${matricula}`,
				timer: 3000,
				showConfirmButton: false
			});
		}

		showError(message) {
			this.showResult(`
			<div class="alert alert-danger">
				<i class="bi bi-exclamation-triangle-fill"></i> ${message}
			</div>
		`);

			Swal.fire({
				icon: 'error',
				title: 'Error de Reconocimiento',
				text: message,
				confirmButtonText: 'Intentar de nuevo'
			});
		}

		showResult(html) {
			document.getElementById('resultadoIA').innerHTML = html;
		}

		stopCamera() {
			if (this.stream) {
				this.stream.getTracks().forEach(track => track.stop());
				this.stream = null;
			}
			if (this.video) {
				this.video.srcObject = null;
			}
		}

		restartCamera() {
			this.stopCamera();
			this.showResult('');
			setTimeout(() => {
				this.initCamera();
			}, 500);
		}
	}

	// Instancia global
	const matriculaRecognition = new MatriculaRecognition();

	document.addEventListener('DOMContentLoaded', function () {
		// Configurar modal
		matriculaRecognition.modal = new bootstrap.Modal(document.getElementById('matriculaModal'));

		// Abrir reconocimiento de matrícula
		document.getElementById('btnReconocerMatricula').addEventListener('click', async function () {
			matriculaRecognition.modal.show();
		});

		// Inicializar cámara cuando se abre el modal
		document.getElementById('matriculaModal').addEventListener('shown.bs.modal', async function () {
			const success = await matriculaRecognition.initCamera();
			if (!success) {
				// Si no se puede inicializar la cámara, cerrar modal
				setTimeout(() => {
					matriculaRecognition.modal.hide();
				}, 3000);
			}
		});

		// Detener cámara cuando se cierra el modal
		document.getElementById('matriculaModal').addEventListener('hidden.bs.modal', function () {
			matriculaRecognition.stopCamera();
		});

		// Capturar matrícula
		document.getElementById('btnCapturarMatricula').addEventListener('click', function () {
			matriculaRecognition.captureAndProcess();
		});

		// Reiniciar cámara
		document.getElementById('btnReiniciarCamara').addEventListener('click', function () {
			matriculaRecognition.restartCamera();
		});

		// Convertir matrícula a mayúsculas automáticamente
		document.getElementById('matricula').addEventListener('input', function () {
			this.value = this.value.toUpperCase();
		});

		// Validar formulario
		document.getElementById('formVehiculo').addEventListener('submit', function (e) {
			const matricula = document.getElementById('matricula').value.trim();

			if (matricula.length < 3) {
				e.preventDefault();
				Swal.fire({
					icon: 'warning',
					title: 'Matrícula inválida',
					text: 'La matrícula debe tener al menos 3 caracteres'
				});
				return;
			}

			// Confirmar registro
			e.preventDefault();
			const marca = document.getElementById('marca').value;
			const modelo = document.getElementById('modelo').value;

			Swal.fire({
				title: '¿Registrar Vehículo?',
				html: `Se registrará el vehículo:<br>
				   <strong>${marca} ${modelo}</strong><br>
				   Matrícula: <strong>${matricula}</strong>`,
				icon: 'question',
				showCancelButton: true,
				confirmButtonColor: '#007bff',
				cancelButtonColor: '#6c757d',
				confirmButtonText: 'Sí, registrar',
				cancelButtonText: 'Cancelar'
			}).then((result) => {
				if (result.isConfirmed) {
					this.submit();
				}
			});
		});
	});

	// Verificar soporte de cámara al cargar la página
	window.addEventListener('load', function () {
		if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
			document.getElementById('btnReconocerMatricula').disabled = true;
			document.getElementById('btnReconocerMatricula').innerHTML = '<i class="bi bi-camera-video-off"></i> Cámara no disponible';
			document.getElementById('btnReconocerMatricula').title = 'Su navegador no soporta acceso a cámara';
		}
	});
</script>

<?php
$registro = VehiculoControlador::ctrRegistrarVehiculo();
if ($registro == "ok") {
	echo '<script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>';
}
?>

<style>
	.form-control-lg {
		font-size: 1.25rem;
		font-weight: 500;
		letter-spacing: 0.1em;
	}

	#matricula {
		background: linear-gradient(45deg, #fff, #f8f9fa);
		border: 2px solid #007bff;
	}

	#matricula:focus {
		border-color: #0056b3;
		box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
	}

	.badge {
		font-size: 1rem;
	}

	.modal-xl {
		max-width: 1140px;
	}

	#cameraVideo {
		border: 3px solid #007bff;
		border-radius: 10px;
	}

	.alert {
		border-radius: 10px;
	}

	.card {
		border-radius: 15px;
		border: none;
		box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
	}

	.card-header {
		border-radius: 15px 15px 0 0 !important;
		border-bottom: 2px solid rgba(255, 255, 255, 0.2);
	}

	.btn {
		border-radius: 8px;
	}

	.input-group .btn {
		border-left: none;
	}

	.form-select:focus,
	.form-control:focus {
		border-color: #007bff;
		box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
	}

	@media (max-width: 768px) {
		.modal-xl {
			max-width: 95%;
		}

		#cameraVideo {
			max-height: 250px;
		}

		.card-body {
			padding: 1rem;
		}
	}

	/* Animación para el botón de IA */
	@keyframes pulse {
		0% {
			transform: scale(1);
		}

		50% {
			transform: scale(1.05);
		}

		100% {
			transform: scale(1);
		}
	}

	.badge:hover {
		animation: pulse 1s infinite;
	}
</style>