<?php
require_once "../modelo/modelo_factura.php";
require_once "../modelo/modelo_detalle_factura.php";
require_once "../modelo/modelo_cliente.php";
require_once "../modelo/modelo_orden_trabajo.php";
require_once "../modelo/modelo_presupuesto.php";

class FacturaControlador
{

    /**
     * Listar todas las facturas
     */
    static public function ctrListarFacturas()
    {
        try {
            return ModeloFactura::mdlListarFacturas();
        } catch (Exception $e) {
            error_log("Error en ctrListarFacturas: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener factura específica con detalles
     */
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

    /**
     * Registrar nueva factura con cliente editable
     */
    static public function ctrRegistrarFactura()
    {
        if (isset($_POST["nombre_cliente"]) && isset($_POST["cedula_ruc"])) {
            try {
                // Validar datos obligatorios
                if (empty(trim($_POST["nombre_cliente"])) || empty(trim($_POST["cedula_ruc"]))) {
                    throw new Exception("El nombre del cliente y cédula/RUC son obligatorios");
                }

                // Determinar si viene de orden o presupuesto
                $id_orden = isset($_POST["id_orden"]) ? intval($_POST["id_orden"]) : null;
                $id_presupuesto = isset($_POST["id_presupuesto"]) ? intval($_POST["id_presupuesto"]) : null;
                $id_cliente_original = isset($_POST["id_cliente_original"]) ? intval($_POST["id_cliente_original"]) : null;

                if (!$id_orden && !$id_presupuesto) {
                    throw new Exception("Debe especificar una orden de trabajo o presupuesto para facturar");
                }

                // Validar que la orden/presupuesto existe y no está facturada
                if ($id_orden) {
                    $orden = ModeloOrdenTrabajo::mdlObtenerOrdenTrabajo($id_orden);
                    if (!$orden) {
                        throw new Exception("La orden de trabajo especificada no existe");
                    }
                    if ($orden['facturado'] == 1) {
                        throw new Exception("Esta orden de trabajo ya ha sido facturada");
                    }
                    if ($orden['estado'] != 'completado') {
                        throw new Exception("Solo se pueden facturar órdenes completadas");
                    }
                }

                if ($id_presupuesto) {
                    $presupuesto = ModeloPresupuesto::mdlObtenerPresupuesto($id_presupuesto);
                    if (!$presupuesto) {
                        throw new Exception("El presupuesto especificado no existe");
                    }
                    if ($presupuesto['facturado'] == 1) {
                        throw new Exception("Este presupuesto ya ha sido facturado");
                    }
                    if ($presupuesto['estado'] != 'aprobado') {
                        throw new Exception("Solo se pueden facturar presupuestos aprobados");
                    }
                }

                // Preparar datos de la factura con información editable del cliente
                $datos = array(
                    "id_cliente" => $id_cliente_original,
                    "id_personal" => $_SESSION["id_personal"],
                    "id_orden" => $id_orden,
                    "id_presupuesto" => $id_presupuesto,
                    "fecha_emision" => date('Y-m-d H:i:s'),
                    "tipo_factura" => $_POST["tipo_factura"] ?? "contado",

                    // Información del cliente EDITABLE
                    "nombre_cliente" => trim($_POST["nombre_cliente"]),
                    "cedula_ruc" => trim($_POST["cedula_ruc"]),
                    "telefono_cliente" => trim($_POST["telefono_cliente"] ?? ""),
                    "email_cliente" => trim($_POST["email_cliente"] ?? ""),
                    "direccion_cliente" => trim($_POST["direccion_cliente"] ?? ""),

                    "observaciones" => trim($_POST["observaciones_factura"] ?? "")
                );

                // Configurar fecha de vencimiento
                if ($datos["tipo_factura"] == "credito" && !empty($_POST["fecha_vencimiento"])) {
                    $datos["fecha_vencimiento"] = $_POST["fecha_vencimiento"];
                } else {
                    $datos["fecha_vencimiento"] = null;
                }

                // Obtener detalles para calcular totales
                $detalles = array();
                $total = 0;

                if ($id_orden) {
                    // Obtener detalles de la orden
                    $detalles_orden = ModeloOrdenDetalle::mdlObtenerDetalles($id_orden);
                    foreach ($detalles_orden as $detalle) {
                        $detalles[] = array(
                            "tipo" => "servicio",
                            "id_producto" => null,
                            "descripcion" => $detalle['descripcion'],
                            "cantidad" => $detalle['cantidad'],
                            "precio_unitario" => $detalle['precio_unitario'],
                            "descuento" => 0,
                            "subtotal" => $detalle['subtotal']
                        );
                        $total += $detalle['subtotal'];
                    }
                }

                if ($id_presupuesto) {
                    // Obtener detalles del presupuesto
                    $detalles_presupuesto = ModeloPresupuesto::mdlObtenerDetallesPresupuesto($id_presupuesto);
                    foreach ($detalles_presupuesto as $detalle) {
                        $detalles[] = array(
                            "tipo" => $detalle['tipo'],
                            "id_producto" => $detalle['id_producto'],
                            "descripcion" => $detalle['descripcion'],
                            "cantidad" => $detalle['cantidad'],
                            "precio_unitario" => $detalle['precio_unitario'],
                            "descuento" => 0,
                            "subtotal" => $detalle['subtotal']
                        );
                        $total += $detalle['subtotal'];
                    }
                }

                if (empty($detalles)) {
                    throw new Exception("No hay servicios o productos para facturar");
                }

                // Completar datos de totales
                $datos["subtotal"] = $total;
                $datos["descuento"] = 0;
                $datos["iva"] = 0; // Paraguay - IVA incluido en precios
                $datos["total"] = $total;
                $datos["estado"] = "pendiente";

                // Registrar factura
                $id_factura = ModeloFactura::mdlRegistrarFactura($datos);

                if (is_numeric($id_factura) && $id_factura > 0) {
                    // Registrar detalles de la factura
                    $errores_detalles = array();

                    foreach ($detalles as $detalle) {
                        $detalle["id_factura"] = $id_factura;
                        $resultado = ModeloDetalleFactura::mdlRegistrarDetalle($detalle);

                        if ($resultado != "ok") {
                            $errores_detalles[] = "Error registrando detalle: " . $detalle['descripcion'];
                        }
                    }

                    if (count($errores_detalles) > 0) {
                        // Si hay errores en detalles, eliminar factura
                        ModeloFactura::mdlEliminarFactura($id_factura);
                        throw new Exception("Error registrando detalles: " . implode(", ", $errores_detalles));
                    }

                    // Marcar orden/presupuesto como facturado
                    if ($id_orden) {
                        ModeloOrdenTrabajo::mdlMarcarComoFacturada($id_orden);
                    }

                    if ($id_presupuesto) {
                        ModeloPresupuesto::mdlMarcarComoFacturado($id_presupuesto);
                    }

                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡Éxito!",
                            text: "La factura ha sido generada correctamente",
                            showConfirmButton: true,
                            confirmButtonText: "Ver Factura"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "index.php?pagina=ver/factura&id=' . $id_factura . '";
                            }
                        });
                    </script>';
                    return "ok";

                } else {
                    throw new Exception("Error al registrar la factura en la base de datos");
                }

            } catch (Exception $e) {
                error_log("Error en ctrRegistrarFactura: " . $e->getMessage());
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error al generar factura",
                        text: "' . addslashes($e->getMessage()) . '",
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return "error";
            }
        }
    }

