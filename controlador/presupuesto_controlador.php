<?php
require_once "../modelo/modelo_presupuesto.php";
require_once "../modelo/modelo_detalle_presupuesto.php";

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

    // Obtener un presupuesto específico con sus detalles
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

    // Registrar nuevo presupuesto con detalles
    static public function ctrRegistrarPresupuesto()
    {
        if (isset($_POST["id_vehiculo"]) && isset($_POST["detalles"])) {
            try {
                // Validar sesión
                if (!isset($_SESSION["id_personal"])) {
                    throw new Exception("No hay sesión de personal activa");
                }

                // Validar datos básicos
                if (empty($_POST["id_vehiculo"])) {
                    throw new Exception("Debe seleccionar un vehículo");
                }

                if (empty($_POST["fecha_validez"])) {
                    throw new Exception("Debe establecer una fecha de validez");
                }

                if (empty($_POST["total"]) || $_POST["total"] <= 0) {
                    throw new Exception("El total debe ser mayor a 0");
                }

                // Validar y decodificar detalles
                $detalles = json_decode($_POST["detalles"], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception("Error en los datos del presupuesto: " . json_last_error_msg());
                }

                if (empty($detalles)) {
                    throw new Exception("Debe agregar al menos un producto o servicio");
                }

                // Preparar datos del presupuesto
                $datos = array(
                    "id_vehiculo" => (int) $_POST["id_vehiculo"],
                    "id_personal" => (int) $_SESSION["id_personal"],
                    "fecha_emision" => date('Y-m-d H:i:s'),
                    "fecha_validez" => $_POST["fecha_validez"],
                    "estado" => "pendiente",
                    "total" => (float) $_POST["total"],
                    "observaciones" => $_POST["observaciones"] ?? ""
                );

                error_log("=== REGISTRANDO PRESUPUESTO ===");
                error_log("Datos del presupuesto: " . print_r($datos, true));

                // Insertar presupuesto
                $id_presupuesto = ModeloPresupuesto::mdlRegistrarPresupuesto($datos);

                if ($id_presupuesto === "error" || !is_numeric($id_presupuesto)) {
                    throw new Exception("Error al registrar el presupuesto en la base de datos");
                }

                error_log("Presupuesto creado con ID: " . $id_presupuesto);

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

                    error_log("Insertando detalle " . ($index + 1) . ": " . print_r($datosDetalle, true));

                    $resultado = ModeloDetallePresupuesto::mdlRegistrarDetalle($datosDetalle);

                    if ($resultado !== "ok") {
                        $errores_detalles[] = "Error al guardar item " . ($index + 1);
                        error_log("Error al insertar detalle " . ($index + 1) . ": " . $resultado);
                    }
                }

                if (!empty($errores_detalles)) {
                    // Si hay errores en detalles, eliminar el presupuesto creado
                    ModeloPresupuesto::mdlEliminarPresupuesto($id_presupuesto);
                    throw new Exception("Errores en los detalles: " . implode(", ", $errores_detalles));
                }

                error_log("SUCCESS: Presupuesto y detalles registrados correctamente");

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
                error_log("ERROR en ctrRegistrarPresupuesto: " . $e->getMessage());

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
            error_log("ERROR: Faltan datos POST - id_vehiculo: " . (isset($_POST["id_vehiculo"]) ? "OK" : "NO") . ", detalles: " . (isset($_POST["detalles"]) ? "OK" : "NO"));

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

    // Eliminar presupuesto y sus detalles
    static public function ctrEliminarPresupuesto()
    {
        if (isset($_POST["eliminarPresupuesto"])) {
            try {
                $id_presupuesto = $_POST["eliminarPresupuesto"];

                error_log("=== ELIMINANDO PRESUPUESTO ===");
                error_log("ID a eliminar: " . $id_presupuesto);

                // Verificar que el presupuesto existe
                $presupuesto = ModeloPresupuesto::mdlObtenerPresupuesto($id_presupuesto);
                if (!$presupuesto) {
                    throw new Exception("El presupuesto no existe");
                }

                error_log("Presupuesto encontrado: " . print_r($presupuesto, true));

                // Primero eliminar los detalles
                $resultadoDetalles = ModeloDetallePresupuesto::mdlEliminarDetallesPresupuesto($id_presupuesto);
                error_log("Resultado eliminación detalles: " . $resultadoDetalles);

                if ($resultadoDetalles !== "ok") {
                    throw new Exception("Error al eliminar los detalles del presupuesto");
                }

                // Luego eliminar el presupuesto
                $resultadoPresupuesto = ModeloPresupuesto::mdlEliminarPresupuesto($id_presupuesto);
                error_log("Resultado eliminación presupuesto: " . $resultadoPresupuesto);

                if ($resultadoPresupuesto == "ok") {
                    error_log("SUCCESS: Presupuesto eliminado correctamente");

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
                    return "ok";
                } else {
                    throw new Exception("Error al eliminar el presupuesto de la base de datos");
                }

            } catch (Exception $e) {
                error_log("ERROR en ctrEliminarPresupuesto: " . $e->getMessage());

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
            error_log("ERROR: No se recibió el ID del presupuesto a eliminar");

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

    // Actualizar estado del presupuesto
    static public function ctrActualizarEstado()
    {
        if (isset($_POST["id_presupuesto"]) && isset($_POST["estado"])) {
            try {
                $respuesta = ModeloPresupuesto::mdlActualizarEstado(
                    $_POST["id_presupuesto"],
                    $_POST["estado"]
                );

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
                error_log("Error en ctrActualizarEstado: " . $e->getMessage());
            }

            echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Ocurrió un error al actualizar el estado",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';
        }
        return null;
    }
}
?>