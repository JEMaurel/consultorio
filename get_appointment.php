<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "consultorio_db");
if ($conn->connect_error) {
    echo json_encode(['error' => 'Conexión fallida']);
    exit();
}
$date = $_GET['date'] ?? '';
$time = $_GET['time'] ?? '';
$sql = "SELECT * FROM appointments WHERE appointment_date = '$date' AND appointment_time = '$time' LIMIT 1";
$result = $conn->query($sql);
if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(['patient_name' => '', 'obra_social' => '']);
}
$conn->close();
?>
