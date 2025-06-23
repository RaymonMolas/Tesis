<!-- Página 404 Modernizada -->
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <!-- Ilustración del error -->
        <div class="mb-8 relative">
            <!-- Fondo decorativo -->
            <div class="absolute inset-0 flex items-center justify-center">
                <div
                    class="w-96 h-96 bg-gradient-to-br from-motor-red-100 to-motor-red-200 rounded-full opacity-20 animate-pulse">
                </div>
            </div>

            <!-- Número 404 grande -->
            <div class="relative z-10">
                <h1 class="text-9xl font-bold text-gray-900 mb-4 tracking-tight">404</h1>
                <div class="flex items-center justify-center space-x-4 mb-6">
                    <div class="h-1 w-16 bg-motor-red-600 rounded"></div>
                    <i data-lucide="wrench" class="w-8 h-8 text-motor-red-600"></i>
                    <div class="h-1 w-16 bg-motor-red-600 rounded"></div>
                </div>
            </div>
        </div>

        <!-- Mensaje principal -->
        <div class="mb-8">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                ¡Ups! Página no encontrada
            </h2>
            <p class="text-lg text-gray-600 mb-6 max-w-lg mx-auto">
                La página que estás buscando no existe o ha sido movida.
                Parece que necesitamos hacer una <span class="text-motor-red-600 font-semibold">revisión técnica</span>
                a esta URL.
            </p>
        </div>

        <!-- Información adicional -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 mb-8 max-w-md mx-auto">
            <div class="flex items-center mb-4">
                <div class="p-2 bg-motor-red-100 rounded-lg">
                    <i data-lucide="info" class="w-5 h-5 text-motor-red-600"></i>
                </div>
                <h3 class="ml-3 text-lg font-semibold text-gray-900">¿Qué puedes hacer?</h3>
            </div>
            <div class="space-y-3 text-left">
                <div class="flex items-center text-sm text-gray-600">
                    <i data-lucide="check" class="w-4 h-4 mr-3 text-green-500 flex-shrink-0"></i>
                    <span>Verifica que la URL esté escrita correctamente</span>
                </div>
                <div class="flex items-center text-sm text-gray-600">
                    <i data-lucide="check" class="w-4 h-4 mr-3 text-green-500 flex-shrink-0"></i>
                    <span>Regresa a la página anterior usando el botón "Atrás"</span>
                </div>
                <div class="flex items-center text-sm text-gray-600">
                    <i data-lucide="check" class="w-4 h-4 mr-3 text-green-500 flex-shrink-0"></i>
                    <span>Utiliza el menú de navegación principal</span>
                </div>
                <div class="flex items-center text-sm text-gray-600">
                    <i data-lucide="check" class="w-4 h-4 mr-3 text-green-500 flex-shrink-0"></i>
                    <span>Contacta al administrador si el problema persiste</span>
                </div>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="flex flex-col sm:flex-row items-center justify-center space-y-3 sm:space-y-0 sm:space-x-4 mb-8">
            <a href="index.php?pagina=inicio"
                class="inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-lg text-base font-medium text-white bg-motor-red-600 hover:bg-motor-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-motor-red-500 transition-colors duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                <i data-lucide="home" class="w-5 h-5 mr-2"></i>
                Ir al Inicio
            </a>

            <button onclick="window.history.back()"
                class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-lg text-base font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-motor-red-500 transition-colors duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                <i data-lucide="arrow-left" class="w-5 h-5 mr-2"></i>
                Página Anterior
            </button>
        </div>

        <!-- Enlaces rápidos -->
        <?php if (isset($_SESSION["validarIngreso"]) && $_SESSION["validarIngreso"] == "ok"): ?>
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 max-w-lg mx-auto">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center justify-center">
                    <i data-lucide="zap" class="w-5 h-5 mr-2 text-motor-red-600"></i>
                    Accesos Rápidos
                </h3>

                <?php if ($_SESSION["tipo_usuario"] == "personal"): ?>
                    <!-- Enlaces para personal -->
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <a href="index.php?pagina=tabla/clientes"
                            class="flex items-center p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors duration-200 group">
                            <i data-lucide="users"
                                class="w-4 h-4 mr-2 text-blue-600 group-hover:scale-110 transition-transform duration-200"></i>
                            <span class="text-blue-700">Clientes</span>
                        </a>

                        <a href="index.php?pagina=tabla/orden_trabajo"
                            class="flex items-center p-3 bg-green-50 hover:bg-green-100 rounded-lg transition-colors duration-200 group">
                            <i data-lucide="wrench"
                                class="w-4 h-4 mr-2 text-green-600 group-hover:scale-110 transition-transform duration-200"></i>
                            <span class="text-green-700">Órdenes</span>
                        </a>

                        <a href="index.php?pagina=tabla/vehiculos"
                            class="flex items-center p-3 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors duration-200 group">
                            <i data-lucide="car"
                                class="w-4 h-4 mr-2 text-purple-600 group-hover:scale-110 transition-transform duration-200"></i>
                            <span class="text-purple-700">Vehículos</span>
                        </a>

                        <a href="index.php?pagina=tabla/facturas"
                            class="flex items-center p-3 bg-yellow-50 hover:bg-yellow-100 rounded-lg transition-colors duration-200 group">
                            <i data-lucide="file-text"
                                class="w-4 h-4 mr-2 text-yellow-600 group-hover:scale-110 transition-transform duration-200"></i>
                            <span class="text-yellow-700">Facturas</span>
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Enlaces para clientes -->
                    <div class="grid grid-cols-1 gap-3 text-sm">
                        <a href="index.php?pagina=nuevo/agendamiento"
                            class="flex items-center justify-center p-3 bg-motor-red-50 hover:bg-motor-red-100 rounded-lg transition-colors duration-200 group">
                            <i data-lucide="calendar-plus"
                                class="w-4 h-4 mr-2 text-motor-red-600 group-hover:scale-110 transition-transform duration-200"></i>
                            <span class="text-motor-red-700">Agendar Cita</span>
                        </a>

                        <a href="index.php?pagina=tabla/agendamiento_cliente"
                            class="flex items-center justify-center p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors duration-200 group">
                            <i data-lucide="calendar"
                                class="w-4 h-4 mr-2 text-blue-600 group-hover:scale-110 transition-transform duration-200"></i>
                            <span class="text-blue-700">Mis Citas</span>
                        </a>

                        <a href="index.php?pagina=tabla/vehiculos_cliente"
                            class="flex items-center justify-center p-3 bg-green-50 hover:bg-green-100 rounded-lg transition-colors duration-200 group">
                            <i data-lucide="car"
                                class="w-4 h-4 mr-2 text-green-600 group-hover:scale-110 transition-transform duration-200"></i>
                            <span class="text-green-700">Mis Vehículos</span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Información de contacto -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <p class="text-sm text-gray-500 mb-4">¿Necesitas ayuda? Contáctanos:</p>
            <div class="flex items-center justify-center space-x-6 text-sm text-gray-600">
                <div class="flex items-center">
                    <i data-lucide="phone" class="w-4 h-4 mr-2 text-motor-red-600"></i>
                    <span>(0984) 800 586</span>
                </div>
                <div class="flex items-center">
                    <i data-lucide="mail" class="w-4 h-4 mr-2 text-motor-red-600"></i>
                    <span>info@motorservicepy.com</span>
                </div>
            </div>
        </div>

        <!-- Logo pequeño -->
        <div class="mt-8 flex items-center justify-center">
            <img src="../img/img-01.jpg" alt="Motor Service" class="w-12 h-12 rounded-full mr-3">
            <div>
                <h4 class="text-lg font-bold text-gray-900">Motor Service</h4>
                <p class="text-sm text-gray-500">Servicio Integral Automotriz</p>
            </div>
        </div>
    </div>
