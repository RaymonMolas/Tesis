<?php
require_once "../modelo/modelo_producto.php";

class ProductoControlador
{

    /**
     * Crear nuevo producto
     */
    static public function ctrCrearProducto()
    {
        if (isset($_POST["codigo_producto"])) {
            // Validar datos
            $errores = self::ctrValidarDatosProducto($_POST);
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
                "codigo_producto" => strtoupper(trim($_POST["codigo_producto"])),
                "nombre" => trim($_POST["nombre_producto"]),
                "descripcion" => trim($_POST["descripcion_producto"]),
                "marca" => trim($_POST["marca_producto"]),
                "precio_compra" => floatval($_POST["precio_compra"]),
                "precio_venta" => floatval($_POST["precio_venta"]),
                "stock_actual" => intval($_POST["stock_actual"]),
                "stock_minimo" => intval($_POST["stock_minimo"]),
                "estado" => $_POST["estado_producto"] ?? "activo"
            );

            $respuesta = ModeloProducto::mdlCrearProducto($datos);

            if ($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡Producto creado!",
                        text: "El producto ha sido creado correctamente",
                        showConfirmButton: false,
                        timer: 2000
                    }).then(function() {
                        window.location = "index.php?pagina=tabla/productos";
                    });
                </script>';
            } else if ($respuesta == "codigo_duplicado") {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "El código de producto ya existe"
                    });
                </script>';
            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Hubo un error al crear el producto"
                    });
                </script>';
            }
        }
    }

    /**
     * Listar productos
     */
    static public function ctrListarProductos()
    {
        try {
            return ModeloProducto::mdlListarProductos();
        } catch (Exception $e) {
            error_log("Error en ctrListarProductos: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener producto por ID
     */
    static public function ctrObtenerProducto($id)
    {
        if (!$id)
            return false;

        try {
            return ModeloProducto::mdlObtenerProducto($id);
        } catch (Exception $e) {
            error_log("Error en ctrObtenerProducto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener producto por código
     */
    static public function ctrObtenerProductoPorCodigo($codigo)
    {
        if (!$codigo)
            return false;

        try {
            return ModeloProducto::mdlObtenerProductoPorCodigo($codigo);
        } catch (Exception $e) {
            error_log("Error en ctrObtenerProductoPorCodigo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Editar producto
     */
    static public function ctrEditarProducto()
    {
        if (isset($_POST["id_producto_editar"])) {
            $id = $_POST["id_producto_editar"];

            // Validar datos
            $errores = self::ctrValidarDatosProducto($_POST, $id);
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
                "codigo_producto" => strtoupper(trim($_POST["codigo_producto"])),
                "nombre" => trim($_POST["nombre_producto"]),
                "descripcion" => trim($_POST["descripcion_producto"]),
                "marca" => trim($_POST["marca_producto"]),
                "precio_compra" => floatval($_POST["precio_compra"]),
                "precio_venta" => floatval($_POST["precio_venta"]),
                "stock_actual" => intval($_POST["stock_actual"]),
                "stock_minimo" => intval($_POST["stock_minimo"]),
                "estado" => $_POST["estado_producto"]
            );

            $respuesta = ModeloProducto::mdlActualizarProducto($id, $datos);

            if ($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡Producto actualizado!",
                        text: "El producto ha sido actualizado correctamente",
                        showConfirmButton: false,
                        timer: 2000
                    }).then(function() {
                        window.location = "index.php?pagina=tabla/productos";
                    });
                </script>';
            } else if ($respuesta == "codigo_duplicado") {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "El código de producto ya existe"
                    });
                </script>';
            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Hubo un error al actualizar el producto"
                    });
                </script>';
            }
        }
    }

    /**
     * Eliminar producto
     */
    static public function ctrEliminarProducto()
    {
        if (isset($_GET["id_producto_eliminar"])) {
            $id = $_GET["id_producto_eliminar"];

            $respuesta = ModeloProducto::mdlEliminarProducto($id);

            if ($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡Producto eliminado!",
                        text: "El producto ha sido eliminado correctamente",
                        showConfirmButton: false,
                        timer: 2000
                    }).then(function() {
                        window.location = "index.php?pagina=tabla/productos";
                    });
                </script>';
            } else if ($respuesta == "usado_en_facturas") {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "No se puede eliminar",
                        text: "Este producto ha sido usado en facturas"
                    });
                </script>';
            } else if ($respuesta == "usado_en_presupuestos") {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "No se puede eliminar",
                        text: "Este producto ha sido usado en presupuestos"
                    });
                </script>';
            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Hubo un error al eliminar el producto"
                    });
                </script>';
            }
        }
    }

    /**
     * Actualizar stock de producto
     */
    static public function ctrActualizarStock()
    {
        if (isset($_POST["id_producto_stock"]) && isset($_POST["cantidad_stock"])) {
            $id = $_POST["id_producto_stock"];
            $cantidad = intval($_POST["cantidad_stock"]);
            $tipo = $_POST["tipo_movimiento"] ?? 'manual';

            $respuesta = ModeloProducto::mdlActualizarStock($id, $cantidad, $tipo);

            if ($respuesta == "ok") {
                echo json_encode(array(
                    "status" => "success",
                    "message" => "Stock actualizado correctamente"
                ));
            } else if ($respuesta == "stock_insuficiente") {
                echo json_encode(array(
                    "status" => "error",
                    "message" => "Stock insuficiente para realizar el movimiento"
                ));
            } else if ($respuesta == "producto_no_encontrado") {
                echo json_encode(array(
                    "status" => "error",
                    "message" => "Producto no encontrado"
                ));
            } else {
                echo json_encode(array(
                    "status" => "error",
                    "message" => "Error al actualizar el stock"
                ));
            }
        }
    }

    /**
     * Cambiar estado del producto
     */
    static public function ctrCambiarEstadoProducto()
    {
        if (isset($_POST["id_producto_estado"]) && isset($_POST["nuevo_estado"])) {
            $id = $_POST["id_producto_estado"];
            $estado = $_POST["nuevo_estado"];

            $respuesta = ModeloProducto::mdlCambiarEstado($id, $estado);

            if ($respuesta == "ok") {
                echo json_encode(array("status" => "success", "message" => "Estado actualizado correctamente"));
            } else {
                echo json_encode(array("status" => "error", "message" => "Error al actualizar el estado"));
            }
        }
    }

    /**
     * Obtener productos con stock bajo
     */
    static public function ctrProductosStockBajo()
    {
        try {
            return ModeloProducto::mdlProductosStockBajo();
        } catch (Exception $e) {
            error_log("Error en ctrProductosStockBajo: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Buscar productos
     */
    static public function ctrBuscarProductos($termino = null)
    {
        if (!$termino && isset($_POST["termino_busqueda"])) {
            $termino = $_POST["termino_busqueda"];
        }

        if (!$termino)
            return array();

        try {
            return ModeloProducto::mdlBuscarProductos($termino);
        } catch (Exception $e) {
            error_log("Error en ctrBuscarProductos: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener productos activos
     */
    static public function ctrObtenerProductosActivos()
    {
        try {
            return ModeloProducto::mdlObtenerProductosActivos();
        } catch (Exception $e) {
            error_log("Error en ctrObtenerProductosActivos: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener estadísticas de productos
     */
    static public function ctrEstadisticasProductos()
    {
        try {
            return ModeloProducto::mdlEstadisticasProductos();
        } catch (Exception $e) {
            error_log("Error en ctrEstadisticasProductos: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener productos por marca
     */
    static public function ctrProductosPorMarca()
    {
        try {
            return ModeloProducto::mdlObtenerProductosPorMarca();
        } catch (Exception $e) {
            error_log("Error en ctrProductosPorMarca: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener productos más vendidos
     */
    static public function ctrProductosMasVendidos($limite = 10)
    {
        try {
            return ModeloProducto::mdlProductosMasVendidos($limite);
        } catch (Exception $e) {
            error_log("Error en ctrProductosMasVendidos: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Generar código de producto automático
     */
    static public function ctrGenerarCodigoProducto($prefijo = 'PROD')
    {
        try {
            return ModeloProducto::mdlGenerarCodigoProducto($prefijo);
        } catch (Exception $e) {
            error_log("Error en ctrGenerarCodigoProducto: " . $e->getMessage());
            return $prefijo . '001';
        }
    }

    /**
     * Validar datos del producto
     */
    static public function ctrValidarDatosProducto($datos, $id_excluir = null)
    {
        $errores = array();

        // Validar código
        if (empty($datos['codigo_producto'])) {
            $errores[] = "El código del producto es obligatorio";
        } else {
            $codigo = strtoupper(trim($datos['codigo_producto']));
            if (strlen($codigo) < 3) {
                $errores[] = "El código debe tener al menos 3 caracteres";
            }

            // Verificar si el código ya existe
            if (ModeloProducto::mdlVerificarCodigo($codigo, $id_excluir)) {
                $errores[] = "El código de producto ya existe";
            }
        }

        // Validar nombre
        if (empty($datos['nombre_producto']) || strlen(trim($datos['nombre_producto'])) < 2) {
            $errores[] = "El nombre debe tener al menos 2 caracteres";
        }

        // Validar precios
        if (empty($datos['precio_compra']) || !is_numeric($datos['precio_compra']) || floatval($datos['precio_compra']) < 0) {
            $errores[] = "El precio de compra debe ser un número válido mayor o igual a 0";
        }

        if (empty($datos['precio_venta']) || !is_numeric($datos['precio_venta']) || floatval($datos['precio_venta']) < 0) {
            $errores[] = "El precio de venta debe ser un número válido mayor o igual a 0";
        }

        // Validar que precio de venta sea mayor al de compra
        if (is_numeric($datos['precio_compra']) && is_numeric($datos['precio_venta'])) {
            if (floatval($datos['precio_venta']) < floatval($datos['precio_compra'])) {
                $errores[] = "El precio de venta debe ser mayor al precio de compra";
            }
        }

        // Validar stock
        if (!isset($datos['stock_actual']) || !is_numeric($datos['stock_actual']) || intval($datos['stock_actual']) < 0) {
            $errores[] = "El stock actual debe ser un número válido mayor o igual a 0";
        }

        if (!isset($datos['stock_minimo']) || !is_numeric($datos['stock_minimo']) || intval($datos['stock_minimo']) < 0) {
            $errores[] = "El stock mínimo debe ser un número válido mayor o igual a 0";
        }

        return $errores;
    }

    /**
     * Generar reporte de productos
     */
    static public function ctrGenerarReporteProductos()
    {
        try {
            $productos = self::ctrListarProductos();
            $estadisticas = self::ctrEstadisticasProductos();
            $stock_bajo = self::ctrProductosStockBajo();
            $por_marca = self::ctrProductosPorMarca();
            $mas_vendidos = self::ctrProductosMasVendidos();

            return array(
                'productos' => $productos,
                'estadisticas' => $estadisticas,
                'stock_bajo' => $stock_bajo,
                'por_marca' => $por_marca,
                'mas_vendidos' => $mas_vendidos,
                'fecha_generacion' => date('Y-m-d H:i:s')
            );
        } catch (Exception $e) {
            error_log("Error en ctrGenerarReporteProductos: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Exportar productos a CSV
     */
    static public function ctrExportarProductosCSV()
    {
        try {
            $productos = self::ctrListarProductos();

            if (empty($productos)) {
                return false;
            }

            $filename = "productos_" . date('Y-m-d_H-i-s') . ".csv";
            $filepath = "../exports/" . $filename;

            // Crear directorio si no existe
            if (!is_dir('../exports')) {
                mkdir('../exports', 0755, true);
            }

            $file = fopen($filepath, 'w');

            // Escribir cabeceras
            fputcsv($file, [
                'Código',
                'Nombre',
                'Descripción',
                'Marca',
                'Precio Compra',
                'Precio Venta',
                'Stock Actual',
                'Stock Mínimo',
                'Estado',
                'Estado Stock',
                'Fecha Registro'
            ]);

            // Escribir datos
            foreach ($productos as $producto) {
                fputcsv($file, [
                    $producto['codigo_producto'],
                    $producto['nombre'],
                    $producto['descripcion'],
                    $producto['marca'],
                    $producto['precio_compra'],
                    $producto['precio_venta'],
                    $producto['stock_actual'],
                    $producto['stock_minimo'],
                    $producto['estado'],
                    $producto['estado_stock'],
                    $producto['fecha_registro']
                ]);
            }

            fclose($file);
            return $filename;
        } catch (Exception $e) {
            error_log("Error en ctrExportarProductosCSV: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Búsqueda avanzada de productos
     */
    static public function ctrBusquedaAvanzada()
    {
        if (isset($_POST["busqueda_avanzada"])) {
            $termino = $_POST["termino"] ?? "";
            $marca = $_POST["marca_filtro"] ?? "";
            $estado = $_POST["estado_filtro"] ?? "";
            $stock_bajo = isset($_POST["solo_stock_bajo"]);

            try {
                $productos = ModeloProducto::mdlListarProductos();

                // Aplicar filtros
                if (!empty($termino)) {
                    $productos = array_filter($productos, function ($producto) use ($termino) {
                        return (stripos($producto['nombre'], $termino) !== false ||
                            stripos($producto['codigo_producto'], $termino) !== false ||
                            stripos($producto['descripcion'], $termino) !== false);
                    });
                }

                if (!empty($marca)) {
                    $productos = array_filter($productos, function ($producto) use ($marca) {
                        return stripos($producto['marca'], $marca) !== false;
                    });
                }

                if (!empty($estado)) {
                    $productos = array_filter($productos, function ($producto) use ($estado) {
                        return $producto['estado'] === $estado;
                    });
                }

                if ($stock_bajo) {
                    $productos = array_filter($productos, function ($producto) {
                        return $producto['estado_stock'] === 'stock_bajo' || $producto['estado_stock'] === 'sin_stock';
                    });
                }

                return array_values($productos);
            } catch (Exception $e) {
                error_log("Error en ctrBusquedaAvanzada: " . $e->getMessage());
                return array();
            }
        }
        return array();
    }

    /**
     * Alertas de stock
     */
    static public function ctrAlertasStock()
    {
        try {
            $productos_bajo_stock = self::ctrProductosStockBajo();
            $alertas = array();

            foreach ($productos_bajo_stock as $producto) {
                $tipo_alerta = 'warning';
                $mensaje = "Stock bajo";

                if ($producto['stock_actual'] <= 0) {
                    $tipo_alerta = 'danger';
                    $mensaje = "Sin stock";
                } elseif ($producto['stock_actual'] <= ($producto['stock_minimo'] / 2)) {
                    $tipo_alerta = 'danger';
                    $mensaje = "Stock crítico";
                }

                $alertas[] = array(
                    'producto' => $producto,
                    'tipo' => $tipo_alerta,
                    'mensaje' => $mensaje
                );
            }

            return $alertas;
        } catch (Exception $e) {
            error_log("Error en ctrAlertasStock: " . $e->getMessage());
            return array();
        }
    }
}
?>