"aqui gestiona las citas cuando el medico acepta o rechaza la cita del paciente lo reenvia al paciente cambiando
su estado de la cita."

<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['correo'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado.']);
    exit();
}

// Conexión a la base de datos

include 'bd.php';


$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Verificar la solicitud
$data = json_decode(file_get_contents('php://input'), true);
$id_notificacion = $data['id_notificacion'];
$accion = $data['accion'];

if (!$id_notificacion || !$accion) {
    echo json_encode(['success' => false, 'message' => 'Solicitud inválida.']);
    exit();
}

// Obtener los datos de la notificación
$sql_notificacion = "SELECT * FROM notificaciones WHERE id_notificacion = ?";
$stmt = $conn->prepare($sql_notificacion);
$stmt->bind_param("i", $id_notificacion);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $notificacion = $result->fetch_assoc();
    $id_paciente = $notificacion['id_paciente'];
    $fecha = $notificacion['fecha'];
    $hora = $notificacion['hora'];

    if ($accion == 'aceptar') {
        // Actualizar el estado de la notificación a 'aceptada'
        $sql_update = "UPDATE notificaciones SET estado = 'aceptada' WHERE id_notificacion = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("i", $id_notificacion);
        $stmt_update->execute();

        // Insertar la cita en la tabla de citas
        $sql_cita = "INSERT INTO cita (fecha, hora, id_paciente, id_medico) VALUES (?, ?, ?, ?)";
        $stmt_cita = $conn->prepare($sql_cita);
        $stmt_cita->bind_param("ssii", $fecha, $hora, $id_paciente, $notificacion['id_medico']);
        $stmt_cita->execute();

        // Notificar al paciente que la cita fue aceptada
        $titulo = "Cita aceptada";
        $mensaje = "Tu cita para la fecha $fecha a las $hora ha sido aceptada.";
        $estado = "aceptada";

        $sql_notificar_paciente = "INSERT INTO notificaciones (titulo, mensaje, fecha, hora, estado, id_medico, id_paciente) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_notificar_paciente = $conn->prepare($sql_notificar_paciente);
        $stmt_notificar_paciente->bind_param("sssssii", $titulo, $mensaje, $fecha, $hora, $estado, $notificacion['id_medico'], $id_paciente);
        $stmt_notificar_paciente->execute();

        echo json_encode(['success' => true, 'message' => 'Cita aceptada y notificación enviada al paciente.']);
    } else if ($accion == 'rechazar') {
        // Actualizar el estado de la notificación a 'rechazada'
        $sql_update = "UPDATE notificaciones SET estado = 'rechazada' WHERE id_notificacion = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("i", $id_notificacion);
        $stmt_update->execute();

        // Notificar al paciente que la cita fue rechazada
        $titulo = "Cita rechazada";
        $mensaje = "Tu cita para la fecha $fecha a las $hora ha sido rechazada.";
        $estado = "rechazada";

        $sql_notificar_paciente = "INSERT INTO notificaciones (titulo, mensaje, fecha, hora, estado, id_medico, id_paciente) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_notificar_paciente = $conn->prepare($sql_notificar_paciente);
        $stmt_notificar_paciente->bind_param("sssssii", $titulo, $mensaje, $fecha, $hora, $estado, $notificacion['id_medico'], $id_paciente);
        $stmt_notificar_paciente->execute();

        echo json_encode(['success' => true, 'message' => 'Cita rechazada y notificación enviada al paciente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Acción inválida.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Notificación no encontrada.']);
}

$conn->close();
?>
