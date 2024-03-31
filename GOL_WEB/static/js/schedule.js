document.addEventListener('DOMContentLoaded', function() {
    loadSchedule();
});

function loadSchedule() {
    // Тут отправляем запрос на сервер для получения расписания
    fetch('../Game/schedule_data.php') // Поменяйте на актуальный путь к вашему PHP скрипту
        .then(response => response.json())
        .then(data => {
            const scheduleContainer = document.getElementById('subjectsContainer');
            data.options.forEach(option => {
                const daySchedule = document.createElement('div');
                const label = document.createElement('label');
                label.textContent = option.option_text;
                const input = document.createElement('input');
                input.type = 'number';
                input.name = `time_${option.id}`;
                input.required = true;

                daySchedule.appendChild(label);
                daySchedule.appendChild(input);
                scheduleContainer.appendChild(daySchedule);
            });
        })
        .catch(error => console.error('Error:', error));
}

function submitSchedule() {
    const form = document.getElementById('scheduleForm');
    const formData = new FormData(form);

    fetch('process_schedule.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        alert(`Вы заработали ${data.totalEarnings} рублей за учебную неделю.`);
    })
    .catch(error => console.error('Error:', error));
}

document.getElementById('scheduleForm').addEventListener('submit', function(event) {
    event.preventDefault();
    submitSchedule();
});
