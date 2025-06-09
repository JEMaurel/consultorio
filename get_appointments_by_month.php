<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "consultorio_db");
if ($conn->connect_error) {
    echo json_encode([]);
    exit();
}
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$month = str_pad($month, 2, '0', STR_PAD_LEFT);
$appointments = [];
$sql = "SELECT appointment_date, appointment_time, patient_name, obra_social FROM appointments WHERE YEAR(appointment_date) = '$year' AND MONTH(appointment_date) = '$month' ORDER BY appointment_date, appointment_time";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $date = $row['appointment_date'];
    if (!isset($appointments[$date])) $appointments[$date] = [];
    $appointments[$date][] = $row;
}
echo json_encode($appointments);
$conn->close();
?>
