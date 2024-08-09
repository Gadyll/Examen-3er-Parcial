<?php
session_start();

if (!isset($_SESSION['correo'])) {
    header("Location: login.html");
    exit();
}

include 'bd.php';
include 'navbar2.php';
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

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

$stmt_avatar = $conn->prepare("SELECT avatar FROM usuarios WHERE id_usuario = ?");
$stmt_avatar->bind_param("i", $id_usuario);
$stmt_avatar->execute();
$stmt_avatar->bind_result($avatar);
$stmt_avatar->fetch();
$stmt_avatar->close();

$stmt_citas = $conn->prepare("SELECT COUNT(*) FROM cita WHERE id_medico = ? AND estado IN ('pendiente', 'aceptada', 'rechazada')");
$stmt_citas->bind_param("i", $id_usuario);
$stmt_citas->execute();
$stmt_citas->bind_result($count_citas);
$stmt_citas->fetch();
$stmt_citas->close();

$sql_pacientes = "SELECT u.id_usuario, u.nombre, u.apellido, u.avatar, u.fecha_nacimiento, g.genero, c.fecha, c.hora
                  FROM usuarios u
                  JOIN cita c ON u.id_usuario = c.id_paciente
                  LEFT JOIN generos g ON u.id_genero = g.id_genero
                  WHERE c.id_medico = ? AND c.estado = 'aceptada'
                  ORDER BY c.fecha, c.hora";

$stmt_pacientes = $conn->prepare($sql_pacientes);
$stmt_pacientes->bind_param("i", $id_usuario);
$stmt_pacientes->execute();
$result_pacientes = $stmt_pacientes->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/inicio_medico.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Inicio</title>
</head>
<body>
 
    <section class="patients-grid">
        <div class="container">
            <?php
            if ($result_pacientes->num_rows > 0) {
                while ($row = $result_pacientes->fetch_assoc()) {
                    $fecha_nacimiento = new DateTime($row["fecha_nacimiento"]);
                    $hoy = new DateTime();
                    $edad = $hoy->diff($fecha_nacimiento)->y;

                    echo '<div class="patient-card">';
                    echo '<img src="' . $row["avatar"] . '" alt="Paciente">';
                    echo '<div class="patient-info">';
                    echo '<h4><a class="patient-name" href="perfil_paciente.php?id=' . $row["id_usuario"] . '">' . $row["nombre"] . ' ' . $row["apellido"] . '</a></h4>';
                    echo '<p><strong>Edad:</strong> ' . $edad . '</p>';
                    echo '<p><strong>Género:</strong> ' . $row["genero"] . '</p>';
                    echo '<p><strong>Fecha de la cita:</strong> ' . $row["fecha"] . '</p>';
                    echo '<p><strong>Hora de la cita:</strong> ' . $row["hora"] . '</p>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo "<div class='no-patients-container'><p class='no-patients'>No hay pacientes registrados.</p></div>";
            }
            ?>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-block">
                <h4>Conócenos</h4>
                <p><a href="nosotros_paciente.php">Quiénes somos</a></p>
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

    <div id="notificationDropdown" class="notification-dropdown">
        <div class="notification-dropdown-header">
            <h4>Citas</h4>
            <span class="close">&times;</span>
        </div>
        <?php
        // Obtener las citas del médico
        $conn = new mysqli($servername, $username, $password, $dbname);
        $sql_citas = "SELECT c.id_cita, c.fecha, c.hora, c.estado, 
                             p.nombre AS paciente_nombre, p.apellido AS paciente_apellido, p.id_usuario AS paciente_id, p.avatar AS paciente_avatar 
                      FROM cita c 
                      LEFT JOIN usuarios p ON c.id_paciente = p.id_usuario 
                      WHERE c.id_medico = ? AND c.estado IN ('pendiente', 'aceptada', 'rechazada')";
        $stmt_citas = $conn->prepare($sql_citas);
        $stmt_citas->bind_param("i", $id_usuario);
        $stmt_citas->execute();
        $result_citas = $stmt_citas->get_result();

        if ($result_citas->num_rows > 0) {
            while ($row = $result_citas->fetch_assoc()) {
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
    </script>
    <script src="JS/inicio_medico.js"></script>
</body>
</html>
