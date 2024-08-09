<?php
session_start();

if (!isset($_SESSION['correo'])) {
    echo '<p>No has iniciado sesión.</p>';
    exit();
}

include 'bd.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo '<p>Conexión fallida: ' . $conn->connect_error . '</p>';
    exit();
}

$correo = $_SESSION['correo'];
$stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE correo = ?");
$stmt->bind_param("s", $correo);
$stmt->execute();
$stmt->bind_result($id_usuario);
$stmt->fetch();
$stmt->close();

if (!$id_usuario) {
    echo '<p>Error al obtener el ID del usuario.</p>';
    exit();
}

$sql_citas = "SELECT c.fecha, c.hora, CONCAT(u.nombre, ' ', u.apellido) AS medico 
              FROM cita c
              JOIN medico m ON c.id_medico = m.id_medico
              JOIN usuarios u ON m.id_usuario = u.id_usuario
              WHERE c.id_paciente = ? AND c.estado = 'pendiente'
              ORDER BY c.fecha ASC, c.hora ASC";
$stmt_citas = $conn->prepare($sql_citas);
$stmt_citas->bind_param("i", $id_usuario);
$stmt_citas->execute();
$result_citas = $stmt_citas->get_result();

if ($result_citas->num_rows > 0) {
    while ($row = $result_citas->fetch_assoc()) {
        echo '<div class="cita-item">';
        echo '<p><strong>Fecha:</strong> ' . htmlspecialchars($row['fecha']) . '</p>';
        echo '<p><strong>Hora:</strong> ' . htmlspecialchars($row['hora']) . '</p>';
        echo '<p><strong>Médico:</strong> ' . htmlspecialchars($row['medico']) . '</p>';
        echo '</div>';
    }
} else {
    echo '<p>No tienes citas pendientes.</p>';
}

$stmt_citas->close();
$conn->close();
?>
