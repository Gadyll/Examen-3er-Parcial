<?php
session_start();

if (!isset($_SESSION['correo'])) {
    header("Location: login.html");
    exit();
}

include 'bd.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID del médico no proporcionado.");
}

$id_medico = $_GET['id'];

// Obtener los datos del médico
$sql = "SELECT u.nombre, u.apellido, u.avatar, e.nombre AS especialidad, u.direccion, c.nombre as ciudad, es.nombre as estado 
        FROM usuarios u 
        INNER JOIN medico m ON u.id_usuario = m.id_usuario 
        INNER JOIN especialidades e ON m.id_especialidad = e.id_especialidad 
        LEFT JOIN ciudad c ON u.id_ciudad = c.id_ciudad
        LEFT JOIN estado es ON u.id_estado = es.id_estado
        WHERE m.id_medico = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_medico);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $medico = $result->fetch_assoc();
} else {
    die("No se encontró el médico.");
}

$conn->close();
include 'navbar.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/perfil_medico.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <title>Perfil del Médico</title>
</head>
<body>
    <main>
        <div class="main-container">
            <section class="tab-content-container">
                <section class="profile container2">
                    <div class="profile-header2">
                        <img src="<?php echo $medico['avatar']; ?>" alt="Doctor" class="doctor-image2">
                        <div class="profile-info2">
                            <h1 class="doctor-name2"><?php echo $medico['nombre'] . ' ' . $medico['apellido']; ?></h1>
                            <p class="doctor-specialty2"><?php echo $medico['especialidad']; ?></p>
                            <p><strong>Dirección:</strong> <span class="doctor-address2"><?php echo $medico['direccion']; ?></span></p>
                            <p><strong>Ubicación:</strong> <span class="doctor-location2"><?php echo $medico['ciudad'] . ', ' . $medico['estado']; ?></span></p>
                            <p><strong>Consulta en línea:</strong> <span class="doctor-online-consultation2">Sí</span></p>
                            <p><strong>Opiniones:</strong> <span class="doctor-reviews2">4.5/5</span></p>
                            <a href="#appointment-form" class="button2" id="book-appointment">Agendar cita</a>
                            <a href="#" class="button2 secondary2" id="send-message">Enviar mensaje</a>
                        </div>
                    </div>

                    <div class="tabs2">
                        <button class="tab-button2 active2" onclick="showTab('consultorios')">Consultorios</button>
                        <button class="tab-button2" onclick="showTab('precios')">Precios</button>
                        <button class="tab-button2" onclick="showTab('experiencia')">Experiencia</button>
                        <button class="tab-button2" onclick="showTab('opiniones')">Opiniones</button>
                    </div>

                    <div id="consultorios" class="tab-content active">
                        <h2>Consultorios</h2>
                        <div class="clinic-list">
                            <p><strong>Dirección:</strong> <?php echo $medico['direccion']; ?></p>
                            <p><strong>Ubicación:</strong> <?php echo $medico['ciudad'] . ', ' . $medico['estado']; ?></p>
                            <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($medico['direccion']); ?>" target="_blank">Ver en Google Maps</a>
                        </div>
                    </div>

                    <div id="precios" class="tab-content">
                        <h2>Precios</h2>
                        <p class="doctor-prices">Consulta general: $500</p>
                    </div>

                    <div id="experiencia" class="tab-content">
                        <h2>Experiencia</h2>
                        <p class="doctor-experience">Más de 10 años de experiencia en Medicina Complementaria y Alternativa.</p>
                    </div>

                    <div id="opiniones" class="tab-content">
                        <h2>Opiniones</h2>
                        <div class="doctor-opinions">
                            <p>"Excelente atención y profesionalismo."</p>
                            <p>"Muy buen doctor, muy recomendado."</p>
                        </div>
                    </div>
                </section>
            </section>

            <section class="appointment-schedule-container">
                <div class="appointment-schedule">
                    <h2>Agendar cita</h2>
                    <form id="appointment-form" action="agendar_cita.php" method="post">
                        <div class="appointment-type">
                            <label>Tipo de visita:</label>
                            <div class="appointment-tabs">
                                <button type="button" class="appointment-tab active">Visita presencial</button>
                                <button type="button" class="appointment-tab">Consulta en línea</button>
                            </div>
                        </div>
                        <div class="appointment-address">
                            <label>Dirección:</label>
                            <input type="text" value="<?php echo $medico['direccion']; ?>" disabled>
                        </div>
                        <div class="appointment-reason">
                            <label>Elige el motivo de la visita:</label>
                            <select name="motivo">
                                <option value="Primera visita Medicina Complementaria y Alternativa">Primera visita Medicina Complementaria y Alternativa</option>
                            </select>
                        </div>
                        <div class="appointment-date">
                            <label>Fecha de la cita:</label>
                            <input type="date" id="appointment-date" name="fecha_cita" class="form-control">
                        </div>
                        <div class="appointment-time">
                            <label>Hora de la cita:</label>
                            <input type="time" id="appointment-time" name="hora_cita" class="form-control">
                        </div>
                        <input type="hidden" name="id_medico" value="<?php echo $id_medico; ?>">
                        <input type="hidden" name="id_usuario" value="<?php echo $_SESSION['id_usuario']; ?>">
                        <button type="submit" class="submit-button">Agendar</button>
                    </form>
                </div>
            </section>
        </div>
    </main>

    <?php
    include 'pie_pagina.php';
    ?>
</body>
</html>
