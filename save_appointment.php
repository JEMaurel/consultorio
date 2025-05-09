<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli("localhost", "root", "", "consultorio_db");
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    $date = $_POST['appointment_date'];
    $time = $_POST['appointment_time'];
    $patient = $_POST['patient_name'];

    $sql = "INSERT INTO appointments (appointment_date, appointment_time, patient_name) VALUES ('$date', '$time', '$patient')";
    if ($conn->query($sql) === TRUE) {
        header("Location: index.php");
    } else {
        echo "Error: " . $conn->error;
    }

    $conn->close();
}
?>