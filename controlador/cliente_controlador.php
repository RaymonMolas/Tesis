<?php
require_once "../modelo/modelo_cliente.php";

class ClienteControlador {
    /* CONTAR TOTAL DE CLIENTES */
    static public function ctrContarClientes() {
        return ModeloCliente::contarClientes();
    }

    /* GUARDAR CLIENTE */
    static public function guardarCliente() {
        if (isset($_POST["nombre"])) {
            $tabla = "cliente";
            $datos = array(
                "nombre"    => $_POST["nombre"],
                "apellido"  => $_POST["apellido"],
                "cedula"    => $_POST["cedula"],
                "direccion" => $_POST["direccion"],
                "telefono"  => $_POST["telefono"],
                "email"     => $_POST["email"]
            );
            $respuesta = ModeloCliente::guardarCliente($tabla, $datos);
            return $respuesta;
        }
    }

    /* BUSCAR CLIENTE */
    static public function buscarCliente($item = null, $valor = null) {
        $tabla = "cliente";
        $respuesta = ModeloCliente::buscarCliente($tabla, $item, $valor);
        return $respuesta;
    }

    /* ACTUALIZAR CLIENTE */
    static public function actualizarCliente() {
        if (isset($_POST["id"])) {
            $tabla = "cliente";
            $datos = array(
                "id_cliente" => $_POST["id"],
                "nombre"     => $_POST["nombre"],
                "apellido"   => $_POST["apellido"],
                "cedula"     => $_POST["cedula"],
                "direccion"  => $_POST["direccion"],
                "telefono"   => $_POST["telefono"],
                "email"      => $_POST["email"]
            );
            $respuesta = ModeloCliente::actualizarCliente($tabla, $datos);
            return $respuesta;
        }
    }

    /* ELIMINAR CLIENTE */
    public function eliminarCliente() {
        if (isset($_POST["eliminarRegistro"])) {
            $tabla = "cliente";
            $valor = $_POST["eliminarRegistro"];
            $respuesta = ModeloCliente::eliminarCliente($tabla, $valor);
            if ($respuesta == "ok") {
                echo '<script>
                    if (window.history.replaceState) {
                        window.history.replaceState(null, null, window.location.href);
                    }
                    window.location = "index.php?pagina=tabla/clientes";
                </script>';
            }
        }
    }
}
