<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultorio - Gestión de Turnos</title>
    <link rel="stylesheet" href="style.css">
</head>
<!-- Chat con IA -->
<div id="chat-ia" style="position: fixed; bottom: 20px; right: 70px; width: 300px; background: white; border: 1.5px solid #ffa726; padding: 0; transition: height 0.3s, min-height 0.3s, width 0.3s, box-shadow 0.3s, right 0.3s; min-height: 40px; z-index: 1000; overflow: hidden; border-radius: 18px; box-shadow: 0 4px 24px #b8b8b840;">
    <div id="ia-header" style="display: flex; justify-content: space-between; align-items: center; cursor: pointer; background: linear-gradient(90deg,#ffa726 60%,#ffd180 100%); border-radius: 18px 18px 0 0; padding: 8px 14px;">
        <h4 id="ia-title" style="margin: 0; color: #fff; font-weight: 700; font-size: 1.1em; letter-spacing: 0.04em;">IA</h4>
        <button id="toggle-ia" style="background: none; border: none; font-size: 20px; color: #fff; cursor: pointer; font-weight: bold;">&#x2212;</button>
    </div>
    <div id="ia-content" style="padding: 10px 12px 12px 12px; background: #fffbe9; border-radius: 0 0 18px 18px;">
        <div id="respuesta-ia" style="height: 200px; overflow-y: scroll; border: 1px solid #ffe0b2; background: #fff; padding: 7px; margin: 10px 0; border-radius: 10px; font-size: 0.98em;"></div>
        <div style="display: flex; gap: 6px; align-items: center;">
            <input type="text" id="pregunta-ia" placeholder="Pregunta algo..." style="flex:1; padding: 8px 10px; border-radius: 8px; border: 1.5px solid #ffa726; font-size: 1em; background: #fff9f2;">
            <button onclick="consultarIA()" style="padding: 8px 16px; border-radius: 8px; background: #ffa726; color: #fff; font-weight: bold; border: none; font-size: 1em; box-shadow: 0 2px 8px #e0c9a650; transition: background 0.2s;">Enviar</button>
        </div>
    </div>
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
// Minimizar/expandir panel IA
const iaPanel = document.getElementById('chat-ia');
const iaContent = document.getElementById('ia-content');
const toggleBtn = document.getElementById('toggle-ia');
const iaTitle = document.getElementById('ia-title');
let iaMinimized = false;
function setIAMinimized(minimized) {
    iaMinimized = minimized;
    if (iaMinimized) {
        iaContent.style.display = 'none';
        toggleBtn.style.display = 'none';
        iaPanel.style.width = '60px';
        iaPanel.style.minHeight = '36px';
        iaPanel.style.height = '36px';
        iaTitle.textContent = 'IA';
        iaPanel.style.cursor = 'pointer';
        iaPanel.style.background = 'linear-gradient(90deg,#ffa726 60%,#ffd180 100%)';
        iaPanel.style.boxShadow = '0 2px 8px #e0c9a650';
        iaPanel.style.borderRadius = '18px';
        iaTitle.style.color = '#fff';
        iaTitle.style.fontWeight = '700';
        iaTitle.style.fontSize = '1.1em';
        iaTitle.style.letterSpacing = '0.04em';
        iaHeader.style.justifyContent = 'center';
    } else {
        iaContent.style.display = 'block';
        toggleBtn.style.display = 'inline-block';
        iaPanel.style.width = '300px';
        iaPanel.style.minHeight = '';
        iaPanel.style.height = '';
        iaTitle.textContent = 'Consulta IA';
        iaPanel.style.cursor = '';
        iaPanel.style.background = '#fff';
        iaPanel.style.boxShadow = '0 4px 24px #b8b8b840';
        iaPanel.style.borderRadius = '18px';
        iaTitle.style.color = '#ffa726';
        iaTitle.style.fontWeight = '700';
        iaTitle.style.fontSize = '1.1em';
        iaTitle.style.letterSpacing = '0.04em';
        iaHeader.style.justifyContent = 'space-between';
    }
}
const iaHeader = document.getElementById('ia-header');
toggleBtn.addEventListener('click', function(e) {
    e.stopPropagation();
    setIAMinimized(true);
});
iaPanel.addEventListener('click', function(e) {
    if (iaMinimized) {
        setIAMinimized(false);
    }
});
setIAMinimized(true);
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
            <form method="POST">
                <label for="appointment_time">Hora:</label>
                <input type="time" id="appointment_time" name="appointment_time" required>
                <input type="hidden" id="appointment_date" name="appointment_date">
                <label for="patient_name">Nombre del Paciente:</label>
                <input type="text" id="patient_name" name="patient_name" required>
                <label for="obra_social">Obra Social:</label>
                <input type="text" id="obra_social" name="obra_social" placeholder="(opcional)">
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
                <!-- El botón Eliminar se agrega dinámicamente por JS si corresponde -->
            </form>
        </div>
    </div>

    <!-- Botón pequeño 'P' para buscar pacientes -->
<button id="btn-buscar-paciente" title="Buscar paciente" style="position:absolute;top:18px;right:68px;z-index:1200;width:32px;height:32px;border-radius:50%;background:#ffa726;color:#fff;font-weight:bold;font-size:18px;border:none;box-shadow:0 2px 8px #e0c9a650;cursor:pointer;">P</button>
<!-- Buscador de pacientes (oculto por defecto) -->
<div id="buscador-paciente-modal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.25);z-index:1201;align-items:center;justify-content:center;">
  <div style="background:#fff;padding:24px 18px 18px 18px;border-radius:14px;max-width:350px;width:95vw;box-shadow:0 4px 24px #b8b8b840;position:relative;">
    <button id="cerrar-buscador-paciente" style="position:absolute;top:8px;right:8px;background:none;border:none;font-size:20px;color:#e53935;cursor:pointer;">&times;</button>
    <h3 style="margin-top:0;font-size:1.1em;">Buscar paciente</h3>
    <input type="text" id="input-buscar-paciente" placeholder="Nombre del paciente..." style="width:100%;padding:8px 10px;margin-bottom:10px;border-radius:8px;border:1.5px solid #e0c9a6;font-size:1em;">
    <div id="resultados-buscar-paciente" style="max-height:180px;overflow-y:auto;"></div>
    <div id="datos-paciente-buscado" style="margin-top:12px;"></div>
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
