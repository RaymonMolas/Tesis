<?php
require_once __DIR__ . "/../modelo/modelo_orden_trabajo.php";
require_once __DIR__ . "/../modelo/modelo_orden_detalle.php";
require_once __DIR__ . "/../modelo/modelo_vehiculo.php";
require_once __DIR__ . "/../modelo/modelo_cliente.php";

class OrdenTrabajoControlador
{
    /**
     * Listar todas las órdenes de trabajo
     */
    static public function ctrListarOrdenesTrabajo()
    {
        try {
            return ModeloOrdenTrabajo::mdlListarOrdenesTrabajo();
        } catch (Exception $e) {
            error_log("Error en ctrListarOrdenesTrabajo: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener una orden específica con sus detalles
     */
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

    /**
     * Registrar nueva orden de trabajo con servicios específicos
     */
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
                    "estado" => "en_proceso",
                    "observaciones" => $_POST["observaciones"] ?? ""
                );

                // Insertar orden de trabajo
                $id_orden = ModeloOrdenTrabajo::mdlRegistrarOrdenTrabajo($datos);

                if (is_numeric($id_orden) && $id_orden > 0) {
                    // Registrar detalles de servicios específicos
                    $resultado_detalles = ModeloOrdenDetalle::mdlRegistrarMultiplesDetalles($id_orden, $servicios);

                    if ($resultado_detalles == "ok") {
                        echo '<script>
                            Swal.fire({
                                icon: "success",
                                title: "¡Éxito!",
                                text: "La orden de trabajo ha sido creada correctamente",
                                showConfirmButton: true,
                                confirmButtonText: "Ver Orden"
                            }).then((result) => {
                                if (result.value) {
                                    window.location = "index.php?pagina=ver/orden_trabajo&id=' . $id_orden . '";
                                }
                            });
                        </script>';
                        return "ok";
                    } else {
                        // Si hay error en detalles, eliminar la orden
                        ModeloOrdenTrabajo::mdlEliminarOrdenTrabajo($id_orden);
                        throw new Exception("Error al registrar los servicios de la orden");
                    }
                } else {
                    throw new Exception("Error al crear la orden de trabajo");
                }

            } catch (Exception $e) {
                error_log("Error en ctrRegistrarOrdenTrabajo: " . $e->getMessage());
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error al crear orden",
                        text: "' . addslashes($e->getMessage()) . '",
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return "error";
            }
        }
    }

    /**
     * Actualizar orden de trabajo existente
     */
    static public function ctrActualizarOrdenTrabajo()
    {
        if (isset($_POST["id_orden"]) && isset($_POST["servicios"])) {
            try {
                // Validar datos básicos
                if (empty($_POST["id_vehiculo"])) {
                    throw new Exception("Debe seleccionar un vehículo");
                }

                // Validar y decodificar servicios
                $servicios = json_decode($_POST["servicios"], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception("Error en los datos de servicios: " . json_last_error_msg());
                }

                if (empty($servicios)) {
                    throw new Exception("Debe tener al menos un servicio en la orden");
                }

                // Preparar datos de la orden
                $datos = array(
                    "id_orden" => (int) $_POST["id_orden"],
                    "id_vehiculo" => (int) $_POST["id_vehiculo"],
                    "fecha_ingreso" => $_POST["fecha_entrada"],
                    "fecha_salida" => $_POST["fecha_salida"],
                    "kilometraje_actual" => $_POST["kilometraje_actual"] ?? null,
                    "estado" => $_POST["estado"] ?? "en_proceso",
                    "observaciones" => $_POST["observaciones"] ?? ""
                );

                // Actualizar orden de trabajo
                $resultado_orden = ModeloOrdenTrabajo::mdlActualizarOrdenTrabajo($datos);

                if ($resultado_orden == "ok") {
                    // Actualizar detalles de servicios
                    $resultado_detalles = ModeloOrdenDetalle::mdlRegistrarMultiplesDetalles($datos["id_orden"], $servicios);

                    if ($resultado_detalles == "ok") {
                        echo '<script>
                            Swal.fire({
                                icon: "success",
                                title: "¡Actualizada!",
                                text: "La orden de trabajo ha sido actualizada correctamente",
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location = "index.php?pagina=tabla/orden_trabajo";
                            });
                        </script>';
                        return "ok";
                    } else {
                        throw new Exception("Error al actualizar los servicios de la orden");
                    }
                } else {
                    throw new Exception("Error al actualizar la orden de trabajo");
                }

            } catch (Exception $e) {
                error_log("Error en ctrActualizarOrdenTrabajo: " . $e->getMessage());
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error al actualizar",
                        text: "' . addslashes($e->getMessage()) . '",
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return "error";
            }
        }
    }

    /**
     * Eliminar orden de trabajo
     */
    static public function ctrEliminarOrdenTrabajo($id)
    {
        try {
            // Verificar que la orden no esté facturada
            $orden = ModeloOrdenTrabajo::mdlObtenerOrdenTrabajo($id);
            if ($orden && $orden['facturado'] == 1) {
                return "orden_facturada";
            }

            return ModeloOrdenTrabajo::mdlEliminarOrdenTrabajo($id);

        } catch (Exception $e) {
            error_log("Error en ctrEliminarOrdenTrabajo: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Cambiar estado de orden
     */
    static public function ctrCambiarEstadoOrden($id, $nuevo_estado)
    {
        try {
            $datos = array(
                "id_orden" => $id,
                "estado" => $nuevo_estado
            );

            // Si se marca como entregado, agregar fecha de salida si no la tiene
            if ($nuevo_estado == "entregado") {
                $orden = ModeloOrdenTrabajo::mdlObtenerOrdenTrabajo($id);
                if ($orden && empty($orden['fecha_salida'])) {
                    $datos["fecha_salida"] = date('Y-m-d H:i:s');
                }
            }

            return ModeloOrdenTrabajo::mdlActualizarEstado($datos);

        } catch (Exception $e) {
            error_log("Error en ctrCambiarEstadoOrden: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Obtener órdenes por estado
     */
    static public function ctrObtenerOrdenesPorEstado($estado)
    {
        try {
            return ModeloOrdenTrabajo::mdlObtenerOrdenesPorEstado($estado);
        } catch (Exception $e) {
            error_log("Error en ctrObtenerOrdenesPorEstado: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener estadísticas de órdenes
     */
    static public function ctrEstadisticasOrdenes()
    {
        try {
            return ModeloOrdenTrabajo::mdlEstadisticasOrdenes();
        } catch (Exception $e) {
            error_log("Error en ctrEstadisticasOrdenes: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener servicios más solicitados
     */
    static public function ctrServiciosMasSolicitados($limite = 10)
    {
        try {
            return ModeloOrdenTrabajo::mdlObtenerServiciosMasSolicitados($limite);
        } catch (Exception $e) {
            error_log("Error en ctrServiciosMasSolicitados: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Buscar órdenes con filtros avanzados
     */
    static public function ctrBuscarOrdenes($filtros = array())
    {
        try {
            return ModeloOrdenTrabajo::mdlBuscarOrdenesConFiltros($filtros);
        } catch (Exception $e) {
            error_log("Error en ctrBuscarOrdenes: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Validar servicios antes de procesar
     */
    static public function ctrValidarServicios($servicios)
    {
        $errores = array();

        if (!is_array($servicios) || empty($servicios)) {
            $errores[] = "Debe agregar al menos un servicio";
            return $errores;
        }

        foreach ($servicios as $index => $servicio) {
            $servicio_num = $index + 1;

            // Validar estructura
            if (
                !isset($servicio['tipo']) || !isset($servicio['descripcion']) ||
                !isset($servicio['cantidad']) || !isset($servicio['precioUnitario']) ||
                !isset($servicio['subtotal'])
            ) {
                $errores[] = "Servicio #{$servicio_num}: Estructura incompleta";
                continue;
            }

            // Validar tipo de servicio
            if (!ModeloOrdenTrabajo::mdlValidarTipoServicio($servicio['tipo'])) {
                $errores[] = "Servicio #{$servicio_num}: Tipo de servicio inválido";
            }

            // Validar descripción
            if (empty(trim($servicio['descripcion']))) {
                $errores[] = "Servicio #{$servicio_num}: La descripción es obligatoria";
            }

            // Validar cantidad
            if (!is_numeric($servicio['cantidad']) || $servicio['cantidad'] <= 0) {
                $errores[] = "Servicio #{$servicio_num}: La cantidad debe ser mayor a 0";
            }

            // Validar precio
            if (!is_numeric($servicio['precioUnitario']) || $servicio['precioUnitario'] <= 0) {
                $errores[] = "Servicio #{$servicio_num}: El precio debe ser mayor a 0";
            }

            // Validar subtotal
            if (is_numeric($servicio['cantidad']) && is_numeric($servicio['precioUnitario'])) {
                $subtotal_calculado = $servicio['cantidad'] * $servicio['precioUnitario'];
                if (abs($subtotal_calculado - $servicio['subtotal']) > 0.01) {
                    $errores[] = "Servicio #{$servicio_num}: El subtotal no coincide";
                }
            }
        }

        return $errores;
    }

    /**
     * Obtener resumen de orden para facturación
     */
    static public function ctrObtenerResumenParaFacturacion($id_orden)
    {
        try {
            $orden = self::ctrObtenerOrdenTrabajo($id_orden);
            if (!$orden) {
                return false;
            }

            // Verificar que esté completada y no facturada
            if ($orden['estado'] != 'completado') {
                return array('error' => 'La orden debe estar completada para facturar');
            }

            if ($orden['facturado'] == 1) {
                return array('error' => 'Esta orden ya ha sido facturada');
            }

            // Preparar resumen
            $resumen = array(
                'orden' => $orden,
                'detalles' => $orden['detalles'],
                'total' => $orden['total'],
                'servicios_count' => count($orden['detalles']),
                'puede_facturar' => true
            );

            return $resumen;

        } catch (Exception $e) {
            error_log("Error en ctrObtenerResumenParaFacturacion: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Marcar orden como facturada
     */
    static public function ctrMarcarComoFacturada($id_orden)
    {
        try {
            return ModeloOrdenTrabajo::mdlMarcarComoFacturada($id_orden);
        } catch (Exception $e) {
            error_log("Error en ctrMarcarComoFacturada: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Obtener órdenes pendientes de facturación
     */
    static public function ctrOrdenesPendientesFacturacion()
    {
        try {
            return ModeloOrdenTrabajo::mdlObtenerOrdenesPendientesFacturacion();
        } catch (Exception $e) {
            error_log("Error en ctrOrdenesPendientesFacturacion: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Generar reporte de productividad
     */
    static public function ctrReporteProductividad($fecha_inicio = null, $fecha_fin = null)
    {
        try {
            if (!$fecha_inicio) {
                $fecha_inicio = date('Y-m-01'); // Primer día del mes actual
            }
            if (!$fecha_fin) {
                $fecha_fin = date('Y-m-d'); // Día actual
            }

            $filtros = array(
                'fecha_desde' => $fecha_inicio,
                'fecha_hasta' => $fecha_fin
            );

            $ordenes = ModeloOrdenTrabajo::mdlBuscarOrdenesConFiltros($filtros);
            $servicios_stats = ModeloOrdenDetalle::mdlEstadisticasServicios($fecha_inicio, $fecha_fin);

            return array(
                'periodo' => array(
                    'inicio' => $fecha_inicio,
                    'fin' => $fecha_fin
                ),
                'ordenes' => $ordenes,
                'total_ordenes' => count($ordenes),
                'servicios_estadisticas' => $servicios_stats,
                'total_facturado' => array_sum(array_column($ordenes, 'total_orden'))
            );

        } catch (Exception $e) {
            error_log("Error en ctrReporteProductividad: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Duplicar orden de trabajo (para casos recurrentes)
     */
    static public function ctrDuplicarOrden($id_orden_original)
    {
        try {
            $orden_original = self::ctrObtenerOrdenTrabajo($id_orden_original);
            if (!$orden_original) {
                return "orden_no_encontrada";
            }

            // Preparar datos para nueva orden
            $datos_nueva = array(
                "id_vehiculo" => $orden_original["id_vehiculo"],
                "id_personal" => $_SESSION["id_personal"],
                "fecha_ingreso" => date('Y-m-d H:i:s'),
                "fecha_salida" => date('Y-m-d H:i:s', strtotime('+1 day')),
                "kilometraje_actual" => null,
                "estado" => "en_proceso",
                "observaciones" => "Duplicada de orden #" . str_pad($id_orden_original, 6, '0', STR_PAD_LEFT)
            );

            // Crear nueva orden
            $id_nueva_orden = ModeloOrdenTrabajo::mdlRegistrarOrdenTrabajo($datos_nueva);

            if (is_numeric($id_nueva_orden) && $id_nueva_orden > 0) {
                // Duplicar servicios
                if (!empty($orden_original['detalles'])) {
                    $servicios = array();
                    foreach ($orden_original['detalles'] as $detalle) {
                        $servicios[] = array(
                            'tipo' => $detalle['tipo_servicio'],
                            'descripcion' => $detalle['descripcion'],
                            'cantidad' => $detalle['cantidad'],
                            'precioUnitario' => $detalle['precio_unitario'],
                            'subtotal' => $detalle['subtotal']
                        );
                    }

                    $resultado = ModeloOrdenDetalle::mdlRegistrarMultiplesDetalles($id_nueva_orden, $servicios);
                    if ($resultado == "ok") {
                        return $id_nueva_orden;
                    }
                }
            }

            return "error";

        } catch (Exception $e) {
            error_log("Error en ctrDuplicarOrden: " . $e->getMessage());
            return "error";
        }
    }
}
?>