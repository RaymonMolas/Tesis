<?php
if (!isset($_SESSION["validarIngreso"])) {
    echo '<script>window.location = "index.php?pagina=login";</script>';
    return;
} elseif ($_SESSION["validarIngreso"] != "ok") {
    echo '<script>window.location = "index.php?pagina=login";</script>';
    return;
}

// Obtener datos según el tipo de usuario
if ($_SESSION["tipo_usuario"] == "personal") {
    // Personal puede ver todas las citas
    $citas = ControladorAgendamiento::listarSolicitudesPendientes();
    $todasLasCitas = ControladorAgendamiento::listarTodasLasCitas();
} else {
    // Cliente solo ve sus citas
    $id_cliente = $_SESSION["id_cliente"];
    $misCitas = ControladorAgendamiento::obtenerCitasCliente($id_cliente);
    $misVehiculos = VehiculoControlador::ctrListarVehiculosCliente($id_cliente);
}

// Procesar nueva cita si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agendar_cita'])) {
    $resultado = ControladorAgendamiento::ctrRegistrarCita();
    
    if ($resultado == "ok") {
        echo '<script>
            Swal.fire({
                title: "¡Éxito!",
                text: "Cita agendada correctamente",
                icon: "success",
                confirmButtonText: "Aceptar"
            }).then(() => {
                window.location.reload();
            });
        </script>';
    } else {
        echo '<script>
            Swal.fire({
                title: "Error",
                text: "Error al agendar la cita: ' . $resultado . '",
                icon: "error",
                confirmButtonText: "Aceptar"
            });
        </script>';
    }
}

// Procesar acciones del personal (confirmar, completar, cancelar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_cita'])) {
    $resultado = ControladorAgendamiento::ctrActualizarEstadoCita();
    
    if ($resultado == "ok") {
        echo '<script>
            Swal.fire({
                title: "¡Éxito!",
                text: "Cita actualizada correctamente",
                icon: "success",
                confirmButtonText: "Aceptar"
            }).then(() => {
                window.location.reload();
            });
        </script>';
    }
}
?>

<title>AGENDAMIENTO - Sistema de Citas</title>

