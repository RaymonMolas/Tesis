<?php

require_once __DIR__ . "/../modelo/modelo_usuario.php";

class ControladorUsuario
{
    // Buscar usuarios por tipo
    static public function buscarUsuarios($tipo = "cliente")
    {
        try {
            return ModeloUsuario::buscarUsuarios($tipo);
        } catch (Exception $e) {
            error_log("Error en buscarUsuarios: " . $e->getMessage());
            return array();
        }
    }

    // Eliminar usuario
    public function eliminarUsuario()
    {
        if (isset($_POST["eliminarRegistro"]) && isset($_POST["tipoEliminar"])) {
            try {
                $tipo = $_POST["tipoEliminar"];
                $campo = ($tipo === "cliente") ? "id_usuario_cliente" : "id_usuario_personal";
                $tabla = ($tipo === "cliente") ? "usuariocliente" : "usuariopersonal";
                $valor = $_POST["eliminarRegistro"];

                $respuesta = ModeloUsuario::eliminarUsuario($tabla, $campo, $valor);

                if ($respuesta === "ok") {
                    echo '<script>
                        if (window.history.replaceState) {
                            window.history.replaceState(null, null, window.location.href);
                        }
                        window.location = "index.php?pagina=tabla/usuarios";
                    </script>';
                }
            } catch (Exception $e) {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "' . $e->getMessage() . '"
                    });
                </script>';
            }
        }
    }

    // Guardar usuario cliente
    static public function guardarUsuarioCliente()
    {
        if (isset($_POST["id_cliente"])) {
            try {
                // Validar que el cliente no tenga ya un usuario
                if (ModeloUsuario::clienteTieneUsuario($_POST["id_cliente"])) {
                    echo '<script>
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "Este cliente ya tiene un usuario asignado"
                        });
                    </script>';
                    return "error";
                }

                // Validar que el nombre de usuario no exista
                if (ModeloUsuario::verificarUsuarioExiste($_POST["usuario"])) {
                    echo '<script>
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "Ya existe un usuario con este nombre"
                        });
                    </script>';
                    return "error";
                }

                $tabla = "usuariocliente";
                $datos = array(
                    "id_cliente" => $_POST["id_cliente"],
                    "usuario" => $_POST["usuario"],
                    "contrasena" => $_POST["contrasena"]
                );
                $respuesta = ModeloUsuario::guardarUsuario($tabla, $datos);
                return $respuesta;
            } catch (Exception $e) {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "' . $e->getMessage() . '"
                    });
                </script>';
                return "error";
            }
        }
        return "error";
    }

    // Guardar usuario personal
    static public function guardarUsuarioPersonal()
    {
        if (isset($_POST["id_personal"])) {
            try {
                // Validar que el personal no tenga ya un usuario
                if (ModeloUsuario::personalTieneUsuario($_POST["id_personal"])) {
                    echo '<script>
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "Este personal ya tiene un usuario asignado"
                        });
                    </script>';
                    return "error";
                }

                // Validar que el nombre de usuario no exista
                if (ModeloUsuario::verificarUsuarioExiste($_POST["usuario"])) {
                    echo '<script>
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "Ya existe un usuario con este nombre"
                        });
                    </script>';
                    return "error";
                }

                $tabla = "usuariopersonal";
                $datos = array(
                    "id_personal" => $_POST["id_personal"],
                    "usuario" => $_POST["usuario"],
                    "contrasena" => $_POST["contrasena"]
                );
                $respuesta = ModeloUsuario::guardarUsuario($tabla, $datos);
                return $respuesta;
            } catch (Exception $e) {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "' . $e->getMessage() . '"
                    });
                </script>';
                return "error";
            }
        }
        return "error";
    }

    // Guardar usuario genérico
    static public function guardarUsuario($tipo)
    {
        if ($tipo == "cliente") {
            return self::guardarUsuarioCliente();
        } elseif ($tipo == "personal") {
            return self::guardarUsuarioPersonal();
        }
        return null;
    }

    // Buscar usuario cliente por ID
    static public function buscarUsuarioClientePorId($id)
    {
        return ModeloUsuario::buscarUsuarioPorId("usuariocliente", "id_usuario_cliente", $id);
    }

    // Buscar usuario personal por ID
    static public function buscarUsuarioPersonalPorId($id)
    {
        return ModeloUsuario::buscarUsuarioPorId("usuariopersonal", "id_usuario_personal", $id);
    }

    // Actualizar usuario
    static public function actualizarUsuario($tipo)
    {
        if (isset($_POST["id_usuario"])) {
            try {
                $tabla = ($tipo == "cliente") ? "usuariocliente" : "usuariopersonal";
                $campo = ($tipo == "cliente") ? "id_usuario_cliente" : "id_usuario_personal";

                // Validar que el nombre de usuario no exista en otro registro
                if (ModeloUsuario::verificarUsuarioExiste($_POST["usuario"], $_POST["id_usuario"], $tabla)) {
                    echo '<script>
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "Ya existe otro usuario con este nombre"
                        });
                    </script>';
                    return "error";
                }

                $datos = array(
                    "id" => $_POST["id_usuario"],
                    "usuario" => $_POST["usuario"],
                    "contrasena" => $_POST["contrasena"]
                );

                $respuesta = ModeloUsuario::actualizarUsuario($tabla, $campo, $datos);
                
                if ($respuesta == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡Éxito!",
                            text: "Usuario actualizado correctamente"
                        }).then(function(result) {
                            if (result.value) {
                                window.location = "index.php?pagina=tabla/usuarios";
                            }
                        });
                    </script>';
                }
                
                return $respuesta;
            } catch (Exception $e) {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "' . $e->getMessage() . '"
                    });
                </script>';
                return "error";
            }
        }
        return null;
    }

    // Cambiar contraseña
    static public function cambiarContrasena($tipo, $id, $contrasena_nueva)
    {
        try {
            $tabla = ($tipo == "cliente") ? "usuariocliente" : "usuariopersonal";
            $campo = ($tipo == "cliente") ? "id_usuario_cliente" : "id_usuario_personal";
            
            return ModeloUsuario::cambiarContrasena($tabla, $campo, $id, $contrasena_nueva);
        } catch (Exception $e) {
            error_log("Error en cambiarContrasena: " . $e->getMessage());
            return "error";
        }
    }

    // Obtener estadísticas
    static public function obtenerEstadisticas()
    {
        try {
            return ModeloUsuario::obtenerEstadisticas();
        } catch (Exception $e) {
            error_log("Error en obtenerEstadisticas: " . $e->getMessage());
            return array();
        }
    }

    // Bloquear usuario
    static public function bloquearUsuario($tabla, $usuario)
    {
        try {
            return ModeloUsuario::bloquearUsuario($tabla, $usuario);
        } catch (Exception $e) {
            error_log("Error en bloquearUsuario: " . $e->getMessage());
            return "error";
        }
    }

    // Desbloquear usuario
    static public function desbloquearUsuario($tipo, $id)
    {
        try {
            $tabla = ($tipo == "cliente") ? "usuariocliente" : "usuariopersonal";
            $campo = ($tipo == "cliente") ? "id_usuario_cliente" : "id_usuario_personal";
            
            return ModeloUsuario::desbloquearUsuario($tabla, $campo, $id);
        } catch (Exception $e) {
            error_log("Error en desbloquearUsuario: " . $e->getMessage());
            return "error";
        }
    }
}
?>
?>