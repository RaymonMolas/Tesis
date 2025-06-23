<?php

class logincontrolador {

  public function ctrIngreso() {

    if (isset($_POST["txtusuario"]) && isset($_POST["tipo_usuario"])) {

      $usuario = $_POST["txtusuario"];
      $clave = $_POST["txtclave"];
      $tipo = $_POST["tipo_usuario"];

      // Definir tabla y campos según el tipo de usuario
      if ($tipo == "cliente") {
        $tabla = "usuariocliente";
        $itemUsuario = "usuario";
        $itemClave = "contrasena";
        $campoId = "id_usuario_cliente";
        $campoFK = "id_cliente"; // Clave foránea para cliente
      } elseif ($tipo == "personal") {
        $tabla = "usuariopersonal";
        $itemUsuario = "usuario";
        $itemClave = "contrasena";
        $campoId = "id_usuario_personal";
        $campoFK = "id_personal"; // Clave foránea para personal
      } else {
        echo '<div class="alert alert-danger">Tipo de usuario no válido.</div>';
        return;
      }

      // Buscar usuario
      $respuesta = modelologin::buscarusuario($tabla, $itemUsuario, $usuario);

      if ($respuesta && $respuesta[$itemUsuario] == $usuario && $respuesta[$itemClave] == $clave) {

        $_SESSION["validarIngreso"] = "ok";
        $_SESSION["usuario"] = $usuario;
        $_SESSION["tipo_usuario"] = $tipo;
        $_SESSION["id_usuario"] = $respuesta[$campoId];

        // Guardar también el ID correspondiente
        if ($tipo === "cliente") {
          $_SESSION["id_cliente"] = $respuesta[$campoFK];
        } elseif ($tipo === "personal") {
          $_SESSION["id_personal"] = $respuesta[$campoFK];
        }

        echo '<script>
          if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
          }
          window.location = "index.php?pagina=inicio";
        </script>';

      } else {
        echo '<script>
          if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
          }
        </script>';
        echo '<div class="alert alert-danger">Error al ingresar, usuario o contraseña incorrectos.</div>';
      }
    }
  }
}
?>