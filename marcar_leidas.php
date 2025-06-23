<?php
session_start();

if (isset($_SESSION["id_cliente"])) {
    ModeloAgendamiento::marcarNotificacionesLeidas($_SESSION["id_cliente"]);
}
?>