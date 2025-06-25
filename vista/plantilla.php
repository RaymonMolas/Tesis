<?php
session_start();

// Obtener información de la empresa
$infoEmpresa = ControladorPlantilla::ctrObtenerInfoEmpresa();

// Verificar permisos de la página solicitada
$paginaActual = $_GET["pagina"] ?? "inicio";

// Si no hay sesión y no es una página pública, redirigir al login
if (!isset($_SESSION["validarIngreso"]) || $_SESSION["validarIngreso"] != "ok") {
    $paginasPublicas = ["login", ""];
    if (!in_array($paginaActual, $paginasPublicas)) {
        echo '<script>window.location = "index.php?pagina=login";</script>';
        return;
    }
}

// Verificar permisos específicos de la página
if (isset($_SESSION["validarIngreso"]) && $_SESSION["validarIngreso"] == "ok") {
    if (!ControladorPlantilla::ctrValidarPermisos($paginaActual)) {
        // Redirigir a página de error o inicio según el tipo de usuario
        echo '<script>window.location = "index.php?pagina=inicio";</script>';
        return;
    }
}

// Registrar tiempo de inicio de sesión si no existe
if (isset($_SESSION["validarIngreso"]) && !isset($_SESSION["tiempo_inicio_sesion"])) {
    $_SESSION["tiempo_inicio_sesion"] = date('Y-m-d H:i:s');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de Gestión de Taller Mecánico - <?php echo htmlspecialchars($infoEmpresa['nombre_empresa']); ?>">
    <meta name="keywords" content="taller, mecánico, gestión, clientes, vehículos, órdenes, facturación">
    <meta name="author" content="<?php echo htmlspecialchars($infoEmpresa['nombre_empresa']); ?>">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Font Awesome (opcional) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Estilos personalizados globales -->
    <style>
        :root {
            --primary-color: #667eea;
            --primary-dark: #764ba2;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            --border-radius: 10px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-family);
            background-color: #f8f9fa;
            line-height: 1.6;
            color: #333;
        }

        /* Navbar personalizado */
        .navbar-custom {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            box-shadow: var(--box-shadow);
            padding: 0.5rem 0;
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
            color: white !important;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .navbar-brand:hover {
            color: #f8f9fa !important;
        }

        .company-logo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            border-radius: var(--border-radius);
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-link:hover {
            color: white !important;
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: white !important;
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border-radius: var(--border-radius);
            margin-top: 0.5rem;
        }

        .dropdown-item {
            padding: 0.75rem 1rem;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .dropdown-item:hover {
            background: var(--primary-color);
            color: white;
            transform: translateX(5px);
        }

        /* Usuario info en navbar */
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: white;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .user-details {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.9rem;
        }

        .user-role {
            font-size: 0.75rem;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Contenido principal */
        .main-content {
            min-height: calc(100vh - 76px);
            padding: 0;
        }

        /* Notificaciones */
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger-color);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        /* Scrollbar personalizado */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }

        /* Utilidades */
        .text-primary-custom {
            color: var(--primary-color) !important;
        }

        .bg-primary-custom {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%) !important;
        }

        .border-primary-custom {
            border-color: var(--primary-color) !important;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.5rem 1.5rem;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            color: white;
        }

        /* Loading overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
        }

        .loading-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar-nav {
                padding: 1rem 0;
            }
            
            .user-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
                margin-top: 1rem;
                padding-top: 1rem;
                border-top: 1px solid rgba(255, 255, 255, 0.2);
            }
            
            .user-details {
                align-items: flex-start;
            }
        }

        /* Transiciones de página */
        .page-transition {
            animation: fadeInUp 0.5s ease-out;
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

        /* Estilos para impresión */
        @media print {
            .navbar, .no-print {
                display: none !important;
            }
            
            body {
                background: white;
            }
            
            .main-content {
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>

<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <!-- SVG Icons -->
    <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
        <symbol id="home" viewBox="0 0 16 16">
            <path d="m8 3.293 6 6V13.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 13.5V9.293l6-6zm5-.793V6l-2-2V2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5z"/>
            <path d="M7.293 1.5a1 1 0 0 1 1.414 0l6.647 6.646a.5.5 0 0 1-.708.708L8 2.207 1.354 8.854a.5.5 0 1 1-.708-.708L7.293 1.5z"/>
        </symbol>
        <symbol id="people" viewBox="0 0 16 16">
            <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816zM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275zM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
        </symbol>
        <symbol id="gear" viewBox="0 0 16 16">
            <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492zM5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0z"/>
            <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52l-.094-.319zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115l.094-.319z"/>
        </symbol>
        <symbol id="calendar" viewBox="0 0 16 16">
            <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/>
        </symbol>
    </svg>

    <?php if (isset($_SESSION["validarIngreso"]) && $_SESSION["validarIngreso"] == "ok"): ?>
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
            <div class="container-fluid">
                <!-- Brand -->
                <a class="navbar-brand" href="index.php?pagina=inicio">
                    <div class="company-logo">
                        <?php if (!empty($infoEmpresa['logo'])): ?>
                            <img src="<?php echo $infoEmpresa['logo']; ?>" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        <?php else: ?>
                            <i class="bi bi-gear-fill"></i>
                        <?php endif; ?>
                    </div>
                    <span class="d-none d-md-inline"><?php echo htmlspecialchars($infoEmpresa['nombre_empresa']); ?></span>
                </a>

                <!-- Toggle button for mobile -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" style="border: 1px solid rgba(255,255,255,0.3);">
                    <i class="bi bi-list" style="color: white; font-size: 1.5rem;"></i>
                </button>

                <!-- Navigation Menu -->
                <div class="collapse navbar-collapse" id="navbarNav">
                    <?php if ($_SESSION["tipo_usuario"] == "personal"): ?>
                        <!-- Menú para Personal -->
                        <ul class="navbar-nav me-auto">
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($paginaActual == 'inicio') ? 'active' : ''; ?>" href="index.php?pagina=inicio">
                                    <i class="bi bi-house"></i> Inicio
                                </a>
                            </li>
                            
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-calendar-event"></i> Agendamiento
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="index.php?pagina=agendamiento">
                                        <i class="bi bi-calendar-plus"></i> Gestionar Citas
                                    </a></li>
                                    <li><a class="dropdown-item" href="index.php?pagina=tabla/historicocitas">
                                        <i class="bi bi-clock-history"></i> Historial de Citas
                                    </a></li>
                                </ul>
                            </li>

                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-gear"></i> Gestión
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="index.php?pagina=tabla/clientes">
                                        <i class="bi bi-people"></i> Clientes
                                    </a></li>
                                    <li><a class="dropdown-item" href="index.php?pagina=tabla/vehiculos">
                                        <i class="bi bi-car-front"></i> Vehículos
                                    </a></li>
                                    <li><a class="dropdown-item" href="index.php?pagina=tabla/personales">
                                        <i class="bi bi-person-badge"></i> Personal
                                    </a></li>
                                    <li><a class="dropdown-item" href="index.php?pagina=tabla/productos">
                                        <i class="bi bi-box"></i> Productos
                                    </a></li>
                                    <li><a class="dropdown-item" href="index.php?pagina=tabla/usuarios">
                                        <i class="bi bi-person-gear"></i> Usuarios
                                    </a></li>
                                </ul>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link <?php echo ($paginaActual == 'tabla/presupuestos') ? 'active' : ''; ?>" href="index.php?pagina=tabla/presupuestos">
                                    <i class="bi bi-file-earmark-text"></i> Presupuestos
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link <?php echo ($paginaActual == 'tabla/orden_trabajo') ? 'active' : ''; ?>" href="index.php?pagina=tabla/orden_trabajo">
                                    <i class="bi bi-tools"></i> Órdenes
                                </a>
                            </li>

                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-receipt"></i> Facturación
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="index.php?pagina=tabla/facturas">
                                        <i class="bi bi-receipt-cutoff"></i> Facturas
                                    </a></li>
                                    <li><a class="dropdown-item" href="index.php?pagina=nuevo/factura">
                                        <i class="bi bi-plus-circle"></i> Nueva Factura
                                    </a></li>
                                </ul>
                            </li>
                        </ul>

                        <!-- Notificaciones para Personal -->
                        <ul class="navbar-nav">
                            <li class="nav-item position-relative">
                                <a class="nav-link" href="index.php?pagina=agendamiento" title="Citas pendientes">
                                    <i class="bi bi-bell"></i>
                                    <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
                                </a>
                            </li>
                        </ul>

                    <?php else: ?>
                        <!-- Menú para Cliente -->
                        <ul class="navbar-nav me-auto">
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($paginaActual == 'inicio') ? 'active' : ''; ?>" href="index.php?pagina=inicio">
                                    <i class="bi bi-house"></i> Mi Panel
                                </a>
                            </li>
                            
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($paginaActual == 'agendamiento') ? 'active' : ''; ?>" href="index.php?pagina=agendamiento">
                                    <i class="bi bi-calendar-plus"></i> Agendar Cita
                                </a>
                            </li>
                            
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($paginaActual == 'tabla/historial') ? 'active' : ''; ?>" href="index.php?pagina=tabla/historial">
                                    <i class="bi bi-clock-history"></i> Mi Historial
                                </a>
                            </li>
                        </ul>
                    <?php endif; ?>

                    <!-- Usuario info -->
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($_SESSION["nombre"], 0, 1) . substr($_SESSION["apellido"], 0, 1)); ?>
                        </div>
                        <div class="user-details">
                            <div class="user-name"><?php echo htmlspecialchars($_SESSION["nombre"] . " " . $_SESSION["apellido"]); ?></div>
                            <div class="user-role"><?php echo ucfirst($_SESSION["tipo_usuario"]); ?></div>
                        </div>
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-gear"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#">
                                    <i class="bi bi-person"></i> Mi Perfil
                                </a></li>
                                <li><a class="dropdown-item" href="#">
                                    <i class="bi bi-gear"></i> Configuración
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="index.php?pagina=salir">
                                    <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Espaciado para navbar fijo -->
        <div style="height: 76px;"></div>
    <?php endif; ?>

    <!-- Contenido Principal -->
    <main class="main-content">
        <div class="page-transition">
            <?php
            // Incluir la página solicitada
            if (isset($_GET["pagina"])) {
                $paginasPermitidas = [
                    "inicio", "login", "obtener_vehiculo", "agendamiento", "marcar_leidas",
                    "tabla/clientes", "tabla/usuarios", "tabla/personales", "tabla/productos", 
                    "tabla/vehiculos", "tabla/presupuestos", "tabla/facturas", "tabla/orden_trabajo", 
                    "tabla/historicocitas", "tabla/historial",
                    "nuevo/cliente", "nuevo/usuario", "nuevo/personal", "nuevo/producto", 
                    "nuevo/vehiculo", "nuevo/presupuesto", "nuevo/factura", "nuevo/orden_trabajo",
                    "editar/cliente", "editar/usuario", "editar/personal", "editar/producto", 
                    "editar/vehiculo", "editar/presupuesto", "editar/orden_trabajo",
                    "ver/presupuesto", "ver/factura", "ver/orden_trabajo",
                    "salir"
                ];

                if (in_array($_GET["pagina"], $paginasPermitidas)) {
                    include $_GET["pagina"] . ".php";
                } else {
                    include "error404.php";
                }
            } else {
                // Página por defecto
                if (isset($_SESSION["validarIngreso"]) && $_SESSION["validarIngreso"] == "ok") {
                    include "inicio.php";
                } else {
                    include "login.php";
                }
            }
            ?>
        </div>
    </main>

    <!-- Scripts -->
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Scripts personalizados -->
    <script>
        // Variables globales
        window.sistemaConfig = {
            baseUrl: '<?php echo $_SERVER['PHP_SELF']; ?>',
            tipoUsuario: '<?php echo $_SESSION["tipo_usuario"] ?? ""; ?>',
            nombreUsuario: '<?php echo htmlspecialchars($_SESSION["nombre"] ?? ""); ?>',
            formatoFecha: 'dd/mm/yyyy',
            formatoHora: 'HH:mm',
            moneda: '₲'
        };

        // Función para mostrar loading
        function showLoading() {
            document.getElementById('loadingOverlay').classList.add('active');
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').classList.remove('active');
        }

        // Mostrar loading en navegación
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (link && link.href && !link.href.includes('#') && !link.href.includes('javascript:')) {
                if (!link.target || link.target === '_self') {
                    showLoading();
                    
                    // Ocultar loading después de 5 segundos por seguridad
                    setTimeout(hideLoading, 5000);
                }
            }
        });

        // Ocultar loading cuando la página carga
        window.addEventListener('load', function() {
            hideLoading();
        });

        // Actualizar notificaciones para personal
        <?php if (isset($_SESSION["tipo_usuario"]) && $_SESSION["tipo_usuario"] == "personal"): ?>
            function actualizarNotificaciones() {
                fetch('index.php?pagina=obtener_notificaciones')
                    .then(response => response.json())
                    .then(data => {
                        const badge = document.getElementById('notificationBadge');
                        if (data.total > 0) {
                            badge.textContent = data.total;
                            badge.style.display = 'flex';
                            
                            // Mostrar toast si hay nuevas notificaciones
                            const prevTotal = localStorage.getItem('prevNotificaciones') || 0;
                            if (data.total > prevTotal) {
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        title: 'Nuevas notificaciones',
                                        text: `Tienes ${data.total} cita(s) pendiente(s)`,
                                        icon: 'info',
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 5000
                                    });
                                }
                            }
                            localStorage.setItem('prevNotificaciones', data.total);
                        } else {
                            badge.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        console.log('Error al obtener notificaciones:', error);
                    });
            }

            // Actualizar notificaciones cada 30 segundos
            actualizarNotificaciones();
            setInterval(actualizarNotificaciones, 30000);
        <?php endif; ?>

        // Mantener sesión activa
        function mantenerSesionActiva() {
            fetch('index.php?pagina=ping_sesion', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'ping=1'
            }).catch(error => {
                console.log('Error manteniendo sesión:', error);
            });
        }

        // Ping cada 5 minutos para mantener la sesión
        <?php if (isset($_SESSION["validarIngreso"])): ?>
            setInterval(mantenerSesionActiva, 300000); // 5 minutos
        <?php endif; ?>

        // Funciones utilitarias globales
        window.formatearFecha = function(fecha) {
            const d = new Date(fecha);
            const dia = String(d.getDate()).padStart(2, '0');
            const mes = String(d.getMonth() + 1).padStart(2, '0');
            const año = d.getFullYear();
            return `${dia}/${mes}/${año}`;
        };

        window.formatearMoneda = function(valor) {
            return '₲ ' + new Intl.NumberFormat('es-PY').format(valor);
        };

        window.confirmarAccion = function(mensaje, callback) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: mensaje,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#667eea',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, continuar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed && typeof callback === 'function') {
                        callback();
                    }
                });
            } else {
                if (confirm(mensaje) && typeof callback === 'function') {
                    callback();
                }
            }
        };

        // Manejo de errores globales
        window.addEventListener('error', function(e) {
            console.error('Error JavaScript:', e.error);
        });

        // Mensaje de desarrollo (remover en producción)
        console.log('%c🔧 Sistema de Taller v1.0', 'color: #667eea; font-size: 16px; font-weight: bold;');
        console.log('%cDesarrollado para la gestión eficiente de talleres mecánicos', 'color: #6c757d;');
    </script>

    <!-- Scripts específicos de página -->
    <?php
    // Incluir scripts específicos según la página
    $scriptsPagina = [
        'tabla/clientes' => 'clientes.js',
        'tabla/vehiculos' => 'vehiculos.js',
        'tabla/presupuestos' => 'presupuestos.js',
        'tabla/facturas' => 'facturas.js',
        'agendamiento' => 'agendamiento.js',
        'inicio' => 'dashboard.js'
    ];

    if (isset($_GET["pagina"]) && array_key_exists($_GET["pagina"], $scriptsPagina)) {
        $scriptFile = "../js/" . $scriptsPagina[$_GET["pagina"]];
        if (file_exists($scriptFile)) {
            echo "<script src='$scriptFile'></script>";
        }
    }
    ?>
</body>
</html>