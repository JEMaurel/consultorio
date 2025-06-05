<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "consultorio_db");
if ($conn->connect_error) {
    die(json_encode(['error' => 'Conexión fallida']));
}

$date = $_GET['date'] ?? date('Y-m-d');
$times = ["08:00", "08:30", "09:00", "09:30", "10:00", "10:30", "11:00", "11:30",
          "12:00", "12:30", "13:00", "13:30", "14:00", "14:30", "15:00", "15:30", "16:00", "16:30", "17:00"];

// Agregar horas existentes en la base de datos para ese día
$extra_times = [];
$res = $conn->query("SELECT appointment_time FROM appointments WHERE appointment_date = '$date'");
while ($row = $res->fetch_assoc()) {
    if (!in_array($row['appointment_time'], $times)) {
        $extra_times[] = $row['appointment_time'];
    }
}
$all_times = array_unique(array_merge($times, $extra_times));
// Ordenar por hora
usort($all_times, function($a, $b) { return strtotime($a) - strtotime($b); });

// Normalizar horas para evitar duplicados visuales (ej: 08:30 y 08:30:00)
function normalize_time($t) {
    $parts = explode(':', $t);
    return count($parts) >= 2 ? sprintf('%02d:%02d', $parts[0], $parts[1]) : $t;
}
$normalized_times = [];
foreach ($all_times as $time) {
    $norm = normalize_time($time);
    if (!in_array($norm, $normalized_times)) {
        $normalized_times[] = $norm;
    }
}

$schedule = [];
foreach ($normalized_times as $time) {
    $sql = "SELECT patient_name FROM appointments WHERE appointment_date = '$date' AND (appointment_time = '$time' OR appointment_time = '$time:00')";
    $result = $conn->query($sql);
    $patient = $result->num_rows > 0 ? $result->fetch_assoc()['patient_name'] : '';
    $schedule[] = ['time' => $time, 'patient' => $patient];
}

echo json_encode($schedule);
$conn->close();
?>
