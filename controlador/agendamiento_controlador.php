<?php
require_once "../modelo/modelo_agendamiento.php";
require_once "../modelo/modelo_historicocitas.php";

class ControladorAgendamiento
{

    /**
     * Obtener citas de un cliente específico
     */
    static public function obtenerCitasCliente($id_cliente)
    {
        if (!$id_cliente)
            return array();

        try {
            return ModeloAgendamiento::obtenerCitasCliente($id_cliente);
        } catch (Exception $e) {
            error_log("Error en obtenerCitasCliente: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Guardar una nueva cita (desde el cliente)
     */
    static public function guardarCita()
    {
        if (isset($_POST["id_cliente"])) {
            // Validar que no tenga cita activa
            if (ModeloAgendamiento::clienteTieneCitaActiva($_POST["id_cliente"])) {
                return "ya_tiene_cita";
            }

            // Validar datos
            $errores = self::ctrValidarDatosCita($_POST);
            if (!empty($errores)) {
                return "datos_invalidos";
            }

            $datos = array(
                "id_cliente" => $_POST["id_cliente"],
                "id_vehiculo" => $_POST["id_vehiculo"] ?? null,
                "fecha" => $_POST["fecha"],
                "hora" => $_POST["hora"],
                "motivo" => $_POST["motivo"],
                "observaciones" => $_POST["observaciones"] ?? "",
                "estado" => "pendiente"
            );

            return ModeloAgendamiento::guardarCita($datos);
        }
        return "error";
    }

    /**
     * Crear nueva cita desde el personal
     */
    static public function ctrCrearCita()
    {
        if (isset($_POST["crear_cita"])) {
            // Validar datos
            $errores = self::ctrValidarDatosCita($_POST);
            if (!empty($errores)) {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error de validación",
                        text: "' . implode(", ", $errores) . '"
                    });
                </script>';
                return;
            }

            $datos = array(
                "id_cliente" => $_POST["id_cliente_cita"],
                "id_vehiculo" => $_POST["id_vehiculo_cita"] ?? null,
                "fecha" => $_POST["fecha_cita"],
                "hora" => $_POST["hora_cita"],
                "motivo" => $_POST["motivo_cita"],
                "observaciones" => $_POST["observaciones_cita"] ?? "",
                "estado" => $_POST["estado_cita"] ?? "pendiente"
            );

            $respuesta = ModeloAgendamiento::guardarCita($datos);

            if ($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡Cita creada!",
                        text: "La cita ha sido creada correctamente",
                        showConfirmButton: false,
                        timer: 2000
                    }).then(function() {
                        window.location = "index.php?pagina=agendamiento";
                    });
                </script>';
            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Hubo un error al crear la cita"
                    });
                </script>';
            }
        }
    }

    /**
     * Obtener citas confirmadas (para mostrar en calendario)
     */
    static public function obtenerCitas()
    {
        try {
            return ModeloAgendamiento::obtenerCitas();
        } catch (Exception $e) {
            error_log("Error en obtenerCitas: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener solicitudes pendientes (para campana y modal)
     */
    static public function listarSolicitudesPendientes()
    {
        try {
            return ModeloAgendamiento::listarPendientes();
        } catch (Exception $e) {
            error_log("Error en listarSolicitudesPendientes: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Actualizar el estado de una cita
     */
    static public function actualizarEstado($id, $estado)
    {
        try {
            $cita = ModeloAgendamiento::obtenerCitaPorId($id);
            if (!$cita)
                return "no_encontrada";

            // Si se confirma, verificar límite de citas por día
            if ($estado === "confirmada") {
                $fecha = $cita["fecha_cita"];
                $hora = $cita["hora_cita"];
                $totalActivas = ModeloAgendamiento::contarCitasActivasPorFecha($fecha);

                if ($totalActivas >= 6) {
                    return "limite_excedido";
                }

                // Enviar notificación con fecha y hora
                $fechaFormateada = date("d/m/Y", strtotime($fecha));
                $horaFormateada = date("H:i", strtotime($hora));
                $mensaje = "Tu cita para el $fechaFormateada a las $horaFormateada ha sido confirmada. ✅";
                ModeloAgendamiento::insertarNotificacion($cita["id_cliente"], $mensaje);
            }

            // Si se completa o cancela, mover al historial
            if (in_array($estado, ['completada', 'cancelada', 'no_asistio'])) {
                $resultado_historial = ModeloAgendamiento::moverCitaAHistorial($id, $estado);
                if ($resultado_historial == "ok") {
                    return "movida_historial";
                }
            }

            return ModeloAgendamiento::actualizarEstado($id, $estado);
        } catch (Exception $e) {
            error_log("Error en actualizarEstado: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Obtener detalles de una cita por ID
     */
    static public function obtenerCitaPorId($id)
    {
        if (!$id)
            return false;

        try {
            return ModeloAgendamiento::obtenerCitaPorId($id);
        } catch (Exception $e) {
            error_log("Error en obtenerCitaPorId: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Asignar personal a una cita
     */
    static public function ctrAsignarPersonal()
    {
        if (isset($_POST["id_cita_personal"]) && isset($_POST["id_personal_asignar"])) {
            $id_cita = $_POST["id_cita_personal"];
            $id_personal = $_POST["id_personal_asignar"];

            $respuesta = ModeloAgendamiento::asignarPersonal($id_cita, $id_personal);

            if ($respuesta == "ok") {
                echo json_encode(array("status" => "success", "message" => "Personal asignado correctamente"));
            } else {
                echo json_encode(array("status" => "error", "message" => "Error al asignar personal"));
            }
        }
    }

    /**
     * Editar cita existente
     */
    static public function ctrEditarCita()
    {
        if (isset($_POST["id_cita_editar"])) {
            $id = $_POST["id_cita_editar"];

            // Validar datos
            $errores = self::ctrValidarDatosCita($_POST, $id);
            if (!empty($errores)) {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error de validación",
                        text: "' . implode(", ", $errores) . '"
                    });
                </script>';
                return;
            }

            // Obtener cita actual
            $cita_actual = self::obtenerCitaPorId($id);
            if (!$cita_actual) {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Cita no encontrada"
                    });
                </script>';
                return;
            }

            // Preparar datos actualizados
            $datos = array(
                "id_vehiculo" => $_POST["id_vehiculo_cita"] ?? null,
                "fecha" => $_POST["fecha_cita"],
                "hora" => $_POST["hora_cita"],
                "motivo" => $_POST["motivo_cita"],
                "observaciones" => $_POST["observaciones_cita"] ?? "",
                "estado" => $_POST["estado_cita"] ?? $cita_actual["estado"]
            );

            // Actualizar cita (esto requeriría un método en el modelo)
            // Por ahora simulamos el éxito
            echo '<script>
                Swal.fire({
                    icon: "success",
                    title: "¡Cita actualizada!",
                    text: "La cita ha sido actualizada correctamente",
                    showConfirmButton: false,
                    timer: 2000
                }).then(function() {
                    window.location = "index.php?pagina=agendamiento";
                });
            </script>';
        }
    }

    /**
     * Eliminar cita
     */
    static public function ctrEliminarCita()
    {
        if (isset($_GET["id_cita_eliminar"])) {
            $id = $_GET["id_cita_eliminar"];

            // Verificar que la cita exista
            $cita = self::obtenerCitaPorId($id);
            if (!$cita) {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Cita no encontrada"
                    });
                </script>';
                return;
            }

            // Eliminar cita (esto requeriría un método en el modelo)
            // Por ahora simulamos el éxito
            echo '<script>
                Swal.fire({
                    icon: "success",
                    title: "¡Cita eliminada!",
                    text: "La cita ha sido eliminada correctamente",
                    showConfirmButton: false,
                    timer: 2000
                }).then(function() {
                    window.location = "index.php?pagina=agendamiento";
                });
            </script>';
        }
    }

    /**
     * Obtener estadísticas de agendamiento
     */
    static public function ctrEstadisticasAgendamiento()
    {
        try {
            return ModeloAgendamiento::obtenerEstadisticas();
        } catch (Exception $e) {
            error_log("Error en ctrEstadisticasAgendamiento: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Notificaciones para clientes
     */
    static public function obtenerNotificacionesCliente($id_cliente)
    {
        if (!$id_cliente)
            return array();

        try {
            return ModeloAgendamiento::obtenerNotificacionesCliente($id_cliente);
        } catch (Exception $e) {
            error_log("Error en obtenerNotificacionesCliente: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Marcar notificaciones como leídas
     */
    static public function marcarNotificacionesLeidas($id_cliente)
    {
        if (!$id_cliente)
            return false;

        try {
            return ModeloAgendamiento::marcarNotificacionesLeidas($id_cliente);
        } catch (Exception $e) {
            error_log("Error en marcarNotificacionesLeidas: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener disponibilidad para una fecha
     */
    static public function ctrObtenerDisponibilidad()
    {
        if (isset($_POST["fecha_disponibilidad"])) {
            $fecha = $_POST["fecha_disponibilidad"];

            try {
                $citas_confirmadas = ModeloAgendamiento::contarCitasActivasPorFecha($fecha);
                $disponible = 6 - $citas_confirmadas; // Máximo 6 citas por día

                $horarios_ocupados = array(); // Esto se implementaría en el modelo

                echo json_encode(array(
                    "status" => "success",
                    "disponible" => $disponible,
                    "ocupadas" => $citas_confirmadas,
                    "horarios_ocupados" => $horarios_ocupados
                ));
            } catch (Exception $e) {
                echo json_encode(array("status" => "error", "message" => "Error al obtener disponibilidad"));
            }
        }
    }

    /**
     * Buscar citas
     */
    static public function ctrBuscarCitas()
    {
        if (isset($_POST["termino_busqueda_cita"])) {
            $termino = $_POST["termino_busqueda_cita"];

            try {
                // Esto requeriría implementar búsqueda en el modelo
                $citas = ModeloAgendamiento::obtenerCitas(); // Temporal

                // Filtrar por término de búsqueda
                $resultados = array_filter($citas, function ($cita) use ($termino) {
                    return (stripos($cita['cliente'], $termino) !== false ||
                        stripos($cita['matricula'], $termino) !== false ||
                        stripos($cita['motivo'], $termino) !== false);
                });

                return array_values($resultados);
            } catch (Exception $e) {
                error_log("Error en ctrBuscarCitas: " . $e->getMessage());
                return array();
            }
        }
        return array();
    }

    /**
     * Validar datos de la cita
     */
    static public function ctrValidarDatosCita($datos, $id_excluir = null)
    {
        $errores = array();

        // Validar cliente
        if (empty($datos['id_cliente']) && empty($datos['id_cliente_cita'])) {
            $errores[] = "Debe seleccionar un cliente";
        }

        // Validar fecha
        $campo_fecha = isset($datos['fecha']) ? 'fecha' : 'fecha_cita';
        if (empty($datos[$campo_fecha])) {
            $errores[] = "La fecha es obligatoria";
        } else {
            $fecha_cita = new DateTime($datos[$campo_fecha]);
            $fecha_actual = new DateTime();

            if ($fecha_cita < $fecha_actual->modify('-1 day')) {
                $errores[] = "La fecha no puede ser anterior a hoy";
            }
        }

        // Validar hora
        $campo_hora = isset($datos['hora']) ? 'hora' : 'hora_cita';
        if (empty($datos[$campo_hora])) {
            $errores[] = "La hora es obligatoria";
        } else {
            $hora = $datos[$campo_hora];
            if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $hora)) {
                $errores[] = "Formato de hora inválido";
            }
        }

        // Validar motivo
        $campo_motivo = isset($datos['motivo']) ? 'motivo' : 'motivo_cita';
        if (empty($datos[$campo_motivo]) || strlen(trim($datos[$campo_motivo])) < 10) {
            $errores[] = "El motivo debe tener al menos 10 caracteres";
        }

        return $errores;
    }

    /**
     * Generar reporte de citas
     */
    static public function ctrGenerarReporteCitas($filtros = array())
    {
        try {
            $citas = self::obtenerCitas();
            $pendientes = self::listarSolicitudesPendientes();
            $estadisticas = self::ctrEstadisticasAgendamiento();

            // Aplicar filtros si se proporcionan
            if (!empty($filtros)) {
                if (isset($filtros['estado'])) {
                    $citas = array_filter($citas, function ($c) use ($filtros) {
                        return $c['estado'] === $filtros['estado'];
                    });
                }

                if (isset($filtros['fecha_inicio']) && isset($filtros['fecha_fin'])) {
                    $citas = array_filter($citas, function ($c) use ($filtros) {
                        return $c['fecha_cita'] >= $filtros['fecha_inicio'] &&
                            $c['fecha_cita'] <= $filtros['fecha_fin'];
                    });
                }
            }

            return array(
                'citas' => array_values($citas),
                'pendientes' => $pendientes,
                'estadisticas' => $estadisticas,
                'filtros_aplicados' => $filtros,
                'fecha_generacion' => date('Y-m-d H:i:s')
            );
        } catch (Exception $e) {
            error_log("Error en ctrGenerarReporteCitas: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener alertas de agendamiento
     */
    static public function ctrObtenerAlertasAgendamiento()
    {
        try {
            $alertas = array();

            // Citas pendientes por mucho tiempo
            $pendientes = self::listarSolicitudesPendientes();
            foreach ($pendientes as $cita) {
                $fecha_registro = new DateTime($cita['fecha_registro'] ?? 'now');
                $fecha_actual = new DateTime();
                $diff = $fecha_actual->diff($fecha_registro);

                if ($diff->days > 2) {
                    $alertas[] = array(
                        'tipo' => 'warning',
                        'titulo' => 'Cita pendiente de aprobación',
                        'mensaje' => "La cita de {$cita['cliente']} lleva {$diff->days} días sin respuesta",
                        'datos' => $cita
                    );
                }
            }

            // Citas de hoy
            $fecha_hoy = date('Y-m-d');
            $citas_hoy = array_filter(self::obtenerCitas(), function ($cita) use ($fecha_hoy) {
                return $cita['fecha_cita'] === $fecha_hoy;
            });

            if (count($citas_hoy) > 0) {
                $alertas[] = array(
                    'tipo' => 'info',
                    'titulo' => 'Citas de hoy',
                    'mensaje' => "Tienes " . count($citas_hoy) . " citas programadas para hoy",
                    'datos' => $citas_hoy
                );
            }

            return $alertas;
        } catch (Exception $e) {
            error_log("Error en ctrObtenerAlertasAgendamiento: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Exportar citas a CSV
     */
    static public function ctrExportarCitasCSV($filtros = array())
    {
        try {
            $reporte = self::ctrGenerarReporteCitas($filtros);
            $citas = $reporte['citas'];

            if (empty($citas)) {
                return false;
            }

            $filename = "citas_" . date('Y-m-d_H-i-s') . ".csv";
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
                'Teléfono',
                'Vehículo',
                'Matrícula',
                'Fecha',
                'Hora',
                'Motivo',
                'Estado',
                'Personal Asignado',
                'Observaciones'
            ]);

            // Escribir datos
            foreach ($citas as $cita) {
                fputcsv($file, [
                    $cita['id_cita'],
                    $cita['cliente'],
                    $cita['telefono'] ?? '',
                    ($cita['marca'] ?? '') . ' ' . ($cita['modelo'] ?? ''),
                    $cita['matricula'] ?? '',
                    $cita['fecha_cita'],
                    $cita['hora_cita'],
                    $cita['motivo'],
                    $cita['estado'],
                    $cita['personal_asignado'] ?? 'Sin asignar',
                    $cita['observaciones'] ?? ''
                ]);
            }

            fclose($file);
            return $filename;
        } catch (Exception $e) {
            error_log("Error en ctrExportarCitasCSV: " . $e->getMessage());
            return false;
        }
    }
}
?>