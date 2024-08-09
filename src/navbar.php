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

// Obtener el id_usuario a partir del correo
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
$stmt_citas = $conn->prepare("SELECT COUNT(*) FROM cita WHERE id_paciente = ? AND estado = 'pendiente'");
$stmt_citas->bind_param("i", $id_usuario);
$stmt_citas->execute();
$stmt_citas->bind_result($count_citas);
$stmt_citas->fetch();
$stmt_citas->close();

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
                        <img id="avatar" src="<?php echo htmlspecialchars($avatar ?? 'default_avatar.png'); ?>" alt="Avatar" class="avatar">
                    </a>
                    <div class="dropdown-content">
                        <a href="editar_perfil.php">Editar perfil</a>
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
                <div class="icons">
                    <div class="search-icon" onclick="toggleSearch()">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="search-bar">
                        <input type="text" id="searchInput" placeholder="Buscar especialistas..." oninput="buscarEspecialistas()">
                    </div>
                </div>
            </ul>
        </nav>
    </div>
</header>

<div id="notificationDropdown" class="notification-dropdown">
    <div class="notification-dropdown-header">
        <h4>Citas Pendientes</h4>
        <span class="close">&times;</span>
    </div>
    <div id="citasContent">
        <p>Cargando...</p>
    </div>
</div>


<script>
    document.getElementById('notificationIcon').onclick = function() {
        var dropdown = document.getElementById('notificationDropdown');
        var content = document.getElementById('citasContent');
        
        fetch('obtener_citas_pendientes.php')
            .then(response => response.text())
            .then(data => {
                content.innerHTML = data;
            })
            .catch(error => {
                console.error('Error:', error);
                content.innerHTML = '<p>Ocurrió un error al obtener las citas.</p>';
            });
        
        dropdown.style.display = "block";
    }

    function closeDropdown() {
        document.getElementById('notificationDropdown').style.display = "none";
    }

    window.onclick = function(event) {
        var dropdown = document.getElementById('notificationDropdown');
        if (event.target == dropdown) {
            dropdown.style.display = "none";
        }
    }

    document.querySelector('.close').onclick = closeDropdown;
</script>
</body>
</html>