<style>
    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
    }

    .page-header {
        background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
        border-radius: 15px;
        text-align: center;
    }

    .page-title {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }

    .page-subtitle {
        opacity: 0.9;
        font-size: 1.1rem;
    }

    .main-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .form-container, .citas-container {
        background: white;
        border-radius: 15px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .container-header {
        background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
        color: white;
        padding: 1.5rem;
        text-align: center;
    }

    .container-content {
        padding: 2rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-control {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        transition: all 0.3s ease;
        width: 100%;
    }

    .form-control:focus {
        border-color: #6f42c1;
        box-shadow: 0 0 0 3px rgba(111, 66, 193, 0.1);
        outline: none;
    }

    .form-control.is-valid {
        border-color: #28a745;
    }

    .form-control.is-invalid {
        border-color: #dc3545;
    }

    .btn-primary {
        background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
        border: none;
        border-radius: 10px;
        padding: 0.75rem 2rem;
        font-weight: 600;
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(111, 66, 193, 0.3);
        width: 100%;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(111, 66, 193, 0.4);
        color: white;
    }

    .cita-card {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        border-left: 4px solid #6f42c1;
        transition: all 0.3s ease;
    }

    .cita-card:hover {
        transform: translateX(5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .cita-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .cita-cliente {
        font-weight: bold;
        color: #2c3e50;
        font-size: 1.1rem;
    }

    .cita-fecha {
        background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .cita-motivo {
        color: #6c757d;
        margin-bottom: 1rem;
        font-style: italic;
    }

    .cita-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .btn-action {
        padding: 0.4rem 0.8rem;
        border: none;
        border-radius: 5px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-confirmar {
        background: #28a745;
        color: white;
    }

    .btn-completar {
        background: #17a2b8;
        color: white;
    }

    .btn-cancelar {
        background: #dc3545;
        color: white;
    }

    .btn-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
    }

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-pendiente {
        background: #fff3cd;
        color: #856404;
    }

    .status-confirmada {
        background: #d1ecf1;
        color: #0c5460;
    }

    .status-completada {
        background: #d4edda;
        color: #155724;
    }

    .status-cancelada {
        background: #f8d7da;
        color: #721c24;
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #7f8c8d;
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .calendar-container {
        background: white;
        border-radius: 15px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        margin-top: 2rem;
        overflow: hidden;
    }

    .calendar-header {
        background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
        color: white;
        padding: 1rem;
        text-align: center;
        font-weight: bold;
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 1px;
        background: #e9ecef;
    }

    .calendar-day {
        background: white;
        padding: 1rem;
        text-align: center;
        min-height: 80px;
        position: relative;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .calendar-day:hover {
        background: #f8f9fa;
    }

    .calendar-day.has-cita {
        background: linear-gradient(135deg, rgba(111, 66, 193, 0.1) 0%, rgba(232, 62, 140, 0.1) 100%);
    }

    .calendar-day.has-cita::after {
        content: '';
        position: absolute;  
        bottom: 5px;
        right: 5px;
        width: 8px;
        height: 8px;
        background: #6f42c1;
        border-radius: 50%;
    }

    .required {
        color: #dc3545;
        font-weight: bold;
    }

    .form-hint {
        font-size: 0.875rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }

    .invalid-feedback {
        display: block;
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    @media (max-width: 1024px) {
        .main-container {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .calendar-grid {
            grid-template-columns: repeat(7, 1fr);
            font-size: 0.85rem;
        }
        
        .calendar-day {
            min-height: 60px;
            padding: 0.5rem;
        }
        
        .cita-header {
            flex-direction: column;
            gap: 0.5rem;
            align-items: flex-start;
        }
    }
</style>

<div class="container-fluid">
    <!-- Encabezado de la página -->
    <div class="page-header">
        <h1 class="page-title">
            <i class="bi bi-calendar-event"></i> 
            <?php echo $_SESSION["tipo_usuario"] == "personal" ? "Gestión de Citas" : "Mis Citas"; ?>
        </h1>
        <p class="page-subtitle">
            <?php echo $_SESSION["tipo_usuario"] == "personal" ? "Administra las citas de los clientes" : "Agenda y consulta tus citas"; ?>
        </p>
    </div>

    <?php if ($_SESSION["tipo_usuario"] == "cliente"): ?>
        <!-- PANEL DE CLIENTE -->
        <div class="main-container">
            <!-- Formulario para agendar nueva cita -->
            <div class="form-container">
                <div class="container-header">
                    <h3><i class="bi bi-calendar-plus"></i> Agendar Nueva Cita</h3>
                </div>
                <div class="container-content">
                    <form method="POST" id="formAgendarCita">
                        <input type="hidden" name="agendar_cita" value="1">
                        <input type="hidden" name="id_cliente" value="<?php echo $_SESSION['id_cliente']; ?>">

                        <div class="form-group">
                            <label class="form-label">
                                <i class="bi bi-calendar3"></i> Fecha de la Cita <span class="required">*</span>
                            </label>
                            <input type="date" class="form-control" name="fecha_cita" id="fecha_cita" 
                                   min="<?php echo date('Y-m-d'); ?>" required>
                            <div class="form-hint">Seleccione una fecha futura</div>
                            <div class="invalid-feedback" id="error-fecha_cita"></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="bi bi-clock"></i> Hora de la Cita <span class="required">*</span>
                            </label>
                            <select class="form-control" name="hora_cita" id="hora_cita" required>
                                <option value="">Seleccione una hora</option>
                                <option value="08:00">08:00 AM</option>
                                <option value="08:30">08:30 AM</option>
                                <option value="09:00">09:00 AM</option>
                                <option value="09:30">09:30 AM</option>
                                <option value="10:00">10:00 AM</option>
                                <option value="10:30">10:30 AM</option>
                                <option value="11:00">11:00 AM</option>
                                <option value="11:30">11:30 AM</option>
                                <option value="14:00">02:00 PM</option>
                                <option value="14:30">02:30 PM</option>
                                <option value="15:00">03:00 PM</option>
                                <option value="15:30">03:30 PM</option>
                                <option value="16:00">04:00 PM</option>
                                <option value="16:30">04:30 PM</option>
                                <option value="17:00">05:00 PM</option>
                                <option value="17:30">05:30 PM</option>
                            </select>
                            <div class="form-hint">Horarios disponibles de lunes a viernes</div>
                            <div class="invalid-feedback" id="error-hora_cita"></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="bi bi-chat-left-text"></i> Motivo de la Cita <span class="required">*</span>
                            </label>
                            <textarea class="form-control" name="motivo_cita" id="motivo_cita" 
                                      placeholder="Describe el motivo de tu cita (problema, servicio requerido, etc.)" 
                                      rows="4" maxlength="500" required></textarea>
                            <div class="form-hint">Describe detalladamente el servicio que necesitas</div>
                            <div class="invalid-feedback" id="error-motivo_cita"></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="bi bi-journal-text"></i> Observaciones Adicionales
                            </label>
                            <textarea class="form-control" name="observaciones" id="observaciones" 
                                      placeholder="Información adicional (opcional)" 
                                      rows="3" maxlength="500"></textarea>
                            <div class="form-hint">Información adicional que consideres importante</div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-calendar-check"></i> Agendar Cita
                        </button>
                    </form>
                </div>
            </div>

            <!-- Mis citas -->
            <div class="citas-container">
                <div class="container-header">
                    <h3><i class="bi bi-list-check"></i> Mis Citas</h3>
                </div>
                <div class="container-content">
                    <?php if (!empty($misCitas)): ?>
                        <?php foreach ($misCitas as $cita): ?>
                            <div class="cita-card">
                                <div class="cita-header">
                                    <div class="cita-fecha">
                                        <i class="bi bi-calendar"></i> 
                                        <?php echo date('d/m/Y', strtotime($cita['fecha_cita'])); ?>
                                        <i class="bi bi-clock"></i> 
                                        <?php echo date('H:i', strtotime($cita['hora_cita'])); ?>
                                    </div>
                                    <span class="status-badge status-<?php echo $cita['estado']; ?>">
                                        <?php echo ucfirst($cita['estado']); ?>
                                    </span>
                                </div>
                                <div class="cita-motivo">
                                    <i class="bi bi-chat-left-text"></i> 
                                    <?php echo htmlspecialchars($cita['motivo_cita']); ?>
                                </div>
                                <?php if (!empty($cita['observaciones'])): ?>
                                    <div class="cita-motivo">
                                        <i class="bi bi-info-circle"></i> 
                                        <strong>Observaciones:</strong> <?php echo htmlspecialchars($cita['observaciones']); ?>
                                    </div>
                                <?php endif; ?>
                                <small class="text-muted">
                                    <i class="bi bi-calendar-plus"></i> 
                                    Solicitada el <?php echo date('d/m/Y H:i', strtotime($cita['fecha_solicitud'])); ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-calendar-x"></i>
                            <h4>No tienes citas agendadas</h4>
                            <p>Agenda tu primera cita usando el formulario de la izquierda</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- PANEL DE PERSONAL -->
        <div class="main-container">
            <!-- Citas pendientes -->
            <div class="citas-container">
                <div class="container-header">
                    <h3><i class="bi bi-clock-history"></i> Citas Pendientes de Confirmación</h3>
                </div>
                <div class="container-content">
                    <?php if (!empty($citas)): ?>
                        <?php foreach ($citas as $cita): ?>
                            <div class="cita-card">
                                <div class="cita-header">
                                    <div class="cita-cliente">
                                        <i class="bi bi-person-circle"></i> 
                                        <?php echo htmlspecialchars($cita['nombre_cliente']); ?>
                                    </div>
                                    <div class="cita-fecha">
                                        <i class="bi bi-calendar"></i> 
                                        <?php echo date('d/m/Y', strtotime($cita['fecha_cita'])); ?>
                                        <i class="bi bi-clock"></i> 
                                        <?php echo date('H:i', strtotime($cita['hora_cita'])); ?>
                                    </div>
                                </div>
                                <div class="cita-motivo">
                                    <i class="bi bi-chat-left-text"></i> 
                                    <?php echo htmlspecialchars($cita['motivo_cita']); ?>
                                </div>
                                <?php if (!empty($cita['observaciones'])): ?>
                                    <div class="cita-motivo">
                                        <i class="bi bi-info-circle"></i> 
                                        <strong>Observaciones:</strong> <?php echo htmlspecialchars($cita['observaciones']); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="cita-actions">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="accion_cita" value="confirmar">
                                        <input type="hidden" name="id_agendamiento" value="<?php echo $cita['id_agendamiento']; ?>">
                                        <button type="submit" class="btn-action btn-confirmar">
                                            <i class="bi bi-check-circle"></i> Confirmar
                                        </button>
                                    </form>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="accion_cita" value="cancelar">
                                        <input type="hidden" name="id_agendamiento" value="<?php echo $cita['id_agendamiento']; ?>">
                                        <button type="submit" class="btn-action btn-cancelar" 
                                                onclick="return confirm('¿Está seguro de cancelar esta cita?')">
                                            <i class="bi bi-x-circle"></i> Cancelar
                                        </button>
                                    </form>
                                </div>
                                <small class="text-muted">
                                    <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($cita['telefono_cliente']); ?>
                                    | <i class="bi bi-calendar-plus"></i> 
                                    Solicitada el <?php echo date('d/m/Y H:i', strtotime($cita['fecha_solicitud'])); ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-check-circle"></i>
                            <h4>No hay citas pendientes</h4>
                            <p>Todas las citas han sido procesadas</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Todas las citas -->
            <div class="citas-container">
                <div class="container-header">
                    <h3><i class="bi bi-list-ul"></i> Todas las Citas</h3>
                </div>
                <div class="container-content">
                    <?php if (!empty($todasLasCitas)): ?>
                        <?php foreach (array_slice($todasLasCitas, 0, 10) as $cita): ?>
                            <div class="cita-card">
                                <div class="cita-header">
                                    <div class="cita-cliente">
                                        <i class="bi bi-person-circle"></i> 
                                        <?php echo htmlspecialchars($cita['nombre_cliente']); ?>
                                    </div>
                                    <div class="cita-fecha">
                                        <i class="bi bi-calendar"></i> 
                                        <?php echo date('d/m/Y', strtotime($cita['fecha_cita'])); ?>
                                        <i class="bi bi-clock"></i> 
                                        <?php echo date('H:i', strtotime($cita['hora_cita'])); ?>
                                    </div>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span class="status-badge status-<?php echo $cita['estado']; ?>">
                                        <?php echo ucfirst($cita['estado']); ?>
                                    </span>
                                    <?php if ($cita['estado'] == 'confirmada'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="accion_cita" value="completar">
                                            <input type="hidden" name="id_agendamiento" value="<?php echo $cita['id_agendamiento']; ?>">
                                            <button type="submit" class="btn-action btn-completar">
                                                <i class="bi bi-check2-all"></i> Completar
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="text-center mt-3">
                            <a href="index.php?pagina=tabla/historicocitas" class="btn btn-primary">
                                Ver Historial Completo
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-calendar-x"></i>
                            <h4>No hay citas registradas</h4>
                            <p>Las citas aparecerán aquí cuando los clientes las soliciten</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    // Validación del formulario de cita
    document.getElementById('formAgendarCita')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const fecha = document.getElementById('fecha_cita').value;
        const hora = document.getElementById('hora_cita').value;
        const motivo = document.getElementById('motivo_cita').value.trim();
        
        let isValid = true;
        
        // Validar fecha
        if (!fecha) {
            document.getElementById('error-fecha_cita').textContent = 'La fecha es obligatoria';
            document.getElementById('fecha_cita').classList.add('is-invalid');
            isValid = false;
        } else {
            const fechaSeleccionada = new Date(fecha);
            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);
            
            if (fechaSeleccionada < hoy) {
                document.getElementById('error-fecha_cita').textContent = 'La fecha debe ser futura';
                document.getElementById('fecha_cita').classList.add('is-invalid');
                isValid = false;
            } else {
                document.getElementById('error-fecha_cita').textContent = '';
                document.getElementById('fecha_cita').classList.remove('is-invalid');
                document.getElementById('fecha_cita').classList.add('is-valid');
            }
        }
        
        // Validar hora
        if (!hora) {
            document.getElementById('error-hora_cita').textContent = 'La hora es obligatoria';
            document.getElementById('hora_cita').classList.add('is-invalid');
            isValid = false;
        } else {
            document.getElementById('error-hora_cita').textContent = '';
            document.getElementById('hora_cita').classList.remove('is-invalid');
            document.getElementById('hora_cita').classList.add('is-valid');
        }
        
        // Validar motivo
        if (!motivo) {
            document.getElementById('error-motivo_cita').textContent = 'El motivo es obligatorio';
            document.getElementById('motivo_cita').classList.add('is-invalid');
            isValid = false;
        } else if (motivo.length < 10) {
            document.getElementById('error-motivo_cita').textContent = 'El motivo debe tener al menos 10 caracteres';
            document.getElementById('motivo_cita').classList.add('is-invalid');
            isValid = false;
        } else {
            document.getElementById('error-motivo_cita').textContent = '';
            document.getElementById('motivo_cita').classList.remove('is-invalid');
            document.getElementById('motivo_cita').classList.add('is-valid');
        }
        
        if (isValid) {
            Swal.fire({
                title: '¿Confirmar cita?',
                text: `¿Deseas agendar la cita para el ${fecha} a las ${hora}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, agendar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        } else {
            Swal.fire({
                title: 'Formulario incompleto',
                text: 'Por favor, corrige los errores en el formulario',
                icon: 'warning',
                confirmButtonText: 'Aceptar'
            });
        }
    });

    // Validación en tiempo real
    document.getElementById('fecha_cita')?.addEventListener('change', function() {
        const fecha = new Date(this.value);
        const hoy = new Date();
        const diaSemana = fecha.getDay();
        
        // Verificar que no sea fin de semana
        if (diaSemana === 0 || diaSemana === 6) {
            Swal.fire({
                title: 'Fecha no disponible',
                text: 'No atendemos los fines de semana. Por favor, selecciona un día de lunes a viernes.',
                icon: 'warning',
                confirmButtonText: 'Aceptar'
            });
            this.value = '';
            return;
        }
        
        // Verificar disponibilidad (aquí podrías hacer una consulta AJAX)
        // Por ahora solo validamos que no sea pasada
        if (fecha < hoy) {
            this.classList.add('is-invalid');
            document.getElementById('error-fecha_cita').textContent = 'La fecha debe ser futura';
        } else {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
            document.getElementById('error-fecha_cita').textContent = '';
        }
    });

    // Animaciones de entrada
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.cita-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });
</script>