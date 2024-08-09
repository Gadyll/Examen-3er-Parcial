"este es la de editar en perfil paciente ya sirve pero pruebalo "

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

// Obtener los datos del usuario
$sql = "SELECT u.nombre, u.apellido, u.telefono, u.correo, u.avatar, u.fecha_nacimiento, u.id_estado, u.id_ciudad, u.direccion, DATE_FORMAT(u.ultimo_ingreso, '%d/%b/%Y %r') AS ultimo_ingreso, e.nombre AS estado, c.nombre AS ciudad 
        FROM usuarios u 
        LEFT JOIN estado e ON u.id_estado = e.id_estado 
        LEFT JOIN ciudad c ON u.id_ciudad = c.id_ciudad 
        WHERE u.correo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $usuario = $result->fetch_assoc();
    $nombreCompleto = $usuario['nombre'] . ' ' . $usuario['apellido'];
    $nombre = $usuario['nombre'];
    $apellido = $usuario['apellido'];
    $telefono = $usuario['telefono'];
    $correo = $usuario['correo'];
    $fecha_nacimiento = $usuario['fecha_nacimiento'];
    $estado = $usuario['id_estado'];
    $ciudad = $usuario['id_ciudad'];
    $direccion = $usuario['direccion'];
    $avatar = $usuario['avatar'] ? $usuario['avatar'] : 'imagenes/avatar.png';
    $ultimoIngreso = $usuario['ultimo_ingreso'] ? $usuario['ultimo_ingreso'] : 'N/A';
} else {
    echo "No se encontró el usuario.";
    exit();
}

// Obtener la lista de ciudades
$sql_ciudades = "SELECT id_ciudad, nombre FROM ciudad";
$result_ciudades = $conn->query($sql_ciudades);

// Obtener la lista de estados
$sql_estados = "SELECT id_estado, nombre FROM estado";
$result_estados = $conn->query($sql_estados);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/editar_perfil.css">
    <title>Editar Perfil</title>
</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="header-container">
                <a href="#" class="logo">Logo</a>
                <ul class="nav-menu">
                    <li class="nav-menu-item">
                        <a href="inicio_paciente.php" class="nav-menu-link nav-link">Inicio</a>
                    </li>
                    <li class="nav-menu-item dropdown">
                        <a href="#" class="nav-menu-link nav-link">
                            <img id="avatar" src="<?php echo $avatar; ?>" alt="Avatar" class="avatar">
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <div class="main-container">
        <div class="profile-section">
            <h1>Editar Perfil</h1>
            <form action="actualizar_perfil.php" method="post" enctype="multipart/form-data">
                <div class="avatar-upload">
                    <img src="<?php echo $avatar; ?>" alt="Avatar" id="profile-image">
                    <label for="avatar-input" class="edit-icon">
                        <i class="fas fa-pencil-alt"></i>
                    </label>
                    <input type="file" name="avatar" id="avatar-input" style="display: none;">
                </div>
                <div class="form-section">
                    <label for="nombre">Nombre</label>
                    <input type="text" name="nombre" id="nombre" value="<?php echo $nombre; ?>" required>
                </div>
                <div class="form-section">
                    <label for="apellido">Apellido</label>
                    <input type="text" name="apellido" id="apellido" value="<?php echo $apellido; ?>" required>
                </div>
                <div class="form-section">
                    <label for="correo">Correo electrónico</label>
                    <input type="email" name="correo" id="correo" value="<?php echo $correo; ?>" required>
                </div>
                <div class="form-section">
                    <label for="telefono">Teléfono</label>
                    <input type="text" name="telefono" id="telefono" value="<?php echo $telefono; ?>" required>
                </div>
                <div class="form-section">
                    <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                    <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" value="<?php echo $fecha_nacimiento; ?>" required>
                </div>
                <div class="form-section">
                    <label for="estado">Estado</label>
                    <select name="estado" id="estado" required>
                        <?php while ($row_estado = $result_estados->fetch_assoc()): ?>
                            <option value="<?php echo $row_estado['id_estado']; ?>" <?php echo $estado == $row_estado['id_estado'] ? 'selected' : ''; ?>>
                                <?php echo $row_estado['nombre']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-section">
                    <label for="ciudad">Ciudad</label>
                    <select name="ciudad" id="ciudad" required>
                        <?php while ($row_ciudad = $result_ciudades->fetch_assoc()): ?>
                            <option value="<?php echo $row_ciudad['id_ciudad']; ?>" <?php echo $ciudad == $row_ciudad['id_ciudad'] ? 'selected' : ''; ?>>
                                <?php echo $row_ciudad['nombre']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-section">
                    <label for="direccion">Dirección</label>
                    <input type="text" name="direccion" id="direccion" value="<?php echo $direccion; ?>" required>
                </div>
                <button type="submit" class="btn">Guardar Cambios</button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('avatar-input').onchange = function() {
            document.forms[0].submit();
        };

        document.addEventListener("DOMContentLoaded", function() {
            <?php if (isset($_SESSION['success_message'])): ?>
                alert("<?php echo $_SESSION['success_message']; ?>");
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['error_message'])): ?>
                alert("<?php echo $_SESSION['error_message']; ?>");
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
        });
    </script>
</body>
</html>