    /**
     * Actualizar factura existente
     */
    static public function ctrActualizarFactura()
    {
        if (isset($_POST["id_factura"]) && isset($_POST["nombre_cliente"]) && isset($_POST["cedula_ruc"])) {
            try {
                $datos = array(
                    "id_factura" => intval($_POST["id_factura"]),
                    "tipo_factura" => $_POST["tipo_factura"] ?? "contado",

                    // Información del cliente editable
                    "nombre_cliente" => trim($_POST["nombre_cliente"]),
                    "cedula_ruc" => trim($_POST["cedula_ruc"]),
                    "telefono_cliente" => trim($_POST["telefono_cliente"] ?? ""),
                    "email_cliente" => trim($_POST["email_cliente"] ?? ""),
                    "direccion_cliente" => trim($_POST["direccion_cliente"] ?? ""),

                    "estado" => $_POST["estado"] ?? "pendiente",
                    "observaciones" => trim($_POST["observaciones"] ?? "")
                );

                // Configurar fecha de vencimiento
                if ($datos["tipo_factura"] == "credito" && !empty($_POST["fecha_vencimiento"])) {
                    $datos["fecha_vencimiento"] = $_POST["fecha_vencimiento"];
                } else {
                    $datos["fecha_vencimiento"] = null;
                }

                $resultado = ModeloFactura::mdlActualizarFactura($datos);

                if ($resultado == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡Éxito!",
                            text: "La factura ha sido actualizada correctamente",
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location = "index.php?pagina=tabla/facturas";
                        });
                    </script>';
                    return "ok";
                } else {
                    throw new Exception("Error al actualizar la factura");
                }

            } catch (Exception $e) {
                error_log("Error en ctrActualizarFactura: " . $e->getMessage());
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "' . addslashes($e->getMessage()) . '"
                    });
                </script>';
                return "error";
            }
        }
    }

    /**
     * Eliminar factura (anular)
     */
    static public function ctrEliminarFactura($id)
    {
        try {
            // Obtener información de la factura
            $factura = ModeloFactura::mdlObtenerFactura($id);
            if (!$factura) {
                return "factura_no_encontrada";
            }

            // Si está pagada, no se puede anular
            if ($factura['estado'] == 'pagado') {
                return "factura_pagada";
            }

            // Anular factura (cambiar estado)
            $resultado = ModeloFactura::mdlAnularFactura($id);

            if ($resultado == "ok") {
                // Desmarcar orden/presupuesto como facturado si corresponde
                if ($factura['id_orden']) {
                    ModeloOrdenTrabajo::mdlDesmarcarComoFacturada($factura['id_orden']);
                }

                if ($factura['id_presupuesto']) {
                    ModeloPresupuesto::mdlDesmarcarComoFacturado($factura['id_presupuesto']);
                }
            }

            return $resultado;

        } catch (Exception $e) {
            error_log("Error en ctrEliminarFactura: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Marcar factura como pagada
     */
    static public function ctrMarcarComoPagada($id)
    {
        try {
            $datos = array(
                "id_factura" => $id,
                "estado" => "pagado",
                "fecha_pago" => date('Y-m-d H:i:s')
            );

            return ModeloFactura::mdlActualizarEstado($datos);

        } catch (Exception $e) {
            error_log("Error en ctrMarcarComoPagada: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Obtener estadísticas de facturación
     */
    static public function ctrEstadisticasFacturacion($fecha_inicio = null, $fecha_fin = null)
    {
        try {
            return ModeloFactura::mdlEstadisticasFacturacion($fecha_inicio, $fecha_fin);
        } catch (Exception $e) {
            error_log("Error en ctrEstadisticasFacturacion: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Buscar facturas con filtros
     */
    static public function ctrBuscarFacturas($filtros = array())
    {
        try {
            return ModeloFactura::mdlBuscarFacturasConFiltros($filtros);
        } catch (Exception $e) {
            error_log("Error en ctrBuscarFacturas: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Validar datos de cliente para facturación
     */
    static public function ctrValidarDatosCliente($datos)
    {
        $errores = array();

        // Validar nombre
        if (empty(trim($datos['nombre_cliente']))) {
            $errores[] = "El nombre del cliente es obligatorio";
        } elseif (strlen(trim($datos['nombre_cliente'])) < 3) {
            $errores[] = "El nombre del cliente debe tener al menos 3 caracteres";
        }

        // Validar cédula/RUC
        if (empty(trim($datos['cedula_ruc']))) {
            $errores[] = "La cédula o RUC es obligatorio";
        } elseif (strlen(trim($datos['cedula_ruc'])) < 4) {
            $errores[] = "La cédula o RUC debe tener al menos 4 caracteres";
        }

        // Validar email si se proporciona
        if (!empty($datos['email_cliente']) && !filter_var($datos['email_cliente'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = "El formato del email no es válido";
        }

        // Validar teléfono (formato básico)
        if (!empty($datos['telefono_cliente'])) {
            $telefono = preg_replace('/[^0-9+()-]/', '', $datos['telefono_cliente']);
            if (strlen($telefono) < 6) {
                $errores[] = "El teléfono debe tener al menos 6 dígitos";
            }
        }

        return $errores;
    }

    /**
     * Generar número de factura automático
     */
    static public function ctrGenerarNumeroFactura()
    {
        try {
            return ModeloFactura::mdlGenerarNumeroFactura();
        } catch (Exception $e) {
            error_log("Error en ctrGenerarNumeroFactura: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener facturas vencidas
     */
    static public function ctrObtenerFacturasVencidas()
    {
        try {
            return ModeloFactura::mdlObtenerFacturasVencidas();
        } catch (Exception $e) {
            error_log("Error en ctrObtenerFacturasVencidas: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener resumen mensual de facturación
     */
    static public function ctrResumenMensualFacturacion($anho = null)
    {
        try {
            if (!$anho) {
                $anho = date('Y');
            }
            return ModeloFactura::mdlResumenMensualFacturacion($anho);
        } catch (Exception $e) {
            error_log("Error en ctrResumenMensualFacturacion: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Crear factura desde orden de trabajo (método simplificado)
     */
    static public function ctrFacturarOrdenTrabajo($id_orden, $datos_cliente_editables = array())
    {
        try {
            // Obtener orden con detalles
            $orden = ModeloOrdenTrabajo::mdlObtenerOrdenTrabajo($id_orden);
            if (!$orden || $orden['facturado'] == 1) {
                return "error_orden";
            }

            // Usar datos editables del cliente si se proporcionan
            $nombre_cliente = !empty($datos_cliente_editables['nombre_cliente'])
                ? $datos_cliente_editables['nombre_cliente']
                : $orden['nombre_cliente'];

            $documento = !empty($datos_cliente_editables['cedula_ruc'])
                ? $datos_cliente_editables['cedula_ruc']
                : (!empty($orden['ruc']) ? $orden['ruc'] : $orden['cedula']);

            // Preparar datos de factura
            $datos = array(
                "id_cliente" => $orden['id_cliente'],
                "id_personal" => $_SESSION["id_personal"],
                "id_orden" => $id_orden,
                "id_presupuesto" => null,
                "fecha_emision" => date('Y-m-d H:i:s'),
                "tipo_factura" => "contado",

                // Información del cliente (editable)
                "nombre_cliente" => $nombre_cliente,
                "cedula_ruc" => $documento,
                "telefono_cliente" => $datos_cliente_editables['telefono_cliente'] ?? $orden['telefono'],
                "email_cliente" => $datos_cliente_editables['email_cliente'] ?? $orden['email'],
                "direccion_cliente" => $datos_cliente_editables['direccion_cliente'] ?? $orden['direccion'],

                "subtotal" => $orden['total'],
                "descuento" => 0,
                "iva" => 0,
                "total" => $orden['total'],
                "estado" => "pendiente",
                "observaciones" => "Factura generada desde Orden de Trabajo #" . str_pad($id_orden, 6, '0', STR_PAD_LEFT)
            );

            // Crear factura
            $id_factura = ModeloFactura::mdlRegistrarFactura($datos);

            if (is_numeric($id_factura)) {
                // Agregar detalles desde la orden
                $detalles_orden = ModeloOrdenDetalle::mdlObtenerDetalles($id_orden);
                foreach ($detalles_orden as $detalle) {
                    $detalle_factura = array(
                        "id_factura" => $id_factura,
                        "tipo" => "servicio",
                        "id_producto" => null,
                        "descripcion" => $detalle['descripcion'],
                        "cantidad" => $detalle['cantidad'],
                        "precio_unitario" => $detalle['precio_unitario'],
                        "descuento" => 0,
                        "subtotal" => $detalle['subtotal']
                    );
                    ModeloDetalleFactura::mdlRegistrarDetalle($detalle_factura);
                }

                // Marcar orden como facturada
                ModeloOrdenTrabajo::mdlMarcarComoFacturada($id_orden);

                return $id_factura;
            }

            return "error";

        } catch (Exception $e) {
            error_log("Error en ctrFacturarOrdenTrabajo: " . $e->getMessage());
            return "error";
        }
    }
}
?>