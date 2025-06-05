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

// Modifico para devolver también obra_social si existe
$patient = isset($_GET['patient']) ? $conn->real_escape_string($_GET['patient']) : '';
$sql = "SELECT history, data, (SELECT obra_social FROM appointments WHERE patient_name = '$patient' ORDER BY appointment_date DESC LIMIT 1) as obra_social FROM patients WHERE name = '$patient' LIMIT 1";
$result = $conn->query($sql);
if ($row = $result->fetch_assoc()) {
    echo json_encode(['history' => $row['history'], 'data' => $row['data'], 'obra_social' => $row['obra_social']]);
} else {
    echo json_encode(['history' => '', 'data' => '', 'obra_social' => '']);
}
$conn->close();
?>
