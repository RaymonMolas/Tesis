<?php

require_once __DIR__ . "/../modelo/modelo_cliente.php";

class ClienteControlador
{
    // Listar todos los clientes
    static public function ctrListarClientes()
    {
        try {
            return ModeloCliente::mdlListarClientes();
        } catch (Exception $e) {
            error_log("Error en ctrListarClientes: " . $e->getMessage());
            return array();
        }
    }

    // Obtener un cliente específico
    static public function ctrObtenerCliente($id)
    {
        try {
            return ModeloCliente::mdlObtenerCliente($id);
        } catch (Exception $e) {
            error_log("Error en ctrObtenerCliente: " . $e->getMessage());
            return false;
        }
    }

    // Registrar nuevo cliente
    static public function ctrRegistrarCliente()
    {
        if (isset($_POST["nombre"]) && isset($_POST["apellido"])) {
            try {
                // Validar datos obligatorios
                if (empty($_POST["nombre"])) {
                    throw new Exception("El nombre es obligatorio");
                }

                if (empty($_POST["apellido"])) {
                    throw new Exception("El apellido es obligatorio");
                }

                if (empty($_POST["telefono"])) {
                    throw new Exception("El teléfono es obligatorio");
                }

                // Validar email si se proporciona
                if (!empty($_POST["email"]) && !filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("El formato del email no es válido");
                }

                // Verificar si el email ya existe
                if (!empty($_POST["email"]) && ModeloCliente::mdlVerificarEmail($_POST["email"])) {
                    throw new Exception("Ya existe un cliente con este email");
                }

                // Verificar si el teléfono ya existe
                if (ModeloCliente::mdlVerificarTelefono($_POST["telefono"])) {
                    throw new Exception("Ya existe un cliente con este teléfono");
                }

                // Preparar datos
                $datos = array(
                    "nombre" => trim($_POST["nombre"]),
                    "apellido" => trim($_POST["apellido"]),
                    "telefono" => trim($_POST["telefono"]),
                    "email" => trim($_POST["email"]) ?? null,
                    "direccion" => trim($_POST["direccion"]) ?? null
                );

                $respuesta = ModeloCliente::mdlRegistrarCliente($datos);

                if ($respuesta == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡Éxito!",
                            text: "El cliente ha sido registrado correctamente",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "index.php?pagina=tabla/clientes";
                            }
                        });
                    </script>';
                } else {
                    throw new Exception("Error al registrar el cliente");
                }
                return "ok";

            } catch (Exception $e) {
                error_log("Error en ctrRegistrarCliente: " . $e->getMessage());

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
                    text: "Faltan datos obligatorios para registrar el cliente",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';
        }
        return null;
    }

    // Actualizar cliente
    static public function ctrActualizarCliente()
    {
        if (isset($_POST["id_cliente"])) {
            try {
                // Validar datos obligatorios
                if (empty($_POST["nombre"])) {
                    throw new Exception("El nombre es obligatorio");
                }

                if (empty($_POST["apellido"])) {
                    throw new Exception("El apellido es obligatorio");
                }

                if (empty($_POST["telefono"])) {
                    throw new Exception("El teléfono es obligatorio");
                }

                // Validar email si se proporciona
                if (!empty($_POST["email"]) && !filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("El formato del email no es válido");
                }

                // Verificar si el email ya existe (excluyendo el cliente actual)
                if (!empty($_POST["email"]) && ModeloCliente::mdlVerificarEmail($_POST["email"], $_POST["id_cliente"])) {
                    throw new Exception("Ya existe otro cliente con este email");
                }

                // Verificar si el teléfono ya existe (excluyendo el cliente actual)
                if (ModeloCliente::mdlVerificarTelefono($_POST["telefono"], $_POST["id_cliente"])) {
                    throw new Exception("Ya existe otro cliente con este teléfono");
                }

                // Preparar datos
                $datos = array(
                    "id_cliente" => $_POST["id_cliente"],
                    "nombre" => trim($_POST["nombre"]),
                    "apellido" => trim($_POST["apellido"]),
                    "telefono" => trim($_POST["telefono"]),
                    "email" => trim($_POST["email"]) ?? null,
                    "direccion" => trim($_POST["direccion"]) ?? null
                );

                $respuesta = ModeloCliente::mdlActualizarCliente($datos);

                if ($respuesta == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡Éxito!",
                            text: "El cliente ha sido actualizado correctamente",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "index.php?pagina=tabla/clientes";
                            }
                        });
                    </script>';
                } else {
                    throw new Exception("Error al actualizar el cliente");
                }
                return "ok";

            } catch (Exception $e) {
                error_log("Error en ctrActualizarCliente: " . $e->getMessage());

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

    // Eliminar cliente
    static public function ctrEliminarCliente()
    {
        if (isset($_POST["eliminarCliente"])) {
            try {
                // Verificar si el cliente puede ser eliminado
                if (!ModeloCliente::mdlPuedeEliminar($_POST["eliminarCliente"])) {
                    throw new Exception("No se puede eliminar el cliente porque tiene vehículos o facturas asociadas");
                }

                $respuesta = ModeloCliente::mdlEliminarCliente($_POST["eliminarCliente"]);

                if ($respuesta == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡Éxito!",
                            text: "El cliente ha sido eliminado correctamente",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "index.php?pagina=tabla/clientes";
                            }
                        });
                    </script>';
                } else {
                    throw new Exception("No se pudo eliminar el cliente");
                }
                return "ok";

            } catch (Exception $e) {
                error_log("Error en ctrEliminarCliente: " . $e->getMessage());

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
                    text: "No se especificó qué cliente eliminar",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';
        }
        return null;
    }

    // Buscar clientes
    static public function ctrBuscarClientes($termino, $tipo = 'nombre')
    {
        try {
            switch ($tipo) {
                case 'nombre':
                    return ModeloCliente::mdlBuscarPorNombre($termino);
                case 'telefono':
                    return ModeloCliente::mdlBuscarPorTelefono($termino);
                default:
                    return ModeloCliente::mdlBuscarPorNombre($termino);
            }
        } catch (Exception $e) {
            error_log("Error en ctrBuscarClientes: " . $e->getMessage());
            return array();
        }
    }

    // Obtener clientes más frecuentes
    static public function ctrObtenerClientesFrecuentes($limite = 10)
    {
        try {
            return ModeloCliente::mdlObtenerClientesFrecuentes($limite);
        } catch (Exception $e) {
            error_log("Error en ctrObtenerClientesFrecuentes: " . $e->getMessage());
            return array();
        }
    }

    // Obtener estadísticas de clientes
    static public function ctrObtenerEstadisticasClientes()
    {
        try {
            return ModeloCliente::mdlObtenerEstadisticas();
        } catch (Exception $e) {
            error_log("Error en ctrObtenerEstadisticasClientes: " . $e->getMessage());
            return array();
        }
    }

    // Obtener clientes recientes para el dashboard
    static public function ctrObtenerClientesRecientes($limite = 5)
    {
        try {
            return ModeloCliente::mdlObtenerClientesRecientes($limite);
        } catch (Exception $e) {
            error_log("Error en ctrObtenerClientesRecientes: " . $e->getMessage());
            return array();
        }
    }

    // Contar total de clientes
    static public function ctrContarClientes()
    {
        try {
            return ModeloCliente::mdlContarClientes();
        } catch (Exception $e) {
            error_log("Error en ctrContarClientes: " . $e->getMessage());
            return 0;
        }
    }

    // Obtener historial completo de un cliente
    static public function ctrObtenerHistorialCliente($id_cliente)
    {
        try {
            return ModeloCliente::mdlObtenerHistorialCompleto($id_cliente);
        } catch (Exception $e) {
            error_log("Error en ctrObtenerHistorialCliente: " . $e->getMessage());
            return array();
        }
    }

    // Validar datos de cliente
    static public function ctrValidarDatosCliente($datos)
    {
        $errores = array();

        // Validar nombre
        if (empty($datos['nombre']) || strlen(trim($datos['nombre'])) < 2) {
            $errores[] = "El nombre debe tener al menos 2 caracteres";
        }

        // Validar apellido
        if (empty($datos['apellido']) || strlen(trim($datos['apellido'])) < 2) {
            $errores[] = "El apellido debe tener al menos 2 caracteres";
        }

        // Validar teléfono
        if (empty($datos['telefono'])) {
            $errores[] = "El teléfono es obligatorio";
        } else if (!preg_match('/^[0-9\-\+\(\)\s]+$/', $datos['telefono'])) {
            $errores[] = "El formato del teléfono no es válido";
        }

        // Validar email si se proporciona
        if (!empty($datos['email']) && !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = "El formato del email no es válido";
        }

        return $errores;
    }
}
?>