<?php
session_start();

if (!isset($_SESSION['correo']) || !isset($_POST['id_cita']) || !isset($_POST['accion'])) {
    header("Location: login.html");
    exit();
}

include 'bd.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$id_cita = $_POST['id_cita'];
$accion = $_POST['accion'];
$estado = ($accion == 'aceptar') ? 'aceptada' : 'rechazada';

$sql = "UPDATE cita SET estado = ? WHERE id_cita = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $estado, $id_cita);

if ($stmt->execute()) {
    if ($accion == 'rechazar') {
        // Eliminar la cita si se rechaza
        $sql_delete = "DELETE FROM cita WHERE id_cita = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $id_cita);
        $stmt_delete->execute();
        $stmt_delete->close();
    }
    header("Location: doctor.php");
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
