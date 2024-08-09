"Aqui solo muestra informacion de nosotros en paciente "
<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['correo'])) {
    header("Location: login.html");
    exit();
}

include 'navbar.php';
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
$stmt = $conn->prepare("SELECT avatar FROM usuarios WHERE id_usuario = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$stmt->bind_result($avatar);
$stmt->fetch();
$stmt->close();

// Obtener el conteo de citas pendientes
$stmt = $conn->prepare("SELECT COUNT(*) FROM cita WHERE id_paciente = ? AND estado IN ('pendiente', 'aceptada', 'rechazada')");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$stmt->bind_result($count_citas);
$stmt->fetch();
$stmt->close();

// Obtener las citas del usuario
$sql_citas = "SELECT c.fecha, c.hora, c.estado, 
                      u.nombre AS medico_nombre, u.apellido AS medico_apellido, u.avatar AS medico_avatar 
                      FROM cita c 
                      LEFT JOIN medico med ON c.id_medico = med.id_medico 
                      LEFT JOIN usuarios u ON med.id_usuario = u.id_usuario 
                      WHERE c.id_paciente = ? AND c.estado IN ('pendiente', 'aceptada', 'rechazada')";
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

    <?php
 include 'pie_pagina.php';
 ?>
    <div id="notificationDropdown" class="notification-dropdown">
        <div class="notification-dropdown-header">
            <h4>Citas</h4>
            <span class="close">&times;</span>
        </div>
        <?php
        if ($result_citas->num_rows > 0) {
            while($row = $result_citas->fetch_assoc()) {
                echo '<div class="notification-item">';
                echo '<img src="' . $row["medico_avatar"] . '" alt="Avatar">';
                echo '<div class="notification-text">';
                echo '<p><strong>Cita con ' . $row["medico_nombre"] . ' ' . $row["medico_apellido"] . '</strong></p>';
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

    
   
</body>
</html>
