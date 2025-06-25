<?php

require_once __DIR__ . "/../modelo/modelo_vehiculo.php";

class VehiculoControlador
{
    // Listar todos los vehículos
    static public function ctrListarVehiculos()
    {
        try {
            return ModeloVehiculo::mdlListarVehiculos();
        } catch (Exception $e) {
            error_log("Error en ctrListarVehiculos: " . $e->getMessage());
            return array();
        }
    }

    // Listar vehículos de un cliente específico
    static public function ctrListarVehiculosCliente($id_cliente)
    {
        try {
            return ModeloVehiculo::mdlListarVehiculosCliente($id_cliente);
        } catch (Exception $e) {
            error_log("Error en ctrListarVehiculosCliente: " . $e->getMessage());
            return array();
        }
    }

    // Obtener un vehículo específico
    static public function ctrObtenerVehiculo($id)
    {
        try {
            return ModeloVehiculo::mdlObtenerVehiculo($id);
        } catch (Exception $e) {
            error_log("Error en ctrObtenerVehiculo: " . $e->getMessage());
            return false;
        }
    }

    // Registrar nuevo vehículo
    static public function ctrRegistrarVehiculo()
    {
        if (isset($_POST["id_cliente"]) && isset($_POST["marca"])) {
            try {
                // Validar datos obligatorios
                if (empty($_POST["id_cliente"])) {
                    throw new Exception("Debe seleccionar un cliente");
                }

                if (empty($_POST["marca"])) {
                    throw new Exception("La marca es obligatoria");
                }

                if (empty($_POST["modelo"])) {
                    throw new Exception("El modelo es obligatorio");
                }

                if (empty($_POST["año"]) || !is_numeric($_POST["año"])) {
                    throw new Exception("El año debe ser un número válido");
                }

                if (empty($_POST["matricula"])) {
                    throw new Exception("La matrícula es obligatoria");
                }

                // Validar año
                $año_actual = date('Y');
                if ($_POST["año"] < 1900 || $_POST["año"] > ($año_actual + 1)) {
                    throw new Exception("El año debe estar entre 1900 y " . ($año_actual + 1));
                }

                // Verificar si la matrícula ya existe
                if (ModeloVehiculo::mdlVerificarMatricula($_POST["matricula"])) {
                    throw new Exception("Ya existe un vehículo con esta matrícula");
                }

                // Preparar datos
                $datos = array(
                    "id_cliente" => (int) $_POST["id_cliente"],
                    "marca" => trim(strtoupper($_POST["marca"])),
                    "modelo" => trim(strtoupper($_POST["modelo"])),
                    "año" => (int) $_POST["año"],
                    "matricula" => trim(strtoupper($_POST["matricula"])),
                    "color" => trim($_POST["color"]) ?? null,
                    "numero_motor" => trim($_POST["numero_motor"]) ?? null,
                    "numero_chasis" => trim($_POST["numero_chasis"]) ?? null,
                    "combustible" => $_POST["combustible"] ?? "gasolina",
                    "observaciones" => trim($_POST["observaciones"]) ?? null
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
                        }).then((result) => {
                            if (result.value) {
                                window.location = "index.php?pagina=tabla/vehiculos";
                            }
                        });
                    </script>';
                } else {
                    throw new Exception("Error al registrar el vehículo");
                }
                return "ok";

            } catch (Exception $e) {
                error_log("Error en ctrRegistrarVehiculo: " . $e->getMessage());

                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "' . addslashes($e->getMessage()) . '",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        } else {
            echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Faltan datos obligatorios para registrar el vehículo",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';
        }
        return null;
    }

    // Actualizar vehículo
    static public function ctrActualizarVehiculo()
    {
        if (isset($_POST["id_vehiculo"])) {
            try {
                // Validar datos obligatorios
                if (empty($_POST["id_cliente"])) {
                    throw new Exception("Debe seleccionar un cliente");
                }

                if (empty($_POST["marca"])) {
                    throw new Exception("La marca es obligatoria");
                }

                if (empty($_POST["modelo"])) {
                    throw new Exception("El modelo es obligatorio");
                }

                if (empty($_POST["año"]) || !is_numeric($_POST["año"])) {
                    throw new Exception("El año debe ser un número válido");
                }

                if (empty($_POST["matricula"])) {
                    throw new Exception("La matrícula es obligatoria");
                }

                // Validar año
                $año_actual = date('Y');
                if ($_POST["año"] < 1900 || $_POST["año"] > ($año_actual + 1)) {
                    throw new Exception("El año debe estar entre 1900 y " . ($año_actual + 1));
                }

                // Verificar si la matrícula ya existe (excluyendo el vehículo actual)
                if (ModeloVehiculo::mdlVerificarMatricula($_POST["matricula"], $_POST["id_vehiculo"])) {
                    throw new Exception("Ya existe otro vehículo con esta matrícula");
                }

                // Preparar datos
                $datos = array(
                    "id_vehiculo" => (int) $_POST["id_vehiculo"],
                    "id_cliente" => (int) $_POST["id_cliente"],
                    "marca" => trim(strtoupper($_POST["marca"])),
                    "modelo" => trim(strtoupper($_POST["modelo"])),
                    "año" => (int) $_POST["año"],
                    "matricula" => trim(strtoupper($_POST["matricula"])),
                    "color" => trim($_POST["color"]) ?? null,
                    "numero_motor" => trim($_POST["numero_motor"]) ?? null,
                    "numero_chasis" => trim($_POST["numero_chasis"]) ?? null,
                    "combustible" => $_POST["combustible"] ?? "gasolina",
                    "observaciones" => trim($_POST["observaciones"]) ?? null
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
                        }).then((result) => {
                            if (result.value) {
                                window.location = "index.php?pagina=tabla/vehiculos";
                            }
                        });
                    </script>';
                } else {
                    throw new Exception("Error al actualizar el vehículo");
                }
                return "ok";

            } catch (Exception $e) {
                error_log("Error en ctrActualizarVehiculo: " . $e->getMessage());

                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "' . addslashes($e->getMessage()) . '",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
        return null;
    }

    // Eliminar vehículo
    static public function ctrEliminarVehiculo()
    {
        if (isset($_POST["eliminarVehiculo"])) {
            try {
                // Verificar si el vehículo puede ser eliminado
                if (!ModeloVehiculo::mdlPuedeEliminar($_POST["eliminarVehiculo"])) {
                    throw new Exception("No se puede eliminar el vehículo porque tiene órdenes de trabajo o presupuestos asociados");
                }

                $respuesta = ModeloVehiculo::mdlEliminarVehiculo($_POST["eliminarVehiculo"]);

                if ($respuesta == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡Éxito!",
                            text: "El vehículo ha sido eliminado correctamente",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "index.php?pagina=tabla/vehiculos";
                            }
                        });
                    </script>';
                } else {
                    throw new Exception("No se pudo eliminar el vehículo");
                }
                return "ok";

            } catch (Exception $e) {
                error_log("Error en ctrEliminarVehiculo: " . $e->getMessage());

                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "' . addslashes($e->getMessage()) . '",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        } else {
            echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se especificó qué vehículo eliminar",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';
        }
        return null;
    }

    // Buscar vehículos por matrícula
    static public function ctrBuscarPorMatricula($matricula)
    {
        try {
            return ModeloVehiculo::mdlBuscarPorMatricula($matricula);
        } catch (Exception $e) {
            error_log("Error en ctrBuscarPorMatricula: " . $e->getMessage());
            return array();
        }
    }

    // Obtener marcas más comunes
    static public function ctrObtenerMarcasComunes($limite = 10)
    {
        try {
            return ModeloVehiculo::mdlObtenerMarcasComunes($limite);
        } catch (Exception $e) {
            error_log("Error en ctrObtenerMarcasComunes: " . $e->getMessage());
            return array();
        }
    }

    // Obtener estadísticas de vehículos
    static public function ctrObtenerEstadisticasVehiculos()
    {
        try {
            return ModeloVehiculo::mdlObtenerEstadisticas();
        } catch (Exception $e) {
            error_log("Error en ctrObtenerEstadisticasVehiculos: " . $e->getMessage());
            return array();
        }
    }

    // Contar total de vehículos
    static public function ctrContarVehiculos()
    {
        try {
            return ModeloVehiculo::mdlContarVehiculos();
        } catch (Exception $e) {
            error_log("Error en ctrContarVehiculos: " . $e->getMessage());
            return 0;
        }
    }

    // Obtener historial de servicios de un vehículo
    static public function ctrObtenerHistorialVehiculo($id_vehiculo)
    {
        try {
            return ModeloVehiculo::mdlObtenerHistorialServicios($id_vehiculo);
        } catch (Exception $e) {
            error_log("Error en ctrObtenerHistorialVehiculo: " . $e->getMessage());
            return array();
        }
    }

    // Validar datos de vehículo
    static public function ctrValidarDatosVehiculo($datos)
    {
        $errores = array();

        // Validar marca
        if (empty($datos['marca']) || strlen(trim($datos['marca'])) < 2) {
            $errores[] = "La marca debe tener al menos 2 caracteres";
        }

        // Validar modelo
        if (empty($datos['modelo']) || strlen(trim($datos['modelo'])) < 2) {
            $errores[] = "El modelo debe tener al menos 2 caracteres";
        }

        // Validar año
        if (empty($datos['año']) || !is_numeric($datos['año'])) {
            $errores[] = "El año debe ser un número válido";
        } else {
            $año_actual = date('Y');
            if ($datos['año'] < 1900 || $datos['año'] > ($año_actual + 1)) {
                $errores[] = "El año debe estar entre 1900 y " . ($año_actual + 1);
            }
        }

        // Validar matrícula
        if (empty($datos['matricula'])) {
            $errores[] = "La matrícula es obligatoria";
        } else if (strlen(trim($datos['matricula'])) < 3) {
            $errores[] = "La matrícula debe tener al menos 3 caracteres";
        }

        // Validar combustible
        $combustibles_validos = ['gasolina', 'diesel', 'hibrido', 'electrico', 'gnv'];
        if (!empty($datos['combustible']) && !in_array($datos['combustible'], $combustibles_validos)) {
            $errores[] = "El tipo de combustible no es válido";
        }

        return $errores;
    }

    // Obtener vehículos para select (AJAX)
    static public function ctrObtenerVehiculosParaSelect($id_cliente = null)
    {
        try {
            if ($id_cliente) {
                $vehiculos = ModeloVehiculo::mdlListarVehiculosCliente($id_cliente);
            } else {
                $vehiculos = ModeloVehiculo::mdlListarVehiculos();
            }

            $resultado = array();
            foreach ($vehiculos as $vehiculo) {
                $resultado[] = array(
                    'id' => $vehiculo['id_vehiculo'],
                    'texto' => $vehiculo['marca'] . ' ' . $vehiculo['modelo'] . ' (' . $vehiculo['matricula'] . ')'
                );
            }

            return $resultado;
        } catch (Exception $e) {
            error_log("Error en ctrObtenerVehiculosParaSelect: " . $e->getMessage());
            return array();
        }
    }
}
?>