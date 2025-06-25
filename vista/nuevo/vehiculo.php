<?php
if (!isset($_SESSION["validarIngreso"])) {
    echo '<script>window.location = "index.php?pagina=login";</script>';
    return;
} elseif ($_SESSION["validarIngreso"] != "ok") {
    echo '<script>window.location = "index.php?pagina=login";</script>';
    return;
}

// Verificar permisos
if ($_SESSION["tipo_usuario"] != "personal") {
    echo '<script>window.location = "index.php?pagina=inicio";</script>';
    return;
}

// Obtener lista de clientes para el select
$clientes = ClienteControlador::ctrListarClientes();

// Procesar formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultado = VehiculoControlador::ctrRegistrarVehiculo();
    
    if ($resultado == "ok") {
        echo '<script>
            Swal.fire({
                title: "¡Éxito!",
                text: "Vehículo registrado correctamente",
                icon: "success",
                confirmButtonText: "Aceptar"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location = "index.php?pagina=tabla/vehiculos";
                }
            });
        </script>';
    } else {
        echo '<script>
            Swal.fire({
                title: "Error",
                text: "Error al registrar el vehículo: ' . $resultado . '",
                icon: "error",
                confirmButtonText: "Aceptar"
            });
        </script>';
    }
}
?>

<title>NUEVO VEHÍCULO - Registro de Vehículo</title>

