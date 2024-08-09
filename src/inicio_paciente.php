<?php
session_start();

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

// Obtener los detalles de las citas
$stmt_detalle_citas = $conn->prepare("SELECT c.fecha, c.hora, c.estado, u.nombre AS medico_nombre, u.apellido AS medico_apellido, u.avatar AS medico_avatar 
                                      FROM cita c 
                                      LEFT JOIN medico m ON c.id_medico = m.id_medico 
                                      LEFT JOIN usuarios u ON m.id_usuario = u.id_usuario 
                                      WHERE c.id_paciente = ?");
$stmt_detalle_citas->bind_param("i", $id_usuario);
$stmt_detalle_citas->execute();
$citas = $stmt_detalle_citas->get_result();
$stmt_detalle_citas->close();

// Obtener la lista de médicos
$sql = "SELECT m.id_medico, u.id_usuario, u.nombre, u.apellido, u.avatar, u.direccion, c.nombre as ciudad, e.nombre as estado, esp.nombre as especialidad
        FROM usuarios u
        JOIN medico m ON u.id_usuario = m.id_usuario
        JOIN ciudad c ON u.id_ciudad = c.id_ciudad
        JOIN estado e ON u.id_estado = e.id_estado
        JOIN especialidades esp ON m.id_especialidad = esp.id_especialidad";

$result = $conn->query($sql);

if (!$result) {
    die("Error en la consulta: " . $conn->error);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/inicio_paciente.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Inicio</title>
</head>
<body>

<section class="doctors-grid">
    <div class="container">
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo '<div class="doctor-card">';
                echo '<img src="' . htmlspecialchars($row["avatar"] ?? 'default_avatar.png') . '" alt="Doctor">';
                echo '<div class="doctor-info">';
                echo '<h4><a class="doctor-name" href="perfil_medico.php?id=' . htmlspecialchars($row["id_medico"]) . '">' . htmlspecialchars($row["nombre"]) . ' ' . htmlspecialchars($row["apellido"]) . '</a></h4>';
                echo '<p>' . htmlspecialchars($row["especialidad"]) . '</p>';
                echo '<p><strong>Dirección:</strong> ' . htmlspecialchars($row["direccion"]) . '</p>';
                echo '<p><strong>Ubicación:</strong> ' . htmlspecialchars($row["ciudad"]) . ', ' . htmlspecialchars($row["estado"]) . '</p>';
                echo '<a href="https://www.google.com/maps/search/?api=1&query=' . urlencode($row["direccion"]) . '" target="_blank">Ver en el mapa</a>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo "<p>No hay médicos disponibles.</p>";
        }
        ?>
    </div>
</section>

<section class="map">
    <div class="container">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3732.249823706356!2d-100.39178338454833!3d20.5887933862095!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x85d342b59c65c799%3A0x4c49fef1c170d2b8!2sQuer%C3%A9taro%2C%20Qro.%2C%20M%C3%A9xico!5e0!3m2!1ses-419!2sus!4v1624023242342!5m2!1ses-419!2sus" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
    </div>
</section>

<section class="testimonials">
    <div class="container">
        <h2>Testimonios</h2>
        <div class="testimonial-slider">
            <div class="testimonial-card">
                <p class="quote">"Excelente atención y resultados inmediatos!"</p>
                <p class="author">- Juan Pérez</p>
            </div>
            <div class="testimonial-card">
                <p class="quote">"Muy recomendable, profesionales y amables."</p>
                <p class="author">- María López</p>
            </div>
            <div class="testimonial-card">
                <p class="quote">"Recibí un servicio increíble, definitivamente volveré."</p>
                <p class="author">- Luis Gómez</p>
            </div>
        </div>
    </div>
</section>    

<?php
 include 'pie_pagina.php';
 ?>
<script src="JS/inicio_paciente.js"></script>
</body>
</html>
