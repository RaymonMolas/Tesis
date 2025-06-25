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

    // Crear orden de trabajo desde presupuesto
    // REMOVED as per user request to keep presupuesto normal without creating orders


    // Registrar nueva orden con detalles
    static public function ctrRegistrarOrdenTrabajo()
    {
        if (isset($_POST["id_vehiculo"]) && isset($_POST["servicios"])) {
            try {
                // REMOVED handling of creating order from presupuesto as per user request
                /*
                if (isset($_POST["desde_presupuesto"]) && !empty($_POST["desde_presupuesto"])) {
                    $id_presupuesto = intval($_POST["desde_presupuesto"]);
                    $resultado = self::ctrCrearOrdenDesdePresupuesto($id_presupuesto);
                    if ($resultado !== false) {
                        echo '<script>
                            Swal.fire({
                                icon: "success",
                                title: "¡Éxito!",
                                text: "La orden de trabajo ha sido creada desde el presupuesto correctamente",
                                showConfirmButton: true,
                                confirmButtonText: "Facturar"
                            }).then((result) => {
                                if (result.value) {
                                    window.location = "index.php?pagina=nuevo/factura&desde_orden=' . $resultado . '";
                                }
                            });
                        </script>';
                        return "ok";
                    } else {
                        throw new Exception("Error al crear la orden desde el presupuesto");
                    }
                }
                */

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

                // Debug: Mostrar datos POST
                error_log("=== DEBUG ORDEN TRABAJO ===");
                error_log("POST completo: " . print_r($_POST, true));
                error_log("Servicios JSON: " . $_POST["servicios"]);

                // Validar y decodificar servicios
                $servicios = json_decode($_POST["servicios"], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log("Error JSON: " . json_last_error_msg());
                    throw new Exception("Error en los datos de servicios: " . json_last_error_msg());
                }

                if (empty($servicios)) {
                    error_log("Servicios vacío después de decodificar");
                    throw new Exception("Debe agregar al menos un servicio");
                }

                error_log("Servicios decodificados: " . print_r($servicios, true));

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

                error_log("Datos de la orden: " . print_r($datos, true));

                // Insertar orden de trabajo
                $resultado = ModeloOrdenTrabajo::mdlRegistrarOrdenTrabajo($datos);
                error_log("Resultado registro orden: " . print_r($resultado, true));

                // CAMBIO PRINCIPAL: Ahora el modelo devuelve directamente el ID o "error"
                if ($resultado !== "error" && is_numeric($resultado)) {
                    $id_orden = $resultado; // El ID viene directamente del modelo
                    error_log("ID orden obtenido directamente: " . $id_orden);

                    // Insertar detalles de servicios
                    $errores_servicios = array();

                    foreach ($servicios as $index => $servicio) {
                        error_log("Procesando servicio #" . ($index + 1) . ": " . print_r($servicio, true));

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

                        $detalleServicio = array(
                            "id_orden" => (int) $id_orden,
                            "tipo_servicio" => trim($servicio["tipo"]),
                            "descripcion" => trim($servicio["descripcion"]),
                            "cantidad" => (int) $servicio["cantidad"],
                            "precio_unitario" => (float) $servicio["precioUnitario"],
                            "subtotal" => (float) $servicio["subtotal"]
                        );

                        error_log("Datos del servicio #" . ($index + 1) . " para insertar: " . print_r($detalleServicio, true));

                        $resultadoServicio = ModeloOrdenDetalle::mdlRegistrarDetalle($detalleServicio);
                        error_log("Resultado inserción servicio #" . ($index + 1) . ": " . $resultadoServicio);

                        if ($resultadoServicio !== "ok") {
                            $errores_servicios[] = "Error al guardar servicio " . ($index + 1);
                        }
                    }

                    if (!empty($errores_servicios)) {
                        // Si hay errores en servicios, eliminar la orden creada
                        error_log("Errores encontrados, eliminando orden creada");
                        ModeloOrdenTrabajo::mdlEliminarOrdenTrabajo($id_orden);
                        throw new Exception("Errores en los servicios: " . implode(", ", $errores_servicios));
                    }

                    error_log("SUCCESS: Orden de trabajo y servicios registrados correctamente");

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
                    error_log("Error en registro de orden: " . print_r($resultado, true));
                    throw new Exception("Error al registrar la orden de trabajo en la base de datos");
                }

            } catch (Exception $e) {
                error_log("ERROR en ctrRegistrarOrdenTrabajo: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());

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
            error_log("ERROR: Faltan datos POST");
            error_log("POST recibido: " . print_r($_POST, true));
            error_log("id_vehiculo presente: " . (isset($_POST["id_vehiculo"]) ? "SÍ" : "NO"));
            error_log("servicios presente: " . (isset($_POST["servicios"]) ? "SÍ" : "NO"));

            echo '<script>
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Faltan datos obligatorios para crear la orden de trabajo",
                showConfirmButton: true,
                confirmButtonText: "Cerrar"
            });
        </script>';
        }
        return null;
    }

    // Actualizar orden con detalles
