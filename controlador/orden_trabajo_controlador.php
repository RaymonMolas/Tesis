<?php
// SOLUCIÓN: Usar rutas absolutas desde la raíz del proyecto
require_once __DIR__ . "/../modelo/modelo_orden_trabajo.php";
require_once __DIR__ . "/../modelo/modelo_orden_detalle.php";

class OrdenTrabajoControlador
{
    // Listar todas las órdenes de trabajo
    static public function ctrListarOrdenesTrabajo()
    {
        try {
            return ModeloOrdenTrabajo::mdlListarOrdenesTrabajo();
        } catch (Exception $e) {
            error_log("Error en ctrListarOrdenesTrabajo: " . $e->getMessage());
            return array();
        }
    }

    // Obtener una orden específica con sus detalles
    static public function ctrObtenerOrdenTrabajo($id)
    {
        try {
            $orden = ModeloOrdenTrabajo::mdlObtenerOrdenTrabajo($id);
            if ($orden) {
                $orden['detalles'] = ModeloOrdenDetalle::mdlObtenerDetalles($id);
                $orden['total'] = ModeloOrdenDetalle::mdlCalcularTotal($id);
            }
            return $orden;
        } catch (Exception $e) {
            error_log("Error en ctrObtenerOrdenTrabajo: " . $e->getMessage());
            return false;
        }
    }

