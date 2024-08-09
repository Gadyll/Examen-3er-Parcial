<?php
session_start();

if (!isset($_SESSION['correo'])) {
    header("Location: login.html");
    exit();
}

$correo = $_SESSION['correo'];

include 'navbar.php';
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

// Manejar solicitudes POST para mostrar historial de citas o próxima cita
$contenido_citas = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    if ($_POST['accion'] === 'historial_citas') {
        $pagina = isset($_POST['pagina']) ? intval($_POST['pagina']) : 1;
        $registros_por_pagina = 10;
        $offset = ($pagina - 1) * $registros_por_pagina;

        $sql_citas = "SELECT c.fecha, c.hora, CONCAT(u.nombre, ' ', u.apellido) AS medico 
                      FROM cita c
                      JOIN medico m ON c.id_medico = m.id_medico
                      JOIN usuarios u ON m.id_usuario = u.id_usuario
                      WHERE c.id_paciente = ?
                      ORDER BY c.fecha DESC, c.hora DESC
                      LIMIT ?, ?";
        $stmt_citas = $conn->prepare($sql_citas);
        $stmt_citas->bind_param("iii", $id_usuario, $offset, $registros_por_pagina);
        $stmt_citas->execute();
        $result_citas = $stmt_citas->get_result();

        $citas = [];
        while ($row = $result_citas->fetch_assoc()) {
            $citas[] = $row;
        }

        $sql_total_citas = "SELECT COUNT(*) AS total FROM cita WHERE id_paciente = ?";
        $stmt_total = $conn->prepare($sql_total_citas);
        $stmt_total->bind_param("i", $id_usuario);
        $stmt_total->execute();
        $result_total = $stmt_total->get_result();
        $total_citas = $result_total->fetch_assoc()['total'];
        $total_paginas = ceil($total_citas / $registros_por_pagina);

        $stmt_citas->close();
        $stmt_total->close();

        $contenido_citas .= '<div class="citas-historial">';
        $contenido_citas .= '<h3>Historial de Citas</h3>';
        if ($citas) {
            foreach ($citas as $cita) {
                $contenido_citas .= '<div class="cita-item">';
                $contenido_citas .= '<p><strong>Fecha:</strong> ' . $cita['fecha'] . '</p>';
                $contenido_citas .= '<p><strong>Hora:</strong> ' . $cita['hora'] . '</p>';
                $contenido_citas .= '<p><strong>Médico:</strong> ' . $cita['medico'] . '</p>';
                $contenido_citas .= '</div>';
            }
            $contenido_citas .= '<div class="paginacion">';
            for ($i = 1; $i <= $total_paginas; $i++) {
                $contenido_citas .= '<form method="post" action="paciente.php" style="display:inline;">';
                $contenido_citas .= '<input type="hidden" name="accion" value="historial_citas">';
                $contenido_citas .= '<input type="hidden" name="pagina" value="' . $i . '">';
                $contenido_citas .= '<button type="submit" class="btn-paginacion">' . $i . '</button>';
                $contenido_citas .= '</form>';
            }
            $contenido_citas .= '</div>';
        } else {
            $contenido_citas .= '<p>No tienes citas en el historial.</p>';
        }
        $contenido_citas .= '</div>';
    } elseif ($_POST['accion'] === 'proxima_cita') {
        $sql_cita = "SELECT c.fecha, c.hora, CONCAT(u.nombre, ' ', u.apellido) AS medico 
                     FROM cita c
                     JOIN medico m ON c.id_medico = m.id_medico
                     JOIN usuarios u ON m.id_usuario = u.id_usuario
                     WHERE c.id_paciente = ? AND c.fecha >= CURDATE()
                     ORDER BY c.fecha ASC, c.hora ASC LIMIT 1";
        $stmt_cita = $conn->prepare($sql_cita);
        $stmt_cita->bind_param("i", $id_usuario);
        $stmt_cita->execute();
        $result_cita = $stmt_cita->get_result();

        $cita = $result_cita->fetch_assoc();

        $stmt_cita->close();

        $contenido_citas .= '<div class="cita-container">';
        $contenido_citas .= '<h3>Próxima Cita</h3>';
        if ($cita) {
            $contenido_citas .= '<p><strong>Fecha:</strong> ' . $cita['fecha'] . '</p>';
            $contenido_citas .= '<p><strong>Hora:</strong> ' . $cita['hora'] . '</p>';
            $contenido_citas .= '<p><strong>Médico:</strong> ' . $cita['medico'] . '</p>';
        } else {
            $contenido_citas .= '<p>No tienes próximas citas.</p>';
        }
        $contenido_citas .= '</div>';
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/paciente.css?v=1.0">
    <link rel="stylesheet" href="CSS/Nosotros_paciente.css">
    <link rel="stylesheet" href="CSS/editar_perfil.css?v=1.0">

 
    
   
    <title>Paciente</title>
</head>
<body>
 
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
                    <button class="btn" onclick="mostrarExpediente()">Mi expediente</button>
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
                        <span>Género: <?php echo $usuario['genero']; ?></span>
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
                <div class="appointment-info">
                    <div class="btn-group-appointment">
                        <form method="post" action="paciente.php">
                            <input type="hidden" name="id_usuario" value="<?php echo $id_usuario; ?>">
                            <input type="hidden" name="accion" value="proxima_cita">
                            <button type="submit" class="btn btn-appointment">Mi próxima cita</button>
                        </form>
                        <form method="post" action="paciente.php">
                            <input type="hidden" name="id_usuario" value="<?php echo $id_usuario; ?>">
                            <input type="hidden" name="accion" value="historial_citas">
                            <button type="submit" class="btn btn-appointment">Mi historial de citas</button>
                        </form>
                    </div>
                    <div id="info-citas"><?php echo $contenido_citas; ?></div>
                </div>
            </div>
            <div class="right-panel" id="right-panel">
                <!-- Contenido dinámico aquí -->
                <div class="expediente-section">
                    <h2>Mi Expediente</h2>
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success">
                            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger">
                            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($expediente): ?>
                    <div class="btn-group-expediente">
                        <a href="<?php echo $expediente['url_documento']; ?>" target="_blank" class="btn-view">Ver Expediente</a>
                        <form action="eliminar_expediente.php" method="post" style="display:inline;">
                            <input type="hidden" name="id_documento" value="<?php echo $expediente['id_documento']; ?>">
                            <button type="submit" class="btn-danger">Eliminar Expediente</button>
                        </form>
                    </div>

                    <?php else: ?>
                        <p>No tienes un expediente subido actualmente.</p>
                    <?php endif; ?>

                    <form id="expedienteForm" action="subir_expediente.php" method="post" enctype="multipart/form-data" style="<?php echo $expediente ? 'display:none;' : ''; ?>">
                        <div class="file-input-container">
                            <input type="file" name="expediente" id="expedienteInput" class="file-input">
                            <input type="hidden" name="id_usuario" value="<?php echo $id_usuario; ?>">
                            <label for="expedienteInput" class="file-input-label">Elegir Archivo</label>
                        </div>
                        <button type="submit" class="btn btn-submit">Subir Expediente</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

 <?php
 include 'pie_pagina.php';
 ?>

    <script>
     
        function mostrarExpediente() {
            document.getElementById('expedienteForm').style.display = 'block';
            document.getElementById('expedienteDisplay').style.display = 'none';
        }
    </script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/locale/es.js'></script>
</body>
</html>
