<?php

require_once __DIR__ . "/../modelo/modelo_presupuesto.php";
require_once __DIR__ . "/../modelo/modelo_detalle_presupuesto.php";

class PresupuestoControlador
{
    // Listar todos los presupuestos
    static public function ctrListarPresupuestos()
    {
        try {
            return ModeloPresupuesto::mdlListarPresupuestos();
        } catch (Exception $e) {
            error_log("Error en ctrListarPresupuestos: " . $e->getMessage());
            return array();
        }
    }

    // Obtener un presupuesto específico
    static public function ctrObtenerPresupuesto($id)
    {
        try {
            $presupuesto = ModeloPresupuesto::mdlObtenerPresupuesto($id);
            if ($presupuesto) {
                $presupuesto['detalles'] = ModeloDetallePresupuesto::mdlObtenerDetalles($id);
            }
            return $presupuesto;
        } catch (Exception $e) {
            error_log("Error en ctrObtenerPresupuesto: " . $e->getMessage());
            return false;
        }
    }

    // Obtener detalles de un presupuesto
    static public function ctrObtenerDetallesPresupuesto($id)
    {
        try {
            return ModeloDetallePresupuesto::mdlObtenerDetalles($id);
        } catch (Exception $e) {
            error_log("Error en ctrObtenerDetallesPresupuesto: " . $e->getMessage());
            return array();
        }
    }

