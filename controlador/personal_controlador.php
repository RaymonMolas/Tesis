<?php
require_once "../modelo/modelo_personal.php";

class PersonalControlador
{

    /**
     * Crear nuevo personal
     */
    static public function ctrCrearPersonal()
    {
        if (isset($_POST["nombre_personal"])) {
            // Validar datos
            $errores = self::ctrValidarDatosPersonal($_POST);
            if (!empty($errores)) {
                return implode(", ", $errores);
            }

            $datos = array(
                "nombre" => ucwords(strtolower(trim($_POST["nombre_personal"]))),
                "apellido" => ucwords(strtolower(trim($_POST["apellido_personal"]))),
                "cedula" => trim($_POST["cedula_personal"]),
                "telefono" => trim($_POST["telefono_personal"]),
                "email" => strtolower(trim($_POST["email_personal"])),
                "direccion" => trim($_POST["direccion_personal"]),
                "cargo" => $_POST["cargo_personal"],
                "estado" => $_POST["estado_personal"] ?? "activo"
            );

            $respuesta = ModeloPersonal::mdlCrearPersonal($datos);

            if ($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡Personal creado!",
                        text: "El personal ha sido creado correctamente",
                        showConfirmButton: false,
                        timer: 2000
                    }).then(function() {
                        window.location = "index.php?pagina=tabla/personales";
                    });
                </script>';
            } else if ($respuesta == "cedula_duplicada") {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "La cédula ya está registrada en el sistema"
                    });
                </script>';
            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Hubo un error al crear el personal"
                    });
                </script>';
            }
        }
    }

    /**
     * Listar personal
     */
    static public function ctrListarPersonal()
    {
        try {
            return ModeloPersonal::mdlListarPersonal();
        } catch (Exception $e) {
            error_log("Error en ctrListarPersonal: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener personal por ID
     */
    static public function ctrObtenerPersonal($id)
    {
        if (!$id)
            return false;

        try {
            return ModeloPersonal::mdlObtenerPersonal($id);
        } catch (Exception $e) {
            error_log("Error en ctrObtenerPersonal: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Editar personal
     */
    static public function ctrEditarPersonal()
    {
        if (isset($_POST["id_personal_editar"])) {
            $id = $_POST["id_personal_editar"];

            // Validar datos
            $errores = self::ctrValidarDatosPersonal($_POST, $id);
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
                "nombre" => ucwords(strtolower(trim($_POST["nombre_personal"]))),
                "apellido" => ucwords(strtolower(trim($_POST["apellido_personal"]))),
                "cedula" => trim($_POST["cedula_personal"]),
                "telefono" => trim($_POST["telefono_personal"]),
                "email" => strtolower(trim($_POST["email_personal"])),
                "direccion" => trim($_POST["direccion_personal"]),
                "cargo" => $_POST["cargo_personal"],
                "estado" => $_POST["estado_personal"]
            );

            $respuesta = ModeloPersonal::mdlActualizarPersonal($id, $datos);

            if ($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡Personal actualizado!",
                        text: "Los datos han sido actualizados correctamente",
                        showConfirmButton: false,
                        timer: 2000
                    }).then(function() {
                        window.location = "index.php?pagina=tabla/personales";
                    });
                </script>';
            } else if ($respuesta == "cedula_duplicada") {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "La cédula ya está registrada por otro personal"
                    });
                </script>';
            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Hubo un error al actualizar el personal"
                    });
                </script>';
            }
        }
    }

    /**
     * Eliminar personal
     */
    static public function ctrEliminarPersonal()
    {
        if (isset($_GET["id_personal_eliminar"])) {
            $id = $_GET["id_personal_eliminar"];

            $respuesta = ModeloPersonal::mdlEliminarPersonal($id);

            if ($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡Personal eliminado!",
                        text: "El personal ha sido eliminado correctamente",
                        showConfirmButton: false,
                        timer: 2000
                    }).then(function() {
                        window.location = "index.php?pagina=tabla/personales";
                    });
                </script>';
            } else if ($respuesta == "tiene_ordenes") {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "No se puede eliminar",
                        text: "Este personal tiene órdenes de trabajo asignadas"
                    });
                </script>';
            } else if ($respuesta == "tiene_presupuestos") {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "No se puede eliminar",
                        text: "Este personal tiene presupuestos asignados"
                    });
                </script>';
            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Hubo un error al eliminar el personal"
                    });
                </script>';
            }
        }
    }

    /**
     * Cambiar estado del personal
     */
    static public function ctrCambiarEstadoPersonal()
    {
        if (isset($_POST["id_personal_estado"]) && isset($_POST["nuevo_estado"])) {
            $id = $_POST["id_personal_estado"];
            $estado = $_POST["nuevo_estado"];

            $respuesta = ModeloPersonal::mdlCambiarEstado($id, $estado);

            if ($respuesta == "ok") {
                echo json_encode(array("status" => "success", "message" => "Estado actualizado correctamente"));
            } else {
                echo json_encode(array("status" => "error", "message" => "Error al actualizar el estado"));
            }
        }
    }

    /**
     * Obtener personal por cargo
     */
    static public function ctrObtenerPersonalPorCargo($cargo)
    {
        try {
            return ModeloPersonal::mdlObtenerPersonalPorCargo($cargo);
        } catch (Exception $e) {
            error_log("Error en ctrObtenerPersonalPorCargo: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener personal activo
     */
    static public function ctrObtenerPersonalActivo()
    {
        try {
            return ModeloPersonal::mdlObtenerPersonalActivo();
        } catch (Exception $e) {
            error_log("Error en ctrObtenerPersonalActivo: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener estadísticas del personal
     */
    static public function ctrEstadisticasPersonal()
    {
        try {
            return ModeloPersonal::mdlEstadisticasPersonal();
        } catch (Exception $e) {
            error_log("Error en ctrEstadisticasPersonal: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Buscar personal con filtros
     */
    static public function ctrBuscarPersonal()
    {
        if (isset($_POST["buscar_personal"])) {
            $filtros = array(
                "nombre" => $_POST["nombre_buscar"] ?? "",
                "cedula" => $_POST["cedula_buscar"] ?? "",
                "cargo" => $_POST["cargo_buscar"] ?? "",
                "estado" => $_POST["estado_buscar"] ?? ""
            );

            return ModeloPersonal::mdlBuscarPersonal($filtros);
        }
        return array();
    }

    /**
     * Obtener carga de trabajo del personal
     */
    static public function ctrObtenerCargaTrabajo($id_personal = null)
    {
        try {
            $fecha_inicio = date('Y-m-01'); // Inicio del mes actual
            $fecha_fin = date('Y-m-t'); // Fin del mes actual

            return ModeloPersonal::mdlObtenerCargaTrabajo($id_personal, $fecha_inicio, $fecha_fin);
        } catch (Exception $e) {
            error_log("Error en ctrObtenerCargaTrabajo: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Validar datos del personal
     */
    static public function ctrValidarDatosPersonal($datos, $id_excluir = null)
    {
        $errores = array();

        // Validar nombre
        if (empty($datos['nombre_personal']) || strlen(trim($datos['nombre_personal'])) < 2) {
            $errores[] = "El nombre debe tener al menos 2 caracteres";
        }

        // Validar apellido
        if (empty($datos['apellido_personal']) || strlen(trim($datos['apellido_personal'])) < 2) {
            $errores[] = "El apellido debe tener al menos 2 caracteres";
        }

        // Validar cédula
        if (empty($datos['cedula_personal'])) {
            $errores[] = "La cédula es obligatoria";
        } else {
            $cedula = trim($datos['cedula_personal']);
            if (strlen($cedula) < 6) {
                $errores[] = "La cédula debe tener al menos 6 caracteres";
            }

            // Verificar si la cédula ya existe
            if (ModeloPersonal::mdlVerificarCedula($cedula, $id_excluir)) {
                $errores[] = "La cédula ya está registrada";
            }
        }

        // Validar email si se proporciona
        if (!empty($datos['email_personal'])) {
            if (!filter_var($datos['email_personal'], FILTER_VALIDATE_EMAIL)) {
                $errores[] = "El formato del email no es válido";
            }
        }

        // Validar cargo
        $cargos_validos = ['mecanico', 'electricista', 'gerente', 'recepcionista', 'administrador'];
        if (empty($datos['cargo_personal']) || !in_array($datos['cargo_personal'], $cargos_validos)) {
            $errores[] = "Debe seleccionar un cargo válido";
        }

        return $errores;
    }

    /**
     * Generar reporte de personal
     */
    static public function ctrGenerarReportePersonal()
    {
        try {
            $personal = self::ctrListarPersonal();
            $estadisticas = self::ctrEstadisticasPersonal();
            $carga_trabajo = self::ctrObtenerCargaTrabajo();

            return array(
                'personal' => $personal,
                'estadisticas' => $estadisticas,
                'carga_trabajo' => $carga_trabajo,
                'fecha_generacion' => date('Y-m-d H:i:s')
            );
        } catch (Exception $e) {
            error_log("Error en ctrGenerarReportePersonal: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Exportar personal a CSV
     */
    static public function ctrExportarPersonalCSV()
    {
        try {
            $personal = self::ctrListarPersonal();

            if (empty($personal)) {
                return false;
            }

            $filename = "personal_" . date('Y-m-d_H-i-s') . ".csv";
            $filepath = "../exports/" . $filename;

            // Crear directorio si no existe
            if (!is_dir('../exports')) {
                mkdir('../exports', 0755, true);
            }

            $file = fopen($filepath, 'w');

            // Escribir cabeceras
            fputcsv($file, [
                'ID',
                'Nombre',
                'Apellido',
                'Cédula',
                'Teléfono',
                'Email',
                'Dirección',
                'Cargo',
                'Estado',
                'Usuario',
                'Rol',
                'Fecha Registro'
            ]);

            // Escribir datos
            foreach ($personal as $persona) {
                fputcsv($file, [
                    $persona['id_personal'],
                    $persona['nombre'],
                    $persona['apellido'],
                    $persona['cedula'],
                    $persona['telefono'],
                    $persona['email'],
                    $persona['direccion'],
                    $persona['cargo'],
                    $persona['estado'],
                    $persona['usuario'] ?? 'Sin usuario',
                    $persona['rol'] ?? 'N/A',
                    $persona['fecha_registro']
                ]);
            }

            fclose($file);
            return $filename;
        } catch (Exception $e) {
            error_log("Error en ctrExportarPersonalCSV: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener personal disponible para asignar
     */
    static public function ctrPersonalDisponible($cargo = null)
    {
        try {
            if ($cargo) {
                return ModeloPersonal::mdlObtenerPersonalPorCargo($cargo);
            } else {
                return ModeloPersonal::mdlObtenerPersonalActivo();
            }
        } catch (Exception $e) {
            error_log("Error en ctrPersonalDisponible: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Contar personal por cargo
     */
    static public function ctrContarPersonalPorCargo()
    {
        try {
            return ModeloPersonal::mdlContarPersonalPorCargo();
        } catch (Exception $e) {
            error_log("Error en ctrContarPersonalPorCargo: " . $e->getMessage());
            return array();
        }
    }
}
?>