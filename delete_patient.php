<?php
// delete_patient.php
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['name']) || trim($data['name']) === '') {
    echo json_encode(['success' => false, 'error' => 'Nombre de paciente requerido']);
    exit;
}
$name = trim($data['name']);

// --- Eliminar de la base de datos ---
$conn = new mysqli("localhost", "root", "", "consultorio_db");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Conexión fallida']);
    exit;
}
// Eliminar de patients
$stmt = $conn->prepare('DELETE FROM patients WHERE name = ?');
$stmt->bind_param('s', $name);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();
// Eliminar turnos asociados en appointments
$stmt2 = $conn->prepare('DELETE FROM appointments WHERE patient_name = ?');
$stmt2->bind_param('s', $name);
$stmt2->execute();
$stmt2->close();
$conn->close();

if ($affected > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'No se encontró el paciente']);
}
