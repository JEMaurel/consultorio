<?php
// Utilidad para generar el respaldo de turnos por día
function generar_respaldo_turnos($conn) {
    $hoy = date('Y-m-d');
    $mes_atras = date('Y-m-d', strtotime('-1 month', strtotime($hoy)));
    $mes_adelante = date('Y-m-d', strtotime('+1 month', strtotime($hoy)));
    $sql = "SELECT appointment_date, appointment_time, patient_name FROM appointments WHERE appointment_date >= '$mes_atras' AND appointment_date <= '$mes_adelante' ORDER BY appointment_date, appointment_time, patient_name";
    $result = $conn->query($sql);
    $turnos = [];
    while ($row = $result->fetch_assoc()) {
        $fecha = $row['appointment_date'];
        if (!isset($turnos[$fecha])) $turnos[$fecha] = [];
        $turnos[$fecha][] = [
            'hora' => $row['appointment_time'],
            'nombre' => $row['patient_name']
        ];
    }
    $txt = "";
    foreach ($turnos as $fecha => $lista) {
        $txt .= "$fecha\n";
        foreach ($lista as $t) {
            $txt .= "  {$t['hora']} - {$t['nombre']}\n";
        }
    }
    file_put_contents(__DIR__ . '/bacap/respaldo_turnos_por_dia.txt', $txt);
}

// Utilidad para guardar historia clínica en archivo
function guardar_historia_clinica($nombre, $historia) {
    $dir = __DIR__ . '/bacap/historias/';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    $nombre_archivo = $dir . 'historia_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $nombre) . '.txt';
    file_put_contents($nombre_archivo, $historia);
}
