"este no hace nada no lo toques"

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

$searchQuery = "";
if (isset($_GET['search'])) {
    $searchQuery = $_GET['search'];
    $sql = "SELECT u.id_usuario, u.nombre, u.apellido, u.avatar, e.nombre AS especialidad 
            FROM usuarios u 
            INNER JOIN medico m ON u.id_usuario = m.id_usuario 
            INNER JOIN especialidades e ON m.id_especialidad = e.id_especialidad
            WHERE u.nombre LIKE ? OR u.apellido LIKE ?";
    $stmt = $conn->prepare($sql);
    $searchParam = '%' . $searchQuery . '%';
    $stmt->bind_param("ss", $searchParam, $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT u.id_usuario, u.nombre, u.apellido, u.avatar, e.nombre AS especialidad 
            FROM usuarios u 
            INNER JOIN medico m ON u.id_usuario = m.id_usuario 
            INNER JOIN especialidades e ON m.id_especialidad = e.id_especialidad";
    $result = $conn->query($sql);
}

if ($result === false) {
    die("Error en la consulta SQL: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/medicos.css">
    <title>Médicos</title>
</head>
<body>
    <header>
        <div class="menu">
            <div class="container">
                <div class="logo">
                    <img src="imagenes/logo.jpg" alt="#" />
                </div>
                <nav class="navbar">
                    <ul>
                        <li><a href="paciente.php">Inicio</a></li>
                        <li><a href="Nosotros.html">Nosotros</a></li>
                    </ul>
                </nav>
                <form class="search-form" method="GET" action="Medicos.php">
                    <input type="text" name="search" placeholder="Buscar médicos..." value="<?php echo $searchQuery; ?>"/>
                    <button type="submit">Buscar</button>
                </form>
            </div>
        </div>
    </header>

    <div class="doctors-list">
        <div class="container">
            <h1>Médicos</h1>
            <div class="grid">
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '<div class="doctor-card">';
                        echo '<img src="' . $row["avatar"] . '" alt="Avatar del doctor">';
                        echo '<h3>' . $row["nombre"] . ' ' . $row["apellido"] . '</h3>';
                        echo '<p>' . $row["especialidad"] . '</p>';
                        echo '<a href="perfil_medico.php?id=' . $row["id_usuario"] . '">Ver perfil</a>';
                        echo '</div>';
                    }
                } else {
                    echo "No hay médicos registrados.";
                }

                $conn->close();
                ?>
            </div>
        </div>
    </div>
</body>
</html>
