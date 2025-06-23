<?php
require_once "../modelo/modelo_producto.php";

class ProductoControlador {
    // Listar todos los productos
    static public function ctrListarProductos() {
        try {
            return ModeloProducto::mdlListarProductos();
        } catch (Exception $e) {
            error_log("Error en ctrListarProductos: " . $e->getMessage());
            return array();
        }
    }

    // Obtener un producto específico
    static public function ctrObtenerProducto($id) {
        try {
            return ModeloProducto::mdlObtenerProducto($id);
        } catch (Exception $e) {
            error_log("Error en ctrObtenerProducto: " . $e->getMessage());
            return false;
        }
    }

    // Registrar nuevo producto
    static public function ctrRegistrarProducto() {
        if (isset($_POST["codigo"])) {
            try {
                $datos = array(
                    "codigo" => $_POST["codigo"],
                    "nombre" => $_POST["nombre"],
                    "descripcion" => $_POST["descripcion"],
                    "precio" => $_POST["precio"],
                    "stock" => $_POST["stock"],
                    "estado" => "activo"
                );

                $respuesta = ModeloProducto::mdlRegistrarProducto($datos);
                
                if ($respuesta == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡Éxito!",
                            text: "El producto ha sido registrado",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "index.php?pagina=tabla/productos";
                            }
                        });
                    </script>';
                }
                return $respuesta;
            } catch (Exception $e) {
                error_log("Error en ctrRegistrarProducto: " . $e->getMessage());
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Ocurrió un error al registrar el producto",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
        return null;
    }

    // Actualizar producto
    static public function ctrActualizarProducto() {
        if (isset($_POST["id_producto"])) {
            try {
                $datos = array(
                    "id_producto" => $_POST["id_producto"],
                    "codigo" => $_POST["codigo"],
                    "nombre" => $_POST["nombre"],
                    "descripcion" => $_POST["descripcion"],
                    "precio" => $_POST["precio"],
                    "stock" => $_POST["stock"],
                    "estado" => $_POST["estado"]
                );

                $respuesta = ModeloProducto::mdlActualizarProducto($datos);
                
                if ($respuesta == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡Éxito!",
                            text: "El producto ha sido actualizado",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "index.php?pagina=tabla/productos";
                            }
                        });
                    </script>';
                }
                return $respuesta;
            } catch (Exception $e) {
                error_log("Error en ctrActualizarProducto: " . $e->getMessage());
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Ocurrió un error al actualizar el producto",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
        return null;
    }

    // Eliminar producto
    static public function ctrEliminarProducto() {
        if (isset($_POST["eliminarProducto"])) {
            try {
                $respuesta = ModeloProducto::mdlEliminarProducto($_POST["eliminarProducto"]);
                
                if ($respuesta == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡Éxito!",
                            text: "El producto ha sido eliminado",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "index.php?pagina=tabla/productos";
                            }
                        });
                    </script>';
                }
                return $respuesta;
            } catch (Exception $e) {
                error_log("Error en ctrEliminarProducto: " . $e->getMessage());
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Ocurrió un error al eliminar el producto",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
        return null;
    }

    // Actualizar stock
    static public function ctrActualizarStock($id_producto, $cantidad) {
        try {
            return ModeloProducto::mdlActualizarStock($id_producto, $cantidad);
        } catch (Exception $e) {
            error_log("Error en ctrActualizarStock: " . $e->getMessage());
            return "error";
        }
    }
}
