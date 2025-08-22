<?php
// config.php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'prueba2');
define('DB_CHARSET', 'utf8');

// Función para conexión a la base de datos
function conectarDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }
    
    $conn->set_charset(DB_CHARSET);
    return $conn;
}

// Iniciar sesión (si es necesario)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>