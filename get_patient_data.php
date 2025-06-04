<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "consultorio_db");
if ($conn->connect_error) {
    echo json_encode(['error' => 'Conexión fallida']);
    exit();
}

// Crear tabla patients si no existe
$table_sql = "CREATE TABLE IF NOT EXISTS patients (\n    name VARCHAR(255) PRIMARY KEY,\n    history TEXT,\n    data TEXT\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$conn->query($table_sql);

$patient = isset($_GET['patient']) ? $conn->real_escape_string($_GET['patient']) : '';
$sql = "SELECT history, data FROM patients WHERE name = '$patient' LIMIT 1";
$result = $conn->query($sql);
if ($row = $result->fetch_assoc()) {
    echo json_encode(['history' => $row['history'], 'data' => $row['data']]);
} else {
    echo json_encode(['history' => '', 'data' => '']);
}
$conn->close();
?>
