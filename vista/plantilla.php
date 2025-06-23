<?php
// Procesar acciones de citas si vienen por POST
if (isset($_POST["accion_cita"])) {
    if ($_POST["accion_cita"] == "aprobar" && isset($_POST["id_cita"])) {
        $id_cita = $_POST["id_cita"];
        $resultado = ControladorAgendamiento::aprobarCita($id_cita);

        if ($resultado) {
            $_SESSION["mensajeJS"] = "Swal.fire({
				icon: 'success',
				title: 'Cita aprobada',
				text: '✅ La cita fue aprobada correctamente.',
				confirmButtonText: 'Aceptar'
			});";
        } else {
            $_SESSION["mensajeJS"] = "Swal.fire({
				icon: 'warning',
				title: 'Error al aprobar',
				text: '⚠️ Ocurrió un error al aprobar la cita.',
				confirmButtonText: 'Cerrar'
			});";
        }

        header("Location: " . $_SERVER["REQUEST_URI"]);
        exit;
    }

    if ($_POST["accion_cita"] == "rechazar" && isset($_POST["id_cita"])) {
        $id_cita = $_POST["id_cita"];
        $resultado = ControladorAgendamiento::rechazarCita($id_cita);

        if ($resultado) {
            $_SESSION["mensajeJS"] = "Swal.fire({
				icon: 'success',
				title: 'Cita rechazada',
				text: '✅ La cita fue rechazada correctamente.',
				confirmButtonText: 'Aceptar'
			});";
        } else {
            $_SESSION["mensajeJS"] = "Swal.fire({
				icon: 'warning',
				title: 'Error al rechazar',
				text: '⚠️ Ocurrió un error al rechazar la cita.',
				confirmButtonText: 'Cerrar'
			});";
        }

        header("Location: " . $_SERVER["REQUEST_URI"]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de Gestión de Taller Automotriz - Motor Service">
    <meta name="author" content="Motor Service">

    <title>Motor Service - Sistema de Gestión</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Iconos Lucide -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <!-- Chart.js para gráficos -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.tailwindcss.min.css">
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.tailwindcss.min.js"></script>

    <!-- Configuración personalizada de Tailwind -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'motor-red': {
                            50: '#fef2f2',
                            100: '#fee2e2',
                            500: '#ef4444',
                            600: '#dc2626',
                            700: '#b91c1c',
                            800: '#991b1b',
                            900: '#7f1d1d'
                        }
                    },
                    fontFamily: {
                        'display': ['Inter', 'system-ui', 'sans-serif'],
                        'body': ['Inter', 'system-ui', 'sans-serif']
                    }
                }
            }
        }
    </script>

    <!-- Estilos personalizados -->
    <style>
        /* Animaciones personalizadas */
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideInLeft {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeInUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .animate-slide-in-right {
            animation: slideInRight 0.3s ease-out;
        }

        .animate-slide-in-left {
            animation: slideInLeft 0.3s ease-out;
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.5s ease-out;
        }

        /* Scrollbar personalizado */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #dc2626;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #b91c1c;
        }

        /* Loader */
        .loader {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #dc2626;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* DataTables personalización */
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            @apply border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-motor-red-500 focus:border-transparent;
        }
    </style>
</head>

