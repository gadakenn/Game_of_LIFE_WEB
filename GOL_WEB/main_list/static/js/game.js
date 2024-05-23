document.addEventListener('DOMContentLoaded', function() {
  console.log(sessionStorage.getItem('currentRoundIndex'));
  loadRoundData();

  
});



// форматтер для красивого отображения чисел
const formatter = new Intl.NumberFormat('ru-RU', {
  style: 'decimal',        
  minimumFractionDigits: 2, 
  maximumFractionDigits: 2  
});

document.getElementById('next-round').addEventListener('click', function() {
  loadRoundData(true);
  fetch('../../Game/process_answers.php?action=getBalancePage')
  .then(response => response.json())
  .then(data => {
      if(data.balance) {

          // Обновляем значение баланса на странице
          document.getElementById('earnings').innerHTML =
              `<p>Текущий баланс: ${formatter.format(data.balance).replace(',', '.')} руб.</p>`;
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
function loadRoundData(next = false) {
  console.log(sessionStorage.getItem('currentRoundIndex'));
  console.log(next);
  const url = next ? '../../Game/get_game_data.php?action=roundData&next=true' : '../../Game/get_game_data.php?action=roundData';
  fetch(url)
    .then(response => response.json())
    .then(data => {
      updateUIForRound(data);
    })
    .catch(error => console.error('Ошибка при получении данных:', error));
}

// тут скрипт для новостей
let newsSingleAll = document.querySelectorAll(".news-container .news-single");
let currentActive = 0;
let totalNews = newsSingleAll.length;
let duration = 6000;
const removeAllActive = () => {
  newsSingleAll.forEach((n) => {
    n.classList.remove("active");
  });
};

const changeNews = () => {
  if (currentActive >= totalNews - 1) {
    currentActive = 0;
  } else {
    currentActive += 1;
  }

  removeAllActive();
  newsSingleAll[currentActive].classList.add("active");
};

setInterval(changeNews, duration);


function initializeNews() {
  newsSingleAll = document.querySelectorAll(".news-container .news-single");
  currentActive = 0; // Сброс текущей активной новости
  totalNews = newsSingleAll.length; // Обновление общего количества новостей
  removeAllActive();
  newsSingleAll[currentActive].classList.add("active");
}


function showAnalysis(element) {
  var analysisText = element.getAttribute('data-analysis'); // Получаем текст аналитики из атрибута
  var analysisContainer = document.getElementById('analysis-container');
  analysisContainer.innerHTML = analysisText;
  analysisContainer.style.display = 'flex'; // Показываем контейнер
}


function calculateMonthlyPayment(propertyPrice, initialPayment, interestRate) {
  const loanAmount = propertyPrice - initialPayment;  // Основная сумма кредита
  const monthlyInterestRate = interestRate / 12 / 100;  // Месячная процентная ставка
  const numberOfRounds = 12;  // Фиксированное количество раундов для выплаты

  let monthlyPayment;
  if (monthlyInterestRate === 0) {
      monthlyPayment = loanAmount / numberOfRounds;
  } else {
      monthlyPayment = loanAmount * 
          (monthlyInterestRate * Math.pow(1 + monthlyInterestRate, numberOfRounds)) /
          (Math.pow(1 + monthlyInterestRate, numberOfRounds) - 1);
  }

  return monthlyPayment.toFixed(2);  // Округление до двух десятичных знаков
}


function updateUIForRound(data) {
  console.log(data);
  const questionContainer = document.getElementById('question');
  const form = document.getElementById('answers-form');
  const newsContainer = document.querySelector('.news-container');
  const newsFeed = document.querySelector('.news');
  newsContainer.style.display = 'none';
  console.log(data.roundId);

  questionContainer.innerHTML = data.question;

  form.innerHTML = ''; // Очищаем форму от предыдущих элементов
  newsFeed.innerHTML = '';

  const optionsContainer = document.createElement('div');
  optionsContainer.className = 'options-container';

  const inputsContainer = document.createElement('div');
  inputsContainer.className = 'inputs-container';
  form.appendChild(optionsContainer);
  form.appendChild(inputsContainer);

  if (data.type === 'mortgage_rent') {
    data.options.forEach(option => {
      const wrapper = document.createElement('div');
      if (option.type === 'radio') {
        wrapper.classList.add('option-wrapper');
        const label = document.createElement('label');
        label.classList.add('option-label'); // Класс для стилизации, если нужен
    
        const input = document.createElement('input');
        input.type = 'radio';
        input.name = 'selected_business';
        input.value = `option_${option.id}`;
        input.required = true;
    
        // Добавляем радио-кнопку в label
        label.appendChild(input);
    
        // Добавляем HTML содержимое (список) в label после радио-кнопки
        const contentWrapper = document.createElement('span'); // Дополнительный элемент для стилизации содержимого
        contentWrapper.innerHTML = option.option_text;
        label.appendChild(contentWrapper);
    
        wrapper.appendChild(label);
        optionsContainer.appendChild(wrapper);
      } else if (option.type === 'slider') {
        const initialPaymentInput = document.createElement('input');
        initialPaymentInput.type = 'range';
        initialPaymentInput.name = 'option_51';
        initialPaymentInput.min = '0';
        initialPaymentInput.max = '10000000';  // Максимальный первоначальный взнос
        initialPaymentInput.value = '500000';  // Предполагаемое начальное значение
        initialPaymentInput.className = 'slider';
        const initialPaymentLabel = document.createElement('label');
        initialPaymentLabel.innerHTML = '<strong>Первоначальный взнос: <strong>';
        initialPaymentLabel.appendChild(initialPaymentInput);
        initialPaymentLabel.appendChild(document.createElement('span')).textContent = ` ${initialPaymentInput.value}`;
    
        // Обновление текста метки при изменении ползунка
        initialPaymentInput.oninput = function() {
          const propertyPrice = 10000000; // Предположим, что цена недвижимости задана заранее
          const interestRate = 18.3; // Процентная ставка
          const initialPayment = parseInt(this.value); // Текущее значение первоначального взноса
      
          // Обновляем текст метки с первоначальным взносом
          initialPaymentLabel.children[1].textContent = ` ${this.value}`;
      
          // Рассчитываем и отображаем ежемесячный платеж
          const monthlyPayment = calculateMonthlyPayment(propertyPrice, initialPayment, interestRate);
          document.getElementById('monthlyPaymentDisplay').innerHTML = `<strong>Ежемесячный платёж: </strong>${formatter.format(monthlyPayment).replace(',', '.')} руб.`;
      };
      
      // Создаём элемент для отображения ежемесячного платежа
      const monthlyPaymentDisplay = document.createElement('div');
      monthlyPaymentDisplay.id = 'monthlyPaymentDisplay';
      monthlyPaymentDisplay.innerHTML = '<strong>Ежемесячный платёж:</strong> 0 руб.';
      wrapper.appendChild(monthlyPaymentDisplay);
        wrapper.appendChild(initialPaymentInput);
        wrapper.appendChild(initialPaymentLabel);
        inputsContainer.appendChild(wrapper);
      }
    }); 



  } else if (data.type === 'fill_multiple') {
    data.options.forEach(option => {
      const wrapper = document.createElement('div'); // Создаем блочный элемент для группировки label и input
      const label = document.createElement('label');
      label.innerHTML = option.option_text;
      const input = document.createElement('input');
      input.type = 'number';
      input.name = `option_${option.id}`;
      input.required = true;
  
      wrapper.appendChild(label);
      wrapper.appendChild(input);
      inputsContainer.appendChild(wrapper); // Добавляем wrapper вместо отдельных label и input
    });
  } else if (data.type === 'story') {
    data.options.forEach(option => {
      const wrapper = document.createElement('div');
      if (option.type === 'radio') {
        wrapper.classList.add('option-wrapper');
        const label = document.createElement('label');
        label.classList.add('option-label'); // Класс для стилизации, если нужен
    
        const input = document.createElement('input');
        input.type = 'radio';
        input.name = 'selected_business';
        input.value = `option_${option.id}`;
        input.required = true;
    
        // Добавляем радио-кнопку в label
        label.appendChild(input);
    
        // Добавляем HTML содержимое (список) в label после радио-кнопки
        const contentWrapper = document.createElement('span'); // Дополнительный элемент для стилизации содержимого
        contentWrapper.innerHTML = option.option_text;
        label.appendChild(contentWrapper);
    
        wrapper.appendChild(label);
        optionsContainer.appendChild(wrapper);
        
      } else if (option.type === 'fill_num') {
        const label = document.createElement('label');
        const input = document.createElement('input');

        label.innerHTML = option.option_text;
        input.type = 'number';
        input.name = `option_${option.id}`;
        input.required = true;

        wrapper.appendChild(label);
        wrapper.appendChild(input);
        inputsContainer.appendChild(wrapper);
      } else if (option.type === 'text') {
        const label = document.createElement('label');
        label.innerHTML = option.option_text;
        wrapper.appendChild(label);
        form.appendChild(wrapper);
      } else if (option.type === 'hint') {
        const label = document.createElement('label');
        label.innerHTML = option.option_text;
        wrapper.appendChild(label);
        form.appendChild(wrapper);
      } 
    }); 
  } else if (data.type == 'news_round') { // тут делаем вывод для раунда, в котором необходимо анализировать новости
    const newsFeed = document.querySelector('.news');
    newsContainer.style.display = 'flex'; // показываем новостную ленту
    let flag = true;
    data.options.forEach(option => {
      const wrapper = document.createElement('div');
       // Класс для стилизации, если нужен
    
      if (option.type === 'radio') {
        wrapper.classList.add('option-wrapper');
        const label = document.createElement('label');
        label.classList.add('option-label'); // Класс для стилизации, если нужен
    
        const input = document.createElement('input');
        input.type = 'radio';
        input.name = 'selected_business';
        input.value = `option_${option.id}`;
        input.required = true;
    
        // Добавляем радио-кнопку в label
        label.appendChild(input);
    
        // Добавляем HTML содержимое (список) в label после радио-кнопки
        const contentWrapper = document.createElement('span'); // Дополнительный элемент для стилизации содержимого
        contentWrapper.innerHTML = option.option_text;
        label.appendChild(contentWrapper);
    
        wrapper.appendChild(label);
        optionsContainer.appendChild(wrapper);
        
      } else if (option.type === 'fill_num') {
        const label = document.createElement('label');
        const input = document.createElement('input');

        label.innerHTML = option.option_text;
        input.type = 'number';
        input.name = `option_${option.id}`;
        input.required = true;

        wrapper.appendChild(label);
        wrapper.appendChild(input);
        inputsContainer.appendChild(wrapper);
      } else if (option.type === 'news_title') { // добавляем новости
        const newsLink = document.createElement('a');
        newsLink.href = '#';
        newsLink.className = flag ? 'news-single active' : 'news-single';
        newsLink.innerHTML = option.option_text.split('*')[0];
        newsLink.setAttribute('data-analysis', option.option_text.split('*')[1]); // Добавляем аналитику как атрибут
        console.log(option.option_text.split('\n')[1]);
        newsLink.onclick = function() { showAnalysis(this); }; // Добавляем обработчик события клика
        newsFeed.appendChild(newsLink);
        newsFeed.appendChild(document.createTextNode(' '));
        flag = false;
      } else if (option.type === 'text') {
        const label = document.createElement('label');
        label.innerHTML = option.option_text;
        label.className = 'options-title';
        wrapper.appendChild(label);
        optionsContainer.appendChild(wrapper);
      }
    }); 
    initializeNews();
  }


  // Кнопка отправки ответов
  const submitButtonContainer = document.createElement('div');
  submitButtonContainer.className = 'submit-button-container';
  const submitButton = document.createElement('button');
  submitButton.textContent = 'Отправить ответы';
  submitButtonContainer.appendChild(submitButton);
  form.appendChild(submitButtonContainer);


  // Обработчик отправки формы
  form.onsubmit = function(event) {
    event.preventDefault(); // Предотвращаем стандартную отправку формы
    handleSubmit(form, data.roundId); // Обрабатываем отправку формы
  };
}


function handleSubmit(form, roundId) {
  // Обработка ответа игрока
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
      console.log(data);
      // Показываем заработок за раунд
      alert(data.message);
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

