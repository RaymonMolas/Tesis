<?php
// Si ya está logueado, redirigir al inicio
if (isset($_SESSION["validarIngreso"]) && $_SESSION["validarIngreso"] == "ok") {
    echo '<script>window.location = "index.php?pagina=inicio";</script>';
    return;
}

// Obtener información de la empresa para mostrar en el login
$infoEmpresa = ControladorPlantilla::ctrObtenerInfoEmpresa();
?>

<title>INICIAR SESIÓN - <?php echo $infoEmpresa['nombre_empresa']; ?></title>

<style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0;
        padding: 20px;
        position: relative;
        overflow: hidden;
    }

    /* Animación de fondo */
    .background-animation {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
        overflow: hidden;
    }

    .floating-shape {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        animation: float 6s ease-in-out infinite;
    }

    .floating-shape:nth-child(1) {
        width: 80px;
        height: 80px;
        top: 10%;
        left: 10%;
        animation-delay: 0s;
    }

    .floating-shape:nth-child(2) {
        width: 60px;
        height: 60px;
        top: 70%;
        left: 80%;
        animation-delay: 2s;
    }

    .floating-shape:nth-child(3) {
        width: 40px;
        height: 40px;
        top: 30%;
        left: 90%;
        animation-delay: 4s;
    }

    .floating-shape:nth-child(4) {
        width: 100px;
        height: 100px;
        top: 50%;
        left: 5%;
        animation-delay: 1s;
    }

    .floating-shape:nth-child(5) {
        width: 50px;
        height: 50px;
        top: 20%;
        left: 70%;
        animation-delay: 3s;
    }

    @keyframes float {
        0%, 100% {
            transform: translateY(0px) rotate(0deg);
        }
        50% {
            transform: translateY(-20px) rotate(180deg);
        }
    }

    /* Contenedor principal */
    .login-container {
        background: white;
        border-radius: 20px;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
        overflow: hidden;
        width: 100%;
        max-width: 900px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        min-height: 600px;
        position: relative;
    }

    .login-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #ff6b6b, #4ecdc4, #45b7d1, #96ceb4);
        z-index: 10;
    }

    /* Panel de bienvenida */
    .welcome-panel {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 3rem 2rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .welcome-panel::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
        animation: backgroundMove 20s linear infinite;
    }

    @keyframes backgroundMove {
        0% { transform: translate(0, 0); }
        100% { transform: translate(-50px, -50px); }
    }

    .company-logo {
        width: 120px;
        height: 120px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 2rem;
        font-size: 3rem;
        color: white;
        border: 3px solid rgba(255, 255, 255, 0.3);
        position: relative;
        z-index: 2;
    }

    .welcome-title {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 1rem;
        position: relative;
        z-index: 2;
    }

    .welcome-subtitle {
        font-size: 1.2rem;
        opacity: 0.9;
        margin-bottom: 2rem;
        position: relative;
        z-index: 2;
    }

    .features-list {
        list-style: none;
        padding: 0;
        margin: 0;
        position: relative;
        z-index: 2;
    }

    .features-list li {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
        font-size: 1rem;
        opacity: 0.9;
    }

    .features-list i {
        font-size: 1.2rem;
        width: 20px;
        text-align: center;
    }

    /* Panel de login */
    .login-panel {
        padding: 3rem 2rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .login-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .login-title {
        font-size: 2rem;
        font-weight: bold;
        color: #2c3e50;
        margin-bottom: 0.5rem;
    }

    .login-subtitle {
        color: #7f8c8d;
        font-size: 1rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
        position: relative;
    }

    .form-label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.5rem;
        display: block;
        font-size: 0.9rem;
    }

    .input-group {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-icon {
        position: absolute;
        left: 1rem;
        color: #7f8c8d;
        font-size: 1.1rem;
        z-index: 2;
    }

    .form-control {
        width: 100%;
        padding: 1rem 1rem 1rem 3rem;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }

    .form-control:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-control.is-invalid {
        border-color: #dc3545;
        background: #fff5f5;
    }

    .form-control.is-valid {
        border-color: #28a745;
        background: #f8fff8;
    }

    .password-toggle {
        position: absolute;
        right: 1rem;
        color: #7f8c8d;
        cursor: pointer;
        z-index: 2;
        font-size: 1.1rem;
        transition: color 0.3s ease;
    }

    .password-toggle:hover {
        color: #495057;
    }

    .form-select {
        width: 100%;
        padding: 1rem 1rem 1rem 3rem;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #f8f9fa;
        appearance: none;
        background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" fill="%23666" viewBox="0 0 16 16"><path d="M8 11.5L3 6.5h10l-5 5z"/></svg>');
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 16px;
    }

    .form-select:focus {
        outline: none;
        border-color: #667eea;
        background-color: white;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .btn-login {
        width: 100%;
        padding: 1rem;
        border: none;
        border-radius: 10px;
        font-size: 1.1rem;
        font-weight: 600;
        color: white;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        margin-top: 1rem;
    }

    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    }

    .btn-login:active {
        transform: translateY(0);
    }

    .btn-login:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .loading-spinner {
        display: none;
        width: 20px;
        height: 20px;
        border: 2px solid transparent;
        border-top: 2px solid white;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-right: 0.5rem;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .error-message {
        background: #f8d7da;
        color: #721c24;
        padding: 1rem;
        border-radius: 10px;
        border: 1px solid #f5c6cb;
        margin-bottom: 1rem;
        display: none;
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .form-footer {
        text-align: center;
        margin-top: 2rem;
        padding-top: 1rem;
        border-top: 1px solid #e9ecef;
    }

    .form-footer p {
        color: #6c757d;
        font-size: 0.9rem;
        margin: 0;
    }

    .system-status {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 1rem;
        font-size: 0.85rem;
        color: #28a745;
    }

    .status-dot {
        width: 8px;
        height: 8px;
        background: #28a745;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .login-container {
            grid-template-columns: 1fr;
            max-width: 400px;
            margin: 1rem;
        }
        
        .welcome-panel {
            padding: 2rem 1.5rem;
        }
        
        .login-panel {
            padding: 2rem 1.5rem;
        }
        
        .welcome-title {
            font-size: 2rem;
        }
        
        .company-logo {
            width: 80px;
            height: 80px;
            font-size: 2rem;
        }
    }

    /* Modo oscuro (opcional) */
    @media (prefers-color-scheme: dark) {
        .login-panel {
            background: #2c3e50;
            color: white;
        }
        
        .login-title {
            color: white;
        }
        
        .form-control, .form-select {
            background: #34495e;
            border-color: #4a5f7a;
            color: white;
        }
        
        .form-control:focus, .form-select:focus {
            background: #3d4e62;
        }
    }
</style>

<div class="background-animation">
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>
</div>

<div class="login-container">
    <!-- Panel de Bienvenida -->
    <div class="welcome-panel">
        <div class="company-logo">
            <?php if (!empty($infoEmpresa['logo'])): ?>
                <img src="<?php echo $infoEmpresa['logo']; ?>" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
            <?php else: ?>
                <i class="bi bi-gear-fill"></i>
            <?php endif; ?>
        </div>
        
        <h1 class="welcome-title"><?php echo htmlspecialchars($infoEmpresa['nombre_empresa']); ?></h1>
        <p class="welcome-subtitle">Sistema de Gestión de Taller Mecánico</p>
        
        <ul class="features-list">
            <li>
                <i class="bi bi-shield-check"></i>
                <span>Acceso seguro y confiable</span>
            </li>
            <li>
                <i class="bi bi-people"></i>
                <span>Gestión de clientes</span>
            </li>
            <li>
                <i class="bi bi-car-front"></i>
                <span>Control de vehículos</span>
            </li>
            <li>
                <i class="bi bi-tools"></i>
                <span>Órdenes de trabajo</span>
            </li>
            <li>
                <i class="bi bi-receipt"></i>
                <span>Facturación integrada</span>
            </li>
        </ul>
    </div>

    <!-- Panel de Login -->
    <div class="login-panel">
        <div class="login-header">
            <h2 class="login-title">Iniciar Sesión</h2>
            <p class="login-subtitle">Ingresa tus credenciales para acceder al sistema</p>
        </div>

        <div class="error-message" id="errorMessage">
            <i class="bi bi-exclamation-triangle"></i>
            <span id="errorText"></span>
        </div>

        <form method="POST" id="loginForm" novalidate>
            <!-- Tipo de Usuario -->
            <div class="form-group">
                <label class="form-label">Tipo de Usuario</label>
                <div class="input-group">
                    <i class="input-icon bi bi-person-gear"></i>
                    <select class="form-select" name="tipo_usuario" id="tipo_usuario" required>
                        <option value="" disabled selected>Seleccione su tipo de usuario</option>
                        <option value="cliente">Cliente</option>
                        <option value="personal">Personal</option>
                    </select>
                </div>
            </div>

            <!-- Usuario -->
            <div class="form-group">
                <label class="form-label">Usuario</label>
                <div class="input-group">
                    <i class="input-icon bi bi-person"></i>
                    <input type="text" class="form-control" name="txtusuario" id="txtusuario" 
                           placeholder="Ingrese su usuario" required autocomplete="username">
                </div>
            </div>

            <!-- Contraseña -->
            <div class="form-group">
                <label class="form-label">Contraseña</label>
                <div class="input-group">
                    <i class="input-icon bi bi-lock"></i>
                    <input type="password" class="form-control" name="txtclave" id="txtclave" 
                           placeholder="Ingrese su contraseña" required autocomplete="current-password">
                    <i class="password-toggle bi bi-eye" id="passwordToggle"></i>
                </div>
            </div>

            <!-- Procesar login -->
            <?php 
                $ingreso = new logincontrolador();
                $ingreso->ctrIngreso();
            ?>

            <!-- Botón de Login -->
            <button type="submit" class="btn-login" id="loginButton">
                <span class="loading-spinner" id="loadingSpinner"></span>
                <i class="bi bi-box-arrow-in-right" id="loginIcon"></i>
                <span id="loginText">Iniciar Sesión</span>
            </button>
        </form>

        <div class="form-footer">
            <p>Sistema desarrollado para la gestión eficiente de talleres mecánicos</p>
            <div class="system-status">
                <div class="status-dot"></div>
                <span>Sistema en línea</span>
            </div>
        </div>
    </div>
</div>

<script>
    // Alternar visibilidad de contraseña
    document.getElementById('passwordToggle').addEventListener('click', function() {
        const passwordField = document.getElementById('txtclave');
        const toggleIcon = this;
        
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleIcon.className = 'password-toggle bi bi-eye-slash';
        } else {
            passwordField.type = 'password';
            toggleIcon.className = 'password-toggle bi bi-eye';
        }
    });

    // Validación en tiempo real
    const form = document.getElementById('loginForm');
    const inputs = form.querySelectorAll('input, select');
    
    inputs.forEach(input => {
        input.addEventListener('blur', validateField);
        input.addEventListener('input', clearErrors);
    });

    function validateField(e) {
        const field = e.target;
        const value = field.value.trim();
        
        if (field.hasAttribute('required') && !value) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
        } else if (value) {
            field.classList.add('is-valid');
            field.classList.remove('is-invalid');
        }
    }

    function clearErrors(e) {
        const field = e.target;
        field.classList.remove('is-invalid');
        hideError();
    }

    function showError(message) {
        const errorDiv = document.getElementById('errorMessage');
        const errorText = document.getElementById('errorText');
        errorText.textContent = message;
        errorDiv.style.display = 'block';
    }

    function hideError() {
        const errorDiv = document.getElementById('errorMessage');
        errorDiv.style.display = 'none';
    }

    // Envío del formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const tipoUsuario = formData.get('tipo_usuario');
        const usuario = formData.get('txtusuario').trim();
        const clave = formData.get('txtclave').trim();
        
        // Validaciones
        if (!tipoUsuario) {
            showError('Debe seleccionar un tipo de usuario');
            document.getElementById('tipo_usuario').focus();
            return;
        }
        
        if (!usuario) {
            showError('Debe ingresar su usuario');
            document.getElementById('txtusuario').focus();
            return;
        }
        
        if (!clave) {
            showError('Debe ingresar su contraseña');
            document.getElementById('txtclave').focus();
            return;
        }
        
        // Mostrar loading
        const loginButton = document.getElementById('loginButton');
        const loadingSpinner = document.getElementById('loadingSpinner');
        const loginIcon = document.getElementById('loginIcon');
        const loginText = document.getElementById('loginText');
        
        loginButton.disabled = true;
        loadingSpinner.style.display = 'inline-block';
        loginIcon.style.display = 'none';
        loginText.textContent = 'Iniciando sesión...';
        
        hideError();
        
        // Simular delay mínimo para mejor UX
        setTimeout(() => {
            form.submit();
        }, 500);
    });

    // Efectos visuales
    document.addEventListener('DOMContentLoaded', function() {
        // Animación de entrada
        const container = document.querySelector('.login-container');
        container.style.opacity = '0';
        container.style.transform = 'translateY(50px)';
        
        setTimeout(() => {
            container.style.transition = 'all 0.8s ease';
            container.style.opacity = '1';
            container.style.transform = 'translateY(0)';
        }, 100);

        // Foco automático
        setTimeout(() => {
            document.getElementById('tipo_usuario').focus();
        }, 1000);
    });

    // Detección de tecla Enter
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            const activeElement = document.activeElement;
            if (activeElement.tagName === 'INPUT' || activeElement.tagName === 'SELECT') {
                e.preventDefault();
                form.dispatchEvent(new Event('submit'));
            }
        }
    });

    // Prevenir copiar/pegar en el campo de contraseña (opcional)
    document.getElementById('txtclave').addEventListener('paste', function(e) {
        e.preventDefault();
        showError('No se permite pegar contraseñas por seguridad');
        setTimeout(hideError, 3000);
    });

    // Limpiar formulario al cambiar tipo de usuario
    document.getElementById('tipo_usuario').addEventListener('change', function() {
        document.getElementById('txtusuario').value = '';
        document.getElementById('txtclave').value = '';
        hideError();
        
        // Limpiar validaciones
        inputs.forEach(input => {
            input.classList.remove('is-valid', 'is-invalid');
        });
    });
</script>