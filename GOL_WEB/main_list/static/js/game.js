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


function updateUIForRound(data) {
  console.log(data);
  const questionContainer = document.getElementById('question');
  const form = document.getElementById('answers-form');
  console.log(data.roundId);

  questionContainer.innerHTML = data.question;

  form.innerHTML = ''; // Очищаем форму от предыдущих элементов
  if (data.type === 'fill_multiple') {
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
  } else if (data.type === 'story') {
    data.options.forEach(option => {
      const wrapper = document.createElement('div');
      if (option.type === 'radio') {
        const input = document.createElement('input');
        const label = document.createElement('label');

        input.type = 'radio';
        input.name = 'selected_business';
        input.value = `option_${option.id}`;
        input.required = true;

        label.textContent = option.option_text;
        label.appendChild(input);

        wrapper.appendChild(label);
        form.appendChild(wrapper);
      } else if (option.type === 'fill_num') {
        const label = document.createElement('label');
        const input = document.createElement('input');

        label.textContent = option.option_text;
        input.type = 'number';
        input.name = `option_${option.id}`;
        input.required = true;

        wrapper.appendChild(label);
        wrapper.appendChild(input);
        form.appendChild(wrapper);
      } // Добавляем wrapper вместо отдельных label и input
    }); 
  }


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
      alert('Ошибка: ' + data.error + data.data);
    } else {
      console.log(data);
      // Показываем заработок за раунд
      alert('Заработок за раунд: ' + data.totalEarnings + ' руб.');
      if (data.endGame) {
        // Тут подсвечиваем кнопку для окончания игры, если раунд оказался последним
        showEndGameButton();
      }
    }
  })
  .catch(error => {
    // Ловим ошибки в сетевом запросе или ошибки парсинга JSON
    console.error('Ошибка при отправке ответов:', error);
    alert('Произошла ошибка при отправке данных. Проверьте консоль для деталей.');
  });
}


function showEndGameButton() {
  const endGameButton = document.getElementById('end-game-button');
  endGameButton.style.display = 'block'; // Сделать кнопку видимой

  // Устанавливаем обработчик события, если он еще не был установлен
  if (!endGameButton.getAttribute('listener')) {
    endGameButton.addEventListener('click', endGame);
    endGameButton.setAttribute('listener', 'true');
  }
}

function endGame() {
  fetch('../../Game/start_game.php?action=endGame', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: 'action=endGame'
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Перенаправляем пользователя на главную страницу
      window.location.href = 'main_list.php';
    } else {
      // Если произошла ошибка, сообщаем о ней пользователю
      alert('Ошибка при завершении игры: ' + data.error);
    }
  })
  .catch(error => {
    console.error('Ошибка:', error);
  });
}

