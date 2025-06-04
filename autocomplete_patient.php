<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "consultorio_db");
if ($conn->connect_error) {
    echo json_encode([]);
    exit();
}

// Crear tabla patients si no existe
$table_sql = "CREATE TABLE IF NOT EXISTS patients (\n    name VARCHAR(255) PRIMARY KEY,\n    history TEXT,\n    data TEXT\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$conn->query($table_sql);

$q = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : '';
$sql = "SELECT DISTINCT name FROM patients WHERE name LIKE '%$q%' ORDER BY name ASC LIMIT 10";
$result = $conn->query($sql);
$suggestions = [];
while ($row = $result->fetch_assoc()) {
    $suggestions[] = $row['name'];
}
echo json_encode($suggestions);
$conn->close();
?>
