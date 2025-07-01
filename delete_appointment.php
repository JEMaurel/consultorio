<?php
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
$date = $data['appointment_date'] ?? '';
$time = $data['appointment_time'] ?? '';
$conn = new mysqli("localhost", "root", "", "consultorio_db");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Conexión fallida']);
    exit();
}
$sql = "DELETE FROM appointments WHERE appointment_date = '$date' AND appointment_time = '$time'";
if ($conn->query($sql)) {
    // Actualizar respaldo de turnos
    require_once __DIR__ . '/backup_utils.php';
    generar_respaldo_turnos($conn);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}
$conn->close();
?>
