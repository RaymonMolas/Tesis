<?php

require_once __DIR__ . "/../modelo/modelo_producto.php";

class ProductoControlador
{
    // Listar todos los productos
    static public function ctrListarProductos()
    {
        try {
            return ModeloProducto::mdlListarProductos();
        } catch (Exception $e) {
            error_log("Error en ctrListarProductos: " . $e->getMessage());
            return array();
        }
    }

    // Buscar producto (alias para mantener compatibilidad)
    static public function buscarProducto()
    {
        return self::ctrListarProductos();
    }

    // Obtener producto específico
    static public function ctrObtenerProducto($id)
    {
        try {
            return ModeloProducto::mdlObtenerProducto($id);
        } catch (Exception $e) {
            error_log("Error en ctrObtenerProducto: " . $e->getMessage());
            return false;
        }
    }

    // Registrar nuevo producto
    static public function ctrRegistrarProducto()
    {
        if (isset($_POST["nombre"]) && isset($_POST["precio"])) {
            try {
                // Validar datos obligatorios
                if (empty(trim($_POST["nombre"]))) {
                    throw new Exception("El nombre del producto es obligatorio");
                }

                if (empty(trim($_POST["codigo"]))) {
                    throw new Exception("El código del producto es obligatorio");
                }

                if (empty($_POST["precio"]) || $_POST["precio"] <= 0) {
                    throw new Exception("El precio debe ser mayor a 0");
                }

                if (empty($_POST["categoria"])) {
                    throw new Exception("La categoría es obligatoria");
                }

                // Validar que el código no exista
                $productoExistente = ModeloProducto::mdlBuscarPorCodigo($_POST["codigo"]);
                if ($productoExistente) {
                    throw new Exception("Ya existe un producto con este código");
                }

                $datos = array(
                    "nombre" => trim($_POST["nombre"]),
                    "codigo" => trim($_POST["codigo"]),
                    "descripcion" => trim($_POST["descripcion"] ?? ""),
                    "categoria" => trim($_POST["categoria"]),
                    "marca" => trim($_POST["marca"] ?? ""),
                    "precio" => (float) $_POST["precio"],
                    "precio_compra" => !empty($_POST["precio_compra"]) ? (float) $_POST["precio_compra"] : null,
                    "stock" => !empty($_POST["stock"]) ? (int) $_POST["stock"] : 0,
                    "stock_minimo" => !empty($_POST["stock_minimo"]) ? (int) $_POST["stock_minimo"] : 0,
                    "unidad_medida" => trim($_POST["unidad_medida"] ?? "unidad"),
                    "ubicacion" => trim($_POST["ubicacion"] ?? ""),
                    "proveedor" => trim($_POST["proveedor"] ?? ""),
                    "estado" => $_POST["estado"] ?? "activo"
                );

                $resultado = ModeloProducto::mdlRegistrarProducto($datos);

                if ($resultado == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡Éxito!",
                            text: "El producto ha sido registrado correctamente"
                        }).then(function(result) {
                            if (result.value) {
                                window.location = "index.php?pagina=tabla/productos";
                            }
                        });
                    </script>';
                } else {
                    throw new Exception("Error al registrar el producto");
                }
            } catch (Exception $e) {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "' . $e->getMessage() . '"
                    });
                </script>';
            }
        }

        return "ok";
    }

    // Actualizar producto
    static public function ctrActualizarProducto()
    {
        if (isset($_POST["id_producto"]) && isset($_POST["nombre"])) {
            try {
                // Validar que el código no exista en otro registro
                if (!empty($_POST["codigo"])) {
                    $productoExistente = ModeloProducto::mdlBuscarPorCodigo($_POST["codigo"]);
                    if ($productoExistente && $productoExistente["id_producto"] != $_POST["id_producto"]) {
                        throw new Exception("Ya existe otro producto con este código");
                    }
                }

                $datos = array(
                    "id_producto" => (int) $_POST["id_producto"],
                    "nombre" => trim($_POST["nombre"]),
                    "codigo" => trim($_POST["codigo"]),
                    "descripcion" => trim($_POST["descripcion"] ?? ""),
                    "categoria" => trim($_POST["categoria"]),
                    "marca" => trim($_POST["marca"] ?? ""),
                    "precio" => (float) $_POST["precio"],
                    "precio_compra" => !empty($_POST["precio_compra"]) ? (float) $_POST["precio_compra"] : null,
                    "stock" => !empty($_POST["stock"]) ? (int) $_POST["stock"] : 0,
                    "stock_minimo" => !empty($_POST["stock_minimo"]) ? (int) $_POST["stock_minimo"] : 0,
                    "unidad_medida" => trim($_POST["unidad_medida"] ?? "unidad"),
                    "ubicacion" => trim($_POST["ubicacion"] ?? ""),
                    "proveedor" => trim($_POST["proveedor"] ?? ""),
                    "estado" => $_POST["estado"] ?? "activo"
                );

                $resultado = ModeloProducto::mdlActualizarProducto($datos);

                if ($resultado == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡Éxito!",
                            text: "El producto ha sido actualizado correctamente"
                        }).then(function(result) {
                            if (result.value) {
                                window.location = "index.php?pagina=tabla/productos";
                            }
                        });
                    </script>';
                } else {
                    throw new Exception("Error al actualizar el producto");
                }
            } catch (Exception $e) {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "' . $e->getMessage() . '"
                    });
                </script>';
            }
        }

        return "ok";
    }

    // Eliminar producto
    static public function ctrEliminarProducto()
    {
        if (isset($_POST["eliminarProducto"])) {
            try {
                $id = (int) $_POST["eliminarProducto"];
                
                // Verificar si el producto está siendo usado en facturas o presupuestos
                $estaEnUso = ModeloProducto::mdlEstaEnUso($id);
                if ($estaEnUso) {
                    echo '<script>
                        Swal.fire({
                            icon: "warning",
                            title: "No se puede eliminar",
                            text: "Este producto está siendo usado en facturas o presupuestos"
                        });
                    </script>';
                    return "error";
                }

                $resultado = ModeloProducto::mdlEliminarProducto($id);

                if ($resultado == "ok") {
                    echo '<script>
                        Swal.fire({
                            icon: "success",
                            title: "¡Éxito!",
                            text: "El producto ha sido eliminado correctamente"
                        });
                    </script>';
                } else {
                    throw new Exception("Error al eliminar el producto");
                }
            } catch (Exception $e) {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "' . $e->getMessage() . '"
                    });
                </script>';
            }
        }

        return "ok";
    }

    // Obtener productos activos
    static public function ctrObtenerProductosActivos()
    {
        try {
            return ModeloProducto::mdlObtenerProductosActivos();
        } catch (Exception $e) {
            error_log("Error en ctrObtenerProductosActivos: " . $e->getMessage());
            return array();
        }
    }

    // Obtener productos con stock bajo
    static public function ctrObtenerProductosStockBajo()
    {
        try {
            return ModeloProducto::mdlObtenerProductosStockBajo();
        } catch (Exception $e) {
            error_log("Error en ctrObtenerProductosStockBajo: " . $e->getMessage());
            return array();
        }
    }

    // Buscar productos por término
    static public function ctrBuscarProductos($termino)
    {
        try {
            return ModeloProducto::mdlBuscarProductos($termino);
        } catch (Exception $e) {
            error_log("Error en ctrBuscarProductos: " . $e->getMessage());
            return array();
        }
    }

    // Obtener productos por categoría
    static public function ctrObtenerProductosPorCategoria($categoria)
    {
        try {
            return ModeloProducto::mdlObtenerProductosPorCategoria($categoria);
        } catch (Exception $e) {
            error_log("Error en ctrObtenerProductosPorCategoria: " . $e->getMessage());
            return array();
        }
    }

    // Obtener categorías de productos
    static public function ctrObtenerCategorias()
    {
        try {
            return ModeloProducto::mdlObtenerCategorias();
        } catch (Exception $e) {
            error_log("Error en ctrObtenerCategorias: " . $e->getMessage());
            return array();
        }
    }

    // Actualizar stock
    static public function ctrActualizarStock($id_producto, $cantidad, $tipo = "salida")
    {
        try {
            return ModeloProducto::mdlActualizarStock($id_producto, $cantidad, $tipo);
        } catch (Exception $e) {
            error_log("Error en ctrActualizarStock: " . $e->getMessage());
            return "error";
        }
    }

    // Obtener estadísticas de productos
    static public function ctrObtenerEstadisticasProductos()
    {
        try {
            return ModeloProducto::mdlObtenerEstadisticas();
        } catch (Exception $e) {
            error_log("Error en ctrObtenerEstadisticasProductos: " . $e->getMessage());
            return array();
        }
    }

    // Contar total de productos
    static public function ctrContarProductos()
    {
        try {
            return ModeloProducto::mdlContarProductos();
        } catch (Exception $e) {
            error_log("Error en ctrContarProductos: " . $e->getMessage());
            return 0;
        }
    }

    // Generar reporte de inventario
    static public function ctrGenerarReporteInventario()
    {
        try {
            return ModeloProducto::mdlGenerarReporteInventario();
        } catch (Exception $e) {
            error_log("Error en ctrGenerarReporteInventario: " . $e->getMessage());
            return array();
        }
    }
}
?>