<?php

require_once __DIR__ . "/../modelo/modelo_historicocitas.php";

class HistoricocitasControlador
{
    // Listar todas las citas históricas
    static public function ctrListarHistoricocitas()
    {
        try {
            return ModeloHistoricocitas::mdlListarHistoricocitas();
        } catch (Exception $e) {
            error_log("Error en ctrListarHistoricocitas: " . $e->getMessage());
            return array();
        }
    }

    // Obtener historial de un cliente específico
    static public function ctrObtenerHistorialCliente($id_cliente)
    {
        try {
            return ModeloHistoricocitas::mdlObtenerHistorialCliente($id_cliente);
        } catch (Exception $e) {
            error_log("Error en ctrObtenerHistorialCliente: " . $e->getMessage());
            return array();
        }
    }

    // Obtener una cita histórica específica
    static public function ctrObtenerHistoricocita($id)
    {
        try {
            return ModeloHistoricocitas::mdlObtenerHistoricocita($id);
        } catch (Exception $e) {
            error_log("Error en ctrObtenerHistoricocita: " . $e->getMessage());
            return false;
        }
    }

    // Registrar nueva cita en historial
    static public function ctrRegistrarHistoricocita()
    {
        if (isset($_POST["id_cliente"]) && isset($_POST["fecha_cita"])) {
            try {
                // Validar datos obligatorios
                if (empty($_POST["id_cliente"])) {
                    throw new Exception("Debe seleccionar un cliente");
                }

                if (empty($_POST["fecha_cita"])) {
                    throw new Exception("Debe establecer una fecha de cita");
                }

                if (empty($_POST["motivo"])) {
                    throw new Exception("Debe especificar el motivo de la cita");
                }

                $datos = array(
                    "id_cliente" => (int) $_POST["id_cliente"],
                    "id_personal" => isset($_SESSION["id_personal"]) ? (int) $_SESSION["id_personal"] : null,
                    "fecha_cita" => $_POST["fecha_cita"],
                    "hora_cita" => $_POST["hora_cita"] ?? null,
                    "motivo" => trim($_POST["motivo"]),
                    "observaciones" => trim($_POST["observaciones"] ?? ""),
                    "estado" => $_POST["estado"] ?? "completada",
                    "fecha_registro" => date('Y-m-d H:i:s')
                );

                $resultado = ModeloHistoricocitas::mdlRegistrarHistoricocita($datos);

                if ($resultado == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡Éxito!",
                            text: "La cita histórica ha sido registrada correctamente"
                        }).then(function(result) {
                            if (result.value) {
                                window.location = "index.php?pagina=tabla/historicocitas";
                            }
                        });
                    </script>';
                } else {
                    throw new Exception("Error al registrar la cita histórica");
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

    // Actualizar cita histórica
    static public function ctrActualizarHistoricocita()
    {
        if (isset($_POST["id_historicocita"]) && isset($_POST["fecha_cita"])) {
            try {
                $datos = array(
                    "id_historicocita" => (int) $_POST["id_historicocita"],
                    "id_cliente" => (int) $_POST["id_cliente"],
                    "fecha_cita" => $_POST["fecha_cita"],
                    "hora_cita" => $_POST["hora_cita"] ?? null,
                    "motivo" => trim($_POST["motivo"]),
                    "observaciones" => trim($_POST["observaciones"] ?? ""),
                    "estado" => $_POST["estado"] ?? "completada"
                );

                $resultado = ModeloHistoricocitas::mdlActualizarHistoricocita($datos);

                if ($resultado == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡Éxito!",
                            text: "La cita histórica ha sido actualizada correctamente"
                        }).then(function(result) {
                            if (result.value) {
                                window.location = "index.php?pagina=tabla/historicocitas";
                            }
                        });
                    </script>';
                } else {
                    throw new Exception("Error al actualizar la cita histórica");
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

    // Eliminar cita histórica
    static public function ctrEliminarHistoricocita()
    {
        if (isset($_POST["eliminarHistoricocita"])) {
            try {
                $id = (int) $_POST["eliminarHistoricocita"];
                $resultado = ModeloHistoricocitas::mdlEliminarHistoricocita($id);

                if ($resultado == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡Éxito!",
                            text: "La cita histórica ha sido eliminada correctamente"
                        });
                    </script>';
                } else {
                    throw new Exception("Error al eliminar la cita histórica");
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

    // Obtener estadísticas de citas históricas
    static public function ctrObtenerEstadisticasHistoricocitas()
    {
        try {
            return ModeloHistoricocitas::mdlObtenerEstadisticas();
        } catch (Exception $e) {
            error_log("Error en ctrObtenerEstadisticasHistoricocitas: " . $e->getMessage());
            return array();
        }
    }

    // Buscar citas históricas
    static public function ctrBuscarHistoricocitas($termino)
    {
        try {
            return ModeloHistoricocitas::mdlBuscarHistoricocitas($termino);
        } catch (Exception $e) {
            error_log("Error en ctrBuscarHistoricocitas: " . $e->getMessage());
            return array();
        }
    }

    // Obtener citas por rango de fechas
    static public function ctrObtenerCitasPorFechas($fecha_inicio, $fecha_fin)
    {
        try {
            return ModeloHistoricocitas::mdlObtenerCitasPorFechas($fecha_inicio, $fecha_fin);
        } catch (Exception $e) {
            error_log("Error en ctrObtenerCitasPorFechas: " . $e->getMessage());
            return array();
        }
    }

    // Contar total de citas históricas
    static public function ctrContarHistoricocitas()
    {
        try {
            return ModeloHistoricocitas::mdlContarHistoricocitas();
        } catch (Exception $e) {
            error_log("Error en ctrContarHistoricocitas: " . $e->getMessage());
            return 0;
        }
    }
}
?>