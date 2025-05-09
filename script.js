// Generar el calendario
function generateCalendar() {
    const calendar = document.getElementById('calendar');
    const today = new Date();
    const year = today.getFullYear();
    const month = today.getMonth();
    const currentDay = today.getDate();

    // Primer día del mes
    const firstDay = new Date(year, month, 1).getDay();
    // Último día del mes
    const lastDay = new Date(year, month + 1, 0).getDate();

    // Crear tabla del calendario
    let html = '<table><tr><th>Dom</th><th>Lun</th><th>Mar</th><th>Mié</th><th>Jue</th><th>Vie</th><th>Sáb</th></tr><tr>';

    // Rellenar días vacíos antes del primer día
    for (let i = 0; i < firstDay; i++) {
        html += '<td></td>';
    }

    // Rellenar días del mes
    for (let day = 1; day <= lastDay; day++) {
        if ((day + firstDay - 1) % 7 === 0 && day !== 1) {
            html += '</tr><tr>';
        }
        html += `<td${day === currentDay ? ' class="today"' : ''}>${day}</td>`;
    }

    // Rellenar días vacíos al final
    while ((firstDay + lastDay) % 7 !== 0) {
        html += '<td></td>';
        firstDay++;
    }

    html += '</tr></table>';
    calendar.innerHTML = html;

    // Mostrar nombre del mes y año
    const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    calendar.insertAdjacentHTML('afterbegin', `<h3>${monthNames[month]} ${year}</h3>`);
}

// Ejecutar al cargar la página
document.addEventListener('DOMContentLoaded', generateCalendar);

// Funciones para el formulario
function openForm(time) {
    document.getElementById('appointmentForm').style.display = 'block';
    document.getElementById('appointment_time').value = time;
}

function closeForm() {
    document.getElementById('appointmentForm').style.display = 'none';
}