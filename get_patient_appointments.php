<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "consultorio_db");
if ($conn->connect_error) {
    echo json_encode([]);
    exit();
}
$patient = isset($_GET['patient']) ? $conn->real_escape_string($_GET['patient']) : '';
$turnos = [];
if ($patient !== '') {
    $sql = "SELECT appointment_date, appointment_time, obra_social FROM appointments WHERE patient_name = '$patient' ORDER BY appointment_date, appointment_time";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $turnos[] = $row;
    }
}
echo json_encode($turnos);
$conn->close();
?>
