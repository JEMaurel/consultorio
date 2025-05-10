function generateCalendar() {
    const calendar = document.getElementById("calendar");
    const today = new Date();

    for (let i = 0; i < 30; i++) {
        const day = new Date(today);
        day.setDate(today.getDate() + i);
        const dateStr = day.toISOString().split("T")[0];

        const btn = document.createElement("button");
        btn.textContent = dateStr;
        btn.onclick = () => loadSchedule(dateStr);
        calendar.appendChild(btn);
    }
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

function loadSchedule(date) {
    fetch("load_schedule.php?date=" + date)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById("scheduleBody");
            const displayDate = document.getElementById("selectedDateDisplay");
            const scheduleDiv = document.getElementById("schedule");

            tbody.innerHTML = "";
            displayDate.textContent = date;
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
