<?php
ini_set('memory_limit', '1G');

$servername = "localhost";
$username = "jha";
$password = "jhadiel";
$dbname = "sonrisas_sleep";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

?>
