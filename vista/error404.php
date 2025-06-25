<?php
// Obtener la URL que causó el error
$url_solicitada = $_GET['pagina'] ?? 'desconocida';
$usuario_tipo = $_SESSION["tipo_usuario"] ?? 'invitado';
$usuario_nombre = '';

if (isset($_SESSION["nombre"]) && isset($_SESSION["apellido"])) {
    $usuario_nombre = $_SESSION["nombre"] . " " . $_SESSION["apellido"];
}
?>

<title>PÁGINA NO ENCONTRADA - Error 404</title>

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
    }

    .error-container {
        background: white;
        border-radius: 20px;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
        text-align: center;
        padding: 3rem;
        max-width: 600px;
        width: 100%;
        position: relative;
        overflow: hidden;
    }

    .error-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #ff6b6b, #4ecdc4, #45b7d1, #96ceb4);
    }

    .error-icon {
        font-size: 8rem;
        color: #667eea;
        margin-bottom: 1rem;
        animation: bounce 2s infinite;
    }

    .error-code {
        font-size: 6rem;
        font-weight: bold;
        color: #2c3e50;
        margin: 0;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
    }

    .error-title {
        font-size: 2rem;
        color: #34495e;
        margin: 1rem 0;
        font-weight: 600;
    }

    .error-message {
        font-size: 1.1rem;
        color: #7f8c8d;
        margin-bottom: 2rem;
        line-height: 1.6;
    }

    .error-details {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border-left: 4px solid #667eea;
    }

    .error-details h4 {
        color: #2c3e50;
        margin-bottom: 1rem;
        font-size: 1.2rem;
    }

    .error-details p {
        color: #6c757d;
        margin: 0.5rem 0;
        font-size: 0.95rem;
    }

    .error-details strong {
        color: #495057;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
        margin-bottom: 2rem;
    }

    .btn {
        padding: 1rem 2rem;
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
        box-shadow: 0 8px 25px rgba(108, 117, 125, 0.4);
        color: white;
    }

    .btn-outline {
        background: transparent;
        color: #667eea;
        border: 2px solid #667eea;
    }

    .btn-outline:hover {
        background: #667eea;
        color: white;
        transform: translateY(-2px);
    }

    .suggestions {
        background: #e3f2fd;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        text-align: left;
    }

    .suggestions h4 {
        color: #1976d2;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .suggestions ul {
        color: #424242;
        margin: 0;
        padding-left: 1.5rem;
    }

    .suggestions li {
        margin-bottom: 0.5rem;
        line-height: 1.5;
    }

    .suggestions a {
        color: #1976d2;
        text-decoration: none;
        font-weight: 600;
    }

    .suggestions a:hover {
        text-decoration: underline;
    }

    .footer-info {
        border-top: 1px solid #e9ecef;
        padding-top: 1.5rem;
        color: #6c757d;
        font-size: 0.9rem;
    }

    .footer-info p {
        margin: 0.25rem 0;
    }

    .status-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: #d4edda;
        color: #155724;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
        margin: 1rem 0;
    }

    .status-indicator.offline {
        background: #f8d7da;
        color: #721c24;
    }

    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% {
            transform: translateY(0);
        }
        40% {
            transform: translateY(-30px);
        }
        60% {
            transform: translateY(-15px);
        }
    }

    @media (max-width: 768px) {
        .error-container {
            margin: 1rem;
            padding: 2rem 1.5rem;
        }
        
        .error-code {
            font-size: 4rem;
        }
        
        .error-title {
            font-size: 1.5rem;
        }
        
        .error-icon {
            font-size: 5rem;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .btn {
            justify-content: center;
        }
    }

    .animated-bg {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
        opacity: 0.1;
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
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-20px);
        }
    }
</style>

<div class="animated-bg">
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>
</div>

