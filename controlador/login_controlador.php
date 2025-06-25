<?php

require_once __DIR__ . "/../modelo/login_modelo.php";

class LoginControlador
{
    // Procesar ingreso de usuario
    public function ctrIngreso()
    {
        if (isset($_POST["txtusuario"]) && isset($_POST["tipo_usuario"])) {
            try {
                $usuario = trim($_POST["txtusuario"]);
                $clave = $_POST["txtclave"];
                $tipo = $_POST["tipo_usuario"];

                // Validar datos de entrada
                if (empty($usuario)) {
                    throw new Exception("El usuario es obligatorio");
                }

                if (empty($clave)) {
                    throw new Exception("La contraseña es obligatoria");
                }

                // Definir tabla y campos según el tipo de usuario
                if ($tipo == "cliente") {
                    $tabla = "usuariocliente";
                    $itemUsuario = "usuario";
                    $itemClave = "contrasena";
                    $campoId = "id_usuario_cliente";
                    $campoFK = "id_cliente";
                } elseif ($tipo == "personal") {
                    $tabla = "usuariopersonal";
                    $itemUsuario = "usuario";
                    $itemClave = "contrasena";
                    $campoId = "id_usuario_personal";
                    $campoFK = "id_personal";
                } else {
                    throw new Exception("Tipo de usuario no válido");
                }

                // Buscar usuario
                $respuesta = ModeloLogin::buscarUsuario($tabla, $itemUsuario, $usuario);

                if ($respuesta && $respuesta[$itemUsuario] == $usuario && $respuesta[$itemClave] == $clave) {
                    // Login exitoso
                    $_SESSION["validarIngreso"] = "ok";
                    $_SESSION["usuario"] = $usuario;
                    $_SESSION["tipo_usuario"] = $tipo;
                    $_SESSION["id_usuario"] = $respuesta[$campoId];

                    // Guardar también el ID correspondiente
                    if ($tipo === "cliente") {
                        $_SESSION["id_cliente"] = $respuesta[$campoFK];
                    } elseif ($tipo === "personal") {
                        $_SESSION["id_personal"] = $respuesta[$campoFK];
                    }

                    // Registrar último acceso
                    $this->registrarUltimoAcceso($respuesta[$campoId], $tabla);

                    echo '<script>
                        if (window.history.replaceState) {
                            window.history.replaceState(null, null, window.location.href);
                        }
                        window.location = "index.php?pagina=inicio";
                    </script>';

                } else {
                    throw new Exception("Usuario o contraseña incorrectos");
                }

            } catch (Exception $e) {
                echo '<script>
                    if (window.history.replaceState) {
                        window.history.replaceState(null, null, window.location.href);
                    }
                </script>';
                echo '<div class="alert alert-danger">Error al ingresar: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        }
    }

    // Cerrar sesión
    public function ctrCerrarSesion()
    {
        // Limpiar todas las variables de sesión
        session_unset();
        session_destroy();

        echo '<script>
            window.location = "index.php?pagina=login";
        </script>';
    }

    // Verificar si usuario está logueado
    public static function ctrVerificarSesion()
    {
        if (!isset($_SESSION["validarIngreso"]) || $_SESSION["validarIngreso"] != "ok") {
            echo '<script>
                window.location = "index.php?pagina=login";
            </script>';
            exit();
        }
    }

    // Verificar si es personal (para funciones administrativas)
    public static function ctrVerificarPersonal()
    {
        self::ctrVerificarSesion();
        
        if (!isset($_SESSION["tipo_usuario"]) || $_SESSION["tipo_usuario"] != "personal") {
            echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "Acceso Denegado",
                    text: "No tienes permisos para acceder a esta sección"
                }).then(() => {
                    window.location = "index.php?pagina=inicio";
                });
            </script>';
            exit();
        }
    }

    // Registrar último acceso del usuario
    private function registrarUltimoAcceso($id_usuario, $tabla)
    {
        try {
            ModeloLogin::actualizarUltimoAcceso($id_usuario, $tabla);
        } catch (Exception $e) {
            error_log("Error al registrar último acceso: " . $e->getMessage());
        }
    }

    // Cambiar contraseña
    public function ctrCambiarContrasena()
    {
        if (isset($_POST["contrasena_actual"]) && isset($_POST["contrasena_nueva"])) {
            try {
                $contrasena_actual = $_POST["contrasena_actual"];
                $contrasena_nueva = $_POST["contrasena_nueva"];
                $confirmar_contrasena = $_POST["confirmar_contrasena"];

                // Validaciones
                if (empty($contrasena_actual)) {
                    throw new Exception("La contraseña actual es obligatoria");
                }

                if (empty($contrasena_nueva)) {
                    throw new Exception("La nueva contraseña es obligatoria");
                }

                if (strlen($contrasena_nueva) < 6) {
                    throw new Exception("La nueva contraseña debe tener al menos 6 caracteres");
                }

                if ($contrasena_nueva !== $confirmar_contrasena) {
                    throw new Exception("Las contraseñas no coinciden");
                }

                // Verificar contraseña actual
                $tipo = $_SESSION["tipo_usuario"];
                $id_usuario = $_SESSION["id_usuario"];

                if ($tipo == "cliente") {
                    $tabla = "usuariocliente";
                    $campo_id = "id_usuario_cliente";
                } else {
                    $tabla = "usuariopersonal";
                    $campo_id = "id_usuario_personal";
                }

                $usuario_actual = ModeloLogin::obtenerUsuarioPorId($id_usuario, $tabla, $campo_id);

                if (!$usuario_actual || $usuario_actual["contrasena"] !== $contrasena_actual) {
                    throw new Exception("La contraseña actual es incorrecta");
                }

                // Actualizar contraseña
                $respuesta = ModeloLogin::cambiarContrasena($id_usuario, $contrasena_nueva, $tabla, $campo_id);

                if ($respuesta == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡Éxito!",
                            text: "La contraseña ha sido cambiada correctamente"
                        });
                    </script>';
                } else {
                    throw new Exception("Error al cambiar la contraseña");
                }

            } catch (Exception $e) {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "' . addslashes($e->getMessage()) . '"
                    });
                </script>';
            }
        }
    }

    // Obtener información del usuario logueado
    public static function ctrObtenerUsuarioActual()
    {
        self::ctrVerificarSesion();

        $tipo = $_SESSION["tipo_usuario"];
        $id_usuario = $_SESSION["id_usuario"];

        if ($tipo == "cliente") {
            $tabla = "usuariocliente";
            $campo_id = "id_usuario_cliente";
        } else {
            $tabla = "usuariopersonal";
            $campo_id = "id_usuario_personal";
        }

        try {
            return ModeloLogin::obtenerUsuarioPorId($id_usuario, $tabla, $campo_id);
        } catch (Exception $e) {
            error_log("Error en ctrObtenerUsuarioActual: " . $e->getMessage());
            return false;
        }
    }
}
?>