<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultorio - Gestión de Turnos</title>
    <link rel="stylesheet" href="style.css">
</head>
<!-- Chat con IA -->
<div id="chat-ia" style="position: fixed; bottom: 20px; right: 20px; width: 300px; background: white; border: 1px solid #ccc; padding: 10px;">
    <h4>Consulta IA</h4>
    <div id="respuesta-ia" style="height: 200px; overflow-y: scroll; border: 1px solid #eee; padding: 5px; margin: 10px 0;"></div>
    <input type="text" id="pregunta-ia" placeholder="Pregunta algo..." style="width: 70%;">
    <button onclick="consultarIA()" style="width: 25%;">Enviar</button>
</div>

<script>
async function consultarIA() {
    const pregunta = document.getElementById('pregunta-ia').value;
    if (!pregunta) return;
    
    document.getElementById('respuesta-ia').innerHTML += '<p><strong>Tú:</strong> ' + pregunta + '</p>';
    document.getElementById('pregunta-ia').value = '';
    
    try {
        const response = await fetch('ia_consultorio_smart.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({pregunta: pregunta})
        });
        
        const data = await response.json();
        document.getElementById('respuesta-ia').innerHTML += '<p><strong>IA:</strong> ' + data.respuesta + '</p>';
        document.getElementById('respuesta-ia').scrollTop = document.getElementById('respuesta-ia').scrollHeight;
    } catch (error) {
        document.getElementById('respuesta-ia').innerHTML += '<p><strong>Error:</strong> No se pudo conectar con la IA</p>';
    }
}
// Permitir enviar pregunta con Enter
const inputIA = document.getElementById('pregunta-ia');
if (inputIA) {
    inputIA.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            consultarIA();
        }
    });
}
</script>
<body>
    <div class="container">
        <!-- Calendario -->
        <div class="calendar">
            <h2>Calendario</h2>
            <div class="calendar-header">
    <button onclick="prevMonth()">&#8592;</button>
    <span id="monthTitle"></span>
    <button onclick="nextMonth()">&#8594;</button>
</div>
<table id="calendarTable">
    <thead>
        <tr>
            <th>Lun</th>
            <th>Mar</th>
            <th>Mié</th>
            <th>Jue</th>
            <th>Vie</th>
            <th>Sáb</th>
            <th>Dom</th>
        </tr>
    </thead>
    <tbody id="calendarBody"></tbody>
</table>

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