    // Registrar nueva orden con detalles
    static public function ctrRegistrarOrdenTrabajo()
    {
        if (isset($_POST["id_vehiculo"]) && isset($_POST["servicios"])) {
            try {
                // Validar sesión
                if (!isset($_SESSION["id_personal"])) {
                    throw new Exception("No hay sesión de personal activa");
                }

                // Validar datos básicos
                if (empty($_POST["id_vehiculo"])) {
                    throw new Exception("Debe seleccionar un vehículo");
                }

                if (empty($_POST["fecha_entrada"])) {
                    throw new Exception("Debe establecer una fecha de entrada");
                }

                if (empty($_POST["fecha_salida"])) {
                    throw new Exception("Debe establecer una fecha de salida");
                }

                // Validar y decodificar servicios
                $servicios = json_decode($_POST["servicios"], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception("Error en los datos de servicios: " . json_last_error_msg());
                }

                if (empty($servicios)) {
                    throw new Exception("Debe agregar al menos un servicio");
                }

                // Preparar datos de la orden
                $datos = array(
                    "id_vehiculo" => (int) $_POST["id_vehiculo"],
                    "id_personal" => (int) $_SESSION["id_personal"],
                    "fecha_ingreso" => $_POST["fecha_entrada"],
                    "fecha_salida" => $_POST["fecha_salida"],
                    "kilometraje_actual" => isset($_POST["kilometraje_actual"]) ? $_POST["kilometraje_actual"] : null,
                    "tipo_servicio" => isset($_POST["tipo_servicio"]) ? $_POST["tipo_servicio"] : null,
                    "estado" => "en_proceso",
                    "observaciones" => $_POST["observaciones"] ?? ""
                );

                // Insertar orden de trabajo
                $resultado = ModeloOrdenTrabajo::mdlRegistrarOrdenTrabajo($datos);

                if ($resultado !== "error" && is_numeric($resultado)) {
                    $id_orden = $resultado;
                    $errores_servicios = array();

                    // Insertar detalles de servicios
                    foreach ($servicios as $index => $servicio) {
                        // Validar estructura del servicio
                        if (!isset($servicio["tipo"]) || empty($servicio["tipo"])) {
                            $errores_servicios[] = "Tipo de servicio faltante en servicio " . ($index + 1);
                            continue;
                        }

                        if (!isset($servicio["descripcion"]) || empty($servicio["descripcion"])) {
                            $errores_servicios[] = "Descripción faltante en servicio " . ($index + 1);
                            continue;
                        }

                        if (!isset($servicio["cantidad"]) || $servicio["cantidad"] <= 0) {
                            $errores_servicios[] = "Cantidad inválida en servicio " . ($index + 1);
                            continue;
                        }

                        if (!isset($servicio["precioUnitario"]) || $servicio["precioUnitario"] <= 0) {
                            $errores_servicios[] = "Precio inválido en servicio " . ($index + 1);
                            continue;
                        }

                        if (!isset($servicio["subtotal"]) || $servicio["subtotal"] <= 0) {
                            $errores_servicios[] = "Subtotal inválido en servicio " . ($index + 1);
                            continue;
                        }

                        // Preparar datos del detalle
                        $datosDetalle = array(
                            "id_orden" => $id_orden,
                            "tipo_servicio" => $servicio["tipo"],
                            "descripcion" => $servicio["descripcion"],
                            "cantidad" => $servicio["cantidad"],
                            "precio_unitario" => $servicio["precioUnitario"],
                            "subtotal" => $servicio["subtotal"]
                        );

                        $resultadoDetalle = ModeloOrdenDetalle::mdlRegistrarDetalle($datosDetalle);

                        if ($resultadoDetalle !== "ok") {
                            $errores_servicios[] = "Error al guardar servicio " . ($index + 1);
                        }
                    }

                    if (!empty($errores_servicios)) {
                        throw new Exception("Errores en los servicios: " . implode(", ", $errores_servicios));
                    }

                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡Éxito!",
                            text: "La orden de trabajo ha sido registrada correctamente",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "index.php?pagina=tabla/orden_trabajo";
                            }
                        });
                    </script>';
                    return "ok";
                } else {
                    throw new Exception("Error al crear orden de trabajo");
                }

            } catch (Exception $e) {
                error_log("Error en ctrRegistrarOrdenTrabajo: " . $e->getMessage());

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
                    text: "Faltan datos obligatorios para crear la orden",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';
        }
        return null;
    }

    // Actualizar orden de trabajo
    static public function ctrActualizarOrdenTrabajo()
    {
        if (isset($_POST["id_orden"]) && isset($_POST["servicios"])) {
            try {
                // Validar y decodificar servicios
                if (isset($_POST["servicios"]) && !empty($_POST["servicios"])) {
                    $servicios = json_decode($_POST["servicios"], true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new Exception("Error en formato de servicios: " . json_last_error_msg());
                    }
                } else {
                    throw new Exception("No se especificaron servicios para la orden");
                }

                if (empty($servicios)) {
                    throw new Exception("Debe tener al menos un servicio en la orden");
                }

                $datos = array(
                    "id_orden" => $_POST["id_orden"],
                    "id_vehiculo" => $_POST["id_vehiculo"],
                    "id_personal" => $_SESSION["id_personal"],
                    "fecha_salida" => $_POST["fecha_salida"],
                    "estado" => $_POST["estado"] ?? "en_proceso",
                    "observaciones" => $_POST["observaciones"]
                );

                $respuesta = ModeloOrdenTrabajo::mdlActualizarOrdenTrabajo($datos);

                if ($respuesta == "ok") {
                    // Si el nuevo estado es completado, registrar historial
                    if (($datos["estado"] ?? "") == "completado") {
                        $orden = self::ctrObtenerOrdenTrabajo($_POST["id_orden"]);
                        if ($orden) {
                            $datosHistorial = [
                                "id_vehiculo" => $orden["id_vehiculo"],
                                "id_cliente" => $orden["id_cliente"],
                                "id_orden" => $orden["id_orden"],
                                "id_personal" => $_SESSION["id_personal"] ?? null
                            ];
                            require_once __DIR__ . "/historial_controlador.php";
                            $resultadoHistorial = ControladorHistorial::mdlRegistrarHistorialOrden($datosHistorial);
                            if ($resultadoHistorial !== "ok") {
                                error_log("Error al registrar historial de vehículo para orden " . $orden["id_orden"]);
                            }
                        } else {
                            error_log("No se encontró la orden para registrar historial: ID " . $_POST["id_orden"]);
                        }
                    }

                    // Eliminar detalles anteriores
                    ModeloOrdenDetalle::mdlEliminarDetalles($_POST["id_orden"]);

                    // Registrar nuevos detalles
                    $error = false;

                    foreach ($servicios as $servicio) {
                        $detalleServicio = array(
                            "id_orden" => $_POST["id_orden"],
                            "tipo_servicio" => $servicio["tipo"],
                            "descripcion" => $servicio["descripcion"],
                            "cantidad" => $servicio["cantidad"],
                            "precio_unitario" => $servicio["precioUnitario"],
                            "subtotal" => $servicio["subtotal"]
                        );

                        if (ModeloOrdenDetalle::mdlRegistrarDetalle($detalleServicio) !== "ok") {
                            $error = true;
                            break;
                        }
                    }

                    if (!$error) {
                        echo '<script>
                            Swal.fire({
                                icon: "success",
                                title: "¡Éxito!",
                                text: "La orden de trabajo ha sido actualizada",
                                showConfirmButton: true,
                                confirmButtonText: "Cerrar"
                            }).then((result) => {
                                if (result.value) {
                                    window.location = "index.php?pagina=tabla/orden_trabajo";
                                }
                            });
                        </script>';
                        return "ok";
                    }
                }
            } catch (Exception $e) {
                error_log("Error en ctrActualizarOrdenTrabajo: " . $e->getMessage());
            }

            echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Ocurrió un error al actualizar la orden",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';
        }
        return null;
    }

    // Eliminar orden de trabajo
    static public function ctrEliminarOrdenTrabajo()
    {
        if (isset($_POST["eliminarOrden"])) {
            try {
                $respuesta = ModeloOrdenTrabajo::mdlEliminarOrdenTrabajo($_POST["eliminarOrden"]);

                if ($respuesta == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡Éxito!",
                            text: "La orden de trabajo ha sido eliminada correctamente",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "index.php?pagina=tabla/orden_trabajo";
                            }
                        });
                    </script>';
                } else {
                    throw new Exception("No se pudo eliminar la orden de trabajo");
                }
                return "ok";
            } catch (Exception $e) {
                error_log("Error en ctrEliminarOrdenTrabajo: " . $e->getMessage());

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
                    text: "No se especificó qué orden eliminar",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';
        }
        return null;
    }

    // Cambiar estado de orden
    static public function ctrCambiarEstadoOrden()
    {
        if (isset($_POST["id_orden"]) && isset($_POST["nuevo_estado"])) {
            try {
                $datos = array(
                    "id_orden" => $_POST["id_orden"],
                    "estado" => $_POST["nuevo_estado"],
                    "fecha_salida" => ($_POST["nuevo_estado"] == "completado") ? date('Y-m-d H:i:s') : null
                );

                $respuesta = ModeloOrdenTrabajo::mdlActualizarEstado($datos);

                if ($respuesta == "ok") {
                    // Si el nuevo estado es completado, registrar historial
                    if ($_POST["nuevo_estado"] == "completado") {
                        $orden = self::ctrObtenerOrdenTrabajo($_POST["id_orden"]);
                        if ($orden) {
                            $datosHistorial = [
                                "id_vehiculo" => $orden["id_vehiculo"],
                                "id_cliente" => $orden["id_cliente"],
                                "id_orden" => $orden["id_orden"],
                                "descripcion" => "Orden completada: " . ($orden["observaciones"] ?? ""),
                                "id_personal" => $_SESSION["id_personal"] ?? null
                            ];
                            require_once __DIR__ . "/historial_controlador.php";
                            $resultadoHistorial = ControladorHistorial::mdlRegistrarHistorialOrden($datosHistorial);
                            if ($resultadoHistorial !== "ok") {
                                error_log("Error al registrar historial de vehículo para orden " . $orden["id_orden"]);
                            }
                        } else {
                            error_log("No se encontró la orden para registrar historial: ID " . $_POST["id_orden"]);
                        }
                    }

                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡Éxito!",
                            text: "El estado de la orden ha sido actualizado",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "index.php?pagina=tabla/orden_trabajo";
                            }
                        });
                    </script>';
                    return "ok";
                }
            } catch (Exception $e) {
                error_log("Error en ctrCambiarEstadoOrden: " . $e->getMessage());
            }

            echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Ocurrió un error al cambiar el estado",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';
        }
        return null;
    }

    // Método para crear historial de órdenes completadas existentes
    static public function ctrCrearHistorialOrdenesExistentes() {
        try {
            // Obtener órdenes completadas que no tienen historial
            $stmt = Conexion::conectar()->prepare("
                SELECT o.id_orden, o.id_vehiculo, v.id_cliente, o.observaciones, o.id_personal
                FROM ordentrabajo o
                INNER JOIN vehiculo v ON o.id_vehiculo = v.id_vehiculo
                LEFT JOIN historial_vehiculo h ON o.id_orden = h.id_orden
                WHERE o.estado = 'completado' AND h.id_historial IS NULL
            ");
            $stmt->execute();
            $ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $creados = 0;
            foreach ($ordenes as $orden) {
                $datosHistorial = [
                    "id_vehiculo" => $orden["id_vehiculo"],
                    "id_cliente" => $orden["id_cliente"],
                    "id_orden" => $orden["id_orden"],
                    "descripcion" => "Orden completada: " . ($orden["observaciones"] ?? ""),
                    "id_personal" => $orden["id_personal"]
                ];
                
                require_once __DIR__ . "/historial_controlador.php";
                $resultado = ControladorHistorial::mdlRegistrarHistorialOrden($datosHistorial);
                if ($resultado === "ok") {
                    $creados++;
                }
            }

            return $creados;
        } catch (Exception $e) {
            error_log("Error en ctrCrearHistorialOrdenesExistentes: " . $e->getMessage());
            return 0;
        }
    }
}
?>