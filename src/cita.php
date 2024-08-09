"No me acuerdo de esta donde esta relacionada"

<?php

include 'bd.php';


// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener los eventos desde la base de datos
$sql = "SELECT id_cita AS id, fecha AS start, hora AS end, CONCAT('Cita con el doctor ', id_medico) AS title FROM cita";
$result = $conn->query($sql);

$events = array();

while ($row = $result->fetch_assoc()) {
    $row['end'] = date('Y-m-d H:i:s', strtotime($row['start'] . ' ' . $row['end']));
    $row['start'] = date('Y-m-d H:i:s', strtotime($row['start']));
    $events[] = $row;
}

$conn->close();

// Enviar los datos en formato JSON
echo json_encode($events);
?>
