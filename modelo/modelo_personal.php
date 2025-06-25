<?php

require_once "conexion.php";

class ModeloPersonal
{
    // Listar todo el personal
    static public function mdlListarPersonal()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM personal 
                ORDER BY nombre, apellido
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlListarPersonal: " . $e->getMessage());
            return array();
        }
    }

    // Obtener personal específico
    static public function mdlObtenerPersonal($id)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM personal 
                WHERE id_personal = :id
            ");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerPersonal: " . $e->getMessage());
            return false;
        }
    }

    // Registrar nuevo personal
    static public function mdlRegistrarPersonal($datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                INSERT INTO personal (nombre, apellido, cedula, telefono, email, direccion, cargo, fecha_ingreso, salario, estado, fecha_creacion)
                VALUES (:nombre, :apellido, :cedula, :telefono, :email, :direccion, :cargo, :fecha_ingreso, :salario, :estado, NOW())
            ");

            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":apellido", $datos["apellido"], PDO::PARAM_STR);
            $stmt->bindParam(":cedula", $datos["cedula"], PDO::PARAM_STR);
            $stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
            $stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
            $stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);
            $stmt->bindParam(":cargo", $datos["cargo"], PDO::PARAM_STR);
            $stmt->bindParam(":fecha_ingreso", $datos["fecha_ingreso"], PDO::PARAM_STR);
            $stmt->bindParam(":salario", $datos["salario"], PDO::PARAM_STR);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlRegistrarPersonal: " . $e->getMessage());
            return "error";
        }
    }

    // Actualizar personal
    static public function mdlActualizarPersonal($datos)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                UPDATE personal 
                SET nombre = :nombre,
                    apellido = :apellido,
                    cedula = :cedula,
                    telefono = :telefono,
                    email = :email,
                    direccion = :direccion,
                    cargo = :cargo,
                    fecha_ingreso = :fecha_ingreso,
                    salario = :salario,
                    estado = :estado,
                    fecha_actualizacion = NOW()
                WHERE id_personal = :id_personal
            ");

            $stmt->bindParam(":id_personal", $datos["id_personal"], PDO::PARAM_INT);
            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":apellido", $datos["apellido"], PDO::PARAM_STR);
            $stmt->bindParam(":cedula", $datos["cedula"], PDO::PARAM_STR);
            $stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
            $stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
            $stmt->bindParam(":direccion", $datos["direccion"], PDO::PARAM_STR);
            $stmt->bindParam(":cargo", $datos["cargo"], PDO::PARAM_STR);
            $stmt->bindParam(":fecha_ingreso", $datos["fecha_ingreso"], PDO::PARAM_STR);
            $stmt->bindParam(":salario", $datos["salario"], PDO::PARAM_STR);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlActualizarPersonal: " . $e->getMessage());
            return "error";
        }
    }

    // Eliminar personal
    static public function mdlEliminarPersonal($id)
    {
        try {
            $stmt = Conexion::conectar()->prepare("DELETE FROM personal WHERE id_personal = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlEliminarPersonal: " . $e->getMessage());
            return "error";
        }
    }

    // Buscar personal por cédula
    static public function mdlBuscarPorCedula($cedula)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM personal 
                WHERE cedula = :cedula
            ");
            $stmt->bindParam(":cedula", $cedula, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlBuscarPorCedula: " . $e->getMessage());
            return false;
        }
    }

    // Verificar si tiene órdenes asociadas
    static public function mdlTieneOrdenesAsociadas($id_personal)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT COUNT(*) as total 
                FROM ordentrabajo 
                WHERE id_personal = :id_personal
            ");
            $stmt->bindParam(":id_personal", $id_personal, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'] > 0;
        } catch (PDOException $e) {
            error_log("Error en mdlTieneOrdenesAsociadas: " . $e->getMessage());
            return false;
        }
    }

    // Obtener personal activo
    static public function mdlObtenerPersonalActivo()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM personal 
                WHERE estado = 'activo'
                ORDER BY nombre, apellido
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerPersonalActivo: " . $e->getMessage());
            return array();
        }
    }

    // Buscar personal por término
    static public function mdlBuscarPersonal($termino)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM personal 
                WHERE CONCAT(nombre, ' ', apellido) LIKE :termino
                   OR cedula LIKE :termino
                   OR telefono LIKE :termino
                   OR cargo LIKE :termino
                ORDER BY nombre, apellido
            ");
            $termino = "%" . $termino . "%";
            $stmt->bindParam(":termino", $termino, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlBuscarPersonal: " . $e->getMessage());
            return array();
        }
    }

    // Obtener estadísticas del personal
    static public function mdlObtenerEstadisticas()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    COUNT(*) as total_personal,
                    COUNT(CASE WHEN estado = 'activo' THEN 1 END) as personal_activo,
                    COUNT(CASE WHEN estado = 'inactivo' THEN 1 END) as personal_inactivo,
                    AVG(salario) as salario_promedio,
                    COUNT(DISTINCT cargo) as total_cargos,
                    COUNT(CASE WHEN fecha_ingreso >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as nuevos_mes_actual
                FROM personal
            ");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerEstadisticas: " . $e->getMessage());
            return array();
        }
    }

    // Contar total de personal
    static public function mdlContarPersonal()
    {
        try {
            $stmt = Conexion::conectar()->prepare("SELECT COUNT(*) as total FROM personal");
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'];
        } catch (PDOException $e) {
            error_log("Error en mdlContarPersonal: " . $e->getMessage());
            return 0;
        }
    }

    // Obtener personal por cargo
    static public function mdlObtenerPersonalPorCargo($cargo)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM personal 
                WHERE cargo = :cargo AND estado = 'activo'
                ORDER BY nombre, apellido
            ");
            $stmt->bindParam(":cargo", $cargo, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerPersonalPorCargo: " . $e->getMessage());
            return array();
        }
    }

    // Obtener cargos disponibles
    static public function mdlObtenerCargos()
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT DISTINCT cargo 
                FROM personal 
                WHERE cargo IS NOT NULL AND cargo != ''
                ORDER BY cargo
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerCargos: " . $e->getMessage());
            return array();
        }
    }

    // Validar credenciales para login (si el personal puede loguearse directamente)
    static public function mdlValidarCredenciales($cedula, $password)
    {
        try {
            // Este método podría implementarse si el personal tiene contraseñas directas
            // Por ahora, devolvemos false ya que el login se maneja por tabla usuarios
            return false;
        } catch (PDOException $e) {
            error_log("Error en mdlValidarCredenciales: " . $e->getMessage());
            return false;
        }
    }

    // Obtener personal con más órdenes completadas
    static public function mdlObtenerPersonalMasProductivo($limite = 5)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT p.*, 
                       COUNT(o.id_orden) as ordenes_completadas
                FROM personal p
                LEFT JOIN ordentrabajo o ON p.id_personal = o.id_personal AND o.estado = 'completado'
                WHERE p.estado = 'activo'
                GROUP BY p.id_personal
                ORDER BY COUNT(o.id_orden) DESC
                LIMIT :limite
            ");
            $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerPersonalMasProductivo: " . $e->getMessage());
            return array();
        }
    }

    // Obtener personal reciente
    static public function mdlObtenerPersonalReciente($limite = 5)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT * FROM personal 
                ORDER BY fecha_creacion DESC
                LIMIT :limite
            ");
            $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerPersonalReciente: " . $e->getMessage());
            return array();
        }
    }

    // Calcular antigüedad del personal
    static public function mdlObtenerAntiguedad($id_personal)
    {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    DATEDIFF(CURDATE(), fecha_ingreso) as dias_antiguedad,
                    TIMESTAMPDIFF(YEAR, fecha_ingreso, CURDATE()) as años_antiguedad,
                    TIMESTAMPDIFF(MONTH, fecha_ingreso, CURDATE()) as meses_antiguedad
                FROM personal 
                WHERE id_personal = :id_personal
            ");
            $stmt->bindParam(":id_personal", $id_personal, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerAntiguedad: " . $e->getMessage());
            return array();
        }
    }
}
?>