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
                                <button onclick="selectDate('${dateStr}')">${dateCounter}</button>
                            </td>`;
                dateCounter++;
            }
        }
        rowHtml += '</tr>';
        calendarBody += rowHtml;
        if (dateCounter > totalDays) break;
    }

    document.getElementById("calendarBody").innerHTML = calendarBody;
}

function prevMonth() {
    currentDate.setMonth(currentDate.getMonth() - 1);
    generateCalendar(currentDate);
}

function nextMonth() {
    currentDate.setMonth(currentDate.getMonth() + 1);
    generateCalendar(currentDate);
}

function selectDate(date) {
    document.getElementById("selectedDateDisplay").textContent = date;
    loadSchedule(date);
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
                row.innerHTML = `
                    <td>${slot.time}</td>
                    <td>${slot.patient ? slot.patient : "Libre"}</td>
                    <td>${slot.patient ? "Ocupado" : `<button onclick="openForm('${slot.time}', '${date}')">Registrar</button>`}</td>
                `;
                tbody.appendChild(row);
            });
        });
}

function openForm(time, date) {
    document.getElementById("appointment_time").value = time;
    document.getElementById("appointment_date").value = date;
    document.getElementById("appointmentForm").style.display = "block";
}

function closeForm() {
    document.getElementById("appointmentForm").style.display = "none";
}

function toggleDaysSelector() {
    const selector = document.getElementById("daysSelector");
    selector.style.display = selector.style.display === "none" ? "block" : "none";
}
