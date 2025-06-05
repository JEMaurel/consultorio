<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "consultorio_db");
if ($conn->connect_error) {
    echo json_encode([]);
    exit();
}
$patient = isset($_GET['patient']) ? $conn->real_escape_string($_GET['patient']) : '';
$dates = [];
if ($patient !== '') {
    $sql = "SELECT DISTINCT appointment_date FROM appointments WHERE patient_name = '$patient' ORDER BY appointment_date";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $dates[] = $row['appointment_date'];
    }
}
echo json_encode($dates);
$conn->close();
?>
