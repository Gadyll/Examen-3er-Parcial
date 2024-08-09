<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['correo'])) {
    header("Location: login.html");
    exit();
}

include 'bd.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$correo = $_SESSION['correo'];
$stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE correo = ?");
$stmt->bind_param("s", $correo);
$stmt->execute();
$stmt->bind_result($id_usuario);
$stmt->fetch();
$stmt->close();

if (!$id_usuario) {
    die("Error al obtener el ID del usuario.");
}

$sql_citas = "SELECT c.id_cita, c.fecha, c.hora, 
                     p.nombre AS paciente_nombre, p.apellido AS paciente_apellido, p.telefono AS paciente_telefono, p.correo AS paciente_correo 
              FROM cita c 
              LEFT JOIN usuarios p ON c.id_paciente = p.id_usuario 
              WHERE c.id_medico = ? AND c.estado = 'aceptada' AND c.fecha > CURDATE()";
$stmt_citas = $conn->prepare($sql_citas);
$stmt_citas->bind_param("i", $id_usuario);
$stmt_citas->execute();
$result_citas = $stmt_citas->get_result();

if ($result_citas->num_rows > 0) {
    while($row = $result_citas->fetch_assoc()) {
        echo '<div class="cita-item">';
        echo '<p><strong>Fecha:</strong> ' . htmlspecialchars($row['fecha']) . '</p>';
        echo '<p><strong>Hora:</strong> ' . htmlspecialchars($row['hora']) . '</p>';
        echo '<p><strong>Paciente:</strong> ' . htmlspecialchars($row['paciente_nombre']) . ' ' . htmlspecialchars($row['paciente_apellido']) . '</p>';
        echo '<p><strong>Teléfono:</strong> ' . htmlspecialchars($row['paciente_telefono']) . '</p>';
        echo '<p><strong>Correo:</strong> ' . htmlspecialchars($row['paciente_correo']) . '</p>';
        echo '</div>';
    }
} else {
    echo '<p>No hay citas aceptadas.</p>';
}

$stmt_citas->close();
$conn->close();
?>
