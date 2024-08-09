<?php
session_start();

if (!isset($_SESSION['correo'])) {
    header("Location: login.html");
    exit();
}

$correo = $_SESSION['correo'];

include 'bd.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$id_usuario = $_POST['id_usuario'];
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
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Citas</title>
    <link rel="stylesheet" href="CSS/paciente.css">
</head>
<body>
    <div class="citas-historial">
        <h3>Historial de Citas</h3>
        <?php if ($citas): ?>
            <?php foreach ($citas as $cita): ?>
                <div class="cita-item">
                    <p><strong>Fecha:</strong> <?php echo $cita['fecha']; ?></p>
                    <p><strong>Hora:</strong> <?php echo $cita['hora']; ?></p>
                    <p><strong>Médico:</strong> <?php echo $cita['medico']; ?></p>
                </div>
            <?php endforeach; ?>
            <div class="paginacion">
                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <form method="post" action="historial_citas.php" style="display:inline;">
                        <input type="hidden" name="id_usuario" value="<?php echo $id_usuario; ?>">
                        <input type="hidden" name="pagina" value="<?php echo $i; ?>">
                        <button type="submit" class="btn-paginacion"><?php echo $i; ?></button>
                    </form>
                <?php endfor; ?>
            </div>
        <?php else: ?>
            <p>No tienes citas en el historial.</p>
        <?php endif; ?>
    </div>
</body>
</html>
