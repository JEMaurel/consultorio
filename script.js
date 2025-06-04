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
            afterScheduleRender();
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
        e.preventDefault();
        const formData = new FormData(appointmentForm);
        fetch('save_appointment.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(() => {
            closeForm();
            // Esperar un poco para asegurar que la BD se actualizó
            setTimeout(() => {
                const date = document.getElementById('appointment_date').value;
                loadSchedule(date);
            }, 200);
        });
    });
}

function toggleDaysSelector() {
    const selector = document.getElementById("daysSelector");
    selector.style.display = selector.style.display === "none" ? "block" : "none";
}

// Autocompletado y edición de paciente
function setupPatientAutocomplete() {
    document.querySelectorAll('.patient-cell').forEach(cell => {
        cell.style.cursor = 'pointer';
        cell.addEventListener('click', function(e) {
            const currentName = this.textContent;
            showPatientSearch(this, currentName);
        });
    });
}

function showPatientSearch(cell, currentName) {
    // Eliminar cualquier otro desplegable
    document.querySelectorAll('.autocomplete-dropdown').forEach(el => el.remove());
    const dropdown = document.createElement('div');
    dropdown.className = 'autocomplete-dropdown';
    dropdown.style.position = 'absolute';
    dropdown.style.background = '#fff';
    dropdown.style.border = '1px solid #ccc';
    dropdown.style.zIndex = 1001;
    dropdown.style.width = cell.offsetWidth + 'px';
    dropdown.style.maxHeight = '200px';
    dropdown.style.overflowY = 'auto';
    // Posicionar debajo de la celda
    const rect = cell.getBoundingClientRect();
    dropdown.style.left = rect.left + window.scrollX + 'px';
    dropdown.style.top = rect.bottom + window.scrollY + 'px';
    // Input de búsqueda
    const input = document.createElement('input');
    input.type = 'text';
    input.value = currentName;
    input.style.width = '95%';
    input.style.margin = '5px';
    dropdown.appendChild(input);
    // Contenedor de sugerencias
    const suggestionsDiv = document.createElement('div');
    dropdown.appendChild(suggestionsDiv);
    document.body.appendChild(dropdown);
    input.focus();
    // Buscar sugerencias
    input.addEventListener('input', function() {
        fetch('autocomplete_patient.php?q=' + encodeURIComponent(input.value))
            .then(r => r.json())
            .then(suggestions => {
                suggestionsDiv.innerHTML = '';
                suggestions.forEach(name => {
                    const opt = document.createElement('div');
                    opt.textContent = name;
                    opt.className = 'autocomplete-option';
                    opt.style.padding = '4px 8px';
                    opt.style.cursor = 'pointer';
                    opt.addEventListener('click', function() {
                        showPatientDataForm(name, dropdown);
                    });
                    suggestionsDiv.appendChild(opt);
                });
            });
    });
    // Trigger búsqueda inicial
    input.dispatchEvent(new Event('input'));
    // Si se hace click fuera, cerrar
    document.addEventListener('mousedown', function handler(ev) {
        if (!dropdown.contains(ev.target)) {
            dropdown.remove();
            document.querySelectorAll('.patient-data-form').forEach(el => el.remove());
            document.removeEventListener('mousedown', handler);
        }
    });
    // Si se quiere editar el actual
    const editBtn = document.createElement('button');
    editBtn.textContent = 'Ver/Editar datos';
    editBtn.style.margin = '5px';
    editBtn.onclick = function(ev) {
        ev.stopPropagation();
        showPatientDataForm(currentName, dropdown);
    };
    dropdown.appendChild(editBtn);
}

function showPatientDataForm(name, parentDropdown) {
    // Eliminar cualquier otro formulario
    document.querySelectorAll('.patient-data-form').forEach(el => el.remove());
    // Obtener datos
    fetch('get_patient_data.php?patient=' + encodeURIComponent(name))
        .then(r => r.json())
        .then(data => {
            const form = document.createElement('div');
            form.className = 'patient-data-form';
            form.style.background = '#f5ecd7';
            form.style.border = '1px solid #aaa';
            form.style.padding = '12px';
            form.style.marginTop = '5px';
            form.style.position = 'fixed';
            // Calcular posición relativa a la ventana
            let left = 40, top = 40;
            if (parentDropdown && parentDropdown.getBoundingClientRect) {
                const parentRect = parentDropdown.getBoundingClientRect();
                left = Math.max(10, Math.min(window.innerWidth - 350, parentRect.left));
                top = Math.max(10, Math.min(window.innerHeight - 250, parentRect.bottom + 5));
            }
            form.style.left = left + 'px';
            form.style.top = top + 'px';
            form.style.zIndex = 1002;
            form.innerHTML = `
                <strong>Paciente:</strong> <span>${name}</span><br>
                <label>Historia clínica:<br><textarea rows="4" style="width:98%">${data.history || ''}</textarea></label><br>
                <label>Datos adicionales:<br><textarea rows="2" style="width:98%">${data.data || ''}</textarea></label><br>
                <button>Guardar</button>
                <button type="button" class="close-patient-form">Cerrar</button>
            `;
            document.body.appendChild(form);
            // Guardar
            form.querySelector('button').onclick = function(ev) {
                ev.preventDefault();
                const history = form.querySelectorAll('textarea')[0].value;
                const other = form.querySelectorAll('textarea')[1].value;
                fetch('save_patient_data.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({name, history, data: other})
                })
                .then(r => r.json())
                .then(resp => {
                    if (resp.success) {
                        alert('Datos guardados');
                        form.remove();
                        if(parentDropdown) parentDropdown.remove();
                    } else {
                        alert('Error al guardar');
                    }
                });
            };
            // Cerrar
            form.querySelector('.close-patient-form').onclick = function() {
                form.remove();
            };
        });
}

// Llamar después de renderizar la grilla
function afterScheduleRender() {
    setupPatientAutocomplete();
}