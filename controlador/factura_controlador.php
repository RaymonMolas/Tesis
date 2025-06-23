<?php
require_once "../modelo/modelo_factura.php";
require_once "../modelo/modelo_detalle_factura.php";

class FacturaControlador {
    
    // Listar todas las facturas
    static public function ctrListarFacturas() {
        try {
            return ModeloFactura::mdlListarFacturas();
        } catch (Exception $e) {
            error_log("Error en ctrListarFacturas: " . $e->getMessage());
            return array();
        }
    }

    // Obtener una factura específica con sus detalles
    static public function ctrObtenerFactura($id) {
        try {
            return ModeloFactura::mdlObtenerFactura($id);
        } catch (Exception $e) {
            error_log("Error en ctrObtenerFactura: " . $e->getMessage());
            return false;
        }
    }

    // Registrar nueva factura
    static public function ctrRegistrarFactura() {
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

                // Validar y decodificar detalles
                $detalles = json_decode($_POST["detalles"], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception("Error en los datos de la factura: " . json_last_error_msg());
                }

                if (empty($detalles)) {
                    throw new Exception("Debe agregar al menos un producto o servicio");
                }

                // Calcular totales
                $subtotal = 0;
                foreach ($detalles as $detalle) {
                    $subtotal += $detalle["subtotal"];
                }

                $descuento = floatval($_POST["descuento"] ?? 0);
                $iva = floatval($_POST["iva"] ?? 0);
                $total = $subtotal - $descuento + $iva;

                // Preparar datos de la factura
                $datos = array(
                    "id_cliente" => (int) $_POST["id_cliente"],
                    "id_personal" => (int) $_SESSION["id_personal"],
                    "id_orden" => !empty($_POST["id_orden"]) ? (int) $_POST["id_orden"] : null,
                    "id_presupuesto" => !empty($_POST["id_presupuesto"]) ? (int) $_POST["id_presupuesto"] : null,
                    "fecha_emision" => date('Y-m-d H:i:s'),
                    "tipo_factura" => $_POST["tipo_factura"] ?? "contado",
                    "subtotal" => $subtotal,
                    "descuento" => $descuento,
                    "iva" => $iva,
                    "total" => $total,
                    "estado" => $_POST["estado"] ?? "pendiente",
                    "metodo_pago" => $_POST["metodo_pago"] ?? "efectivo",
                    "observaciones" => $_POST["observaciones"] ?? "",
                    //"ruc_empresa" => $_POST["ruc_empresa"] ?? "",
                    //"direccion_empresa" => $_POST["direccion_empresa"] ?? "",
                    //"timbrado_numero" => $_POST["timbrado_numero"] ?? "",
                    //"timbrado_vencimiento" => $_POST["timbrado_vencimiento"] ?? ""
                );

                error_log("=== REGISTRANDO FACTURA ===");
                error_log("Datos de la factura: " . print_r($datos, true));

                // Insertar factura
                $id_factura = ModeloFactura::mdlRegistrarFactura($datos);

                if ($id_factura === "error" || !is_numeric($id_factura)) {
                    throw new Exception("Error al registrar la factura en la base de datos");
                }

                error_log("Factura creada con ID: " . $id_factura);

                // Insertar detalles
                $errores_detalles = array();

                foreach ($detalles as $index => $detalle) {
                    // Validar stock para productos
                    if ($detalle["tipo"] === "producto" && !empty($detalle["id_producto"])) {
                        $producto = ProductoControlador::ctrObtenerProducto($detalle["id_producto"]);
                        if (!$producto || $producto["stock"] < $detalle["cantidad"]) {
                            $errores_detalles[] = "Stock insuficiente para: " . $detalle["descripcion"];
                            continue;
                        }
                    }

                    $datosDetalle = array(
                        "id_factura" => (int) $id_factura,
                        "tipo" => $detalle["tipo"],
                        "id_producto" => !empty($detalle["id_producto"]) ? (int) $detalle["id_producto"] : null,
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
                    throw new Exception("Errores en los detalles: " . implode(", ", $errores_detalles));
                }

                error_log("SUCCESS: Factura y detalles registrados correctamente");

                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡Éxito!",
                        text: "La factura ha sido registrada correctamente",
                        showConfirmButton: true,
                        confirmButtonText: "Ver Factura"
                    }).then((result) => {
                        if (result.value) {
                            window.location = "index.php?pagina=ver/factura&id=' . $id_factura . '";
                        }
                    });
                </script>';
                return "ok";

            } catch (Exception $e) {
                error_log("ERROR en ctrRegistrarFactura: " . $e->getMessage());

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

    // Actualizar estado de factura
    static public function ctrActualizarEstadoFactura() {
        if (isset($_POST["id_factura"]) && isset($_POST["estado"])) {
            try {
                $resultado = ModeloFactura::mdlActualizarEstadoFactura($_POST["id_factura"], $_POST["estado"]);
                
                if ($resultado == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡Éxito!",
                            text: "El estado de la factura ha sido actualizado",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location.reload();
                            }
                        });
                    </script>';
                    return "ok";
                }
            } catch (Exception $e) {
                error_log("Error en ctrActualizarEstadoFactura: " . $e->getMessage());
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

    // Anular factura
    static public function ctrAnularFactura() {
        if (isset($_POST["anular_factura"]) && isset($_POST["motivo_anulacion"])) {
            try {
                $resultado = ModeloFactura::mdlAnularFactura($_POST["anular_factura"], $_POST["motivo_anulacion"]);
                
                if ($resultado == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡Factura Anulada!",
                            text: "La factura ha sido anulada y el stock restaurado",
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
                error_log("Error en ctrAnularFactura: " . $e->getMessage());
            }

            echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Ocurrió un error al anular la factura",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';
        }
        return null;
    }

    // Obtener facturas de un cliente
    static public function ctrObtenerFacturasCliente($id_cliente) {
        try {
            return ModeloFactura::mdlObtenerFacturasCliente($id_cliente);
        } catch (Exception $e) {
            error_log("Error en ctrObtenerFacturasCliente: " . $e->getMessage());
            return array();
        }
    }

    // Obtener estadísticas de facturación
    static public function ctrEstadisticasFacturacion($fecha_inicio = null, $fecha_fin = null) {
        try {
            return ModeloFactura::mdlEstadisticasFacturacion($fecha_inicio, $fecha_fin);
        } catch (Exception $e) {
            error_log("Error en ctrEstadisticasFacturacion: " . $e->getMessage());
            return array();
        }
    }

    // Crear factura desde orden de trabajo
    static public function ctrFacturarOrdenTrabajo($id_orden) {
        try {
            // Obtener orden con detalles
            $orden = OrdenTrabajoControlador::ctrObtenerOrdenTrabajo($id_orden);
            if (!$orden || $orden['facturado'] == 1) {
                return "error_orden";
            }

            // Obtener vehículo y cliente
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
    static public function ctrFacturarPresupuesto($id_presupuesto) {
        try {
            // Obtener presupuesto con detalles
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