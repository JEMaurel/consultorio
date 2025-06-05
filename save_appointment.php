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

    // Verificar si ya existe un turno para esa fecha y hora
    $check = $conn->query("SELECT COUNT(*) as c FROM appointments WHERE appointment_date = '$date' AND appointment_time = '$time'");
    $row = $check->fetch_assoc();
    if ($row['c'] > 0) {
        $conn->close();
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || !empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            echo json_encode(['success' => false, 'error' => 'Ya existe un turno para ese horario.']);
            exit;
        }
        header("Location: index.php?error=duplicado");
        exit;
    }

    // Eliminar turno existente para ese horario y fecha antes de insertar (permite editar)
    $conn->query("DELETE FROM appointments WHERE appointment_date = '$date' AND appointment_time = '$time'");

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
    // Si es petición AJAX, no redirigir
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || !empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        echo json_encode(['success' => true]);
        exit;
    }
    header("Location: index.php");
}
?>
