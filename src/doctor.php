<?php
session_start();

if (!isset($_SESSION['correo'])) {
    header("Location: login.html");
    exit();
}

$correo = $_SESSION['correo'];

include 'bd.php'; // Aquí faltaba el punto y coma

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

// Obtener los datos del doctor
$sql = "SELECT u.id_usuario, d.id_medico, u.nombre, u.apellido, u.telefono, u.correo, u.avatar, u.fecha_nacimiento, g.genero,
               DATE_FORMAT(u.ultimo_ingreso, '%d/%b/%Y %r') AS ultimo_ingreso 
        FROM usuarios u 
        JOIN medico d ON u.id_usuario = d.id_usuario 
        LEFT JOIN generos g ON u.id_genero = g.id_genero
        WHERE u.correo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $doctor = $result->fetch_assoc();
    $id_usuario = $doctor['id_usuario'];
    $id_medico = $doctor['id_medico'];
    $nombreCompleto = $doctor['nombre'] . ' ' . $doctor['apellido'];
    $telefono = $doctor['telefono'];
    $correo = $doctor['correo'];
    $avatar = $doctor['avatar'] ? $doctor['avatar'] : 'imagenes/avatar.png';
    $ultimoIngreso = $doctor['ultimo_ingreso'] ? $doctor['ultimo_ingreso'] : 'N/A';
    $fechaNacimiento = $doctor['fecha_nacimiento'];
    $genero = $doctor['genero'];
} else {
    echo "No se encontró el doctor.";
    exit();
}

// Obtener las citas pendientes para el doctor
$sql_citas = "SELECT c.id_cita, c.fecha, c.hora, c.estado, 
                      p.nombre AS paciente_nombre, p.apellido AS paciente_apellido, p.id_usuario AS paciente_id, p.avatar AS paciente_avatar 
              FROM cita c 
              LEFT JOIN usuarios p ON c.id_paciente = p.id_usuario 
              WHERE c.id_medico = ? AND c.estado = 'pendiente'";
$stmt = $conn->prepare($sql_citas);
$stmt->bind_param("i", $id_medico);
$stmt->execute();
$result_citas = $stmt->get_result();

// Obtener el número de citas pendientes
$sql_count_citas = "SELECT COUNT(*) AS count FROM cita WHERE id_medico = ? AND estado = 'pendiente'";
$stmt_count = $conn->prepare($sql_count_citas);
$stmt_count->bind_param("i", $id_medico);
$stmt_count->execute();
$result_count = $stmt_count->get_result();
$row_count = $result_count->fetch_assoc();
$count_citas = $row_count['count'];

$conn->close();
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

    <div class="main-container">
        <div class="container">
            <div class="left-panel">
                <div class="profile-container">
                    <div class="profile-section">
                        <form action="upload.php" method="post" enctype="multipart/form-data">
                            <input type="file" id="fileInput" name="avatar" style="display:none;" onchange="this.form.submit()">
                        </form>
                        <img src="<?php echo $avatar; ?>" alt="Avatar" class="avatar" onclick="document.getElementById('fileInput').click();">
                    </div>
                    <div class="profile-text">
                        <h3><?php echo $nombreCompleto; ?></h3>
                        <p>Último ingreso: <?php echo $ultimoIngreso; ?></p>
                    </div>
                </div>
                <div class="button-section">
                    <button class="btn" onclick="mostrarAgenda()">Agenda</button>
                </div>
                <div class="personal-info">
                    <h4>Datos Personales</h4>
                    <div class="form-section">
                        <i class="fa-solid fa-user"></i>
                        <span>Edad: <?php echo calcularEdad($fechaNacimiento); ?></span>
                    </div>
                    <div class="form-section">
                        <i class="fa-solid fa-calendar"></i>
                        <span>Fecha de nacimiento: <?php echo $fechaNacimiento; ?></span>
                    </div>
                    <div class="form-section">
                        <i class="fa-solid fa-mars"></i>
                        <span>Género: <?php echo $genero; ?></span>
                    </div>
                    <div class="form-section">
                        <i class="fa-solid fa-phone"></i>
                        <span>Número de teléfono: <?php echo $telefono; ?></span>
                    </div>
                    <div class="form-section">
                        <i class="fa-solid fa-envelope"></i>
                        <span>Correo electrónico: <?php echo $correo; ?></span>
                    </div>
                </div>
            </div>
            <div class="right-panel" id="right-panel">
                <!-- Contenido dinámico aquí -->
            </div>
        </div>
    </div>

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
        ?>
    </div>

    <footer>
        <div class="container">
            <div class="footer-block">
                <h4>Conócenos</h4>
                <p><a href="Nosotros.html">Quiénes somos</a></p>
                <p><a href="#">Nuestra Historia</a></p>
                <p><a href="#">Misión y Visión</a></p>
                <p><a href="#">Equipo</a></p>
            </div>
            <div class="footer-block">
                <h4>Servicios</h4>
                <p><a href="#">Consultas Médicas</a></p>
                <p><a href="#">Laboratorio Clínico</a></p>
                <p><a href="#">Especialidades Médicas</a></p>
                <p><a href="#">Atención Domiciliaria</a></p>
            </div>
            <div class="footer-block">
                <h4>Atención</h4>
                <p>Teléfono: +52 123 456 7890</p>
                <p>Email: info@salud-digna.com</p>
                <p>Horario: Lunes a Viernes 9:00 am - 7:00 pm</p>
            </div>
        </div>
        <div class="social-icons">
            <a href="https://www.facebook.com" target="_blank"><i class="fab fa-facebook-f"></i></a>
            <a href="https://www.instagram.com" target="_blank"><i class="fab fa-instagram"></i></a>
            <a href="https://www.twitter.com" target="_blank"><i class="fab fa-twitter"></i></a>
        </div>
        <div class="newsletter">
            <h4>Suscríbete a Nuestro Boletín</h4>
            <form>
                <input type="email" placeholder="Tu correo electrónico" required>
                <button type="submit">Suscribirse</button>
            </form>
        </div>
        <p>&copy; 2024 Salud Digna. Todos los derechos reservados.</p>
    </footer>

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
