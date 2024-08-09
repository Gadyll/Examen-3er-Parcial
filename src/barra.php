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

// Función para calcular la edad
function calcularEdad($fechaNacimiento) {
    $fechaNacimiento = new DateTime($fechaNacimiento);
    $hoy = new DateTime();
    $edad = $hoy->diff($fechaNacimiento);
    return $edad->y;
}

// Actualizar la fecha y hora de último ingreso
$sql = "UPDATE usuarios SET ultimo_ingreso = NOW() WHERE correo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $correo);
$stmt->execute();

// Obtener los datos del usuario
$sql = "SELECT u.id_usuario, u.id_tipo_usuario, u.nombre, u.apellido, u.telefono, u.correo, u.avatar, u.fecha_nacimiento, 
               DATE_FORMAT(u.ultimo_ingreso, '%d/%b/%Y %r') AS ultimo_ingreso, 
               e.nombre AS estado, c.nombre AS ciudad, g.genero AS genero 
        FROM usuarios u 
        LEFT JOIN estado e ON u.id_estado = e.id_estado 
        LEFT JOIN ciudad c ON u.id_ciudad = c.id_ciudad 
        LEFT JOIN generos g ON u.id_genero = g.id_genero 
        WHERE u.correo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $usuario = $result->fetch_assoc();
    $id_usuario = $usuario['id_usuario'];
    $id_tipo_usuario = $usuario['id_tipo_usuario'];
    $nombreCompleto = $usuario['nombre'] . ' ' . $usuario['apellido'];
    $telefono = $usuario['telefono'];
    $correo = $usuario['correo'];
    $avatar = $usuario['avatar'] ? $usuario['avatar'] : 'imagenes/avatar.png';
    $ultimoIngreso = $usuario['ultimo_ingreso'] ? $usuario['ultimo_ingreso'] : 'N/A';
    $fechaNacimiento = $usuario['fecha_nacimiento'];

    // Verificar que el usuario es un paciente
    if ($id_tipo_usuario == 1) { // 1 es el tipo de usuario para pacientes
        // Obtener la lista de médicos
        $sql_medicos = "SELECT m.id_medico, u.nombre, u.apellido 
                        FROM medico m
                        JOIN usuarios u ON m.id_usuario = u.id_usuario";
        $result_medicos = $conn->query($sql_medicos);

        // Obtener la lista de notificaciones del paciente
        $sql_notificaciones = "SELECT n.titulo, n.mensaje, n.fecha, n.hora, n.timestamp, 
                                      m.nombre AS medico_nombre, m.apellido AS medico_apellido, m.avatar AS medico_avatar 
                              FROM notificaciones n 
                              LEFT JOIN medico med ON n.id_medico = med.id_medico 
                              LEFT JOIN usuarios m ON med.id_usuario = m.id_usuario 
                              WHERE n.id_paciente = ? AND (n.estado = 'pendiente' OR n.estado = 'aceptada' OR n.estado = 'rechazada')";
        $stmt = $conn->prepare($sql_notificaciones);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result_notificaciones = $stmt->get_result();

        // Obtener el número de notificaciones pendientes
        $sql_count_notificaciones = "SELECT COUNT(*) AS count FROM notificaciones WHERE id_paciente = ? AND (estado = 'pendiente' OR estado = 'aceptada' OR estado = 'rechazada')";
        $stmt_count = $conn->prepare($sql_count_notificaciones);
        $stmt_count->bind_param("i", $id_usuario);
        $stmt_count->execute();
        $result_count = $stmt_count->get_result();
        $row_count = $result_count->fetch_assoc();
        $count_notificaciones = $row_count['count'];

        // Obtener el expediente más reciente del usuario
        $sql_expediente = "SELECT * FROM documentos_adjuntos WHERE id_usuario = ? ORDER BY fecha_subida DESC LIMIT 1";
        $stmt_expediente = $conn->prepare($sql_expediente);
        $stmt_expediente->bind_param("i", $id_usuario);
        $stmt_expediente->execute();
        $result_expediente = $stmt_expediente->get_result();
        $expediente = $result_expediente->fetch_assoc();
    } else {
        echo "El usuario no es un paciente.";
        exit();
    }
} else {
    echo "No se encontró el usuario.";
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/Nosotros_paciente.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="CSS/paciente.css?v=1.0">
    <link rel="stylesheet" href="CSS/editar_perfil.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <title>Paciente</title>
</head>
<body>

    <header>
        <div class="container">
            <div class="logo">
                <img src="imagenes/logo.jpg" alt="Logo" />
            </div>
            <nav>
                <ul>
                    <li><a href="inicio_paciente.php">Inicio</a></li>
                    <li><a href="paciente.php">Perfil</a></li>
                    <li><a href="Nosotros_paciente.php">Nosotros</a></li>
                    <li><a href="#">Ubica tu médico</a></li>
                    <li class="nav-menu-item dropdown">
                        <a href="#" class="nav-menu-link nav-link">
                            <img id="avatar" src="<?php echo $avatar; ?>" alt="Avatar" class="avatar">
                        </a>
                        <div class="dropdown-content">
                            <a href="editar_perfil.php">Editar perfil</a>
                            <a href="logout.php">Cerrar sesión</a>
                        </div>
                    </li>
                    <li class="nav-menu-item">
                        <a href="#" class="nav-menu-link nav-link" id="notificationIcon">
                            <i class="fas fa-bell"></i>
                            <?php if ($count_notificaciones > 0): ?>
                                <span id="notificationCount" class="badge"><?php echo $count_notificaciones; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

</body>
</html>