"Muestra las notificaciones en paciente y medico "

<?php
session_start();

if (!isset($_SESSION['correo'])) {
    header("Location: login.html");
    exit();
}

// Conexión a la base de datos

include 'bd.php';


$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener el id_usuario a partir del correo
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

// Verificar si el usuario es un médico o un paciente
$stmt_medico = $conn->prepare("SELECT id_medico FROM medico WHERE id_usuario = ?");
$stmt_medico->bind_param("i", $id_usuario);
$stmt_medico->execute();
$stmt_medico->bind_result($id_medico);
$stmt_medico->fetch();
$stmt_medico->close();

if ($id_medico) {
    // El usuario es un médico, obtener citas pendientes
    $sql_citas_pendientes = "SELECT c.id_cita, c.fecha, c.hora, c.estado, u.nombre AS paciente_nombre, u.apellido AS paciente_apellido, u.avatar AS paciente_avatar
                             FROM cita c 
                             JOIN usuarios u ON c.id_paciente = u.id_usuario 
                             WHERE c.id_medico = ? AND c.estado IN ('pendiente', 'aceptada', 'rechazada')";
    $stmt_citas_pendientes = $conn->prepare($sql_citas_pendientes);
    $stmt_citas_pendientes->bind_param("i", $id_medico);
} else {
    // El usuario es un paciente, obtener citas pendientes
    $sql_citas_pendientes = "SELECT c.id_cita, c.fecha, c.hora, c.estado, u.nombre AS medico_nombre, u.apellido AS medico_apellido, u.avatar AS medico_avatar
                             FROM cita c 
                             JOIN medico m ON c.id_medico = m.id_medico 
                             JOIN usuarios u ON m.id_usuario = u.id_usuario 
                             WHERE c.id_paciente = ? AND c.estado IN ('pendiente', 'aceptada', 'rechazada')";
    $stmt_citas_pendientes = $conn->prepare($sql_citas_pendientes);
    $stmt_citas_pendientes->bind_param("i", $id_usuario);
}

$stmt_citas_pendientes->execute();
$result_citas_pendientes = $stmt_citas_pendientes->get_result();

$citas = array();
while ($row = $result_citas_pendientes->fetch_assoc()) {
    $citas[] = $row;
}

$conn->close();

echo json_encode(['success' => true, 'citas' => $citas]);
?>
