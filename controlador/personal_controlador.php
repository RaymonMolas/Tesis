<?php
require_once "../modelo/modelo_personal.php";

class ControladorPersonal {

    /* GUARDAR PERSONAL */
    static public function guardarPersonal() {
        if (isset($_POST["nombre"])) {
            $tabla = "personal";
            $datos = array(
                "nombre"    => $_POST["nombre"],
                "apellido"  => $_POST["apellido"],
                "cargo"     => $_POST["cargo"],
                "telefono"  => $_POST["telefono"],
                "email"     => $_POST["email"]
            );
            $respuesta = ModeloPersonal::guardarPersonal($tabla, $datos);
            return $respuesta;
        }
    }

    /* BUSCAR PERSONAL */
    static public function buscarPersonal($item = null, $valor = null) {
        $tabla = "personal";
        $respuesta = ModeloPersonal::buscarPersonal($tabla, $item, $valor);
        return $respuesta;
    }

    /* ACTUALIZAR PERSONAL */
    static public function actualizarPersonal() {
        if (isset($_POST["id"])) {
            $tabla = "personal";
            $datos = array(
                "id_personal" => $_POST["id"],
                "nombre"      => $_POST["nombre"],
                "apellido"    => $_POST["apellido"],
                "cargo"       => $_POST["cargo"],
                "telefono"    => $_POST["telefono"],
                "email"       => $_POST["email"]
            );
            $respuesta = ModeloPersonal::actualizarPersonal($tabla, $datos);
            return $respuesta;
        }
    }

    /* ELIMINAR PERSONAL */
    public function eliminarPersonal() {
        if (isset($_POST["eliminarRegistro"])) {
            $tabla = "personal";
            $valor = $_POST["eliminarRegistro"];
            $respuesta = ModeloPersonal::eliminarPersonal($tabla, $valor);
            if ($respuesta == "ok") {
                echo '<script>
                    if (window.history.replaceState) {
                        window.history.replaceState(null, null, window.location.href);
                    }
                    window.location = "index.php?pagina=tabla/personales";
                </script>';
            }
        }
    }
}
?>
