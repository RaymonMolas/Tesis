<?php

require_once __DIR__ . "/../modelo/modelo_personal.php";

class PersonalControlador
{
    // Listar todo el personal
    static public function ctrListarPersonal()
    {
        try {
            return ModeloPersonal::mdlListarPersonal();
        } catch (Exception $e) {
            error_log("Error en ctrListarPersonal: " . $e->getMessage());
            return array();
        }
    }

    // Buscar personal (alias para mantener compatibilidad)
    static public function buscarPersonal()
    {
        return self::ctrListarPersonal();
    }

    // Obtener personal específico
    static public function ctrObtenerPersonal($id)
    {
        try {
            return ModeloPersonal::mdlObtenerPersonal($id);
        } catch (Exception $e) {
            error_log("Error en ctrObtenerPersonal: " . $e->getMessage());
            return false;
        }
    }

    // Registrar nuevo personal
    static public function ctrRegistrarPersonal()
    {
        if (isset($_POST["nombre"]) && isset($_POST["apellido"])) {
            try {
                // Validar datos obligatorios
                if (empty(trim($_POST["nombre"]))) {
                    throw new Exception("El nombre es obligatorio");
                }

                if (empty(trim($_POST["apellido"]))) {
                    throw new Exception("El apellido es obligatorio");
                }

                if (empty(trim($_POST["cedula"]))) {
                    throw new Exception("La cédula es obligatoria");
                }

                if (empty(trim($_POST["telefono"]))) {
                    throw new Exception("El teléfono es obligatorio");
                }

                if (empty(trim($_POST["cargo"]))) {
                    throw new Exception("El cargo es obligatorio");
                }

                // Validar formato de email si se proporciona
                if (!empty($_POST["email"]) && !filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("El formato del email no es válido");
                }

                // Validar que la cédula no exista
                $personalExistente = ModeloPersonal::mdlBuscarPorCedula($_POST["cedula"]);
                if ($personalExistente) {
                    throw new Exception("Ya existe personal con esta cédula");
                }

                $datos = array(
                    "nombre" => trim($_POST["nombre"]),
                    "apellido" => trim($_POST["apellido"]),
                    "cedula" => trim($_POST["cedula"]),
                    "telefono" => trim($_POST["telefono"]),
                    "email" => !empty($_POST["email"]) ? trim($_POST["email"]) : null,
                    "direccion" => !empty($_POST["direccion"]) ? trim($_POST["direccion"]) : null,
                    "cargo" => trim($_POST["cargo"]),
                    "fecha_ingreso" => !empty($_POST["fecha_ingreso"]) ? $_POST["fecha_ingreso"] : date('Y-m-d'),
                    "salario" => !empty($_POST["salario"]) ? (float) $_POST["salario"] : null,
                    "estado" => $_POST["estado"] ?? "activo"
                );

                $resultado = ModeloPersonal::mdlRegistrarPersonal($datos);

                if ($resultado == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡Éxito!",
                            text: "El personal ha sido registrado correctamente"
                        }).then(function(result) {
                            if (result.value) {
                                window.location = "index.php?pagina=tabla/personales";
                            }
                        });
                    </script>';
                } else {
                    throw new Exception("Error al registrar el personal");
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

        return "ok";
    }

    // Actualizar personal
    static public function ctrActualizarPersonal()
    {
        if (isset($_POST["id_personal"]) && isset($_POST["nombre"])) {
            try {
                // Validar que la cédula no exista en otro registro
                if (!empty($_POST["cedula"])) {
                    $personalExistente = ModeloPersonal::mdlBuscarPorCedula($_POST["cedula"]);
                    if ($personalExistente && $personalExistente["id_personal"] != $_POST["id_personal"]) {
                        throw new Exception("Ya existe otro personal con esta cédula");
                    }
                }

                $datos = array(
                    "id_personal" => (int) $_POST["id_personal"],
                    "nombre" => trim($_POST["nombre"]),
                    "apellido" => trim($_POST["apellido"]),
                    "cedula" => trim($_POST["cedula"]),
                    "telefono" => trim($_POST["telefono"]),
                    "email" => !empty($_POST["email"]) ? trim($_POST["email"]) : null,
                    "direccion" => !empty($_POST["direccion"]) ? trim($_POST["direccion"]) : null,
                    "cargo" => trim($_POST["cargo"]),
                    "fecha_ingreso" => $_POST["fecha_ingreso"],
                    "salario" => !empty($_POST["salario"]) ? (float) $_POST["salario"] : null,
                    "estado" => $_POST["estado"] ?? "activo"
                );

                $resultado = ModeloPersonal::mdlActualizarPersonal($datos);

                if ($resultado == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡Éxito!",
                            text: "El personal ha sido actualizado correctamente"
                        }).then(function(result) {
                            if (result.value) {
                                window.location = "index.php?pagina=tabla/personales";
                            }
                        });
                    </script>';
                } else {
                    throw new Exception("Error al actualizar el personal");
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

        return "ok";
    }

    // Eliminar personal
    public function eliminarPersonal()
    {
        if (isset($_POST["eliminarPersonal"])) {
            try {
                $id = (int) $_POST["eliminarPersonal"];
                
                // Verificar si el personal tiene órdenes asociadas
                $tieneOrdenes = ModeloPersonal::mdlTieneOrdenesAsociadas($id);
                if ($tieneOrdenes) {
                    echo '<script>
                        Swal.fire({
                            icon: "warning",
                            title: "No se puede eliminar",
                            text: "Este personal tiene órdenes de trabajo asociadas"
                        });
                    </script>';
                    return "error";
                }

                $resultado = ModeloPersonal::mdlEliminarPersonal($id);

                if ($resultado == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡Éxito!",
                            text: "El personal ha sido eliminado correctamente"
                        });
                    </script>';
                } else {
                    throw new Exception("Error al eliminar el personal");
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

        return "ok";
    }

    // Obtener estadísticas del personal
    static public function ctrObtenerEstadisticasPersonal()
    {
        try {
            return ModeloPersonal::mdlObtenerEstadisticas();
        } catch (Exception $e) {
            error_log("Error en ctrObtenerEstadisticasPersonal: " . $e->getMessage());
            return array();
        }
    }

    // Buscar personal por término
    static public function ctrBuscarPersonal($termino)
    {
        try {
            return ModeloPersonal::mdlBuscarPersonal($termino);
        } catch (Exception $e) {
            error_log("Error en ctrBuscarPersonal: " . $e->getMessage());
            return array();
        }
    }

    // Obtener personal activo
    static public function ctrObtenerPersonalActivo()
    {
        try {
            return ModeloPersonal::mdlObtenerPersonalActivo();
        } catch (Exception $e) {
            error_log("Error en ctrObtenerPersonalActivo: " . $e->getMessage());
            return array();
        }
    }

    // Contar total de personal
    static public function ctrContarPersonal()
    {
        try {
            return ModeloPersonal::mdlContarPersonal();
        } catch (Exception $e) {
            error_log("Error en ctrContarPersonal: " . $e->getMessage());
            return 0;
        }
    }

    // Validar credenciales para login
    static public function ctrValidarCredenciales($cedula, $password)
    {
        try {
            return ModeloPersonal::mdlValidarCredenciales($cedula, $password);
        } catch (Exception $e) {
            error_log("Error en ctrValidarCredenciales: " . $e->getMessage());
            return false;
        }
    }
}
?>