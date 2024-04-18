const syncPointer = ({ x: pointerX, y: pointerY }) => {
    const x = pointerX.toFixed(2)
    const y = pointerY.toFixed(2)
    const xp = (pointerX / window.innerWidth).toFixed(2)
    const yp = (pointerY / window.innerHeight).toFixed(2)
    document.documentElement.style.setProperty('--x', x)
    document.documentElement.style.setProperty('--xp', xp)
    document.documentElement.style.setProperty('--y', y)
    document.documentElement.style.setProperty('--yp', yp)
  }
  document.body.addEventListener('pointermove', syncPointer)

document.addEventListener('DOMContentLoaded', (event) => {
    const switcher = document.getElementById('theme-switcher');
    switcher.addEventListener('change', function() {
        if (this.checked) {
            document.body.classList.add('dark-theme');
            localStorage.setItem('theme', 'dark');
        } else {
            document.body.classList.remove('dark-theme');
            localStorage.setItem('theme', 'light');
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const currentTheme = localStorage.getItem('theme');
    if (currentTheme === 'dark') {
      document.body.classList.add('dark-theme');
      document.getElementById('theme-switcher').checked = true;
    }
  });
  

  document.getElementById('start-new-game').addEventListener('click', function() {
    fetch('../../Game/start_game.php?action=startGame', {
      method: 'POST', // Если нужен POST-запрос
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
    })
    .then(response => {
      if (!response.ok) {
        throw new Error('Ошибка запроса');
      }
      return response.json();
    })
    .then(data => {
      // Проверьте ответ от сервера, если есть необходимые данные для игры
      if (data.success) {
        console.log(data);
        window.location.href = 'schedule.php';
        // loadRoundData(); // Здесь указан переход на страницу с первым заданием
      } else {
        // Обработка ситуации, если игру начать не удалось
        alert('Не удалось начать игру: ' + data.message);
      }
    })
    .catch(error => {
      console.error('Ошибка:', error);
    });
  });