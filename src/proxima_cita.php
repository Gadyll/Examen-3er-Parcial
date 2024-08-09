<?php
session_start();

if (!isset($_SESSION['correo'])) {
    header("Location: login.html");
    exit();
}

$correo = $_SESSION['correo'];

include 'bd.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$id_usuario = $_POST['id_usuario'];

$sql_cita = "SELECT c.fecha, c.hora, CONCAT(u.nombre, ' ', u.apellido) AS medico 
             FROM cita c
             JOIN medico m ON c.id_medico = m.id_medico
             JOIN usuarios u ON m.id_usuario = u.id_usuario
             WHERE c.id_paciente = ? AND c.fecha >= CURDATE()
             ORDER BY c.fecha ASC, c.hora ASC LIMIT 1";
$stmt_cita = $conn->prepare($sql_cita);
$stmt_cita->bind_param("i", $id_usuario);
$stmt_cita->execute();
$result_cita = $stmt_cita->get_result();

$cita = $result_cita->fetch_assoc();

$stmt_cita->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Próxima Cita</title>
    <link rel="stylesheet" href="CSS/paciente.css">
</head>
<body>
    <div class="cita-container">
        <h3>Próxima Cita</h3>
        <?php if ($cita): ?>
            <p><strong>Fecha:</strong> <?php echo $cita['fecha']; ?></p>
            <p><strong>Hora:</strong> <?php echo $cita['hora']; ?></p>
            <p><strong>Médico:</strong> <?php echo $cita['medico']; ?></p>
        <?php else: ?>
            <p>No tienes próximas citas.</p>
        <?php endif; ?>
    </div>
</body>
</html>
