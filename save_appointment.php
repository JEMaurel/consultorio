<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli("localhost", "root", "", "consultorio_db");
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    $date = $_POST['appointment_date'];
    $time = $_POST['appointment_time'];
    $patient = $_POST['patient_name'];
    $obra = $_POST['obra_social'];
    $extra_days = isset($_POST['extra_days']) ? $_POST['extra_days'] : [];

    // Turno principal
    $conn->query("INSERT INTO appointments (appointment_date, appointment_time, patient_name, obra_social) VALUES ('$date', '$time', '$patient', '$obra')");

    // Turnos en días adicionales seleccionados
    $base_date = strtotime($date);
    foreach ($extra_days as $day_name) {
        $target = strtotime("next $day_name", $base_date - 86400);
        $target_date = date('Y-m-d', $target);
        $conn->query("INSERT INTO appointments (appointment_date, appointment_time, patient_name, obra_social) VALUES ('$target_date', '$time', '$patient', '$obra')");
    }

    $conn->close();
    header("Location: index.php");
}
?>
