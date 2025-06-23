<?php

require_once "conexion.php";

class modelologin {

  static public function buscarusuario($tabla, $item, $valor) {

    $query = "SELECT * FROM $tabla WHERE $item = :$item LIMIT 1";
    $stmt = Conexion::conectar()->prepare($query);
    $stmt->bindParam(":" . $item, $valor, PDO::PARAM_STR);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
  }
}
