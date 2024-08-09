<?php
session_start(); // Iniciar sesión

include 'bd.php';

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Verificar si los campos existen en el POST
if (isset($_POST['email']) && isset($_POST['password'])) {

    // Recibir datos del formulario
    $correo = $_POST['email'];
    $contrasena = $_POST['password'];

    // Preparar y ejecutar la consulta para verificar el usuario
    $sql = $conn->prepare("SELECT * FROM usuarios WHERE correo = ?");
    $sql->bind_param("s", $correo);
    $sql->execute();
    $result = $sql->get_result();

    if ($result->num_rows > 0) {
        // Obtener los datos del usuario
        $usuario = $result->fetch_assoc();

        // Verificar la contraseña
        if (password_verify($contrasena, $usuario['contraseña'])) {
            // Actualizar el campo ultimo_ingreso
            $sql_update = $conn->prepare("UPDATE usuarios SET ultimo_ingreso = NOW() WHERE correo = ?");
            $sql_update->bind_param("s", $correo);
            $sql_update->execute();

            // Guardar el correo y el id_usuario en la sesión
            $_SESSION['correo'] = $correo;
            $_SESSION['id_usuario'] = $usuario['id_usuario']; // Guardar id_usuario en la sesión

            // Inicio de sesión exitoso, redirigir al usuario según su tipo
            if ($usuario['id_tipo_usuario'] == 1) {
                header("Location: inicio_paciente.php");
            } elseif ($usuario['id_tipo_usuario'] == 2) {
                header("Location: doctor.php");
            }
            exit();
        } else {
            echo '<script>alert("Contraseña incorrecta."); window.location.href="login.html";</script>';
            exit();
        }
    } else {
        echo '<script>alert("Correo Electrónico no registrado."); window.location.href="login.html";</script>';
        exit();
    }
} else {
    echo '<script>alert("Faltan datos al Formulario."); window.location.href="login.html";</script>';
    exit();
}

?>
