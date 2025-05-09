<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultorio - Gestión de Turnos</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <!-- Horarios de turnos -->
        <div class="schedule">
            <h2>Horario de Turnos - <?php echo date('Y-m-d'); ?></h2>
            <table>
                <thead>
                    <tr>
                        <th>Hora</th>
                        <th>Paciente</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Conexión a la base de datos
                    $conn = new mysqli("localhost", "root", "", "consultorio_db");
                    if ($conn->connect_error) {
                        die("Conexión fallida: " . $conn->connect_error);
                    }

                    // Horarios de ejemplo (8:00 a 17:00, cada 30 minutos)
                    $times = ["08:00", "08:30", "09:00", "09:30", "10:00", "10:30", "11:00", "11:30", "12:00", "12:30", "13:00", "13:30", "14:00", "14:30", "15:00", "15:30", "16:00", "16:30", "17:00"];
                    $today = date('Y-m-d');

                    foreach ($times as $time) {
                        // Verificar si hay un turno registrado
                        $sql = "SELECT patient_name FROM appointments WHERE appointment_date = '$today' AND appointment_time = '$time'";
                        $result = $conn->query($sql);
                        $patient = $result->num_rows > 0 ? $result->fetch_assoc()['patient_name'] : '';

                        echo "<tr>";
                        echo "<td>$time</td>";
                        echo "<td>" . ($patient ? $patient : "Libre") . "</td>";
                        echo "<td>";
                        if (!$patient) {
                            echo "<button onclick=\"openForm('$time')\">Registrar</button>";
                        } else {
                            echo "Ocupado";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Calendario -->
        <div class="calendar">
            <h2>Calendario</h2>
            <div id="calendar"></div>
        </div>
    </div>

    <!-- Formulario para registrar paciente -->
    <div id="appointmentForm" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeForm()">&times;</span>
            <h2>Registrar Paciente</h2>
            <form action="save_appointment.php" method="POST">
                <input type="hidden" id="appointment_time" name="appointment_time">
                <input type="hidden" name="appointment_date" value="<?php echo date('Y-m-d'); ?>">
                <label for="patient_name">Nombre del Paciente:</label>
                <input type="text" id="patient_name" name="patient_name" required>
                <button type="submit">Guardar</button>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>