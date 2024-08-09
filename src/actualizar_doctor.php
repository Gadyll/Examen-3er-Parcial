"aqui sirve para cuando edites el perfil de doctor actualiza en la bd ya sirve pero siquieres checalo"

<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['correo'])) {
    header("Location: login.html");
    exit();
}

$correo_actual = $_SESSION['correo'];

// Conexión a la base de datos

include 'bd.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $nuevoCorreo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $estado = $_POST['estado'];
    $ciudad = $_POST['ciudad'];
    $direccion = $_POST['direccion'];
    $especialidad = $_POST['especialidad'];
    $avatar_path = null;

    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $avatar = $_FILES['avatar']['name'];
        $target = "imagenes/" . basename($avatar);

        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target)) {
            $avatar_path = $target;
        } else {
            echo "Error al subir el archivo.";
            exit();
        }
    }

    // Usar una transacción para asegurarnos de que ambas tablas se actualizan correctamente
    $conn->begin_transaction();

    try {
        if ($avatar_path) {
            $sql = "UPDATE usuarios SET nombre = ?, apellido = ?, correo = ?, telefono = ?, fecha_nacimiento = ?, id_estado = ?, id_ciudad = ?, direccion = ?, avatar = ? WHERE correo = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssiisss", $nombre, $apellido, $nuevoCorreo, $telefono, $fecha_nacimiento, $estado, $ciudad, $direccion, $avatar_path, $correo_actual);
        } else {
            $sql = "UPDATE usuarios SET nombre = ?, apellido = ?, correo = ?, telefono = ?, fecha_nacimiento = ?, id_estado = ?, id_ciudad = ?, direccion = ? WHERE correo = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssiiss", $nombre, $apellido, $nuevoCorreo, $telefono, $fecha_nacimiento, $estado, $ciudad, $direccion, $correo_actual);
        }

        if (!$stmt->execute()) {
            throw new Exception("Error al actualizar el perfil de usuario: " . $stmt->error);
        }

        // Actualizar la tabla medico si es necesario
        $sql_medico = "UPDATE medico SET id_especialidad = ? WHERE id_usuario = (SELECT id_usuario FROM usuarios WHERE correo = ?)";
        $stmt_medico = $conn->prepare($sql_medico);
        $stmt_medico->bind_param("is", $especialidad, $nuevoCorreo);

        if (!$stmt_medico->execute()) {
            throw new Exception("Error al actualizar la especialidad del médico: " . $stmt_medico->error);
        }

        $conn->commit();
        
        // Actualización exitosa
        $_SESSION['correo'] = $nuevoCorreo; // Actualizar correo en la sesión si fue cambiado
        echo "<script>
            alert('Perfil actualizado exitosamente');
            window.location.href = 'doctor.php'; // Redirigir al panel del doctor
        </script>";
    } catch (Exception $e) {
        $conn->rollback();
        // Error en la actualización
        echo "<script>
            alert('Error al actualizar el perfil: " . $e->getMessage() . "');
            window.location.href = 'editar_doctor.php'; // Redirigir a la página de edición de perfil
        </script>";
    }

    $stmt->close();
    $stmt_medico->close();
}

$conn->close();
?>
