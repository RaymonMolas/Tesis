<?php
/**
 * MÓDULO DE CIERRE DE SESIÓN - MOTOR SERVICE
 * 
 * Este archivo maneja el cierre seguro de sesión del usuario
 * y la limpieza de todas las variables de sesión.
 */

// Verificar si hay una sesión activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log del cierre de sesión para auditoría
if (isset($_SESSION["validarIngreso"]) && $_SESSION["validarIngreso"] == "ok") {
    $usuario = $_SESSION["usuario"] ?? 'Usuario desconocido';
    $tipo_usuario = $_SESSION["tipo_usuario"] ?? 'Tipo desconocido';
    $fecha_logout = date('Y-m-d H:i:s');
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'IP desconocida';

    // Aquí puedes agregar logging a base de datos si lo deseas
    error_log("LOGOUT - Usuario: {$usuario}, Tipo: {$tipo_usuario}, IP: {$ip_address}, Fecha: {$fecha_logout}");
}

// Limpiar todas las variables de sesión
$_SESSION = array();

// Si se usa una cookie de sesión, eliminarla
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destruir la sesión completamente
session_destroy();
?>

<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cerrando Sesión - Motor Service</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Iconos Lucide -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
                    }
                }
            }
        }
    </script>

    <style>
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

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        .animate-pulse-custom {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #fee2e2 0%, #fef2f2 50%, #ffffff 100%);
        }
    </style>
</head>

