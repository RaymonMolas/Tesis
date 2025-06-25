<?php

require_once "conexion.php";

class ModeloProducto
{
    // Listar todos los productos
    static public function mdlListarProductos()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM producto 
                ORDER BY nombre
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlListarProductos: " . $e->getMessage());
            return array();
        }
    }

    // Obtener producto específico
    static public function mdlObtenerProducto($id)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM producto 
                WHERE id_producto = :id
            ");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerProducto: " . $e->getMessage());
            return false;
        }
    }

    // Registrar nuevo producto
    static public function mdlRegistrarProducto($datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO producto (nombre, codigo, descripcion, categoria, marca, precio, precio_compra, stock, stock_minimo, unidad_medida, ubicacion, proveedor, estado, fecha_creacion)
                VALUES (:nombre, :codigo, :descripcion, :categoria, :marca, :precio, :precio_compra, :stock, :stock_minimo, :unidad_medida, :ubicacion, :proveedor, :estado, NOW())
            ");

            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
            $stmt->bindParam(":categoria", $datos["categoria"], PDO::PARAM_STR);
            $stmt->bindParam(":marca", $datos["marca"], PDO::PARAM_STR);
            $stmt->bindParam(":precio", $datos["precio"], PDO::PARAM_STR);
            $stmt->bindParam(":precio_compra", $datos["precio_compra"], PDO::PARAM_STR);
            $stmt->bindParam(":stock", $datos["stock"], PDO::PARAM_INT);
            $stmt->bindParam(":stock_minimo", $datos["stock_minimo"], PDO::PARAM_INT);
            $stmt->bindParam(":unidad_medida", $datos["unidad_medida"], PDO::PARAM_STR);
            $stmt->bindParam(":ubicacion", $datos["ubicacion"], PDO::PARAM_STR);
            $stmt->bindParam(":proveedor", $datos["proveedor"], PDO::PARAM_STR);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlRegistrarProducto: " . $e->getMessage());
            return "error";
        }
    }

    // Actualizar producto
    static public function mdlActualizarProducto($datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE producto 
                SET nombre = :nombre,
                    codigo = :codigo,
                    descripcion = :descripcion,
                    categoria = :categoria,
                    marca = :marca,
                    precio = :precio,
                    precio_compra = :precio_compra,
                    stock = :stock,
                    stock_minimo = :stock_minimo,
                    unidad_medida = :unidad_medida,
                    ubicacion = :ubicacion,
                    proveedor = :proveedor,
                    estado = :estado,
                    fecha_actualizacion = NOW()
                WHERE id_producto = :id_producto
            ");

            $stmt->bindParam(":id_producto", $datos["id_producto"], PDO::PARAM_INT);
            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
            $stmt->bindParam(":categoria", $datos["categoria"], PDO::PARAM_STR);
            $stmt->bindParam(":marca", $datos["marca"], PDO::PARAM_STR);
            $stmt->bindParam(":precio", $datos["precio"], PDO::PARAM_STR);
            $stmt->bindParam(":precio_compra", $datos["precio_compra"], PDO::PARAM_STR);
            $stmt->bindParam(":stock", $datos["stock"], PDO::PARAM_INT);
            $stmt->bindParam(":stock_minimo", $datos["stock_minimo"], PDO::PARAM_INT);
            $stmt->bindParam(":unidad_medida", $datos["unidad_medida"], PDO::PARAM_STR);
            $stmt->bindParam(":ubicacion", $datos["ubicacion"], PDO::PARAM_STR);
            $stmt->bindParam(":proveedor", $datos["proveedor"], PDO::PARAM_STR);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlActualizarProducto: " . $e->getMessage());
            return "error";
        }
    }

    // Eliminar producto
    static public function mdlEliminarProducto($id)
    {
        try {
            $stmt = Conexion::conectar()->prepare("DELETE FROM producto WHERE id_producto = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlEliminarProducto: " . $e->getMessage());
            return "error";
        }
    }

    // Buscar producto por código
    static public function mdlBuscarPorCodigo($codigo)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM producto 
                WHERE codigo = :codigo
            ");
            $stmt->bindParam(":codigo", $codigo, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlBuscarPorCodigo: " . $e->getMessage());
            return false;
        }
    }

    // Verificar si está en uso
    static public function mdlEstaEnUso($id_producto)
    {
        try {
            // Verificar en detalles de factura
            $stmt = Conexion::conectar()->prepare("
                SELECT COUNT(*) as total 
                FROM detalle_factura 
                WHERE id_producto = :id_producto
            ");
            $stmt->bindParam(":id_producto", $id_producto, PDO::PARAM_INT);
            $stmt->execute();
            $resultado1 = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verificar en detalles de presupuesto
            $stmt2 = Conexion::conectar()->prepare("
                SELECT COUNT(*) as total 
                FROM detalle_presupuesto 
                WHERE id_producto = :id_producto
            ");
            $stmt2->bindParam(":id_producto", $id_producto, PDO::PARAM_INT);
            $stmt2->execute();
            $resultado2 = $stmt2->fetch(PDO::FETCH_ASSOC);

            return ($resultado1['total'] > 0) || ($resultado2['total'] > 0);
        } catch (PDOException $e) {
            error_log("Error en mdlEstaEnUso: " . $e->getMessage());
            return false;
        }
    }

    // Obtener productos activos
    static public function mdlObtenerProductosActivos()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM producto 
                WHERE estado = 'activo'
                ORDER BY nombre
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerProductosActivos: " . $e->getMessage());
            return array();
        }
    }

    // Obtener productos con stock bajo
    static public function mdlObtenerProductosStockBajo()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM producto 
                WHERE stock <= stock_minimo 
                AND estado = 'activo'
                ORDER BY stock ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerProductosStockBajo: " . $e->getMessage());
            return array();
        }
    }

    // Buscar productos por término
    static public function mdlBuscarProductos($termino)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM producto 
                WHERE nombre LIKE :termino
                   OR codigo LIKE :termino
                   OR descripcion LIKE :termino
                   OR categoria LIKE :termino
                   OR marca LIKE :termino
                ORDER BY nombre
            ");
            $termino = "%" . $termino . "%";
            $stmt->bindParam(":termino", $termino, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlBuscarProductos: " . $e->getMessage());
            return array();
        }
    }

    // Obtener productos por categoría
    static public function mdlObtenerProductosPorCategoria($categoria)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM producto 
                WHERE categoria = :categoria AND estado = 'activo'
                ORDER BY nombre
            ");
            $stmt->bindParam(":categoria", $categoria, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerProductosPorCategoria: " . $e->getMessage());
            return array();
        }
    }

    // Obtener categorías disponibles
    static public function mdlObtenerCategorias()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT DISTINCT categoria 
                FROM producto 
                WHERE categoria IS NOT NULL AND categoria != ''
                ORDER BY categoria
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerCategorias: " . $e->getMessage());
            return array();
        }
    }

    // Actualizar stock
    static public function mdlActualizarStock($id_producto, $cantidad, $tipo = "salida")
    {
        try {
            if ($tipo == "entrada") {
                $sql = "UPDATE producto SET stock = stock + :cantidad WHERE id_producto = :id_producto";
            } else {
                $sql = "UPDATE producto SET stock = stock - :cantidad WHERE id_producto = :id_producto";
            }

            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->bindParam(":id_producto", $id_producto, PDO::PARAM_INT);
            $stmt->bindParam(":cantidad", $cantidad, PDO::PARAM_INT);

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlActualizarStock: " . $e->getMessage());
            return "error";
        }
    }

    // Obtener estadísticas de productos
    static public function mdlObtenerEstadisticas()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    COUNT(*) as total_productos,
                    COUNT(CASE WHEN estado = 'activo' THEN 1 END) as productos_activos,
                    COUNT(CASE WHEN estado = 'inactivo' THEN 1 END) as productos_inactivos,
                    COUNT(CASE WHEN stock <= stock_minimo THEN 1 END) as productos_stock_bajo,
                    AVG(precio) as precio_promedio,
                    SUM(stock * precio) as valor_inventario,
                    COUNT(DISTINCT categoria) as total_categorias,
                    COUNT(DISTINCT marca) as total_marcas
                FROM producto
            ");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerEstadisticas: " . $e->getMessage());
            return array();
        }
    }

    // Contar total de productos
    static public function mdlContarProductos()
    {
        try {
            $stmt = Conexion::conectar()->prepare("SELECT COUNT(*) as total FROM producto");
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'];
        } catch (PDOException $e) {
            error_log("Error en mdlContarProductos: " . $e->getMessage());
            return 0;
        }
    }

    // Generar reporte de inventario
    static public function mdlGenerarReporteInventario()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    codigo,
                    nombre,
                    categoria,
                    marca,
                    stock,
                    stock_minimo,
                    precio,
                    (stock * precio) as valor_stock,
                    unidad_medida,
                    ubicacion,
                    estado,
                    CASE 
                        WHEN stock <= 0 THEN 'Sin Stock'
                        WHEN stock <= stock_minimo THEN 'Stock Bajo'
                        ELSE 'Stock Normal'
                    END as estado_stock
                FROM producto 
                ORDER BY categoria, nombre
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlGenerarReporteInventario: " . $e->getMessage());
            return array();
        }
    }

    // Obtener productos más vendidos
    static public function mdlObtenerProductosMasVendidos($limite = 10)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT p.*, 
                       COALESCE(SUM(df.cantidad), 0) as total_vendido
                FROM producto p
                LEFT JOIN detalle_factura df ON p.id_producto = df.id_producto
                LEFT JOIN factura f ON df.id_factura = f.id_factura AND f.estado = 'pagada'
                WHERE p.estado = 'activo'
                GROUP BY p.id_producto
                ORDER BY total_vendido DESC
                LIMIT :limite
            ");
            $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerProductosMasVendidos: " . $e->getMessage());
            return array();
        }
    }

    // Obtener marcas disponibles
    static public function mdlObtenerMarcas()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT DISTINCT marca 
                FROM producto 
                WHERE marca IS NOT NULL AND marca != ''
                ORDER BY marca
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerMarcas: " . $e->getMessage());
            return array();
        }
    }

    // Obtener productos por rango de precio
    static public function mdlObtenerProductosPorRangoPrecio($precio_min, $precio_max)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM producto 
                WHERE precio BETWEEN :precio_min AND :precio_max
                AND estado = 'activo'
                ORDER BY precio ASC
            ");
            $stmt->bindParam(":precio_min", $precio_min, PDO::PARAM_STR);
            $stmt->bindParam(":precio_max", $precio_max, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerProductosPorRangoPrecio: " . $e->getMessage());
            return array();
        }
    }

    // Actualizar precios masivamente por categoría
    static public function mdlActualizarPreciosMasivos($categoria, $porcentaje, $tipo = "aumento")
    {
        try {
            if ($tipo == "aumento") {
                $sql = "UPDATE producto SET precio = precio * (1 + :porcentaje/100) WHERE categoria = :categoria";
            } else {
                $sql = "UPDATE producto SET precio = precio * (1 - :porcentaje/100) WHERE categoria = :categoria";
            }

            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->bindParam(":categoria", $categoria, PDO::PARAM_STR);
            $stmt->bindParam(":porcentaje", $porcentaje, PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlActualizarPreciosMasivos: " . $e->getMessage());
            return "error";
        }
    }
}
?>