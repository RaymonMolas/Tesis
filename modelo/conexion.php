<?php 

class Conexion{

	static public function conectar(){

		try {
			// Configuración de la base de datos
			$host = "localhost";
			$dbname = "backup_taller";
			$username = "root";
			$password = "";
			
			// Opciones de PDO para mejorar la seguridad y funcionamiento
			$options = array(
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Activar excepciones
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Fetch por defecto asociativo
				PDO::ATTR_EMULATE_PREPARES => false, // Usar prepared statements reales
				PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci" // Charset completo UTF-8
			);

			// Crear conexión PDO
			$link = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, $options);

			return $link;

		} catch (PDOException $e) {
			// Log del error
			error_log("Error de conexión a la base de datos: " . $e->getMessage());
			
			// En desarrollo, mostrar error. En producción, mensaje genérico
			if (defined('DEBUG') && DEBUG === true) {
				die("Error de conexión: " . $e->getMessage());
			} else {
				die("Error de conexión a la base de datos. Contacte al administrador.");
			}
		}
	}

	// Método para cerrar conexión (opcional)
	static public function desconectar($conexion) {
		$conexion = null;
	}

	// Método para obtener información de la conexión
	static public function getInfo() {
		try {
			$link = self::conectar();
			$version = $link->query('SELECT VERSION()')->fetchColumn();
			return array(
				'servidor' => 'MySQL',
				'version' => $version,
				'charset' => 'utf8mb4',
				'base_datos' => 'backup_taller'
			);
		} catch (Exception $e) {
			return array('error' => $e->getMessage());
		}
	}

	// Método para verificar la conexión
	static public function verificarConexion() {
		try {
			$link = self::conectar();
			$link->query('SELECT 1');
			return true;
		} catch (Exception $e) {
			error_log("Error verificando conexión: " . $e->getMessage());
			return false;
		}
	}
}

?>