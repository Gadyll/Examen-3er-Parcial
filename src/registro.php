"aqui es la de registrar al paciente o medico nuevos."

<?php
// Configurar el registro de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log'); // Asegúrate de que esta ruta es escribible


include 'bd.php';


// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    error_log("Conexión fallida: " . $conn->connect_error);
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
        // El correo ya está registrado, mostrar alerta
        echo "<script>
            alert('El correo electrónico ya está registrado. Por favor, usa otro.');
            window.location.href = 'registro.html'; // Redirigir a la página de registro
        </script>";
    } else {
        // Continuar con el registro
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
        $telefono = $_POST['telefono'];
        $sexo = $_POST['sexo'];
        $fecha_nacimiento = $_POST['fecha_nacimiento'];
        $estado = $_POST['estado'];
        $ciudad = $_POST['ciudad'];
        $tipo_usuario = $_POST['tipo_usuario'];
        $rol = $_POST['rol'];
        $especialidad = isset($_POST['especialidad']) ? $_POST['especialidad'] : null;

        // Iniciar transacción
        $conn->begin_transaction();

        try {
            // Insertar en la tabla usuarios
            $sql = "INSERT INTO usuarios (nombre, apellido, correo, contraseña, telefono, id_genero, fecha_nacimiento, id_estado, id_ciudad, id_tipo_usuario, id_rol) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssisiiii", $nombre, $apellido, $correo, $contrasena, $telefono, $sexo, $fecha_nacimiento, $estado, $ciudad, $tipo_usuario, $rol);
            $stmt->execute();

            // Obtener el ID del usuario recién creado
            $id_usuario = $conn->insert_id;

            // Si es un paciente (asumimos que el tipo de usuario 1 es para pacientes)
            if ($tipo_usuario == 1) {
                $sql = "INSERT INTO pacientes (id_usuario) VALUES (?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id_usuario);
                $stmt->execute();
            }

            // Si es un médico (asumimos que el tipo de usuario 2 es para médicos)
            if ($tipo_usuario == 2) {
                $sql = "INSERT INTO medico (id_usuario, id_especialidad) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $id_usuario, $especialidad);
                $stmt->execute();
            }

            // Confirmar transacción
            $conn->commit();

            // Registro exitoso, mostrar alerta
            echo "<script>
                alert('Registro exitoso');
                window.location.href = 'login.html'; // Redirigir a la página de login
            </script>";
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            $conn->rollback();
            error_log("Error en la transacción: " . $e->getMessage());
            echo "<script>
                alert('Error: " . $e->getMessage() . "');
                window.location.href = 'registro.html'; // Redirigir a la página de registro
            </script>";
        }
    }

    $stmt->close();
} else {
    // Faltan datos del formulario, mostrar alerta
    echo "<script>
        alert('Faltan datos del formulario.');
        window.location.href = 'registro.html'; // Redirigir a la página de registro
    </script>";
}

$conn->close();
?>
