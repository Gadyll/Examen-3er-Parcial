
"esto es la funcion de elimar expediente en la parte de paciente.php interfaz perfil y esta conectada con 
paciente.php ya funciona pero pruebalo"

<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar si el usuario ha iniciado sesión
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

// Verificar si se recibió el ID del documento
if (isset($_POST['id_documento'])) {
    $id_documento = $_POST['id_documento'];

    // Obtener la información del documento
    $sql = "SELECT url_documento FROM documentos_adjuntos WHERE id_documento = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_documento);
    $stmt->execute();
    $result = $stmt->get_result();
    $documento = $result->fetch_assoc();

    if ($documento) {
        // Eliminar el archivo del servidor
        $ruta_documento = __DIR__ . '/' . $documento['url_documento'];
        if (file_exists($ruta_documento)) {
            unlink($ruta_documento);
        }

        // Eliminar el registro de la base de datos
        $sql = "DELETE FROM documentos_adjuntos WHERE id_documento = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_documento);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Expediente eliminado exitosamente.";
        } else {
            $_SESSION['error_message'] = "Error al eliminar el expediente de la base de datos: " . $stmt->error;
        }
    } else {
        $_SESSION['error_message'] = "No se encontró el expediente.";
    }
} else {
    $_SESSION['error_message'] = "No se recibió el ID del expediente.";
}

$conn->close();

// Redirigir de nuevo a la página del paciente
header("Location: paciente.php");
exit();
?>