<body class="bg-gray-50 font-body">
    <!-- Loader global -->
    <div id="globalLoader"
        class="fixed top-0 left-0 w-full h-full bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
            <div class="loader"></div>
            <span class="text-gray-700">Cargando...</span>
        </div>
    </div>

    <!-- Contenedor principal -->
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside id="sidebar"
            class="bg-gray-800 text-white w-64 min-h-screen transform transition-transform duration-300 ease-in-out lg:translate-x-0 -translate-x-full fixed lg:static z-30">
            <!-- Logo -->
            <div class="flex items-center justify-center h-16 bg-motor-red-600 border-b border-gray-700">
                <img src="../img/img-01.jpg" alt="Motor Service" class="w-8 h-8 rounded-full mr-2">
                <span class="text-xl font-bold">Motor Service</span>
            </div>

            <!-- Navegación -->
            <nav class="mt-8">
                <?php if (isset($_SESSION["validarIngreso"]) && $_SESSION["validarIngreso"] == "ok"): ?>

                    <!-- Menú Personal -->
                    <?php if ($_SESSION["tipo_usuario"] == "personal"): ?>

                        <!-- Dashboard -->
                        <a href="index.php?pagina=inicio"
                            class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200">
                            <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>
                            Dashboard
                        </a>

                        <!-- Agendamiento -->
                        <div class="px-6 py-2">
                            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Agendamiento</h3>
                        </div>
                        <a href="index.php?pagina=tabla/agendamiento"
                            class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200">
                            <i data-lucide="calendar" class="w-5 h-5 mr-3"></i>
                            Citas
                        </a>
                        <a href="index.php?pagina=tabla/historicocitas"
                            class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200">
                            <i data-lucide="history" class="w-5 h-5 mr-3"></i>
                            Historial de Citas
                        </a>

                        <!-- Gestión -->
                        <div class="px-6 py-2 mt-4">
                            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Gestión</h3>
                        </div>
                        <a href="index.php?pagina=tabla/clientes"
                            class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200">
                            <i data-lucide="users" class="w-5 h-5 mr-3"></i>
                            Clientes
                        </a>
                        <a href="index.php?pagina=tabla/vehiculos"
                            class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200">
                            <i data-lucide="car" class="w-5 h-5 mr-3"></i>
                            Vehículos
                        </a>
                        <a href="index.php?pagina=tabla/personal"
                            class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200">
                            <i data-lucide="user-check" class="w-5 h-5 mr-3"></i>
                            Personal
                        </a>
                        <a href="index.php?pagina=tabla/usuarios"
                            class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200">
                            <i data-lucide="user-cog" class="w-5 h-5 mr-3"></i>
                            Usuarios
                        </a>

                        <!-- Servicios -->
                        <div class="px-6 py-2 mt-4">
                            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Servicios</h3>
                        </div>
                        <a href="index.php?pagina=tabla/orden_trabajo"
                            class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200">
                            <i data-lucide="wrench" class="w-5 h-5 mr-3"></i>
                            Órdenes de Trabajo
                        </a>
                        <a href="index.php?pagina=tabla/presupuestos"
                            class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200">
                            <i data-lucide="calculator" class="w-5 h-5 mr-3"></i>
                            Presupuestos
                        </a>
                        <a href="index.php?pagina=tabla/productos"
                            class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200">
                            <i data-lucide="package" class="w-5 h-5 mr-3"></i>
                            Productos
                        </a>

                        <!-- Facturación -->
                        <div class="px-6 py-2 mt-4">
                            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Facturación</h3>
                        </div>
                        <a href="index.php?pagina=tabla/facturas"
                            class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200">
                            <i data-lucide="file-text" class="w-5 h-5 mr-3"></i>
                            Facturas
                        </a>

                    <?php else: ?>
                        <!-- Menú Cliente -->
                        <a href="index.php?pagina=inicio"
                            class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200">
                            <i data-lucide="home" class="w-5 h-5 mr-3"></i>
                            Inicio
                        </a>
                        <a href="index.php?pagina=nuevo/agendamiento"
                            class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200">
                            <i data-lucide="calendar-plus" class="w-5 h-5 mr-3"></i>
                            Agendar Cita
                        </a>
                        <a href="index.php?pagina=tabla/agendamiento_cliente"
                            class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200">
                            <i data-lucide="calendar" class="w-5 h-5 mr-3"></i>
                            Mis Citas
                        </a>
                        <a href="index.php?pagina=tabla/vehiculos_cliente"
                            class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200">
                            <i data-lucide="car" class="w-5 h-5 mr-3"></i>
                            Mis Vehículos
                        </a>
                    <?php endif; ?>

                <?php endif; ?>
            </nav>

            <!-- Usuario y logout (solo si está logueado) -->
            <?php if (isset($_SESSION["validarIngreso"]) && $_SESSION["validarIngreso"] == "ok"): ?>
                <div class="absolute bottom-0 w-64 p-4 bg-gray-900 border-t border-gray-700">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-motor-red-600 rounded-full flex items-center justify-center">
                            <i data-lucide="user" class="w-4 h-4"></i>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-white"><?php echo $_SESSION["usuario"]; ?></p>
                            <p class="text-xs text-gray-400"><?php echo ucfirst($_SESSION["tipo_usuario"]); ?></p>
                        </div>
                        <a href="index.php?pagina=salir"
                            class="text-gray-400 hover:text-white transition-colors duration-200" title="Cerrar sesión">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </aside>

        <!-- Overlay para móviles -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-20 lg:hidden hidden"></div>

        <!-- Contenido principal -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white shadow-lg border-b border-gray-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center py-4">
                        <!-- Botón menú móvil y título -->
                        <div class="flex items-center">
                            <button id="mobile-menu-btn"
                                class="lg:hidden p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-motor-red-500">
                                <i data-lucide="menu" class="w-6 h-6"></i>
                            </button>

                            <?php if (!isset($_SESSION["validarIngreso"]) || $_SESSION["validarIngreso"] != "ok"): ?>
                                <div class="flex items-center ml-4 lg:ml-0">
                                    <img src="../img/img-01.jpg" alt="Motor Service" class="w-10 h-10 rounded-full mr-3">
                                    <div>
                                        <h1 class="text-xl font-bold text-gray-900">Motor Service</h1>
                                        <p class="text-sm text-gray-600">Servicio Integral Automotriz</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Botones de header -->
                        <?php if (isset($_SESSION["validarIngreso"]) && $_SESSION["validarIngreso"] == "ok"): ?>
                            <div class="flex items-center space-x-4">
                                <!-- Notificaciones -->
                                <div class="relative">
                                    <button
                                        class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-full transition-colors duration-200">
                                        <i data-lucide="bell" class="w-5 h-5"></i>
                                    </button>
                                    <span
                                        class="absolute -top-1 -right-1 bg-motor-red-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">3</span>
                                </div>

                                <!-- Configuración -->
                                <button
                                    class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-full transition-colors duration-200">
                                    <i data-lucide="settings" class="w-5 h-5"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </header>

            <!-- Contenido de la página -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <!-- Aquí se carga el contenido de cada página -->
                    <?php
                    if (isset($_GET["pagina"])) {
                        if (
                            $_GET["pagina"] == "inicio" ||
                            $_GET["pagina"] == "login" ||
                            $_GET["pagina"] == "salir" ||
                            $_GET["pagina"] == "nuevo/cliente" ||
                            $_GET["pagina"] == "editar/cliente" ||
                            $_GET["pagina"] == "tabla/clientes" ||
                            $_GET["pagina"] == "nuevo/agendamiento" ||
                            $_GET["pagina"] == "tabla/agendamiento" ||
                            $_GET["pagina"] == "tabla/agendamiento_cliente" ||
                            $_GET["pagina"] == "tabla/historicocitas" ||
                            $_GET["pagina"] == "nuevo/personal" ||
                            $_GET["pagina"] == "editar/personal" ||
                            $_GET["pagina"] == "tabla/personal" ||
                            $_GET["pagina"] == "nuevo/usuario" ||
                            $_GET["pagina"] == "editar/usuario" ||
                            $_GET["pagina"] == "tabla/usuarios" ||
                            $_GET["pagina"] == "nuevo/producto" ||
                            $_GET["pagina"] == "editar/producto" ||
                            $_GET["pagina"] == "tabla/productos" ||
                            $_GET["pagina"] == "nuevo/orden_trabajo" ||
                            $_GET["pagina"] == "editar/orden_trabajo" ||
                            $_GET["pagina"] == "ver/orden_trabajo" ||
                            $_GET["pagina"] == "tabla/orden_trabajo" ||
                            $_GET["pagina"] == "nuevo/vehiculo" ||
                            $_GET["pagina"] == "editar/vehiculo" ||
                            $_GET["pagina"] == "tabla/vehiculos" ||
                            $_GET["pagina"] == "tabla/vehiculos_cliente" ||
                            $_GET["pagina"] == "nuevo/presupuesto" ||
                            $_GET["pagina"] == "editar/presupuesto" ||
                            $_GET["pagina"] == "ver/presupuesto" ||
                            $_GET["pagina"] == "tabla/presupuestos" ||
                            $_GET["pagina"] == "nuevo/factura" ||
                            $_GET["pagina"] == "ver/factura" ||
                            $_GET["pagina"] == "tabla/facturas"
                        ) {

                            include "vista/" . $_GET["pagina"] . ".php";
                        } else {
                            include "vista/404.php";
                        }
                    } else {
                        if (!isset($_SESSION["validarIngreso"]) || $_SESSION["validarIngreso"] != "ok") {
                            include "vista/login.php";
                        } else {
                            include "vista/inicio.php";
                        }
                    }
                    ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Inicializar iconos Lucide
        lucide.createIcons();

        // Menú móvil
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');

        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
                sidebarOverlay.classList.toggle('hidden');
            });
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', () => {
                sidebar.classList.add('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
            });
        }

        // Funciones globales
        window.showLoader = () => {
            document.getElementById('globalLoader').classList.remove('hidden');
        };

        window.hideLoader = () => {
            document.getElementById('globalLoader').classList.add('hidden');
        };

        // Auto-ocultar alerts después de 5 segundos
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert-dismissible');
                alerts.forEach(alert => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                });
            }, 5000);
        });

        // Configurar DataTables por defecto
        $.extend(true, $.fn.dataTable.defaults, {
            language: {
                "decimal": "",
                "emptyTable": "No hay información disponible",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ entradas",
                "infoEmpty": "Mostrando 0 a 0 de 0 entradas",
                "infoFiltered": "(filtrado de _MAX_ entradas totales)",
                "lengthMenu": "Mostrar _MENU_ entradas",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "No se encontraron registros coincidentes",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            responsive: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
            dom: '<"flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4"<"mb-2 sm:mb-0"l><"mb-2 sm:mb-0"f>>rtip',
            initComplete: function () {
                // Personalizar estilos después de inicializar
                $('.dataTables_length label, .dataTables_filter label').addClass('text-sm text-gray-700');
                $('.dataTables_length select').addClass('ml-2 border border-gray-300 rounded px-2 py-1');
                $('.dataTables_filter input').addClass('ml-2 border border-gray-300 rounded px-3 py-1 focus:ring-2 focus:ring-motor-red-500');
                $('.dataTables_info').addClass('text-sm text-gray-700');
                $('.dataTables_paginate').addClass('mt-4');
            }
        });
    </script>

    <!-- Mensajes JavaScript de sesión -->
    <?php if (isset($_SESSION["mensajeJS"])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                <?php echo $_SESSION["mensajeJS"]; ?>
            });
        </script>
        <?php unset($_SESSION["mensajeJS"]); ?>
    <?php endif; ?>
</body>

</html>