</div>

<!-- Efectos visuales adicionales -->
<div class="fixed top-10 left-10 w-20 h-20 bg-motor-red-100 rounded-full opacity-20 animate-bounce"
    style="animation-delay: 0s;"></div>
<div class="fixed top-32 right-16 w-16 h-16 bg-blue-100 rounded-full opacity-20 animate-bounce"
    style="animation-delay: 1s;"></div>
<div class="fixed bottom-20 left-20 w-12 h-12 bg-green-100 rounded-full opacity-20 animate-bounce"
    style="animation-delay: 2s;"></div>
<div class="fixed bottom-32 right-32 w-14 h-14 bg-yellow-100 rounded-full opacity-20 animate-bounce"
    style="animation-delay: 0.5s;"></div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Inicializar iconos
        lucide.createIcons();

        // Animación de entrada
        const elementos = document.querySelectorAll('.max-w-2xl > *');
        elementos.forEach((elemento, index) => {
            elemento.style.opacity = '0';
            elemento.style.transform = 'translateY(30px)';

            setTimeout(() => {
                elemento.style.transition = 'all 0.6s ease-out';
                elemento.style.opacity = '1';
                elemento.style.transform = 'translateY(0)';
            }, index * 150);
        });

        // Efecto de hover en los botones
        const botones = document.querySelectorAll('a, button');
        botones.forEach(boton => {
            boton.addEventListener('mouseenter', function () {
                this.style.transform = 'translateY(-2px)';
            });

            boton.addEventListener('mouseleave', function () {
                this.style.transform = 'translateY(0)';
            });
        });

        // Registrar el error en la consola para debugging
        console.warn('Página 404: URL no encontrada - ' + window.location.href);

        // Opcional: Enviar error al servidor para logging
        const errorData = {
            url: window.location.href,
            referrer: document.referrer,
            userAgent: navigator.userAgent,
            timestamp: new Date().toISOString()
        };

        // Aquí podrías enviar errorData a tu endpoint de logging
        // fetch('/api/log-404', { method: 'POST', body: JSON.stringify(errorData) });

        // Easter egg: si el usuario hace click 5 veces en el logo, mostrar mensaje
        let clickCount = 0;
        const logo = document.querySelector('img[alt="Motor Service"]');
        if (logo) {
            logo.addEventListener('click', function () {
                clickCount++;
                if (clickCount >= 5) {
                    Swal.fire({
                        title: '¡Has encontrado un Easter Egg! 🥚',
                        text: 'Gracias por usar Motor Service. ¡Eres muy observador!',
                        icon: 'success',
                        confirmButtonColor: '#dc2626',
                        confirmButtonText: '¡Genial!'
                    });
                    clickCount = 0; // Reset counter
                }
            });
        }

        // Agregar animación de pulso al número 404
        const numero404 = document.querySelector('h1');
        if (numero404) {
            setInterval(() => {
                numero404.style.transform = 'scale(1.05)';
                setTimeout(() => {
                    numero404.style.transform = 'scale(1)';
                }, 300);
            }, 3000);
        }
    });

    // Función para reportar el error (opcional)
    function reportError() {
        Swal.fire({
            title: 'Reportar Problema',
            input: 'textarea',
            inputLabel: 'Describe qué estabas intentando hacer cuando ocurrió este error:',
            inputPlaceholder: 'Estaba navegando desde... intentando acceder a...',
            showCancelButton: true,
            confirmButtonText: 'Enviar Reporte',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc2626'
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                // Aquí enviarías el reporte al servidor
                Swal.fire({
                    title: '¡Gracias!',
                    text: 'Tu reporte ha sido enviado. Nos ayuda a mejorar el sistema.',
                    icon: 'success',
                    confirmButtonColor: '#dc2626'
                });
            }
        });
    }
</script>

<style>
    /* Animaciones personalizadas */
    @keyframes float {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-20px);
        }
    }

    .animate-float {
        animation: float 6s ease-in-out infinite;
    }

    /* Efecto de brillo en el número 404 */
    h1 {
        background: linear-gradient(-45deg, #1f2937, #dc2626, #1f2937, #dc2626);
        background-size: 400% 400%;
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: gradient 15s ease infinite;
    }

    @keyframes gradient {
        0% {
            background-position: 0% 50%;
        }

        50% {
            background-position: 100% 50%;
        }

        100% {
            background-position: 0% 50%;
        }
    }

    /* Responsive improvements */
    @media (max-width: 640px) {
        h1 {
            font-size: 6rem;
        }

        h2 {
            font-size: 2rem;
        }

        .fixed {
            display: none;
            /* Ocultar elementos decorativos en móvil */
        }
    }

    /* Mejoras de accesibilidad */
    @media (prefers-reduced-motion: reduce) {
        * {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
    }
</style>