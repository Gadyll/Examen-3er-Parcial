<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['correo'])) {
    header("Location: login.html");
    exit();
}

include 'bd.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener el id_usuario y avatar a partir del correo
$correo = $_SESSION['correo'];
$stmt = $conn->prepare("SELECT id_usuario, avatar FROM usuarios WHERE correo = ?");
$stmt->bind_param("s", $correo);
$stmt->execute();
$stmt->bind_result($id_usuario, $avatar);
$stmt->fetch();
$stmt->close();

if (!$id_usuario) {
    die("Error al obtener el ID del usuario.");
}

// Obtener el conteo de citas pendientes
$stmt_citas_count = $conn->prepare("SELECT COUNT(*) FROM cita WHERE id_medico = ? AND estado = 'pendiente'");
$stmt_citas_count->bind_param("i", $id_usuario);
$stmt_citas_count->execute();
$stmt_citas_count->bind_result($count_citas);
$stmt_citas_count->fetch();
$stmt_citas_count->close();

// Obtener las citas pendientes para mostrar en la barra de notificaciones
$sql_citas = "SELECT c.id_cita, c.fecha, c.hora, c.estado, 
                      p.nombre AS paciente_nombre, p.apellido AS paciente_apellido, p.id_usuario AS paciente_id, p.avatar AS paciente_avatar 
              FROM cita c 
              LEFT JOIN usuarios p ON c.id_paciente = p.id_usuario 
              WHERE c.id_medico = ? AND c.estado = 'pendiente'";
$stmt_citas = $conn->prepare($sql_citas);
$stmt_citas->bind_param("i", $id_usuario);
$stmt_citas->execute();
$result_citas = $stmt_citas->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/Nosotros_medico.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="CSS/doctor.css?v=1.0">
    <link rel="stylesheet" href="CSS/editar_doctor.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <title>Doctor</title>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <img src="imagenes/logo.jpg" alt="Logo" />
            </div>
            <nav>
                <ul>
                    <li><a href="inicio_medico.php">Inicio</a></li>
                    <li><a href="doctor.php">Perfil</a></li>
                    <li><a href="Nosotros_medico.php">Nosotros</a></li>
                    <li><a href="#">Ubica tu médico</a></li>
                    <li class="nav-menu-item dropdown">
                        <a href="#" class="nav-menu-link nav-link">
                            <img id="avatar" src="<?php echo $avatar; ?>" alt="Avatar" class="avatar">
                        </a>
                        <div class="dropdown-content">
                            <a href="editar_doctor.php">Editar perfil</a>
                            <a href="logout.php">Cerrar sesión</a>
                        </div>
                    </li>
                    <li class="nav-menu-item">
                        <a href="#" class="nav-menu-link nav-link" id="notificationIcon">
                            <i class="fas fa-bell"></i>
                            <?php if ($count_citas > 0): ?>
                                <span id="notificationCount" class="badge"><?php echo $count_citas; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <div id="notificationDropdown" class="notification-dropdown">
        <div class="notification-dropdown-header">
            <h4>Citas</h4>
            <span class="close">&times;</span>
        </div>
        <?php
        if ($result_citas->num_rows > 0) {
            while($row = $result_citas->fetch_assoc()) {
                $paciente_avatar = $row["paciente_avatar"] ? $row["paciente_avatar"] : 'imagenes/avatar.png';
                echo '<div class="notification-item">';
                echo '<img src="' . $paciente_avatar . '" alt="Avatar">';
                echo '<div class="notification-text">';
                echo '<p><strong>Cita con ' . $row["paciente_nombre"] . ' ' . $row["paciente_apellido"] . '</strong></p>';
                echo '<p>Fecha: ' . $row["fecha"] . ' Hora: ' . $row["hora"] . '</p>';
                echo '<p>Estado: ' . $row["estado"] . '</p>';
                echo '<div class="notification-actions">';
                echo '<form method="post" action="responder_cita.php">';
                echo '<input type="hidden" name="id_cita" value="' . $row["id_cita"] . '">';
                echo '<button type="submit" name="accion" value="aceptar" class="btn-aceptar">Aceptar</button>';
                echo '<button type="submit" name="accion" value="rechazar" class="btn-rechazar">Rechazar</button>';
                echo '</form>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<p>No tienes citas.</p>';
        }
        $stmt_citas->close();
        $conn->close();
        ?>
    </div>
    <script>
        document.getElementById('notificationIcon').onclick = function() {
            var dropdown = document.getElementById('notificationDropdown');
            dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
        }

        document.querySelector('.close').onclick = function() {
            document.getElementById('notificationDropdown').style.display = "none";
        }

        window.onclick = function(event) {
            if (!event.target.matches('#notificationIcon') && !event.target.matches('.notification-dropdown') && !event.target.matches('.notification-dropdown *')) {
                var dropdowns = document.getElementsByClassName("notification-dropdown");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.style.display === "block") {
                        openDropdown.style.display = "none";
                    }
                }
            }
        }

        function mostrarAgenda() {
            document.getElementById('right-panel').innerHTML = '<h2>Agenda</h2>';
            fetch('obtener_citas_aceptadas.php')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('right-panel').innerHTML = data;
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ocurrió un error al obtener la agenda.');
                });
        }
    </script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/locale/es.js'></script>
</body>
</html>
