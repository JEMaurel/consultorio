<?php
// Este script crea la tabla de pacientes si no existe
$mysqli = new mysqli("localhost", "root", "", "consultorio_db");
$mysqli->query("
CREATE TABLE IF NOT EXISTS patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE,
    history TEXT,
    data TEXT
) CHARACTER SET utf8mb4;
");
echo "Tabla 'patients' lista.";
?>
