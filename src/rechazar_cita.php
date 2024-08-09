este es el boton para rechazar al paciente en doctor en las notificaciones 

<?php
session_start();


include 'bd.php';


$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id_notificacion = $data['id_notificacion'];

    // Obtener detalles de la notificación
    $sql_notificacion = "SELECT fecha, hora, id_paciente, id_medico FROM notificaciones WHERE id_notificacion = ?";
    $stmt = $conn->prepare($sql_notificacion);
    $stmt->bind_param("i", $id_notificacion);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $notificacion = $result->fetch_assoc();
        $fecha = $notificacion['fecha'];
        $hora = $notificacion['hora'];
        $id_paciente = $notificacion['id_paciente'];
        $id_medico = $notificacion['id_medico'];

        // Actualizar el estado de la notificación
        $sql_update = "UPDATE notificaciones SET estado = 'rechazada' WHERE id_notificacion = ?";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param("i", $id_notificacion);
        if ($stmt->execute()) {
            // Enviar notificación al paciente
            $titulo = "Cita rechazada";
            $mensaje = "Tu cita ha sido rechazada.";
            $estado = "pendiente";
            $timestamp = date('Y-m-d H:i:s');
            $sql_notificacion_paciente = "INSERT INTO notificaciones (titulo, mensaje, fecha, hora, estado, id_medico, id_paciente, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql_notificacion_paciente);
            $stmt->bind_param("ssssiiis", $titulo, $mensaje, $fecha, $hora, $estado, $id_medico, $id_paciente, $timestamp);
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Cita rechazada exitosamente.';
            } else {
                $response['message'] = 'Error al insertar la notificación para el paciente.';
            }
        } else {
            $response['message'] = 'Error al actualizar la notificación.';
        }
    } else {
        $response['message'] = 'No se encontró la notificación.';
    }
} else {
    $response['message'] = 'Método de solicitud no permitido.';
}

$conn->close();
echo json_encode($response);
?>
