<?php
// Si ya está logueado, redirigir al inicio
if (isset($_SESSION["validarIngreso"]) && $_SESSION["validarIngreso"] == "ok") {
    echo '<script>window.location = "index.php?pagina=inicio";</script>';
    return;
}
?>

<div class="min-h-screen flex">
    <!-- Panel izquierdo - Información de la empresa -->
    <div
        class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-motor-red-600 to-motor-red-800 flex-col justify-center px-12">
        <div class="max-w-md mx-auto text-center text-white">
            <!-- Logo grande -->
            <div class="mb-8">
                <img src="../img/img-01.jpg" alt="Motor Service"
                    class="w-32 h-32 mx-auto rounded-full shadow-2xl border-4 border-white/20">
            </div>

            <!-- Título principal -->
            <h1 class="text-4xl font-bold mb-4">Motor Service</h1>
            <p class="text-xl text-motor-red-100 mb-8">Servicio Integral Automotriz</p>

            <!-- Características -->
            <div class="space-y-4 text-left">
                <div class="flex items-center">
                    <div class="bg-white/20 rounded-full p-2 mr-4">
                        <i data-lucide="wrench" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold">Gestión Completa</h3>
                        <p class="text-sm text-motor-red-100">Órdenes de trabajo, presupuestos y facturación</p>
                    </div>
                </div>

                <div class="flex items-center">
                    <div class="bg-white/20 rounded-full p-2 mr-4">
                        <i data-lucide="users" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold">Portal del Cliente</h3>
                        <p class="text-sm text-motor-red-100">Acceso directo para agendar citas y seguimiento</p>
                    </div>
                </div>

                <div class="flex items-center">
                    <div class="bg-white/20 rounded-full p-2 mr-4">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold">Seguro y Confiable</h3>
                        <p class="text-sm text-motor-red-100">Datos protegidos con tecnología de vanguardia</p>
                    </div>
                </div>
            </div>

            <!-- Información de contacto -->
            <div class="mt-12 pt-8 border-t border-white/20">
                <div class="flex items-center justify-center space-x-6 text-sm">
                    <div class="flex items-center">
                        <i data-lucide="phone" class="w-4 h-4 mr-2"></i>
                        (0984) 800 586
                    </div>
                    <div class="flex items-center">
                        <i data-lucide="globe" class="w-4 h-4 mr-2"></i>
                        motorservicepy.com
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel derecho - Formulario de login -->
    <div class="w-full lg:w-1/2 flex flex-col justify-center px-6 py-12 lg:px-8 bg-white">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <!-- Logo móvil -->
            <div class="lg:hidden text-center mb-8">
                <img src="../img/img-01.jpg" alt="Motor Service" class="w-20 h-20 mx-auto rounded-full shadow-lg">
                <h2 class="mt-4 text-2xl font-bold text-gray-900">Motor Service</h2>
                <p class="text-gray-600">Servicio Integral Automotriz</p>
            </div>

            <!-- Título del formulario -->
            <div class="text-center">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900">
                    Iniciar Sesión
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Accede a tu cuenta para continuar
                </p>
            </div>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-6 shadow-xl rounded-xl border border-gray-100">
                <form method="post" class="space-y-6" id="loginForm">
                    <!-- Tipo de usuario -->
                    <div>
                        <label for="tipo_usuario" class="block text-sm font-medium leading-6 text-gray-900">
                            Tipo de Usuario
                        </label>
                        <div class="mt-2">
                            <select name="tipo_usuario" id="tipo_usuario" required
                                class="block w-full rounded-lg border-0 py-3 px-4 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-motor-red-600 transition-all duration-200">
                                <option value="" disabled selected>Seleccione tipo de usuario</option>
                                <option value="cliente">👤 Cliente</option>
                                <option value="personal">👨‍💼 Personal</option>
                            </select>
                        </div>
                    </div>

                    <!-- Usuario -->
                    <div>
                        <label for="txtusuario" class="block text-sm font-medium leading-6 text-gray-900">
                            Usuario
                        </label>
                        <div class="mt-2 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="user" class="h-5 w-5 text-gray-400"></i>
                            </div>
                            <input type="text" name="txtusuario" id="txtusuario" required autocomplete="username"
                                class="block w-full rounded-lg border-0 py-3 pl-10 pr-4 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-motor-red-600 transition-all duration-200"
                                placeholder="Ingrese su usuario">
                        </div>
                    </div>

                    <!-- Contraseña -->
                    <div>
                        <label for="txtclave" class="block text-sm font-medium leading-6 text-gray-900">
                            Contraseña
                        </label>
                        <div class="mt-2 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="lock" class="h-5 w-5 text-gray-400"></i>
                            </div>
                            <input type="password" name="txtclave" id="txtclave" required
                                autocomplete="current-password"
                                class="block w-full rounded-lg border-0 py-3 pl-10 pr-12 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-motor-red-600 transition-all duration-200"
                                placeholder="Ingrese su contraseña">
                            <button type="button" id="togglePassword"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i data-lucide="eye"
                                    class="h-5 w-5 text-gray-400 hover:text-gray-600 transition-colors duration-200"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Recordar sesión -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember-me" name="remember-me" type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-motor-red-600 focus:ring-motor-red-600">
                            <label for="remember-me" class="ml-3 block text-sm leading-6 text-gray-900">
                                Recordar sesión
                            </label>
                        </div>
                        <div class="text-sm leading-6">
                            <a href="#"
                                class="font-semibold text-motor-red-600 hover:text-motor-red-500 transition-colors duration-200">
                                ¿Olvidó su contraseña?
                            </a>
                        </div>
                    </div>

                    <!-- Procesamiento del login en PHP -->
                    <?php
                    $ingreso = new logincontrolador();
                    $ingreso->ctrIngreso();
                    ?>

                    <!-- Botón de login -->
                    <div>
                        <button type="submit" id="loginButton"
                            class="flex w-full justify-center rounded-lg bg-motor-red-600 px-3 py-3 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-motor-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-motor-red-600 transition-all duration-200 transform hover:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed">
                            <span id="loginButtonText">Iniciar Sesión</span>
                            <div id="loginSpinner" class="hidden ml-2">
                                <div class="loader border-white"></div>
                            </div>
                        </button>
                    </div>
                </form>

                <!-- Información adicional -->
                <div class="mt-8 text-center">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-xs uppercase">
                            <span class="bg-white px-2 text-gray-500">Información del Sistema</span>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-4 text-xs text-gray-500">
                        <div class="flex items-center justify-center">
                            <i data-lucide="shield" class="w-3 h-3 mr-1"></i>
                            Seguro SSL
                        </div>
                        <div class="flex items-center justify-center">
                            <i data-lucide="clock" class="w-3 h-3 mr-1"></i>
                            24/7 Disponible
                        </div>
                    </div>
                </div>
            </div>

            <!-- Demo credentials -->
            <div class="mt-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
                <h3 class="text-sm font-medium text-gray-900 mb-3 flex items-center">
                    <i data-lucide="info" class="w-4 h-4 mr-2 text-blue-500"></i>
                    Credenciales de Demostración
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                    <div class="bg-white p-3 rounded border">
                        <p class="font-medium text-gray-700">Personal/Administrador:</p>
                        <p class="text-gray-600">Usuario: <strong>admin</strong></p>
                        <p class="text-gray-600">Contraseña: <strong>admin123</strong></p>
                    </div>
                    <div class="bg-white p-3 rounded border">
                        <p class="font-medium text-gray-700">Cliente:</p>
                        <p class="text-gray-600">Usuario: <strong>pedro</strong></p>
                        <p class="text-gray-600">Contraseña: <strong>pedro123</strong></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para funcionalidades del login -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Inicializar iconos
        lucide.createIcons();

        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('txtclave');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function () {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                // Cambiar icono
                const icon = this.querySelector('i');
                if (type === 'text') {
                    icon.setAttribute('data-lucide', 'eye-off');
                } else {
                    icon.setAttribute('data-lucide', 'eye');
                }
                lucide.createIcons();
            });
        }

        // Form submission handling
        const loginForm = document.getElementById('loginForm');
        const loginButton = document.getElementById('loginButton');
        const loginButtonText = document.getElementById('loginButtonText');
        const loginSpinner = document.getElementById('loginSpinner');

        if (loginForm) {
            loginForm.addEventListener('submit', function (e) {
                // Mostrar loading
                loginButton.disabled = true;
                loginButtonText.textContent = 'Iniciando...';
                loginSpinner.classList.remove('hidden');

                // Opcional: agregar un timeout para restablecer el botón si hay error
                setTimeout(() => {
                    if (loginButton.disabled) {
                        loginButton.disabled = false;
                        loginButtonText.textContent = 'Iniciar Sesión';
                        loginSpinner.classList.add('hidden');
                    }
                }, 10000); // 10 segundos timeout
            });
        }

        // Autocompletar credenciales demo al hacer click
        const demoCredentials = document.querySelectorAll('[data-demo-user]');
        demoCredentials.forEach(element => {
            element.addEventListener('click', function () {
                const user = this.dataset.demoUser;
                const password = this.dataset.demoPassword;
                const userType = this.dataset.demoType;

                document.getElementById('txtusuario').value = user;
                document.getElementById('txtclave').value = password;
                document.getElementById('tipo_usuario').value = userType;
            });
        });

        // Animaciones de entrada
        setTimeout(() => {
            document.querySelector('.sm\\:mx-auto').classList.add('animate-fade-in-up');
        }, 100);

        // Focus automático en el primer campo vacío
        const tipoUsuario = document.getElementById('tipo_usuario');
        const usuario = document.getElementById('txtusuario');

        if (tipoUsuario.value === '') {
            tipoUsuario.focus();
        } else if (usuario.value === '') {
            usuario.focus();
        }
    });

    // Validación en tiempo real
    document.getElementById('txtusuario').addEventListener('input', function () {
        const value = this.value.trim();
        const parent = this.closest('div').closest('div');

        if (value.length >= 3) {
            parent.classList.remove('ring-red-500');
            parent.classList.add('ring-green-500');
        } else {
            parent.classList.remove('ring-green-500');
            parent.classList.add('ring-red-500');
        }
    });

    document.getElementById('txtclave').addEventListener('input', function () {
        const value = this.value.trim();
        const parent = this.closest('div').closest('div');

        if (value.length >= 4) {
            parent.classList.remove('ring-red-500');
            parent.classList.add('ring-green-500');
        } else {
            parent.classList.remove('ring-green-500');
            parent.classList.add('ring-red-500');
        }
    });
</script>

<style>
    /* Estilos adicionales para el login */
    .animate-fade-in-up {
        animation: fadeInUp 0.6s ease-out forwards;
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

    /* Loader pequeño para el botón */
    .loader {
        border: 2px solid transparent;
        border-top: 2px solid currentColor;
        border-radius: 50%;
        width: 16px;
        height: 16px;
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

    /* Efectos hover mejorados */
    .transition-all {
        transition: all 0.2s ease-in-out;
    }

    /* Estilos para los inputs en focus */
    input:focus,
    select:focus {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.15);
    }

    /* Gradiente animado de fondo */
    .bg-gradient-to-br {
        background-size: 400% 400%;
        animation: gradientShift 15s ease infinite;
    }

    @keyframes gradientShift {
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
        .sm\:mx-auto {
            margin-left: 0;
            margin-right: 0;
        }

        .sm\:w-full {
            width: 100%;
        }

        .shadow-xl {
            box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
    }
</style>