<div class="error-container">
    <div class="error-icon">
        <i class="bi bi-exclamation-triangle"></i>
    </div>
    
    <h1 class="error-code">404</h1>
    <h2 class="error-title">¡Oops! Página no encontrada</h2>
    
    <p class="error-message">
        Lo sentimos, la página que estás buscando no existe o ha sido movida. 
        Verifica la URL o navega a una sección disponible.
    </p>

    <div class="status-indicator">
        <i class="bi bi-wifi"></i>
        <span>Sistema en línea</span>
    </div>

    <div class="error-details">
        <h4><i class="bi bi-info-circle"></i> Detalles del Error</h4>
        <p><strong>Página solicitada:</strong> <?php echo htmlspecialchars($url_solicitada); ?></p>
        <p><strong>Usuario:</strong> <?php echo htmlspecialchars($usuario_nombre ?: $usuario_tipo); ?></p>
        <p><strong>Tipo de usuario:</strong> <?php echo ucfirst($usuario_tipo); ?></p>
        <p><strong>Fecha y hora:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
    </div>

    <div class="suggestions">
        <h4><i class="bi bi-lightbulb"></i> ¿Qué puedes hacer?</h4>
        <ul>
            <li>Verificar que la URL esté escrita correctamente</li>
            <li>Regresar a la página de inicio y navegar desde allí</li>
            <li>Usar el menú de navegación para encontrar lo que buscas</li>
            <?php if ($usuario_tipo == "personal"): ?>
                <li>Acceder a las <a href="index.php?pagina=tabla/clientes">herramientas de gestión</a></li>
                <li>Ver las <a href="index.php?pagina=tabla/orden_trabajo">órdenes de trabajo</a></li>
            <?php elseif ($usuario_tipo == "cliente"): ?>
                <li>Consultar tu <a href="index.php?pagina=tabla/historial">historial de servicios</a></li>
                <li>Agendar una nueva <a href="index.php?pagina=agendamiento">cita</a></li>
            <?php endif; ?>
            <li>Contactar al administrador del sistema si el problema persiste</li>
        </ul>
    </div>

    <div class="action-buttons">
        <a href="index.php?pagina=inicio" class="btn btn-primary">
            <i class="bi bi-house"></i> Ir a Inicio
        </a>
        
        <button onclick="window.history.back()" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver Atrás
        </button>
        
        <?php if ($usuario_tipo == "personal"): ?>
            <a href="index.php?pagina=tabla/clientes" class="btn btn-outline">
                <i class="bi bi-people"></i> Gestión
            </a>
        <?php elseif ($usuario_tipo == "cliente"): ?>
            <a href="index.php?pagina=agendamiento" class="btn btn-outline">
                <i class="bi bi-calendar-plus"></i> Agendar Cita
            </a>
        <?php endif; ?>
    </div>

    <div class="footer-info">
        <p><i class="bi bi-shield-check"></i> <strong>Sistema de Taller - Versión 1.0</strong></p>
        <p>Si necesitas ayuda, contacta al administrador del sistema</p>
        <p><small>Error Code: HTTP 404 - Page Not Found</small></p>
    </div>
</div>

<script>
    // Animación de entrada
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.querySelector('.error-container');
        container.style.opacity = '0';
        container.style.transform = 'translateY(50px)';
        
        setTimeout(() => {
            container.style.transition = 'all 0.8s ease';
            container.style.opacity = '1';
            container.style.transform = 'translateY(0)';
        }, 100);
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
            document.body.removeChild(particle);
        }, 1000);
    }

    // Easter egg: Konami code
    let konamiCode = [];
    const konamiSequence = [
        'ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown',
        'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight',
        'KeyB', 'KeyA'
    ];

    document.addEventListener('keydown', function(e) {
        konamiCode.push(e.code);
        if (konamiCode.length > konamiSequence.length) {
            konamiCode.shift();
        }
        
        if (konamiCode.join('') === konamiSequence.join('')) {
            // Easter egg activado
            document.body.style.animation = 'rainbow 2s infinite';
            
            const style = document.createElement('style');
            style.textContent = `
                @keyframes rainbow {
                    0% { filter: hue-rotate(0deg); }
                    100% { filter: hue-rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
            
            setTimeout(() => {
                document.body.style.animation = '';
                document.head.removeChild(style);
            }, 5000);
            
            konamiCode = [];
        }
    });

    // Registrar el error para estadísticas (opcional)
    if (typeof gtag !== 'undefined') {
        gtag('event', 'page_not_found', {
            'page_title': '404 Error',
            'page_location': window.location.href,
            'requested_page': '<?php echo htmlspecialchars($url_solicitada); ?>'
        });
    }
</script>