<style>
    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
    }

    .page-header {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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

    .form-container {
        background: white;
        border-radius: 15px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .form-header {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 1.5rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .form-content {
        padding: 2rem;
    }

    .form-section {
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 2px solid #f8f9fa;
    }

    .form-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }

    .section-title {
        color: #2c3e50;
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
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
    }

    .form-control:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
    }

    .form-control.is-valid {
        border-color: #28a745;
    }

    .form-control.is-invalid {
        border-color: #dc3545;
    }

    .invalid-feedback {
        display: block;
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    .valid-feedback {
        display: block;
        color: #28a745;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    .input-group {
        position: relative;
    }

    .input-group-text {
        background: #f8f9fa;
        border: 2px solid #e9ecef;
        border-right: none;
        border-radius: 10px 0 0 10px;
        padding: 0.75rem 1rem;
        color: #6c757d;
    }

    .input-group .form-control {
        border-left: none;
        border-radius: 0 10px 10px 0;
    }

    .input-group .form-control:focus {
        box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
    }

    .required {
        color: #dc3545;
        font-weight: bold;
    }

    .btn-primary {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        border-radius: 10px;
        padding: 0.75rem 2rem;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
    }

    .btn-secondary {
        background: #6c757d;
        border: none;
        border-radius: 10px;
        padding: 0.75rem 2rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-2px);
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        padding-top: 1.5rem;
        border-top: 2px solid #f8f9fa;
    }

    .breadcrumb {
        background: rgba(255, 255, 255, 0.9);
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        content: "→";
        color: #28a745;
        font-weight: bold;
    }

    .form-hint {
        font-size: 0.875rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }

    .character-count {
        font-size: 0.75rem;
        color: #6c757d;
        text-align: right;
        margin-top: 0.25rem;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .form-row-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 1rem;
    }

    .vehicle-preview {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 10px;
        padding: 1rem;
        margin-top: 1rem;
        text-align: center;
    }

    .vehicle-icon {
        font-size: 3rem;
        color: #28a745;
        margin-bottom: 0.5rem;
    }

    .alert-info {
        background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
        border: 1px solid #b6d4da;
        border-radius: 10px;
        color: #0c5460;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    @media (max-width: 768px) {
        .form-row, .form-row-3 {
            grid-template-columns: 1fr;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .page-title {
            font-size: 2rem;
        }
    }
</style>

<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav class="breadcrumb">
        <a class="breadcrumb-item" href="index.php?pagina=inicio">Inicio</a>
        <a class="breadcrumb-item" href="index.php?pagina=tabla/vehiculos">Vehículos</a>
        <span class="breadcrumb-item active">Nuevo Vehículo</span>
    </nav>

    <!-- Encabezado de la página -->
    <div class="page-header">
        <h1 class="page-title">
            <i class="bi bi-car-front-fill"></i> Nuevo Vehículo
        </h1>
        <p class="page-subtitle">Registra un nuevo vehículo en el sistema</p>
    </div>

    <!-- Formulario -->
    <div class="form-container">
        <div class="form-header">
            <h3 class="mb-0">
                <i class="bi bi-car-front"></i> Información del Vehículo
            </h3>
            <small>Completa todos los campos obligatorios</small>
        </div>

        <div class="form-content">
            <form method="POST" id="formVehiculo" novalidate>
                <!-- Selección de Cliente -->
                <div class="form-section">
                    <h4 class="section-title">
                        <i class="bi bi-person"></i> Propietario del Vehículo
                    </h4>
                    
                    <?php if (empty($clientes)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>¡Atención!</strong> No hay clientes registrados. 
                            <a href="index.php?pagina=nuevo/cliente" class="alert-link">Registra un cliente primero</a>.
                        </div>
                    <?php else: ?>
                        <div class="form-group">
                            <label class="form-label">
                                <i class="bi bi-person-circle"></i> Cliente Propietario <span class="required">*</span>
                            </label>
                            <select class="form-control" name="id_cliente" id="id_cliente" required onchange="mostrarInfoCliente(this)">
                                <option value="">Seleccione un cliente</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?php echo $cliente['id_cliente']; ?>" 
                                            data-nombre="<?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?>"
                                            data-telefono="<?php echo htmlspecialchars($cliente['telefono']); ?>"
                                            data-email="<?php echo htmlspecialchars($cliente['email']); ?>">
                                        <?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?> 
                                        - <?php echo htmlspecialchars($cliente['telefono']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback" id="error-id_cliente"></div>
                        </div>

                        <div id="infoCliente" class="alert alert-info" style="display: none;">
                            <h6><i class="bi bi-info-circle"></i> Información del Cliente</h6>
                            <div id="datosCliente"></div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Información Básica del Vehículo -->
                <div class="form-section">
                    <h4 class="section-title">
                        <i class="bi bi-car-front"></i> Información Básica
                    </h4>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="bi bi-badge-tm"></i> Marca <span class="required">*</span>
                            </label>
                            <input type="text" class="form-control" name="marca" id="marca" 
                                   placeholder="Ej: Toyota, Ford, Chevrolet" required maxlength="100"
                                   onkeyup="validarCampo(this); actualizarPreview()" onblur="validarCampo(this)">
                            <div class="invalid-feedback" id="error-marca"></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="bi bi-car-front"></i> Modelo <span class="required">*</span>
                            </label>
                            <input type="text" class="form-control" name="modelo" id="modelo" 
                                   placeholder="Ej: Corolla, Focus, Cruze" required maxlength="100"
                                   onkeyup="validarCampo(this); actualizarPreview()" onblur="validarCampo(this)">
                            <div class="invalid-feedback" id="error-modelo"></div>
                        </div>
                    </div>

                    <div class="form-row-3">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="bi bi-calendar"></i> Año <span class="required">*</span>
                            </label>
                            <input type="number" class="form-control" name="año" id="año" 
                                   placeholder="2020" required 
                                   min="1900" max="<?php echo date('Y') + 1; ?>"
                                   onkeyup="validarAño(this); actualizarPreview()" onblur="validarAño(this)">
                            <div class="invalid-feedback" id="error-año"></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="bi bi-palette"></i> Color <span class="required">*</span>
                            </label>
                            <input type="text" class="form-control" name="color" id="color" 
                                   placeholder="Ej: Blanco, Negro, Azul" required maxlength="50"
                                   onkeyup="validarCampo(this); actualizarPreview()" onblur="validarCampo(this)">
                            <div class="invalid-feedback" id="error-color"></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="bi bi-123"></i> Matrícula <span class="required">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">PY</span>
                                <input type="text" class="form-control" name="matricula" id="matricula" 
                                       placeholder="ABC123" required maxlength="20" style="text-transform: uppercase;"
                                       onkeyup="validarMatricula(this); actualizarPreview()" onblur="validarMatricula(this)">
                            </div>
                            <div class="form-hint">Formato: ABC123 o AB123CD</div>
                            <div class="invalid-feedback" id="error-matricula"></div>
                        </div>
                    </div>
                </div>

                <!-- Información Técnica -->
                <div class="form-section">
                    <h4 class="section-title">
                        <i class="bi bi-gear"></i> Información Técnica
                    </h4>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="bi bi-fuel-pump"></i> Tipo de Combustible <span class="required">*</span>
                            </label>
                            <select class="form-control" name="combustible" id="combustible" required onchange="validarCampo(this)">
                                <option value="">Seleccione el combustible</option>
                                <option value="gasolina">Gasolina</option>
                                <option value="diesel">Diésel</option>
                                <option value="electrico">Eléctrico</option>
                                <option value="hibrido">Híbrido</option>
                            </select>
                            <div class="invalid-feedback" id="error-combustible"></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="bi bi-gear-wide-connected"></i> Transmisión <span class="required">*</span>
                            </label>
                            <select class="form-control" name="transmision" id="transmision" required onchange="validarCampo(this)">
                                <option value="">Seleccione la transmisión</option>
                                <option value="manual">Manual</option>
                                <option value="automatica">Automática</option>
                            </select>
                            <div class="invalid-feedback" id="error-transmision"></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="bi bi-hash"></i> Número de Chasis
                            </label>
                            <input type="text" class="form-control" name="chasis" id="chasis" 
                                   placeholder="Número de chasis del vehículo" maxlength="50"
                                   onkeyup="validarChasis(this)" onblur="validarChasis(this)">
                            <div class="form-hint">Opcional - Número identificatorio del chasis</div>
                            <div class="invalid-feedback" id="error-chasis"></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="bi bi-engine"></i> Número de Motor
                            </label>
                            <input type="text" class="form-control" name="motor" id="motor" 
                                   placeholder="Número de motor del vehículo" maxlength="50"
                                   onkeyup="validarMotor(this)" onblur="validarMotor(this)">
                            <div class="form-hint">Opcional - Número identificatorio del motor</div>
                            <div class="invalid-feedback" id="error-motor"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-speedometer2"></i> Kilometraje Actual
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="kilometraje" id="kilometraje" 
                                   placeholder="0" min="0" max="999999"
                                   onkeyup="validarKilometraje(this)" onblur="validarKilometraje(this)">
                            <span class="input-group-text">km</span>
                        </div>
                        <div class="form-hint">Kilometraje actual del vehículo</div>
                        <div class="invalid-feedback" id="error-kilometraje"></div>
                    </div>
                </div>

                <!-- Observaciones -->
                <div class="form-section">
                    <h4 class="section-title">
                        <i class="bi bi-chat-left-text"></i> Observaciones Adicionales
                    </h4>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-journal-text"></i> Observaciones
                        </label>
                        <textarea class="form-control" name="observaciones" id="observaciones" 
                                  placeholder="Información adicional del vehículo, estado, modificaciones, etc." 
                                  rows="4" maxlength="1000"
                                  onkeyup="contarCaracteres(this, 1000)"></textarea>
                        <div class="character-count" id="count-observaciones">0 / 1000 caracteres</div>
                    </div>
                </div>

                <!-- Preview del Vehículo -->
                <div class="vehicle-preview" id="vehiclePreview" style="display: none;">
                    <div class="vehicle-icon">
                        <i class="bi bi-car-front-fill"></i>
                    </div>
                    <h5 id="previewTitle">Vista Previa del Vehículo</h5>
                    <p id="previewDetails">Completa los campos para ver la vista previa</p>
                </div>

                <!-- Botones de acción -->
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="cancelar()">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnGuardar" <?php echo empty($clientes) ? 'disabled' : ''; ?>>
                        <i class="bi bi-save"></i> Guardar Vehículo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Validación en tiempo real
    function validarCampo(campo) {
        const valor = campo.value.trim();
        const errorId = 'error-' + campo.name;
        const errorDiv = document.getElementById(errorId);
        
        if (campo.hasAttribute('required') && valor === '') {
            campo.classList.add('is-invalid');
            campo.classList.remove('is-valid');
            errorDiv.textContent = `Este campo es obligatorio`;
            return false;
        } else if (valor.length > 0) {
            campo.classList.add('is-valid');
            campo.classList.remove('is-invalid');
            errorDiv.textContent = '';
            return true;
        } else {
            campo.classList.remove('is-invalid', 'is-valid');
            errorDiv.textContent = '';
            return true;
        }
    }

    function validarAño(campo) {
        const valor = parseInt(campo.value);
        const errorDiv = document.getElementById('error-año');
        const añoActual = new Date().getFullYear();
        
        if (!valor) {
            campo.classList.add('is-invalid');
            campo.classList.remove('is-valid');
            errorDiv.textContent = 'El año es obligatorio';
            return false;
        }
        
        if (valor < 1900 || valor > añoActual + 1) {
            campo.classList.add('is-invalid');
            campo.classList.remove('is-valid');
            errorDiv.textContent = `El año debe estar entre 1900 y ${añoActual + 1}`;
            return false;
        }
        
        campo.classList.add('is-valid');
        campo.classList.remove('is-invalid');
        errorDiv.textContent = '';
        return true;
    }

    function validarMatricula(campo) {
        const valor = campo.value.toUpperCase().trim();
        const errorDiv = document.getElementById('error-matricula');
        campo.value = valor; // Convertir a mayúsculas automáticamente
        
        if (valor === '') {
            campo.classList.add('is-invalid');
            campo.classList.remove('is-valid');
            errorDiv.textContent = 'La matrícula es obligatoria';
            return false;
        }
        
        // Formato básico de matrícula paraguaya: ABC123 o AB123CD
        if (!/^[A-Z]{2,3}\d{3}[A-Z]{0,2}$/.test(valor)) {
            campo.classList.add('is-invalid');
            campo.classList.remove('is-valid');
            errorDiv.textContent = 'Formato de matrícula inválido. Ej: ABC123 o AB123CD';
            return false;
        }
        
        campo.classList.add('is-valid');
        campo.classList.remove('is-invalid');
        errorDiv.textContent = '';
        return true;
    }

    function validarChasis(campo) {
        const valor = campo.value.trim();
        const errorDiv = document.getElementById('error-chasis');
        
        if (valor === '') {
            campo.classList.remove('is-invalid', 'is-valid');
            errorDiv.textContent = '';
            return true;
        }
        
        if (valor.length < 17) {
            campo.classList.add('is-invalid');
            campo.classList.remove('is-valid');
            errorDiv.textContent = 'El número de chasis debe tener al menos 17 caracteres';
            return false;
        }
        
        campo.classList.add('is-valid');
        campo.classList.remove('is-invalid');
        errorDiv.textContent = '';
        return true;
    }

    function validarMotor(campo) {
        const valor = campo.value.trim();
        const errorDiv = document.getElementById('error-motor');
        
        if (valor === '') {
            campo.classList.remove('is-invalid', 'is-valid');
            errorDiv.textContent = '';
            return true;
        }
        
        if (valor.length < 5) {
            campo.classList.add('is-invalid');
            campo.classList.remove('is-valid');
            errorDiv.textContent = 'El número de motor debe tener al menos 5 caracteres';
            return false;
        }
        
        campo.classList.add('is-valid');
        campo.classList.remove('is-invalid');
        errorDiv.textContent = '';
        return true;
    }

    function validarKilometraje(campo) {
        const valor = parseInt(campo.value);
        const errorDiv = document.getElementById('error-kilometraje');
        
        if (campo.value === '') {
            campo.classList.remove('is-invalid', 'is-valid');
            errorDiv.textContent = '';
            return true;
        }
        
        if (valor < 0 || valor > 999999) {
            campo.classList.add('is-invalid');
            campo.classList.remove('is-valid');
            errorDiv.textContent = 'El kilometraje debe estar entre 0 y 999,999 km';
            return false;
        }
        
        campo.classList.add('is-valid');
        campo.classList.remove('is-invalid');
        errorDiv.textContent = '';
        return true;
    }

    function contarCaracteres(campo, maximo) {
        const contador = document.getElementById('count-' + campo.name);
        const longitud = campo.value.length;
        contador.textContent = `${longitud} / ${maximo} caracteres`;
        
        if (longitud > maximo * 0.9) {
            contador.style.color = '#dc3545';
        } else if (longitud > maximo * 0.7) {
            contador.style.color = '#ffc107';
        } else {
            contador.style.color = '#6c757d';
        }
    }

    function mostrarInfoCliente(select) {
        const option = select.options[select.selectedIndex];
        const infoDiv = document.getElementById('infoCliente');
        const datosDiv = document.getElementById('datosCliente');
        
        if (option.value) {
            const nombre = option.getAttribute('data-nombre');
            const telefono = option.getAttribute('data-telefono');
            const email = option.getAttribute('data-email') || 'No especificado';
            
            datosDiv.innerHTML = `
                <p><strong>Nombre:</strong> ${nombre}</p>
                <p><strong>Teléfono:</strong> ${telefono}</p>
                <p><strong>Email:</strong> ${email}</p>
            `;
            infoDiv.style.display = 'block';
            
            validarCampo(select);
        } else {
            infoDiv.style.display = 'none';
        }
    }

    function actualizarPreview() {
        const marca = document.getElementById('marca').value;
        const modelo = document.getElementById('modelo').value;
        const año = document.getElementById('año').value;
        const color = document.getElementById('color').value;
        const matricula = document.getElementById('matricula').value;
        
        const preview = document.getElementById('vehiclePreview');
        const title = document.getElementById('previewTitle');
        const details = document.getElementById('previewDetails');
        
        if (marca || modelo || año || color || matricula) {
            const vehiculoTexto = `${marca} ${modelo}`.trim();
            const añoTexto = año ? `(${año})` : '';
            const colorTexto = color ? `Color: ${color}` : '';
            const matriculaTexto = matricula ? `Matrícula: ${matricula}` : '';
            
            title.textContent = vehiculoTexto || 'Vista Previa del Vehículo';
            
            const detalles = [añoTexto, colorTexto, matriculaTexto].filter(d => d).join(' | ');
            details.textContent = detalles || 'Completa más campos para ver los detalles';
            
            preview.style.display = 'block';
        } else {
            preview.style.display = 'none';
        }
    }

    // Validación del formulario completo
    function validarFormulario() {
        const camposObligatorios = ['id_cliente', 'marca', 'modelo', 'año', 'color', 'matricula', 'combustible', 'transmision'];
        const camposOpcionales = ['chasis', 'motor', 'kilometraje'];
        
        let esValido = true;
        
        // Validar campos obligatorios
        camposObligatorios.forEach(campo => {
            const elemento = document.getElementById(campo);
            if (campo === 'año') {
                if (!validarAño(elemento)) esValido = false;
            } else if (campo === 'matricula') {
                if (!validarMatricula(elemento)) esValido = false;
            } else {
                if (!validarCampo(elemento)) esValido = false;
            }
        });
        
        // Validar campos opcionales
        camposOpcionales.forEach(campo => {
            const elemento = document.getElementById(campo);
            if (elemento.value.trim() !== '') {
                switch(campo) {
                    case 'chasis':
                        if (!validarChasis(elemento)) esValido = false;
                        break;
                    case 'motor':
                        if (!validarMotor(elemento)) esValido = false;
                        break;
                    case 'kilometraje':
                        if (!validarKilometraje(elemento)) esValido = false;
                        break;
                }
            }
        });
        
        return esValido;
    }

    // Envío del formulario
    document.getElementById('formVehiculo').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!validarFormulario()) {
            Swal.fire({
                title: 'Formulario incompleto',
                text: 'Por favor, corrige los errores en el formulario',
                icon: 'warning',
                confirmButtonText: 'Aceptar'
            });
            return;
        }
        
        // Mostrar confirmación antes de enviar
        Swal.fire({
            title: '¿Confirmar registro?',
            text: '¿Deseas registrar este vehículo?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, registrar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });

    function cancelar() {
        Swal.fire({
            title: '¿Cancelar registro?',
            text: 'Se perderán todos los datos ingresados',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, cancelar',
            cancelButtonText: 'Continuar editando'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location = 'index.php?pagina=tabla/vehiculos';
            }
        });
    }

    // Animaciones de entrada
    document.addEventListener('DOMContentLoaded', function() {
        const formSections = document.querySelectorAll('.form-section');
        formSections.forEach((section, index) => {
            section.style.opacity = '0';
            section.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                section.style.transition = 'all 0.5s ease';
                section.style.opacity = '1';
                section.style.transform = 'translateY(0)';
            }, index * 200);
        });
    });
</script>