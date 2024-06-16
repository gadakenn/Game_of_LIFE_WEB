


// форматтер для красивого отображения чисел
const formatter = new Intl.NumberFormat('ru-RU', {
  style: 'decimal',        
  minimumFractionDigits: 2, 
  maximumFractionDigits: 2  
});

document.getElementById('next-round').addEventListener('click', function() {
  loadRoundData(true);
  
  // Запрос для обновления баланса
  fetch('../../Game/process_answers.php?action=getBalancePage')
  .then(response => response.json())
  .then(data => {
      if(data.balance) {
          // Обновляем значение баланса на странице
          document.getElementById('earnings').innerHTML =
              `<p>Текущий баланс: ${formatter.format(data.balance).replace(',', '.')} руб.</p>`;
      } else {
          console.error('Ошибка: ', data.error);
      }
  })
  .catch(error => {
      console.error('Ошибка при запросе баланса: ', error);
  });

  document.getElementById('salary-spending').style.display = 'none';
  // Запрос для обновления salary-spending
  fetch('../../Game/process_answers.php?action=getSalarySpending')
  .then(response => response.json())
  .then(data => {
      if((data.salary && data.salary.length > 0) || (data.spending && data.spending.length > 0)) {
          let salarySpendingHTML = '';

          // Формируем HTML для зарплаты
          for (const [source, amount] of Object.entries(data.salary)) {
            salarySpendingHTML += `<p class="salary">${source}: +${formatter.format(amount[0]).replace(',', '.')} руб.</p>`;
          }

        // Формируем HTML для трат
          for (const [source, amount] of Object.entries(data.spending)) {
              salarySpendingHTML += `<p class="spending">${source}: -${formatter.format(amount[0]).replace(',', '.')} руб.</p>`;
          }

          // Обновляем содержимое контейнера
          document.getElementById('salary-spending').style.display = 'initial';
          document.getElementById('salary-spending').innerHTML = salarySpendingHTML;
      } else {
          document.getElementById('salary-spending').style.display = 'none';
          console.log(': ', data.error);
      }
  })
  .catch(error => {
      console.error('Ошибка при запросе данных salary-spending: ', error);
  });
});

function showModal(message) {
	const modal = document.getElementById('customModal');
	const modalMessage = document.getElementById('modalMessage');
	modalMessage.textContent = message;
	window.location.hash = "customModal";
  
	// Закрытие модального окна при клике на "X"
	document.querySelector('.close').addEventListener('click', function() {
	  window.location.hash = "close";
	});
  }
  
  


async function loadRoundData(next = false, gptReload = false) {
	const loadingElement = document.getElementById('loading');
	const questionContainer = document.getElementById('question');
	const inputsContainer = document.getElementById('answers-form');
	const nextRoundButton = document.getElementById('next-round');
	
	// Проверка наличия элемента загрузки и контейнера вопроса
	if (!loadingElement || !questionContainer) {
	  console.error('Необходимые элементы не найдены!');
	  return;
	}
  
	// Показать элемент загрузки и скрыть контейнер вопроса
	loadingElement.style.display = 'flex';
	inputsContainer.style.display = 'none';
	nextRoundButton.style.display = 'none';
	questionContainer.style.display = 'none'; // Скрываем контейнер вопроса во время загрузки
  
	// Создаем элемент gptWrapper
	let gptWrapper = document.querySelector('.gpt-wrapper');
	if (!gptWrapper) {
	  gptWrapper = document.createElement('div');
	  gptWrapper.className = 'gpt-wrapper';
	  document.body.appendChild(gptWrapper); // Добавляем gptWrapper в body
	}
  
	try {
	  let url = next ? '../../Game/get_game_data.php?action=roundData&next=true' : '../../Game/get_game_data.php?action=roundData';
	  url = gptReload ? `../../Game/get_game_data.php?action=gptRoundData` : url;
	  const response = await fetch(url);
	  const data = await response.json();
	  updateUIForRound(data);

	  // Скрыть элемент загрузки и показать обновлённый контейнер вопроса
	  loadingElement.style.display = 'none';
	  inputsContainer.style.display = 'block';
	  questionContainer.style.display = 'block'; // Показываем обновленный контейнер вопроса
	} catch (error) {
	  console.error('Ошибка при получении данных:', error);
	  loadingElement.style.display = 'none'; // Скрыть элемент загрузки в случае ошибки
	  showReloadButton(gptWrapper);
	}
  }
  
  function showReloadButton(container) {
	console.log('Запуск showReloadButton');
	// Проверка, есть ли уже кнопка перезагрузки
	if (document.querySelector('.reload-button')) return;
  
	if (!container) {
	  console.error('Container not found');
	  return;
	}
  
	const reloadButton = document.createElement('button');
	reloadButton.className = 'reload-button';
	reloadButton.innerHTML = '<img src="../static/css/reload-arrow-svgrepo-com.svg" alt="reload" />';
	reloadButton.addEventListener('click', () => {
	  loadRoundData(false, true);
	});
	console.log('Добавление кнопки перезагрузки');
	container.appendChild(reloadButton);
  }

