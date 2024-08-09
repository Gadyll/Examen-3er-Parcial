<?php
session_start();

if (!isset($_SESSION['correo'])) {
    header("Location: login.html");
    exit();
}

include 'bd.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fecha_cita = $_POST['fecha_cita'];
    $hora_cita = $_POST['hora_cita'];
    $id_medico = $_POST['id_medico'];
    $id_usuario = $_SESSION['id_usuario']; // Usar el id_usuario de la sesión

    $sql = "INSERT INTO cita (fecha, hora, id_paciente, id_medico, estado) VALUES (?, ?, ?, ?, 'pendiente')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $fecha_cita, $hora_cita, $id_usuario, $id_medico);

    if ($stmt->execute()) {
        echo "<script>alert('Cita agendada exitosamente.');</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Cita</title>
</head>
<body>
    <!-- Aquí puedes redirigir a otra página si lo deseas -->
    <script>
        window.location.href = 'perfil_medico.php?id=<?php echo $id_medico; ?>';
    </script>
</body>
</html>