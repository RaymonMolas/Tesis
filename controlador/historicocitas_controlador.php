<?php
require_once "../modelo/modelo_historicocitas.php";

class HistoricoCitasControlador
{

    /**
     * Obtener historial completo de citas
     */
    static public function ctrObtenerHistorialCompleto()
    {
        try {
            return ModeloHistoricoCitas::mdlObtenerHistorialCompleto();
        } catch (Exception $e) {
            error_log("Error en ctrObtenerHistorialCompleto: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener historial de un cliente específico
     */
    static public function ctrObtenerHistorialCliente($id_cliente)
    {
        if (!$id_cliente)
            return array();

        try {
            return ModeloHistoricoCitas::mdlObtenerHistorialCliente($id_cliente);
        } catch (Exception $e) {
            error_log("Error en ctrObtenerHistorialCliente: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener historial de un vehículo específico
     */
    static public function ctrObtenerHistorialVehiculo($id_vehiculo)
    {
        if (!$id_vehiculo)
            return array();

        try {
            return ModeloHistoricoCitas::mdlObtenerHistorialVehiculo($id_vehiculo);
        } catch (Exception $e) {
            error_log("Error en ctrObtenerHistorialVehiculo: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener estadísticas del historial
     */
    static public function ctrEstadisticasHistorial($periodo = 30)
    {
        try {
            return ModeloHistoricoCitas::mdlEstadisticasHistorial($periodo);
        } catch (Exception $e) {
            error_log("Error en ctrEstadisticasHistorial: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener historial por rango de fechas
     */
    static public function ctrObtenerHistorialPorFechas()
    {
        if (isset($_POST["fecha_inicio_historial"]) && isset($_POST["fecha_fin_historial"])) {
            $fecha_inicio = $_POST["fecha_inicio_historial"];
            $fecha_fin = $_POST["fecha_fin_historial"];

            try {
                return ModeloHistoricoCitas::mdlObtenerHistorialPorFechas($fecha_inicio, $fecha_fin);
            } catch (Exception $e) {
                error_log("Error en ctrObtenerHistorialPorFechas: " . $e->getMessage());
                return array();
            }
        }
        return array();
    }

    /**
     * Buscar en historial
     */
    static public function ctrBuscarHistorial()
    {
        if (isset($_POST["termino_busqueda_historial"])) {
            $termino = $_POST["termino_busqueda_historial"];

            try {
                return ModeloHistoricoCitas::mdlBuscarHistorial($termino);
            } catch (Exception $e) {
                error_log("Error en ctrBuscarHistorial: " . $e->getMessage());
                return array();
            }
        }
        return array();
    }

    /**
     * Obtener clientes más frecuentes
     */
    static public function ctrClientesMasFrecuentes($limite = 10)
    {
        try {
            return ModeloHistoricoCitas::mdlClientesMasFrecuentes($limite);
        } catch (Exception $e) {
            error_log("Error en ctrClientesMasFrecuentes: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener motivos más comunes
     */
    static public function ctrMotivosMasComunes($limite = 10)
    {
        try {
            return ModeloHistoricoCitas::mdlMotivosMasComunes($limite);
        } catch (Exception $e) {
            error_log("Error en ctrMotivosMasComunes: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener resumen mensual
     */
    static public function ctrResumenMensual($anho = null)
    {
        try {
            return ModeloHistoricoCitas::mdlResumenMensual($anho);
        } catch (Exception $e) {
            error_log("Error en ctrResumenMensual: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener citas por estado final
     */
    static public function ctrCitasPorEstado($periodo = 30)
    {
        try {
            return ModeloHistoricoCitas::mdlCitasPorEstado($periodo);
        } catch (Exception $e) {
            error_log("Error en ctrCitasPorEstado: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Actualizar observaciones de una cita histórica
     */
    static public function ctrActualizarObservaciones()
    {
        if (isset($_POST["id_historico_obs"]) && isset($_POST["observaciones_historico"])) {
            $id_historico = $_POST["id_historico_obs"];
            $observaciones = $_POST["observaciones_historico"];

            $respuesta = ModeloHistoricoCitas::mdlActualizarObservaciones($id_historico, $observaciones);

            if ($respuesta == "ok") {
                echo json_encode(array("status" => "success", "message" => "Observaciones actualizadas correctamente"));
            } else {
                echo json_encode(array("status" => "error", "message" => "Error al actualizar las observaciones"));
            }
        }
    }

    /**
     * Eliminar registro del historial
     */
    static public function ctrEliminarHistorico()
    {
        if (isset($_GET["id_historico_eliminar"])) {
            $id_historico = $_GET["id_historico_eliminar"];

            $respuesta = ModeloHistoricoCitas::mdlEliminarHistorico($id_historico);

            if ($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡Registro eliminado!",
                        text: "El registro ha sido eliminado del historial",
                        showConfirmButton: false,
                        timer: 2000
                    }).then(function() {
                        window.location = "index.php?pagina=tabla/historicocitas";
                    });
                </script>';
            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Hubo un error al eliminar el registro"
                    });
                </script>';
            }
        }
    }

    /**
     * Limpiar historial antiguo
     */
    static public function ctrLimpiarHistorialAntiguo()
    {
        if (isset($_POST["dias_antiguedad"])) {
            $dias = intval($_POST["dias_antiguedad"]);

            if ($dias < 365) {
                echo json_encode(array("status" => "error", "message" => "Solo se puede limpiar historial con más de 1 año de antigüedad"));
                return;
            }

            $registros_eliminados = ModeloHistoricoCitas::mdlLimpiarHistorialAntiguo($dias);

            if ($registros_eliminados > 0) {
                echo json_encode(array(
                    "status" => "success",
                    "message" => "Se eliminaron $registros_eliminados registros del historial"
                ));
            } else {
                echo json_encode(array(
                    "status" => "info",
                    "message" => "No se encontraron registros para eliminar"
                ));
            }
        }
    }

    /**
     * Exportar historial a CSV
     */
    static public function ctrExportarHistorial()
    {
        try {
            $fecha_inicio = $_POST["fecha_inicio_export"] ?? null;
            $fecha_fin = $_POST["fecha_fin_export"] ?? null;

            $historial = ModeloHistoricoCitas::mdlExportarHistorial($fecha_inicio, $fecha_fin);

            if (empty($historial)) {
                return false;
            }

            $filename = "historial_citas_" . date('Y-m-d_H-i-s') . ".csv";
            $filepath = "../exports/" . $filename;

            // Crear directorio si no existe
            if (!is_dir('../exports')) {
                mkdir('../exports', 0755, true);
            }

            $file = fopen($filepath, 'w');

            // Escribir cabeceras
            fputcsv($file, [
                'ID',
                'Fecha Cita',
                'Hora',
                'Cliente',
                'Cédula',
                'Teléfono',
                'Matrícula',
                'Marca',
                'Modelo',
                'Motivo',
                'Estado Final',
                'Observaciones',
                'Fecha Registro'
            ]);

            // Escribir datos
            foreach ($historial as $registro) {
                fputcsv($file, [
                    $registro['id_historico'],
                    $registro['fecha_cita'],
                    $registro['hora_cita'],
                    $registro['cliente'],
                    $registro['cedula'],
                    $registro['telefono'],
                    $registro['matricula'] ?? 'N/A',
                    $registro['marca'] ?? 'N/A',
                    $registro['modelo'] ?? 'N/A',
                    $registro['motivo'],
                    $registro['estado_final'],
                    $registro['observaciones'],
                    $registro['fecha_registro']
                ]);
            }

            fclose($file);
            return $filename;
        } catch (Exception $e) {
            error_log("Error en ctrExportarHistorial: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generar reporte completo del historial
     */
    static public function ctrGenerarReporteCompleto($filtros = array())
    {
        try {
            $historial = self::ctrObtenerHistorialCompleto();
            $estadisticas = self::ctrEstadisticasHistorial();
            $clientes_frecuentes = self::ctrClientesMasFrecuentes();
            $motivos_comunes = self::ctrMotivosMasComunes();
            $resumen_mensual = self::ctrResumenMensual();
            $citas_por_estado = self::ctrCitasPorEstado();
            $tendencias = ModeloHistoricoCitas::mdlTendenciasAsistencia();

            // Aplicar filtros si se proporcionan
            if (!empty($filtros)) {
                if (isset($filtros['estado_final'])) {
                    $historial = array_filter($historial, function ($h) use ($filtros) {
                        return $h['estado_final'] === $filtros['estado_final'];
                    });
                }

                if (isset($filtros['fecha_inicio']) && isset($filtros['fecha_fin'])) {
                    $historial = array_filter($historial, function ($h) use ($filtros) {
                        return $h['fecha_cita'] >= $filtros['fecha_inicio'] &&
                            $h['fecha_cita'] <= $filtros['fecha_fin'];
                    });
                }
            }

            return array(
                'historial' => array_values($historial),
                'estadisticas' => $estadisticas,
                'clientes_frecuentes' => $clientes_frecuentes,
                'motivos_comunes' => $motivos_comunes,
                'resumen_mensual' => $resumen_mensual,
                'citas_por_estado' => $citas_por_estado,
                'tendencias_asistencia' => $tendencias,
                'filtros_aplicados' => $filtros,
                'fecha_generacion' => date('Y-m-d H:i:s')
            );
        } catch (Exception $e) {
            error_log("Error en ctrGenerarReporteCompleto: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener tendencias de asistencia
     */
    static public function ctrTendenciasAsistencia($meses = 12)
    {
        try {
            return ModeloHistoricoCitas::mdlTendenciasAsistencia($meses);
        } catch (Exception $e) {
            error_log("Error en ctrTendenciasAsistencia: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Análisis de satisfacción del cliente (basado en historial)
     */
    static public function ctrAnalisisSatisfaccion()
    {
        try {
            $historial = self::ctrObtenerHistorialCompleto();
            $analisis = array();

            foreach ($historial as $registro) {
                $cliente_id = $registro['id_cliente'];

                if (!isset($analisis[$cliente_id])) {
                    $analisis[$cliente_id] = array(
                        'nombre_cliente' => $registro['nombre_cliente'],
                        'total_citas' => 0,
                        'completadas' => 0,
                        'canceladas' => 0,
                        'no_asistio' => 0,
                        'tasa_asistencia' => 0,
                        'ultima_cita' => null
                    );
                }

                $analisis[$cliente_id]['total_citas']++;

                switch ($registro['estado_final']) {
                    case 'completada':
                        $analisis[$cliente_id]['completadas']++;
                        break;
                    case 'cancelada':
                        $analisis[$cliente_id]['canceladas']++;
                        break;
                    case 'no_asistio':
                        $analisis[$cliente_id]['no_asistio']++;
                        break;
                }

                // Actualizar última cita
                if (
                    !$analisis[$cliente_id]['ultima_cita'] ||
                    $registro['fecha_cita'] > $analisis[$cliente_id]['ultima_cita']
                ) {
                    $analisis[$cliente_id]['ultima_cita'] = $registro['fecha_cita'];
                }
            }

            // Calcular tasas de asistencia
            foreach ($analisis as $cliente_id => &$datos) {
                if ($datos['total_citas'] > 0) {
                    $datos['tasa_asistencia'] = round(
                        ($datos['completadas'] / $datos['total_citas']) * 100,
                        2
                    );
                }
            }

            // Ordenar por tasa de asistencia descendente
            uasort($analisis, function ($a, $b) {
                return $b['tasa_asistencia'] <=> $a['tasa_asistencia'];
            });

            return array_values($analisis);
        } catch (Exception $e) {
            error_log("Error en ctrAnalisisSatisfaccion: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Identificar patrones en el historial
     */
    static public function ctrIdentificarPatrones()
    {
        try {
            $historial = self::ctrObtenerHistorialCompleto();
            $patrones = array();

            // Patrón 1: Días de la semana más frecuentes
            $dias_semana = array();
            foreach ($historial as $registro) {
                $dia = date('l', strtotime($registro['fecha_cita']));
                $dias_semana[$dia] = ($dias_semana[$dia] ?? 0) + 1;
            }
            arsort($dias_semana);
            $patrones['dias_mas_frecuentes'] = $dias_semana;

            // Patrón 2: Horarios más frecuentes
            $horarios = array();
            foreach ($historial as $registro) {
                $hora = date('H:00', strtotime($registro['hora_cita']));
                $horarios[$hora] = ($horarios[$hora] ?? 0) + 1;
            }
            arsort($horarios);
            $patrones['horarios_mas_frecuentes'] = $horarios;

            // Patrón 3: Meses con más citas
            $meses = array();
            foreach ($historial as $registro) {
                $mes = date('M Y', strtotime($registro['fecha_cita']));
                $meses[$mes] = ($meses[$mes] ?? 0) + 1;
            }
            arsort($meses);
            $patrones['meses_mas_activos'] = array_slice($meses, 0, 12, true);

            // Patrón 4: Clientes recurrentes
            $clientes_recurrentes = array_filter($this->ctrClientesMasFrecuentes(20), function ($cliente) {
                return $cliente['total_citas'] >= 3;
            });
            $patrones['clientes_recurrentes'] = $clientes_recurrentes;

            return $patrones;
        } catch (Exception $e) {
            error_log("Error en ctrIdentificarPatrones: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Generar alertas basadas en historial
     */
    static public function ctrGenerarAlertasHistorial()
    {
        try {
            $alertas = array();

            // Alerta 1: Clientes con muchas cancelaciones
            $analisis = self::ctrAnalisisSatisfaccion();
            foreach ($analisis as $cliente) {
                if ($cliente['total_citas'] >= 3 && $cliente['tasa_asistencia'] < 50) {
                    $alertas[] = array(
                        'tipo' => 'warning',
                        'titulo' => 'Cliente con baja asistencia',
                        'mensaje' => "El cliente {$cliente['nombre_cliente']} tiene una tasa de asistencia del {$cliente['tasa_asistencia']}%",
                        'datos' => $cliente
                    );
                }
            }

            // Alerta 2: Clientes inactivos (más de 6 meses sin citas)
            $fecha_limite = date('Y-m-d', strtotime('-6 months'));
            foreach ($analisis as $cliente) {
                if ($cliente['ultima_cita'] && $cliente['ultima_cita'] < $fecha_limite) {
                    $alertas[] = array(
                        'tipo' => 'info',
                        'titulo' => 'Cliente inactivo',
                        'mensaje' => "El cliente {$cliente['nombre_cliente']} no ha tenido citas desde {$cliente['ultima_cita']}",
                        'datos' => $cliente
                    );
                }
            }

            return $alertas;
        } catch (Exception $e) {
            error_log("Error en ctrGenerarAlertasHistorial: " . $e->getMessage());
            return array();
        }
    }
}
?>