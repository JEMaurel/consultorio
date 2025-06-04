let currentDate = new Date();

function generateCalendar(date = new Date()) {
    const month = date.getMonth();
    const year = date.getFullYear();

    const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    document.getElementById('monthTitle').textContent = `${monthNames[month]} ${year}`;

    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const todayStr = new Date().toISOString().split('T')[0];

    let calendarBody = '';
    let dateCounter = 1;

    let startDay = (firstDay.getDay() + 6) % 7;
    const totalDays = lastDay.getDate();

    for (let row = 0; row < 6; row++) {
        let rowHtml = '<tr>';
        for (let col = 0; col < 7; col++) {
            if ((row === 0 && col < startDay) || dateCounter > totalDays) {
                rowHtml += '<td></td>';
            } else {
                const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(dateCounter).padStart(2, '0')}`;
                const isToday = dateStr === todayStr;
                rowHtml += `<td class="${isToday ? 'today' : ''}">
                                <button class="calendar-day-btn" onclick="selectDate('${dateStr}', this)">${dateCounter}</button>
                            </td>`;
                dateCounter++;
            }
        }
        rowHtml += '</tr>';
        calendarBody += rowHtml;
        if (dateCounter > totalDays) break;
    }

    document.getElementById("calendarBody").innerHTML = calendarBody;

    // Selección visual del día en el calendario
    document.querySelectorAll("#calendarBody .calendar-day-btn").forEach(btn => {
        btn.addEventListener("click", function() {
            document.querySelectorAll("#calendarBody .calendar-day-btn.selected").forEach(sel => sel.classList.remove("selected"));
            this.classList.add("selected");
        });
    });
}

function prevMonth() {
    currentDate.setMonth(currentDate.getMonth() - 1);
    generateCalendar(currentDate);
}

function nextMonth() {
    currentDate.setMonth(currentDate.getMonth() + 1);
    generateCalendar(currentDate);
}

function selectDate(date, btn = null) {
    document.getElementById("selectedDateDisplay").textContent = date;
    loadSchedule(date);

    // Resalta el botón seleccionado si se llama desde el onclick
    if (btn) {
        document.querySelectorAll("#calendarBody .calendar-day-btn.selected").forEach(sel => sel.classList.remove("selected"));
        btn.classList.add("selected");
    }
}

function loadSchedule(date) {
    fetch("load_schedule.php?date=" + date)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById("scheduleBody");
            const scheduleDiv = document.getElementById("schedule");

            tbody.innerHTML = "";
            scheduleDiv.style.display = "block";

            data.forEach(slot => {
                const row = document.createElement("tr");
                if (slot.patient) {
                    row.innerHTML = `
                        <td>${slot.time}</td>
                        <td class="patient-cell">${slot.patient}</td>
                        <td>
                            <button class="edit-btn ocupado-btn" onclick="editAppointment('${slot.time}', '${date}')">Ocupado</button>
                        </td>
                    `;
                } else {
                    row.innerHTML = `
                        <td>${slot.time}</td>
                        <td>Libre</td>
                        <td><button onclick="openForm('${slot.time}', '${date}')">Registrar</button></td>
                    `;
                }
                tbody.appendChild(row);
            });
        });
}

function openForm(time, date) {
    document.getElementById("appointment_time").value = time;
    document.getElementById("appointment_date").value = date;
    document.getElementById("appointmentForm").style.display = "block";
}

function editAppointment(time, date) {
    // Cargar datos del turno para edición
    fetch(`get_appointment.php?date=${date}&time=${time}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById("appointment_time").value = time;
            document.getElementById("appointment_date").value = date;
            document.getElementById("patient_name").value = data.patient_name;
            document.getElementById("obra_social").value = data.obra_social;
            document.getElementById("appointmentForm").style.display = "block";
            // Mostrar botón para eliminar
            let delBtn = document.getElementById('delete-appointment-btn');
            if (!delBtn) {
                delBtn = document.createElement('button');
                delBtn.id = 'delete-appointment-btn';
                delBtn.type = 'button';
                delBtn.textContent = 'Eliminar turno';
                delBtn.style.backgroundColor = '#e53935';
                delBtn.style.marginTop = '10px';
                delBtn.style.color = '#fff';
                delBtn.onclick = function() { deleteAppointment(time, date); };
                document.querySelector('#appointmentForm .modal-content form').appendChild(delBtn);
            } else {
                delBtn.onclick = function() { deleteAppointment(time, date); };
                delBtn.style.display = 'block';
            }
        });
}

function deleteAppointment(time, date) {
    if (!confirm('¿Seguro que desea eliminar este turno?')) return;
    fetch('delete_appointment.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({appointment_time: time, appointment_date: date})
    })
    .then(response => response.json())
    .then(data => {
        closeForm();
        // Recargar la grilla
        loadSchedule(date);
    });
}

function closeForm() {
    document.getElementById("appointmentForm").style.display = "none";
    // Ocultar botón eliminar si existe
    let delBtn = document.getElementById('delete-appointment-btn');
    if (delBtn) delBtn.style.display = 'none';
    // Limpiar campos del formulario
    document.getElementById("patient_name").value = '';
    document.getElementById("obra_social").value = '';
}

// Actualizar nombre del paciente en la tabla al guardar
const appointmentForm = document.querySelector('#appointmentForm form');
if (appointmentForm) {
    appointmentForm.addEventListener('submit', function(e) {
        setTimeout(() => {
            // Espera a que el backend procese y recarga la grilla
            const date = document.getElementById('appointment_date').value;
            loadSchedule(date);
        }, 300);
    });
}

function toggleDaysSelector() {
    const selector = document.getElementById("daysSelector");
    selector.style.display = selector.style.display === "none" ? "block" : "none";
}