static public function ctrActualizarOrdenTrabajo()
{
    if (isset($_POST["id_orden"])) {
        try {
            // Determinar si los servicios vienen en formato JSON o array
            $servicios = null;
            if (isset($_POST["servicios"]) && !empty($_POST["servicios"])) {
                // Formato JSON
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
                    //"descripcion" => "Orden completada: " . ($orden["observaciones"] ?? ""),
                    "id_personal" => $_SESSION["id_personal"] ?? null
                ];
                        require_once __DIR__ . "/historial_controlador.php";
                        error_log("Intentando registrar historial con datos: " . print_r($datosHistorial, true));
                        $resultadoHistorial = ControladorHistorial::mdlRegistrarHistorialOrden($datosHistorial);
                        error_log("Resultado registro historial: " . $resultadoHistorial);
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
                text: "Ocurrió un error al actualizar la orden de trabajo",
                showConfirmButton: true,
                confirmButtonText: "Cerrar"
            });
        </script>';
    }
    return null;
}

    // Eliminar orden y sus detalles
    static public function ctrEliminarOrdenTrabajo()
    {
        if (isset($_POST["eliminarOrden"])) {
            try {
                $id_orden = $_POST["eliminarOrden"];

                error_log("=== ELIMINANDO ORDEN DE TRABAJO ===");
                error_log("ID a eliminar: " . $id_orden);

                // Verificar que la orden existe
                $orden = ModeloOrdenTrabajo::mdlObtenerOrdenTrabajo($id_orden);
                if (!$orden) {
                    throw new Exception("La orden de trabajo no existe");
                }

                error_log("Orden encontrada: " . print_r($orden, true));

                // Primero eliminar los detalles
                $resultadoDetalles = ModeloOrdenDetalle::mdlEliminarDetalles($id_orden);
                error_log("Resultado eliminación detalles: " . $resultadoDetalles);

                if ($resultadoDetalles !== "ok") {
                    throw new Exception("Error al eliminar los detalles de la orden");
                }

                // Luego eliminar la orden
                $resultadoOrden = ModeloOrdenTrabajo::mdlEliminarOrdenTrabajo($id_orden);
                error_log("Resultado eliminación orden: " . $resultadoOrden);

                if ($resultadoOrden == "ok") {
                    error_log("SUCCESS: Orden de trabajo eliminada correctamente");

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
                    return "ok";
                } else {
                    throw new Exception("Error al eliminar la orden de trabajo de la base de datos");
                }

            } catch (Exception $e) {
                error_log("ERROR en ctrEliminarOrdenTrabajo: " . $e->getMessage());

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
            error_log("ERROR: No se recibió el ID de la orden a eliminar");

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
    error_log("ctrCambiarEstadoOrden llamado con POST: " . print_r($_POST, true) . " y SESSION id_personal: " . ($_SESSION["id_personal"] ?? "null"));
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
                    // Obtener datos de la orden para el historial
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
                        error_log("Intentando registrar historial con datos: " . print_r($datosHistorial, true));
                        $resultadoHistorial = ControladorHistorial::mdlRegistrarHistorialOrden($datosHistorial);
                        error_log("Resultado registro historial: " . $resultadoHistorial);
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
                SELECT o.*, v.id_cliente 
                FROM ordentrabajo o
                LEFT JOIN historialvehiculo h ON o.id_orden = h.id_orden
                JOIN vehiculo v ON o.id_vehiculo = v.id_vehiculo
                WHERE o.estado = 'completado' 
                AND h.id_orden IS NULL
            ");
            
            $stmt->execute();
            $ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $creados = 0;
            $errores = 0;
            
            foreach ($ordenes as $orden) {
                $datosHistorial = [
                    "id_vehiculo" => $orden["id_vehiculo"],
                    //"id_cliente" => $orden["id_cliente"], // Removed because column does not exist
                    "id_orden" => $orden["id_orden"],
                    //"descripcion" => "Orden completada: " . ($orden["observaciones"] ?? ""),
                    "id_personal" => $orden["id_personal"] ?? 1  // Usar ID 1 como fallback
                ];
                
                require_once __DIR__ . "/historial_controlador.php";
                $resultado = ControladorHistorial::mdlRegistrarHistorialOrden($datosHistorial);
                
                if ($resultado === "ok") {
                    $creados++;
                    error_log("Historial creado para orden " . $orden["id_orden"]);
                } else {
                    $errores++;
                    error_log("Error al crear historial para orden " . $orden["id_orden"]);
                }
            }
            
            return [
                "status" => "ok",
                "mensaje" => "Proceso completado. Historiales creados: $creados. Errores: $errores",
                "creados" => $creados,
                "errores" => $errores
            ];
            
        } catch (Exception $e) {
            error_log("Error en ctrCrearHistorialOrdenesExistentes: " . $e->getMessage());
            return [
                "status" => "error",
                "mensaje" => "Error al procesar las órdenes: " . $e->getMessage()
            ];
        }
    }

}
?>
