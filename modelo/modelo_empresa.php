<?php
require_once "conexion.php";

class ModeloEmpresa {

    // Obtener información estática de la empresa (RUC, dirección, timbrado)
    static public function mdlObtenerInfoEmpresa() {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT ruc_empresa, direccion_empresa, timbrado_numero, timbrado_vencimiento
                FROM empresa_info
                ORDER BY id DESC
                LIMIT 1
            ");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlObtenerInfoEmpresa: " . $e->getMessage());
            return false;
        }
    }
}
?>
