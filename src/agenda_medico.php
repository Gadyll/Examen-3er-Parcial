"este es para qeu funcione el boton de doctor.php en la interfaz de perfil cuando le de clic en agenda muestre 
en lo blanco la agenda con el paciente que acepte"


<?php
session_start();

// Conexión a la base de datos

include 'bd.php';


$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$response = array('success' => false, 'citas' => array());

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $correo = $_SESSION['correo'];

    // Obtener el id del médico basado en el correo
    $sql_medico = "SELECT m.id_medico 
                   FROM medico m 
                   JOIN usuarios u ON m.id_usuario = u.id_usuario 
                   WHERE u.correo = ?";
    $stmt = $conn->prepare($sql_medico);
    if ($stmt === false) {
        error_log("Error preparando la consulta para obtener el ID del médico: " . $conn->error);
        $response['message'] = 'Error al preparar la consulta para obtener el ID del médico.';
        echo json_encode($response);
        exit();
    }

    $stmt->bind_param("s", $correo);
    if (!$stmt->execute()) {
        error_log("Error ejecutando la consulta para obtener el ID del médico: " . $stmt->error);
        $response['message'] = 'Error al ejecutar la consulta para obtener el ID del médico.';
        echo json_encode($response);
        exit();
    }

    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $id_medico = $row['id_medico'];

        // Obtener las citas del médico
        $sql_citas = "SELECT c.fecha, c.hora, u.nombre AS paciente_nombre, u.apellido AS paciente_apellido, u.telefono AS paciente_telefono, u.correo AS paciente_correo 
                      FROM cita c 
                      JOIN usuarios u ON c.id_paciente = u.id_usuario 
                      WHERE c.id_medico = ?";
        $stmt = $conn->prepare($sql_citas);
        if ($stmt === false) {
            error_log("Error preparando la consulta para obtener las citas: " . $conn->error);
            $response['message'] = 'Error al preparar la consulta para obtener las citas.';
            echo json_encode($response);
            exit();
        }

        $stmt->bind_param("i", $id_medico);
        if (!$stmt->execute()) {
            error_log("Error ejecutando la consulta para obtener las citas: " . $stmt->error);
            $response['message'] = 'Error al ejecutar la consulta para obtener las citas.';
            echo json_encode($response);
            exit();
        }

        $result_citas = $stmt->get_result();
        while ($row_cita = $result_citas->fetch_assoc()) {
            $response['citas'][] = $row_cita;
        }

        $response['success'] = true;
    } else {
        error_log("No se encontró el médico para el correo: " . $correo);
        $response['message'] = 'No se encontró el médico.';
    }
} else {
    $response['message'] = 'Método de solicitud no permitido.';
}

$conn->close();
echo json_encode($response);
?>
