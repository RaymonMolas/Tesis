<?php
// Obtener información del usuario antes de cerrar sesión
$usuario_nombre = '';
$usuario_tipo = '';
$tiempo_sesion = '';

if (isset($_SESSION["nombre"]) && isset($_SESSION["apellido"])) {
    $usuario_nombre = $_SESSION["nombre"] . " " . $_SESSION["apellido"];
}

if (isset($_SESSION["tipo_usuario"])) {
    $usuario_tipo = $_SESSION["tipo_usuario"];
}

if (isset($_SESSION["tiempo_inicio_sesion"])) {
    $inicio = strtotime($_SESSION["tiempo_inicio_sesion"]);
    $ahora = time();
    $duracion = $ahora - $inicio;
    
    $horas = floor($duracion / 3600);
    $minutos = floor(($duracion % 3600) / 60);
    
    if ($horas > 0) {
        $tiempo_sesion = $horas . "h " . $minutos . "m";
    } else {
        $tiempo_sesion = $minutos . " minutos";
    }
}

// Destruir la sesión
session_unset();
session_destroy();

// Limpiar cookies si existen
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}
?>

<title>CERRANDO SESIÓN - Sistema de Taller</title>

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

    @keyframes float {
        0%, 100% {
            transform: translateY(0px) rotate(0deg);
        }
        50% {
            transform: translateY(-20px) rotate(180deg);
        }
    }

    /* Contenedor principal */
    .logout-container {
        background: white;
        border-radius: 20px;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
        text-align: center;
        padding: 3rem;
        max-width: 500px;
        width: 100%;
        position: relative;
        overflow: hidden;
    }

    .logout-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #ff6b6b, #4ecdc4, #45b7d1, #96ceb4);
    }

    .logout-icon {
        font-size: 5rem;
        color: #667eea;
        margin-bottom: 1.5rem;
        animation: fadeInScale 1s ease-out;
    }

    .logout-title {
        font-size: 2.5rem;
        font-weight: bold;
        color: #2c3e50;
        margin-bottom: 1rem;
        animation: fadeInUp 1s ease-out 0.2s both;
    }

    .logout-message {
        font-size: 1.2rem;
        color: #7f8c8d;
        margin-bottom: 2rem;
        animation: fadeInUp 1s ease-out 0.4s both;
    }

    .session-info {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border-left: 4px solid #667eea;
        animation: fadeInUp 1s ease-out 0.6s both;
    }

    .session-info h4 {
        color: #2c3e50;
        margin-bottom: 1rem;
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .session-details {
        display: grid;
        gap: 0.5rem;
        text-align: left;
        color: #6c757d;
    }

    .session-detail {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px solid #e9ecef;
    }

    .session-detail:last-child {
        border-bottom: none;
    }

    .session-detail-label {
        font-weight: 600;
        color: #495057;
    }

    .session-detail-value {
        color: #6c757d;
        font-family: 'Courier New', monospace;
    }

    .loading-container {
        margin: 2rem 0;
        animation: fadeInUp 1s ease-out 0.8s both;
    }

    .loading-spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #e9ecef;
        border-top: 4px solid #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 1rem;
    }

    .loading-text {
        color: #6c757d;
        font-size: 1rem;
        margin-bottom: 0.5rem;
    }

    .progress-bar {
        width: 100%;
        height: 6px;
        background: #e9ecef;
        border-radius: 3px;
        overflow: hidden;
        margin-bottom: 1rem;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 3px;
        animation: progressFill 3s ease-out forwards;
        width: 0;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
        animation: fadeInUp 1s ease-out 1s both;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        color: white;
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
        box-shadow: 0 5px 15px rgba(108, 117, 125, 0.3);
    }

    .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-2px);
        color: white;
    }

    .security-notice {
        background: #e8f4fd;
        border-radius: 10px;
        padding: 1rem;
        margin-top: 2rem;
        border: 1px solid #b8daff;
        animation: fadeInUp 1s ease-out 1.2s both;
    }

    .security-notice-title {
        color: #004085;
        font-weight: 600;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .security-notice-text {
        color: #004085;
        font-size: 0.9rem;
        margin: 0;
    }

    .countdown {
        font-size: 1.5rem;
        font-weight: bold;
        color: #667eea;
        margin: 1rem 0;
    }

    /* Animaciones */
    @keyframes fadeInScale {
        from {
            opacity: 0;
            transform: scale(0.5);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    @keyframes progressFill {
        0% { width: 0%; }
        100% { width: 100%; }
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .logout-container {
            margin: 1rem;
            padding: 2rem 1.5rem;
        }
        
        .logout-title {
            font-size: 2rem;
        }
        
        .logout-icon {
            font-size: 3.5rem;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .session-details {
            font-size: 0.9rem;
        }
    }
</style>

<div class="background-animation">
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>
</div>

<div class="logout-container">
    <div class="logout-icon">
        <i class="bi bi-box-arrow-right"></i>
    </div>
    
    <h1 class="logout-title">Sesión Cerrada</h1>
    <p class="logout-message">Tu sesión ha sido cerrada exitosamente</p>

    <?php if (!empty($usuario_nombre)): ?>
        <div class="session-info">
            <h4>
                <i class="bi bi-person-check"></i>
                Resumen de Sesión
            </h4>
            <div class="session-details">
                <div class="session-detail">
                    <span class="session-detail-label">Usuario:</span>
                    <span class="session-detail-value"><?php echo htmlspecialchars($usuario_nombre); ?></span>
                </div>
                <div class="session-detail">
                    <span class="session-detail-label">Tipo:</span>
                    <span class="session-detail-value"><?php echo ucfirst($usuario_tipo); ?></span>
                </div>
                <?php if (!empty($tiempo_sesion)): ?>
                    <div class="session-detail">
                        <span class="session-detail-label">Duración:</span>
                        <span class="session-detail-value"><?php echo $tiempo_sesion; ?></span>
                    </div>
                <?php endif; ?>
                <div class="session-detail">
                    <span class="session-detail-label">Fecha:</span>
                    <span class="session-detail-value"><?php echo date('d/m/Y H:i:s'); ?></span>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="loading-container">
        <div class="loading-spinner"></div>
        <div class="loading-text">Limpiando datos de sesión...</div>
        <div class="progress-bar">
            <div class="progress-fill"></div>
        </div>
        <div class="countdown" id="countdown">5</div>
    </div>

    <div class="action-buttons">
        <a href="index.php?pagina=login" class="btn btn-primary">
            <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
        </a>
        <button onclick="window.close()" class="btn btn-secondary">
            <i class="bi bi-x-circle"></i> Cerrar Ventana
        </button>
    </div>

    <div class="security-notice">
        <div class="security-notice-title">
            <i class="bi bi-shield-check"></i>
            Recomendación de Seguridad
        </div>
        <p class="security-notice-text">
            Por tu seguridad, cierra completamente el navegador si usas un equipo compartido. 
            Evita dejar tu sesión abierta en computadoras públicas.
        </p>
    </div>
</div>

<script>
    // Countdown automático para redirección
    let countdown = 5;
    const countdownElement = document.getElementById('countdown');
    
    const countdownInterval = setInterval(() => {
        countdown--;
        countdownElement.textContent = countdown;
        
        if (countdown <= 0) {
            clearInterval(countdownInterval);
            window.location.href = 'index.php?pagina=login';
        }
    }, 1000);

    // Actualizar texto del loading
    const loadingTexts = [
        'Limpiando datos de sesión...',
        'Cerrando conexiones...',
        'Liberando recursos...',
        'Finalizando procesos...',
        'Redirigiendo...'
    ];

    let textIndex = 0;
    const loadingTextElement = document.querySelector('.loading-text');
    
    const textInterval = setInterval(() => {
        textIndex = (textIndex + 1) % loadingTexts.length;
        loadingTextElement.textContent = loadingTexts[textIndex];
    }, 600);

    // Limpiar intervalos cuando se vaya a redirigir
    setTimeout(() => {
        clearInterval(textInterval);
        loadingTextElement.textContent = 'Redirigiendo...';
    }, 4000);

    // Prevenir navegación hacia atrás
    window.addEventListener('beforeunload', function(e) {
        // Limpiar cualquier dato sensible del localStorage/sessionStorage
        localStorage.clear();
        sessionStorage.clear();
    });

    // Efecto de partículas al hacer clic
    document.addEventListener('click', function(e) {
        createParticle(e.clientX, e.clientY);
    });

    function createParticle(x, y) {
        const particle = document.createElement('div');
        particle.style.position = 'fixed';
        particle.style.left = x + 'px';
        particle.style.top = y + 'px';
        particle.style.width = '6px';
        particle.style.height = '6px';
        particle.style.background = '#667eea';
        particle.style.borderRadius = '50%';
        particle.style.pointerEvents = 'none';
        particle.style.zIndex = '9999';
        particle.style.transition = 'all 0.8s ease-out';
        
        document.body.appendChild(particle);
        
        setTimeout(() => {
            particle.style.transform = 'translateY(-100px) scale(0)';
            particle.style.opacity = '0';
        }, 50);
        
        setTimeout(() => {
            if (document.body.contains(particle)) {
                document.body.removeChild(particle);
            }
        }, 1000);
    }

    // Limpiar historial de navegación sensible
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }

    // Detección de teclas para navegación rápida
    document.addEventListener('keydown', function(e) {
        switch(e.key) {
            case 'Enter':
                window.location.href = 'index.php?pagina=login';
                break;
            case 'Escape':
                if (window.close) window.close();
                break;
        }
    });

    // Mensaje de despedida personalizado según la hora
    document.addEventListener('DOMContentLoaded', function() {
        const hora = new Date().getHours();
        let mensaje = '';
        
        if (hora >= 5 && hora < 12) {
            mensaje = '¡Que tengas un buen día!';
        } else if (hora >= 12 && hora < 18) {
            mensaje = '¡Que tengas una buena tarde!';
        } else {
            mensaje = '¡Que tengas una buena noche!';
        }
        
        setTimeout(() => {
            const messageElement = document.querySelector('.logout-message');
            messageElement.textContent = mensaje;
        }, 3000);
    });

    // Analytics (si está configurado)
    if (typeof gtag !== 'undefined') {
        gtag('event', 'logout', {
            'event_category': 'user',
            'event_label': '<?php echo $usuario_tipo; ?>',
            'value': 1
        });
    }
</script>