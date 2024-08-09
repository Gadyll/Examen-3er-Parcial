<?php
session_start();

if (!isset($_SESSION['correo']) || !isset($_SESSION['id_usuario'])) {
    header("Location: login.html");
    exit();
}

include 'bd.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$id_usuario = $_SESSION['id_usuario']; // Usar id_usuario almacenado en la sesión

// Obtener la ID del médico basado en el id_usuario
$sql_medico = "SELECT id_medico FROM medico WHERE id_usuario = ?";
$stmt_medico = $conn->prepare($sql_medico);
$stmt_medico->bind_param("i", $id_usuario);
$stmt_medico->execute();
$result_medico = $stmt_medico->get_result();

if ($result_medico->num_rows > 0) {
    $medico_data = $result_medico->fetch_assoc();
    $id_medico = $medico_data['id_medico'];
} else {
    echo '<p>No se encontró el médico.</p>';
    exit();
}

$stmt_medico->close();

// Obtener las citas aceptadas para el médico
$sql = "SELECT c.fecha, c.hora, u.nombre AS paciente_nombre, u.apellido AS paciente_apellido 
        FROM cita c 
        INNER JOIN usuarios u ON c.id_paciente = u.id_usuario 
        WHERE c.id_medico = ? AND c.estado = 'aceptada' AND c.fecha >= CURDATE() 
        ORDER BY c.fecha, c.hora";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_medico);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<div class="cita-item">';
        echo '<p><strong>Fecha:</strong> ' . $row['fecha'] . '</p>';
        echo '<p><strong>Hora:</strong> ' . $row['hora'] . '</p>';
        echo '<p><strong>Paciente:</strong> ' . $row['paciente_nombre'] . ' ' . $row['paciente_apellido'] . '</p>';
        echo '</div>';
    }
} else {
    echo '<p>No hay citas aceptadas próximas.</p>';
}

$stmt->close();
$conn->close();
?>