<body class="h-full gradient-bg">
    <div class="min-h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Contenedor principal -->
            <div class="bg-white rounded-xl shadow-2xl p-8 border border-gray-200 animate-fade-in-up">
                <!-- Logo y encabezado -->
                <div class="text-center mb-8">
                    <div class="mx-auto h-20 w-20 bg-motor-red-100 rounded-full flex items-center justify-center mb-4">
                        <img src="../img/img-01.jpg" alt="Motor Service"
                            class="w-16 h-16 rounded-full border-2 border-motor-red-200">
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Motor Service</h2>
                    <p class="text-sm text-gray-600">Servicio Integral Automotriz</p>
                </div>

                <!-- Icono de cierre de sesión -->
                <div class="text-center mb-6">
                    <div
                        class="mx-auto h-16 w-16 bg-green-100 rounded-full flex items-center justify-center mb-4 animate-pulse-custom">
                        <i data-lucide="log-out" class="h-8 w-8 text-green-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Sesión Cerrada</h3>
                    <p class="text-gray-600">Tu sesión ha sido cerrada de forma segura</p>
                </div>

                <!-- Información de seguridad -->
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i data-lucide="shield-check" class="h-5 w-5 text-green-400"></i>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-green-800">Sesión Segura</h4>
                            <div class="mt-1 text-sm text-green-700">
                                <ul class="space-y-1">
                                    <li class="flex items-center">
                                        <i data-lucide="check" class="h-3 w-3 mr-2"></i>
                                        Datos de sesión eliminados
                                    </li>
                                    <li class="flex items-center">
                                        <i data-lucide="check" class="h-3 w-3 mr-2"></i>
                                        Cookies de seguridad limpiadas
                                    </li>
                                    <li class="flex items-center">
                                        <i data-lucide="check" class="h-3 w-3 mr-2"></i>
                                        Historial de navegación protegido
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recomendaciones de seguridad -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i data-lucide="info" class="h-5 w-5 text-blue-400"></i>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-blue-800">Recomendaciones</h4>
                            <div class="mt-1 text-sm text-blue-700">
                                <ul class="space-y-1">
                                    <li class="flex items-start">
                                        <i data-lucide="dot" class="h-3 w-3 mr-2 mt-1 flex-shrink-0"></i>
                                        <span>Cierra tu navegador si usas un equipo compartido</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i data-lucide="dot" class="h-3 w-3 mr-2 mt-1 flex-shrink-0"></i>
                                        <span>No dejes tu equipo desatendido</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i data-lucide="dot" class="h-3 w-3 mr-2 mt-1 flex-shrink-0"></i>
                                        <span>Usa contraseñas seguras y únicas</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas de la sesión -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Información de la Sesión</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Hora de cierre:</span>
                            <p class="font-medium text-gray-900" id="logout-time"></p>
                        </div>
                        <div>
                            <span class="text-gray-500">Duración:</span>
                            <p class="font-medium text-gray-900" id="session-duration">Calculando...</p>
                        </div>
                        <div class="col-span-2">
                            <span class="text-gray-500">IP Address:</span>
                            <p class="font-medium text-gray-900">
                                <?php echo $_SERVER['REMOTE_ADDR'] ?? 'No disponible'; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="space-y-3">
                    <button onclick="redirigirLogin()"
                        class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-motor-red-600 hover:bg-motor-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-motor-red-500 transition-colors duration-200">
                        <i data-lucide="log-in" class="h-4 w-4 mr-2"></i>
                        Iniciar Nueva Sesión
                    </button>

                    <button onclick="cerrarVentana()"
                        class="w-full flex justify-center items-center py-3 px-4 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-motor-red-500 transition-colors duration-200">
                        <i data-lucide="x" class="h-4 w-4 mr-2"></i>
                        Cerrar Navegador
                    </button>
                </div>

                <!-- Enlaces adicionales -->
                <div class="mt-6 text-center border-t border-gray-200 pt-6">
                    <div class="space-y-2">
                        <a href="mailto:soporte@motorservicepy.com"
                            class="text-sm text-motor-red-600 hover:text-motor-red-500 transition-colors duration-200">
                            <i data-lucide="mail" class="h-4 w-4 inline mr-1"></i>
                            ¿Problemas? Contactar Soporte
                        </a>
                        <br>
                        <a href="#" onclick="mostrarAyuda()"
                            class="text-sm text-gray-500 hover:text-gray-700 transition-colors duration-200">
                            <i data-lucide="help-circle" class="h-4 w-4 inline mr-1"></i>
                            Ayuda y Documentación
                        </a>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center">
                <p class="text-xs text-gray-500">
                    © 2024 Motor Service. Todos los derechos reservados.
                </p>
                <p class="text-xs text-gray-400 mt-1">
                    Sistema de Gestión de Taller Automotriz v2.0
                </p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Inicializar iconos
            lucide.createIcons();

            // Mostrar hora actual de logout
            const now = new Date();
            document.getElementById('logout-time').textContent = now.toLocaleTimeString('es-PY');

            // Calcular duración de sesión si hay datos en localStorage
            // (esto sería mejor manejarlo desde el servidor, pero por simplicidad lo hago en JS)
            const sessionStart = localStorage.getItem('session_start_time');
            if (sessionStart) {
                const startTime = new Date(sessionStart);
                const endTime = now;
                const duration = endTime - startTime;

                const hours = Math.floor(duration / (1000 * 60 * 60));
                const minutes = Math.floor((duration % (1000 * 60 * 60)) / (1000 * 60));

                document.getElementById('session-duration').textContent =
                    `${hours}h ${minutes}m`;
            } else {
                document.getElementById('session-duration').textContent = 'No disponible';
            }

            // Limpiar localStorage relacionado con la sesión
            localStorage.removeItem('session_start_time');
            localStorage.removeItem('user_preferences');

            // Limpiar sessionStorage
            sessionStorage.clear();

            // Prevenir navegación hacia atrás
            history.pushState(null, null, location.href);
            window.addEventListener('popstate', function () {
                history.pushState(null, null, location.href);
            });

            // Auto-redirect después de 30 segundos
            setTimeout(() => {
                if (confirm('¿Deseas ser redirigido automáticamente al login?')) {
                    redirigirLogin();
                }
            }, 30000);

            // Mostrar mensaje de confirmación
            setTimeout(() => {
                Swal.fire({
                    title: '¡Sesión cerrada exitosamente!',
                    text: 'Gracias por usar Motor Service',
                    icon: 'success',
                    timer: 3000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            }, 500);
        });

        function redirigirLogin() {
            // Limpiar cualquier dato residual
            if ('caches' in window) {
                caches.keys().then(names => {
                    names.forEach(name => {
                        caches.delete(name);
                    });
                });
            }

            // Redirigir al login
            window.location.href = 'index.php?pagina=login';
        }

        function cerrarVentana() {
            Swal.fire({
                title: '¿Cerrar navegador?',
                text: 'Se cerrará la ventana actual del navegador',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, cerrar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Intentar cerrar la ventana/pestaña
                    window.close();

                    // Si no se puede cerrar (por políticas del navegador), mostrar instrucciones
                    setTimeout(() => {
                        Swal.fire({
                            title: 'Instrucciones',
                            html: `
                                <p class="mb-4">Para cerrar completamente:</p>
                                <div class="text-left space-y-2">
                                    <p><strong>Chrome/Edge:</strong> Ctrl + Shift + W</p>
                                    <p><strong>Firefox:</strong> Ctrl + Shift + W</p>
                                    <p><strong>Safari:</strong> Cmd + W</p>
                                    <p><strong>Móvil:</strong> Cerrar pestaña manualmente</p>
                                </div>
                            `,
                            icon: 'info',
                            confirmButtonColor: '#dc2626'
                        });
                    }, 1000);
                }
            });
        }

        function mostrarAyuda() {
            Swal.fire({
                title: 'Centro de Ayuda',
                html: `
                    <div class="text-left space-y-4">
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-2">Contacto de Soporte</h4>
                            <p class="text-sm text-gray-600">Email: soporte@motorservicepy.com</p>
                            <p class="text-sm text-gray-600">Tel: (0984) 800 586</p>
                        </div>
                        
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-2">Horarios de Atención</h4>
                            <p class="text-sm text-gray-600">Lunes a Viernes: 8:00 - 18:00</p>
                            <p class="text-sm text-gray-600">Sábados: 8:00 - 13:00</p>
                        </div>
                        
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-2">Problemas Comunes</h4>
                            <ul class="text-sm text-gray-600 space-y-1">
                                <li>• ¿Olvidaste tu contraseña? Contacta al administrador</li>
                                <li>• ¿Problemas de acceso? Verifica tu conexión</li>
                                <li>• ¿Error en el sistema? Reporta el problema</li>
                            </ul>
                        </div>
                    </div>
                `,
                icon: 'info',
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Entendido'
            });
        }

        // Seguridad adicional: limpiar datos sensibles del DOM
        window.addEventListener('beforeunload', function () {
            // Limpiar cualquier información sensible que pueda quedar en memoria
            document.querySelectorAll('input[type="password"]').forEach(input => {
                input.value = '';
            });
        });
    </script>
</body>

</html>