    // Registrar nuevo presupuesto con detalles
    static public function ctrRegistrarPresupuesto()
    {
        if (isset($_POST["id_vehiculo"]) && isset($_POST["detalles"])) {
            try {
                // Validar sesión
                if (!isset($_SESSION["id_personal"])) {
                    throw new Exception("No hay sesión de personal activa");
                }

                // Validar y decodificar detalles
                $detalles = json_decode($_POST["detalles"], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception("Error en formato de detalles: " . json_last_error_msg());
                }

                if (empty($detalles)) {
                    throw new Exception("Debe agregar al menos un detalle");
                }

                // Preparar datos del presupuesto
                $datos = array(
                    "id_vehiculo" => $_POST["id_vehiculo"],
                    "id_personal" => $_SESSION["id_personal"],
                    "fecha_emision" => date('Y-m-d H:i:s'),
                    "fecha_validez" => $_POST["fecha_validez"],
                    "estado" => "pendiente",
                    "total" => $_POST["total"],
                    "observaciones" => $_POST["observaciones"] ?? ""
                );

                // Insertar presupuesto
                $id_presupuesto = ModeloPresupuesto::mdlRegistrarPresupuesto($datos);

                if ($id_presupuesto === "error" || !is_numeric($id_presupuesto)) {
                    throw new Exception("Error al registrar el presupuesto en la base de datos");
                }

                // Insertar detalles uno por uno
                $errores_detalles = array();

                foreach ($detalles as $index => $detalle) {
                    // Validar cada detalle
                    if (empty($detalle["descripcion"])) {
                        $errores_detalles[] = "Descripción vacía en item " . ($index + 1);
                        continue;
                    }

                    if (empty($detalle["cantidad"]) || $detalle["cantidad"] <= 0) {
                        $errores_detalles[] = "Cantidad inválida en item " . ($index + 1);
                        continue;
                    }

                    if (empty($detalle["precio_unitario"]) || $detalle["precio_unitario"] <= 0) {
                        $errores_detalles[] = "Precio inválido en item " . ($index + 1);
                        continue;
                    }

                    $datosDetalle = array(
                        "id_presupuesto" => (int) $id_presupuesto,
                        "descripcion" => trim($detalle["descripcion"]),
                        "cantidad" => (int) $detalle["cantidad"],
                        "precio_unitario" => (float) $detalle["precio_unitario"],
                        "subtotal" => (float) $detalle["subtotal"],
                        "tipo" => $detalle["tipo"] ?? "producto",
                        "id_producto" => isset($detalle["id_producto"]) && !empty($detalle["id_producto"]) ? (int) $detalle["id_producto"] : null
                    );

                    $resultado = ModeloDetallePresupuesto::mdlRegistrarDetalle($datosDetalle);

                    if ($resultado !== "ok") {
                        $errores_detalles[] = "Error al guardar item " . ($index + 1);
                    }
                }

                if (!empty($errores_detalles)) {
                    // Si hay errores en detalles, eliminar el presupuesto creado
                    ModeloPresupuesto::mdlEliminarPresupuesto($id_presupuesto);
                    throw new Exception("Errores en los detalles: " . implode(", ", $errores_detalles));
                }

                echo '<script>
                Swal.fire({
                    icon: "success", 
                    title: "¡Éxito!",
                    text: "El presupuesto ha sido registrado correctamente",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                }).then((result) => {
                    if (result.value) {
                        window.location = "index.php?pagina=tabla/presupuestos";
                    }
                });
            </script>';
                return "ok";

            } catch (Exception $e) {
                error_log("Error en ctrRegistrarPresupuesto: " . $e->getMessage());

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
                text: "Faltan datos obligatorios para crear el presupuesto",
                showConfirmButton: true,
                confirmButtonText: "Cerrar"
            });
        </script>';
        }
        return null;
    }

    // Actualizar presupuesto con detalles
    static public function ctrActualizarPresupuesto()
    {
        if (isset($_POST["id_presupuesto"])) {
            try {
                // Determinar si los detalles vienen en formato JSON o array
                $detalles = null;
                if (isset($_POST["detalles"]) && !empty($_POST["detalles"])) {
                    // Formato JSON (desde nuevo formulario)
                    $detalles = json_decode($_POST["detalles"], true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new Exception("Error en formato de detalles: " . json_last_error_msg());
                    }
                } else if (isset($_POST["descripcion"]) && is_array($_POST["descripcion"])) {
                    // Formato array (desde formulario de edición tradicional)
                    $detalles = [];
                    for ($i = 0; $i < count($_POST["descripcion"]); $i++) {
                        if (!empty($_POST["descripcion"][$i])) {
                            $detalles[] = [
                                "tipo" => $_POST["tipo"][$i] ?? "servicio",
                                "id_producto" => !empty($_POST["id_producto"][$i]) ? $_POST["id_producto"][$i] : null,
                                "descripcion" => $_POST["descripcion"][$i],
                                "cantidad" => $_POST["cantidad"][$i],
                                "precio_unitario" => $_POST["precio_unitario"][$i],
                                "subtotal" => $_POST["subtotal"][$i]
                            ];
                        }
                    }
                }

                if (empty($detalles)) {
                    throw new Exception("Debe tener al menos un detalle en el presupuesto");
                }

                $datos = array(
                    "id_presupuesto" => $_POST["id_presupuesto"],
                    "id_vehiculo" => $_POST["id_vehiculo"],
                    "fecha_validez" => $_POST["fecha_validez"],
                    "estado" => $_POST["estado"],
                    "total" => $_POST["total"],
                    "observaciones" => $_POST["observaciones"]
                );

                $respuesta = ModeloPresupuesto::mdlActualizarPresupuesto($datos);

                if ($respuesta == "ok") {
                    // Eliminar detalles anteriores
                    ModeloDetallePresupuesto::mdlEliminarDetallesPresupuesto($_POST["id_presupuesto"]);

                    // Registrar nuevos detalles
                    $error = false;

                    foreach ($detalles as $detalle) {
                        $datosDetalle = array(
                            "id_presupuesto" => $_POST["id_presupuesto"],
                            "descripcion" => $detalle["descripcion"],
                            "cantidad" => $detalle["cantidad"],
                            "precio_unitario" => $detalle["precio_unitario"],
                            "subtotal" => $detalle["subtotal"],
                            "tipo" => $detalle["tipo"],
                            "id_producto" => $detalle["id_producto"] ?? null
                        );

                        if (ModeloDetallePresupuesto::mdlRegistrarDetalle($datosDetalle) !== "ok") {
                            $error = true;
                            break;
                        }
                    }

                    if (!$error) {
                        echo '<script>
                            Swal.fire({
                                icon: "success",
                                title: "¡Éxito!",
                                text: "El presupuesto ha sido actualizado",
                                showConfirmButton: true,
                                confirmButtonText: "Cerrar"
                            }).then((result) => {
                                if (result.value) {
                                    window.location = "index.php?pagina=tabla/presupuestos";
                                }
                            });
                        </script>';
                        return "ok";
                    }
                }
            } catch (Exception $e) {
                error_log("Error en ctrActualizarPresupuesto: " . $e->getMessage());
            }

            echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Ocurrió un error al actualizar el presupuesto",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';
        }
        return null;
    }

    // Eliminar presupuesto
    static public function ctrEliminarPresupuesto()
    {
        if (isset($_POST["eliminarPresupuesto"])) {
            try {
                $respuesta = ModeloPresupuesto::mdlEliminarPresupuesto($_POST["eliminarPresupuesto"]);

                if ($respuesta == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡Éxito!",
                            text: "El presupuesto ha sido eliminado correctamente",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "index.php?pagina=tabla/presupuestos";
                            }
                        });
                    </script>';
                } else {
                    throw new Exception("No se pudo eliminar el presupuesto");
                }
                return "ok";
            } catch (Exception $e) {
                error_log("Error en ctrEliminarPresupuesto: " . $e->getMessage());

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
                    text: "No se especificó qué presupuesto eliminar",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';
        }
        return null;
    }

    // Cambiar estado del presupuesto
    static public function ctrCambiarEstadoPresupuesto()
    {
        if (isset($_POST["id_presupuesto"]) && isset($_POST["nuevo_estado"])) {
            try {
                $respuesta = ModeloPresupuesto::mdlActualizarEstado($_POST["id_presupuesto"], $_POST["nuevo_estado"]);

                if ($respuesta == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡Éxito!",
                            text: "El estado del presupuesto ha sido actualizado",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "index.php?pagina=tabla/presupuestos";
                            }
                        });
                    </script>';
                    return "ok";
                }
            } catch (Exception $e) {
                error_log("Error en ctrCambiarEstadoPresupuesto: " . $e->getMessage());
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

    // Obtener presupuestos por cliente
    static public function ctrObtenerPresupuestosPorCliente($id_cliente)
    {
        try {
            return ModeloPresupuesto::mdlObtenerPresupuestosPorCliente($id_cliente);
        } catch (Exception $e) {
            error_log("Error en ctrObtenerPresupuestosPorCliente: " . $e->getMessage());
            return array();
        }
    }

    // Obtener estadísticas de presupuestos
    static public function ctrObtenerEstadisticasPresupuestos()
    {
        try {
            return ModeloPresupuesto::mdlObtenerEstadisticas();
        } catch (Exception $e) {
            error_log("Error en ctrObtenerEstadisticasPresupuestos: " . $e->getMessage());
            return array();
        }
    }

    // Marcar presupuesto como facturado
    static public function ctrMarcarComoFacturado($id_presupuesto)
    {
        try {
            return ModeloPresupuesto::mdlMarcarComoFacturado($id_presupuesto);
        } catch (Exception $e) {
            error_log("Error en ctrMarcarComoFacturado: " . $e->getMessage());
            return "error";
        }
    }
}
?>