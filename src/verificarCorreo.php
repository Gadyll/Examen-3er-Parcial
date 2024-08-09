"Este verifica el correo para cambiarlo en editar perfil para medico y paciente"

<?php

include 'bd.php';


// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if (isset($_POST['correo'])) {
    $correo = $_POST['correo'];

    $sql = "SELECT id_usuario FROM usuarios WHERE correo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(["existe" => true, "message" => "El correo electrónico ya está registrado. Por favor, usa otro."]);
    } else {
        echo json_encode(["existe" => false]);
    }

    $stmt->close();
}

$conn->close();
?>
