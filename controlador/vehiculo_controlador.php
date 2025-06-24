<?php
require_once "../modelo/modelo_vehiculo.php";
require_once "../modelo/modelo_cliente.php";

class VehiculoControlador
{

    /**
     * Contar total de vehículos
     */
    static public function ctrContarVehiculos()
    {
        try {
            return ModeloVehiculo::mdlContarVehiculos();
        } catch (Exception $e) {
            error_log("Error en ctrContarVehiculos: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Listar vehículos de un cliente específico
     */
    static public function ctrListarVehiculosCliente($id_cliente)
    {
        if (!$id_cliente)
            return array();

        try {
            return ModeloVehiculo::mdlListarVehiculosCliente($id_cliente);
        } catch (Exception $e) {
            error_log("Error en ctrListarVehiculosCliente: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener lista de vehículos con información del cliente
     */
    static public function ctrListarVehiculos()
    {
        try {
            return ModeloVehiculo::mdlListarVehiculos();
        } catch (Exception $e) {
            error_log("Error en ctrListarVehiculos: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener un vehículo específico
     */
    static public function ctrObtenerVehiculo($id)
    {
        if (!$id)
            return false;

        try {
            return ModeloVehiculo::mdlObtenerVehiculo($id);
        } catch (Exception $e) {
            error_log("Error en ctrObtenerVehiculo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registrar nuevo vehículo con validación de matrícula
     */
    static public function ctrRegistrarVehiculo()
    {
        if (isset($_POST["matricula"])) {
            try {
                // Validar datos obligatorios
                if (empty(trim($_POST["matricula"]))) {
                    throw new Exception("La matrícula es obligatoria");
                }

                if (empty(trim($_POST["marca"]))) {
                    throw new Exception("La marca es obligatoria");
                }

                if (empty(trim($_POST["modelo"]))) {
                    throw new Exception("El modelo es obligatorio");
                }

                if (empty($_POST["anho"]) || !is_numeric($_POST["anho"])) {
                    throw new Exception("El año debe ser un número válido");
                }

                if (empty($_POST["id_cliente"])) {
                    throw new Exception("Debe seleccionar un cliente");
                }

                // Limpiar y validar matrícula
                $matricula = strtoupper(trim($_POST["matricula"]));
                $matricula = preg_replace('/[^A-Z0-9]/', '', $matricula);

                if (strlen($matricula) < 3) {
                    throw new Exception("La matrícula debe tener al menos 3 caracteres");
                }

                // Validar formato de matrícula paraguaya
                if (!self::ctrValidarFormatoMatricula($matricula)) {
                    // Solo advertir, no bloquear
                    error_log("Advertencia: Matrícula con formato no estándar: " . $matricula);
                }

                // Validar que el cliente existe
                $cliente = ModeloCliente::mdlObtenerCliente($_POST["id_cliente"]);
                if (!$cliente) {
                    throw new Exception("El cliente seleccionado no existe");
                }

                // Validar año
                $anho_actual = date('Y');
                if ($_POST["anho"] < 1900 || $_POST["anho"] > ($anho_actual + 1)) {
                    throw new Exception("El año debe estar entre 1900 y " . ($anho_actual + 1));
                }

                // Preparar datos
                $datos = array(
                    "matricula" => $matricula,
                    "marca" => ucwords(trim($_POST["marca"])),
                    "modelo" => ucwords(trim($_POST["modelo"])),
                    "anho" => intval($_POST["anho"]),
                    "color" => ucwords(trim($_POST["color"] ?? "")),
                    "id_cliente" => intval($_POST["id_cliente"])
                );

                // Registrar vehículo
                $respuesta = ModeloVehiculo::mdlRegistrarVehiculo($datos);

                if ($respuesta == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡Éxito!",
                            html: "El vehículo <strong>' . $datos["marca"] . ' ' . $datos["modelo"] . '</strong><br>con matrícula <strong>' . $datos["matricula"] . '</strong><br>ha sido registrado correctamente",
                            showConfirmButton: true,
                            confirmButtonText: "Ver Vehículos"
                        }).then(function(result){
                            if(result.value){
                                window.location = "index.php?pagina=tabla/vehiculos";
                            }
                        });
                    </script>';
                    return "ok";
                } elseif ($respuesta == "matricula_duplicada") {
                    throw new Exception("Ya existe un vehículo con esa matrícula");
                } else {
                    throw new Exception("Error al registrar el vehículo");
                }

            } catch (Exception $e) {
                error_log("Error en ctrRegistrarVehiculo: " . $e->getMessage());
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error al registrar vehículo",
                        text: "' . addslashes($e->getMessage()) . '",
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return "error";
            }
        }
    }

    /**
     * Actualizar vehículo existente
     */
    static public function ctrActualizarVehiculo()
    {
        if (isset($_POST["id_vehiculo"])) {
            try {
                // Validaciones similares al registro
                if (empty(trim($_POST["matricula"]))) {
                    throw new Exception("La matrícula es obligatoria");
                }

                // Limpiar matrícula
                $matricula = strtoupper(trim($_POST["matricula"]));
                $matricula = preg_replace('/[^A-Z0-9]/', '', $matricula);

                $datos = array(
                    "id_vehiculo" => intval($_POST["id_vehiculo"]),
                    "matricula" => $matricula,
                    "marca" => ucwords(trim($_POST["marca"])),
                    "modelo" => ucwords(trim($_POST["modelo"])),
                    "anho" => intval($_POST["anho"]),
                    "color" => ucwords(trim($_POST["color"] ?? "")),
                    "id_cliente" => intval($_POST["id_cliente"])
                );

                $respuesta = ModeloVehiculo::mdlActualizarVehiculo($datos);

                if ($respuesta == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡Actualizado!",
                            text: "El vehículo ha sido actualizado correctamente",
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location = "index.php?pagina=tabla/vehiculos";
                        });
                    </script>';
                    return "ok";
                } elseif ($respuesta == "matricula_duplicada") {
                    throw new Exception("Ya existe otro vehículo con esa matrícula");
                } else {
                    throw new Exception("Error al actualizar el vehículo");
                }

            } catch (Exception $e) {
                error_log("Error en ctrActualizarVehiculo: " . $e->getMessage());
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error al actualizar",
                        text: "' . addslashes($e->getMessage()) . '"
                    });
                </script>';
                return "error";
            }
        }
    }

    /**
     * Eliminar vehículo (cambiar estado a inactivo)
     */
    static public function ctrEliminarVehiculo($id)
    {
        try {
            // Verificar que no tenga órdenes de trabajo asociadas
            $tiene_ordenes = ModeloVehiculo::mdlTieneOrdenesAsociadas($id);
            if ($tiene_ordenes) {
                return "tiene_ordenes";
            }

            return ModeloVehiculo::mdlEliminarVehiculo($id);

        } catch (Exception $e) {
            error_log("Error en ctrEliminarVehiculo: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Buscar vehículo por matrícula
     */
    static public function ctrBuscarPorMatricula($matricula)
    {
        try {
            if (empty($matricula)) {
                return false;
            }

            // Limpiar matrícula
            $matricula = strtoupper(trim($matricula));
            $matricula = preg_replace('/[^A-Z0-9]/', '', $matricula);

            return ModeloVehiculo::mdlBuscarPorMatricula($matricula);

        } catch (Exception $e) {
            error_log("Error en ctrBuscarPorMatricula: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validar formato de matrícula paraguaya
     */
    static public function ctrValidarFormatoMatricula($matricula)
    {
        // Patrones comunes de matrículas paraguayas
        $patrones = array(
            '/^[A-Z]{3}[0-9]{3}$/',     // ABC123 (formato estándar)
            '/^[A-Z]{3}[0-9]{4}$/',     // ABC1234 (formato nuevo)
            '/^[A-Z]{2}[0-9]{4}$/',     // AB1234 (formato alternativo)
            '/^[A-Z]{4}[0-9]{2}$/',     // ABCD12 (formato especial)
            '/^[A-Z]{1}[0-9]{3}[A-Z]{2}$/', // A123BC (formato especial)
        );

        foreach ($patrones as $patron) {
            if (preg_match($patron, $matricula)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Procesar matrícula reconocida por IA
     */
    static public function ctrProcesarMatriculaIA($matricula_reconocida)
    {
        try {
            // Limpiar y validar matrícula reconocida
            $matricula = strtoupper(trim($matricula_reconocida));
            $matricula = preg_replace('/[^A-Z0-9]/', '', $matricula);

            if (strlen($matricula) < 3) {
                return array(
                    'valida' => false,
                    'error' => 'Matrícula muy corta'
                );
            }

            // Verificar si ya existe
            $vehiculo_existente = self::ctrBuscarPorMatricula($matricula);

            $resultado = array(
                'valida' => true,
                'matricula_limpia' => $matricula,
                'formato_valido' => self::ctrValidarFormatoMatricula($matricula),
                'existe' => $vehiculo_existente ? true : false
            );

            if ($vehiculo_existente) {
                $resultado['vehiculo_existente'] = $vehiculo_existente;
                $resultado['mensaje'] = 'Esta matrícula ya está registrada';
            } else {
                $resultado['mensaje'] = 'Matrícula disponible para registro';
            }

            return $resultado;

        } catch (Exception $e) {
            error_log("Error en ctrProcesarMatriculaIA: " . $e->getMessage());
            return array(
                'valida' => false,
                'error' => 'Error procesando matrícula'
            );
        }
    }

    /**
     * Obtener estadísticas de vehículos
     */
    static public function ctrEstadisticasVehiculos()
    {
        try {
            return ModeloVehiculo::mdlEstadisticasVehiculos();
        } catch (Exception $e) {
            error_log("Error en ctrEstadisticasVehiculos: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Buscar vehículos con filtros avanzados
     */
    static public function ctrBuscarVehiculos($filtros = array())
    {
        try {
            return ModeloVehiculo::mdlBuscarVehiculosConFiltros($filtros);
        } catch (Exception $e) {
            error_log("Error en ctrBuscarVehiculos: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener historial completo de un vehículo
     */
    static public function ctrObtenerHistorialVehiculo($id_vehiculo)
    {
        try {
            $vehiculo = self::ctrObtenerVehiculo($id_vehiculo);
            if (!$vehiculo) {
                return false;
            }

            // Obtener órdenes de trabajo
            $ordenes = ModeloVehiculo::mdlObtenerOrdenesVehiculo($id_vehiculo);

            // Obtener presupuestos
            $presupuestos = ModeloVehiculo::mdlObtenerPresupuestosVehiculo($id_vehiculo);

            // Obtener historial de servicios
            $historial = ModeloVehiculo::mdlObtenerHistorialServicios($id_vehiculo);

            return array(
                'vehiculo' => $vehiculo,
                'ordenes' => $ordenes,
                'presupuestos' => $presupuestos,
                'historial' => $historial,
                'total_ordenes' => count($ordenes),
                'total_presupuestos' => count($presupuestos),
                'total_servicios' => count($historial)
            );

        } catch (Exception $e) {
            error_log("Error en ctrObtenerHistorialVehiculo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Sugerir mantenimientos según kilometraje
     */
    static public function ctrSugerirMantenimientos($id_vehiculo)
    {
        try {
            $vehiculo = self::ctrObtenerVehiculo($id_vehiculo);
            if (!$vehiculo || !$vehiculo['kilometraje_actual']) {
                return array();
            }

            $km = $vehiculo['kilometraje_actual'];
            $sugerencias = array();

            // Mantenimientos básicos según kilometraje
            if ($km >= 5000) {
                $sugerencias[] = array(
                    'tipo' => 'aceite_motor',
                    'descripcion' => 'Cambio de aceite de motor',
                    'prioridad' => 'alta',
                    'kilometraje' => 5000
                );
            }

            if ($km >= 10000) {
                $sugerencias[] = array(
                    'tipo' => 'filtro_aire',
                    'descripcion' => 'Cambio de filtro de aire',
                    'prioridad' => 'media',
                    'kilometraje' => 10000
                );
            }

            if ($km >= 15000) {
                $sugerencias[] = array(
                    'tipo' => 'filtro_aceite',
                    'descripcion' => 'Cambio de filtro de aceite',
                    'prioridad' => 'alta',
                    'kilometraje' => 15000
                );
            }

            if ($km >= 20000) {
                $sugerencias[] = array(
                    'tipo' => 'bujias',
                    'descripcion' => 'Revisión/cambio de bujías',
                    'prioridad' => 'media',
                    'kilometraje' => 20000
                );
            }

            if ($km >= 40000) {
                $sugerencias[] = array(
                    'tipo' => 'aceite_caja',
                    'descripcion' => 'Cambio de aceite de caja',
                    'prioridad' => 'media',
                    'kilometraje' => 40000
                );
            }

            return $sugerencias;

        } catch (Exception $e) {
            error_log("Error en ctrSugerirMantenimientos: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Validar datos del vehículo antes de guardar
     */
    static public function ctrValidarDatosVehiculo($datos)
    {
        $errores = array();

        // Validar matrícula
        if (empty($datos['matricula'])) {
            $errores[] = "La matrícula es obligatoria";
        } else {
            $matricula = strtoupper(trim($datos['matricula']));
            if (strlen($matricula) < 3) {
                $errores[] = "La matrícula debe tener al menos 3 caracteres";
            }
        }

        // Validar marca
        if (empty($datos['marca']) || strlen(trim($datos['marca'])) < 2) {
            $errores[] = "La marca debe tener al menos 2 caracteres";
        }

        // Validar modelo
        if (empty($datos['modelo']) || strlen(trim($datos['modelo'])) < 2) {
            $errores[] = "El modelo debe tener al menos 2 caracteres";
        }

        // Validar año
        if (empty($datos['anho']) || !is_numeric($datos['anho'])) {
            $errores[] = "El año debe ser un número válido";
        } else {
            $anho = intval($datos['anho']);
            if ($anho < 1900 || $anho > (date('Y') + 1)) {
                $errores[] = "El año debe estar entre 1900 y " . (date('Y') + 1);
            }
        }

        // Validar cliente
        if (empty($datos['id_cliente']) || !is_numeric($datos['id_cliente'])) {
            $errores[] = "Debe seleccionar un cliente válido";
        }

        return $errores;
    }

    /**
     * Generar reporte de vehículos por marca
     */
    static public function ctrReporteVehiculosPorMarca()
    {
        try {
            return ModeloVehiculo::mdlReporteVehiculosPorMarca();
        } catch (Exception $e) {
            error_log("Error en ctrReporteVehiculosPorMarca: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener vehículos que necesitan mantenimiento
     */
    static public function ctrVehiculosMantenimientoPendiente()
    {
        try {
            return ModeloVehiculo::mdlVehiculosMantenimientoPendiente();
        } catch (Exception $e) {
            error_log("Error en ctrVehiculosMantenimientoPendiente: " . $e->getMessage());
            return array();
        }
    }
}
?>