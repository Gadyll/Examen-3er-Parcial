"Aqui solo muestra informacion de nosotros en medico "

<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['correo'])) {
    header("Location: login.html");
    exit();
}


include 'bd.php';


$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener el id_usuario a partir del correo
$correo = $_SESSION['correo'];
$stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE correo = ?");
$stmt->bind_param("s", $correo);
$stmt->execute();
$stmt->bind_result($id_usuario);
$stmt->fetch();
$stmt->close();

if (!$id_usuario) {
    die("Error al obtener el ID del usuario.");
}

// Obtener el avatar del usuario
$stmt_avatar = $conn->prepare("SELECT avatar FROM usuarios WHERE id_usuario = ?");
$stmt_avatar->bind_param("i", $id_usuario);
$stmt_avatar->execute();
$stmt_avatar->bind_result($avatar);
$stmt_avatar->fetch();
$stmt_avatar->close();

// Obtener el conteo de citas
$stmt_citas = $conn->prepare("SELECT COUNT(*) FROM cita WHERE id_medico = ? AND estado IN ('pendiente', 'aceptada', 'rechazada')");
$stmt_citas->bind_param("i", $id_usuario);
$stmt_citas->execute();
$stmt_citas->bind_result($count_citas);
$stmt_citas->fetch();
$stmt_citas->close();

// Obtener las citas del usuario
$sql_citas = "SELECT c.fecha, c.hora, c.estado, 
              p.nombre AS paciente_nombre, p.apellido AS paciente_apellido, p.avatar AS paciente_avatar 
              FROM cita c 
              LEFT JOIN usuarios p ON c.id_paciente = p.id_usuario 
              WHERE c.id_medico = ? AND c.estado IN ('pendiente', 'aceptada', 'rechazada')";
$stmt = $conn->prepare($sql_citas);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result_citas = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/Nosotros_paciente.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Nosotros</title>
    <script src="JS/Nosotros.js" defer></script>
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

    <div class="hero">
        <div class="hero-content">
            <h1>Sonrisas Sleep</h1>
            <p>Trabajamos con el propósito de servir como un puente entre personas que sufren de problemas del sueño y médicos capacitados en trastornos del sueño.</p>
            <p>Buscamos proporcionar un recurso accesible y confiable para mejorar la salud del sueño y, por ende, la calidad de vida de sus usuarios.</p>
        </div>
    </div>

    <div class="tabs">
        <a href="#propósitoSection" class="tab" data-section="#propósitoSection">PROPÓSITO</a>
        <a href="#misiónSection" class="tab" data-section="#misiónSection">MISIÓN</a>
        <a href="#visiónSection" class="tab" data-section="#visiónSection">VISIÓN</a>
        <a href="#valoresSection" class="tab" data-section="#valoresSection">VALORES</a>
    </div>

    <div class="info-sections">
        <div id="propósitoSection" class="info-section">
            <div class="info-image">
                <img src="imagenes/proposito1.png" alt="Nuestra Tecnología">
            </div>
            <div class="info-text">
                <h2>Propósito</h2>
                <p>En Sonrisas Sleep, nuestro propósito es asegurar que todos tengan acceso a una atención médica de calidad. Trabajamos para eliminar barreras económicas y sociales, proporcionando soluciones efectivas para mejorar la salud del sueño y permitir que cada persona viva una vida más saludable.</p>
            </div>
        </div>
        <div id="misiónSection" class="info-section">
            <div class="info-image">
                <img src="imagenes/mision1.png" alt="Nuestro Modelo">
            </div>
            <div class="info-text">
                <h2>Misión</h2>
                <p>Ofrecer apoyo en el cuidado del sueño, ayudando a las personas a gestionar su salud y su calidad de vida de manera efectiva.</p>
            </div>
        </div>
        <div id="visiónSection" class="info-section">
            <div class="info-image">
                <img src="imagenes/vision1.png" alt="Nuestro Modelo">
            </div>
            <div class="info-text">
                <h2>Visión</h2>
                <p>Aspiramos a ser la referencia principal en salud del sueño en Latinoamérica, estableciendo estándares de excelencia en atención médica. Queremos ser reconocidos por ofrecer servicios innovadores y accesibles que mejoren la vida de las personas en toda la región.</p>
            </div>
        </div>
        <div id="valoresSection" class="info-section">
            <div class="info-image">
                <img src="imagenes/valores1.jpg" alt="Nuestro Modelo">
            </div>
            <div class="info-text">
                <h2>Valores</h2>
                <p>Empatía: Comprendemos y valoramos las experiencias de nuestros pacientes para ofrecer un cuidado personalizado.</p>
                <p>Colaboración: Trabajamos en equipo y con nuestros pacientes para lograr soluciones integrales.</p>
                <p>Adaptabilidad: Nos ajustamos a los cambios y desafíos para ofrecer el mejor cuidado posible.</p>
                <p>Humildad: Mantenemos una actitud abierta, aprendiendo y mejorando constantemente.</p>
                <p>Integridad: Operamos con ética y honestidad, manteniendo la confianza y la calidad en nuestros servicios.</p>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <div class="footer-block">
                <h4>Contacto</h4>
                <p>Dirección: Carretera Estatal 420 S/N, El Rosario, 76240, 76240 Santiago de Querétaro, Qro.</p>
                <p>Teléfono: (442) 849 8477 </p>
                <p>Email: sonrisasleep@gmail.com</p>
            </div>
            <div class="footer-block">
                <h4>Redes Sociales</h4>
                <p>Facebook: /sonrisasleep</p>
                <p>Twitter: @sonrisasleep</p>
                <p>Instagram: @sonrisasleep</p>
            </div>
            <div class="footer-block">
                <h4>Horarios</h4>
                <p>Lunes a Viernes: 9:00 AM - 6:00 PM</p>
                <p>Sábado: 10:00 AM - 2:00 PM</p>
                <p>Domingo: Cerrado</p>
            </div>
        </div>
        <div class="social-icons">
            <a href="https://www.facebook.com" target="_blank"><i class="fab fa-facebook-f"></i></a>
            <a href="https://www.instagram.com" target="_blank"><i class="fab fa-instagram"></i></a>
            <a href="https://www.twitter.com" target="_blank"><i class="fab fa-twitter"></i></a>
        </div>
    </footer>

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
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<p>No tienes citas.</p>';
        }
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
    </script>
</body>
</html>
