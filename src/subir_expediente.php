"Este es para subir el expediente mediante archivos ya sirve pero si le haces cambios lo pruebas."

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

// Verificar si el formulario fue enviado y si hay un archivo
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['expediente'])) {
    $id_usuario = $_POST['id_usuario'];
    $nombre_documento = $_FILES['expediente']['name'];
    $url_documento = __DIR__ . '/uploads/' . basename($nombre_documento);
    $fecha_subida = date('Y-m-d H:i:s');

    // Crear la carpeta uploads si no existe
    if (!file_exists(__DIR__ . '/uploads')) {
        mkdir(__DIR__ . '/uploads', 0777, true);
    }

    // Mover el archivo subido al directorio 'uploads'
    if (move_uploaded_file($_FILES['expediente']['tmp_name'], $url_documento)) {
        // Convertir a ruta relativa para guardar en la base de datos
        $url_documento_db = 'uploads/' . basename($nombre_documento);

        // Insertar información del documento en la base de datos
        $sql = "INSERT INTO documentos_adjuntos (nombre_documento, url_documento, fecha_subida, id_usuario) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $nombre_documento, $url_documento_db, $fecha_subida, $id_usuario);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Expediente subido exitosamente.";
        } else {
            $_SESSION['error_message'] = "Error al guardar la información del expediente en la base de datos: " . $stmt->error;
        }
    } else {
        $_SESSION['error_message'] = "Error al mover el expediente al directorio de uploads.";
    }
} else {
    $_SESSION['error_message'] = "No se recibió ningún archivo.";
}

$conn->close();

// Redirigir de nuevo a la página del paciente
header("Location: paciente.php");
exit();
?>
