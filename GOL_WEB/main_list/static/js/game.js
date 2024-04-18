document.addEventListener('DOMContentLoaded', function() {
  // Загрузка данных текущего раунда при первом запуске
  loadRoundData();
});

document.getElementById('next-round').addEventListener('click', function() {
  loadRoundData();
  fetch('../../Game/process_answers.php?action=getBalancePage')
  .then(response => response.json())
  .then(data => {
      if(data.balance) {
          // Обновляем значение баланса на странице
          document.getElementById('earnings').innerHTML =
              `<p>Текущий баланс: ${data.balance} руб.</p>`;
      } else {
          // Обрабатываем ошибку
          console.error('Ошибка: ', data.error);
      }
  })
  .catch(error => {
      console.error('Ошибка при запросе баланса: ', error);
  });
});

// Загружает данные для текущего раунда
function loadRoundData() {
  console.log(sessionStorage.getItem('currentRoundIndex'));
  fetch('../../Game/get_game_data.php?action=roundData') // Путь к вашему PHP скрипту, обрабатывающему данные
    .then(response => response.json())
    .then(data => {
      updateUIForRound(data); // Обновляем интерфейс согласно полученным данным
    })
    .catch(error => console.error('Ошибка при получении данных:', error));
}

// Обновляет интерфейс для отображения текущего раунда
function updateUIForRound(data) {
  console.log(data);
  const questionContainer = document.getElementById('question');
  const form = document.getElementById('answers-form');
  console.log(data.roundId);

  questionContainer.textContent = data.question;
  form.innerHTML = ''; // Очищаем форму от предыдущих элементов

  // Динамически создаем элементы формы для ответов
  data.options.forEach(option => {
    const wrapper = document.createElement('div'); // Создаем блочный элемент для группировки label и input
    const label = document.createElement('label');
    label.textContent = option.option_text;
    const input = document.createElement('input');
    input.type = 'number';
    input.name = `option_${option.id}`;
    input.required = true;

    wrapper.appendChild(label);
    wrapper.appendChild(input);
    form.appendChild(wrapper); // Добавляем wrapper вместо отдельных label и input
  });

  // Кнопка отправки ответов
  const submitButton = document.createElement('button');
  submitButton.textContent = 'Отправить ответы';
  form.appendChild(submitButton);

  // Обработчик отправки формы
  form.onsubmit = function(event) {
    event.preventDefault(); // Предотвращаем стандартную отправку формы
    handleSubmit(form, data.roundId); // Обрабатываем отправку формы
  };
}


function handleSubmit(form, roundId) {
  const formData = new FormData(form);
  formData.append('roundId', roundId); // Добавляем ID раунда для серверной обработки

  console.log("FormData:", Object.fromEntries(formData.entries()));

  fetch('../../Game/process_answers.php?action=answerProcessing', { // Серверный скрипт для обработки ответов
    method: 'POST',
    body: formData
  })
  .then(response => {
    if (!response.ok) {
      throw new Error('Сетевой ответ не ok');
    }
    return response.json();
  })
  .then(data => {
    if (data.error) {
      // Показываем сообщение об ошибке
      alert('Ошибка: ' + data.error);
    } else {
      // Показываем заработок за раунд
      alert('Заработок за раунд: ' + data.totalEarnings + ' руб.');
    }
  })
  .catch(error => {
    // Ловим ошибки в сетевом запросе или ошибки парсинга JSON
    console.error('Ошибка при отправке ответов:', error);
    alert('Произошла ошибка при отправке данных. Проверьте консоль для деталей.');
  });
}






table_headings.forEach((head, i) => {
    let sort_asc = true;
    head.onclick = () => {
        table_headings.forEach(head => head.classList.remove('active'));
        head.classList.add('active');

        document.querySelectorAll('td').forEach(td => td.classList.remove('active'));
        table_rows.forEach(row => {
            row.querySelectorAll('td')[i].classList.add('active');
        })

        head.classList.toggle('asc', sort_asc);
        sort_asc = head.classList.contains('asc') ? false : true;

        sortTable(i, sort_asc);
    }
})


function sortTable(column, sort_asc) {
    [...table_rows].sort((a, b) => {
        let first_row = a.querySelectorAll('td')[column].textContent.toLowerCase(),
            second_row = b.querySelectorAll('td')[column].textContent.toLowerCase();

        return sort_asc ? (first_row < second_row ? 1 : -1) : (first_row < second_row ? -1 : 1);
    })
        .map(sorted_row => document.querySelector('tbody').appendChild(sorted_row));
}





