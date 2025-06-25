<?php

require_once __DIR__ . "/../modelo/modelo_factura.php";
require_once __DIR__ . "/../modelo/modelo_detalle_factura.php";

class FacturaControlador
{
    // Listar todas las facturas
    static public function ctrListarFacturas()
    {
        try {
            return ModeloFactura::mdlListarFacturas();
        } catch (Exception $e) {
            error_log("Error en ctrListarFacturas: " . $e->getMessage());
            return array();
        }
    }

    // Obtener una factura específica
    static public function ctrObtenerFactura($id)
    {
        try {
            $factura = ModeloFactura::mdlObtenerFactura($id);
            if ($factura) {
                $factura['detalles'] = ModeloDetalleFactura::mdlObtenerDetalles($id);
            }
            return $factura;
        } catch (Exception $e) {
            error_log("Error en ctrObtenerFactura: " . $e->getMessage());
            return false;
        }
    }

    // Registrar nueva factura
    static public function ctrRegistrarFactura()
    {
        if (isset($_POST["id_cliente"]) && isset($_POST["detalles"])) {
            try {
                // Validar sesión
                if (!isset($_SESSION["id_personal"])) {
                    throw new Exception("No hay sesión de personal activa");
                }

                // Validar datos básicos
                if (empty($_POST["id_cliente"])) {
                    throw new Exception("Debe seleccionar un cliente");
                }

                if (empty($_POST["total"]) || $_POST["total"] <= 0) {
                    throw new Exception("El total debe ser mayor a 0");
                }

                // Validar y decodificar detalles
                $detalles = json_decode($_POST["detalles"], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception("Error en los datos de la factura: " . json_last_error_msg());
                }

                if (empty($detalles)) {
                    throw new Exception("Debe agregar al menos un producto o servicio");
                }

                // Preparar datos de la factura
                $datos = array(
                    "id_cliente" => (int) $_POST["id_cliente"],
                    "id_personal" => (int) $_SESSION["id_personal"],
                    "id_orden" => isset($_POST["id_orden"]) ? (int) $_POST["id_orden"] : null,
                    "id_presupuesto" => isset($_POST["id_presupuesto"]) ? (int) $_POST["id_presupuesto"] : null,
                    "fecha_emision" => date('Y-m-d H:i:s'),
                    "tipo_factura" => $_POST["tipo_factura"] ?? "contado",
                    "subtotal" => (float) $_POST["subtotal"],
                    "descuento" => (float) ($_POST["descuento"] ?? 0),
                    "iva" => (float) ($_POST["iva"] ?? 0),
                    "total" => (float) $_POST["total"],
                    "estado" => "pendiente",
                    "metodo_pago" => $_POST["metodo_pago"] ?? "efectivo",
                    "observaciones" => $_POST["observaciones"] ?? ""
                );

                // Insertar factura
                $id_factura = ModeloFactura::mdlRegistrarFactura($datos);

                if ($id_factura === "error" || !is_numeric($id_factura)) {
                    throw new Exception("Error al registrar la factura en la base de datos");
                }

                // Insertar detalles
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
                        "id_factura" => (int) $id_factura,
                        "tipo" => $detalle["tipo"] ?? "producto",
                        "id_producto" => isset($detalle["id_producto"]) && !empty($detalle["id_producto"]) ? (int) $detalle["id_producto"] : null,
                        "descripcion" => trim($detalle["descripcion"]),
                        "cantidad" => (int) $detalle["cantidad"],
                        "precio_unitario" => (float) $detalle["precio_unitario"],
                        "descuento" => (float) ($detalle["descuento"] ?? 0),
                        "subtotal" => (float) $detalle["subtotal"]
                    );

                    $resultado = ModeloDetalleFactura::mdlRegistrarDetalle($datosDetalle);

                    if ($resultado !== "ok") {
                        $errores_detalles[] = "Error al guardar item " . ($index + 1);
                    }
                }

                if (!empty($errores_detalles)) {
                    // Si hay errores en detalles, eliminar la factura creada
                    ModeloFactura::mdlEliminarFactura($id_factura);
                    throw new Exception("Errores en los detalles: " . implode(", ", $errores_detalles));
                }

                // Marcar orden como facturada si existe
                if (isset($_POST["id_orden"]) && !empty($_POST["id_orden"])) {
                    require_once __DIR__ . "/orden_trabajo_controlador.php";
                    ModeloOrdenTrabajo::mdlMarcarComoFacturada($_POST["id_orden"]);
                }

                // Marcar presupuesto como facturado si existe
                if (isset($_POST["id_presupuesto"]) && !empty($_POST["id_presupuesto"])) {
                    require_once __DIR__ . "/presupuesto_controlador.php";
                    ModeloPresupuesto::mdlMarcarComoFacturado($_POST["id_presupuesto"]);
                }

                echo '<script>
                Swal.fire({
                    icon: "success", 
                    title: "¡Éxito!",
                    text: "La factura ha sido registrada correctamente",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                }).then((result) => {
                    if (result.value) {
                        window.location = "index.php?pagina=tabla/facturas";
                    }
                });
            </script>';
                return "ok";

            } catch (Exception $e) {
                error_log("Error en ctrRegistrarFactura: " . $e->getMessage());

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
                text: "Faltan datos obligatorios para crear la factura",
                showConfirmButton: true,
                confirmButtonText: "Cerrar"
            });
        </script>';
        }
        return null;
    }

    // Actualizar factura
    static public function ctrActualizarFactura()
    {
        if (isset($_POST["id_factura"])) {
            try {
                // Validar y decodificar detalles
                $detalles = null;
                if (isset($_POST["detalles"]) && !empty($_POST["detalles"])) {
                    $detalles = json_decode($_POST["detalles"], true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new Exception("Error en formato de detalles: " . json_last_error_msg());
                    }
                }

                if (empty($detalles)) {
                    throw new Exception("Debe tener al menos un detalle en la factura");
                }

                $datos = array(
                    "id_factura" => $_POST["id_factura"],
                    "tipo_factura" => $_POST["tipo_factura"],
                    "subtotal" => $_POST["subtotal"],
                    "descuento" => $_POST["descuento"] ?? 0,
                    "iva" => $_POST["iva"] ?? 0,
                    "total" => $_POST["total"],
                    "estado" => $_POST["estado"],
                    "metodo_pago" => $_POST["metodo_pago"],
                    "observaciones" => $_POST["observaciones"]
                );

                $respuesta = ModeloFactura::mdlActualizarFactura($datos);

                if ($respuesta == "ok") {
                    // Eliminar detalles anteriores
                    ModeloDetalleFactura::mdlEliminarDetallesFactura($_POST["id_factura"]);

                    // Registrar nuevos detalles
                    $error = false;

                    foreach ($detalles as $detalle) {
                        $datosDetalle = array(
                            "id_factura" => $_POST["id_factura"],
                            "tipo" => $detalle["tipo"],
                            "id_producto" => $detalle["id_producto"] ?? null,
                            "descripcion" => $detalle["descripcion"],
                            "cantidad" => $detalle["cantidad"],
                            "precio_unitario" => $detalle["precio_unitario"],
                            "descuento" => $detalle["descuento"] ?? 0,
                            "subtotal" => $detalle["subtotal"]
                        );

                        if (ModeloDetalleFactura::mdlRegistrarDetalle($datosDetalle) !== "ok") {
                            $error = true;
                            break;
                        }
                    }

                    if (!$error) {
                        echo '<script>
                            Swal.fire({
                                icon: "success",
                                title: "¡Éxito!",
                                text: "La factura ha sido actualizada",
                                showConfirmButton: true,
                                confirmButtonText: "Cerrar"
                            }).then((result) => {
                                if (result.value) {
                                    window.location = "index.php?pagina=tabla/facturas";
                                }
                            });
                        </script>';
                        return "ok";
                    }
                }
            } catch (Exception $e) {
                error_log("Error en ctrActualizarFactura: " . $e->getMessage());
            }

            echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Ocurrió un error al actualizar la factura",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';
        }
        return null;
    }

    // Eliminar factura
    static public function ctrEliminarFactura()
    {
        if (isset($_POST["eliminarFactura"])) {
            try {
                $respuesta = ModeloFactura::mdlEliminarFactura($_POST["eliminarFactura"]);

                if ($respuesta == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡Éxito!",
                            text: "La factura ha sido eliminada correctamente",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "index.php?pagina=tabla/facturas";
                            }
                        });
                    </script>';
                } else {
                    throw new Exception("No se pudo eliminar la factura");
                }
                return "ok";
            } catch (Exception $e) {
                error_log("Error en ctrEliminarFactura: " . $e->getMessage());

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
                    text: "No se especificó qué factura eliminar",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';
        }
        return null;
    }

    // Cambiar estado de factura
    static public function ctrCambiarEstadoFactura()
    {
        if (isset($_POST["id_factura"]) && isset($_POST["nuevo_estado"])) {
            try {
                $respuesta = ModeloFactura::mdlActualizarEstado($_POST["id_factura"], $_POST["nuevo_estado"]);

                if ($respuesta == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡Éxito!",
                            text: "El estado de la factura ha sido actualizado",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "index.php?pagina=tabla/facturas";
                            }
                        });
                    </script>';
                    return "ok";
                }
            } catch (Exception $e) {
                error_log("Error en ctrCambiarEstadoFactura: " . $e->getMessage());
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

    // Obtener facturas por cliente
    static public function ctrObtenerFacturasPorCliente($id_cliente)
    {
        try {
            return ModeloFactura::mdlObtenerFacturasPorCliente($id_cliente);
        } catch (Exception $e) {
            error_log("Error en ctrObtenerFacturasPorCliente: " . $e->getMessage());
            return array();
        }
    }

    // Obtener estadísticas de facturación
    static public function ctrEstadisticasFacturacion($fecha_inicio = null, $fecha_fin = null)
    {
        try {
            return ModeloFactura::mdlEstadisticasFacturacion($fecha_inicio, $fecha_fin);
        } catch (Exception $e) {
            error_log("Error en ctrEstadisticasFacturacion: " . $e->getMessage());
            return array();
        }
    }

    // Crear factura desde orden de trabajo
    static public function ctrFacturarOrdenTrabajo($id_orden)
    {
        try {
            // Obtener orden con detalles
            require_once __DIR__ . "/orden_trabajo_controlador.php";
            $orden = OrdenTrabajoControlador::ctrObtenerOrdenTrabajo($id_orden);
            if (!$orden || $orden['facturado'] == 1) {
                return "error_orden";
            }

            // Obtener vehículo y cliente
            require_once __DIR__ . "/vehiculo_controlador.php";
            $vehiculo = VehiculoControlador::ctrObtenerVehiculo($orden['id_vehiculo']);
            if (!$vehiculo) {
                return "error_vehiculo";
            }

            // Preparar detalles desde la orden
            $detalles = array();
            if (isset($orden['detalles'])) {
                foreach ($orden['detalles'] as $detalle) {
                    $detalles[] = array(
                        "tipo" => "servicio",
                        "id_producto" => null,
                        "descripcion" => $detalle['descripcion'],
                        "cantidad" => $detalle['cantidad'],
                        "precio_unitario" => $detalle['precio_unitario'],
                        "descuento" => 0,
                        "subtotal" => $detalle['subtotal']
                    );
                }
            }

            // Calcular total
            $total = $orden['total'] ?? 0;

            // Preparar datos de factura
            $datos = array(
                "id_cliente" => $vehiculo['id_cliente'],
                "id_personal" => $_SESSION["id_personal"],
                "id_orden" => $id_orden,
                "id_presupuesto" => null,
                "fecha_emision" => date('Y-m-d H:i:s'),
                "tipo_factura" => "contado",
                "subtotal" => $total,
                "descuento" => 0,
                "iva" => 0,
                "total" => $total,
                "estado" => "pendiente",
                "metodo_pago" => "efectivo",
                "observaciones" => "Factura generada desde Orden de Trabajo #" . $id_orden
            );

            // Crear factura
            $id_factura = ModeloFactura::mdlRegistrarFactura($datos);

            if (is_numeric($id_factura)) {
                // Agregar detalles
                foreach ($detalles as $detalle) {
                    $detalle["id_factura"] = $id_factura;
                    ModeloDetalleFactura::mdlRegistrarDetalle($detalle);
                }

                return $id_factura;
            }

            return "error";
        } catch (Exception $e) {
            error_log("Error en ctrFacturarOrdenTrabajo: " . $e->getMessage());
            return "error";
        }
    }

    // Crear factura desde presupuesto
    static public function ctrFacturarPresupuesto($id_presupuesto)
    {
        try {
            // Obtener presupuesto con detalles
            require_once __DIR__ . "/presupuesto_controlador.php";
            $presupuesto = PresupuestoControlador::ctrObtenerPresupuesto($id_presupuesto);
            if (!$presupuesto || $presupuesto['facturado'] == 1 || $presupuesto['estado'] != 'aprobado') {
                return "error_presupuesto";
            }

            // Preparar detalles desde el presupuesto
            $detalles = array();
            if (isset($presupuesto['detalles'])) {
                foreach ($presupuesto['detalles'] as $detalle) {
                    $detalles[] = array(
                        "tipo" => $detalle['tipo'],
                        "id_producto" => $detalle['id_producto'],
                        "descripcion" => $detalle['descripcion'],
                        "cantidad" => $detalle['cantidad'],
                        "precio_unitario" => $detalle['precio_unitario'],
                        "descuento" => 0,
                        "subtotal" => $detalle['subtotal']
                    );
                }
            }

            // Preparar datos de factura
            $datos = array(
                "id_cliente" => $presupuesto['id_cliente'],
                "id_personal" => $_SESSION["id_personal"],
                "id_orden" => null,
                "id_presupuesto" => $id_presupuesto,
                "fecha_emision" => date('Y-m-d H:i:s'),
                "tipo_factura" => "contado",
                "subtotal" => $presupuesto['total'],
                "descuento" => 0,
                "iva" => 0,
                "total" => $presupuesto['total'],
                "estado" => "pendiente",
                "metodo_pago" => "efectivo",
                "observaciones" => "Factura generada desde Presupuesto #" . $id_presupuesto
            );

            // Crear factura
            $id_factura = ModeloFactura::mdlRegistrarFactura($datos);

            if (is_numeric($id_factura)) {
                // Agregar detalles
                foreach ($detalles as $detalle) {
                    $detalle["id_factura"] = $id_factura;
                    ModeloDetalleFactura::mdlRegistrarDetalle($detalle);
                }

                return $id_factura;
            }

            return "error";
        } catch (Exception $e) {
            error_log("Error en ctrFacturarPresupuesto: " . $e->getMessage());
            return "error";
        }
    }
}
?>