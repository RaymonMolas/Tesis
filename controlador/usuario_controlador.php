<?php
require_once "../modelo/modelo_usuario.php";
require_once "../modelo/modelo_personal.php";
require_once "../modelo/modelo_cliente.php";

class UsuarioControlador
{

    /**
     * Crear usuario de personal
     */
    static public function ctrCrearUsuarioPersonal()
    {
        if (isset($_POST["id_personal_usuario"])) {
            // Validar datos
            $errores = self::ctrValidarDatosUsuario($_POST, 'personal');
            if (!empty($errores)) {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error de validación",
                        text: "' . implode(", ", $errores) . '"
                    });
                </script>';
                return;
            }

            $datos = array(
                "id_personal" => $_POST["id_personal_usuario"],
                "usuario" => strtolower(trim($_POST["usuario_personal"])),
                "contrasena" => md5($_POST["contrasena_personal"]),
                "rol" => $_POST["rol_personal"],
                "estado" => $_POST["estado_usuario"] ?? "activo"
            );

            $respuesta = ModeloUsuario::mdlCrearUsuarioPersonal($datos);

            if ($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡Usuario creado!",
                        text: "El usuario de personal ha sido creado correctamente",
                        showConfirmButton: false,
                        timer: 2000
                    }).then(function() {
                        window.location = "index.php?pagina=tabla/usuarios";
                    });
                </script>';
            } else if ($respuesta == "usuario_duplicado") {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "El nombre de usuario ya existe"
                    });
                </script>';
            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Hubo un error al crear el usuario"
                    });
                </script>';
            }
        }
    }

    /**
     * Crear usuario de cliente
     */
    static public function ctrCrearUsuarioCliente()
    {
        if (isset($_POST["id_cliente_usuario"])) {
            // Validar datos
            $errores = self::ctrValidarDatosUsuario($_POST, 'cliente');
            if (!empty($errores)) {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error de validación",
                        text: "' . implode(", ", $errores) . '"
                    });
                </script>';
                return;
            }

            $datos = array(
                "id_cliente" => $_POST["id_cliente_usuario"],
                "usuario" => strtolower(trim($_POST["usuario_cliente"])),
                "contrasena" => md5($_POST["contrasena_cliente"]),
                "estado" => $_POST["estado_usuario"] ?? "activo"
            );

            $respuesta = ModeloUsuario::mdlCrearUsuarioCliente($datos);

            if ($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡Usuario creado!",
                        text: "El usuario de cliente ha sido creado correctamente",
                        showConfirmButton: false,
                        timer: 2000
                    }).then(function() {
                        window.location = "index.php?pagina=tabla/usuarios";
                    });
                </script>';
            } else if ($respuesta == "usuario_duplicado") {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "El nombre de usuario ya existe"
                    });
                </script>';
            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Hubo un error al crear el usuario"
                    });
                </script>';
            }
        }
    }

    /**
     * Listar usuarios de personal
     */
    static public function ctrListarUsuariosPersonal()
    {
        try {
            return ModeloUsuario::mdlListarUsuariosPersonal();
        } catch (Exception $e) {
            error_log("Error en ctrListarUsuariosPersonal: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Listar usuarios de clientes
     */
    static public function ctrListarUsuariosCliente()
    {
        try {
            return ModeloUsuario::mdlListarUsuariosCliente();
        } catch (Exception $e) {
            error_log("Error en ctrListarUsuariosCliente: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener usuario de personal por ID
     */
    static public function ctrObtenerUsuarioPersonal($id)
    {
        if (!$id)
            return false;

        try {
            return ModeloUsuario::mdlObtenerUsuarioPersonal($id);
        } catch (Exception $e) {
            error_log("Error en ctrObtenerUsuarioPersonal: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener usuario de cliente por ID
     */
    static public function ctrObtenerUsuarioCliente($id)
    {
        if (!$id)
            return false;

        try {
            return ModeloUsuario::mdlObtenerUsuarioCliente($id);
        } catch (Exception $e) {
            error_log("Error en ctrObtenerUsuarioCliente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Editar usuario de personal
     */
    static public function ctrEditarUsuarioPersonal()
    {
        if (isset($_POST["id_usuario_personal_editar"])) {
            $id = $_POST["id_usuario_personal_editar"];

            // Validar datos
            $errores = self::ctrValidarDatosUsuario($_POST, 'personal', $id);
            if (!empty($errores)) {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error de validación",
                        text: "' . implode(", ", $errores) . '"
                    });
                </script>';
                return;
            }

            $datos = array(
                "usuario" => strtolower(trim($_POST["usuario_personal"])),
                "rol" => $_POST["rol_personal"],
                "estado" => $_POST["estado_usuario"]
            );

            // Si se proporciona nueva contraseña
            if (!empty($_POST["contrasena_personal"])) {
                ModeloUsuario::mdlCambiarContrasenaPersonal($id, md5($_POST["contrasena_personal"]));
            }

            $respuesta = ModeloUsuario::mdlActualizarUsuarioPersonal($id, $datos);

            if ($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡Usuario actualizado!",
                        text: "El usuario ha sido actualizado correctamente",
                        showConfirmButton: false,
                        timer: 2000
                    }).then(function() {
                        window.location = "index.php?pagina=tabla/usuarios";
                    });
                </script>';
            } else if ($respuesta == "usuario_duplicado") {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "El nombre de usuario ya existe"
                    });
                </script>';
            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Hubo un error al actualizar el usuario"
                    });
                </script>';
            }
        }
    }

    /**
     * Editar usuario de cliente
     */
    static public function ctrEditarUsuarioCliente()
    {
        if (isset($_POST["id_usuario_cliente_editar"])) {
            $id = $_POST["id_usuario_cliente_editar"];

            // Validar datos
            $errores = self::ctrValidarDatosUsuario($_POST, 'cliente', $id);
            if (!empty($errores)) {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error de validación",
                        text: "' . implode(", ", $errores) . '"
                    });
                </script>';
                return;
            }

            $datos = array(
                "usuario" => strtolower(trim($_POST["usuario_cliente"])),
                "estado" => $_POST["estado_usuario"]
            );

            // Si se proporciona nueva contraseña
            if (!empty($_POST["contrasena_cliente"])) {
                ModeloUsuario::mdlCambiarContrasenaCliente($id, md5($_POST["contrasena_cliente"]));
            }

            $respuesta = ModeloUsuario::mdlActualizarUsuarioCliente($id, $datos);

            if ($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡Usuario actualizado!",
                        text: "El usuario ha sido actualizado correctamente",
                        showConfirmButton: false,
                        timer: 2000
                    }).then(function() {
                        window.location = "index.php?pagina=tabla/usuarios";
                    });
                </script>';
            } else if ($respuesta == "usuario_duplicado") {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "El nombre de usuario ya existe"
                    });
                </script>';
            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Hubo un error al actualizar el usuario"
                    });
                </script>';
            }
        }
    }

    /**
     * Eliminar usuario de personal
     */
    static public function ctrEliminarUsuarioPersonal()
    {
        if (isset($_GET["id_usuario_personal_eliminar"])) {
            $id = $_GET["id_usuario_personal_eliminar"];

            $respuesta = ModeloUsuario::mdlEliminarUsuarioPersonal($id);

            if ($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡Usuario eliminado!",
                        text: "El usuario ha sido eliminado correctamente",
                        showConfirmButton: false,
                        timer: 2000
                    }).then(function() {
                        window.location = "index.php?pagina=tabla/usuarios";
                    });
                </script>';
            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Hubo un error al eliminar el usuario"
                    });
                </script>';
            }
        }
    }

    /**
     * Eliminar usuario de cliente
     */
    static public function ctrEliminarUsuarioCliente()
    {
        if (isset($_GET["id_usuario_cliente_eliminar"])) {
            $id = $_GET["id_usuario_cliente_eliminar"];

            $respuesta = ModeloUsuario::mdlEliminarUsuarioCliente($id);

            if ($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡Usuario eliminado!",
                        text: "El usuario ha sido eliminado correctamente",
                        showConfirmButton: false,
                        timer: 2000
                    }).then(function() {
                        window.location = "index.php?pagina=tabla/usuarios";
                    });
                </script>';
            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Hubo un error al eliminar el usuario"
                    });
                </script>';
            }
        }
    }

    /**
     * Cambiar estado de usuario personal
     */
    static public function ctrCambiarEstadoPersonal()
    {
        if (isset($_POST["id_usuario_personal_estado"]) && isset($_POST["nuevo_estado"])) {
            $id = $_POST["id_usuario_personal_estado"];
            $estado = $_POST["nuevo_estado"];

            $respuesta = ModeloUsuario::mdlCambiarEstadoPersonal($id, $estado);

            if ($respuesta == "ok") {
                echo json_encode(array("status" => "success", "message" => "Estado actualizado correctamente"));
            } else {
                echo json_encode(array("status" => "error", "message" => "Error al actualizar el estado"));
            }
        }
    }

    /**
     * Cambiar estado de usuario cliente
     */
    static public function ctrCambiarEstadoCliente()
    {
        if (isset($_POST["id_usuario_cliente_estado"]) && isset($_POST["nuevo_estado"])) {
            $id = $_POST["id_usuario_cliente_estado"];
            $estado = $_POST["nuevo_estado"];

            $respuesta = ModeloUsuario::mdlCambiarEstadoCliente($id, $estado);

            if ($respuesta == "ok") {
                echo json_encode(array("status" => "success", "message" => "Estado actualizado correctamente"));
            } else {
                echo json_encode(array("status" => "error", "message" => "Error al actualizar el estado"));
            }
        }
    }

    /**
     * Obtener estadísticas de usuarios
     */
    static public function ctrEstadisticasUsuarios()
    {
        try {
            return ModeloUsuario::mdlEstadisticasUsuarios();
        } catch (Exception $e) {
            error_log("Error en ctrEstadisticasUsuarios: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener personal sin usuario
     */
    static public function ctrPersonalSinUsuario()
    {
        try {
            return ModeloUsuario::mdlPersonalSinUsuario();
        } catch (Exception $e) {
            error_log("Error en ctrPersonalSinUsuario: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener clientes sin usuario
     */
    static public function ctrClientesSinUsuario()
    {
        try {
            return ModeloUsuario::mdlClientesSinUsuario();
        } catch (Exception $e) {
            error_log("Error en ctrClientesSinUsuario: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Buscar usuarios
     */
    static public function ctrBuscarUsuarios()
    {
        if (isset($_POST["termino_busqueda_usuario"])) {
            $termino = $_POST["termino_busqueda_usuario"];
            $tipo = $_POST["tipo_usuario_buscar"] ?? "ambos";

            try {
                return ModeloUsuario::mdlBuscarUsuarios($termino, $tipo);
            } catch (Exception $e) {
                error_log("Error en ctrBuscarUsuarios: " . $e->getMessage());
                return array();
            }
        }
        return array();
    }

    /**
     * Cambiar contraseña de usuario personal
     */
    static public function ctrCambiarContrasenaPersonal()
    {
        if (isset($_POST["id_usuario_personal_contrasena"]) && isset($_POST["nueva_contrasena"])) {
            $id = $_POST["id_usuario_personal_contrasena"];
            $nueva_contrasena = md5($_POST["nueva_contrasena"]);

            $respuesta = ModeloUsuario::mdlCambiarContrasenaPersonal($id, $nueva_contrasena);

            if ($respuesta == "ok") {
                echo json_encode(array("status" => "success", "message" => "Contraseña actualizada correctamente"));
            } else {
                echo json_encode(array("status" => "error", "message" => "Error al actualizar la contraseña"));
            }
        }
    }

    /**
     * Cambiar contraseña de usuario cliente
     */
    static public function ctrCambiarContrasenaCliente()
    {
        if (isset($_POST["id_usuario_cliente_contrasena"]) && isset($_POST["nueva_contrasena"])) {
            $id = $_POST["id_usuario_cliente_contrasena"];
            $nueva_contrasena = md5($_POST["nueva_contrasena"]);

            $respuesta = ModeloUsuario::mdlCambiarContrasenaCliente($id, $nueva_contrasena);

            if ($respuesta == "ok") {
                echo json_encode(array("status" => "success", "message" => "Contraseña actualizada correctamente"));
            } else {
                echo json_encode(array("status" => "error", "message" => "Error al actualizar la contraseña"));
            }
        }
    }

    /**
     * Validar datos del usuario
     */
    static public function ctrValidarDatosUsuario($datos, $tipo = 'personal', $id_excluir = null)
    {
        $errores = array();

        // Validar usuario
        $campo_usuario = ($tipo === 'personal') ? 'usuario_personal' : 'usuario_cliente';
        if (empty($datos[$campo_usuario])) {
            $errores[] = "El nombre de usuario es obligatorio";
        } else {
            $usuario = strtolower(trim($datos[$campo_usuario]));
            if (strlen($usuario) < 3) {
                $errores[] = "El usuario debe tener al menos 3 caracteres";
            }
            if (!preg_match('/^[a-z0-9_]+$/', $usuario)) {
                $errores[] = "El usuario solo puede contener letras minúsculas, números y guiones bajos";
            }

            // Verificar si el usuario ya existe
            if (ModeloUsuario::mdlVerificarUsuario($usuario, $tipo, $id_excluir)) {
                $errores[] = "El nombre de usuario ya existe";
            }
        }

        // Validar contraseña
        $campo_contrasena = ($tipo === 'personal') ? 'contrasena_personal' : 'contrasena_cliente';
        if (empty($datos[$campo_contrasena]) && !$id_excluir) {
            $errores[] = "La contraseña es obligatoria";
        } else if (!empty($datos[$campo_contrasena])) {
            if (strlen($datos[$campo_contrasena]) < 6) {
                $errores[] = "La contraseña debe tener al menos 6 caracteres";
            }
        }

        // Validaciones específicas para personal
        if ($tipo === 'personal') {
            // Validar que el personal exista
            if (empty($datos['id_personal_usuario']) || !is_numeric($datos['id_personal_usuario'])) {
                $errores[] = "Debe seleccionar un personal válido";
            }

            // Validar rol
            $roles_validos = ['administrador', 'gerente', 'empleado'];
            if (empty($datos['rol_personal']) || !in_array($datos['rol_personal'], $roles_validos)) {
                $errores[] = "Debe seleccionar un rol válido";
            }
        } else {
            // Validaciones específicas para cliente
            if (empty($datos['id_cliente_usuario']) || !is_numeric($datos['id_cliente_usuario'])) {
                $errores[] = "Debe seleccionar un cliente válido";
            }
        }

        return $errores;
    }

    /**
     * Generar reporte de usuarios
     */
    static public function ctrGenerarReporteUsuarios()
    {
        try {
            $usuarios_personal = self::ctrListarUsuariosPersonal();
            $usuarios_cliente = self::ctrListarUsuariosCliente();
            $estadisticas = self::ctrEstadisticasUsuarios();
            $personal_sin_usuario = self::ctrPersonalSinUsuario();
            $clientes_sin_usuario = self::ctrClientesSinUsuario();

            return array(
                'usuarios_personal' => $usuarios_personal,
                'usuarios_cliente' => $usuarios_cliente,
                'estadisticas' => $estadisticas,
                'personal_sin_usuario' => $personal_sin_usuario,
                'clientes_sin_usuario' => $clientes_sin_usuario,
                'fecha_generacion' => date('Y-m-d H:i:s')
            );
        } catch (Exception $e) {
            error_log("Error en ctrGenerarReporteUsuarios: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Generar usuario automático
     */
    static public function ctrGenerarUsuarioAutomatico($nombre, $apellido)
    {
        try {
            // Crear usuario basado en nombre y apellido
            $usuario_base = strtolower(substr($nombre, 0, 3) . substr($apellido, 0, 3));
            $usuario = $usuario_base;
            $contador = 1;

            // Verificar disponibilidad
            while (
                ModeloUsuario::mdlVerificarUsuario($usuario, 'personal') ||
                ModeloUsuario::mdlVerificarUsuario($usuario, 'cliente')
            ) {
                $usuario = $usuario_base . $contador;
                $contador++;
            }

            return $usuario;
        } catch (Exception $e) {
            error_log("Error en ctrGenerarUsuarioAutomatico: " . $e->getMessage());
            return 'usuario' . rand(100, 999);
        }
    }

    /**
     * Generar contraseña temporal
     */
    static public function ctrGenerarContrasenaaTemporal()
    {
        $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $contrasena = '';

        for ($i = 0; $i < 8; $i++) {
            $contrasena .= $caracteres[rand(0, strlen($caracteres) - 1)];
        }

        return $contrasena;
    }

    /**
     * Verificar seguridad de contraseña
     */
    static public function ctrVerificarSeguridadContrasena($contrasena)
    {
        $puntuacion = 0;
        $mensajes = array();

        // Longitud
        if (strlen($contrasena) >= 8) {
            $puntuacion += 2;
        } else {
            $mensajes[] = "Debe tener al menos 8 caracteres";
        }

        // Mayúsculas
        if (preg_match('/[A-Z]/', $contrasena)) {
            $puntuacion += 1;
        } else {
            $mensajes[] = "Debe contener al menos una mayúscula";
        }

        // Minúsculas
        if (preg_match('/[a-z]/', $contrasena)) {
            $puntuacion += 1;
        } else {
            $mensajes[] = "Debe contener al menos una minúscula";
        }

        // Números
        if (preg_match('/[0-9]/', $contrasena)) {
            $puntuacion += 1;
        } else {
            $mensajes[] = "Debe contener al menos un número";
        }

        // Caracteres especiales
        if (preg_match('/[^a-zA-Z0-9]/', $contrasena)) {
            $puntuacion += 1;
        } else {
            $mensajes[] = "Recomendado: incluir caracteres especiales";
        }

        // Evaluar nivel de seguridad
        if ($puntuacion >= 5) {
            $nivel = "Muy segura";
        } elseif ($puntuacion >= 4) {
            $nivel = "Segura";
        } elseif ($puntuacion >= 3) {
            $nivel = "Moderada";
        } else {
            $nivel = "Débil";
        }

        return array(
            'puntuacion' => $puntuacion,
            'nivel' => $nivel,
            'mensajes' => $mensajes
        );
    }
}
?>