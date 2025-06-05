<?php
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
$name = $data['name'] ?? '';
$history = $data['history'] ?? '';
$other = $data['data'] ?? '';
$obra_social = '';
// Intentar extraer obra social del primer renglón de la historia clínica
if (preg_match('/Obra Social:\s*(.+)/i', $history, $matches)) {
    $obra_social = trim($matches[1]);
}
$conn = new mysqli("localhost", "root", "", "consultorio_db");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Conexión fallida']);
    exit();
}
// Crear tabla patients si no existe
$table_sql = "CREATE TABLE IF NOT EXISTS patients (\n    name VARCHAR(255) PRIMARY KEY,\n    history TEXT,\n    data TEXT\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$conn->query($table_sql);
// Si existe, actualiza; si no, inserta
$sql = "INSERT INTO patients (name, history, data) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE history=?, data=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('sssss', $name, $history, $other, $history, $other);
$ok = $stmt->execute();
// Si se extrajo obra social, actualizar también en appointments
if ($obra_social !== '') {
    $conn->query("UPDATE appointments SET obra_social = '" . $conn->real_escape_string($obra_social) . "' WHERE patient_name = '" . $conn->real_escape_string($name) . "'");
}
echo json_encode(['success' => $ok]);
$conn->close();
?>
