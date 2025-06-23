<?php
require_once "../modelo/modelo_vehiculo.php";

class VehiculoControlador {
    // Contar total de vehículos
    static public function ctrContarVehiculos() {
        return ModeloVehiculo::mdlContarVehiculos();
    }

    // Listar vehículos de un cliente específico
    static public function ctrListarVehiculosCliente($id_cliente) {
        if (!$id_cliente) return array();
        return ModeloVehiculo::mdlListarVehiculosCliente($id_cliente);
    }

    // Obtener lista de vehículos
    static public function ctrListarVehiculos() {
        return ModeloVehiculo::mdlListarVehiculos();
    }

    // Obtener un vehículo específico
    static public function ctrObtenerVehiculo($id) {
        if (!$id) return false;
        return ModeloVehiculo::mdlObtenerVehiculo($id);
    }

    // Registrar nuevo vehículo
    static public function ctrRegistrarVehiculo() {
        if (isset($_POST["matricula"])) {
            $datos = array(
                "matricula" => $_POST["matricula"],
                "marca" => $_POST["marca"],
                "modelo" => $_POST["modelo"],
                "anho" => $_POST["anho"],
                "color" => $_POST["color"],
                "id_cliente" => $_POST["id_cliente"]
            );

            $respuesta = ModeloVehiculo::mdlRegistrarVehiculo($datos);
            
            if ($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡Éxito!",
                        text: "El vehículo ha sido registrado correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "index.php?pagina=tabla/vehiculos";
                        }
                    });
                </script>';
            }
        }
    }

    // Actualizar vehículo
    static public function ctrActualizarVehiculo() {
        if (isset($_POST["id_vehiculo"])) {
            $datos = array(
                "id_vehiculo" => $_POST["id_vehiculo"],
                "matricula" => $_POST["matricula"],
                "marca" => $_POST["marca"],
                "modelo" => $_POST["modelo"],
                "anho" => $_POST["anho"],
                "color" => $_POST["color"],
                "id_cliente" => $_POST["id_cliente"]
            );

            $respuesta = ModeloVehiculo::mdlActualizarVehiculo($datos);
            
            if ($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡Éxito!",
                        text: "El vehículo ha sido actualizado correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "index.php?pagina=tabla/vehiculos";
                        }
                    });
                </script>';
            }
        }
    }

    // Eliminar vehículo
    static public function ctrEliminarVehiculo() {
        if (isset($_GET["id_vehiculo"])) {
            $respuesta = ModeloVehiculo::mdlEliminarVehiculo($_GET["id_vehiculo"]);
            
            if ($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡Éxito!",
                        text: "El vehículo ha sido eliminado correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "index.php?pagina=tabla/vehiculos";
                        }
                    });
                </script>';
            }
        }
    }
}
