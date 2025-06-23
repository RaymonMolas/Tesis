<?php
require_once "../modelo/modelo_usuario.php";

class ControladorUsuario {

    static public function buscarUsuarios($tipo) {
        if ($tipo == "cliente") {
            $tabla = "usuariocliente";
            $tipoRelacion = "cliente";
        } elseif ($tipo == "personal") {
            $tabla = "usuariopersonal";
            $tipoRelacion = "personal";
        } else {
            return [];
        }

        return ModeloUsuario::buscarUsuarios($tabla, $tipoRelacion);
    }

    public function eliminarUsuario() {
        if (isset($_POST["eliminarRegistro"])) {
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
        }
    }

    static public function guardarUsuarioCliente() {
        if (isset($_POST["id_cliente"])) {
            $tabla = "usuariocliente";
            $datos = array(
                "id_cliente" => $_POST["id_cliente"],
                "usuario" => $_POST["usuario"],
                "contrasena" => $_POST["contrasena"]
            );
            $respuesta = ModeloUsuario::guardarUsuario($tabla, $datos);
            return $respuesta;
        }
    }

    // GUARDAR USUARIO PERSONAL
    static public function guardarUsuarioPersonal() {
        if (isset($_POST["id_personal"])) {
            $tabla = "usuariopersonal";
            $datos = array(
                "id_personal" => $_POST["id_personal"],
                "usuario" => $_POST["usuario"],
                "contrasena" => $_POST["contrasena"]
            );
            $respuesta = ModeloUsuario::guardarUsuario($tabla, $datos);
            return $respuesta;
        }
    }

    static public function guardarUsuario($tipo) {
        if ($tipo == "cliente") {
            return self::guardarUsuarioCliente();
        } elseif ($tipo == "personal") {
            return self::guardarUsuarioPersonal();
        }
        return null;
    }

    static public function buscarUsuarioClientePorId($id) {
        return ModeloUsuario::buscarUsuarioPorId("usuariocliente", "id_usuario_cliente", $id);
    }
    
    static public function buscarUsuarioPersonalPorId($id) {
        return ModeloUsuario::buscarUsuarioPorId("usuariopersonal", "id_usuario_personal", $id);
    }
    
    static public function actualizarUsuario($tipo) {
        if (isset($_POST["id_usuario"])) {
            $tabla = ($tipo == "cliente") ? "usuariocliente" : "usuariopersonal";
            $campo = ($tipo == "cliente") ? "id_usuario_cliente" : "id_usuario_personal";
    
            $datos = array(
                "id" => $_POST["id_usuario"],
                "usuario" => $_POST["usuario"],
                "contrasena" => $_POST["contrasena"]
            );
    
            return ModeloUsuario::actualizarUsuario($tabla, $campo, $datos);
        }
        return null;
    }
}
?>
