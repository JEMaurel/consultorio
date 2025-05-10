<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "consultorio_db");
if ($conn->connect_error) {
    die(json_encode(['error' => 'Conexión fallida']));
}

$date = $_GET['date'] ?? date('Y-m-d');
$times = ["08:00", "08:30", "09:00", "09:30", "10:00", "10:30", "11:00", "11:30",
          "12:00", "12:30", "13:00", "13:30", "14:00", "14:30", "15:00", "15:30", "16:00", "16:30", "17:00"];

$schedule = [];

foreach ($times as $time) {
    $sql = "SELECT patient_name FROM appointments WHERE appointment_date = '$date' AND appointment_time = '$time'";
    $result = $conn->query($sql);
    $patient = $result->num_rows > 0 ? $result->fetch_assoc()['patient_name'] : '';
    $schedule[] = ['time' => $time, 'patient' => $patient];
}

echo json_encode($schedule);
$conn->close();
?>
