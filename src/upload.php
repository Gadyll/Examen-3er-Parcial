"Este es para cambiar la foto "

<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['correo'])) {
    header("Location: login.html");
    exit();
}

$correo = $_SESSION['correo'];

// Conexión a la base de datos

include 'bd.php';


$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if (isset($_FILES['avatar'])) {
    $avatar = $_FILES['avatar']['name'];
    $target = "imagenes/" . basename($avatar);

    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target)) {
        $sql = "UPDATE usuarios SET avatar='$target' WHERE correo='$correo'";
        if ($conn->query($sql) === TRUE) {
            header("Location: paciente.php");
        } else {
            echo "Error al actualizar el avatar: " . $conn->error;
        }
    } else {
        echo "Error al subir el archivo.";
    }
} else {
    echo "No se ha seleccionado ningún archivo.";
}

$conn->close();
?>
