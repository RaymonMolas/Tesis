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

// Procesar formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultado = ClienteControlador::ctrRegistrarCliente();
    
    if ($resultado == "ok") {
        echo '<script>
            Swal.fire({
                title: "¡Éxito!",
                text: "Cliente registrado correctamente",
                icon: "success",
                confirmButtonText: "Aceptar"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location = "index.php?pagina=tabla/clientes";
                }
            });
        </script>';
    } else {
        echo '<script>
            Swal.fire({
                title: "Error",
                text: "Error al registrar el cliente: ' . $resultado . '",
                icon: "error",
                confirmButtonText: "Aceptar"
            });
        </script>';
    }
}
?>

<title>NUEVO CLIENTE - Registro de Cliente</title>

<style>
    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
    }

    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .required {
        color: #dc3545;
        font-weight: bold;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 10px;
        padding: 0.75rem 2rem;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
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
        color: #667eea;
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

    @media (max-width: 768px) {
        .form-row {
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
        <a class="breadcrumb-item" href="index.php?pagina=tabla/clientes">Clientes</a>
        <span class="breadcrumb-item active">Nuevo Cliente</span>
    </nav>

    <!-- Encabezado de la página -->
    <div class="page-header">
        <h1 class="page-title">
            <i class="bi bi-person-plus"></i> Nuevo Cliente
        </h1>
        <p class="page-subtitle">Registra un nuevo cliente en el sistema</p>
    </div>

    <!-- Formulario -->
    <div class="form-container">
        <div class="form-header">
            <h3 class="mb-0">
                <i class="bi bi-person-lines-fill"></i> Información del Cliente
            </h3>
            <small>Completa todos los campos obligatorios</small>
        </div>

        <div class="form-content">
            <form method="POST" id="formCliente" novalidate>
                <!-- Información Personal -->
                <div class="form-section">
                    <h4 class="section-title">
                        <i class="bi bi-person"></i> Información Personal
                    </h4>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="bi bi-person"></i> Nombre <span class="required">*</span>
                            </label>
                            <input type="text" class="form-control" name="nombre" id="nombre" 
                                   placeholder="Ingrese el nombre" required maxlength="100"
                                   onkeyup="validarCampo(this)" onblur="validarCampo(this)">
                            <div class="invalid-feedback" id="error-nombre"></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="bi bi-person"></i> Apellido <span class="required">*</span>
                            </label>
                            <input type="text" class="form-control" name="apellido" id="apellido" 
                                   placeholder="Ingrese el apellido" required maxlength="100"
                                   onkeyup="validarCampo(this)" onblur="validarCampo(this)">
                            <div class="invalid-feedback" id="error-apellido"></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="bi bi-card-text"></i> Número de Cédula
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">CI</span>
                                <input type="text" class="form-control" name="cedula" id="cedula" 
                                       placeholder="Ej: 1234567" maxlength="20"
                                       onkeyup="validarCedula(this)" onblur="validarCedula(this)">
                            </div>
                            <div class="form-hint">Ingrese solo números, sin puntos ni guiones</div>
                            <div class="invalid-feedback" id="error-cedula"></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="bi bi-building"></i> RUC
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">RUC</span>
                                <input type="text" class="form-control" name="ruc" id="ruc" 
                                       placeholder="Ej: 12345678-9" maxlength="20"
                                       onkeyup="validarRuc(this)" onblur="validarRuc(this)">
                            </div>
                            <div class="form-hint">Formato: 12345678-9</div>
                            <div class="invalid-feedback" id="error-ruc"></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="bi bi-calendar"></i> Fecha de Nacimiento
                            </label>
                            <input type="date" class="form-control" name="fecha_nacimiento" id="fecha_nacimiento"
                                   max="<?php echo date('Y-m-d', strtotime('-18 years')); ?>"
                                   onchange="validarFechaNacimiento(this)">
                            <div class="form-hint">El cliente debe ser mayor de edad</div>
                            <div class="invalid-feedback" id="error-fecha_nacimiento"></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="bi bi-gender-ambiguous"></i> Sexo
                            </label>
                            <select class="form-control" name="sexo" id="sexo">
                                <option value="">Seleccione una opción</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Información de Contacto -->
                <div class="form-section">
                    <h4 class="section-title">
                        <i class="bi bi-telephone"></i> Información de Contacto
                    </h4>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="bi bi-telephone"></i> Teléfono <span class="required">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-telephone"></i>
                                </span>
                                <input type="tel" class="form-control" name="telefono" id="telefono" 
                                       placeholder="Ej: 0981-123456" required maxlength="20"
                                       onkeyup="validarTelefono(this)" onblur="validarTelefono(this)">
                            </div>
                            <div class="form-hint">Incluye código de área (0981, 021, etc.)</div>
                            <div class="invalid-feedback" id="error-telefono"></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="bi bi-envelope"></i> Email
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">@</span>
                                <input type="email" class="form-control" name="email" id="email" 
                                       placeholder="ejemplo@gmail.com" maxlength="100"
                                       onkeyup="validarEmail(this)" onblur="validarEmail(this)">
                            </div>
                            <div class="form-hint">Email opcional para notificaciones</div>
                            <div class="invalid-feedback" id="error-email"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-geo-alt"></i> Dirección
                        </label>
                        <textarea class="form-control" name="direccion" id="direccion" 
                                  placeholder="Dirección completa del cliente" rows="3" maxlength="500"
                                  onkeyup="contarCaracteres(this, 500)"></textarea>
                        <div class="character-count" id="count-direccion">0 / 500 caracteres</div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="cancelar()">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnGuardar">
                        <i class="bi bi-save"></i> Guardar Cliente
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
            errorDiv.textContent = `El campo ${campo.placeholder.toLowerCase()} es obligatorio`;
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

    function validarCedula(campo) {
        const valor = campo.value.trim();
        const errorDiv = document.getElementById('error-cedula');
        
        if (valor === '') {
            campo.classList.remove('is-invalid', 'is-valid');
            errorDiv.textContent = '';
            return true;
        }
        
        // Solo números
        if (!/^\d+$/.test(valor)) {
            campo.classList.add('is-invalid');
            campo.classList.remove('is-valid');
            errorDiv.textContent = 'La cédula debe contener solo números';
            return false;
        }
        
        // Longitud mínima
        if (valor.length < 6) {
            campo.classList.add('is-invalid');
            campo.classList.remove('is-valid');
            errorDiv.textContent = 'La cédula debe tener al menos 6 dígitos';
            return false;
        }
        
        campo.classList.add('is-valid');
        campo.classList.remove('is-invalid');
        errorDiv.textContent = '';
        return true;
    }

    function validarRuc(campo) {
        const valor = campo.value.trim();
        const errorDiv = document.getElementById('error-ruc');
        
        if (valor === '') {
            campo.classList.remove('is-invalid', 'is-valid');
            errorDiv.textContent = '';
            return true;
        }
        
        // Formato RUC paraguayo: 12345678-9
        if (!/^\d{8}-\d$/.test(valor)) {
            campo.classList.add('is-invalid');
            campo.classList.remove('is-valid');
            errorDiv.textContent = 'Formato de RUC inválido. Use: 12345678-9';
            return false;
        }
        
        campo.classList.add('is-valid');
        campo.classList.remove('is-invalid');
        errorDiv.textContent = '';
        return true;
    }

    function validarTelefono(campo) {
        const valor = campo.value.trim();
        const errorDiv = document.getElementById('error-telefono');
        
        if (valor === '') {
            campo.classList.add('is-invalid');
            campo.classList.remove('is-valid');
            errorDiv.textContent = 'El teléfono es obligatorio';
            return false;
        }
        
        // Formato básico de teléfono paraguayo
        if (!/^0\d{2,3}-?\d{6,7}$/.test(valor.replace(/\s/g, ''))) {
            campo.classList.add('is-invalid');
            campo.classList.remove('is-valid');
            errorDiv.textContent = 'Formato de teléfono inválido. Ej: 0981-123456';
            return false;
        }
        
        campo.classList.add('is-valid');
        campo.classList.remove('is-invalid');
        errorDiv.textContent = '';
        return true;
    }

    function validarEmail(campo) {
        const valor = campo.value.trim();
        const errorDiv = document.getElementById('error-email');
        
        if (valor === '') {
            campo.classList.remove('is-invalid', 'is-valid');
            errorDiv.textContent = '';
            return true;
        }
        
        // Validación de email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(valor)) {
            campo.classList.add('is-invalid');
            campo.classList.remove('is-valid');
            errorDiv.textContent = 'Formato de email inválido';
            return false;
        }
        
        campo.classList.add('is-valid');
        campo.classList.remove('is-invalid');
        errorDiv.textContent = '';
        return true;
    }

    function validarFechaNacimiento(campo) {
        const valor = campo.value;
        const errorDiv = document.getElementById('error-fecha_nacimiento');
        
        if (valor === '') {
            campo.classList.remove('is-invalid', 'is-valid');
            errorDiv.textContent = '';
            return true;
        }
        
        const fecha = new Date(valor);
        const hoy = new Date();
        const edad = hoy.getFullYear() - fecha.getFullYear();
        
        if (edad < 18) {
            campo.classList.add('is-invalid');
            campo.classList.remove('is-valid');
            errorDiv.textContent = 'El cliente debe ser mayor de edad';
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

    // Validación del formulario completo
    function validarFormulario() {
        const campos = ['nombre', 'apellido', 'telefono'];
        const camposOpcionales = ['cedula', 'ruc', 'email', 'fecha_nacimiento'];
        
        let esValido = true;
        
        // Validar campos obligatorios
        campos.forEach(campo => {
            const elemento = document.getElementById(campo);
            if (!validarCampo(elemento)) {
                esValido = false;
            }
        });
        
        // Validar campos opcionales
        camposOpcionales.forEach(campo => {
            const elemento = document.getElementById(campo);
            if (elemento.value.trim() !== '') {
                switch(campo) {
                    case 'cedula':
                        if (!validarCedula(elemento)) esValido = false;
                        break;
                    case 'ruc':
                        if (!validarRuc(elemento)) esValido = false;
                        break;
                    case 'email':
                        if (!validarEmail(elemento)) esValido = false;
                        break;
                    case 'fecha_nacimiento':
                        if (!validarFechaNacimiento(elemento)) esValido = false;
                        break;
                }
            }
        });
        
        // Validar teléfono por separado
        if (!validarTelefono(document.getElementById('telefono'))) {
            esValido = false;
        }
        
        return esValido;
    }

    // Envío del formulario
    document.getElementById('formCliente').addEventListener('submit', function(e) {
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
            text: '¿Deseas registrar este cliente?',
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
                window.location = 'index.php?pagina=tabla/clientes';
            }
        });
    }

    // Formatear campos automáticamente
    document.getElementById('telefono').addEventListener('input', function() {
        let valor = this.value.replace(/\D/g, '');
        if (valor.length > 4) {
            valor = valor.substring(0, 4) + '-' + valor.substring(4, 10);
        }
        this.value = valor;
    });

    document.getElementById('ruc').addEventListener('input', function() {
        let valor = this.value.replace(/\D/g, '');
        if (valor.length > 8) {
            valor = valor.substring(0, 8) + '-' + valor.substring(8, 9);
        }
        this.value = valor;
    });

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