document.addEventListener('DOMContentLoaded', () => {
  // if 
  // document.getElementById('salary-spending').style.display = 'none'; // надо сделать, что если пустой, то не показывать

    window.showAnalysis = function(element) {
      var analysisText = element.getAttribute('data-analysis');
      var analysisContainer = document.getElementById('analysis-container');
      analysisContainer.innerHTML = analysisText;
      analysisContainer.style.display = 'block';
  };

  document.addEventListener('click', function(event) {
      var analysisContainer = document.getElementById('analysis-container');
      var newsLinks = document.querySelectorAll('.news-single');
      if (!analysisContainer.contains(event.target) && !Array.from(newsLinks).some(link => link.contains(event.target))) {
          analysisContainer.style.display = 'none';
      }
  });
  particleground(document.getElementById('particles-foreground'), {
    dotColor: 'rgba(255, 255, 255, 1)',
    lineColor: 'rgba(255, 255, 255, 0.05)',
    minSpeedX: 0.3,
    maxSpeedX: 0.6,
    minSpeedY: 0.3,
    maxSpeedY: 0.6,
    density: 50000, // One particle every n pixels
    curvedLines: false,
    proximity: 250, // How close two dots need to be before they join
    parallaxMultiplier: 10, // Lower the number is more extreme parallax
    particleRadius: 4, // Dot size
  });

  particleground(document.getElementById('particles-background'), {
    dotColor: 'rgba(255, 255, 255, 0.5)',
    lineColor: 'rgba(255, 255, 255, 0.05)',
    minSpeedX: 0.075,
    maxSpeedX: 0.15,
    minSpeedY: 0.075,
    maxSpeedY: 0.15,
    density: 30000, // One particle every n pixels
    curvedLines: false,
    proximity: 20, // How close two dots need to be before they join
    parallaxMultiplier: 20, // Lower the number is more extreme parallax
    particleRadius: 2, // Dot size
  });
  loadRoundData();
});





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
  if (!data || !data.question) {
    questionContainer.innerHTML = "<p>Вопрос отсутствует или произошла ошибка.</p>";
    return;
  }
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
  // Создаем контейнер для подсказок
  const hintContainer = document.createElement('div');
  hintContainer.className = 'hint-container';
  hintContainer.style.display = 'none';
  form.appendChild(hintContainer);
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
          initialPaymentLabel.children[1].textContent = `${formatter.format(this.value).replace(',', '.')} руб.`;
      
          // Рассчитываем и отображаем ежемесячный платеж
          const monthlyPayment = calculateMonthlyPayment(propertyPrice, initialPayment, interestRate);
          document.getElementById('monthlyPaymentDisplay').innerHTML = `<strong>Ежемесячный платёж: </strong>${formatter.format(monthlyPayment).replace(',', '.')} руб.`;
      };
      
      // Создаём элемент для отображения ежемесячного платежа
      const monthlyPaymentDisplay = document.createElement('div');
      monthlyPaymentDisplay.id = 'monthlyPaymentDisplay';
      monthlyPaymentDisplay.innerHTML = '<strong>Ежемесячный платёж:</strong> 0 руб.';
      wrapper.classList.add('input-wrapper');
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
      wrapper.classList.add('input-wrapper');
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
        wrapper.classList.add('input-wrapper');
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
        const wrapper = document.createElement('div');
        wrapper.className = 'hint-wrapper';

        const label = document.createElement('label');
        label.className = 'hint-label';
        label.innerHTML = '<strong>ПОДСКАЗКА</strong>';
        wrapper.appendChild(label);

        const hintText = document.createElement('div');
        hintText.className = 'hint-text';
        hintText.innerHTML = option.option_text;
        wrapper.appendChild(hintText);

        // Добавляем обработчик события клика для показа/скрытия подсказки
        wrapper.addEventListener('click', () => {
          wrapper.classList.toggle('show');
        });

        hintContainer.appendChild(wrapper);
        hintContainer.style.display = 'flex';
      }
    }); 
  } else if (data.type == 'news_round') { // тут делаем вывод для раунда, в котором необходимо анализировать новости
    const newsFeed = document.querySelector('.news');
    newsContainer.style.display = 'flex'; // показываем новостную ленту
    let flag = true;
    let radioGroup;
    let radioGroupCount = 0;
    
    data.options.forEach((option, index) => {
      const wrapper = document.createElement('div');
      // Класс для стилизации, если нужен
    
      if (option.type === 'text') {
        // Создаем новый контейнер для радио-группы
      
        optionsContainer.appendChild(wrapper);

        // Создаем новый контейнер для радио-группы
        radioGroup = document.createElement('div');
        radioGroup.id = `radio-group-${Math.floor(index / 3)}`;
        radioGroup.classList.add('radio-group');
        radioGroupCount = 0;
        const heading = document.createElement('h3');
        heading.innerHTML = option.option_text;
        radioGroup.appendChild(heading);
        optionsContainer.appendChild(radioGroup);
    
      } else if (option.type === 'radio') {
        // Если нет активного radioGroup, создаем новый
      
    
        // Добавляем радио-кнопку в текущий контейнер
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
        if (radioGroup) {
          radioGroup.appendChild(wrapper);
          radioGroupCount++;
          optionsContainer.appendChild(radioGroup);
        } else {
          optionsContainer.appendChild(wrapper);
        }
    
        // Если в текущем контейнере достигнуто 3 радио-кнопки, сбрасываем radioGroup
        if (radioGroupCount >= 3) {
          radioGroup = null;
        }
      } else if (option.type === 'fill_num') {
        const label = document.createElement('label');
        const input = document.createElement('input');
        wrapper.classList.add('input-wrapper');
    
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
      }
    }); 
    initializeNews();
    

  } else if (data.type === 'gpt') {
    const label = document.createElement('label');
    const textarea = document.createElement('textarea');
    const wrapper = document.createElement('div');
    wrapper.classList.add('input-wrapper');

    label.innerHTML = data.message;
    textarea.name = `gpt`;
    textarea.required = true;
    textarea.classList.add('wide-textarea'); // Добавляем класс wide-textarea
    inputsContainer.label = 'Введите ответ в свободной форме.';
    wrapper.appendChild(label);
    wrapper.appendChild(textarea);
    inputsContainer.appendChild(wrapper);
    
  } else if (data.type === 'player_info') {
    data.options.forEach(option => {
      const wrapper = document.createElement('div');
       // Класс для стилизации, если нужен
    
       if (option.type === 'radio') {
        // Создаем контейнер для группы радио-кнопок
        let radioGroup = document.getElementById('sex-radio-group');
        if (!radioGroup) {
            radioGroup = document.createElement('div');
            radioGroup.id = 'sex-radio-group';
            radioGroup.classList.add('radio-group');
    
            // Создаем заголовок для радио-кнопок
            const heading = document.createElement('h3');
            heading.innerText = 'Пол';
            radioGroup.appendChild(heading);
        }
    
        const wrapper = document.createElement('div');
        wrapper.classList.add('option-wrapper');
    
        const label = document.createElement('label');
        label.classList.add('option-label');
    
        const input = document.createElement('input');
        input.type = 'radio';
        input.name = 'sex';
        input.value = `option_${option.id}`;
        input.required = true;
    
        // Добавляем радио-кнопку в label
        label.appendChild(input);
    
        // Добавляем HTML содержимое (список) в label после радио-кнопки
        const contentWrapper = document.createElement('span');
        contentWrapper.innerHTML = option.option_text;
        label.appendChild(contentWrapper);
    
        wrapper.appendChild(label);
        radioGroup.appendChild(wrapper);
    
        optionsContainer.appendChild(radioGroup);
    
        
      } else if (option.type === 'fill_text') {
        const label = document.createElement('label');
        const textarea = document.createElement('textarea');
        const wrapper = document.createElement('div');
        wrapper.classList.add('input-wrapper');
    
        label.innerHTML = option.option_text;
        textarea.name = `option_${option.id}`;
        textarea.required = true;
        textarea.classList.add('wide-textarea'); // Добавляем класс wide-textarea
    
        wrapper.appendChild(label);
        wrapper.appendChild(textarea);
        inputsContainer.appendChild(wrapper);
    
      } else if (option.type === 'country') {
        const label = document.createElement('label');
        const input = document.createElement('input');
        wrapper.classList.add('input-wrapper');

        label.innerHTML = option.option_text;
        input.type = 'text';
        input.name = `option_${option.id}`;
        input.required = true;
        input.placeholder = 'Введите страну'

        wrapper.appendChild(label);
        wrapper.appendChild(input);
        inputsContainer.appendChild(wrapper);
      } else if (option.type === 'fin') {
        // Создаем контейнер для группы радио-кнопок
        let radioGroup = document.getElementById('radio-group');
        if (!radioGroup) {
            radioGroup = document.createElement('div');
            radioGroup.id = 'radio-group';
            radioGroup.classList.add('radio-group');
    
            // Создаем заголовок для радио-кнопок
            const heading = document.createElement('h3');
            heading.innerText = 'Уровень риска в финансовых решениях';
            radioGroup.appendChild(heading);
        }
    
        const wrapper = document.createElement('div');
        wrapper.classList.add('option-wrapper');
    
        const label = document.createElement('label');
        label.classList.add('option-label'); // Класс для стилизации, если нужен
    
        const input = document.createElement('input');
        input.type = 'radio';
        input.name = 'risk_level';
        input.value = `option_${option.id}`;
        input.required = true;
    
        // Добавляем радио-кнопку в label
        label.appendChild(input);
    
        // Добавляем HTML содержимое (список) в label после радио-кнопки
        const contentWrapper = document.createElement('span'); // Дополнительный элемент для стилизации содержимого
        contentWrapper.innerHTML = option.option_text;
        label.appendChild(contentWrapper);
    
        wrapper.appendChild(label);
        radioGroup.appendChild(wrapper);
    
        optionsContainer.appendChild(radioGroup);
    }
    
    });
  }


  // Кнопка отправки ответов
  const submitButtonContainer = document.createElement('div');
  submitButtonContainer.className = 'submit-button-container';
  const submitButton = document.createElement('button');
  submitButton.classList.add('submit-answer');
  submitButton.textContent = 'Отправить ответ';
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
  const loadingElement = document.getElementById('loading');
  loadingElement.style.display = 'flex';
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
      loadingElement.style.display = 'none';
      showModal('Ошибка: ' + data.error);
    } else {
      console.log(data);
      loadingElement.style.display = 'none';
      // Показываем заработок за раунд
      const nextRoundButton = document.getElementById('next-round');
      nextRoundButton.style.display = 'block';
      showModal(data.message);
      if (data.endGame) {
        // Тут подсвечиваем кнопку для окончания игры, если раунд оказался последним
        showEndGameButton();
      }
    }
  })
  .catch(error => {
    // Ловим ошибки в сетевом запросе или ошибки парсинга JSON
    console.error('Ошибка при отправке ответов:', error);
    showModal('Произошла ошибка при отправке данных. Проверьте консоль для деталей.');
  });
}


function showEndGameButton() {
  const nextRoundButton = document.getElementById('next-round');
  nextRoundButton.style.display = 'none';
  const endGameButton = document.getElementById('end-game-button');
  endGameButton.style.display = 'flex'; // Сделать кнопку видимой

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
      showModal('Ошибка при завершении игры: ' + data.error);
    }
  })
  .catch(error => {
    console.error('Ошибка:', error);
  });
}

