<?php
require_once "../modelo/modelo_presupuesto.php";
require_once "../modelo/modelo_vehiculo.php";
require_once "../modelo/modelo_cliente.php";
require_once "../modelo/modelo_producto.php";

class PresupuestoControlador
{

    /**
     * Crear nuevo presupuesto
     */
    static public function ctrCrearPresupuesto()
    {
        if (isset($_POST["id_vehiculo_presupuesto"])) {

            // Validar datos básicos
            if (empty($_POST["id_vehiculo_presupuesto"]) || empty($_POST["fecha_validez"])) {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Faltan datos obligatorios para crear el presupuesto"
                    });
                </script>';
                return;
            }

            // Datos del presupuesto
            $datos_presupuesto = array(
                "id_vehiculo" => $_POST["id_vehiculo_presupuesto"],
                "id_personal" => $_SESSION["id_personal"],
                "fecha_validez" => $_POST["fecha_validez"],
                "total" => 0, // Se calculará después
                "estado" => "pendiente",
                "observaciones" => $_POST["observaciones_presupuesto"] ?? ""
            );

            // Crear presupuesto
            $id_presupuesto = ModeloPresupuesto::mdlCrearPresupuesto($datos_presupuesto);

            if ($id_presupuesto != "error" && is_numeric($id_presupuesto)) {

                $total_presupuesto = 0;

                // Insertar servicios si existen
                if (isset($_POST["servicios"]) && is_array($_POST["servicios"])) {
                    foreach ($_POST["servicios"] as $servicio) {
                        if (!empty($servicio["descripcion"]) && $servicio["precio"] > 0) {
                            $subtotal = $servicio["cantidad"] * $servicio["precio"];
                            $total_presupuesto += $subtotal;

                            $detalle = array(
                                "id_presupuesto" => $id_presupuesto,
                                "tipo" => "servicio",
                                "id_producto" => null,
                                "descripcion" => $servicio["descripcion"],
                                "cantidad" => $servicio["cantidad"],
                                "precio_unitario" => $servicio["precio"],
                                "subtotal" => $subtotal
                            );

                            ModeloPresupuesto::mdlInsertarDetallePresupuesto($detalle);
                        }
                    }
                }

                // Insertar productos si existen
                if (isset($_POST["productos"]) && is_array($_POST["productos"])) {
                    foreach ($_POST["productos"] as $producto) {
                        if (!empty($producto["id_producto"]) && $producto["cantidad"] > 0) {
                            $info_producto = ModeloProducto::mdlObtenerProducto($producto["id_producto"]);
                            if ($info_producto) {
                                $precio = $producto["precio"] ?? $info_producto["precio_venta"];
                                $subtotal = $producto["cantidad"] * $precio;
                                $total_presupuesto += $subtotal;

                                $detalle = array(
                                    "id_presupuesto" => $id_presupuesto,
                                    "tipo" => "producto",
                                    "id_producto" => $producto["id_producto"],
                                    "descripcion" => $info_producto["nombre"],
                                    "cantidad" => $producto["cantidad"],
                                    "precio_unitario" => $precio,
                                    "subtotal" => $subtotal
                                );

                                ModeloPresupuesto::mdlInsertarDetallePresupuesto($detalle);
                            }
                        }
                    }
                }

                // Actualizar total del presupuesto
                $datos_actualizacion = array(
                    "fecha_validez" => $_POST["fecha_validez"],
                    "total" => $total_presupuesto,
                    "estado" => "pendiente",
                    "observaciones" => $_POST["observaciones_presupuesto"] ?? ""
                );

                ModeloPresupuesto::mdlActualizarPresupuesto($id_presupuesto, $datos_actualizacion);

                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡Presupuesto creado!",
                        text: "El presupuesto ha sido creado correctamente",
                        showConfirmButton: false,
                        timer: 2000
                    }).then(function() {
                        window.location = "index.php?pagina=tabla/presupuestos";
                    });
                </script>';

            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Hubo un error al crear el presupuesto"
                    });
                </script>';
            }
        }
    }

    /**
     * Listar presupuestos
     */
    static public function ctrListarPresupuestos()
    {
        try {
            return ModeloPresupuesto::mdlListarPresupuestos();
        } catch (Exception $e) {
            error_log("Error en ctrListarPresupuestos: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener presupuesto por ID
     */
    static public function ctrObtenerPresupuesto($id)
    {
        if (!$id)
            return false;

        try {
            return ModeloPresupuesto::mdlObtenerPresupuesto($id);
        } catch (Exception $e) {
            error_log("Error en ctrObtenerPresupuesto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener detalles del presupuesto
     */
    static public function ctrObtenerDetallesPresupuesto($id_presupuesto)
    {
        if (!$id_presupuesto)
            return array();

        try {
            return ModeloPresupuesto::mdlObtenerDetallesPresupuesto($id_presupuesto);
        } catch (Exception $e) {
            error_log("Error en ctrObtenerDetallesPresupuesto: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Editar presupuesto
     */
    static public function ctrEditarPresupuesto()
    {
        if (isset($_POST["id_presupuesto_editar"])) {
            $id = $_POST["id_presupuesto_editar"];

            $datos = array(
                "fecha_validez" => $_POST["fecha_validez"],
                "total" => floatval($_POST["total_presupuesto"]),
                "estado" => $_POST["estado_presupuesto"],
                "observaciones" => $_POST["observaciones_presupuesto"] ?? ""
            );

            $respuesta = ModeloPresupuesto::mdlActualizarPresupuesto($id, $datos);

            if ($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡Presupuesto actualizado!",
                        text: "El presupuesto ha sido actualizado correctamente",
                        showConfirmButton: false,
                        timer: 2000
                    }).then(function() {
                        window.location = "index.php?pagina=tabla/presupuestos";
                    });
                </script>';
            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Hubo un error al actualizar el presupuesto"
                    });
                </script>';
            }
        }
    }

    /**
     * Cambiar estado del presupuesto
     */
    static public function ctrCambiarEstadoPresupuesto()
    {
        if (isset($_POST["id_presupuesto_estado"]) && isset($_POST["nuevo_estado"])) {
            $id = $_POST["id_presupuesto_estado"];
            $estado = $_POST["nuevo_estado"];

            $respuesta = ModeloPresupuesto::mdlCambiarEstado($id, $estado);

            if ($respuesta == "ok") {
                echo json_encode(array("status" => "success", "message" => "Estado actualizado correctamente"));
            } else {
                echo json_encode(array("status" => "error", "message" => "Error al actualizar el estado"));
            }
        }
    }

    /**
     * Eliminar presupuesto
     */
    static public function ctrEliminarPresupuesto()
    {
        if (isset($_GET["id_presupuesto_eliminar"])) {
            $id = $_GET["id_presupuesto_eliminar"];

            $respuesta = ModeloPresupuesto::mdlEliminarPresupuesto($id);

            if ($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡Presupuesto eliminado!",
                        text: "El presupuesto ha sido eliminado correctamente",
                        showConfirmButton: false,
                        timer: 2000
                    }).then(function() {
                        window.location = "index.php?pagina=tabla/presupuestos";
                    });
                </script>';
            } else if ($respuesta == "ya_facturado") {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "No se puede eliminar",
                        text: "Este presupuesto ya ha sido facturado"
                    });
                </script>';
            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Hubo un error al eliminar el presupuesto"
                    });
                </script>';
            }
        }
    }

    /**
     * Obtener presupuestos pendientes
     */
    static public function ctrPresupuestosPendientes()
    {
        try {
            return ModeloPresupuesto::mdlPresupuestosPendientes();
        } catch (Exception $e) {
            error_log("Error en ctrPresupuestosPendientes: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener presupuestos próximos a vencer
     */
    static public function ctrPresupuestosProximosVencer($dias = 7)
    {
        try {
            return ModeloPresupuesto::mdlPresupuestosProximosVencer($dias);
        } catch (Exception $e) {
            error_log("Error en ctrPresupuestosProximosVencer: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener presupuestos de un cliente
     */
    static public function ctrPresupuestosCliente($id_cliente)
    {
        if (!$id_cliente)
            return array();

        try {
            return ModeloPresupuesto::mdlPresupuestosCliente($id_cliente);
        } catch (Exception $e) {
            error_log("Error en ctrPresupuestosCliente: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener estadísticas de presupuestos
     */
    static public function ctrEstadisticasPresupuestos()
    {
        try {
            return ModeloPresupuesto::mdlEstadisticasPresupuestos();
        } catch (Exception $e) {
            error_log("Error en ctrEstadisticasPresupuestos: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Buscar presupuestos
     */
    static public function ctrBuscarPresupuestos()
    {
        if (isset($_POST["termino_busqueda_presupuesto"])) {
            $termino = $_POST["termino_busqueda_presupuesto"];

            try {
                return ModeloPresupuesto::mdlBuscarPresupuestos($termino);
            } catch (Exception $e) {
                error_log("Error en ctrBuscarPresupuestos: " . $e->getMessage());
                return array();
            }
        }
        return array();
    }

    /**
     * Duplicar presupuesto
     */
    static public function ctrDuplicarPresupuesto()
    {
        if (isset($_POST["id_presupuesto_duplicar"]) && isset($_POST["nueva_fecha_validez"])) {
            $id_presupuesto = $_POST["id_presupuesto_duplicar"];
            $nueva_fecha_validez = $_POST["nueva_fecha_validez"];

            $respuesta = ModeloPresupuesto::mdlDuplicarPresupuesto($id_presupuesto, $nueva_fecha_validez);

            if (is_numeric($respuesta)) {
                echo json_encode(array(
                    "status" => "success",
                    "message" => "Presupuesto duplicado correctamente",
                    "nuevo_id" => $respuesta
                ));
            } else if ($respuesta == "presupuesto_no_encontrado") {
                echo json_encode(array("status" => "error", "message" => "Presupuesto no encontrado"));
            } else {
                echo json_encode(array("status" => "error", "message" => "Error al duplicar el presupuesto"));
            }
        }
    }

    /**
     * Convertir presupuesto a orden de trabajo
     */
    static public function ctrConvertirAOrden()
    {
        if (isset($_POST["id_presupuesto_convertir"])) {
            $id_presupuesto = $_POST["id_presupuesto_convertir"];

            $datos_orden = array(
                "id_personal" => $_POST["id_personal_orden"] ?? $_SESSION["id_personal"],
                "fecha_ingreso" => $_POST["fecha_ingreso"] ?? date('Y-m-d H:i:s'),
                "kilometraje_actual" => $_POST["kilometraje_actual"] ?? 0
            );

            $respuesta = ModeloPresupuesto::mdlConvertirAOrden($id_presupuesto, $datos_orden);

            if (is_numeric($respuesta)) {
                echo json_encode(array(
                    "status" => "success",
                    "message" => "Presupuesto convertido a orden de trabajo",
                    "id_orden" => $respuesta
                ));
            } else if ($respuesta == "presupuesto_no_valido") {
                echo json_encode(array("status" => "error", "message" => "El presupuesto debe estar aprobado para convertir"));
            } else {
                echo json_encode(array("status" => "error", "message" => "Error al convertir el presupuesto"));
            }
        }
    }

    /**
     * Marcar presupuestos vencidos (tarea programada)
     */
    static public function ctrMarcarPresupuestosVencidos()
    {
        try {
            $cantidad = ModeloPresupuesto::mdlMarcarPresupuestosVencidos();
            return array(
                "status" => "success",
                "message" => "Se marcaron $cantidad presupuestos como vencidos"
            );
        } catch (Exception $e) {
            error_log("Error en ctrMarcarPresupuestosVencidos: " . $e->getMessage());
            return array(
                "status" => "error",
                "message" => "Error al marcar presupuestos vencidos"
            );
        }
    }

    /**
     * Validar presupuesto antes de crear
     */
    static public function ctrValidarPresupuesto($datos)
    {
        $errores = array();

        // Validar vehículo
        if (empty($datos['id_vehiculo_presupuesto'])) {
            $errores[] = "Debe seleccionar un vehículo";
        }

        // Validar fecha de validez
        if (empty($datos['fecha_validez'])) {
            $errores[] = "La fecha de validez es obligatoria";
        } else {
            $fecha_validez = new DateTime($datos['fecha_validez']);
            $fecha_actual = new DateTime();

            if ($fecha_validez <= $fecha_actual) {
                $errores[] = "La fecha de validez debe ser posterior a la fecha actual";
            }
        }

        // Validar que tenga al menos un servicio o producto
        $tiene_servicios = isset($datos['servicios']) && !empty($datos['servicios']);
        $tiene_productos = isset($datos['productos']) && !empty($datos['productos']);

        if (!$tiene_servicios && !$tiene_productos) {
            $errores[] = "Debe agregar al menos un servicio o producto al presupuesto";
        }

        return $errores;
    }

    /**
     * Generar reporte de presupuestos
     */
    static public function ctrGenerarReportePresupuestos($filtros = array())
    {
        try {
            $presupuestos = self::ctrListarPresupuestos();
            $estadisticas = self::ctrEstadisticasPresupuestos();
            $pendientes = self::ctrPresupuestosPendientes();
            $proximos_vencer = self::ctrPresupuestosProximosVencer();

            // Aplicar filtros si se proporcionan
            if (!empty($filtros)) {
                if (isset($filtros['estado'])) {
                    $presupuestos = array_filter($presupuestos, function ($p) use ($filtros) {
                        return $p['estado'] === $filtros['estado'];
                    });
                }

                if (isset($filtros['fecha_inicio']) && isset($filtros['fecha_fin'])) {
                    $presupuestos = array_filter($presupuestos, function ($p) use ($filtros) {
                        return $p['fecha_emision'] >= $filtros['fecha_inicio'] &&
                            $p['fecha_emision'] <= $filtros['fecha_fin'];
                    });
                }
            }

            return array(
                'presupuestos' => array_values($presupuestos),
                'estadisticas' => $estadisticas,
                'pendientes' => $pendientes,
                'proximos_vencer' => $proximos_vencer,
                'filtros_aplicados' => $filtros,
                'fecha_generacion' => date('Y-m-d H:i:s')
            );
        } catch (Exception $e) {
            error_log("Error en ctrGenerarReportePresupuestos: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Exportar presupuestos a CSV
     */
    static public function ctrExportarPresupuestosCSV($filtros = array())
    {
        try {
            $reporte = self::ctrGenerarReportePresupuestos($filtros);
            $presupuestos = $reporte['presupuestos'];

            if (empty($presupuestos)) {
                return false;
            }

            $filename = "presupuestos_" . date('Y-m-d_H-i-s') . ".csv";
            $filepath = "../exports/" . $filename;

            // Crear directorio si no existe
            if (!is_dir('../exports')) {
                mkdir('../exports', 0755, true);
            }

            $file = fopen($filepath, 'w');

            // Escribir cabeceras
            fputcsv($file, [
                'ID',
                'Cliente',
                'Vehículo',
                'Matrícula',
                'Fecha Emisión',
                'Fecha Validez',
                'Total',
                'Estado',
                'Personal',
                'Días Restantes'
            ]);

            // Escribir datos
            foreach ($presupuestos as $presupuesto) {
                fputcsv($file, [
                    $presupuesto['id_presupuesto'],
                    $presupuesto['nombre_cliente'],
                    $presupuesto['marca'] . ' ' . $presupuesto['modelo'],
                    $presupuesto['matricula'],
                    $presupuesto['fecha_emision'],
                    $presupuesto['fecha_validez'],
                    $presupuesto['total'],
                    $presupuesto['estado'],
                    $presupuesto['nombre_personal'],
                    $presupuesto['dias_restantes']
                ]);
            }

            fclose($file);
            return $filename;
        } catch (Exception $e) {
            error_log("Error en ctrExportarPresupuestosCSV: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener alertas de presupuestos
     */
    static public function ctrObtenerAlertasPresupuestos()
    {
        try {
            $alertas = array();

            // Presupuestos próximos a vencer
            $proximos_vencer = self::ctrPresupuestosProximosVencer(7);
            foreach ($proximos_vencer as $presupuesto) {
                $tipo = 'warning';
                if ($presupuesto['dias_restantes'] <= 2) {
                    $tipo = 'danger';
                }

                $alertas[] = array(
                    'tipo' => $tipo,
                    'titulo' => 'Presupuesto próximo a vencer',
                    'mensaje' => "Presupuesto #{$presupuesto['id_presupuesto']} vence en {$presupuesto['dias_restantes']} días",
                    'datos' => $presupuesto
                );
            }

            // Presupuestos pendientes por mucho tiempo (más de 30 días)
            $pendientes_antiguos = array_filter(self::ctrPresupuestosPendientes(), function ($p) {
                $fecha_emision = new DateTime($p['fecha_emision']);
                $fecha_actual = new DateTime();
                $diff = $fecha_actual->diff($fecha_emision);
                return $diff->days > 30;
            });

            foreach ($pendientes_antiguos as $presupuesto) {
                $alertas[] = array(
                    'tipo' => 'info',
                    'titulo' => 'Presupuesto pendiente',
                    'mensaje' => "Presupuesto #{$presupuesto['id_presupuesto']} lleva más de 30 días pendiente",
                    'datos' => $presupuesto
                );
            }

            return $alertas;
        } catch (Exception $e) {
            error_log("Error en ctrObtenerAlertasPresupuestos: " . $e->getMessage());
            return array();
        }
    }
}
?>