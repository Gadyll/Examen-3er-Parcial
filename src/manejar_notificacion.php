"este es para reenviar las notificaciones al paciente"

<?php
session_start();

// Configurar el registro de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log'); // Asegúrate de que esta ruta es escribible

// Conexión a la base de datos

include 'bd.php';


$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$response = array('success' => false, 'message' => '');

$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['id_notificacion']) && isset($data['aceptar'])) {
    $id_notificacion = $data['id_notificacion'];
    $aceptar = $data['aceptar'];

    // Obtener la notificación
    $sql_notificacion = "SELECT * FROM notificaciones WHERE id_notificacion = ?";
    $stmt = $conn->prepare($sql_notificacion);
    if ($stmt === false) {
        error_log("Error preparando la consulta: " . $conn->error);
        $response['message'] = 'Error al preparar la consulta para obtener la notificación.';
        echo json_encode($response);
        exit();
    }

    $stmt->bind_param("i", $id_notificacion);
    if (!$stmt->execute()) {
        error_log("Error ejecutando la consulta: " . $stmt->error);
        $response['message'] = 'Error al ejecutar la consulta para obtener la notificación.';
        echo json_encode($response);
        exit();
    }

    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $notificacion = $result->fetch_assoc();
        $id_paciente = $notificacion['id_paciente'];
        $fecha = $notificacion['fecha'];
        $hora = $notificacion['hora'];
        $id_medico = $notificacion['id_medico'];

        if ($aceptar) {
            // Marcar la notificación como aceptada
            $sql_update = "UPDATE notificaciones SET estado = 'aceptada' WHERE id_notificacion = ?";
            $stmt_update = $conn->prepare($sql_update);
            if ($stmt_update === false) {
                error_log("Error preparando la consulta para actualizar la notificación: " . $conn->error);
                $response['message'] = 'Error al preparar la consulta para actualizar la notificación.';
                echo json_encode($response);
                exit();
            }

            $stmt_update->bind_param("i", $id_notificacion);
            if ($stmt_update->execute()) {
                // Insertar la cita en la tabla citas
                $sql_cita = "INSERT INTO cita (fecha, hora, id_paciente, id_medico) VALUES (?, ?, ?, ?)";
                $stmt_cita = $conn->prepare($sql_cita);
                if ($stmt_cita === false) {
                    error_log("Error preparando la consulta para insertar la cita: " . $conn->error);
                    $response['message'] = 'Error al preparar la consulta para insertar la cita.';
                    echo json_encode($response);
                    exit();
                }

                $stmt_cita->bind_param("ssii", $fecha, $hora, $id_paciente, $id_medico);
                if ($stmt_cita->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Cita aceptada y agendada exitosamente.';

                    // Insertar notificación para el paciente
                    $titulo = "Cita aceptada";
                    $mensaje = "El médico ha aceptado su cita para la fecha $fecha a las $hora.";
                    $estado = "pendiente";

                    $sql_notificacion_paciente = "INSERT INTO notificaciones (titulo, mensaje, fecha, hora, estado, id_medico, id_paciente) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt_notificacion_paciente = $conn->prepare($sql_notificacion_paciente);
                    if ($stmt_notificacion_paciente === false) {
                        error_log("Error preparando la consulta para insertar la notificación para el paciente: " . $conn->error);
                        $response['message'] = 'Error al preparar la consulta para insertar la notificación para el paciente.';
                        echo json_encode($response);
                        exit();
                    }

                    $stmt_notificacion_paciente->bind_param("sssssii", $titulo, $mensaje, $fecha, $hora, $estado, $id_medico, $id_paciente);
                    if (!$stmt_notificacion_paciente->execute()) {
                        error_log("Error al insertar la notificación para el paciente: " . $stmt_notificacion_paciente->error); // Registrar el error
                        $response['message'] = 'Error al insertar la notificación para el paciente.';
                    }
                } else {
                    error_log("Error al insertar la cita: " . $stmt_cita->error); // Registrar el error
                    $response['message'] = 'Error al insertar la cita.';
                }
            } else {
                error_log("Error al actualizar la notificación: " . $stmt_update->error); // Registrar el error
                $response['message'] = 'Error al actualizar la notificación.';
            }
        } else {
            // Marcar la notificación como rechazada
            $sql_update = "UPDATE notificaciones SET estado = 'rechazada' WHERE id_notificacion = ?";
            $stmt_update = $conn->prepare($sql_update);
            if ($stmt_update === false) {
                error_log("Error preparando la consulta para actualizar la notificación: " . $conn->error);
                $response['message'] = 'Error al preparar la consulta para actualizar la notificación.';
                echo json_encode($response);
                exit();
            }

            $stmt_update->bind_param("i", $id_notificacion);
            if ($stmt_update->execute()) {
                // Insertar notificación para el paciente
                $titulo = "Cita rechazada";
                $mensaje = "El médico ha rechazado su cita para la fecha $fecha a las $hora.";
                $estado = "pendiente";

                $sql_notificacion_paciente = "INSERT INTO notificaciones (titulo, mensaje, fecha, hora, estado, id_medico, id_paciente) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt_notificacion_paciente = $conn->prepare($sql_notificacion_paciente);
                if ($stmt_notificacion_paciente === false) {
                    error_log("Error preparando la consulta para insertar la notificación para el paciente: " . $conn->error);
                    $response['message'] = 'Error al preparar la consulta para insertar la notificación para el paciente.';
                    echo json_encode($response);
                    exit();
                }

                $stmt_notificacion_paciente->bind_param("sssssii", $titulo, $mensaje, $fecha, $hora, $estado, $id_medico, $id_paciente);
                if ($stmt_notificacion_paciente->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Cita rechazada y notificación enviada al paciente.';
                } else {
                    error_log("Error al insertar la notificación para el paciente: " . $stmt_notificacion_paciente->error); // Registrar el error
                    $response['message'] = 'Error al insertar la notificación para el paciente.';
                }
            } else {
                error_log("Error al actualizar la notificación: " . $stmt_update->error); // Registrar el error
                $response['message'] = 'Error al actualizar la notificación.';
            }
        }
    } else {
        error_log("No se encontró la notificación con el ID: " . $id_notificacion); // Depuración
        $response['message'] = 'No se encontró la notificación.';
    }
} else {
    $response['message'] = 'Solicitud inválida.';
}

$conn->close();
echo json_encode($response);
?>
