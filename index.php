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
        <!-- Calendario -->
        <div class="calendar">
            <h2>Calendario</h2>
            <div id="calendar"></div>
        </div>

        <!-- Horarios de turnos -->
        <div class="schedule" id="schedule">
            <h2>Horario de Turnos - <span id="selectedDateDisplay"><?php echo date('Y-m-d'); ?></span></h2>
            <table>
                <thead>
                    <tr>
                        <th>Hora</th>
                        <th>Paciente</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="scheduleBody">
                    <!-- Se llenará por JS -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Formulario para registrar paciente -->
    <div id="appointmentForm" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeForm()">&times;</span>
            <h2>Registrar Paciente</h2>
            <form action="save_appointment.php" method="POST">
                <input type="hidden" id="appointment_time" name="appointment_time">
                <input type="hidden" id="appointment_date" name="appointment_date">
                <label for="patient_name">Nombre del Paciente:</label>
                <input type="text" id="patient_name" name="patient_name" required>
                <label for="obra_social">Obra Social:</label>
                <input type="text" id="obra_social" name="obra_social" required>
                <label>
                    <input type="checkbox" id="multiple_days" onclick="toggleDaysSelector()">
                    Repetir este turno otros días de esta semana
                </label>
                <div id="daysSelector" style="display: none;">
                    <label><input type="checkbox" name="extra_days[]" value="Monday"> Lunes</label>
                    <label><input type="checkbox" name="extra_days[]" value="Tuesday"> Martes</label>
                    <label><input type="checkbox" name="extra_days[]" value="Wednesday"> Miércoles</label>
                    <label><input type="checkbox" name="extra_days[]" value="Thursday"> Jueves</label>
                    <label><input type="checkbox" name="extra_days[]" value="Friday"> Viernes</label>
                </div>
                <button type="submit">Guardar</button>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const today = new Date().toISOString().split("T")[0];
            loadSchedule(today);
            generateCalendar();
        });
    </script>
</body>
</html>
