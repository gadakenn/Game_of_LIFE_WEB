// Начинаем новую игру
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


document.querySelector('.hint-wrapper-main-list').addEventListener('click', () => {
    document.querySelector('.hint-wrapper-main-list').classList.toggle('show');
});




