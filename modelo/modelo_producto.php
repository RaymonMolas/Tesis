<?php
require_once "conexion.php";

class ModeloProducto
{

    /**
     * Crear nuevo producto
     */
    static public function mdlCrearProducto($datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO producto (codigo_producto, nombre, descripcion, marca, precio_compra, precio_venta, stock_actual, stock_minimo, estado)
                VALUES (:codigo_producto, :nombre, :descripcion, :marca, :precio_compra, :precio_venta, :stock_actual, :stock_minimo, :estado)
            ");

            $stmt->bindParam(":codigo_producto", $datos["codigo_producto"], PDO::PARAM_STR);
            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
            $stmt->bindParam(":marca", $datos["marca"], PDO::PARAM_STR);
            $stmt->bindParam(":precio_compra", $datos["precio_compra"], PDO::PARAM_STR);
            $stmt->bindParam(":precio_venta", $datos["precio_venta"], PDO::PARAM_STR);
            $stmt->bindParam(":stock_actual", $datos["stock_actual"], PDO::PARAM_INT);
            $stmt->bindParam(":stock_minimo", $datos["stock_minimo"], PDO::PARAM_INT);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (Exception $e) {
            error_log("Error en mdlCrearProducto: " . $e->getMessage());
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                if (strpos($e->getMessage(), 'codigo_producto') !== false) {
                    return "codigo_duplicado";
                }
            }
            return "error";
        }
    }

    /**
     * Obtener lista de productos
     */
    static public function mdlListarProductos()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT *,
                    CASE 
                        WHEN stock_actual <= 0 THEN 'sin_stock'
                        WHEN stock_actual <= stock_minimo THEN 'stock_bajo'
                        ELSE 'stock_normal'
                    END as estado_stock
                FROM producto
                ORDER BY nombre
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlListarProductos: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener producto por ID
     */
    static public function mdlObtenerProducto($id)
    {
        try {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM producto WHERE id_producto = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlObtenerProducto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener producto por código
     */
    static public function mdlObtenerProductoPorCodigo($codigo)
    {
        try {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM producto WHERE codigo_producto = :codigo");
            $stmt->bindParam(":codigo", $codigo, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlObtenerProductoPorCodigo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar producto
     */
    static public function mdlActualizarProducto($id, $datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE producto SET
                    codigo_producto = :codigo_producto,
                    nombre = :nombre,
                    descripcion = :descripcion,
                    marca = :marca,
                    precio_compra = :precio_compra,
                    precio_venta = :precio_venta,
                    stock_actual = :stock_actual,
                    stock_minimo = :stock_minimo,
                    estado = :estado,
                    fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id_producto = :id
            ");

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":codigo_producto", $datos["codigo_producto"], PDO::PARAM_STR);
            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
            $stmt->bindParam(":marca", $datos["marca"], PDO::PARAM_STR);
            $stmt->bindParam(":precio_compra", $datos["precio_compra"], PDO::PARAM_STR);
            $stmt->bindParam(":precio_venta", $datos["precio_venta"], PDO::PARAM_STR);
            $stmt->bindParam(":stock_actual", $datos["stock_actual"], PDO::PARAM_INT);
            $stmt->bindParam(":stock_minimo", $datos["stock_minimo"], PDO::PARAM_INT);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (Exception $e) {
            error_log("Error en mdlActualizarProducto: " . $e->getMessage());
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                if (strpos($e->getMessage(), 'codigo_producto') !== false) {
                    return "codigo_duplicado";
                }
            }
            return "error";
        }
    }

    /**
     * Eliminar producto
     */
    static public function mdlEliminarProducto($id)
    {
        try {
            // Verificar si se ha usado en facturas
            $stmt = Conexion::conectar()->prepare("SELECT COUNT(*) FROM detalle_factura WHERE id_producto = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            $usado_facturas = $stmt->fetchColumn();

            if ($usado_facturas > 0) {
                return "usado_en_facturas";
            }

            // Verificar si se ha usado en presupuestos
            $stmt = Conexion::conectar()->prepare("SELECT COUNT(*) FROM detalle_presupuesto WHERE id_producto = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            $usado_presupuestos = $stmt->fetchColumn();

            if ($usado_presupuestos > 0) {
                return "usado_en_presupuestos";
            }

            // Si no se ha usado, eliminar
            $stmt = Conexion::conectar()->prepare("DELETE FROM producto WHERE id_producto = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            return $stmt->execute() ? "ok" : "error";
        } catch (Exception $e) {
            error_log("Error en mdlEliminarProducto: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Actualizar stock de producto
     */
    static public function mdlActualizarStock($id, $cantidad, $tipo_movimiento = 'manual')
    {
        try {
            $conexion = Conexion::conectar();
            $conexion->beginTransaction();

            // Obtener stock actual
            $stmt = $conexion->prepare("SELECT stock_actual FROM producto WHERE id_producto = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            $stock_actual = $stmt->fetchColumn();

            if ($stock_actual === false) {
                $conexion->rollBack();
                return "producto_no_encontrado";
            }

            // Calcular nuevo stock
            $nuevo_stock = $stock_actual + $cantidad;

            if ($nuevo_stock < 0) {
                $conexion->rollBack();
                return "stock_insuficiente";
            }

            // Actualizar stock
            $stmt = $conexion->prepare("
                UPDATE producto SET 
                    stock_actual = :nuevo_stock,
                    fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id_producto = :id
            ");
            $stmt->bindParam(":nuevo_stock", $nuevo_stock, PDO::PARAM_INT);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();

            $conexion->commit();
            return "ok";
        } catch (Exception $e) {
            $conexion->rollBack();
            error_log("Error en mdlActualizarStock: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Obtener productos con stock bajo
     */
    static public function mdlProductosStockBajo()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT *,
                    CASE 
                        WHEN stock_actual <= 0 THEN 'sin_stock'
                        WHEN stock_actual <= stock_minimo THEN 'stock_bajo'
                        ELSE 'stock_normal'
                    END as estado_stock
                FROM producto
                WHERE stock_actual <= stock_minimo AND estado = 'activo'
                ORDER BY stock_actual ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlProductosStockBajo: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Buscar productos
     */
    static public function mdlBuscarProductos($termino)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT *,
                    CASE 
                        WHEN stock_actual <= 0 THEN 'sin_stock'
                        WHEN stock_actual <= stock_minimo THEN 'stock_bajo'
                        ELSE 'stock_normal'
                    END as estado_stock
                FROM producto
                WHERE (nombre LIKE :termino OR codigo_producto LIKE :termino OR marca LIKE :termino)
                AND estado = 'activo'
                ORDER BY nombre
            ");
            $termino = '%' . $termino . '%';
            $stmt->bindParam(":termino", $termino, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlBuscarProductos: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener productos activos
     */
    static public function mdlObtenerProductosActivos()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT *,
                    CASE 
                        WHEN stock_actual <= 0 THEN 'sin_stock'
                        WHEN stock_actual <= stock_minimo THEN 'stock_bajo'
                        ELSE 'stock_normal'
                    END as estado_stock
                FROM producto
                WHERE estado = 'activo'
                ORDER BY nombre
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlObtenerProductosActivos: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Cambiar estado del producto
     */
    static public function mdlCambiarEstado($id, $estado)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE producto SET 
                    estado = :estado,
                    fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id_producto = :id
            ");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            return $stmt->execute() ? "ok" : "error";
        } catch (Exception $e) {
            error_log("Error en mdlCambiarEstado: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Verificar código de producto único
     */
    static public function mdlVerificarCodigo($codigo, $id_excluir = null)
    {
        try {
            $sql = "SELECT COUNT(*) FROM producto WHERE codigo_producto = :codigo";
            if ($id_excluir) {
                $sql .= " AND id_producto != :id_excluir";
            }

            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->bindParam(":codigo", $codigo, PDO::PARAM_STR);
            if ($id_excluir) {
                $stmt->bindParam(":id_excluir", $id_excluir, PDO::PARAM_INT);
            }

            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Error en mdlVerificarCodigo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener estadísticas de productos
     */
    static public function mdlEstadisticasProductos()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    COUNT(*) as total_productos,
                    SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as activos,
                    SUM(CASE WHEN estado = 'inactivo' THEN 1 ELSE 0 END) as inactivos,
                    SUM(CASE WHEN estado = 'descontinuado' THEN 1 ELSE 0 END) as descontinuados,
                    SUM(CASE WHEN stock_actual <= 0 THEN 1 ELSE 0 END) as sin_stock,
                    SUM(CASE WHEN stock_actual <= stock_minimo AND stock_actual > 0 THEN 1 ELSE 0 END) as stock_bajo,
                    SUM(stock_actual * precio_compra) as valor_inventario_compra,
                    SUM(stock_actual * precio_venta) as valor_inventario_venta,
                    COUNT(DISTINCT marca) as marcas_diferentes
                FROM producto
            ");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlEstadisticasProductos: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Obtener productos por marca
     */
    static public function mdlObtenerProductosPorMarca()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    marca,
                    COUNT(*) as cantidad,
                    SUM(stock_actual) as total_stock,
                    SUM(stock_actual * precio_venta) as valor_total
                FROM producto
                WHERE estado = 'activo'
                GROUP BY marca
                ORDER BY cantidad DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlObtenerProductosPorMarca: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Productos más vendidos
     */
    static public function mdlProductosMasVendidos($limite = 10)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    p.id_producto,
                    p.codigo_producto,
                    p.nombre,
                    p.marca,
                    p.precio_venta,
                    p.stock_actual,
                    SUM(df.cantidad) as total_vendido,
                    SUM(df.subtotal) as total_ingresos
                FROM producto p
                INNER JOIN detalle_factura df ON p.id_producto = df.id_producto
                INNER JOIN factura f ON df.id_factura = f.id_factura
                WHERE f.estado != 'anulado' AND f.fecha_emision >= CURDATE() - INTERVAL 3 MONTH
                GROUP BY p.id_producto
                ORDER BY total_vendido DESC
                LIMIT :limite
            ");
            $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en mdlProductosMasVendidos: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Generar código de producto automático
     */
    static public function mdlGenerarCodigoProducto($prefijo = 'PROD')
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT MAX(CAST(SUBSTRING(codigo_producto, LENGTH(:prefijo) + 1) AS UNSIGNED)) as ultimo_numero
                FROM producto 
                WHERE codigo_producto LIKE CONCAT(:prefijo2, '%')
            ");
            $stmt->bindParam(":prefijo", $prefijo, PDO::PARAM_STR);
            $stmt->bindParam(":prefijo2", $prefijo, PDO::PARAM_STR);
            $stmt->execute();

            $ultimo_numero = $stmt->fetchColumn();
            $siguiente_numero = ($ultimo_numero) ? $ultimo_numero + 1 : 1;

            return $prefijo . str_pad($siguiente_numero, 3, '0', STR_PAD_LEFT);
        } catch (Exception $e) {
            error_log("Error en mdlGenerarCodigoProducto: " . $e->getMessage());
            return $prefijo . '001';
        }
    }
}
?>